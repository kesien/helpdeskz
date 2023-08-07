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

class Links extends BaseController
{
    public function manage()
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }
        $links = Services::links();
        if ($this->request->getMethod() == 'get') {
            if (is_numeric($this->request->getGet('link_id'))) {
                if ($this->request->getGet('action') == 'move_down') {
                    $links->move_down($this->request->getGet('link_id'));
                } elseif ($this->request->getGet('action') == 'move_up') {
                    $links->move_up($this->request->getGet('link_id'));
                }
                return redirect()->to(current_url());
            }
        } elseif ($this->request->getMethod() == 'post') {
            if (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } elseif ($this->request->getPost('do') == 'remove') {
                $links->remove($this->request->getPost('link_id'));
                $this->session->setFlashdata('form_success', lang('Admin.settings.linkRemoved'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/links', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'first_position' => $links->getFirstPosition(),
            'last_position' => $links->getLastPosition(),
            'list_departments' => $links->getAll()
        ]);
    }

    public function edit($link_id)
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }

        $links = Services::links();
        if (!$link = $links->getByID($link_id)) {
            return redirect()->route('staff_departments');
        }
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'name' => 'required',
                'url' => 'required|valid_url',
                'category_id' => 'required'
            ], [
                'name' => [
                    'required' => lang('Admin.error.enterLinkName')
                ],
                'url' => [
                    'required' => lang('Admin.error.enterLinkUrl'),
                    'in_list' => lang('Admin.error.validUrl')
                ],
                'category_id' => [
                    'required' => lang('Admin.error.selectCategory')
                ]
            ]);

            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $linkModel = new \App\Models\Link();
                if ($this->request->getPost('position') == 'start') {
                    $firstPosition = $links->getFirstPosition();
                    $linkModel->increment('link_order', 1);
                    $position = $firstPosition->link_order;
                } elseif ($this->request->getPost('position') == 'last') {
                    $lastPosition = $links->getLastPosition();
                    $position = $lastPosition->link_order + 1;
                } elseif (is_numeric($this->request->getPost('position'))) {
                    if ($link = $links->getByID($this->request->getPost('position'))) {
                        $position = $link->link_order + 1;
                        $linkModel->where('link_order>', $link->link_order)
                            ->increment('link_order', 1);
                    } else {
                        $position = $link->dep_order;
                    }
                } else {
                    $position = $link->dep_order;
                }
                $links->update(
                    $link->id,
                    $this->request->getPost('name'),
                    $this->request->getPost('private'),
                    $position
                );
                $this->session->setFlashdata('form_success', lang('Admin.links.linkUpdated'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/departments_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'department' => $link,
            'list_departments' => $links->getAll()
        ]);
    }

    public function create()
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }

        $departments = Services::departments();
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'name' => 'required',
                'private' => 'required|in_list[0,1]'
            ], [
                'name' => [
                    'required' => lang('Admin.error.enterDepartmentName')
                ],
                'private' => [
                    'required' => lang('Admin.error.selectDepartmentType'),
                    'in_list' => lang('Admin.error.selectDepartmentType')
                ],
            ]);
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $departments->create($this->request->getPost('name'), $this->request->getPost('private'), $this->request->getPost('position'));
                $this->session->setFlashdata('form_success', lang('Admin.tickets.departmentCreated'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/departments_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'list_departments' => $departments->getAll()
        ]);
    }

}