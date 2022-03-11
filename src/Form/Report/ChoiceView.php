<?php
namespace App\Form\Report;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use App\Form\React\ReactChoiceType;
use App\Services\MyACP\MyACP;

class ChoiceView extends ReactChoiceType
{
    private $myACP;

    public function __construct(MyACP $myACP)
    {
        $this->myACP = $myACP;
    }

    protected function getChoices($options)
    {
        dump($options);
        $choices = [];
        if (isset($options['attr']['field']['data'])) {
            if (isset($options['attr']['field']['data']['choices'])) {
                foreach ($options['attr']['field']['data']['choices'] as $key => $val) {
                    $choices[$key] = $val;
                }
            }
            if (isset($options['attr']['field']['data']['entity'])) {
                $entity = $this->myACP->getRepository($options['attr']['field']['data']['entity']['name']);
                $method = $options['attr']['field']['data']['entity']['method'];
                $res = $entity->$method();
                foreach ($res as $row) {
                    $choices[$row[$options['attr']['field']['data']['entity']['option']]] = $row[$options['attr']['field']['data']['entity']['value']];
                }
            }
        }
        return $choices;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $options['label'] = isset($options['attr']['field']['options']['label']) ? $options['attr']['field']['options']['label'] : '';
        parent::buildView($view, $form, $options);
        $view->parent->vars['react'][$view->vars['name']]['type'] = 'mfw-choice';
        $view->parent->vars['react'][$view->vars['name']]['translate'] = isset($options['attr']['field']['options']['choice_translation_domain']) ?
            $options['attr']['field']['options']['choice_translation_domain'] : false;
    }
}
