<?php
namespace App\Form\Report;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

use App\Form\React\ReactForm;
use App\Form\React\ReactHiddenType;
use App\Form\React\ReactTextType;
use App\Form\React\ReactRangeType;
use App\Form\Report\LocationView;
use App\Form\Report\CurrencyView;

class Query extends ReactForm
{
    const FIELDS_MAP = [
        'MfwHidden' => [
            'view' => ReactHiddenType::class,
            'request' => HiddenType::class
        ],
        'MfwText' => [
            'view' => ReactTextType::class,
            'request' => TextType::class,
        ],
        'MfwLocation' => [
            'view' => LocationView::class,
            'request' => LocationRequest::class
        ],
        'MfwCurrency' => [
            'view' => CurrencyView::class,
            'request' => IntegerType::class
        ],
        'MfwPeriod' => [
            'view' => ReactRangeType::class,
            'request' => ReactRangeType::class
        ]
    ];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['query']['fields'] as $name => $field) {
            if (!isset($this::FIELDS_MAP[$field['type']])) {

            }
            $builder->add(
                $name,
                $this::FIELDS_MAP[$field['type']][$options['request'] === true ? 'request' : 'view'],
                [
                    'attr' => ['field' => $field],

                ]
            );
        }
        if (isset($options['query']['multiUpload'])) {
            $builder->add('step', ReactHiddenType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['query', 'request'])->setDefault('request', false);
    }
}
