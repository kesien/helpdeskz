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
            <?php echo lang('Admin.tickets.selectDepartment'); ?>
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
    <div class="card-header border-bottom">
        <h6 class="mb-0">
            <?php echo lang('Admin.tickets.selectDepartment'); ?>
        </h6>
    </div>
    <div class="card-body">
        <?php
        echo form_open_multipart('', [], ['do' => 'submit']);
        ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="form-group">
                    <div class="form-group">
                        <div class="form-group">
                            <label>
                                <?php echo lang('Admin.tickets.departments'); ?>
                            </label>

                            <?php
                            if ($departments = getDepartments(true)) {
                                foreach ($departments as $item) {
                                    ?>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="department<?php echo $item->id; ?>" name="department"
                                            value="<?php echo $item->id; ?>" class="custom-control-input">
                                        <label class="custom-control-label" for="department<?php echo $item->id; ?>">
                                            <?php echo $item->name; ?>
                                        </label>
                                    </div>
                                    <?php
                                }
                            }
                            ?>

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button class="btn btn-primary">
                        <?php echo lang('Admin.tickets.next'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php
    echo form_close();
    ?>
</div>
<?php
$this->endSection();
?>