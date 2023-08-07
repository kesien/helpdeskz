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
            <?php echo lang('Admin.links.linkCategories'); ?>
        </span>
        <h3 class="page-title">
            <?php echo isset($link_category) ? lang('Admin.links.editCategory') : lang('Admin.links.newCategory'); ?>
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
                <?php echo lang('Admin.form.linkCategory'); ?>
            </label>
            <input type="text" name="name" class="form-control"
                value="<?php echo set_value('name', isset($link_category) ? $link_category->name : ''); ?>">
        </div>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.linkCategoryDescription'); ?>
            </label>
            <input type="text" name="description" class="form-control"
                value="<?php echo set_value('description', isset($link_category) ? $link_category->description : ''); ?>">
        </div>
        <?php if (!isset($list_link_category) || (isset($list_link_category) && count($list_link_category) > 1)): ?>
            <div class="form-group">
                <label>
                    <?php echo lang('Admin.form.displayOrder'); ?>
                </label>
                <select name="position" class="form-control custom-select">
                    <?php
                    if (isset($link_category)) {
                        echo '<option value="">' . lang('Admin.form.notModify') . '</option>';
                    }
                    ?>
                    <option value="start">
                        <?php echo lang('Admin.form.beginningList'); ?>
                    </option>
                    <option value="end">
                        <?php echo lang('Admin.form.endList'); ?>
                    </option>
                    <?php
                    $default = set_value('position', isset($link_category) ? $link_category->dep_order : '');
                    if (isset($list_link_categories)) {
                        foreach ($list_link_categories as $item) {
                            if (!isset($link_category) || (isset($link_category) && $link_category->id != $item->id)) {
                                echo '<option value="' . $item->id . '">' . lang_replace('Admin.form.afterItem', ['%item%' => $item->name]) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <button class="btn btn-primary">
                <?php echo lang('Admin.form.submit'); ?>
            </button>
            <a href="<?php echo site_url(route_to('staff_link_categories')); ?>" class="btn btn-secondary"><?php echo lang('Admin.form.goBack'); ?></a>
        </div>
        <?php
        echo form_close();
        ?>
    </div>
</div>
<?php
$this->endSection();