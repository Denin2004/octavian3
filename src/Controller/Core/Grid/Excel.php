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
    public function index(Request $request, Factory $excelService, SiteConfig $config)
    {
        return $this->render(
            'base.web.html.twig',
            [
                'numeral' => $config->get('numeral')
            ]
        );

        if ($request->request->get('excelData') == null) {
            return $this->render(
                'base.web.html.twig',
                [
                    'numeral' => $config->get('numeral')
                ]
            );
        }
        $data = json_decode($request->request->get('excelData'), true);
        dump($data);
        if ($data == null) {
            return $this->render(
                'base.web.html.twig',
                [
                    'numeral' => $config->get('numeral')
                ]
            );
        }
//        dump($request->request->get('excelData'), json_decode('{aaaa: gggg}'));
/*        $form = $this->createForm(GridExel::class);
        $form->handleRequest($request);
        if (!$form->isValid()) {
            $errors = $this->textFormErrors($form);
            return $this->getTemplate(
                $request,
                [
                    'errors' => $errors,
                    'module_template_file' => '',
                    'title' => $this->translator->trans('errors._error'),
                ]
            );
        }
        $query = $form->getData();
        $data = json_decode($query['data']);*/
        $objExcel = $excelService->createSpreadsheet();
/*        $startRow = 1;
        if (isset($data->addHeader)) {
            $tmpfile = tempnam(sys_get_temp_dir(), 'html');
            file_put_contents($tmpfile, '<?xml version="1.0" encoding="utf-8"?>'.$data->addHeader);
            $excelHTMLReader = new Html('HTML');
            $objExcel = $excelHTMLReader->load($tmpfile);
            unlink($tmpfile);
            $objExcel->setActiveSheetIndex(0);
            $aSheet = $objExcel->getActiveSheet();
            for ($line = 0; $line < $data->addHeaderLines; $line++) {
                for ($col = 0; $col < count($data->header); $col++) {
                    $colIndex = Coordinate::stringFromColumnIndex($col+1);
                    $val = $aSheet->getCell($colIndex.($line))->getValue();
                    if ($val != null) {
                        $aSheet->getStyle($colIndex.($line))->applyFromArray(
                            [
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => [
                                            'rgb' => '100000'
                                        ]
                                    ]
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                    'wrapText' => true
                                ],
                                'font' => [
                                    'bold' => true
                                ]
                            ]
                        );
                    } else {
                        // Bug Spreadsheet !!!!
                        $aSheet->getStyle($colIndex.($line))->applyFromArray(
                            [
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => Border::BORDER_THIN,
                                        'color' => [
                                            'rgb' => '100000'
                                        ]
                                    ]
                                ],
                                'alignment' => [
                                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                                    'vertical' => Alignment::VERTICAL_CENTER,
                                    'wrapText' => true
                                ],
                                'font' => [
                                    'bold' => true
                                ]
                            ]
                        );
                    }
                }
            }

            //$aSheet->insertNewRowBefore(1);
            $rowNum = $data->addHeaderLines;
        } else {
            $objExcel->setActiveSheetIndex(0);
            $aSheet = $objExcel->getActiveSheet();
            $rowNum = 1;
        }
        $startRow = $rowNum + 1;
        $col = 0;
        $aSheet->mergeCells('A1:'.Coordinate::stringFromColumnIndex(count($data->header) - 1).'1');
        $aSheet->setCellValueExplicit('A1', htmlspecialchars_decode($data->title, ENT_QUOTES), DataType::TYPE_STRING);
        $aSheet->getStyle('A1')->applyFromArray(
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_NONE,
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'font' => [
                    'bold' => true,
                ],
            ]
        );
        foreach ($data->header as $key => $header) {
            $colIndex = Coordinate::stringFromColumnIndex($col + 1);
            if (isset($header->title)) {
                $aSheet->setCellValueExplicit($colIndex.$rowNum, html_entity_decode($header->title, ENT_QUOTES), DataType::TYPE_STRING);
            }
            $aSheet->getColumnDimension($colIndex)->setAutoSize(true);
            if (isset($header->mfw_total_group)) {
                $data->header[$key]->total_group = [
                    'oper' => $header->mfw_total_group,
                    'total' => 0,
                    'cnt' => 0,
                ];
            } else {
                $data->header[$key]->total_group = [
                    'oper' => '',
                ];
            }
            if (isset($header->mfw_total)) {
                $data->header[$key]->total = [
                    'oper' => $header->mfw_total,
                    'total' => 0,
                    'cnt' => 0,
                ];
            } else {
                $data->header[$key]->total = [
                    'oper' => '',
                ];
            }
            $data->header[$key]->xls_format = [
                'formatCode' => ''];
            $data->header[$key]->alignment = Alignment::HORIZONTAL_LEFT;
            if (isset($header->mfw_format)) {
                if ($header->mfw_format == $this->siteConfig->get('js_number_format')) {
                    $data->header[$key]->format = 'number';
                    $data->header[$key]->xls_format['formatCode'] = $this->siteConfig->get('xls_number_format');
                }
                if ($header->mfw_format == $this->siteConfig->get('js_int_format')) {
                    if (($data->header[$key]->mfw_type == 'mfw-checkbox')||(isset($data->header[$key]->checked))) {
                        $data->header[$key]->mfw_type = 'mfw-checkbox';
                        $data->header[$key]->format = 'checkbox';
                        $data->header[$key]->xls_format['formatCode'] = NumberFormat::FORMAT_TEXT;
                    } else {
                        $data->header[$key]->format = 'int';
                        $data->header[$key]->xls_format['formatCode'] = $this->siteConfig->get('xls_int_format');
                    }
                }
            } else {
                if (isset($header->mfw_type) && ($header->mfw_type == 'mfw-duration')) {
                    $data->header[$key]->format = 'duration';
                    $data->header[$key]->xls_format['formatCode'] = '';
                } else {
                    $data->header[$key]->format = '';
                    $data->header[$key]->xls_format['formatCode'] = NumberFormat::FORMAT_TEXT;
                }
            }
            if (isset($header->className)) {
                if (strpos($header->className, 'dt-body-right') !== false) {
                    $data->header[$key]->alignment = Alignment::HORIZONTAL_RIGHT;
                }
                if (strpos($header->className, 'dt-body-center') !== false) {
                    $data->header[$key]->alignment = Alignment::HORIZONTAL_CENTER;
                }
            }
            ++$col;
        }
        $lastCol = Coordinate::stringFromColumnIndex($col);
        $aSheet->getStyle('A'.$rowNum.':'.$lastCol.$rowNum)->applyFromArray(
            [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => [
                            'rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'font' => [
                    'bold' => true,
                ]
            ]
        );
        $aSheet->freezePane('A'.($rowNum + 1));

        $aSheet->getRowDimension($rowNum)->setRowHeight(30);
        ++$rowNum;
        $useGroup = $data->groupCol != '';
        $groupValue = null;
        if ($useGroup) {
            $groupName = $data->groupCol;
        }
        foreach ($data->body as $row) {
            if (($useGroup === true) and (($row->$groupName != $groupValue)or($groupValue === null))) {
                if ($rowNum != $startRow) {
                    $this->printFooter($aSheet, $rowNum, $data->header);
                    ++$rowNum;
                }
                $groupValue = $row->$groupName;
                $aSheet->mergeCells('A'.$rowNum.':'.$lastCol.$rowNum);
                $aSheet->setCellValueExplicit('A'.$rowNum, $groupValue, DataType::TYPE_STRING);
                $aSheet->getStyle('A'.$rowNum)->applyFromArray(
                    [
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_LEFT,
                            'wrapText' => true,
                        ],
                        'font' => [
                            'bold' => true,
                        ],
                    ]
                );
                $aSheet->getRowDimension($rowNum)->setRowHeight(40);
                ++$rowNum;
            }
            $col = 0;

            foreach ($data->header as $key => $header) {
                $colIndex = Coordinate::stringFromColumnIndex($col + 1);
                $name = $header->name;
                switch ($header->format) {
                    case 'checkbox':
                        $aSheet->setCellValueExplicit(
                            $colIndex.$rowNum,
                            property_exists($data->header[$key], 'checked_text') ? ($this->translator->trans($data->header[$key]->checked_text) == $row->$name ? 'V' : '') : $row->$name,
                            DataType::TYPE_STRING
                        );
                        break;
                    case '':
                    case 'duration':
                        $aSheet->setCellValueExplicit($colIndex.$rowNum, $this->unformat($header, $row->$name), DataType::TYPE_STRING);
                        break;
                    case 'number':
                    case 'int':
                        $aSheet->setCellValueExplicit($colIndex.$rowNum, $this->unformat($header, $row->$name), DataType::TYPE_NUMERIC);
                        break;
                }
//                $aSheet->setCellValue   ($colIndex.$rowNum, $this->unformat($header, $row->$name));
                $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                    [
                        'alignment' => [
                            'horizontal' => $header->alignment,
                            'wrapText' => true,
                        ],
                        'numberFormat' => $header->xls_format
                    ]
                );
                switch ($header->total_group['oper']) {
                    case 'sum-duration':
                        $header->total_group['total'] = $header->total_group['total'] +
                            $this->durationUnformat($row->$name);
                        break;
                    case 'sum':
                        $header->total_group['total'] = $header->total_group['total'] +
                            $this->unformat($header, $row->$name);
                        break;
                    case 'count':
                        $header->total_group['cnt'] ++;
                        break;
                    case 'avg':
                        $header->total_group['total'] = $header->total_group['total'] +
                            $this->unformat($header, $row->$name);
                        ++$header->total_group['cnt'];
                        break;
                }
                switch ($header->total['oper']) {
                    case 'sum-duration':
                        $header->total['total'] = $header->total['total'] +
                            $this->durationUnformat($row->$name);
                        break;
                    case 'sum':
                        $header->total['total'] = $header->total['total'] +
                            $this->unformat($header, $row->$name);
                        break;
                    case 'count':
                        $header->total['cnt'] ++;
                        break;
                    case 'avg':
                        $header->total['total'] = $header->total['total'] +
                            $this->unformat($header, $row->$name);
                        ++$header->total['cnt'];
                        break;
                }
                ++$col;
            }
            $aSheet->getStyle('A'.$rowNum.':'.$lastCol.$rowNum)->applyFromArray(
                [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => '000000'
                            ],
                        ],
                    ],
                ]
            );
            ++$rowNum;
        }
        if ($useGroup === true) {
            $this->printFooter($aSheet, $rowNum, $data->header);
            ++$rowNum;
        }

        $col = 0;
        foreach ($data->header as $header) {
            $colIndex = Coordinate::stringFromColumnIndex($col + 1);
            switch ($header->total['oper']) {
                case 'sum':
                    $aSheet->setCellValueExplicit($colIndex.$rowNum, $header->total['total'], DataType::TYPE_NUMERIC);
                    $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => $header->alignment,
                                'wrapText' => true,
                            ],
                            'font' => [
                                'bold' => true,
                            ],
                            'numberFormat' => $header->xls_format,
                        ]
                    );
                    break;
                case 'sum-duration':
                    $aSheet->setCellValueExplicit($colIndex.$rowNum, $this->secsToDuration($header->total['total']), DataType::TYPE_STRING);
                    $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => $header->alignment,
                                'wrapText' => true,
                            ],
                            'font' => [
                                'bold' => true,
                            ],
                        ]
                    );
                    break;
                case 'count':
                    $aSheet->setCellValueExplicit($colIndex.$rowNum, $header->total['cnt'], DataType::TYPE_NUMERIC);
                    $header->total['cnt'] = 0;
                    $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => $header->alignment,
                                'wrapText' => true,
                            ],
                            'font' => [
                                'bold' => true,
                            ],
                            'numberFormat' => [
                                'formatCode' => $this->siteConfig->get('xls_int_format')
                            ],
                        ]
                    );
                    break;
                case 'avg':
                    if ($header->total['cnt'] != 0) {
                        $aSheet->setCellValueExplicit($colIndex.$rowNum, $header->total['total'] / $header->total['cnt'], DataType::TYPE_NUMERIC);
                        $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                            [
                                'alignment' => [
                                    'horizontal' => $header->alignment,
                                    'wrapText' => true,
                                ],
                                'font' => [
                                    'bold' => true,
                                ],
                                'numberFormat' => $header->xls_format,
                            ]
                        );
                    }
                    break;
            }
            $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                [
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'FFFFFF']
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'FFFFFF']
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'FFFFFF']
                        ]
                    ]
                ]
            );
            ++$col;
        }*/
        $aSheet->setTitle('Report');
        $file = explode(',', $data->title);
        $writer = $excelService->createWriter($objExcel, 'Xls');
        $response = $excelService->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, implode('_', $file).'.xls');
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function printFooter($aSheet, $rowNum, $headers)
    {
        $col = 0;
        foreach ($headers as $header) {
            $colIndex = Coordinate::stringFromColumnIndex($col + 1);
            switch ($header->total_group['oper']) {
                case 'sum-duration':
                    $aSheet->setCellValueExplicit($colIndex.$rowNum, $this->secsToDuration($header->total_group['total']), DataType::TYPE_STRING);
                    $header->total_group['total'] = 0;
                    $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => $header->alignment,
                                'vertical' => Alignment::VERTICAL_TOP,
                                'wrapText' => true,
                            ],
                            'font' => [
                                'bold' => true,
                            ],
                        ]
                    );
                    break;
                case 'sum':
                    $aSheet->setCellValueExplicit($colIndex.$rowNum, $header->total_group['total'], DataType::TYPE_NUMERIC);
                    $header->total_group['total'] = 0;
                    $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => $header->alignment,
                                'vertical' => Alignment::VERTICAL_TOP,
                                'wrapText' => true,
                            ],
                            'font' => [
                                'bold' => true,
                            ],
                            'numberFormat' => $header->xls_format,
                        ]
                    );
                    break;
                case 'count':
                    $aSheet->setCellValueExplicit($colIndex.$rowNum, $header->total_group['cnt'], DataType::TYPE_NUMERIC);
                    $header->total_group['cnt'] = 0;
                    $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                        [
                            'alignment' => [
                                'horizontal' => $header->alignment,
                                'vertical' => Alignment::VERTICAL_TOP,
                                'wrapText' => true,
                            ],
                            'font' => [
                                'bold' => true,
                            ],
                            'numberFormat' => [
                                'formatCode' => $this->siteConfig->get('xls_int_format')],
                        ]
                    );
                    break;
                case 'avg':
                    if ($header->total_group['cnt'] != 0) {
                        $aSheet->setCellValueExplicit(
                            $colIndex.$rowNum,
                            $header->total_group['sum'] / $header->total_group['cnt'],
                            DataType::TYPE_STRING
                        );
                        $header->total_group['cnt'] = 0;
                        $header->total_group['sum'] = 0;
                        $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                            [
                                'alignment' => [
                                    'horizontal' => $header->alignment,
                                    'vertical' => Alignment::VERTICAL_TOP,
                                    'wrapText' => true,
                                ],
                                'font' => [
                                    'bold' => true,
                                ],
                                'numberFormat' => $header->xls_format,
                            ]
                        );
                    }
                    break;
            }
            $aSheet->getStyle($colIndex.$rowNum)->applyFromArray(
                [
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'FFFFFF',],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'FFFFFF',],
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'rgb' => 'FFFFFF',],
                        ],
                    ],
                ]
            );
            ++$col;
        }
        $aSheet->getRowDimension($rowNum)->setRowHeight(30);
    }

    protected function formatValue($header, $value)
    {
        switch ($header->format) {
            case 'number':
                return $this->getNumberFormat($value);
            case 'int':
                return $this->getIntegerFormat($value);
        }
        return $value;
    }

    protected function unformat($header, $value)
    {
        switch ($header->format) {
            case 'number':
                return $this->numberUnformat($value);
            case 'int':
                return $this->integerUnformat($value);
        }
        if ($header->xls_format['formatCode'] == NumberFormat::FORMAT_TEXT) {
            return $value;
        }
        return $value;
    }

    protected function numberUnformat($number)
    {
        $number = preg_replace('/^[^\d]-+/', '', $number);
        $number = str_replace([
            $this->siteConfig->get('php_thousand_sep'),
            $this->siteConfig->get('php_dec_point')], ['', '.'], $number);
        settype($number, 'float');
        return $number;
    }

    protected function integerUnformat($number)
    {
        $number = preg_replace('/^[^\d]-+/', '', $number);
        $number = str_replace([$this->siteConfig->get('php_thousand_sep')], [''], $number);
        settype($number, 'int');
        return $number;
    }

    protected function durationUnformat($duration)
    {
        $duration = preg_replace('/^[^\d]-+/', '', $duration);
        $duration = $this->timeToInt($duration);
        settype($duration, 'int');
        return $duration;
    }
}
