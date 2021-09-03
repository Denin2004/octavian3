<?php
namespace App\Form\Core\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;

use App\Services\SiteConfig\SiteConfig;
use App\Form\Core\Type\MfwDateTransformer;

class MfwDateType extends AbstractType
{
    private $php_format;
    private $locale;
    private $siteConfig;

    public function __construct(SiteConfig $config, RequestStack $requestStack)
    {
        $this->php_format = $config->get('php_date_format');
        $this->locale = $requestStack->getCurrentRequest()->getLocale();
        $this->siteConfig = $config;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'js_format' => 'js_date_format',
                'php_format' => $this->php_format,
                'allow_empty' => false,
                'constraints' => [new Callback(['callback' => [$this, 'checkDate']])],
                'add_time' => ''
            ]
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['add_time'] != '') {
            $builder->addModelTransformer(new MfwDateTransformer([
                'addTime' => $options['add_time'],
                'format' => $options['php_format'],
                'siteConfig' => $this->siteConfig,
                'name' => $builder->getName()
            ]));
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['js_format'] = $options['js_format'];
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
            $options['add_time'] == '' ? $options['php_format'] : $options['php_format'].' '.$this->siteConfig->get('php_time_format'),
            $value
        );
        if (!$oDate) {
            $context->buildViolation('mfw.'.$object->getName().'.error')
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}
