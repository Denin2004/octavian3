<?php
namespace App\Form\Report;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

use App\Form\React\ReactChoiceType;
use App\Entity\Currency;

class  CurrencyView extends ReactChoiceType
{
    private $currencyDB;

    public function __construct(Currency $currencyDB)
    {
        $this->currencyDB = $currencyDB;
    }

    protected function getChoices($options)
    {
        $choices = [];
        if (isset($options['attr']['field']['all'])) {
            $choices['currency.all '] = $options['attr']['field']['all'];
        }
        $currencies = $this->currencyDB->listCurrency(isset($options['attr']['field']['woPOS']) ? $options['attr']['field']['woPOS'] : false);
        foreach ($currencies as $currency) {
            $choices[$currency['CURRENCY_ABBR']] = $currency['CURRENCY_CODE'];
        }
        return $choices;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $options['label'] = isset($options['label']) ? $options['label'] : 'currency._currency';
        parent::buildView($view, $form, $options);
    }
}
