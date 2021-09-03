<?php
namespace App\Entity;

use App\Services\MyACP\MyACPEntity;

class Users extends MyACPEntity
{
    public function getUserId($login)
    {
        return $this->myACP->execSQL([
            'sql' => 'begin :p_emp_id := personal.pkg_employee.get_employee_id(:login); end;',
            'in' => [
                'login' => $login
            ],
            'out' => [
                'p_emp_id'
            ]
        ])['p_emp_id'];
    }

    public function userAccess($id)
    {
        $res = [
            'routes' => [],
            'rolesAccess' => []
        ];
        $rolesAccess = $this->execSQL([
            'sql' => 'begin web_engine20.pkg_uac.user_access(:p_user_id, :result); end;',
            'cursors' => ['result'],
            'in' => [
                'p_user_id' => $id
            ]
        ]);
        foreach ($rolesAccess as $roleAccess) {
            if (($roleAccess['SECURITY'] != '')and($roleAccess['SECURITY'] != null)) {
                $res['rolesAccess'][] = json_decode($roleAccess['SECURITY'], true);
            }
        }
        $reports = $this->execSQL([
            'sql' => 'begin web_engine20.pkg_uac.user_reports(:p_user_id, :result); end;',
            'cursors' => ['result'],
            'in' => [
                'p_user_id' => $id
            ]
        ]);
        foreach ($reports as $report) {
            $res['reports'][] = $report['REPORT_ID'];
        }
        return $res;
    }
}
