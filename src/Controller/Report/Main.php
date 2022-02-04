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
