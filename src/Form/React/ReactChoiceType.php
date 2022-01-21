<?php
namespace App\Form\React;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;

class ReactChoiceType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['choices']) == 0) {
            $builder->setAttribute('mfw_options', $this->getChoices($options));
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $choices = $form->getConfig()->getOption('choices');
        if (count($choices) == 0) {
            $choices = $form->getConfig()->getAttribute('mfw_options');
        }
        $reactChoices = [];
        foreach ($choices as $label => $value) {
            $reactChoices[] = [
                'label' => $label,
                'value' => $value
            ];
        }
        $view->parent->vars['react'][$view->vars['name']] = [
            'type' => 'mfw-choice',
            'choices' => $reactChoices,
            'value' => $view->vars['value'],
            'multiple' => isset($options['multiple']) ? $options['multiple'] : false,
            'full_name' => $view->vars['full_name'],
            'label' => $options['label']
        ];
    }

    protected function getChoices($options)
    {
        return [];
    }
}
