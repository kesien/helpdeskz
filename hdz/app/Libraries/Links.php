<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Libraries;


use App\Models\Link;
use Config\Services;

class Links
{
    protected $linkModel;
    public function __construct()
    {
        $this->linkModel = new \App\Models\Link();
    }
    public function getAll()
    {
        $q = $this->linkModel->orderBy('name', 'desc')
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
        if ($data = $this->linkModel->find($id)) {
            return $data;
        }
        return null;
    }

    public function getRow($where = array())
    {
        $q = $this->linkModel->where($where)->get(1);
        if ($q->resultID->num_rows == 0) {
            return null;
        }
        return $q->getRow();
    }

    public function getByCategoryId($id)
    {
        return $this->getRow(['link_category_id' => $id]);
    }

    public function remove_link($id)
    {
        $this->linkModel->delete($id);
    }

    public function create($name, $url, $link_category_id = null)
    {
        $this->linkModel->protect(false);
        $this->linkModel->insert([
            'name' => esc($name),
            'url' => esc($url),
            'link_category_id' => $link_category_id
        ]);
        $this->linkModel->protect(true);
        return $this->linkModel->getInsertID();
    }

    public function update($id, $name, $url, $link_category_id)
    {
        $this->linkModel->protect(false);
        $this->linkModel->update($id, [
            'name' => esc($name),
            'url' => esc($url),
            'link_category_id' => $link_category_id
        ]);
        $this->linkModel->protect(true);
    }
}