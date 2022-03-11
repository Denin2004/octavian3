<?php
namespace App\Services\Report;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Symfony\Component\Form\FormFactoryInterface;

use App\Form\Report\Query;
use App\Services\MyACP\MyACP;
use App\Services\SiteConfig\SiteConfig;
use App\Controller\CommonController;
use App\Services\RPN\RPNService;

class Report
{

    const REPORT_RESULT = 0;
    const DATATABLE_RESULT = 1;
    const PURE_RESULT = 2;
    const FORMATTED_RESULT = 3;

    protected $formData = [];
    protected $lastError = '';
    protected $myACP;
    protected $siteConfig;
    protected $reportID;
    protected $uniqid;
    protected $id;
    protected $request;
    protected $reportDB;
    protected $rpn;
    protected $router;
    protected $twig;
    protected $formBuilder;

    public function __construct(
        MyACP $myACP,
        SiteConfig $config,
        RequestStack $requestStack,
        RPNService $rpn,
        RouterInterface $router,
        FormFactoryInterface $formBuilder
    ) {
        $this->myACP = $myACP;
        $this->siteConfig = $config;
        $this->request = $requestStack->getCurrentRequest();
        $this->rpn = $rpn;
        $this->router = $router;
        $this->formBuilder = $formBuilder;
    }

    public function isError()
    {
        return $this->lastError != '';
    }

    public function getReport(Request $request, $id, $uniqid = '')
    {
        if (!$this->myACP->getUser()->getReportAccess($id)) {
            $this->lastError = '403';
            return [
                'error' => '403'
            ];
        }
        $this->lastError = '';
        $this->reportDB = $this->readReport($id);
        if ($this->reportDB === false) {
            return $this->reportDB;
        }
        $this->id = $id;
        $this->uniqid = $uniqid == '' ? substr(uniqid(), -6) : $uniqid;
        $this->reportDB['db_id'] = $this->id;
        $this->reportDB['uniqid'] = $this->uniqid;
        $this->reportDB['id'].= '_'.$this->reportDB['uniqid'];
        $this->reportID = $this->reportDB['id'];
        $this->reportDB['handler']->beforePage($this->reportDB, $id);
        if ($this->reportDB['handler']->isError()) {
            $this->lastError = $this->reportDB['handler']->getLastError();
            $this->reportDB['error'] = $this->lastError;
            return $this->reportDB;
        }
        if (isset($this->reportDB['query'])) {
            $query = $request->query->all();
            foreach ($query as $key => $qry) {
                $query[$key] = is_array($qry) ? $qry : urldecode($qry);
            }
            $form = $this->formBuilder->create(
                Query::class,
                $query,
                [
                    'query' => $this->reportDB['query']
                ]
            );
            $form->add(
                'show',
                SubmitType::class,
                [
                    'attr'=> [
                        'class' => 'btn-success mb-0 btn-sm ml-2',
                        'type' => 'submit'
                    ],
                    'label' => 'common.show'
                ]
            );

            $this->formData = $form->getData();
            $this->reportDB['autoload'] = isset($this->reportDB['autoload']) ? $this->reportDB['autoload'] : 'false';
            if (!isset($query['autoload'])) {
                foreach ($form->all() as $key => $val) {
                    if (isset($query[$key])) {
                        $this->reportDB['autoload'] = 'true';
                        break;
                    }
                    if ($this->checkFormQuery($val, $query)) {
                        $this->reportDB['autoload'] = 'true';
                        break;
                    }
                }
            }
            $v = $form->createView();
            $this->reportDB['formQuery'] = $v->vars['react'];
            $this->reportDB['results'] = $this->parseResults();
            if ($this->reportDB['results'] === false) {
                $this->reportDB['error'] = $this->lastError;
                return $this->reportDB;
            }
            $this->reportDB['handler']->afterParseResults($this->reportDB);
        } else {
            $this->reportDB['results'] = $this->parseResults();
            if ($this->reportDB['results'] === false) {
                $this->reportDB['error'] = $this->lastError;
                return $this->reportDB;
            }
            $this->reportDB['handler']->afterParseResults($this->reportDB);
            $this->reportDB['handler']->beforeResult($this->reportDB, $id, $this->formData);
            if ($this->reportDB['handler']->isError()) {
                $this->lastError = $this->reportDB['handler']->getLastError();
                $this->reportDB['error'] = $this->lastError;
                return $this->reportDB;
            };
        }
        $this->reportDB['reportId'] = $id;
        return $this->reportDB;
    }

