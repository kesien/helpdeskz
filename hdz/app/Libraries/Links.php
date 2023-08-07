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
        $q = $this->linkModel->orderBy('default', 'desc')
            ->orderBy('created', 'desc')
            ->get();
        if ($q->resultID->num_rows == 0) {
            return null;
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }

    public function getFetcher()
    {
        $q = $this->linkModel->where('incoming_type', 'imap')
            ->orWhere('incoming_type', 'pop')
            ->get();
        if ($q->resultID->num_rows == 0) {
            return null;
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }

    public function getDefault()
    {
        return $this->getRow(['default' => 1]);
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
        return $this->getRow(['category_id' => $id]);
    }

    public function set_default($id)
    {
        $count = $this->linkModel->where('id', $id)
            ->countAllResults();
        if ($count == 0) {
            return false;
        }
        $this->linkModel->protect(false);
        $this->linkModel->where('default', '1')
            ->set('default', 0)
            ->update();
        $this->linkModel->update($id, [
            'default' => '1'
        ]);
        $this->linkModel->protect(true);
    }

    public function remove_link($id)
    {
        $this->linkModel->delete($id);
    }

    public function addLink()
    {
        $request = Services::request();
        $this->linkModel->protect(false);
        $this->linkModel->insert([
            'name' => $request->getPost('name'),
            'url' => $request->getPost('url'),
            'category_id' => $request->getPost('category_id'),
            'created' => time(),
        ]);
        $this->linkModel->protect(true);
        return $this->linkModel->getInsertID();
    }

    public function updateLink($id)
    {
        $request = Services::request();
        $this->linkModel->protect(false);
        $this->linkModel->update($id, [
            'name' => $request->getPost('name'),
            'email' => $request->getPost('url'),
            'category_id' => $request->getPost('category_id'),
            'last_update' => time(),
        ]);
        $this->linkModel->protect(true);
        return true;
    }
}