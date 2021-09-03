<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwChoice;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class MfwCardsType extends MfwChoice
{
    public function queryField($form, $element, $name)
    {
        if (isset($element['options'])) {
            $element['options']['choice_translation_domain'] = false;
            $element['options']['label'] = isset($element['options']['label']) ? $element['options']['label'] : 'card._card';
        } else {
            $element['options'] = [
                'choice_translation_domain' => false,
                'label' => 'player.card.type'
            ];
        }
        MfwChoice::queryField($form, $element, $name);
    }

    protected function getChoices($element)
    {
        $choices = [];

        $cardsDB = $this->myACP->getRepository('Cards');
        $cards = $cardsDB->types();
        foreach ($cards['result'] as $c) {
            $choices[$c['NAME']] = $c['CARD_TYPE_ID'];
        }
        return $choices;
    }

    public function inputField($form, $element, $name)
    {
        $form->add(
            $name,
            IntegerType::class,
            [

            ]
        );
    }
}
