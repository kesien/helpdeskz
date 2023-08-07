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
            <?php echo lang('Admin.links.linkCategories'); ?>
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
    '<input type="hidden" name="link_category_id" id="link_category_id">' .
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
                <a href="<?php echo site_url(route_to('staff_link_category_new')); ?>" class="btn btn-primary btn-sm"><i
                        class="fa fa-plus"></i>
                    <?php echo lang('Admin.links.newCategory'); ?>
                </a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="titles">
                <tr>
                    <th>
                        <?php echo lang('Admin.form.linkCategoryName'); ?>
                    </th>
                    <th>
                        <?php echo lang('Admin.form.linkCategoryDescription'); ?>
                    </th>
                    <th></th>
                </tr>
            </thead>
            <?php
            if (isset($list_link_categories)) {
                foreach ($list_link_categories as $item) {
                    ?>
                    <tr>
                        <td>
                            <?php echo $item->name; ?>
                        </td>
                        <td>
                            <?php echo (isset($item->description) ? $item->description : ''); ?>
                        </td>
                        <td class="text-right">
                            <div class="btn-group">
                                <?php
                                if ($first_position->id == $item->id) {
                                    echo '<a href="#" class="btn btn-outline-light btn-sm disabled"><i class="fa fa-chevron-up"></i></a>';
                                    if ($last_position->id == $item->id) {
                                        echo '<a href="#" class="btn btn-outline-light btn-sm disabled"><i class="fa fa-chevron-down"></i></a>';
                                    } else {
                                        echo '<a href="' . current_url() . '?action=move_down&link_category_id=' . $item->id . '" class="btn btn-outline-dark btn-sm"><i class="fa fa-chevron-down"></i></a>';
                                    }
                                } elseif ($last_position->id == $item->id) {
                                    echo '<a href="' . current_url() . '?action=move_up&link_category_id=' . $item->id . '" class="btn btn-outline-dark btn-sm"><i class="fa fa-chevron-up"></i></a>';
                                    echo '<a href="#" class="btn btn-outline-light btn-sm disabled"><i class="fa fa-chevron-down"></i></a>';
                                } else {
                                    echo '<a href="' . current_url() . '?action=move_up&link_category_id=' . $item->id . '" class="btn btn-outline-dark btn-sm"><i class="fa fa-chevron-up"></i></a>';
                                    echo '<a href="' . current_url() . '?action=move_down&link_category_id=' . $item->id . '" class="btn btn-outline-dark btn-sm"><i class="fa fa-chevron-down"></i></a>';
                                }

                                ?>
                            </div>
                            <div class="btn-group">
                                <?php
                                echo '<a href="' . site_url(route_to('staff_link_category_id', $item->id)) . '" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>';
                                echo ' <button type="button" onclick="removeLinkCategory(' . $item->id . ')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>';
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