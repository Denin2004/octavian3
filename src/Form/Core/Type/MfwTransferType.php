<?php
namespace App\Form\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class MfwTransferType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'transferValues' => [],
                'allParent' => false
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['transferValues'] = $options['transferValues'];
        $view->vars['allParent'] = $options['allParent'];
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
