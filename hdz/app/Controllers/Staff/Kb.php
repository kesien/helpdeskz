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

class Kb extends BaseController
{
    /*
     * Categories
     */
    public function categories()
    {
        $kb = Services::kb();
        if ($this->request->getGet('action') == 'move_down' && is_numeric($this->request->getGet('id'))) {
            $kb->moveCategory($this->request->getGet('id'), false);
            return redirect()->to(current_url());
        } elseif ($this->request->getGet('action') == 'move_up' && is_numeric($this->request->getGet('id'))) {
            $kb->moveCategory($this->request->getGet('id'), true);
            return redirect()->to(current_url());
        }
        if ($this->request->getPost('do') == 'remove') {
            if (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $kb->removeCategory($this->request->getPost('category_id'));
                $this->session->setFlashdata('form_success', lang('Admin.kb.categoryRemoved'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/kb_categories', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => ($this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null),
            'kb_list' => $kb->getChildren(0, false, 0, ' - - - '),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function newCategory()
    {
        $kb = Services::kb();
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'name' => 'required',
                'parent' => 'required|is_natural',
                'public' => 'required|in_list[0,1]',
            ], [
                'name' => [
                    'required' => lang('Admin.error.enterCategoryName')
                ],
                'parent' => [
                    'required' => lang('Admin.error.selectParentCategory'),
                    'is_natural' => lang('Admin.error.selectParentCategory'),
                ],
                'public' => [
                    'required' => lang('Admin.error.selectCategoryType'),
                    'in_list' => lang('Admin.error.selectCategoryType'),
                ]
            ]);
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $kb->insertCategory($this->request->getPost('name'), $this->request->getPost('parent'), $this->request->getPost('public'), $this->request->getPost('agent'));
                $this->session->setFlashdata('form_success', lang('Admin.kb.categoryCreated'));
                return redirect()->to(current_url());
            }
        }
        return view('staff/kb_categories_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'kb_list' => $kb->getChildren(0, false, 0, ' - - - '),
            'agents' => Services::staff()->getAgents(),
            'parent' => (is_numeric($this->request->getGet('parent')) ? $this->request->getGet('parent') : 0),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function viewCategory($category_id)
    {
        $kb = Services::kb();
        $category = $kb->getCategory($category_id, false);
        if (!$category) {
            return redirect()->to('staff_kb_categories');
        }
        $articles = $kb->getArticles($category_id);

        return view('staff/kb_view_category', [
            'category' => $category,
            'articles' => $articles,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function viewArticle($article_id)
    {
        $kb = Services::kb();
        if (!$article = $kb->getArticle($article_id)) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($article->category != 0) {
            if (!in_array($article->category, $kb->publicCategories())) {
                throw PageNotFoundException::forPageNotFound();
            }
            if (!$category = $kb->getCategory($article->category)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        if ($this->request->getGet('download')) {
            $attachments = Services::attachments();
            if (!$file = $attachments->getRow(['id' => $this->request->getGet('download'), 'article_id' => $article_id])) {
                return view('client/error', [
                    'title' => lang('Client.error.fileNotFound'),
                    'body' => lang('Client.error.fileNotFoundMsg'),
                    'footer' => ''
                ]);
            }
            return $attachments->download($file);
        }
        $kb->addView($article_id);

        return view('staff/kb_view_article', [
            'article' => $article,
            'category' => $category,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function editCategory($category_id)
    {
        $kb = Services::kb();
        if (!$category = $kb->getCategory($category_id, false)) {
            return redirect()->to('staff_kb_categories');
        }

        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'name' => 'required',
                'parent' => 'required|is_natural',
                'public' => 'required|in_list[0,1]',
            ], [
                'name' => [
                    'required' => lang('Admin.error.enterCategoryName')
                ],
                'parent' => [
                    'required' => lang('Admin.error.selectParentCategory'),
                    'is_natural' => lang('Admin.error.selectParentCategory'),
                ],
                'public' => [
                    'required' => lang('Admin.error.selectCategoryType'),
                    'in_list' => lang('Admin.error.selectCategoryType'),
                ]
            ]);
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $agents = $this->request->getPost('agent');
                $agents_assigned = isset($agents) ? serialize($agents) : null;
                $kb->updateCategory([
                    'name' => esc($this->request->getPost('name')),
                    'parent' => $this->request->getPost('parent'),
                    'public' => $this->request->getPost('public'),
                    'agents_assigned' => $agents_assigned
                ], $category->id);
                $this->session->setFlashdata('form_success', lang('Admin.kb.categoryUpdated'));
                return redirect()->to(current_url());
            }
        }

        return view('staff/kb_categories_form', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'category' => $category,
            'agents' => Services::staff()->getAgents(),
            'kb_list' => $kb->getChildren(0, false, 0, ' - - - '),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    /*
     * Articles
     */
    public function articles($category = 0)
    {
        $kb = Services::kb();
        if ($this->request->getPost('do') == 'remove') {
            if (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $kb->removeArticle($this->request->getPost('article_id'));
                $this->session->setFlashdata('form_success', 'Article has been removed.');
                return redirect()->to(current_url());
            }
        }
        $pagination = $kb->articlesPagination($category);
        return view('staff/kb_articles', [
            'error_msg' => isset($error_msg) ? $error_msg : null,
            'success_msg' => $this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null,
            'articles_result' => $pagination['result'],
            'pager' => $pagination['pager'],
            'category' => $category,
            'kb_list' => $kb->getChildren(0, false, 0, ' - - - '),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function newArticle()
    {
        $kb = Services::kb();
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'title' => 'required',
                'category_id' => 'required|is_natural_no_zero',
                'public' => 'required|in_list[0,1]',
                'content' => 'required'
            ], [
                'title' => [
                    'required' => lang('Admin.error.enterTitle'),
                ],
                'category_id' => [
                    'required' => lang('Admin.error.selectCategory'),
                    'is_natural_no_zero' => lang('Admin.error.selectCategory'),
                ],
                'public' => [
                    'required' => lang('Admin.error.selectArticleType'),
                    'in_list' => lang('Admin.error.selectArticleType'),
                ],
                'content' => [
                    'required' => lang('Admin.error.enterContent')
                ]
            ]);
            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $kb->addArticle(
                    $this->request->getPost('title'),
                    $this->request->getPost('content'),
                    $this->request->getPost('category_id'),
                    $this->request->getPost('public')
                );
                $this->session->setFlashdata('form_success', 'New article has been created.');
                return redirect()->to(current_url());
            }
        }
        return view('staff/kb_articles_form', [
            'error_msg' => (isset($error_msg) ? $error_msg : null),
            'success_msg' => ($this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null),
            'kb_list' => $kb->getChildren(0, false, 0, ' - - - ', true),
            'category_id' => (is_numeric($this->request->getGet('category_id')) ? $this->request->getGet('category_id') : 0),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function editArticle($article_id)
    {
        $kb = Services::kb();
        if (!$article = $kb->getArticle($article_id, false)) {
            return redirect()->route('staff_kb_articles');
        }
        if ($this->request->getPost('do') == 'submit') {
            $validation = Services::validation();
            $validation->setRules([
                'title' => 'required',
                'category_id' => 'required|is_natural_no_zero',
                'public' => 'required|in_list[0,1]',
                'content' => 'required'
            ], [
                'title' => [
                    'required' => lang('Admin.error.enterTitle'),
                ],
                'category_id' => [
                    'required' => lang('Admin.error.selectCategory'),
                    'is_natural_no_zero' => lang('Admin.error.selectCategory'),
                ],
                'public' => [
                    'required' => lang('Admin.error.selectArticleType'),
                    'in_list' => lang('Admin.error.selectArticleType'),
                ],
                'content' => [
                    'required' => lang('Admin.error.enterContent')
                ]
            ]);

            if ($validation->withRequest($this->request)->run() == false) {
                $error_msg = $validation->listErrors();
            } elseif (defined('HDZDEMO')) {
                $error_msg = 'This is not possible in demo version.';
            } else {
                $kb->updateArticle(
                    $article->id,
                    $this->request->getPost('title'),
                    $this->request->getPost('content'),
                    $this->request->getPost('category_id'),
                    $this->request->getPost('public')
                );
                $this->session->setFlashdata('form_success', 'Article has been updated.');
                return redirect()->to(current_url());
            }
        }
        return view('staff/kb_articles_form', [
            'error_msg' => (isset($error_msg) ? $error_msg : null),
            'success_msg' => ($this->session->has('form_success') ? $this->session->getFlashdata('form_success') : null),
            'kb_list' => $kb->getChildren(0, false, 0, ' - - - ', true),
            'article' => $article,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }
}