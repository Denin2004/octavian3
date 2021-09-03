<?php
namespace App\Form\Core\Type;

use Symfony\Component\Form\DataTransformerInterface;

class MfwDateTransformer implements DataTransformerInterface
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
    }

    public function transform($value)
    {
        return $value;
    }

    public function reverseTransform($value)
    {
        $oDate = \DateTime::createFromFormat($this->options['format'], $value);
        if (!$oDate) {
            return '';
        }
        switch ($this->options['addTime']) {
            case 'endDay':
                $oDate->setTime(23, 59, 59);
                return $oDate->format($this->options['format'].' '.$this->options['siteConfig']->get('php_time_format'));
            case 'startDay':
                $oDate->setTime(0, 0, 0);
                return $oDate->format($this->options['format'].' '.$this->options['siteConfig']->get('php_time_format'));
        }
        return $value;
    }
}
