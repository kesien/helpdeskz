<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Libraries;


use Config\Services;

class Departments
{
    protected $departmentsModel;
    private $public_departments;
    private $all_departments;
    public function __construct()
    {
        $this->departmentsModel = new \App\Models\Departments();
    }
    public function getPublic()
    {
        if(!$this->public_departments){
            $this->public_departments = $this->getList(true);
        }
        return $this->public_departments;
    }
    public function getAll()
    {
        if(!$this->all_departments){
            $this->all_departments = $this->getList(false);
        }
        return $this->all_departments;
    }

    public function getList($onlyPublic=true)
    {
        if($onlyPublic){
            $this->departmentsModel->where('private', 0);
        }
        $q = $this->departmentsModel->orderBy('dep_order','asc')
            ->get();
        if($q->resultID->num_rows == 0){
            return null;
        }
        $result = $q->getResult();
        $q->freeResult();
        return $result;
    }

    public function getByID($id)
    {
        if($department = $this->departmentsModel->find($id)){
            return $department;
        }
        return null;
    }

    public function count()
    {
        return $this->departmentsModel->countAll();
    }

    public function move_up($id)
    {
        if(!$department = $this->getByID($id)){
            return false;
        }
        $q = $this->departmentsModel->select('id, dep_order')
            ->where('dep_order<', $department->dep_order)
            ->orderBy('dep_order','desc')
            ->get(1);
        if($q->resultID->num_rows > 0){
            $prev = $q->getRow();
            $this->departmentsModel->protect(false);
            $this->departmentsModel->update($department->id, [
                'dep_order' => $prev->dep_order
            ]);
            $this->departmentsModel->update($prev->id, [
                'dep_order' => $department->dep_order
            ]);
            $this->departmentsModel->protect(true);
        }
        return true;
    }

    public function move_down($id)
    {
        if(!$department = $this->getByID($id)){
            return false;
        }
        $q = $this->departmentsModel->select('id, dep_order')
            ->where('dep_order>', $department->dep_order)
            ->orderBy('dep_order','asc')
            ->get(1);
        if($q->resultID->num_rows > 0){
            $next = $q->getRow();
            $this->departmentsModel->protect(false);
            $this->departmentsModel->update($department->id, [
                'dep_order' => $next->dep_order
            ]);
            $this->departmentsModel->update($next->id, [
                'dep_order' => $department->dep_order
            ]);
            $this->departmentsModel->protect(true);
        }
        return true;
    }

    public function isValid($id)
    {
        $q = $this->departmentsModel->where('id', $id)
            ->countAllResults();
        return ($q == 0) ? false : true;
    }



    public function getFirstPosition()
    {
        $q = $this->departmentsModel->select('id, dep_order')
            ->orderBy('dep_order','asc')
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }

    public function getLastPosition()
    {
        $q = $this->departmentsModel->select('id, dep_order')
            ->orderBy('dep_order','desc')
            ->get(1);
        if($q->resultID->num_rows == 0){
            return null;
        }
        return $q->getRow();
    }

    public function countTickets($department_id)
    {
        $ticketModel = new \App\Models\Tickets();
        return $ticketModel->where('department_id', $department_id)
            ->countAllResults();
    }

    public function countAgents($department_id)
    {
        $staffModel = new \App\Models\Staff();
        return $staffModel->like('department', '"'.$department_id.'"')
            ->countAllResults();
    }

    public function getAllAgentsForDepartment($department_id) {
        $staffModel = new \App\Models\Staff();
        $q = $staffModel
            ->like('department', '"'.$department_id.'"')
            ->get();

        if($q->resultID->num_rows == 0){
            return [];
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }

    public function getAllActiveAgentsForDepartment($department_id) {
        $staffModel = new \App\Models\Staff();
        $q = $staffModel
            ->like('department', '"'.$department_id.'"')
            ->get();

        if($q->resultID->num_rows == 0){
            return [];
        }
        $r = $q->getResult();
        $q->freeResult();
        $result = array();
        foreach ($r as $agent) {
            $states = isset($agent->state) ? unserialize($agent->state) : array();
            if (array_key_exists($department_id, $states) && $states[$department_id] == "1") {
                array_push($result, $agent);
            }
        }
        return $result;
    }

    public function getDefaultAgentForDepartment($department_id) {
        $this->departmentsModel->where('departments.id', $department_id);
        $q = $this->departmentsModel->select('departments.*, a.fullname as agent_name, a.id as agent_id')->join('staff as a', 'a.id=departments.default_agent_id')
            ->get(1);
        if ($q->resultID && $q->resultID->num_rows == 0) {
            return null;
        }
        return $q->getRow();
    }

    public function remove($id)
    {
        $ticketModel = new \App\Models\Tickets();
        $tickets = Services::tickets();
        $q = $ticketModel->select('id')
            ->where('department_id', $id)
            ->get();
        if($q->resultID->num_rows > 0){
            foreach ($q->getResult() as $item){
                $tickets->deleteTicket($item->id);
            }
            $q->freeResult();
        }
        $this->departmentsModel->delete($id);
        return true;
    }

    public function create($name, $private=0, $position='start'){
        $dep_order = 1;
        if($position == 'start'){
            if($r = $this->getFirstPosition()){
                $dep_order = $r->dep_order;
                $this->departmentsModel->increment('dep_order', 1);
            }
        }elseif (is_numeric($position)){
            if($r = $this->getByID($position)){
                $dep_order = $r->dep_order+1;
                $this->departmentsModel->where('dep_order>', $r->dep_order)
                    ->increment('dep_order', 1);
            }
        }else{
            if($r = $this->getLastPosition()){
                $dep_order = $r->dep_order+1;
            }
        }
        $this->departmentsModel->protect(false);
        $this->departmentsModel->insert([
            'dep_order' => $dep_order,
            'name' => esc($name),
            'private' => $private
        ]);
        $this->departmentsModel->protect(true);
        return $this->departmentsModel->getInsertID();
    }

    public function update($id, $name, $private, $position, $default_agent = null)
    {
        $this->departmentsModel->protect(false);
        $this->departmentsModel->update($id, [
            'name' => esc($name),
            'private' => $private,
            'dep_order' => $position,
            'default_agent_id' => $default_agent
        ]);
        if (isset($default_agent)) {
            $staff = Services::staff();
            $agent = $staff->getAgentById($default_agent);
            $states = isset($agent->state) ? unserialize($agent->state) : array();
            if (!array_key_exists($id, $states) || $states[$id] != "1") {
                $states[$id] = "1";
            }
            $staff->updateAgent($agent->id, $agent->fullname, $agent->username, $agent->email, '', $agent->admin, unserialize($agent->department), $agent->active, $states);
        }
        $this->departmentsModel->protect(true);
    }
}