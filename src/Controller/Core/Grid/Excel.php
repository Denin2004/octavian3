<?php
namespace App\Controller\Core\Grid;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Roromix\Bundle\SpreadsheetBundle\Factory;

use App\Services\SiteConfig\SiteConfig;

class Excel extends AbstractController
{
    private $data;
    private $rowNum;
    private $sheet;

    public function index(Request $request, Factory $excelService, SiteConfig $config)
    {
        if ($request->request->get('excelData') == null) {
            return $this->render(
                'base.web.html.twig',
                [
                    'numeral' => $config->get('numeral')
                ]
            );
        }
        $this->data = json_decode($request->request->get('excelData'), true);
/*        dump($this->data);
        return $this->render(
            'base.web.html.twig',
            [
                'numeral' => $config->get('numeral')
            ]
        );

        if ($this->data == null) {
            return $this->render(
                'base.web.html.twig',
                [
                    'numeral' => $config->get('numeral')
                ]
            );
        }*/
        $objExcel = $excelService->createSpreadsheet();
        if ($this->data['head'] != false) {
            $tmpfile = tempnam(sys_get_temp_dir(), 'html');
            file_put_contents($tmpfile, '<?xml version="1.0" encoding="utf-8"?><html>'.$this->data['head'].'</html>');
            $excelHTMLReader = new Html('HTML');
            $objExcel = $excelHTMLReader->load($tmpfile);
            unlink($tmpfile);
            $objExcel->setActiveSheetIndex(0);
            $this->sheet = $objExcel->getActiveSheet();
            for ($line = 1; $line <= $this->data['headLines']; $line++) {
                for ($col = 0; $col < count($this->data['columns']); $col++) {
                    $colIndex = Coordinate::stringFromColumnIndex($col+1);
                    $val = $this->sheet->getCell($colIndex.($line))->getValue();
                    if ($val != null) {
                        $this->sheet->getStyle($colIndex.($line))->applyFromArray(
                            [
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => [
                                            'rgb' => '100000'
                                        ]
                                    ]
                                ],
                                'font' => [
                                    'bold' => true
                                ],
                                'alignment' => [
                                    'wrapText' => true
                                ]
                            ]
                        );
                    } else {
                        // Bug Spreadsheet !!!!
                        $this->sheet->getStyle($colIndex.($line))->applyFromArray(
                            [
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => [
                                            'rgb' => '100000'
                                        ]
                                    ]
                                ],
                                'font' => [
                                    'bold' => true
                                ],
                                'alignment' => [
                                    'wrapText' => true
                                ]
                            ]
                        );
                    }
                }
            }
            $this->rowNum = $this->data['headLines'];
        } else {
            $objExcel->setActiveSheetIndex(0);
            $this->sheet = $objExcel->getActiveSheet();
            $this->rowNum = 1;
            foreach ($this->data['columns'] as $i => $col) {
                $colIndex = Coordinate::stringFromColumnIndex($i+1);
                $this->sheet->setCellValueExplicit($colIndex.$this->rowNum, html_entity_decode($col['title'], ENT_QUOTES), DataType::TYPE_STRING);
                $this->data['columns'][$i]['aligment'] = Alignment::HORIZONTAL_LEFT;
                switch ($col['align']) {
                    case 'right':
                        $this->data['columns'][$i]['aligment'] = Alignment::HORIZONTAL_RIGHT;
                        break;
                    case 'center':
                        $this->data['columns'][$i]['aligment'] = Alignment::HORIZONTAL_CENTER;
                        break;
                }
                $this->sheet->getColumnDimension($colIndex)->setAutoSize(true);
                $this->sheet->getRowDimension($this->rowNum)->setRowHeight(40);
                $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => '100000'
                            ]
                        ]
                    ],
                    'font' => [
                        'bold' => true
                    ],
                    'alignment' => [
                        'wrapText' => true,
                        'horizontal' => $this->data['columns'][$i]['aligment'],
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);
            }
            $this->sheet->freezePane('A'.($this->rowNum+1));
            $this->rowNum++;
        }

        foreach ($this->data['columns'] as $i => $col) {
            if (!isset($this->data['columns'][$i]['aligment'])) {
                $this->data['columns'][$i]['aligment'] = Alignment::HORIZONTAL_LEFT;
                switch ($col['align']) {
                    case 'right':
                        $this->data['columns'][$i]['aligment'] = Alignment::HORIZONTAL_RIGHT;
                        break;
                    case 'center':
                        $this->data['columns'][$i]['aligment'] = Alignment::HORIZONTAL_CENTER;
                        break;
                }
            }
            $this->data['columns'][$i]['type'] = DataType::TYPE_STRING;
            $this->data['columns'][$i]['format'] = [
                'formatCode' => ''
            ];
            if (in_array('mfw-int', $col['types'])) {
                $this->data['columns'][$i]['format']['formatCode'] = $config->get('xls_int_format');
                $this->data['columns'][$i]['type'] = DataType::TYPE_NUMERIC;
            }
            if (in_array('mfw-num', $col['types'])) {
                $this->data['columns'][$i]['format']['formatCode'] = $config->get('xls_number_format');
                $this->data['columns'][$i]['type'] = DataType::TYPE_NUMERIC;
            }
            if (in_array('mfw-native-int', $col['types'])) {
                $this->data['columns'][$i]['type'] = DataType::TYPE_NUMERIC;
            }
            if (in_array('mfw-checkbox', $col['types'])&& isset($col['checked'])) {
                $this->data['columns'][$i]['checkbox'] = true;
                $this->data['columns'][$i]['aligment'] = Alignment::HORIZONTAL_CENTER;
            }
            if (isset($col['mfw_total'])) {
                $this->data['columns'][$i]['total'] = [
                    'sum' => 0,
                    'cnt' => 0
                ];
            }
        }