    // typeResult 0 - report result , 1- datatable result, 2 - pure result, 3 - formatted
    public function getResult(Request $request, $id)
    {
        $res = [];
        if (!$this->myACP->getUser()->getReportAccess($id)) {
            $this->lastError = '403';
            return $res;
        }
        $this->reportDB = $this->readReport($id);
        if ($this->reportDB === false) {
            return $res;
        }
        $this->id = $id;
        $this->reportID = $this->reportDB['id'];
        $this->formData = [];
        $this->reportDB['handler']->beforeResultQuery($this->reportDB);
        if ($this->reportDB['handler']->isError()) {
            $this->lastError = $this->reportDB['handler']->getLastError();
            return $res;
        };
        if (isset($this->reportDB['query'])) {
            $form = $this->formBuilder->create(
                Query::class,
                [],
                [
                    'query' => $this->reportDB['query'],
                    'request' => true
                ]
            );

            $formRequest = json_decode($request->getContent(), true);
            if ($formRequest == null) {
                $this->lastError = 'report.error.no_query_data';
                return $res;
            }
            $form->submit($formRequest);
            if (!$form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    $this->lastError.= 'Code400 '.$error->getMessage().' ';
                }
                return $res;
            }
            $this->formData = $form->getData();
            if (isset($this->reportDB['query']['multiUpload'])) {
                $multiRes = [];
                $dateFormat = $form->get('date_from')->getConfig()->getOptions()['php_format'];
                if ($dateFormat ==  $this->siteConfig->get('php_date_format')) {
                    $dateFormat = '!'.$dateFormat;
                } else {
                    $this->formData['date_from'].= ':00';
                    $this->formData['date_to'].= ':00';
                }
                if ($this->formData['step'] == 0) {
                    $controller->addResponse([
                        'mfwFormMU:setSteps' => [
                            'selector' => '#rep_'.$this->reportDB['id'].' .mfw-qry-form',
                            'steps' => $controller->getSteps(
                                $this->formData,
                                [
                                    'interval' => 'P1D',
                                    'dateFormat' => $dateFormat,
                                ]
                            )
                        ]
                    ]);
                }
                $controller->setStep(
                    $this->formData,
                    [
                        'interval' => 'P1D',
                        'dateFormat' => $dateFormat,
                    ]
                );
            }
        }

