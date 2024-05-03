<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Controllers\Staff;


use App\Controllers\BaseController;
use App\Libraries\EmailRules;
use Config\Services;

class Departments extends BaseController
{
    public function manage()
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }
        $departments = Services::departments();
        if ($this->request->getMethod() == 'get') {
            if (is_numeric($this->request->getGet('department_id'))) {
                if ($this->request->getGet('action') == 'move_down') {
                    $departments->move_down($this->request->getGet('department_id'));
                } elseif ($this->request->getGet('action') == 'move_up') {
                    $departments->move_up($this->request->getGet('department_id'));
                }
                return redirect()->to(current_url());
            }
        } elseif ($this->request->getMethod() == 'post') {
            if (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } elseif ($this->request->getPost('do') == 'remove') {
                $departments->remove($this->request->getPost('department_id'));
                $this->session->setFlashdata('form_success', lang('Admin.tickets.departmentRemoved'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/departments', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'first_position' => $departments->getFirstPosition(),
            'last_position' => $departments->getLastPosition(),
            'list_departments' => $departments->getAll(),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function edit($department_id)
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }

        $departments = Services::departments();
        $rules = new EmailRules();
        if (!$department = $departments->getByID($department_id)) {
            return redirect()->route('staff_departments');
        }
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
            if ($this->request->getPost('rule_value') != '') {
                $validation->setRule('rule_type', lang('Admin.settings.ruleType'), 'required');
                $validation->setRule('rule_condition', lang('Admin.settings.ruleRule'), 'required');
                $validation->setRule('rule_value', lang('Admin.settings.ruleValue'), 'required');
                $validation->setRule('rule_action', lang('Admin.settings.ruleAction'), 'required');

                if($this->request->getPost('rule_action') == '0') {
                    $validation->setRule('rule_email', 'rule_email', 'required|valid_email', [
                        'required' => lang('Admin.form.rules.enterValidEmail'),
                        'valid_email' => lang('Admin.form.rules.enterValidEmail')
                    ]);
                }
                if($this->request->getPost('rule_action') == '1') {
                    $validation->setRule('rule_assign_to', lang('Admin.settings.ruleAssignTo'), 'required');
                }
                if($this->request->getPost('rule_action') == '2') {
                    $validation->setRule('priority', lang('Admin.settings.priority'), 'required');
                }
            }
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $departmentModel = new \App\Models\Departments();
                if ($this->request->getPost('position') == 'start') {
                    $firstPosition = $departments->getFirstPosition();
                    $departmentModel->increment('dep_order', 1);
                    $position = $firstPosition->dep_order;
                } elseif ($this->request->getPost('position') == 'last') {
                    $lastPosition = $departments->getLastPosition();
                    $position = $lastPosition->dep_order + 1;
                } elseif (is_numeric($this->request->getPost('position'))) {
                    if ($dep = $departments->getByID($this->request->getPost('position'))) {
                        $position = $dep->dep_order + 1;
                        $departmentModel->where('dep_order>', $dep->dep_order)
                            ->increment('dep_order', 1);
                    } else {
                        $position = $department->dep_order;
                    }
                } else {
                    $position = $department->dep_order;
                }
                $departments->update(
                    $department->id,
                    $this->request->getPost('name'),
                    $this->request->getPost('private'),
                    $position,
                    $this->request->getPost('default_agent') == '0' ? null : $this->request->getPost('default_agent')
                );
                if($this->request->getPost('rule_value') != '') {
                    $outcome = '';
                    $outcome_id = 0;
                    if ($this->request->getPost('rule_action') == '0') { 
                        $outcome = $this->request->getPost('rule_email');
                    }
                    if ($this->request->getPost('rule_action') == '1') {
                        $outcome_id = $this->request->getPost('rule_assign_to');
                        $outcome = Services::staff()->getAgentById($outcome_id)->fullname;
                    }
                    if ($this->request->getPost('rule_action') == '2') {
                        $outcome_id = $this->request->getPost('priority');
                        $outcome = Services::tickets()->getPriorityByID($outcome_id)->name;
                    }
                    $rules->create($department_id, 
                                   $this->request->getPost('rule_type'), 
                                   $this->request->getPost('rule_value'), 
                                   $this->request->getPost('rule_action'), 
                                   $outcome,
                                   $outcome_id,
                                   $this->request->getPost('rule_condition'));
                }
                $this->session->setFlashdata('form_success', lang('Admin.tickets.departmentUpdated'));
                return redirect()->to(current_url());
            }
        }
        if ($this->request->getPost('do') == 'removeFilter') {
            $rules->remove_filter($this->request->getPost('filter_id'));
            $this->session->setFlashdata('form_success', lang('Admin.form.rules.ruleDeleted'));
            return redirect()->to(current_url());
        }
        return view('staff/departments_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'department' => $department,
            'list_departments' => $departments->getAll(),
            'agents' => Services::staff()->getAgents(),
            'agents_for_department' => $departments->getAllAgentsForDepartment($department_id),
            'ticket_priorities' => Services::tickets()->getPriorities(),
            'rules' => $rules->getAllForDepartment($department_id),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function create()
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }

        $departments = Services::departments();
        $rules = new EmailRules();
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
            if ($this->request->getPost('rule_value') != '') {
                $validation->setRule('rule_type', lang('Admin.settings.ruleType'), 'required');
                $validation->setRule('rule_condition', lang('Admin.settings.ruleRule'), 'required');
                $validation->setRule('rule_value', lang('Admin.settings.ruleValue'), 'required');
                $validation->setRule('rule_action', lang('Admin.settings.ruleAction'), 'required');

                if($this->request->getPost('rule_action') == '0') {
                    $validation->setRule('rule_email', lang('Admin.settings.ruleEmail'), 'required|valid_email');
                }
                if($this->request->getPost('rule_action') == '1') {
                    $validation->setRule('rule_assign_to', lang('Admin.settings.ruleAssignTo'), 'required');
                }
                if($this->request->getPost('rule_action') == '2') {
                    $validation->setRule('priority', lang('Admin.settings.priority'), 'required');
                }
            }
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $department_id = $departments->create($this->request->getPost('name'), $this->request->getPost('private'), $this->request->getPost('position'));
                if($this->request->getPost('rule_value') != '') {
                    $outcome = '';
                    $outcome_id = 0;
                    if ($this->request->getPost('rule_action') == '0') { 
                        $outcome = $this->request->getPost('rule_email');
                    }
                    if ($this->request->getPost('rule_action') == '1') {
                        $outcome_id = $this->request->getPost('rule_assign_to');
                        $outcome = Services::staff()->getAgentById($outcome_id)->fullname;
                    }
                    if ($this->request->getPost('rule_action') == '2') {
                        $outcome_id = $this->request->getPost('priority');
                        $outcome = Services::tickets()->getPriorityByID($outcome_id)->name;
                    }
                    $rules->create($department_id, 
                                   $this->request->getPost('rule_type'), 
                                   $this->request->getPost('rule_value'), 
                                   $this->request->getPost('rule_action'), 
                                   $outcome,
                                   $outcome_id,
                                   $this->request->getPost('rule_condition'));
                }
                $this->session->setFlashdata('form_success', lang('Admin.tickets.departmentCreated'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/departments_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'list_departments' => $departments->getAll(),
            'ticket_priorities' => Services::tickets()->getPriorities(),
            'agents' => Services::staff()->getAgents(),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

}