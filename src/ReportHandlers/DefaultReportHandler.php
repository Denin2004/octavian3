<?php
namespace App\ReportHandlers;

use App\Form\Core\Grid\InlineEdit;

class DefaultReportHandler
{
    protected $callPrm;
    protected $controller;
    protected $error;
    protected $lastError;

    public function __construct($params)
    {
        $this->callPrm = $params;
        $this->error = false;
        $this->lastError = '';
    }

    public function isError()
    {
        return $this->error;
    }

    public function mfwNumFldConfig(&$column)
    {
    }

    public function mfwNumFldResult(&$res, $key, $column, $row)
    {
    }

    public function mfwIntFldConfig(&$column)
    {
    }

    public function mfwIntFldResult(&$res, $key, $column, $row)
    {
    }

    public function mfwDateTimeFldResult(&$res, $key, $column, $row)
    {
        if (($res[$key][$column['data']] == '')or($res[$key][$column['data']] == null)) {
            $res[$key][$column['data'].'_SORT'] = '';
            return;
        }
        $date = \DateTime::createFromFormat(
            $this->callPrm['siteConfig']->get('php_date_format').' '.$this->callPrm['siteConfig']->get('php_time_format'),
            $res[$key][$column['data']]
        );
        if ($date === false) {
            $res[$key][$column['data']] = 'date.invalid_format';
        } else {
            $res[$key][$column['data'].'_SORT'] = $date->format('YmdHis');
        }
    }

    public function mfwTimeFldResult(&$res, $key, $column, $row)
    {
        $res[$key][$column['data'].'_SORT'] = $res[$key][$column['data']] == '' ? '' :  \DateTime::createFromFormat(
            $this->callPrm['siteConfig']->get('php_time_format'),
            $res[$key][$column['data']]
        )->format('His');
    }

    public function mfwDateFldResult(&$res, $key, $column, $row)
    {
        if (($res[$key][$column['data']] == '')or($res[$key][$column['data']] == null)) {
            $res[$key][$column['data'].'_SORT'] = ' ';
            return;
        }
        $date = \DateTime::createFromFormat(
            $this->callPrm['siteConfig']->get('php_date_format'),
            $res[$key][$column['data']]
        );
        if ($date === false) {
            $date = \DateTime::createFromFormat(
                $this->callPrm['siteConfig']->get('php_date_format').' '.$this->callPrm['siteConfig']->get('php_time_format'),
                $res[$key][$column['data']]
            );
            if ($date === false) {
                $res[$key][$column['data']] = $this->callPrm['translator']->trans('date.invalid_format');
            } else {
                $res[$key][$column['data']] = $date->format($this->callPrm['siteConfig']->get('php_date_format'));
            }
        }
        $res[$key][$column['data'].'_SORT'] = $date === false ? '' : \DateTime::createFromFormat(
            $this->callPrm['siteConfig']->get('php_date_format'),
            $res[$key][$column['data']]
        )->format('YmdHis').'000000';
    }

    public function mfwDateTimeMilliFldResult(&$res, $key, $column, $row)
    {
        if (($res[$key][$column['data']] == '')or($res[$key][$column['data']] == null)) {
            $res[$key][$column['data'].'_SORT'] = ' ';
            return;
        }
        $res[$key][$column['data'].'_SORT'] =  \DateTime::createFromFormat(
            $this->callPrm['siteConfig']->get('php_date_format').' '.$this->callPrm['siteConfig']->get('php_time_milli_format'),
            $res[$key][$column['data']]
        )->format('YmdHisu');
    }

    public function mfwCheckboxFldResult(&$res, $key, $column, $row)
    {
        $checked = $res[$key][$column['data']] == $column['checked'];
        $res[$key][$column['data']] = $this->callPrm['templating']->render(
            'core/grid/checkbox.html.twig',
            [
                'checked' => $checked,
                'text' => isset($column['checked_text']) ? ($checked == true ? $column['checked_text'] : (isset($column['unchecked_text']) ? $column['unchecked_text'] : ''))
                    : ($checked == true ? $column['checked'] : '')
            ]
        );
    }

    public function mfwCheckboxFldConfig(&$column)
    {
        $column['mfw_format'] = $this->callPrm['siteConfig']->get('js_int_format');
        $column['className'] = isset($column['className']) ? $column['className'] : 'dt-body-center dt-foot-center dt-head-center';
        $column['checked_text'] = isset($column['checked_text']) ? $column['checked_text'] : $column['checked'];
    }

