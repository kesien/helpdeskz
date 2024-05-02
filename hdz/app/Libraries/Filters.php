<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Libraries;


use App\Models\Filter;

class Filters
{
    protected $filterModel;
    public function __construct()
    {
        $this->filterModel = new Filter();
    }
    public function getAllForEmail($emailId)
    {
        $q = $this->filterModel
            ->where(['email_id' => $emailId])
            ->orderBy('id', 'desc')
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

    public function create($email_id, $type, $value, $outcome, $condition, $description = null)
    {
        $this->filterModel->protect(false);
        $this->filterModel->insert([
            'email_id' => esc($email_id),
            'type' => esc($type),
            'condition' => esc($condition),
            'value' => esc($value),
            'outcome' => esc($outcome),
            'description' => esc($description)
        ]);
        $this->filterModel->protect(true);
        return $this->filterModel->getInsertID();
    }

    public function update($id, $type, $condition, $value, $outcome, $description = null)
    {
        $this->filterModel->protect(false);
        $this->filterModel->update($id, [
            'type' => esc($type),
            'condition' => esc($condition),
            'value' => esc($value),
            'outcome' => esc($outcome),
            'description' => $description
        ]);
        $this->filterModel->protect(true);
    }
}