<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwChoice;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class MfwJackpot extends MfwChoice
{
    public function queryField($form, $element, $name)
    {
        if (isset($element['options'])) {
            $element['options']['choice_translation_domain'] = false;
            $element['options']['label'] = isset($element['options']['label']) ? $element['options']['label'] : 'jackpot._jackpot';
        } else {
            $element['options'] = [
                'choice_translation_domain' => false,
                'label' => 'jackpot._jackpot'
            ];
        }
        MfwChoice::queryField($form, $element, $name);
    }

    protected function getChoices($element)
    {
        $choices = [];
        if (isset($element['all'])) {
            $allLoc = $this->translator->trans('jackpot.all');
            $choices[$allLoc] = $element['all'];
        }
        $jackpotsDB = $this->myACP->getRepository('Jackpots');
        $jackpots = $jackpotsDB->listJackpots();
        foreach ($jackpots as $loc) {
            $choices[$loc['PROGRESSIVE_NAME']] = $loc['PROGRESSIVE_CODE'];
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
