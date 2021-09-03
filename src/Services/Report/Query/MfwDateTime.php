<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwDate;
use App\Form\Core\Type\MfwDateTimeType;

class MfwDateTime extends MfwDate
{
    public function queryField($form, $element, $name)
    {
        $element['options'] = isset($element['options']) ? $element['options'] : [];
        $data = isset($form->getData()[$name]) ? $this->getDateFromString(urldecode($form->getData()[$name])) : false;
        $element['options']['data'] = $data ? $data->format($this->siteConfig->get('php_date_format').' '.$this->siteConfig->get('php_time_format')) : $this->getDefaultDate($element, $this->siteConfig->get('php_date_format'));
        $form->add($name, MfwDateTimeType::class, $element['options']);
    }

    public function inputField($form, $element, $name)
    {
        $form->add($name, MfwDateTimeType::class, $element['options']);
    }
}
