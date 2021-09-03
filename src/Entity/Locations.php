<?php
namespace App\Entity;

use App\Services\MyACP\MyACPEntity;

class Locations extends MyACPEntity
{
    public function locationsForUser()
    {
        return $this->execSQL([
            'sql' => 'begin acp.www_xml_main.get_emp_area_name_curs(:compid, :p_emp_id, :result); end;',
            'cursors' => ['result'],
            'in' => [
                'compid' => $this->user->getICompId(),
                'p_emp_id' => $this->user->getUserId()
            ]
        ]);
    }

    public function checkLocationAccessDB($iLocation)
    {
        if (!in_array($iLocation, [0, -1])) {
            return $this->execSQL([
                'sql' => 'begin acp.www_xml_main.check_emp_area_access(:p_comp_id, :p_area_code, :p_emp_id, :p_result); end;',
                'in' => [
                    'p_comp_id' => $this->user->getICompId(),
                    'p_area_code' => $iLocation,
                    'p_emp_id' => $this->user->getUserId()
                ],
                'out' => [
                    'p_result'
                ]
            ])['p_result'] != 0;
        }

        if (in_array($iLocation, [0, -1])) {
            return $this->execSQL([
                'sql' => 'begin acp.www_xml_main.check_emp_all_area_access(:p_comp_id, :p_emp_id, :p_result); end;',
                'in' => [
                    'p_comp_id' => $this->user->getICompId(),
                    'p_emp_id' => $this->user->getUserId()
                ],
                'out' => [
                    'p_result'
                ]
            ])['p_result'] != 0;
        }
    }

    public function lastGameDayTime($params)
    {
        return $this->execSQL([
            'sql' => 'begin :p_date := acp.meter_history_utl.get_last_closed_gd(:area_id); end;',
            'in' => $params,
            'out' => ['p_date']
        ])['p_date'];
    }

    public function closingMethod()
    {
        return $this->execSQL([
            'sql' => 'begin :p_method := acp.meter_history_utl.get_closing_gd_method(); end;',
            'out' => ['p_method']
        ])['p_method'];
    }

    public function closeGamingDays($params)
    {
        $params['db_date_format'] = $this->db_date_format;
        return $this->execSQL([
            'sql' => 'begin acp.meter_history_utl.man_close_gd_until(:location_id, to_date(:date, :db_date_format)); end;',
            'in' => $params
        ]);
    }

    public function sections($params)
    {
        return $this->execSQL([
            'sql' => 'begin acp.www_billhand.get_section(:location_id, :result); end;',
            'cursors' => ['result'],
            'in' => $params
        ]);
    }

    public function stations($params)
    {
        return $this->execSQL([
            'sql' => 'begin cage.pkg_report.get_terminal_list(:location_id, :result); end;',
            'cursors' => ['result'],
            'in' => $params
        ]);
    }
}
