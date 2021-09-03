<?php
namespace App\Services\MyACP;

use App\Services\MyACP\MyACP;
use App\Services\SiteConfig\SiteConfig;

class MyACPEntity
{
    protected $myACP;
    protected $db_date_format;
    protected $user;
    protected $siteConfig;

    public function __construct(MyACP $myACP, SiteConfig $config)
    {
        $this->myACP = $myACP;
        $this->db_date_format = $myACP->getDBDateFormat();
        $this->user = $myACP->getUser();
        $this->siteConfig = $config;
    }

    protected function datePrepare($date)
    {
        $oDate = \DateTime::createFromFormat($this->siteConfig->get('php_date_format').' '.$this->siteConfig->get('php_time_format'), $date);
        if (!$oDate) {
            $oDate = \DateTime::createFromFormat($this->siteConfig->get('php_date_format').' '.$this->siteConfig->get('php_time_no_sec_format'), $date);
            if (!$oDate) {
                $oDate = \DateTime::createFromFormat($this->siteConfig->get('php_date_format'), $date);
                if (!$oDate) {
                    return $date;
                } else {
                    $oDate->setTime(0, 0, 0);
                }
            } else {
                $oDate->setTime($oDate->format('H'), $oDate->format('i'), 0);
            }
            return $oDate->format($this->siteConfig->get('php_date_format').' '.$this->siteConfig->get('php_time_format'));
        }
        return $date;
    }

    protected function execSQL($params)
    {
        if (isset($params['dates'])) {
            foreach ($params['dates'] as $name) {
                $params['in'][$name] = $this->datePrepare($params['in'][$name]);
            }
        }
        return $this->myACP->execSQL($params);
    }
}
