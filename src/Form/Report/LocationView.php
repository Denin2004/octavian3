<?php
namespace App\Form\Report;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use App\Form\React\ReactChoiceType;
use App\Entity\Locations;

class LocationView extends ReactChoiceType
{
    private $locationDB;

    public function __construct(Locations $locationDB)
    {
        $this->locationDB = $locationDB;
    }

    protected function getChoices($options)
    {
        $choices = [];
        if (isset($options['attr']['field']['all'])) {
            $choices['location.all'] = $options['attr']['field']['all'];
        }
        $locations = $this->locationDB->locationsForUser();
        foreach ($locations as $loc) {
            $choices[$loc['AREA_NAME']] = $loc['AREA_CODE'];
        }
        return $choices;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $options['label'] = isset($options['label']) ? $options['label'] : 'location._location';
        parent::buildView($view, $form, $options);
        $view->parent->vars['react'][$view->vars['name']]['type'] = 'mfw-location';
        $view->parent->vars['react'][$view->vars['name']]['all'] = isset($options['attr']['field']['all']);
    }
}
