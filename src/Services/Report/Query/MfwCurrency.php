<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwChoice;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class MfwCurrency extends MfwChoice
{
    public function queryField($form, $element, $name)
    {
        if (isset($element['options'])) {
            $element['options']['choice_translation_domain'] = false;
            $element['options']['label'] = isset($element['options']['label']) ? $element['options']['label'] : 'currency._currency';
        } else {
            $element['options'] = [
                'choice_translation_domain' => false,
                'label' => 'currency._currency'
            ];
        }
        MfwChoice::queryField($form, $element, $name);
    }

    protected function getChoices($element)
    {
        $choices = [];
        if (isset($element['all'])) {
            $choices['currency.all'] = $element['all'];
        }
        $location = $this->myACP->getRepository('Currency');
        $locations = $location->listCurrency(isset($element['woPOS']) ? $element['woPOS'] : false);
        foreach ($locations as $loc) {
            $choices[$loc['CURRENCY_ABBR']] = $loc['CURRENCY_CODE'];
        }
        return $choices;
    }

    public function inputField($form, $element, $name)
    {
        $form->add(
            $name,
            IntegerType::class,
            [

            ]
        );
    }
}
