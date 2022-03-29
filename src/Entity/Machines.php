<?php
namespace App\Entity;

use App\Services\MyACP\MyACPEntity;

class Machines extends MyACPEntity
{
    public function statuses()
    {
        return $this->execSQL([
            'sql' => 'begin :result := acp.loc_stmonitor.get_group; end;',
            'cursors' => ['result'],
        ]);
    }

    public function status($id)
    {
        $res = $this->execSQL([
            'sql' => 'begin acp.web2_machines.status(:id, :status, :egm_status);end;',
            'cursors' => [
                'status',
                'egm_status'
            ],
            'in' => [
                'id' => $id
            ]
        ]);
        $error = $this->myACP->getError('', true, 1);
        if ($error != '') {
            return ['error' => $error];
        }
        if (count($res['status']) == 0) {
            return ['error' => 'machine.errors.no_such_machine'];
        }
        $res['params'] = $this->execSQL([
            'sql' => 'begin acp.web2_machines.Most_important_params(:id, :result);end;',
            'cursors' => ['result'],
            'in' => [
                'id' => $id
            ]
        ]);
        $error = $this->myACP->getError('', true, 1);
        if ($error != '') {
            return ['error' => $error];
        }
        return $res;
    }
}
