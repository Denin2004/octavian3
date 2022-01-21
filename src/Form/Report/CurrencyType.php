<?php
namespace App\Form\Report;

use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Form\React\ReactChoiceType;

class  CurrencyType extends ReactChoiceType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['request', 'field'])
            ->setDefaults([
                'request' => false,
                'field' => []
            ]);
    }

    private function getOptions()
    {
        dump('!!!!');
        return [];
    }
}