/*        dump($this->data);
        return $this->render(
            'base.web.html.twig',
            [
                'numeral' => $config->get('numeral')
            ]
        );*/
        if ($this->data['groups']) {
            foreach ($this->data['groups'] as $row) {
                if (isset($row['group'])) {
                    $groupColIndex = array_search($row['group']['dataIndex'], array_column($this->data['columns'], 'dataIndex'));
                    $colIndex = Coordinate::stringFromColumnIndex($groupColIndex+1);
                    $this->sheet->setCellValueExplicit($colIndex.$this->rowNum, $row['group']['value'], DataType::TYPE_STRING);
                    foreach ($this->data['columns'] as $i => $col) {
                        $colIndex = Coordinate::stringFromColumnIndex($i+1);
                        $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'CCFFCC'
                                ]
                            ]
                        ]);
                        if ($row['group']['detailed']) {
                            $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                                'borders' => [
                                    'bottom' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => [
                                            'rgb' => '000000'
                                        ]
                                    ]
                                ]
                            ]);
                        }
                    }
                    foreach ($row['group']['totals'] as $total) {
                        $totalColIndex = array_search($total['column']['dataIndex'], array_column($this->data['columns'], 'dataIndex'));
                        $colIndex = Coordinate::stringFromColumnIndex($totalColIndex+1);
                        switch (array_key_first($total['results'])) {
                            case 'sum':
                                $this->sheet->setCellValueExplicit($colIndex.$this->rowNum, $total['results']['sum'], DataType::TYPE_NUMERIC);
                                $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                                    'font' => [
                                        'bold' => true
                                    ],
                                    'alignment' => [
                                        'wrapText' => true,
                                        'horizontal' => $this->data['columns'][$totalColIndex]['aligment']
                                    ],
                                    'numberFormat' => $this->data['columns'][$totalColIndex]['format']
                                ]);
                                break;
                            case 'cnt':
                                $this->sheet->setCellValueExplicit($colIndex.$this->rowNum, $total['results']['cnt'], DataType::TYPE_NUMERIC);
                                $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                                    'font' => [
                                        'bold' => true
                                    ],
                                    'alignment' => [
                                        'wrapText' => true,
                                        'horizontal' => $this->data['columns'][$totalColIndex]['aligment']
                                    ],
                                    'numberFormat' => [
                                        'formatCode' => $config->get('xls_int_format')
                                    ]
                                ]);
                                break;
                            case 'avg':
                                $this->sheet->setCellValueExplicit($colIndex.$this->rowNum, $total['results']['avg'], DataType::TYPE_NUMERIC);
                                $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                                    'font' => [
                                        'bold' => true
                                    ],
                                    'alignment' => [
                                        'wrapText' => true,
                                        'horizontal' => $this->data['columns'][$totalColIndex]['aligment']
                                    ],
                                    'numberFormat' => $this->data['columns'][$totalColIndex]['format']
                                ]);
                                break;
                        }
                    }
                    $this->sheet->getRowDimension($this->rowNum)->setRowHeight(30);
                    $this->rowNum++;
                }
                if (isset($row['rowIndex'])) {
                    dump($this->data['data'][$row['rowIndex']]);
                    $this->showRow($this->data['data'][$row['rowIndex']]);
                }
            }
        } else {
            foreach ($this->data['data'] as $row) {
                $this->showRow($row);
            }
        }
        foreach ($this->data['columns'] as $i => $col) {
            if (!isset($column['mfw_total'])) {
                continue;
            }
            $colIndex = Coordinate::stringFromColumnIndex($i + 1);
            switch ($column['mfw_total']) {
                case 'sum':
                    $this->sheet->setCellValueExplicit($colIndex.$this->rowNum, $col['total']['sum'], DataType::TYPE_NUMERIC);
                    $aSheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                        'alignment' => [
                            'horizontal' => $col['alignment'],
                            'wrapText' => true
                        ],
                        'font' => [
                            'bold' => true
                        ],
                        'numberFormat' => $col['format']
                    ]);
                    break;
                case 'sum-duration':
                    $hours = floor($col['total']['sum'] / 3600);
                    $mins = floor($col['total']['sum'] / 60 % 60);
                    $secs = floor($col['total']['sum'] % 60);
                    $this->sheet->setCellValueExplicit($colIndex.$this->rowNum, sprintf('%02d:%02d:%02d', $hours, $mins, $secs), DataType::TYPE_STRING);
                    $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                        'alignment' => [
                            'horizontal' => $col['alignment'],
                            'wrapText' => true
                        ],
                        'font' => [
                            'bold' => true
                        ]
                    ]);
                    break;
                case 'count':
                    $this->sheet->setCellValueExplicit($colIndex.$this->rowNum, $col['total']['cnt'], DataType::TYPE_NUMERIC);
                    $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                        'alignment' => [
                            'horizontal' => $col['alignment'],
                            'wrapText' => true
                        ],
                        'font' => [
                            'bold' => true
                        ],
                        'numberFormat' => [
                            'formatCode' => $this->siteConfig->get('xls_int_format')
                        ]
                    ]);
                    break;
                case 'avg':
                    if ($col['total']['cnt'] != 0) {
                        $this->sheet->setCellValueExplicit($colIndex.$this->rowNum, $col['total']['sum'] / $col['total']['cnt'], DataType::TYPE_NUMERIC);
                        $this->heet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                            'alignment' => [
                                'horizontal' => $col['alignment'],
                                'wrapText' => true
                            ],
                            'font' => [
                                'bold' => true
                            ],
                            'numberFormat' => $this->siteConfig->get('xls_number_format')
                        ]);
                    }
                    break;
            }
            $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray([
                'borders' => [
                    'left' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => 'FFFFFF'
                        ]
                    ],
                    'right' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => 'FFFFFF'
                        ]
                    ],
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => 'FFFFFF'
                        ]
                    ]
                ]
            ]);
        }
        $this->sheet->setTitle('Report');
        //$file = explode(',', $data->title);
        $file = ['test'];
        $writer = $excelService->createWriter($objExcel, 'Xls');
        $response = $excelService->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, implode('_', $file).'.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function showRow($row)
    {
        foreach ($this->data['columns'] as $key => $column) {
            $colIndex = Coordinate::stringFromColumnIndex($key+1);
            dump($column);
            if (isset($column['checkbox'])) {
                $this->sheet->setCellValueExplicit(
                    $colIndex.$this->rowNum,
                    $row[$column['dataIndex']] == $column['checked'] ? 'V' : '',
                    DataType::TYPE_STRING
                );
            } else {
                $this->sheet->setCellValueExplicit(
                    $colIndex.$this->rowNum,
                    $row[$column['dataIndex']],
                    $column['type']
                );
            }
            $this->sheet->getStyle($colIndex.$this->rowNum)->applyFromArray(
                [
                    'alignment' => [
                        'horizontal' => $column['aligment'],
                        'wrapText' => true,
                    ],
                    'numberFormat' => $column['format'],
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => '000000']
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => '000000']
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => '000000']
                        ]
                    ]
                ]
            );
            if (isset($column['total'])) {
                switch ($column['mfw_total']) {
                    case 'sum-duration':
                    case 'sum':
                        $this->data['columns'][$key]['total']['sum'] = $this->data['columns'][$key]['total']['sum'] + $row[$column['dataIndex']];
                        break;
                    case 'count':
                        $this->data['columns'][$key]['total']['cnt']++;
                        break;
                    case 'avg':
                        $this->data['columns'][$key]['total']['sum'] = $this->data['columns'][$key]['total']['sum'] + $row[$column['dataIndex']];
                        $this->data['columns'][$key]['total']['cnt']++;
                        break;
                }
            }
        }
        $this->rowNum++;
    }
}
