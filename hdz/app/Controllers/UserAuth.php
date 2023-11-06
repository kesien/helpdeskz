<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */

namespace App\Controllers;


use App\Libraries\reCAPTCHA;
use Config\Services;

class UserAuth extends BaseController
{
    public function login()
    {
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules(
                [
                    'email' => 'required|valid_email',
                    'password' => 'required',
                ]
            );

            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = lang('Client.error.invalidEmailPassword');
            } elseif (
                !$client_data = $this->client->getRow([
                    'email' => $this->request->getPost('email'),
                    'status' => 1
                ])
            ) {
                $error_msg = lang('Client.error.invalidEmailPassword');
            } else {
                if (!password_verify($this->request->getPost('password'), $client_data->password)) {
                    $error_msg = lang('Client.error.invalidEmailPassword');
                } else {
                    $this->client->login($client_data->id, $client_data->password);
                    return redirect()->route('view_tickets');
                }
            }

        }
        return view('client/login', [
            'error_msg' => isset($error_msg) ? $error_msg : null
        ]);
    }

    public function forgot()
    {
        $reCAPTCHA = new reCAPTCHA();
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRule('email', 'email', 'required|valid_email');
            if (!$reCAPTCHA->validate()) {
                $error_msg = lang('Client.error.invalidCaptcha');
            } elseif ($validation->withRequest($this->request)->run() == false) {
                $error_msg = lang('Client.error.enterValidEmail');
            } elseif (!$client_data = $this->client->getRow(['email' => $this->request->getPost('email')])) {
                $error_msg = lang('Client.error.emailNotFound');
            } else {
                $this->client->recoverPassword($client_data);
                $this->session->setFlashdata('form_success', lang('Client.login.passwordSent'));
                return redirect()->route('forgot_password');
            }
        }
        return view('client/forgot', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'recaptcha' => $reCAPTCHA->display()
        ]);
    }

    public function profile()
    {
        $validation = Services::validation();
        if ($this->request->getPost('do') == 'general') {
            $validation->setRule('fullname', 'fullname', 'required', [
                'required' => lang('Client.error.enterFullName')
            ]);
            if ($this->request->getPost('email') != $this->client->getData('email')) {
                $validation->setRule('email', 'email', 'required|valid_email|is_unique[users.email]', [
                    'required' => lang('Client.error.enterValidEmail'),
                    'valid_email' => lang('Client.error.enterValidEmail'),
                    'is_unique' => lang('Client.error.emailUsed')
                ]);
            }
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } else {
                $timezone_user = in_array($this->request->getPost('timezone'), timezone_identifiers_list()) ? $this->request->getPost('timezone') : '';
                $this->client->update([
                    'email' => $this->request->getPost('email'),
                    'fullname' => esc($this->request->getPost('fullname')),
                    'timezone' => $timezone_user
                ]);
                $this->session->setFlashdata('form_success', lang('Client.account.profileUpdated'));
                return redirect()->route('profile');
            }
        } elseif ($this->request->getPost('do') == 'password') {
            $validation->setRules([
                'current_password' => 'required',
                'new_password' => 'required',
                'new_password2' => 'matches[new_password]'
            ], [
                'current_password' => [
                    'required' => lang('Client.error.enterExistingPassword')
                ],
                'new_password' => [
                    'required' => lang('Client.error.enterNewPassword')
                ],
                'new_password2' => [
                    'matches' => lang('Client.error.passwordsNotMatches')
                ]
            ]);
            if ($validation->withRequest($this->request)->run() == FALSE) {
                $error_msg = $validation->listErrors();
            } elseif (!password_verify($this->request->getPost('current_password'), $this->client->getData('password'))) {
                $error_msg = lang('Client.error.wrongExistingPassword');
            } else {
                $password = password_hash($this->request->getPost('new_password'), PASSWORD_BCRYPT);
                $this->client->update([
                    'password' => $password
                ]);
                $this->client->createSession($this->client->getData('id'), $password);
                $this->session->setFlashdata('form_success', lang('Client.account.passwordUpdated'));
                return redirect()->route('profile');
            }
        }
        return view('client/profile', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function logout()
    {
        return $this->client->logout();
    }

    private function findUncategorizedLinks($links)
    {
        $categoryLinks = [];
        foreach ($links as $link) {
            if (empty($link->link_category_id)) {
                $categoryLinks[] = $link;
            }
        }
        return $categoryLinks;
    }

    private function getLinkCategoryMap()
    {
        $links = Services::links();
        $link_categories = Services::linkCategories();
        $categoryLinksMap = [];
        $allLinks = $links->getAll();
        $allCategories = $link_categories->getAll();

        if (!isset($allLinks) || count($allLinks) == 0) {
            return $categoryLinksMap;
        }

        // Categorize links based on their link_category_id if there are categories
        if (!empty($allCategories)) {
            foreach ($allLinks as $link) {
                $categoryId = $link->link_category_id;
                if (!isset($categoryLinksMap[$categoryId])) {
                    $categoryLinksMap[$categoryId] = [];
                }
                $categoryLinksMap[$categoryId][] = $link;
            }
        }

        // Retrieve uncategorized links
        $uncategorizedLinks = $this->findUncategorizedLinks($allLinks);
        if (!empty($uncategorizedLinks)) {
            $categoryLinksMap["Uncategorized"] = $uncategorizedLinks;
        }

        // Retrieve category names if there are categories
        $categoryNames = [];
        if (!empty($allCategories)) {
            foreach ($allCategories as $category) {
                $categoryNames[$category->id] = $category->name;
            }
        }

        // Transform the category IDs into category names
        $transformedCategoryLinksMap = [];
        foreach ($categoryLinksMap as $categoryId => $links) {
            $categoryName = isset($categoryNames[$categoryId]) ? $categoryNames[$categoryId] : "Uncategorized";
            $transformedCategoryLinksMap[$categoryName] = $links;
        }

        return $transformedCategoryLinksMap;
    }
}