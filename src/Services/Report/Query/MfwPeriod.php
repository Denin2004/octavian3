<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwDate;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use App\Form\Core\Type\MfwDateTimeType;
use App\Form\Core\Type\MfwDateType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;

use App\Services\SiteConfig\SiteConfig;

class MfwPeriod extends MfwDate
{
    protected $router;

    public function __construct(SiteConfig $siteConfig, RouterInterface $router, RequestStack $requestStack)
    {
        parent::__construct($siteConfig, $requestStack);
        $this->router = $router;
    }

    private $customPeriods = [
        'custom' => [
            'value' => 1,
            'text' => 'date_block.custom'
        ],
        'startMonth' => [
            'value' => 2,
            'text' => 'date_block.start_month'
        ],
        'prevMonth' => [
            'value' => 3,
            'text' => 'date_block.prev_month'
        ],
        'lastDay' => [
            'value' => 4,
            'text' => 'date_block.last_day'
        ],
        'last2Day' => [
            'value' => 5,
            'text' => 'date_block.2_day'
        ],
        'last3Day' => [
            'value' => 6,
            'text' => 'date_block.3_day'
        ],
        'prevWeek' => [
            'value' => 7,
            'text' => 'date_block.prev_week'
        ],
        'lastGamingDay' => [
            'value' => 8,
            'text' => 'date_block.last_gaming_day'
        ],
        'last2GamingDay' => [
            'value' => 9,
            'text' => 'date_block.2_gaming_day'
        ],
        'last3GamingDay' => [
            'value' => 10,
            'text' => 'date_block.3_gaming_day'
        ],
        'today' => [
            'value' => 11,
            'text' => 'date_block.today'
        ],
        'lastHour' => [
            'value' => 12,
            'text' => 'date_block.last_hour'
        ],
        'last2Hour' => [
            'value' => 13,
            'text' => 'date_block.last_2_hour'
        ],
        'last4Hour' => [
            'value' => 14,
            'text' => 'date_block.last_4_hour'
        ],
        'last12Hour' => [
            'value' => 15,
            'text' => 'date_block.last_12_hour'
        ],
        'last24Hour' => [
            'value' => 16,
            'text' => 'date_block.last_24_hour'
        ],
        'last48Hour' => [
            'value' => 17,
            'text' => 'date_block.last_48_hour'
        ]

    ];

    public function queryField($form, $element, $name)
    {
        $idPeriod = isset($element['identy']) ? $element['identy'] : 'mfw-period';
        $optStart = [
            'label' => 'date.from',
            'attr' => [
                'class' => 'mfw-date-block mfw-date-block-from '.$idPeriod
            ]
        ];
        $optEnd = [
            'label' => 'date.to',
            'attr' => [
                'class' => 'mfw-date-block mfw-date-block-to '.$idPeriod
            ]
        ];
        $data = $form->getData();
        if (isset($element['custom'])) {
            $choices = [];
            $value = 1;
            $selected = isset($element['custom']['value']) ? $element['custom']['value'] : '';
            if (isset($data['date_from']) or isset($data['date_to'])) {
                if (!in_array('custom', $element['custom']['periods'])) {
                    $element['custom']['periods'][] = 'custom';
                }
                $selected = 'custom';
            }
            foreach ($element['custom']['periods'] as $choice) {
                if (isset($this->customPeriods[$choice])) {
                    $choices[$this->customPeriods[$choice]['text']] = $this->customPeriods[$choice]['value'];
                    if ($selected == $choice) {
                        $value = $this->customPeriods[$choice]['value'];
                    }
                }
            }
            $form->add(
                'period_type',
                ChoiceType::class,
                [
                    'choices' => $choices,
                    'expanded' => false,
                    'multiple' => false,
                    'label' => false,
                    'data' => $value,
                    'attr' => array_merge(
                        [
                            'class' => 'mfw',
                            'data-mfw_type' => 'mfwDateBlock,select2',
                            'data-mfw_identy' => $idPeriod,
                            'addClass' => 'mfw-select2-show-all',
                            'data-mfw_config' => '{"width": "100%", "dropdownAutoWidth": true, "minimumResultsForSearch": "20", "dropdownParent": ".form-group", "parentAddClass": "eee"}'
                        ],
                        isset($element['datesFromServer'])&&($element['datesFromServer'] == true) ?
                        [
                           'data-mfw_game_time' => $this->router->generate('getGamingTime'),
                           'data-mfw_add_time' => $element['addTime'] == true ? 1 : 0
                        ] : []
                    )
                ]
            );
        }
        if ((isset($element['start']))or((isset($element['end'])))) {
            $format = $element['addTime'] == true ?
                $this->siteConfig->get('php_date_format').' '.$this->siteConfig->get('php_time_format')
                : $this->siteConfig->get('php_date_format');
            if ((isset($element['start']['add'])||isset($element['start']['set'])) && !isset($data['date_from'])) {
                $optStart['data'] = $this->getDefaultDate($element['start'], $format);
            }
            if (isset($element['start']['options'])) {
                $optStart = array_merge($optStart, $element['start']['options']);
            }
            if ((isset($element['end']['add'])||isset($element['end']['set'])) && !isset($data['date_to'])) {
                $optEnd['data'] = $this->getDefaultDate($element['end'], $format);
            }
            if (isset($element['end']['options'])) {
                $optEnd = array_merge($optEnd, $element['end']['options']);
            }
        }
        if ($element['addTime'] == true) {
            $form->add('date_from', MfwDateTimeType::class, $optStart);
            $form->add('date_to', MfwDateTimeType::class, $optEnd);

        } else {
            $form->add('date_from', MfwDateType::class, $optStart);
            $form->add('date_to', MfwDateType::class, $optEnd);
        }
    }

    public function inputField($form, $element, $name)
    {
        if (isset($element['custom'])) {
            $form->add('period_type', IntegerType::class);
        }
        $optStart = [
            'constraints' => [new Callback(['callback' => [$this, 'checkPeriod']])]
        ];
        $optEnd = [];
        if ($element['addTime'] == true) {
            if ((isset($element['start']))or((isset($element['end'])))) {
                if (isset($element['start']['options'])) {
                    $optStart = array_merge($optStart, $element['start']['options']);
                }
                if (isset($element['end']['options'])) {
                    $optEnd = array_merge($optEnd, $element['end']['options']);
                }
            }
            $form->add('date_from', MfwDateTimeType::class, $optStart)
                ->add('date_to', MfwDateTimeType::class, $optEnd);
        } else {
            if (isset($element['start']['options'])) {
                $optStart = array_merge($optStart, $element['start']['options']);
            }
            if (isset($element['end']['options'])) {
                $optEnd = array_merge($optEnd, $element['end']['options']);
            }
            $form->add('date_from', MfwDateType::class, $optStart)
                ->add('date_to', MfwDateType::class, $optEnd);
        }
    }

    public function checkPeriod($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $data = $form->getData();
        $options = $form->get('date_to')->getConfig()->getOptions();
        $format = (isset($options['add_secs']) and $options['add_secs'] == true) ? $this->siteConfig->get('php_date_format').' '.$this->siteConfig->get('php_time_format') : $options['php_format'];
        $dtTo = \DateTime::createFromFormat($format, $data['date_to']);
        $dtFrom = \DateTime::createFromFormat($format, $value);
        if ($dtFrom > $dtTo) {
            $context->buildViolation('mfw.wrong_date_range')
                ->addViolation();
        }
    }
}
