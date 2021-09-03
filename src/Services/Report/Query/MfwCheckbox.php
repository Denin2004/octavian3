<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwQuery;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\CallbackTransformer;

class MfwCheckbox extends MfwQuery
{
    public $variables;

    public function queryField($form, $element, $name)
    {
        $form->add($name, CheckboxType::class, isset($element['options']) ? $element['options'] : []);
    }

    public function inputField($form, $element, $name)
    {
        $form->add($name, CheckboxType::class);
        if (isset($element['values'])) {
            $this->variables = $element['values'];
            $form->get($name)
                ->addModelTransformer(new CallbackTransformer(
                    function ($value) {
                        return $value;
                    },
                    function ($value) {
                        return $value === true ? $this->variables['checked'] : $this->variables['unchecked'];
                    }
                ));
        }
    }
}
