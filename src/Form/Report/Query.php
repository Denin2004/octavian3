<?php
namespace App\Form\Report;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

use App\Form\React\ReactForm;

class Query extends ReactForm
{

    protected $container;
    protected $reactView;

    public function __construct(ContainerInterface $container, CsrfTokenManagerInterface $tokenManager)
    {
        parent::__construct($tokenManager);
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->reactView = [];
        $method = $options['input'] ? 'inputField' : 'queryField';
        foreach ($options['query']['fields'] as $name => $field) {
            if ($field['type'] != 'custom') {
                $qryField = $this->container->get('report.'.$field['type']);
                $this->reactView[$name] = $qryField->$method($builder, $field, $name);
            } else {
                $this->$method($builder, $field, $name);
            }
        }
        if (isset($options['query']['multiUpload'])) {
            $builder->add(
                'step',
                HiddenType::class,
                $options['input'] ? [] :
                [
                    'attr' => [
                        'class' => 'mfw-step'
                    ]
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['query', 'input']);
    }
}
