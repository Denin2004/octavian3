<?php
namespace App\Form\React;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ReactPasswordType extends AbstractType
{
    public function getParent()
    {
        return PasswordType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->parent->vars['react'][$view->vars['name']] = [
            'type' => 'mfw-password',
            'value' => $view->vars['value'],
            'full_name' => $view->vars['full_name']
        ];
    }
}
