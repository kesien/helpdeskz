<?php
namespace App\Controllers;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 *
 * @package CodeIgniter
 */

use App\Libraries\Client;
use App\Libraries\Settings;
use App\Libraries\Staff;
use CodeIgniter\Controller;
use CodeIgniter\Session\Session;
use Config\Services;

class BaseController extends Controller
{

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['cookie', 'form', 'html', 'helpdesk', 'number', 'filesystem', 'text'];
    /**
     * @var $session Session
     */
    protected $session;
    /**
     * @var $settings Settings
     */
    protected $settings;
    /**
     * @var $staff Staff
     */
    protected $staff;
    /**
     * @var $client Client
     */
    protected $client;


    /**
     * Constructor.
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {

        // Do Not Edit This Line
        parent::initController($request, $response, $logger);
        //--------------------------------------------------------------------
        // Preload any models, libraries, etc, here.
        //--------------------------------------------------------------------
        // E.g.:
        // $this->session = \Config\Services::session();
        if (!defined('INSTALL_ENVIRONMENT')) {
            $this->client = Services::client();
            $this->settings = Services::settings();
            $this->session = Services::session();
            $this->staff = Services::staff();
            if ($this->settings->config('maintenance') == 1) {
                if (!$this->staff->isOnline()) {
                    die();
                }
            }

        }
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

    public function getLinkCategoryMap()
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
            usort($links, function ($a, $b) {
                $nameA = $a->name;
                $nameB = $b->name;

                // Extract the numeric part from the names
                preg_match('/^([\d.]+)?\s*(.*)$/', $nameA, $matchesA);
                preg_match('/^([\d.]+)?\s*(.*)$/', $nameB, $matchesB);

                // Check if both links are numeric or non-numeric
                $isNumericA = is_numeric($matchesA[1] ?? '');
                $isNumericB = is_numeric($matchesB[1] ?? '');

                // Compare numeric links first
                if ($isNumericA && $isNumericB) {
                    return intval($matchesA[1]) - intval($matchesB[1]);
                } elseif ($isNumericA) {
                    return -1; // $a is numeric, place it first
                } elseif ($isNumericB) {
                    return 1; // $b is numeric, place it first
                }

                // If both are non-numeric, or have no numeric part, compare alphabetically
                return strcasecmp($matchesA[2], $matchesB[2]);
            });


            $categoryName = isset($categoryNames[$categoryId]) ? $categoryNames[$categoryId] : "Uncategorized";
            $transformedCategoryLinksMap[$categoryName] = $links;
        }

        return $transformedCategoryLinksMap;
    }

}
