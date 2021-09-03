<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwQuery;
use Symfony\Component\Form\Extension\Core\Type\PercentType;

class MfwPercent extends MfwQuery
{
    public function queryField($form, $element, $name)
    {
        $form->add($name, PercentType::class, isset($element['options']) ? $element['options'] : []);
    }

    public function inputField($form, $element, $name)
    {
        $form->add($name, PercentType::class);
    }
}
