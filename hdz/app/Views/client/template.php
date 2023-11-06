<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$page_controller = isset($page_controller) ? $page_controller : '';
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSS -->
    <?php
    echo link_tag('favicon.ico', 'icon', 'image/x-icon') .
        link_tag('https://fonts.googleapis.com/css?family=Source+Sans+Pro:200,300,400,400i,600') .
        link_tag('assets/components/font-awesome/css/font-awesome.min.css') .
        link_tag('assets/components/bootstrap/css/bootstrap.min.css') .
        link_tag('assets/components/select2/css/select2.min.css') .
        link_tag('assets/components/select2/css/select2-bootstrap.min.css') .
        link_tag('assets/admin/styles/shards-dashboards.1.1.0.css') .
        link_tag('assets/helpdeskz/css/helpdesk.css');
    $this->renderSection('css_block');
    ?>
    <title>
        <?php $this->renderSection('window_title'); ?>
    </title>
</head>

<body class="h-100">
    <div class="container-fluid">
        <div class="row">
            <!-- Main Sidebar -->
            <aside class="main-sidebar col-12 col-md-3 col-lg-2 px-0">
                <div class="main-navbar">
                    <nav class="navbar align-items-stretch navbar-light bg-white flex-md-nowrap border-bottom p-0">
                        <a class="navbar-brand w-100 mr-0" href="<?php echo site_url(route_to('/')); ?>"
                            style="line-height: 25px;">
                            <div class="d-table m-auto">
                                <img id="main-logo" class="d-inline-block align-top mr-1" style="max-width: 150px;"
                                    src="<?php echo base_url('assets/helpdeskz/images/logo.png'); ?>">
                            </div>
                        </a>
                        <a class="toggle-sidebar d-sm-inline d-md-none d-lg-none">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                    </nav>
                </div>
                <div class="nav-wrapper">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo site_url(route_to('home')); ?>"><?php echo lang('Client.kb.menu'); ?></a>
                        </li>
                        <?php if (client_online()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo site_url(route_to('view_tickets')); ?>"><?php echo lang('Client.viewTickets.menu'); ?></a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo site_url(route_to('submit_ticket')); ?>"><?php echo lang('Client.submitTicket.menu'); ?></a>
                        </li>
                        <?php if (client_online()): ?>
                            <li class="nav-item dropdown <?php if ($page_controller == 'account') {
                                echo 'active';
                            } ?>">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <?php echo lang('Client.account.menu'); ?>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="<?php echo site_url(route_to('profile')); ?>"><?php echo lang('Client.account.editProfile'); ?></a>
                                    <a class="dropdown-item" href="<?php echo site_url(route_to('logout')); ?>"><?php echo lang('Client.account.logout'); ?></a>
                                </div>
                            </li>
                        <?php else: ?>
                            <li class="nav-item <?php if ($page_controller == 'login') {
                                echo 'active';
                            } ?>">
                                <a class="nav-link" href="<?php echo site_url(route_to('login')); ?>"><?php echo lang('Client.login.menu'); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if (isset($category_links_map)): ?>
                            <?php foreach ($category_links_map as $category_name => $links): ?>
                                <?php if (count($links) > 0): ?>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-folder"></i>
                                            <span>
                                                <?php echo $category_name; ?>
                                            </span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-small">
                                            <?php
                                            foreach ($links as $link) {
                                                echo '<a class="dropdown-item"
                                                    href="' . $link->url . '" target="_blank">' . $link->name . "</a>";
                                            }
                                            ?>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </aside>
            <main class="main-content col-lg-10 col-md-9 col-sm-12 p-0 offset-lg-2 offset-md-3">
                <div class="main-navbar sticky-top bg-white">
                    <!-- Main Navbar -->
                    <nav class="navbar align-items-stretch navbar-light flex-md-nowrap p-0">
                        <nav class="nav">
                            <a href="#"
                                class="nav-link nav-link-icon toggle-sidebar d-md-inline d-lg-none text-center border-left"
                                data-toggle="collapse" data-target=".header-navbar" aria-expanded="false"
                                aria-controls="header-navbar">
                                <i class="fa fa-bars"></i>
                            </a>
                        </nav>
                    </nav>
                </div>
                <div class="main-content-container container-fluid px-4">
                    <div class="main-content-container container-fluid px-4">
                        <?php
                        $this->renderSection('content');
                        ?>
                    </div>
                </div>
                <footer class="main-footer d-flex mt-3 p-2 px-3 bg-white border-top">
                    <span class="copyright ml-auto my-auto mr-2">Copyright © 2015 -
                        <?php echo date('Y'); ?>
                        <a href="https://helpdeskz.com" rel="nofollow">HelpDeskZ v
                            <?php echo HDZ_VERSION; ?>
                        </a>
                    </span>
                </footer>
            </main>
        </div>
    </div>



    <!-- Javascript -->
    <?php
    echo script_tag('assets/components/jquery/jquery.min.js') .
        script_tag('assets/components/bootstrap/js/bootstrap.bundle.min.js') .
        script_tag('assets/admin/scripts/shards.min.js') .
        script_tag('assets/admin/scripts/shards-dashboards.1.1.0.js') .
        script_tag('assets/components/select2/js/select2.min.js') .
        script_tag('assets/components/blockui/jquery.blockUI.js') .
        script_tag('assets/helpdeskz/js/helpdesk.js') .
        script_tag('assets/helpdeskz/js/staff.js');
    $this->renderSection('script_block');
    ?>
</body>

</html>