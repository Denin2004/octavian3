<?php
namespace App\Form\Report;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use App\Form\React\ReactRangeType;

class Period extends ReactRangeType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->parent->vars['react'][$view->vars['name']]['options'] = $options['attr']['field'];
    }
}
