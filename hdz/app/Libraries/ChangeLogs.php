<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Libraries;


use App\Models\ChangeLog;

class ChangeLogs
{
    protected $changeLogModel;
    public function __construct()
    {
        $this->changeLogModel = new \App\Models\ChangeLog();
    }
    public function getAll($ticket_id)
    {
        $q = $this->changeLogModel
            ->where('ticket_id', $ticket_id)
            ->orderBy('date', 'desc')
            ->get();
        if ($q->resultID->num_rows == 0) {
            return null;
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }

    public function getByID($id)
    {
        if ($data = $this->changeLogModel->find($id)) {
            return $data;
        }
        return null;
    }

    public function create($staff_id, $ticket_id, $staff_name, $action = "")
    {
        $this->changeLogModel->protect(false);
        $this->changeLogModel->insert([
            'staff_id' => $staff_id,
            'ticket_id' => $ticket_id,
            'date' => time(),
            'staff_name' => esc($staff_name),
            'action' => esc($action)
        ]);
        $this->changeLogModel->protect(true);
        return $this->changeLogModel->getInsertID();
    }
}