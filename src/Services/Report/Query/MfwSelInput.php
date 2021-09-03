<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwQuery;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class MfwSelInput extends MfwQuery
{
    private $customValue = '';
    private $name = '';

    public function queryField($form, $element, $name)
    {
        $form->add(
            $name.'_sel',
            ChoiceType::class,
            array_replace_recursive(
                [
                    'choices' => $element['choices'],
                    'expanded' => false,
                    'multiple' => false,
                    'label' => false,
                    'attr' => [
                        'class' => 'mfw',
                        'data-mfw_type' => 'select2,mfwSelInput',
                        'data-mfw_input' => '.mfw-sel-input-'.$name,
                        'data-mfw_view_value' => 0
                    ]
                ],
                isset($element['options']) ? $element['options'] : []
            )
        )->add(
            $name.'_input',
            TextType::class,
            array_replace_recursive(
                [
                    'label' => 'common.value._value',
                    'data' => 0,
                    'attr' => [
                        'class' => 'mfw-sel-input-'.$name.' mfw-input-number d-none'
                    ],
                    'label_attr' => [
                        'class' => 'mfw-sel-input-'.$name.' d-none'
                    ]
                ],
                isset($element['input_options']) ? $element['input_options'] : []
            )
        );
    }

    public function inputField($form, $element, $name)
    {
        $this->customValue = isset($element['customValue']) ? $element['customValue'] : '';
        if (isset($element['intInput'])) {
            $form->add($name.'_sel', IntegerType::class)
                ->add(
                    $name.'_input',
                    IntegerType::class
                );
        } else {
            $form->add($name, ChoiceType::class, ['choices' => $element['choices']])
                ->add(
                    $name.'_input',
                    TextType::class
                );
        }
        if (isset($element['customValue'])) {
            $this->name = $name;
            $form->addEventListener(
                FormEvents::PRE_SUBMIT,
                [$this, 'checkSelected']
            );
        }
    }

    public function checkSelected(FormEvent $event)
    {
        $data = $event->getData();
        $event->getForm()->add(
            $this->name,
            HiddenType::class,
            [
                'empty_data'=> $data[$this->name.'_sel'] == $this->customValue ? $data[$this->name.'_input'] : $data[$this->name.'_sel']
            ]
        );
    }
}
