<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?= $this->renderSection('window_title') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="300"> <!-- 5 percenként frissít -->
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
</head>
<body>
    <div class="p-5">

        <?= $this->renderSection('page_content') ?>
    </div>
</body>
</html>