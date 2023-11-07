<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Controllers\Staff;


use App\Controllers\BaseController;
use Config\Services;

class LinkCategories extends BaseController
{
    public function manage()
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }
        $link_categories = Services::linkCategories();
        if ($this->request->getMethod() == 'get') {
            if (is_numeric($this->request->getGet('link_category_id'))) {
                if ($this->request->getGet('action') == 'move_down') {
                    $link_categories->move_down($this->request->getGet('link_category_id'));
                } elseif ($this->request->getGet('action') == 'move_up') {
                    $link_categories->move_up($this->request->getGet('link_category_id'));
                }
                return redirect()->to(current_url());
            }
        } elseif ($this->request->getMethod() == 'post') {
            if (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } elseif ($this->request->getPost('do') == 'remove') {
                $link_categories->remove($this->request->getPost('link_category_id'));
                $this->session->setFlashdata('form_success', lang('Admin.links.linkCategoryRemoved'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/link_category', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'first_position' => $link_categories->getFirstPosition(),
            'last_position' => $link_categories->getLastPosition(),
            'list_link_categories' => $link_categories->getAll(),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function edit($link_category_id)
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }

        $link_categories = Services::linkCategories();
        if (!$link_category = $link_categories->getByID($link_category_id)) {
            return redirect()->route('staff_link-categories');
        }
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'name' => 'required'
            ], [
                'name' => [
                    'required' => lang('Admin.error.enterCategoryName')
                ]
            ]);

            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $link_category_model = new \App\Models\LinkCategory();
                if ($this->request->getPost('position') == 'start') {
                    $firstPosition = $link_categories->getFirstPosition();
                    $link_category_model->increment('dep_order', 1);
                    $position = $firstPosition->dep_order;
                } elseif ($this->request->getPost('position') == 'last') {
                    $lastPosition = $link_categories->getLastPosition();
                    $position = $lastPosition->dep_order + 1;
                } elseif (is_numeric($this->request->getPost('position'))) {
                    if ($dep = $link_categories->getByID($this->request->getPost('position'))) {
                        $position = $dep->dep_order + 1;
                        $link_category_model->where('dep_order>', $dep->dep_order)
                            ->increment('dep_order', 1);
                    } else {
                        $position = $link_category->dep_order;
                    }
                } else {
                    $position = $link_category->dep_order;
                }
                $link_categories->update(
                    $link_category->id,
                    $this->request->getPost('name'),
                    $this->request->getPost('description'),
                    $position
                );
                $this->session->setFlashdata('form_success', lang('Admin.links.categoryUpdated'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/link_category_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'link_category' => $link_category,
            'list_link_categories' => $link_categories->getAll(),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function create()
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }

        $link_categories = Services::linkCategories();
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'name' => 'required'
            ], [
                'name' => [
                    'required' => lang('Admin.error.enterCategoryName')
                ]
            ]);
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $link_categories->create($this->request->getPost('name'), $this->request->getPost('description'), $this->request->getPost('position'));
                $this->session->setFlashdata('form_success', lang('Admin.links.categoryCreated'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/link_category_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'list_link_categories' => $link_categories->getAll(),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

}