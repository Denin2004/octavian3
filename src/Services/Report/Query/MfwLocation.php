<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwChoice;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MfwLocation extends MfwChoice
{
    public function queryField($form, $element, $name)
    {
        if (isset($element['options'])) {
            $element['options']['label'] = isset($element['options']['label']) ? $element['options']['label'] : 'location._location';
            $element['options'] = array_replace_recursive(
                [
                    'choice_translation_domain' => false,
                    'required' => isset($element['all']) && $element['all'] == '' ? false : true,
                    'attr' => [
                        'class' => 'mfw mfw-location-select'
                    ]
                ],
                $element['options']
            );
        } else {
            $element['options'] = [
                'choice_translation_domain' => false,
                'label' => 'location._location',
                'required' => isset($element['all']) && $element['all'] == '' ? false : true,
                'attr' => [
                    'class' => 'mfw mfw-location-select'
                ]
            ];
        }
        MfwChoice::queryField($form, $element, $name);
    }

    protected function getChoices($element)
    {
        $choices = [];
        if (isset($element['all'])) {
            $choices['location.all'] = $element['all'];
        }
        $location = $this->myACP->getRepository('Locations');
        $locations = $location->locationsForUser();
        foreach ($locations as $loc) {
            $choices[$loc['AREA_NAME']] = $loc['AREA_CODE'];
        }
        return $choices;
    }

    public function inputField($form, $element, $name)
    {
        $form->add(
            $name,
            IntegerType::class,
            [
                'constraints' => [
                    new Callback(['callback' => [$this, 'checkLocation']])
                ],
                'attr' => [
                    'all' => isset($element['all']),
                    'allCode' => isset($element['all']) ? $element['all'] : null
                ]
            ]
        );
    }

    public function checkLocation($value, ExecutionContextInterface $context)
    {
        $config = $context->getObject()->getConfig()->getOptions();
        if (($config['attr']['all'] && $config['attr']['allCode'] == $value)) {
            return;
        }
        $location = $this->myACP->getRepository('Locations');
        if (!$location->checkLocationAccessDB($value)) {
            $context->buildViolation('location.error.access')
                ->addViolation();
        }
    }
}
