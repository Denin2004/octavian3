<?php
namespace App\Form\Report;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\AbstractType;

class ChoiceRequest extends AbstractType
{
    public function getParent()
    {
        return HiddenType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new Callback(['callback' => [$this, 'checkInteger']])
            ]
        ]);
    }

    public function checkInteger($value, ExecutionContextInterface $context)
    {
        $config = $context->getObject()->getConfig()->getOptions();
        if (!isset($config['attr']['field']['intInput'])) {
            return;
        }
        if (!is_int(is_numeric($value) ? $value*1 : '')) {
            $context->buildViolation('choice.error.code_not_integer')
                ->addViolation();
        }
    }
}
