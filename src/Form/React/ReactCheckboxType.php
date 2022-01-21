<?php
namespace App\Form\React;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ReactCheckboxType extends AbstractType
{
    public function getParent()
    {
        return CheckboxType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $value = $form->getData();
        $view->parent->vars['react'][$view->vars['name']] = [
            'type' => 'mfw-checkbox',
            'value' => $value == null ? false : $value,
            'full_name' => $view->vars['full_name']
        ];
    }
}
