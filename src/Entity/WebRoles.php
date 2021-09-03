<?php
namespace App\Entity;

use App\Services\MyACP\MyACPEntity;

class WebRoles extends MyACPEntity
{
    public function access($id)
    {
        return $this->execSQL([
            'sql' => 'begin web_engine20.pkg_uac.role_access(:role_id, :config, :name, :result); end;',
            'cursors' => 'result',
            'in' => ['role_id' => $id],
            'out' => ['name'],
            'lobs' => [
                [
                    'name' => 'config',
                    'type' => OCI_B_CLOB
                ]
            ]
        ]);
    }
}
