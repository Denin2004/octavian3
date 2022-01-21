<?php
namespace App\Form\Report;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;


use App\Entity\Locations;

class  LocationRequest extends HiddenType
{
    private $locationDB;

    public function __construct(Locations $locationDB)
    {
        $this->locationDB = $locationDB;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new Callback(['callback' => [$this, 'checkLocation']])
            ]
        ]);
    }

    public function checkLocation($value, ExecutionContextInterface $context)
    {
        return true;
        $config = $context->getObject()->getConfig()->getOptions();
        if (($config['attr']['field']['all'] && $config['attr']['field']['allCode'] == $value)) {
            return;
        }
        if (!$this->locationDB->checkLocationAccessDB($value)) {
            $context->buildViolation($this->translator->trans('location.error.access'))
                ->addViolation();
        }
    }
}
