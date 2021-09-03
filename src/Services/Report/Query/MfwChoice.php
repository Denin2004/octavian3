<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use App\Services\MyACP\MyACP;

class MfwChoice extends MfwQuery
{
    protected $myACP;

    public function __construct(MyACP $myACP)
    {
        $this->myACP = $myACP;
    }

    public function queryField($form, $element, $name)
    {
        $element['options'] = isset($element['options']) ? $element['options'] : [];
        $form->add(
            $name,
            ChoiceType::class,
            array_replace_recursive(
                [
                    'choices' => $this->getChoices($element),
                    'expanded' => false,
                    'multiple' => false,
                    'label_attr' => [
                        'class' => 'mfw-select-label'
                    ],
                    'attr' => [
                        'class' => 'mfw',
                        'data-mfw_type' => 'select2',
                        'data-mfw_config' => '{"width": "auto", "dropdownAutoWidth": true, "minimumResultsForSearch": "Infinity"}'
                    ]
                ],
                $element['options']
            )
        );
    }

    public function inputField($form, $element, $name)
    {
        if (isset($element['intInput'])) {
            $form->add($name, IntegerType::class);
        } else {
            $form->add($name, ChoiceType::class, ['choices' => $this->getChoices($element)]);
        }
    }

    protected function getChoices($element)
    {
        $choices = [];
        if (isset($element['data'])) {
            if (isset($element['data']['choices'])) {
                foreach ($element['data']['choices'] as $key => $val) {
                    $choices[$key] = $val;
                }
            }
            if (isset($element['data']['entity'])) {
                $entity = $this->myACP->getRepository($element['data']['entity']['name']);
                $method = $element['data']['entity']['method'];
                $res = $entity->$method();
                foreach ($res as $row) {
                    $choices[$row[$element['data']['entity']['option']]] = $row[$element['data']['entity']['value']];
                }
            }
        }
        return $choices;
    }
}
