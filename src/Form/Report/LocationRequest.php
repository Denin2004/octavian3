<?php
namespace App\Form\Report;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\AbstractType;

use App\Entity\Locations;

class LocationRequest extends AbstractType
{
    private $locationDB;

    public function __construct(Locations $locationDB)
    {
        $this->locationDB = $locationDB;
    }

    public function getParent()
    {
        return HiddenType::class;
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
        $config = $context->getObject()->getConfig()->getOptions();
        if (($config['attr']['field']['all'] && $config['attr']['field']['allCode'] == $value)) {
            return;
        }
        if (!$this->locationDB->checkLocationAccessDB($value)) {
            $context->buildViolation('location.error.access')
                ->addViolation();
        }
    }
}
