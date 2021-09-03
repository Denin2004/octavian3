<?php
namespace App\Entity;

use App\Services\MyACP\MyACPEntity;

class Reports extends MyACPEntity
{
    public function reports($params = ['id' => -1])
    {
        $res = $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.reports(:id, :result); end;',
            'cursors' => ['result'],
            'in' => $params
        ]);
        $id = -1;
        $entityIDs = [];
        $entityNames = [];
        foreach ($res as $key => $row) {
            if ($id != $row['ID']) {
                if ($id != -1) {
                    $res[$keyRec]['ENTITIES'] = implode(', ', $entityNames);
                    $res[$keyRec]['ENTITY_IDS'] = implode(',', $entityIDs);
                    $entityIDs = [];
                    $entityNames = [];
                }
                $id = $row['ID'];
                $keyRec = $key;
                $res[$keyRec]['ENTITIES'] = '';
                $res[$keyRec]['ENTITY_IDS'] = '';
            }
            if ($row['ENTITY_ID'] != '') {
                $entityIDs[] = $row['ENTITY_ID'];
                $entityNames[] = $row['ENTITY_NAME'];
            }
            if ($keyRec != $key) {
                unset($res[$key]);
            }
        }
        if ($id != -1) {
            $res[$keyRec]['ENTITIES'] = implode(', ', $entityNames);
            $res[$keyRec]['ENTITY_IDS'] = implode(',', $entityIDs);
        }
        return array_values($res);
    }

    public function post($params)
    {
        $config = $params['config'];
        unset($params['config']);
        $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.report_post(:id, :name, :config, :global_search, :drill_down, :ids);end;',
            'in' => $params,
            'lobs' => [
                [
                    'name' => 'config',
                    'type' => OCI_B_CLOB,
                    'data' => $config
                ]
            ],
            'collections' => [
                [
                    'column' => 'ids',
                    'values' => $params['entity_ids'],
                    'type' => 'CL_NUMBER',
                    'schema' => 'ACP'
                ]
            ],
            'out' => ['id']
        ]);
    }

    public function delete($id)
    {
        $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.report_delete(:id);end;',
            'in' => ['id' => $id]
        ]);
    }

    public function reportSQL($sql, $params, $fields)
    {
        $sqlText = $sql['text'];
        $addDateFormat = false;
        $dates = [];
        foreach ($fields as $name => $field) {
            if ($field['type'] == 'MfwPeriod') {
                $sqlText = str_replace(':date_from', 'TO_DATE(:date_from, :db_date_format)', $sqlText);
                $sqlText = str_replace(':date_to', 'TO_DATE(:date_to, :db_date_format)', $sqlText);
                $addDateFormat = true;
                $dates[] = 'date_from';
                $dates[] = 'date_to';
            }
            if (($field['type'] == 'MfwDate')or($field['type'] == 'MfwDateTime')) {
                $sqlText = str_replace(':'.$name, 'TO_DATE(:'.$name.', :db_date_format)', $sqlText);
                $addDateFormat = true;
                $dates[] = $name;
            }
        }
        if (isset($sql['dates'])) {
            foreach ($sql['dates'] as $name) {
                $sqlText = str_replace(':'.$name, 'TO_DATE(:'.$name.', :db_date_format)', $sqlText);
                $addDateFormat = true;
                $dates[] = $name;
            }
        }
        if ($addDateFormat) {
            $params['db_date_format'] = $this->db_date_format;
        }
        if (isset($sql['extValues'])) {
            foreach ($sql['extValues'] as $name => $value) {
                $val = explode(':', $value);
                switch ($val[0]) {
                    case 'compId':
                        $params[$name] = $this->user->getICompId();
                        break;
                    case 'userId':
                        $params[$name] = $this->user->getUserId();
                        break;
                    case 'dbDateFormat':
                        $params[$name] = $this->db_date_format;
                        break;
                    case 'val':
                        $params[$name] = $val[1] == 'null' ? null : $val[1];
                        break;
                    case 'def':
                        if (!isset($params[$name])) {
                            $params[$name] = $val[1] == 'null' ? null : $val[1];
                        }
                        break;
                }
            }
        }
        return $this->execSQL([
            'sql' => $sqlText,
            'cursors' => isset($sql['cursors']) ? $sql['cursors'] : ['result'],
            'in' => $params,
            'out' => isset($sql['outParams']) ? $sql['outParams'] : [],
            'dates' => $dates
        ]);
    }

    public function groups($params)
    {
        return $this->execSQL([
            'sql' =>'begin web_engine20.pkg_reports.report_groups(:id, :result); end;',
            'cursors' => ['result'],
            'in' => $params
        ]);
    }

    public function postGroup($params)
    {
        $params['parent_id'] = $params['parent_id'] != -1 ? $params['parent_id'] : '';
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.report_group_post(:id, :parent_id, :name);end;',
            'in' => $params, ['id']
        ]);
    }

    public function deleteGroup($params)
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.report_group_delete(:id);end;',
            'in' => $params
        ]);
    }

    public function groupsWithReports()
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.report_groups_with_reports(:result);end;',
            'cursors' => ['result']
        ]);
    }

    public function findReports($params)
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.report_find(:query, :result);end;',
            'cursors' => ['result'],
            'in' => $params
        ]);
    }

    public function addReportToGroup($params)
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.report_group_add_report(:group_id, :report_id, :name);end;',
            'in' => $params,
            'out' => ['name']
        ]);
    }

    public function deleteReportFromGroup($params)
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.report_group_delete_report(:group_id, :report_id);end;',
            'in' => $params
        ]);
    }

    public function export($params)
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.export(:id, :result, :entities); end;',
            'cursors' => ['result', 'entities'],
            'in' => $params
        ]);
    }

    public function import($params)
    {
        $config = $params['config'];
        foreach ($params['entities'] as $key => $entity) {
            $params['entities'][$key] = intval($entity);
        };
        unset($params['config']);
        $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.import(:id, :name, :config, :globalSearch, :drillDown, :ent);end;',
            'in' => $params,
            'lobs' => [
                [
                    'name' => 'config',
                    'type' => OCI_B_CLOB,
                    'data' => $config
                ]
            ],
            'collections' => [
                [
                    'column' => 'ent',
                    'values' => $params['entities'],
                    'type' => 'CL_NUMBER',
                    'schema' => 'ACP'
                ]
            ]
        ]);
    }

    public function globalSearch()
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.global_search(:result); end;',
            'cursors' => ['result']
        ]);
    }

    public function setGlobalSearch($params)
    {
        $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.set_global_search(:id, :globalSearch);end;',
            'in' => $params
        ]);
    }

    public function addToFavorites($params)
    {
        $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.add_to_favorites(:report_id);end;',
            'in' => $params
        ]);
    }

    public function removeFromFavorites($params)
    {
        $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.remove_from_favorites(:report_id);end;',
            'in' => $params
        ]);
    }

    public function favorites()
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_reports.favorites(:result); end;',
            'cursors' => ['result']
        ]);
    }
}
