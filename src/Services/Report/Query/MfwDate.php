<?php
namespace App\Services\Report\Query;

use App\Services\Report\Query\MfwQuery;
use App\Form\Core\Type\MfwDateType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Form\CallbackTransformer;

use App\Services\SiteConfig\SiteConfig;

class MfwDate extends MfwQuery
{
    protected $siteConfig;
    protected $locale;

    public function __construct(SiteConfig $siteConfig, RequestStack $requestStack)
    {
        $this->siteConfig = $siteConfig;
        $this->locale = $requestStack->getCurrentRequest()->getLocale();
    }

    public function queryField($form, $element, $name)
    {
        $element['options'] = isset($element['options']) ? $element['options'] : [];
        $data = isset($form->getData()[$name]) ? $this->getDateFromString(urldecode($form->getData()[$name])) : false;
        $element['options']['data'] = $data ? $data->format($this->siteConfig->get('php_date_format')) : $this->getDefaultDate($element, $this->siteConfig->get('php_date_format'));
        $form->add($name, MfwDateType::class, $element['options']);
    }

    public function inputField($form, $element, $name)
    {
        if (isset($element['options']['php_format'])) {
            $form->add($name, MfwDateType::class);
            $this->options_php_format = $element['options']['php_format'];
            $form->get($name)->addModelTransformer(
                new CallbackTransformer(
                    function ($value) {
                        return $value;
                    },
                    function ($value) {
                        $fmt = new \IntlDateFormatter(
                            $this->locale,
                            \IntlDateFormatter::FULL,
                            \IntlDateFormatter::FULL
                        );
                        $fmt->setPattern($this->options_php_format);
                        $date = new \Datetime();
                        $tmStamp = $fmt->parse($value);
                        if (!$tmStamp) {
                            return '';
                        }
                        $date->setTimestamp($tmStamp);
                        return $date->format($this->siteConfig->get('php_date_format'));
                    }
                )
            );
        } else {
            $form->add($name, MfwDateType::class, $element['options']);
        }
    }

    protected function getDefaultDate($element, $format)
    {
        $dt = new \DateTime();
        if (isset($element['add'])) {
            $def = explode(' ', $element['add']);
            if (count($def) >= 2) {
                $method = array_shift($def);
                $dt->$method(new \DateInterval(implode(' ', $def)));
            }
        }
        if (isset($element['set'])) {
            switch ($element['set']) {
                case 'startDay':
                    $dt->setTime(0, 0, 0);
                    break;
                case 'endDay':
                    $dt->setTime(23, 59, 59);
                    break;
            }
        }
        if (isset($element['options']['php_format'])) {
            $fmt = new \IntlDateFormatter(
                $this->locale,
                \IntlDateFormatter::NONE,
                \IntlDateFormatter::NONE
            );
            $fmt->setPattern($element['options']['php_format']);
            return $fmt->format($dt);
        } else {
            return $dt->format($format);
        }
    }

    protected function getDateFromString($dateString)
    {
        $date = \DateTime::createFromFormat($this->siteConfig->get('php_date_format'), $dateString);
        if ($date) {
            return $date;
        } else {
            $date = \DateTime::createFromFormat($this->siteConfig->get('php_date_format').' '.$this->siteConfig->get('php_time_format'), $dateString);
            if ($date) {
                return $date;
            } else {
                $date = \DateTime::createFromFormat($this->siteConfig->get('php_date_format').' '.$this->siteConfig->get('php_time_no_sec_format'), $dateString);
                if ($date) {
                    return $date;
                }
            }
        }
        return false;
    }
}
