<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwQuery;
use App\Form\Core\Type\MfwTypeaheadType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MfwPlayer extends MfwQuery
{
    protected $router;
    protected $translator;

    public function __construct(RouterInterface $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator= $translator;
    }

    public function queryField($form, $element, $name)
    {
        $uniqid = substr(uniqid(), -6);
        if (isset($element['options'])) {
            $element['options']['label'] = isset($element['options']['label']) ? $element['options']['label'] : 'player._player';
            if (isset($element['options']['attr'])) {
                $element['options']['attr']['class'] = isset($element['options']['attr']['class']) ?
                    $element['options']['attr']['class'].' mfw-rep-plr-'.$uniqid : 'mfw-rep-plr-'.$uniqid;
            } else {
                $element['options']['attr'] = ['class' => 'mfw-rep-plr-'.$uniqid];
            }
        } else {
            $element['options'] = [
                'label' => 'player._player',
                'attr' => [
                    'class' => 'mfw-rep-plr-'.$uniqid
                ]
            ];
        }
        $element['options']['label'] = $this->translator->trans($element['options']['label']);
        $options = $form->getOptions();
        $form->add(
            'player_id',
            MfwTypeaheadType::class,
            array_replace_recursive(
                [
                    'json_config' => [
                        'sourceURL' => $this->router->generate('playerFindByType'),
                        'selectURL' => $this->router->generate(
                            'reportViewPlayer',
                            [
                                'uniqid' => $uniqid,
                                'selectOne' => isset($element['multiSelect']) ? 0 : 1
                            ]
                        ),
                        'template' => true,
                        'formSubmit' => false,
                        'minLength' => 2,
                        'method' => 'post'
                    ],
                    'data' => [
                        'text' => isset($options['data']['player_name']) ? $options['data']['player_name'] : '',
                        'val' => isset($options['data']['player_id']) ? $options['data']['player_id'] : ''
                    ],
                    'form_text' => $this->translator->trans('player.widget_placeholder'),
                    'addon' => [
                        [
                            'icon' => 'fa-image',
                            'url' => $this->router->generate('playerRecodnitionModal'),
                            'method' => 'get'
                        ]
                    ]
                ],
                $element['options']
            )
        );
    }

    public function inputField($form, $element, $name)
    {
        $form->add('player_id_text', HiddenType::class)
            ->add('player_id', HiddenType::class);
    }
}
