<?php
namespace App\Services\RPN;

class RPN
{
    protected $operators = [
        'values' => '-+*/()',
        'priority' => [
            1,1,2,2,3,3
        ],
        'methods' => [
            'minus',
            'plus',
            'multiplication',
            'division',
            'wrong',
            'wrong'
        ]
    ];

    protected $polandRecord = [];
    protected $calcStack = [];
    protected $errorCode = '';
    protected $numUnformat = [];

    public function __construct($php_dec_point, $php_thousand_sep)
    {
        $this->numUnformat = [$php_dec_point, $php_thousand_sep];
    }

    public function errorCode()
    {
        return $this->errorCode;
    }

    public function parse($expression)
    {
        $this->polandRecord = [];
        if ($expression == '') {
            return;
        }
        $stackOperators = [];
        $operand = '';
        $expressionChar = str_split(str_replace(',', '.', str_replace(' ', '', $expression)));
        foreach ($expressionChar as $val) {
            $opIndex = strpos($this->operators['values'], $val);
            if ($opIndex !== false) {
                if ($operand != '') {
                    $this->addOperand($operand);
                    $operand = '';
                }
                if ($val != ')') {
                    $len = count($stackOperators);
                    if ($len != 0) {
                        if (($stackOperators[0]['priority'] < $this->operators['priority'][$opIndex])or
                            ($stackOperators[0]['operator'] == '(')) {
                            array_unshift(
                                $stackOperators,
                                [
                                    'priority' => $this->operators['priority'][$opIndex],
                                    'operator' => $val,
                                    'index' => $opIndex
                                ]
                            );
                        } else {
                            while (isset($stackOperators[0])) {
                                if ($stackOperators[0]['operator'] == '(') {
                                    array_shift($stackOperators);
                                    break;
                                }
                                $operator = array_shift($stackOperators);
                                $this->polandRecord[] = [
                                    'method' =>  $this->operators['methods'][$operator['index']]
                                ];
                            }
                            array_unshift(
                                $stackOperators,
                                [
                                    'priority' => $this->operators['priority'][$opIndex],
                                    'operator' => $val,
                                    'index' => $opIndex
                                ]
                            );
                        }
                    } else {
                        array_unshift(
                            $stackOperators,
                            [
                                'priority' => $this->operators['priority'][$opIndex],
                                'operator' => $val,
                                'index' => $opIndex
                            ]
                        );
                    }
                } else {
                    while (isset($stackOperators[0])) {
                        if ($stackOperators[0]['operator'] == '(') {
                            array_shift($stackOperators);
                            break;
                        }
                        $operator = array_shift($stackOperators);
                        $this->polandRecord[] = [
                            'method' =>  $this->operators['methods'][$operator['index']]
                        ];
                    }
                }
            } else {
                $operand.= $val;
            }
        }
        if ($operand != '') {
            $this->addOperand($operand);
        }
        if (count($stackOperators) != 0) {
            foreach ($stackOperators as $operator) {
                if ($operator['operator'] != ')') {
                    $this->polandRecord[] = [
                        'method' =>  $this->operators['methods'][$operator['index']]
                    ];
                }
            }
        }
    }

    public function calc($data, $test = false)
    {
        $this->calcStack = [];
        $this->errorCode = '';
        foreach ($this->polandRecord as $record) {
            $method = $record['method'].'Calc';
            $this->$method($record, $data, $test);
            if ($this->errorCode != '') {
                return false;
            }
        }
        return isset($this->calcStack[0]) ? $this->calcStack[0] : '';
    }

    public function test($expression)
    {
        if ($expression == '') {
            $this->errorCode = 'rpn.empty_formula';
            return false;
        }
        $this->parse($expression);
        $this->calc([], true);
        return $this->errorCode == '';
    }

    protected function addOperand($operand)
    {
        if (is_numeric($operand)) {
            $this->polandRecord[] = [
                'method' => 'constant',
                'value' => $operand
            ];
        } else {
            $this->polandRecord[] = [
                'method' => 'rowData',
                'name' => $operand
            ];
        }
    }

    protected function constantCalc($record)
    {
        if (!is_numeric($record['value'])) {
            $this->errorCode = 'rpn.constant_not_number';
            return;
        }
        $this->calcStack[] = $record['value'];
    }

    protected function rowDataCalc($record, $data, $test)
    {
        if ($test === true) {
            $this->calcStack[] = 1;
            return;
        }
        if (!array_key_exists($record['name'], $data)) {
            $this->errorCode = 'rpn.no_such_field';
            return;
        }
        $value = is_null($data[$record['name']]) ? 0 : strip_tags(str_replace($this->numUnformat, ['.', ''], preg_replace('/^[^\d]-+/', '', $data[$record['name']])));
        if (!is_numeric($value)) {
            $this->errorCode = 'rpn.invalid_field_type';
            return;
        }
        settype($value, 'float');
        $this->calcStack[] = $value;
    }

    protected function plusCalc($record, $data)
    {
        if (count($this->calcStack) < 2) {
            $this->errorCode = 'rpn.wrong_formula';
            return;
        }
        try {
            $this->calcStack[] = array_pop($this->calcStack)+array_pop($this->calcStack);
        } catch (Exception $e) {
            $this->errorCode = 'rpn.invalid_argument';
            return;
        }
    }

    protected function minusCalc($record, $data)
    {
        if (count($this->calcStack) < 2) {
            $this->errorCode = 'rpn.wrong_formula';
            return;
        }
        $op1 = array_pop($this->calcStack);
        $op2 = array_pop($this->calcStack);
        try {
            $this->calcStack[] = $op2 - $op1;
        } catch (Exception $e) {
            $this->errorCode = 'rpn.invalid_argument';
            return;
        }
    }

    protected function multiplicationCalc($record, $data)
    {
        if (count($this->calcStack) < 2) {
            $this->errorCode = 'rpn.wrong_formula';
            return;
        }
        try {
            $this->calcStack[] = array_pop($this->calcStack)*array_pop($this->calcStack);
        } catch (Exception $e) {
            $this->errorCode = 'rpn.invalid_argument';
            return;
        }
    }

    protected function divisionCalc($record, $data, $test)
    {
        if (count($this->calcStack) < 2) {
            $this->errorCode = 'rpn.wrong_formula';
            return;
        }
        $op1 = array_pop($this->calcStack);
        $op2 = array_pop($this->calcStack);
        if (($op1 == 0)or($op1 == '0')) {
            $this->errorCode = 'rpn.division_by_zero';
            return;
        }
        if ($test === true) {
            $this->calcStack[] = $op2*$op1;
        } else {
            try {
                $this->calcStack[] = $op2/$op1;
            } catch (Exception $e) {
                $this->errorCode = 'rpn.invalid_argument';
                return;
            }
        }
    }

    protected function wrongCalc($record, $data)
    {
        $this->errorCode = 'rpn.wrong_formula';
    }
}
