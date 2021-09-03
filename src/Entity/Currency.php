<?php
namespace App\Entity;

use App\Services\MyACP\MyACPEntity;

class Currency extends MyACPEntity
{
    public function listCurrency($woPOS = false)
    {
        return $woPOS == true ?
            $this->execSQL([
                'sql' => 'begin acp.www_xml_main_new.get_currency_without_pos(:result); end;',
                'cursors' => ['result']
            ]) :
        $this->execSQL([
            'sql' => 'begin acp.www_xml_main_new.get_active_currency_list(:result); end;',
            'cursors' => ['result']
        ]);
    }

    public function national()
    {
        return $this->execSQL([
            'sql' => 'begin acp.WWW_CURRENCY.get_national_currency(:id, :abbr, :desc); end;',
            'out' => ['id', 'abbr', 'desc']
        ]);
    }

    public function addRate($params)
    {
        $this->execSQL([
            'sql' => 'begin acp.WWW_CURRENCY.add_currency_rate(:id, :value, 1); end;',
            'in' => $params
        ]);
    }

    public function denoms($curr_id)
    {
        $denoms = $this->execSQL([
            'sql' => 'begin acp.www_xml_main_new.get_enabled_bill_denoms(:result); end;',
            'cursors' => ['result']
        ]);
        foreach ($denoms as $key => $row) {
            if ($row['CURRENCY_CODE'] != $curr_id) {
                unset($denoms[$key]);
            }
        }
        return $denoms;
    }

    public function getAbbr($curr_id)
    {
        return $this->execSQL([
            'sql' => 'begin :value := loc_common.pkg_common.get_currency_abbr(:currency_code); end;',
            'in' => [
                'currency_code' => $curr_id
            ],
            'out' => ['value']
        ])['value'];
    }

    public function config()
    {
        return $this->execSQL([
            'sql' => 'begin ACP.WWW_XML_MAIN_NEW.get_currency_ptm_config(:result); end;',
            'cursors' => ['result']
        ]);
    }

    public function setConfig($params)
    {
        $this->execSQL([
            'sql' => 'begin ACP.WWW_XML_MAIN_NEW.set_currency_ptm_config(:currency_id, :nominal, :value); end;',
            'in' => $params
        ]);
    }

    public function nominals()
    {
        return [
            '1',
            '2',
            '5',
            '10',
            '20',
            '25',
            '50',
            '100',
            '200',
            '250',
            '500',
            '1000',
            '2000',
            '2500',
            '5000',
            '10000',
            '20000',
            '25000',
            '50000',
            '100000',
            '200000',
            '250000',
            '500000',
            '1000000'
        ];
    }
}
