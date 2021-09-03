<?php
namespace App\Form\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Callback;

use App\Services\SiteConfig\SiteConfig;

class MfwDateTimeType extends AbstractType
{
    private $config;

    public function __construct(SiteConfig $config)
    {
        $this->config = $config;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'js_format' => 'js_datetime_format',
                'php_format' => $this->config->get('php_date_format').' '.$this->config->get('php_time_no_sec_format'),
                'allow_empty' => false,
                'add_secs' => false,
                'constraints' => [new Callback(['callback' => [$this, 'checkDate']])]
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['js_format'] = $options['add_secs'] ? 'js_datetimesec_format' : $options['js_format'];
        $view->vars['allow_empty'] = $options['allow_empty'];
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function checkDate($value, ExecutionContextInterface $context)
    {
        $object = $context->getObject()->getConfig();
        $options = $object->getOptions();
        if ($value == '') {
            $required = isset($options['required']) ? $options['required'] : true;
            if ($required === true) {
                $context->buildViolation('mfw.blank_error')
                ->addViolation();
            }
            return;
        }
        $oDate = \DateTime::createFromFormat(
            $options['add_secs'] ? $this->config->get('php_date_format').' '.$this->config->get('php_time_format') : $options['php_format'],
            $value
        );
        if (!$oDate) {
            $context->buildViolation('mfw.'.$object->getName().'.error')
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}