        $this->reportDB['handler']->beforeResult($this->reportDB, $id, $this->formData);
        if ($this->reportDB['handler']->isError()) {
            $this->lastError = $this->reportDB['handler']->getLastError();
            return $res;
        };
        foreach ($this->reportDB['results'] as $resKey => $result) {
            $res[$resKey] = isset($result['query']) ? $this->getDBResult($result, isset($this->reportDB['query']) ? $this->reportDB['query']['fields'] : []) : [];
            if ($res[$resKey] === false) {
                return false;
            }
            $this->reportDB['handler']->afterResult($res[$resKey], $result);
            if (isset($result['tableConfig'])) {
                $rows = isset($res[$resKey]['result']) ? $res[$resKey]['result'] : $res[$resKey];
                $clcFields = [];
                if (isset($result['tableConfig']['tableInit']['buttons']) &&
                    (in_array('mfwClcFields', $result['tableConfig']['tableInit']['buttons']))) {
                    $clcFieldsDB = $this->myACP->getRepository('ClcFields');
                    $clcFields = $clcFieldsDB->getClcFields([
                        'report_id' => $id,
                        'realTblSelector' => '#tbl_'.$this->reportDB['id'].'_'.$resKey
                    ], true);
                    if ($this->isDBError()) {
                        return $res;
                    }
                    foreach ($clcFields as $key => $clcField) {
                        $clcFields[$key]['rpn'] = $this->rpn->createRPN();
                        $formula = json_decode($clcField['FORMULA'], true);
                        $clcFields[$key]['rpn']->parse(implode('', $formula['tags']));
                    }
                }
                $this->tableFormatResult($result, $rows, $clcFields);
            }
            if (isset($result['pivotConfig'])) {
                $frmtRes = $this->pivotFormatResult($result, $res);
            }
        }
        return $res;
    }

    // typeResult 0 - report result , 1- datatable result, 2 - pure result, 3 - formatted
    public function addResult($addData, CommonController $controller, $id, $typeResult, $uniqid)
    {
        $res = [];
        if (!$this->myACP->getUser()->getReportAccess($id)) {
            $this->lastError = '403';
            return $res;
        }
        $this->reportDB = $this->readReport($controller, $id);
        if ($this->reportDB === false) {
            return $res;
        }
        $this->id = $id;
        $this->uniqid = $uniqid;
        $this->reportDB['db_id'] = $this->id;
        $this->reportDB['uniqid'] = $uniqid;
        $this->reportDB['id'].= '_'.$uniqid;
        $this->reportID = $this->reportDB['id'];
        $this->formData = [];

        $this->reportDB['handler']->beforeResult($this->reportDB, $id, $this->formData);
        if ($this->reportDB['handler']->isError()) {
            $this->lastError = $this->reportDB['handler']->getLastError();
            return $res;
        };
        foreach ($this->reportDB['results'] as $resKey => $result) {
            $res = $addData;
            if ($res === false) {
                return $res;
            }
            if ($typeResult == self::PURE_RESULT) {
                return $res;
            }
            $this->reportDB['handler']->afterResult($res, $result);
            if ($typeResult == self::FORMATTED_RESULT) {
                return $res;
            }
            if (isset($result['tableConfig'])) {
                $rows = isset($res['result']) ? $res['result'] : $res;
                $clcFields = [];
                if (isset($result['tableConfig']['tableInit']['buttons']) &&
                    (in_array('mfwClcFields', $result['tableConfig']['tableInit']['buttons']))) {
                    $clcFieldsDB = $this->myACP->getRepository('ClcFields');
                    $clcFields = $clcFieldsDB->getClcFields([
                        'report_id' => $id,
                        'realTblSelector' => '#tbl_'.$this->reportDB['id'].'_'.$resKey
                    ], true);
                    if ($this->isDBError()) {
                        return $res;
                    }
                    foreach ($clcFields as $key => $clcField) {
                        $clcFields[$key]['rpn'] = $this->rpn->createRPN();
                        $formula = json_decode($clcField['FORMULA'], true);
                        $clcFields[$key]['rpn']->parse(implode('', $formula['tags']));
                    }
                }
                $frmtRes = $this->tableFormatResult($result, $rows, $clcFields, $controller);
                if ($frmtRes === false) {
                    return $res;
                }
                if ($typeResult == self::DATATABLE_RESULT) {
                    return $frmtRes;
                } else {
                    if (isset($this->reportDB['query']['multiUpload'])) {
                        $multiRes[] = [
                            'selector' => '#tbl_'.$this->reportDB['id'].'_'.$resKey,
                            'data' => $frmtRes
                        ];
                    } else {
                        $controller->addResponse([
                            'mfwDataTable:addData' => [
                                'selector' => '#tbl_'.$this->reportDB['id'].'_'.$resKey,
                                'data' => $frmtRes
                            ]
                        ]);
                    }
                }
            }
            if (isset($result['pivotConfig'])) {
                $frmtRes = $this->pivotFormatResult($result, $res);
                if ($frmtRes === false) {
                    return $res;
                }
                if (isset($this->reportDB['query']['multiUpload'])) {
                    $multiRes[] = [
                        'selector' => '#pivot_'.$this->reportDB['id'].'_'.$resKey,
                        'data' => $frmtRes
                    ];
                } else {
                    $controller->addResponse([
                        'mfwPivot:jsonBuild' => [
                            'selector' => '#pivot_'.$this->reportDB['id'].'_'.$resKey,
                            'body' => $frmtRes
                        ]
                    ]);
                }
            }
            if (isset($result['twigConfig'])) {
                $controller->addResponse([
                    'setHTML' => [
                        'selector' => '#twig_'.$this->reportDB['id'].'_'.$resKey,
                        'html' => $this->twig->render(
                            $result['twigConfig']['twig'],
                            [
                                'res' => $res,
                                'report' => $this->reportDB,
                                'formData' => $this->formData
                            ]
                        )
                    ]
                ]);
            }
        }
        return $res;
    }

    protected function getMethodName($name)
    {
        $nm = explode('-', $name);
        $res = array_shift($nm);
        foreach ($nm as $partName) {
            $res.= ucfirst($partName);
        }
        return $res;
    }

    public function parseResults()
    {
        foreach ($this->reportDB['results'] as $key => $result) {
            if (isset($result['tableConfig'])) {
                if (isset($result['tableConfig']['extended'])) {
                    if (isset($result['tableConfig']['extended']['thead'])) {
                        foreach ($result['tableConfig']['extended']['thead'] as $headKey => $head) {
                            if ($headKey != 'react') {
                                unset($this->reportDB['results'][$key]['tableConfig']['extended']['thead'][$headKey]);
                            }
                        }
                    }
/* !!!!! Надо!!!!!                   if (isset($result['tableConfig']['extended']['subTableURL'])) {
                        $this->reportDB['results'][$key]['tableConfig']['extended']['subTableURL'] = $this->generateURL($result['tableConfig']['extended']['subTableURL']);
                    }*/
                }
                foreach ($result['tableConfig']['tableInit']['columns'] as $key => $col) {
                    if (isset($col['mfw_type'])) {
                        $types = explode(',', $col['mfw_type']);
                        foreach ($types as $type) {
                            $method = $this->getMethodName($type).'FldConfig';
                            if (method_exists($handler, $method)) {
                                $handler->$method($result['tableConfig']['tableInit']['columns'][$key]);
                                if ($handler->isError()) {
                                    $this->lastError = $handler->getLastError();
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->reportDB['results'];
        $results = [];
        foreach ($this->reportDB['results'] as $key => $res) {
            $res['selector'] = $this->reportDB['id'].'_'.$key;
            if (isset($res['tableConfig'])) {
                $tblTemplate = json_decode(
                    $this->twig->render(
                        '/report/result/table_template.html.twig',
                        [
                            'target' => $this->reportDB['target'],
                            'config' => $res['tableConfig']
                        ]
                    ),
                    true
                );
                $res['tableConfig']['report_id'] = $this->id;
                $res = array_replace_recursive($tblTemplate, $res);
                $tblRes = $this->tableResult($this->reportDB['handler'], $res);
                if ($tblRes === false) {
                    return false;
                }
                $results[] = $tblRes;
            }
            if (isset($res['pivotConfig'])) {
                $res['pivotConfig']['report_id'] = $this->id;
                $pivotRes = $this->pivotResult($this->reportDB['handler'], $res);
                $pivotRes['target'] = $this->reportDB['target'];
                if ($pivotRes === false) {
                    return false;
                }
                $results[] = $pivotRes;
            }
            if (isset($res['twigConfig'])) {
                $results[] = $res;
            }
        }
        return $results;
    }

    protected function readReport($id)
    {
        $reports = $this->myACP->getRepository('Reports');
        $report = $reports->reports(['id' => $id]);
        if ($this->isDBError()) {
            return false;
        }
        $res = null;
        if (isset($report[0])) {
            $res = json_decode($report[0]['CONFIG'], true);
            $handlerName = 'App\\ReportHandlers\\'.(isset($res['customHandler']) ? $res['customHandler'] : 'DefaultReportHandler');
            $res['handler'] = new $handlerName(
                [
                    'myACP' => $this->myACP,
                    'siteConfig' => $this->siteConfig,
                    'router' => $this->router,
                    'templating' => $this->twig,
                    'report' => $this,
                    'formBuilder' => $this->formBuilder,
                    'request' => $this->request
                ]
            );
            $res['mayBeFavorite'] = $report[0]['GLOBAL_SEARCH'] == 1;
//            $res['favorite'] = $report[0]['EMPLOYEE_ID'] != null;
        }
        return $res;
    }

    protected function tableResult($handler, $result)
    {
        dump('111111');
        foreach ($result['tableConfig']['tableInit']['columns'] as $key => $col) {
            if (isset($col['title'])) {
                $result['tableConfig']['tableInit']['columns'][$key]['title'] = $this->translator->trans($col['title']);
            }
            if (isset($col['mfw_type'])) {
                $types = explode(',', $col['mfw_type']);
                foreach ($types as $type) {
                    $method = $this->getMethodName($type).'FldConfig';
                    if (method_exists($handler, $method)) {
                        $handler->$method($result['tableConfig']['tableInit']['columns'][$key]);
                        if ($handler->isError()) {
                            $this->lastError = $handler->getLastError();
                            return false;
                        }
                    }
                }
            }
        }
        $layoutDB = $this->myACP->getRepository('Layouts');
        if ($this->isDBError()) {
            $this->lastError = $this->myACP->getLastError();
            return false;
        }
        if (isset($result['tableConfig']['tableInit']['buttons'])) {
            if (in_array('mfwLayouts', $result['tableConfig']['tableInit']['buttons'])) {
                $layouts = $layoutDB->getReportLayouts([
                    'report_id' => $result['tableConfig']['report_id'],
                    'layout_code' => 'tbl_'.$result['selector'],
                    'type' => 0,
                    'id' => -1
                ]);
                $default = '';
                foreach ($layouts as $key => $layout) {
                    $layouts[$key]['delete_url'] = $this->router->generate(
                        'gridLayoutDelete',
                        [
                            'layout_id' => $layout['ID'],
                            'layout_code' => 'tbl_'.$result['selector']
                        ]
                    );
                    if ($layout['DEF'] == 1) {
                        $default = $layout['ID'];
                    }
                }
                $result['tableConfig']['layouts'] = $layouts;
                $result['tableConfig']['tableInit']['stateSave'] = true;
                $result['tableConfig']['loadLayout'] = $default;
            }
            if (in_array('mfwPivot', $result['tableConfig']['tableInit']['buttons'])) {
                $layouts = $layoutDB->getReportLayouts([
                    'report_id' => $result['tableConfig']['report_id'],
                    'layout_code' => 'pivot_'.$result['selector'],
                    'type' => 1,
                    'id' => -1
                ]);
                $result['pivotLayouts'] = $layouts;
            }
            if (in_array('mfwChart', $result['tableConfig']['tableInit']['buttons'])) {
                $layouts = $layoutDB->getReportLayouts([
                    'report_id' => $result['tableConfig']['report_id'],
                    'layout_code' => 'chart_'.$result['selector'],
                    'type' => 2,
                    'id' => -1
                ]);
                $result['chartLayouts'] = $layouts;
            }
            if (in_array('mfwClcFields', $result['tableConfig']['tableInit']['buttons'])) {
                $clcFieldsDB = $this->myACP->getRepository('ClcFields');
                $clcFields = $clcFieldsDB->getClcFields([
                    'report_id' => $result['tableConfig']['report_id'],
                    'realTblSelector' => '#tbl_'.$result['selector']
                ]);
                if ($this->isDBError()) {
                    $this->lastError = $this->myACP->getLastError();
                    return false;
                }
                $len = count($result['tableConfig']['tableInit']['columns']);
                foreach ($clcFields as $field) {
                    $add = [
                        'title' => $field['NAME'],
                        'data' => 'CLC_'.$field['ID'],
                        'type' => $field['TYPE_FIELD'] == 0 ? 'mfw-int' : 'mfw-num'
                    ];
                    if ($field['SUM_GROUP'] == 1) {
                        $add['mfw_total_group'] = 'sum';
                    }
                    if ($field['SUM_ALL'] == 1) {
                        $add['mfw_total'] = 'sum';
                    }
                    $result['tableConfig']['tableInit']['columns'][] = $add;
                    $method = $this->getMethodName($add['type']).'FldConfig';
                    $handler->$method($result['tableConfig']['tableInit']['columns'][$len]);
                    if ($handler->isError()) {
                        $this->lastError = $handler->getLastError();
                        return false;
                    }
                    $len++;
                }
            }
            foreach ($result['tableConfig']['tableInit']['buttons'] as $key => $button) {
                if (is_array($button)) {
                    $this->parseButton($result['tableConfig']['tableInit']['buttons'][$key]);
                }
            }
        }
        if (isset($result['tableConfig']['extended'])) {
            if (isset($result['tableConfig']['extended']['thead'])) {
                foreach ($result['tableConfig']['extended']['thead'] as $key => $head) {
                    if ($key != 'react') {
                        unset($result['tableConfig']['extended']['thead'][$key]);
                    }
                }
            }
            if (isset($result['tableConfig']['extended']['subTableURL'])) {
                $result['tableConfig']['extended']['subTableURL'] = $this->generateURL($result['tableConfig']['extended']['subTableURL']);
            }
            if (isset($result['tableConfig']['extended']['autoRefresh'])) {
                if (isset($this->reportDB['query'])) {
                    $result['tableConfig']['extended']['autoRefresh'] = [
                        'item' => 'report'
                    ];
                } else {
                    $result['tableConfig']['extended']['autoRefresh'] = [
                        'url' => $this->router->generate(
                            'reportData',
                            [
                                'id' => $this->id,
                                'uniqid' => $this->uniqid,
                                'dataTable' => 1
                            ]
                        )
                    ];
                }
            }
        }
        return $result;
    }

    protected function pivotResult($handler, $result)
    {
        foreach ($result['pivotConfig']['columns'] as $key => $col) {
            if (isset($col['title'])) {
                $result['pivotConfig']['columns'][$key]['title'] = $this->translator->trans($col['title']);
            }
        }
        $layoutDB = $this->myACP->getRepository('Layouts');
        if ($this->isDBError()) {
            $this->lastError = $this->myACP->getLastError();
            return false;
        }
        $layouts = $layoutDB->getReportLayouts([
            'report_id' => $result['pivotConfig']['report_id'],
            'layout_code' => 'pivot_'.$result['selector'],
            'type' => 1,
            'id' => -1
        ]);
        $result['pivotLayouts'] = $layouts;
        return $result;
    }

    private function getDBResult($result, $fields = [])
    {
        $res = [];
        if (isset($result['query']['entity'])) {
            $entity = $this->myACP->getRepository($result['query']['entity']['name']);
            $method = $result['query']['entity']['method'];
            $res = $entity->$method($this->formData);
            if ($this->isDBError()) {
                $this->lastError = $this->myACP->getLastError();
                return false;
            }
            //dump($res);
            return $res;
        }
        if (isset($result['query']['sql'])) {
            $entity = $this->myACP->getRepository('Reports');
            $res = $entity->reportSQL(
                $result['query']['sql'],
                $this->formData,
                $fields
            );
            if ($this->isDBError()) {
                $this->lastError = $this->myACP->getLastError();
                return false;
            }
        }
        //dump($res);
        return $res;
    }

    private function tableFormatResult($result, &$res, $clcFields)
    {
        foreach ($res as $key => $row) {
            foreach ($result['tableConfig']['tableInit']['columns'] as $column) {
                if (isset($column['mfw_type'])) {
                    $types = explode(',', $column['mfw_type']);
                    foreach ($types as $type) {
                        $method = $this->getMethodName($type).'FldResult';
                        if (method_exists($this->reportDB['handler'], $method)) {
                            $this->reportDB['handler']->$method($res, $key, $column, $row);
                        } /*else {
                            $res[$key][$column['data']] = 'No such method '.$method;
                        }*/
                    }
                }
            }
            foreach ($clcFields as $field) {
                $clcRes = $field['rpn']->calc($res[$key]);
                if ($clcRes === false) {
                    $res[$key]['CLC_'.$field['ID']] = $field['rpn']->errorCode();
                } else {
                    $res[$key]['CLC_'.$field['ID']] = $clcRes;
                }
            }
        }
    }

    private function pivotFormatResult($result, &$res)
    {
        $resQ = [];
        $trans = [];
        foreach ($result['pivotConfig']['columns'] as $column) {
            $trans[$column['title']] = $this->translator->trans($column['title']);
        }
        foreach ($res as $key => $row) {
            $newRow = [];
            foreach ($result['pivotConfig']['columns'] as $column) {
                if (isset($column['mfw_type'])) {
                    $types = explode(',', $column['mfw_type']);
                    foreach ($types as $type) {
                        $method = $this->getMethodName($type).'FldResult';
                        if (method_exists($this->reportDB['handler'], $method)) {
                            $this->reportDB['handler']->$method($res, $key, $column, $row);
                        }
                    }
                }
                $newRow[$trans[$column['title']]] = $res[$key][$column['data']];
            }
            $resQ[] = $newRow;
        }
        return $resQ;
    }

    protected function isDBError($addCode = '', $text = false, $line = 0)
    {
        $error = $this->myACP->getError($addCode, $text, $line);
        if ($error == '') {
            return false;
        }
        $this->lastError = $error;
/*        $transError = $this->translator->trans($error);
        if ($transError == $error) {
            $this->lastError = $this->myACP->getLastError();
        } else {
            $this->lastError = $this->translator->trans($error);
        }*/
        return true;
    }

    public function getFormData()
    {
        return $this->formData;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function get($option)
    {
        switch ($option) {
            case 'reportID':
                return $this->reportID;
            case 'uniqID':
                return $this->uniqid;
            case 'id':
                return $this->id;
            case 'reportDB':
                return $this->reportDB;
        }
    }

    private function checkFormQuery($form, $query)
    {
        foreach ($form->all() as $key => $val) {
            if (isset($query[$key])) {
                return true;
                break;
            }
            if ($this->checkFormQuery($val, $query)) {
                return true;
                break;
            }
        }
        return false;
    }

    public function info()
    {
        return [
            'dbId' => $this->id,
            'uniqid' => $this->uniqid,
            'selector' => $this->reportID
        ];
    }

    public function setFormData($data)
    {
        $this->formData = array_merge($this->formData, $data);
    }

    protected function generateURL($data)
    {
        $params = [];
        if (isset($data['url'])) {
            $params = [];
            foreach ($data['params'] as $name => $value) {
                $val = explode(':', $value);
                switch ($val[0]) {
                    case 'val':
                        return $val[1];
                    case 'reportID':
                        return $this->reportID;
                    case 'reportUniqID':
                        return $this->uniqid;
                    case 'reportBaseID':
                        return $this->id;
                }
                $params[$name] = $val;
            }
            return $this->router->generate($data['url'], $params);
        }
        return $this->router->generate($data);
    }

    protected function parseButton(&$button)
    {
        switch ($button['extend']) {
            case 'mfwUrl':
                $params = isset($button['urlParams']) ? $button['urlParams'] : [];
                $params['report_id'] = $this->reportID;
                $button['url'] = $this->router->generate(
                    $button['url'],
                    $params
                );
                $button['text'] = $this->translator->trans($button['text']);
                if (isset($button['confirm'])) {
                    $button['confirm'] = $this->translator->trans($button['confirm']);
                }
                if (isset($button['addForm']) && $button['addForm'] == 'reportQuery') {
                    $button['addForm'] = '#rep_'.$this->reportID.' .mfw-qry-form';
                }
                break;
            case 'mfwJSButton':
                $button['text'] = $this->translator->trans($button['text']);
                break;
            case 'mfwDropDownButton':
                $button['text'] = $this->translator->trans($button['text']);
                foreach ($button['buttons'] as $iKey => $dropButton) {
                    $this->parseButton($button['buttons'][$iKey]);
                }
                break;
        }
    }
}
