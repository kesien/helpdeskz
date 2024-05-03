<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Libraries;


use App\Models\EmailRule;

class EmailRules
{
    protected $filterModel;
    public function __construct()
    {
        $this->filterModel = new EmailRule();
    }
    public function getAllForDepartment($departmentId)
    {
        $q = $this->filterModel
            ->where(['department_id' => $departmentId])
            ->orderBy('id', 'desc')
            ->get();
        if ($q->resultID->num_rows == 0) {
            return [];
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }

    public function getByID($id)
    {
        if ($data = $this->filterModel->find($id)) {
            return $data;
        }
        return null;
    }

    public function getRow($where = array())
    {
        $q = $this->filterModel->where($where)->get(1);
        if ($q->resultID->num_rows == 0) {
            return null;
        }
        return $q->getRow();
    }

    public function remove_filter($id)
    {
        $this->filterModel->delete($id);
    }

    public function create($department_id, $type, $value, $action, $outcome, $rule_outcome_id, $condition)
    {
        $this->filterModel->protect(false);
        $this->filterModel->insert([
            'department_id' => esc($department_id),
            'type' => esc($type),
            'rule_condition' => esc($condition),
            'rule_value' => esc($value),
            'rule_action' => esc($action),
            'outcome_id' => esc($rule_outcome_id),
            'outcome' => esc($outcome),
        ]);
        $this->filterModel->protect(true);
        return $this->filterModel->getInsertID();
    }

    public function checkRulesForDepartment($department_id) {
        $rules = $this->getAllForDepartment($department_id);
        return isset($rules) ? true : false;
    }
}