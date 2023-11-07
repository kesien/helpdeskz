<?php
/**
 * @package EvolutionScript
 * @author: EvolutionScript S.A.C.
 * @Copyright (c) 2010 - 2020, EvolutionScript.com
 * @link http://www.evolutionscript.com
 */
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class UserAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('cookie');
        helper('helpdesk');
        $staff = Services::staff();
        if (!$staff->isOnline() || $client->isOnline()) {
            if (!isset($arguments)) {
                return redirect()->route('staff_login');
            } elseif ($arguments[0] != 'login') {
                return redirect()->route('staff_login');
            }
        } else {
            if (isset($arguments) && $arguments[0] == 'login') {
                return redirect()->route('staff_dashboard');
            }
            set_timezone(($staff->getData('timezone') == '' ? Services::settings()->config('timezone') : $staff->getData('timezone')));
            Services::tickets()->autoCloseTickets();
        }
        $client = Services::client();
        $settings = Services::settings();
        if ($settings->config('maintenance') == '1') {
            return redirect()->route('maintenance');
        }
        if ($client->isOnline()) {
            set_timezone(($client->getData('timezone') == '' ? $settings->config('timezone') : $client->getData('timezone')));
        } else {
            set_timezone($settings->config('timezone'));
        }
        if (is_array($arguments)) {
            if ($arguments[0] == 'visitor') {
                if ($client->isOnline()) {
                    return redirect()->route('view_tickets');
                }
            } elseif ($arguments[0] == 'user') {
                if (!$client->isOnline()) {
                    return redirect()->route('login');
                }
            }
        }
        /*

        */
        // Do something here
    }

    //--------------------------------------------------------------------

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}