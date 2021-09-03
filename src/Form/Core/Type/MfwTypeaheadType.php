<?php
namespace App\Form\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class MfwTypeaheadType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'src_url' => '',
                'float_label' => false,
                'form_submit' => false,
                'on_setvalue' => '',
                'json_config' => '',
                'min_length' => 3,
                'typeahead_attr' => [],
                'addon' => false,
                'form_text' => ''
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['src_url'] = $options['src_url'];
        $view->vars['float_label'] = $options['float_label'];
        $view->vars['form_submit'] = $options['form_submit'];
        $view->vars['on_setvalue'] = $options['on_setvalue'];
        $view->vars['min_length'] = $options['min_length'];
        $view->vars['json_config'] = $options['json_config'];
        $view->vars['typeahead_attr'] = $options['typeahead_attr'];
        $view->vars['addon'] = $options['addon'];
        $view->vars['form_text'] = $options['form_text'];
        $allData = $form->getParent()->getData();
        $name = $form->getName();
        if (isset($allData[$name.'_text'])) {
            $view->vars['value']['val'] = $allData[$name];
            $view->vars['value']['text'] = $allData[$name.'_text'];
        }
        if (!isset($allData[$name])) {
            $view->vars['value'] = [];
            $view->vars['value']['val'] = '';
            $view->vars['value']['text'] = '';
        }
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
