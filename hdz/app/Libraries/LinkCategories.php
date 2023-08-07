<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Libraries;

use App\Models\LinkCategory;
use Config\Services;

class LinkCategories
{
    protected $linkCategoriesModel;
    private $all_categories;
    public function __construct()
    {
        $this->linkCategoriesModel = new \App\Models\LinkCategory();
    }

    public function getAll()
    {
        $q = $this->linkCategoriesModel->orderBy('dep_order', 'asc')
            ->get();
        if ($q->resultID->num_rows == 0) {
            return null;
        }
        $r = $q->getResult();
        $q->freeResult();
        return $r;
    }

    // public function getList()
    // {
    //     $q = $this->linkCategoriesModel->orderBy('dep_order', 'asc')
    //         ->get();
    //     if ($q->resultID->num_rows == 0) {
    //         return null;
    //     }
    //     $result = $q->getResult();
    //     $q->freeResult();
    //     return $result;
    // }

    public function getByID($id)
    {
        if ($link_category = $this->linkCategoriesModel->find($id)) {
            return $link_category;
        }
        return null;
    }

    public function count()
    {
        return $this->linkCategoriesModel->countAll();
    }

    public function move_up($id)
    {
        if (!$link_category = $this->getByID($id)) {
            return false;
        }
        $q = $this->linkCategoriesModel->select('id, dep_order')
            ->where('dep_order<', $link_category->dep_order)
            ->orderBy('dep_order', 'desc')
            ->get(1);
        if ($q->resultID->num_rows > 0) {
            $prev = $q->getRow();
            $this->linkCategoriesModel->protect(false);
            $this->linkCategoriesModel->update($link_category->id, [
                'dep_order' => $prev->dep_order
            ]);
            $this->linkCategoriesModel->update($prev->id, [
                'dep_order' => $link_category->dep_order
            ]);
            $this->linkCategoriesModel->protect(true);
        }
        return true;
    }

    public function move_down($id)
    {
        if (!$link_category = $this->getByID($id)) {
            return false;
        }
        $q = $this->linkCategoriesModel->select('id, dep_order')
            ->where('dep_order>', $link_category->dep_order)
            ->orderBy('dep_order', 'asc')
            ->get(1);
        if ($q->resultID->num_rows > 0) {
            $next = $q->getRow();
            $this->linkCategoriesModel->protect(false);
            $this->linkCategoriesModel->update($link_category->id, [
                'dep_order' => $next->dep_order
            ]);
            $this->linkCategoriesModel->update($next->id, [
                'dep_order' => $link_category->dep_order
            ]);
            $this->linkCategoriesModel->protect(true);
        }
        return true;
    }

    public function isValid($id)
    {
        $q = $this->linkCategoriesModel->where('id', $id)
            ->countAllResults();
        return ($q == 0) ? false : true;
    }



    public function getFirstPosition()
    {
        $q = $this->linkCategoriesModel->select('id, dep_order')
            ->orderBy('dep_order', 'asc')
            ->get(1);
        if ($q->resultID->num_rows == 0) {
            return null;
        }
        return $q->getRow();
    }

    public function getLastPosition()
    {
        $q = $this->linkCategoriesModel->select('id, dep_order')
            ->orderBy('dep_order', 'desc')
            ->get(1);
        if ($q->resultID->num_rows == 0) {
            return null;
        }
        return $q->getRow();
    }

    public function countLinks($link_category_id)
    {
        $linkModel = new \App\Models\Link();
        return $linkModel->where('link_category_id', $link_category_id)
            ->countAllResults();
    }

    public function remove($id)
    {
        $linkModel = new \App\Models\Link();
        $links = Services::links();
        $q = $linkModel->select('id')
            ->where('link_category_id', $id)
            ->get();
        if ($q->resultID->num_rows > 0) {
            foreach ($q->getResult() as $item) {
                $links->remove_link($item->id);
            }
            $q->freeResult();
        }
        $this->linkCategoriesModel->delete($id);
        return true;
    }

    public function create($name, $description, $position = 'start')
    {
        $dep_order = 1;
        if ($position == 'start') {
            if ($r = $this->getFirstPosition()) {
                $dep_order = $r->dep_order;
                $this->linkCategoriesModel->increment('dep_order', 1);
            }
        } elseif (is_numeric($position)) {
            if ($r = $this->getByID($position)) {
                $dep_order = $r->dep_order + 1;
                $this->linkCategoriesModel->where('dep_order>', $r->dep_order)
                    ->increment('dep_order', 1);
            }
        } else {
            if ($r = $this->getLastPosition()) {
                $dep_order = $r->dep_order + 1;
            }
        }
        $this->linkCategoriesModel->protect(false);
        $this->linkCategoriesModel->insert([
            'dep_order' => $dep_order,
            'name' => esc($name),
            'description' => esc($description)
        ]);
        $this->linkCategoriesModel->protect(true);
        return $this->linkCategoriesModel->getInsertID();
    }

    public function update($id, $name, $description, $position)
    {
        $this->linkCategoriesModel->protect(false);
        $this->linkCategoriesModel->update($id, [
            'name' => esc($name),
            'description' => esc($description),
            'dep_order' => $position
        ]);
        $this->linkCategoriesModel->protect(true);
    }
}