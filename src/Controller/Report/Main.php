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
        dump($report);
        return new JsonResponse([
            'success' => true,
            'report' => $report
        ]);
    }

    public function data(Request $request, ReportService $repService, $id, $uniqid, $datatable)
    {
        $res = $repService->getResult($request, $this, $id, $datatable, $uniqid);
        if ($repService->isError()) {
            $reportError = $repService->getLastError();
            $errors = explode('Code400', $reportError);
            if (count($errors) > 1) {
                $this->addResponse([
                    'showToast' => [
                        'text' => implode(' ', $errors),
                        'type' => 'error'
                    ]
                ]);
                return $this->getResponse(400);
            }
            $this->addResponse([
                'showToast' => [
                    'text' => $reportError,
                    'type' => 'error'
                ]
            ]);
            if ($reportError == '403') {
                return $this->getResponse(403);
            } else {
                return $this->getResponse(500);
            }
        }
        if ($datatable == 1) {
            return new JsonResponse(['data' => $res]);
        }
        return $this->getResponse();
    }
}
