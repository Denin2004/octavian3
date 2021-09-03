<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwQuery;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MfwText extends MfwQuery
{
    public function queryField($form, $element, $name)
    {
        $formData = $form->getData();
        $options = isset($element['options']) ? $element['options'] : [];
        if (isset($element['autosize'])&& isset($formData[$name])) {
            $options = array_merge(
                $options,
                [
                    'attr' => [
                        'size' => strlen($formData[$name])
                    ]
                ]
            );
        }
        $form->add($name, TextType::class, $options);
    }

    public function inputField($form, $element, $name)
    {
        $form->add($name, TextType::class);
    }
}
