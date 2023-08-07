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
        <span class="text-uppercase page-subtitle">
            <?php echo lang('Admin.links.links'); ?>
        </span>
        <h3 class="page-title">
            <?php echo isset($link) ? lang('Admin.links.editLink') : lang('Admin.links.newLink'); ?>
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
?>

<div class="card">
    <div class="card-body">
        <?php
        echo form_open('', [], ['do' => 'submit']);
        ?>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.link'); ?>
            </label>
            <input type="text" name="name" class="form-control"
                value="<?php echo set_value('name', isset($link) ? $link->name : ''); ?>">
        </div>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.linkUrl'); ?>
            </label>
            <input type="url" name="url" class="form-control"
                value="<?php echo set_value('url', isset($link) ? $link->url : ''); ?>">
        </div>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.linkCategory'); ?>
            </label>
            <select name="link_category_id" class="form-control custom-select">
                <?php
                $default = set_value('link_category_id', (isset($link) && isset($link->link_category_id)) ? $link->link_category_id : '');
                echo '<option value=""></option>';
                if (isset($list_link_categories) && count($list_link_categories) > 0) {
                    foreach ($list_link_categories as $category) {
                        if (isset($link) && $link->link_category_id == $category->id) {
                            echo '<option value="' . $category->id . '" selected>' . $category->name . '</option>';
                        } else {
                            echo '<option value="' . $category->id . '">' . $category->name . '</option>';
                        }
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <button class="btn btn-primary">
                <?php echo lang('Admin.form.submit'); ?>
            </button>
            <a href="<?php echo site_url(route_to('staff_links')); ?>" class="btn btn-secondary"><?php echo lang('Admin.form.goBack'); ?></a>
        </div>
        <?php
        echo form_close();
        ?>
    </div>
</div>
<?php
$this->endSection();