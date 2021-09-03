<?php
namespace App\Services\Report\Query;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\CallbackTransformer;

use App\Services\Report\Query\MfwQuery;
use App\Services\Report\Report;

class MfwReportInfo extends MfwQuery
{

    protected $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function queryField($form, $element, $name)
    {
        $form->add($name, HiddenType::class);
        $form->get($name)->addModelTransformer(
            new CallbackTransformer(
                function ($value) {
                    return json_encode($this->report->info());
                },
                function ($value) {
                    return $value;
                }
            )
        );
    }

    public function inputField($form, $element, $name)
    {
        $form->add($name, HiddenType::class);
        $form->get($name)->addModelTransformer(
            new CallbackTransformer(
                function ($value) {
                    return $value;
                },
                function ($value) {
                    return json_decode($value, true);
                }
            )
        );
    }
}
