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
        $link_categories = Services::linkCategories();
        if ($this->request->getMethod() == 'get') {
            if (is_numeric($this->request->getGet('link_id'))) {
                return redirect()->to(current_url());
            }
        } elseif ($this->request->getMethod() == 'post') {
            if (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } elseif ($this->request->getPost('do') == 'remove') {
                $links->remove_link($this->request->getPost('link_id'));
                $this->session->setFlashdata('form_success', lang('Admin.settings.linkRemoved'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/links', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'list_links' => $links->getAll(),
            'list_link_categories' => $link_categories->getAll()
        ]);
    }

    public function edit($link_id)
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }

        $links = Services::links();
        $linkCategories = Services::linkCategories();
        if (!$link = $links->getByID($link_id)) {
            return redirect()->route('staff_links');
        }
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'name' => 'required',
                'url' => 'required|valid_url',
            ], [
                'name' => [
                    'required' => lang('Admin.error.enterLinkName')
                ],
                'url' => [
                    'required' => lang('Admin.error.enterLinkUrl'),
                    'valid_url' => lang('Admin.error.validUrl')
                ]
            ]);

            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $links->update(
                    $link->id,
                    $this->request->getPost('name'),
                    $this->request->getPost('url'),
                    $this->request->getPost('link_category_id'),
                );
                $this->session->setFlashdata('form_success', lang('Admin.links.linkUpdated'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/links_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'link' => $link,
            'list_link' => $links->getAll(),
            'list_link_categories' => $linkCategories->getAll()
        ]);
    }

    public function create()
    {
        if ($this->staff->getData('admin') != 1) {
            return redirect()->route('staff_dashboard');
        }

        $links = Services::links();
        $linkCategories = Services::linkCategories();
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'name' => 'required',
                'url' => 'required|valid_url',
            ], [
                'name' => [
                    'required' => lang('Admin.error.enterLinkName')
                ],
                'url' => [
                    'required' => lang('Admin.error.enterLinkUrl'),
                    'valid_url' => lang('Admin.error.validUrl')
                ]
            ]);
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $links->create($this->request->getPost('name'), $this->request->getPost('url'), $this->request->getPost('link_category_id'));
                $this->session->setFlashdata('form_success', lang('Admin.links.linkCreated'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/links_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'list_links' => $links->getAll(),
            'list_link_categories' => $linkCategories->getAll()
        ]);
    }

}