    public function mfwIconFldResult(&$res, $key, $column, $row)
    {
        $res[$key][$column['data']] = $this->callPrm['templating']->render(
            'core/grid/icon.html.twig',
            [
                'value' => $res[$key][$column['data']],
                'icons' => $column['icons']
            ]
        );
    }

    public function mfwCheckboxinputFldResult(&$res, $key, $column, $row)
    {
        $checked = $column['checked'] == 'not empty' ? $res[$key][$column['data']] != '' :
                    $res[$key][$column['data']] == $column['checked'];
        $res[$key][$column['data']] = $this->callPrm['templating']->render(
            'core/grid/checkbox_input.html.twig',
            [
                'checked' => $checked,
                'url' => $this->callPrm['router']->generate(
                    $column['action']['route'],
                    $this->urlParams($column['action'], $row, $res[$key])
                ),
                'colName' => $column['data'],
                'text' => [
                    'checked' => $column['checked_text'],
                    'unchecked' => $column['unchecked_text']
                ]
            ]
        );
    }

    public function mfwActionConfig(&$column)
    {
        $column['mfw_noexcel'] = true;
        $column['mfw_noprint'] = true;
        $column['mfw_nogroup'] = true;
        $column['orderable'] = false;
    }

    public function mfwActionFldResult(&$res, $key, $column, $row)
    {
        $actions = [];
        foreach ($column['actions'] as $action) {
            $actions[] = $this->getLink(
                $action,
                $row,
                $res[$key],
                isset($action['textLink']) ? $this->callPrm['translator']->trans($action['textLink']) :
                $this->callPrm['translator']->trans($action['title'])
            );
        }
        $res[$key][$column['data']] = isset($res[$key][$column['data']]) ? $res[$key][$column['data']].implode('', $actions) : implode('', $actions);
    }

    public function mfwPopupActionFldResult(&$res, $key, $column, $row)
    {
        foreach ($column['actions'] as $index => $action) {
            $actionType = 0;
            foreach ($action as $key => $row) {
                $actionType = $key;
                break;
            }
            $params = $this->urlParams($action[$actionType]['action'], $row, $res[$key]);
            $column['actions'][$index][$actionType]['url'] = $this->callPrm['router']->generate(
                $action[$actionType]['action']['route'],
                $params
            );
            if (isset($action[$actionType]['text'])) {
                $val = explode(':', $action[$actionType]['text']);
                switch ($val[0]) {
                    case 'row':
                        if (isset($row[$val[1]])) {
                            $column['actions'][$index][$actionType]['text'] = urlencode(ltrim(strip_tags($row[$val[1]])));
                        } else {
                            $column['actions'][$index][$actionType]['text'] = $this->callPrm['translator']->trans('report.no_such_column').' '.$val[1];
                        }
                        break;
                    case 'qry':
                        $formData = $this->callPrm['report']->getFormData();
                        if (isset($formData[$val[1]])) {
                            $column['actions'][$index][$actionType]['text'] = urlencode($formData[$val[1]]);
                        } else {
                            $column['actions'][$index][$actionType]['text'] = $this->callPrm['translator']->trans('report.no_such_query').' '.$val[1];
                        }
                        break;
                    case 'val':
                        $column['actions'][$index][$actionType]['text'] = $this->callPrm['translator']->trans($val[1]);
                        break;
                    case 'reportID':
                        $params[$key] = $this->callPrm['report']->get('reportID');
                        break;
                    case 'reportUniqID':
                        $params[$key] = $this->callPrm['report']->get('uniqID');
                        break;
                    case 'reportBaseID':
                        $params[$key] = $this->callPrm['report']->get('id');
                        break;
                }
            }
        }
        $res[$key][$column['data']] = $this->callPrm['templating']->render(
            'core/grid/actions.html.twig',
            [
                'value' => $res[$key][$column['data']],
                'items' => $column['actions']
            ]
        );
    }

