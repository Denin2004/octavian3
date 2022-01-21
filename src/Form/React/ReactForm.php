<?php
namespace App\Form\React;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class ReactForm extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('request')->setDefault('request', false);
    }

    public function getParent()
    {
        return FormType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['react'] = [];
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($view->parent != null) {
            $view->parent->vars['react'][$view->vars['name']] = $view->vars['react'];
        } else {
            foreach ($view->children as $key => $child) {
                if ($key == '_token') {
                    $view->vars['react'][$child->vars['name']] = [
                        'value' => $child->vars['value'],
                        'type' => 'mfw-hidden',
                        'full_name' => $child->vars['full_name']
                    ];
                    break;
                }
            }
        }
    }
}
