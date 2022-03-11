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
}