    public function mfwVocFldResult(&$res, $key, $column, $row)
    {
        if ($res[$key][$column['data']] === null) {
            $res[$key][$column['data']] = '';
            return;
        }
        if (isset($column['voc'][$res[$key][$column['data']]])) {
            $res[$key][$column['data']] = $this->callPrm['translator']->trans($column['voc'][$res[$key][$column['data']]]);
        } else {
            $res[$key][$column['data']] = $this->callPrm['translator']->trans('common.unknown_value');
        }
    }

    public function mfwDurationFldConfig(&$column)
    {
        $column['className'] = isset($column['className']) ? $column['className'] : 'dt-body-right dt-foot-right dt-head-right';
    }

    public function mfwDurationFldResult(&$res, $key, $column, $row)
    {
        $res[$key][$column['data'].'_SORT'] = (float)$res[$key][$column['data']];
        $res[$key][$column['data']] = $this->controller->secsToDuration($res[$key][$column['data']]*(isset($column['pureSecs']) ? 1 : 3600));
    }

    public function mfwHighlightFldConfig(&$column)
    {
        $column['mfw_type'] = 'num-html';
    }

    public function mfwHighlightFldResult(&$res, $key, $column, $row)
    {
        $res[$key][$column['data']] = $this->controller->getHighlight($res[$key][$column['data']]);
    }

    public function getLink($action, $row, $res, $title)
    {
        $params = $this->urlParams($action, $row, $res);
        if ($params === false) {
            return (($title ==  '')or($title == ' ')or is_null($title)) ? '' : $title.' Error link params';
        }
        $attrs = isset($action['attrs']) ? $action['attrs'] : [];
        if (isset($attrs['data-mfw_confirm'])) {
            $attrs['data-mfw_confirm'] = $this->callPrm['translator']->trans($attrs['data-mfw_confirm']);
        }
        if (isset($action['ajax'])) {
            return $this->callPrm['templating']->render(
                'core/grid/span_link.html.twig',
                [
                    'item' => [
                        'url' => $this->callPrm['router']->generate(
                            $action['route'],
                            $params
                        ),
                        'text' => $title,
                        'attrs' => $attrs
                    ]
                ]
            );
        }
        if (!isset($attrs['target'])) {
            $attrs['target'] = '_blank';
        }
        return $this->callPrm['templating']->render(
            'core/grid/a_link.html.twig',
            [
                'item' => [
                    'url' => $this->callPrm['router']->generate(
                        $action['route'],
                        $params
                    ),
                    'text' => $title,
                    'attrs' => $attrs
                ]
            ]
        );
    }

    public function mfwLinkFldConfig(&$column)
    {
        $column['type'] = isset($column['numSort'])&& $column['numSort'] == true ? '' : 'html';
    }

    public function mfwIconFldConfig(&$column)
    {
        $column['type'] = 'html';
    }

    public function mfwLinkFldResult(&$res, $key, $column, $row)
    {
        $res[$key][$column['data']] = $this->getLink(
            $column['action'],
            $row,
            $res[$key],
            isset($column['action']['textLink']) ?  $this->callPrm['translator']->trans($column['action']['textLink']) : $res[$key][$column['data']]
        );
    }

    public function mfwInlineEditFldResult(&$res, $key, $column, $row)
    {
        $form = $this->callPrm['formBuilder']->create(
            InlineEdit::class,
            [
                'id' => $res[$key][$column['id']],
                'value' => $res[$key][$column['data']]
            ],
            [
                'action' => $this->callPrm['router']->generate(
                    $column['action']['route'],
                    $this->urlParams($column['action'], $row, $res[$key])
                )
            ]
        );
        $res[$key][$column['data']] = $this->callPrm['templating']->render(
            'core/grid/inline_edit.html.twig',
            [
                'value' => $res[$key][$column['data']],
                'form' => $form->createView(),
                'column' => $column
            ]
        );
    }

