<?php
namespace App\Controller\Report;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use App\Controller\Common;
use App\Services\Report\Report as ReportService;

class Main extends Common
{
    protected $formData = [];
    protected $lastError = '';

    public function metaData(Request $request, ReportService $repService, $id)
    {
        $report = $repService->getReport($request, $id);
        if ($id == 97) {
            $report['results'][0]['tableConfig']['extended']['thead']['react'] = json_decode('[
                        {
                            "title": "currency._currency",
                            "data": "CURRENCY_ABBR",
                            "visible": false
                        },
                        {
                            "title": "jackpot._jackpot",
                            "data": "PROGRESSIVE_NAME",
                            "mfw_type": "mfw-link",
                            "action": {
                                "route": "reportPage",
                                "params": {
                                    "id": "val:56",
                                    "jp_id": "row:PROGRESSIVE_CODE",
                                    "jackpot_name": "row:PROGRESSIVE_NAME",
                                    "target": "val:modal"
                                },
                                "ajax": true,
                                "attrs": {
                                    "data-mfw_method": "get"
                                }
                            }
                        },
                        {
                            "title": "common.type",
                            "data": "PROGRESSIVE_TYPE_NAME"
                        },
                        {
                            "title": "jackpot.current_amount",
                            "data": "CURRENT_JACKPOT_METER",
                            "mfw_type": "mfw-num"
                        },
                        {
                            "title": "jackpot.contribution_min",
                            "data": "DELTA",
                            "mfw_type": "mfw-num"
                        },
                        {
                            "title": "date._date",
                            "children": [
                                {
                                    "title": "date._date",
                                    "data": "LAST_PAID_DATE",
                                    "mfw_type": "mfw-date-time"
                                },
                                {
                                    "title": "findata.amount._amount",
                                    "data": "LAST_PAID_AMOUNT",
                                    "mfw_type": "mfw-num"
                                }
                            ]
                        }
                    ]');
        }
        dump($report);
        if ($repService->isError()) {
            $reportError = $repService->getLastError();
            if ($reportError == '403') {
                return new JsonResponse([
                    'success' => false,
                    'error' => '403'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'error' => $reportError
                ]);
            }
        }
        return new JsonResponse([
            'success' => true,
            'report' => $report
        ]);
    }

    public function data(Request $request, ReportService $repService, $id)
    {
        $res = $repService->getResult($request, $id);
        if ($repService->isError()) {
            $reportError = $repService->getLastError();
            $errors = explode('Code400', $reportError);
            if (count($errors) > 1) {
                return new JsonResponse([
                    'success' => false,
                    'error' => implode(' ', $errors)
                ]);
            }
            if ($reportError == '403') {
                return new JsonResponse([
                    'success' => false,
                    'error' => $reportError
                ], 403);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'error' => $reportError
                ]);
            }
        }
        return new JsonResponse([
            'success' => true,
            'result' => $res
        ]);
    }
}
