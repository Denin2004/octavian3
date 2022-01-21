<?php
namespace App\Form\React;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

use App\Services\SiteConfig\SiteConfig;

class ReactRangeType extends AbstractType
{
    private $siteConfig;

    public function __construct(SiteConfig $config)
    {
        $this->siteConfig = $config;
    }

    public function getParent()
    {
        return HiddenType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(
            function ($value) {
                return $value;
            },
            function ($value) {
                if (isset($value[0])) {
                    $dt = new \DateTime($value[0]);
                    $value[0] = !$dt ? $value[0] : $dt->format($this->siteConfig->get('php_date_format'));
                }
                if (isset($value[1])) {
                    $dt = new \DateTime($value[1]);
                    $value[1] = !$dt ? $value[1] : $dt->format($this->siteConfig->get('php_date_format'));
                }
                return $value;
            }
        ));
        if ($options['request']) {
            $builder->add('0', HiddenType::class)
                ->add('1', HiddenType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [ new Callback(['callback' => [$this, 'checkRange']])],
            'request' => false,
            'compound' => true
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->parent->vars['react'][$view->vars['name']] = [
            'type' => 'mfw-range',
            'value' => $view->vars['value'],
            'full_name' => $view->vars['full_name']
        ];
    }

    public function checkRange($value, ExecutionContextInterface $context)
    {
        if (count($value) != 2) {
            $context->buildViolation('calendar.errors.range_format')->addViolation();
            return;
        }
        $dtFrom = new \DateTime($value[0]);
        $dtTo = new \DateTime($value[1]);
        if (!$dtFrom || !$dtTo) {
            $context->buildViolation('calendar.errors.range_format')->addViolation();
            return;
        }
        if ($dtFrom > $dtTo) {
            $context->buildViolation('calendar.errors.range')->addViolation();
        }
        return;
    }
}