    public function mfwChangeFldResult(&$res, $key, $column, $row)
    {
        $res[$key][$column['data']] = '';
        $newPrefix = isset($column['newPrefix']) ? $column['newPrefix'] : '_NEW';
        $changes = [];
        switch (strtoupper($res[$key][$column['action']])) {
            case 'INSERT':
                foreach ($column['fields'] as $field) {
                    if ($res[$key][$field['data'].$newPrefix] != null) {
                        $changes[] = [
                            'title' => $field['title'],
                            'new' => $res[$key][$field['data'].$newPrefix]
                        ];
                    }
                }
                break;
            case 'UPDATE':
                foreach ($column['fields'] as $field) {
                    if (($res[$key][$field['data'].'_OLD'] != $res[$key][$field['data'].$newPrefix])or
                        (($res[$key][$field['data'].'_OLD'] == null)and($res[$key][$field['data'].$newPrefix] != null))or
                        (($res[$key][$field['data'].'_OLD'] != null)and($res[$key][$field['data'].$newPrefix] == null))
                    ) {
                        $changes[] = [
                            'title' => $field['title'],
                            'new' => $res[$key][$field['data'].$newPrefix],
                            'old' => $res[$key][$field['data'].'_OLD']
                        ];
                    }
                }
                break;
            case 'DELETE':
                foreach ($column['fields'] as $field) {
                    $changes[] = [
                        'title' => $field['title'],
                        'old' => $res[$key][$field['data'].'_OLD']
                    ];
                }
                break;
        }
        if (count($changes) != 0) {
            $res[$key][$column['data']] = $this->callPrm['templating']->render(
                'report/result/grid_cell_changes.html.twig',
                [
                    'changes' => $changes
                ]
            );
        }
    }

    public function queryField(&$form, $field, $name)
    {
    }

    public function inputField(&$form, $field, $name)
    {
    }

    public function customFldResult(&$res, $key, $column, $row)
    {
    }

    public function customFldConfig(&$column)
    {
    }

    public function beforeResult(&$report, $id, &$formData)
    {
    }

    public function beforeResultQuery(&$report)
    {
    }

    public function afterResult(&$resultData, &$result)
    {
    }

    public function beforePage(&$report, $report_id)
    {
    }

    public function afterParseResults(&$report)
    {
    }

    protected function urlParams($action, $row, $res)
    {
        $params = [];
        if (isset($action['params'])) {
            foreach ($action['params'] as $key => $value) {
                if ($key == 'drillData') {
                    $params['drillData'] = [];
                    foreach ($value as $name => $val) {
                        $params[$key][$name] = $this->paramValue($val, $row, $res);
                        if ($params[$key][$name] === false) {
                            return false;
                        }
                    }
                } else {
                    $params[$key] = $this->paramValue($value, $row, $res);
                    if ($params[$key] === false) {
                        return false;
                    }
                }
            }
        }
        return $params;
    }

    protected function paramValue($value, $row, $res)
    {
        $val = explode(':', $value);
        switch ($val[0]) {
            case 'res':
                if (isset($res[$val[1]])) {
                    return urlencode(ltrim(strip_tags($res[$val[1]])));
                }
                return false;//'No row column '.$val[1];
            case 'row':
                if (isset($row[$val[1]])) {
                    return urlencode(ltrim(strip_tags($row[$val[1]])));
                }
                return false;//'No row column '.$val[1];
            case 'qry':
                $formData = $this->callPrm['report']->getFormData();
                if (isset($formData[$val[1]])) {
                    return urlencode($formData[$val[1]]);
                }
                return false;//'No input data '.$key;
            case 'val':
                return $val[1];
            case 'reportID':
                return $this->callPrm['report']->get('reportID');
            case 'reportUniqID':
                return $this->callPrm['report']->get('uniqID');
            case 'reportBaseID':
                return $this->callPrm['report']->get('id');
        }
    }

    public function getLastError()
    {
        return $this->getLastError();
    }

    public function mfwNumRecFldConfig(&$column)
    {
        $column['mfw_format'] = $this->callPrm['siteConfig']->get('js_int_format');
        $column['className'] = isset($column['className']) ? $column['className'] : 'dt-body-right dt-foot-right dt-head-right';
    }

    public function mfwNumRecFldResult(&$res, $key, $column, $row)
    {
        $res[$key][$column['data']] = $key+1;
    }

    public function mfwPctFldConfig(&$column)
    {
        $column['mfw_format'] = $this->callPrm['siteConfig']->get('js_number_format');
        $column['className'] = isset($column['className']) ? $column['className'] : 'dt-body-right dt-foot-right dt-head-right';
    }

    public function mfwPctFldResult(&$res, $key, $column, $row)
    {
        $res[$key][$column['data']] = $this->controller->getNumberFormat($res[$key][$column['data']]*100, isset($column['dec']) ? $column['dec'] : null);
    }
}
