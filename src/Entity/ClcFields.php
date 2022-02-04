<?php
namespace App\Entity;

use App\Services\MyACP\MyACPEntity;

class ClcFields extends MyACPEntity
{
    private $clcTypes = [
        'getIntegerFormat',
        'getNumberFormat',
        'getIntegerFormat',
        'getNumberFormat'
    ];

    public function getClcFields($params, $addMethod = false)
    {
        $res = [];
        if (isset($params['report_id'])&&($params['report_id'] != '')) {
            $selector = explode('_', $params['realTblSelector']);
            unset($selector[count($selector)-2]);
            $params['tblSelector'] = implode('_', $selector);
            $res = $this->execSQL([
                'sql' => 'begin web_engine20.pkg_calc_fields.report(:report_id, :tblSelector, :result); end;',
                'cursors' => ['result'],
                'in' => $params
            ]);
        }
        if (isset($params['route_name'])&&($params['route_name'] != '')) {
            $res = $this->execSQL([
                'sql' => 'begin web_engine20.pkg_calc_fields.route(:route_name, :tblSelector, :result); end;',
                'cursors' => ['result'],
                'in' => $params
            ]);
        }
        if ($addMethod == true) {
            foreach ($res as $key => $row) {
                $res[$key]['method'] = $this->clcTypes[$row['TYPE_FIELD']];
            }
        }
        return $res;
    }

    public function byId($params)
    {
        $res = $this->execSQL([
            'sql' => 'begin web_engine20.pkg_calc_fields.by_id(:id, :result); end;',
            'cursors' => ['result'],
            'in' => $params
        ]);
        return count($res) == 0 ? false : $res[0];
    }


    public function post($params)
    {
        $formula = $params['formula'];
        unset($params['formula']);
        unset($params['tags']);
        unset($params['labels']);
        $params['sum_group'] = $params['sum_group'] == true ? 1 : 0;
        $params['sum_total'] = $params['sum_total'] == true ? 1 : 0;
        if ($params['report_id'] != '') {
            $selector = explode('_', $params['realTblSelector']);
            unset($selector[count($selector)-2]);
            $params['tblSelector'] = implode('_', $selector);
        }
        $this->execSQL([
            'sql' => 'begin web_engine20.pkg_calc_fields.post(
                    :id,
                    :title,
                    :type,
                    :sum_group,
                    :sum_total,
                    :report_id,
                    :tblSelector,
                    :route_name,
                    :formula); end;',
            'in' => $params,
            'lobs' => [
                [
                    'name' => 'formula',
                    'type' => OCI_B_CLOB,
                    'data' => $formula
                ]
            ]
        ]);
        return $this->getClcFields($params);
    }

    public function delete($params)
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_calc_fields.del(:id, :result); end;',
            'cursors' => ['result'],
            'in' => $params
        ]);
    }
}
