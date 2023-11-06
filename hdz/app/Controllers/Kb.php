<?php
namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Services;

class Kb extends BaseController
{
    public function error404()
    {
        echo view('client/error', [
            'title' => lang('Client.404.title'),
            'body' => lang('Client.404.body')
        ]);
    }

    public function home()
    {
        return view('client/home', [
            'category_id' => 0,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function category($category_id = 0)
    {
        $kb = Services::kb();
        if ($category_id != 0) {
            if (!in_array($category_id, $kb->publicCategories())) {
                throw PageNotFoundException::forPageNotFound();
            }
            if (!$category = $kb->getCategory($category_id)) {
                throw PageNotFoundException::forPageNotFound();
            }
        }
        return view('client/home', [
            'category_id' => $category_id,
            'category' => isset($category) ? $category : null,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function article($article_id)
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

        return view('client/article', [
            'article' => $article,
            'category' => $category,
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    public function search()
    {
        if ($this->request->getPostGet('keyword') == '' || strlen($this->request->getPostGet('keyword')) <= 3) {
            return $this->home();
        }
        $kb = Services::kb();
        $result = $kb->searchArticles($this->request->getPostGet('keyword'));
        return view('client/search_result', [
            'result' => $result,
            'keyword' => esc($this->request->getPostGet('keyword')),
            'category_links_map' => $this->getLinkCategoryMap()
        ]);
    }

    private function findLinksForCategory($links, $categoryId)
    {
        $categoryLinks = [];
        foreach ($links as $link) {
            if ($link->link_category_id === $categoryId) {
                $categoryLinks[] = $link;
            }
        }
        return $categoryLinks;
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