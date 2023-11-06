<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('staff/template');
$this->section('content');
?>
<!-- Page Header -->
<div class="page-header row no-gutters py-4">
    <div class="col-12 col-sm-4 text-center text-sm-left mb-0">
        <span class="text-uppercase page-subtitle">HelpDeskZ</span>
        <h3 class="page-title">
            <?php echo lang('Admin.links.links'); ?>
        </h3>
    </div>
</div>
<!-- End Page Header -->


<?php

if (isset($error_msg)) {
    echo '<div class="alert alert-danger">' . $error_msg . '</div>';
}
if (isset($success_msg)) {
    echo '<div class="alert alert-success">' . $success_msg . '</div>';
}
echo form_open('', ['id' => 'manageForm'], ['do' => 'remove']) .
    '<input type="hidden" name="link_id" id="link_id">' .
    form_close();
?>
<div class="card">

    <div class="card-header">
        <div class="row">
            <div class="col d-none d-sm-block">
                <h6 class="mb-0">
                    <?php echo lang('Admin.form.manage'); ?>
                </h6>
            </div>
            <div class="col text-md-right">
                <a href="<?php echo site_url(route_to('staff_links_new')); ?>" class="btn btn-primary btn-sm"><i
                        class="fa fa-plus"></i>
                    <?php echo lang('Admin.links.newLink'); ?>
                </a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="titles">
                <tr>
                    <th>
                        <?php echo lang('Admin.form.linkName'); ?>
                    </th>
                    <th>
                        <?php echo lang('Admin.form.linkCategory'); ?>
                    </th>
                    <th>
                        <?php echo lang('Admin.form.linkUrl'); ?>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <?php
            if (isset($list_links)) {
                foreach ($list_links as $item) {
                    ?>
                    <tr>
                        <td>
                            <?php echo $item->name; ?>
                        </td>
                        <td>
                            <?php
                            if (isset($item->link_category_id) && $item->link_category_id != '') {
                                if (isset($list_link_categories) && count($list_link_categories) > 0) {
                                    $index = array_search($item->link_category_id, array_column($list_link_categories, 'id'));
                                    if ($index !== false) {
                                        echo $list_link_categories[$index]->name;
                                    } else {
                                        echo lang('Admin.form.linkUncategorized');
                                    }
                                } else {
                                    echo lang('Admin.form.linkUncategorized');
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo $item->url; ?>
                        </td>
                        <td class="text-right">
                            <div class="btn-group">
                                <?php
                                echo '<a href="' . site_url(route_to('staff_links_id', $item->id)) . '" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>';
                                echo ' <button type="button" onclick="removeLink(' . $item->id . ')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
                                ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="5">
                        <?php echo lang('Admin.error.recordsNotFound'); ?>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>
</div>
<?php
$this->endSection();