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
            <?php echo lang('Admin.tickets.newTicket'); ?>
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
            <?php echo lang('Admin.tickets.submitNewTicket'); ?>
        </h6>
    </div>
    <div class="card-body">
        <?php
        echo form_open_multipart('', [], ['do' => 'submit']);
        ?>


        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>
                        <?php echo lang('Admin.form.email'); ?>
                    </label>
                    <input type="email" name="email" class="form-control" value="<?php echo staff_data('email'); ?>"
                        required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>
                        <?php echo lang('Admin.form.fullName'); ?>
                    </label>
                    <input type="text" name="fullname" class="form-control"
                        value="<?php echo staff_data('fullname'); ?>">
                    <small class="text-muted form-text">
                        <?php echo lang('Admin.tickets.fullName'); ?>
                    </small>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        <?php echo lang('Admin.form.department'); ?>
                    </label>
                    <input type="text" value="<?php echo $department->name; ?>" class="form-control" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        <?php echo lang('Admin.form.agent'); ?>
                    </label>
                    <select name="agent" class="form-control">
                        <?php
                        if (isset($agents)) {
                            if (empty($agents)) echo '<option value="0" selected>' . lang('Admin.form.none') . '</option>';
                            foreach ($agents as $agent) {
                                if ($department->default_agent_id == $agent->id) {
                                    echo '<option value="' . $agent->id . '" selected>' . $agent->fullname . '</option>';
                                } else {
                                    echo '<option value="' . $agent->id . '">' . $agent->fullname . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                    <?php if (empty($agents)) echo '<small class="text-muted">' . lang('Admin.form.noAgents') . '</small>' ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        <?php echo lang('Admin.form.priority'); ?>
                    </label>
                    <select name="priority" class="form-control custom-select">
                        <?php
                        if (isset($ticket_priorities)) {
                            foreach ($ticket_priorities as $item) {
                                if ($item->id == set_value('priority')) {
                                    echo '<option value="' . $item->id . '" selected>' . $item->name . '</option>';
                                } else {
                                    echo '<option value="' . $item->id . '">' . $item->name . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        <?php echo lang('Admin.form.status'); ?>
                    </label>
                    <select name="status" class="form-control custom-select">
                        <?php
                        foreach ($ticket_statuses as $k => $v) {
                            if ($k == set_value('status')) {
                                echo '<option value="' . $k . '" selected>' . lang('Admin.form.' . $v) . '</option>';
                            } else {
                                echo '<option value="' . $k . '">' . lang('Admin.form.' . $v) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

            </div>
        </div>

        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.subject'); ?>
            </label>
            <input type="text" name="subject" class="form-control" value="<?php echo set_value('subject'); ?>" required>
        </div>

        <div class="form-group">
            <?php
            if (isset($customFields)) {
                foreach ($customFields as $customField) {
                    echo parseCustomFieldsForm($customField);
                }
            }
            ?>
        </div>

        <div class="form-group">
            <textarea class="form-control messageBox" name="message" id="messageBox"
                rows="20"><?php echo set_value('message'); ?></textarea>
        </div>
        <?php
        if (site_config('ticket_attachment')) {
            ?>
            <div class="form-group">
                <label>
                    <?php echo lang('Admin.form.attachments'); ?>
                </label>
                <?php
                for ($i = 1; $i <= site_config('ticket_attachment_number'); $i++) {
                    ?>
                    <div class="row">
                        <div class="col-lg-4 mb-2">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" name="attachment[]"
                                    id="customFile<?php echo $i; ?>">
                                <label class="custom-file-label" for="customFile<?php echo $i; ?>"
                                    data-browse="<?php echo lang('Admin.form.browse'); ?>">
                                    <?php echo lang('Admin.form.chooseFile'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
                <small class="text-muted">
                    <?php echo lang('Admin.form.allowedFiles') . ' *.' . implode(', *.', unserialize(site_config('ticket_file_type'))); ?>
                </small>
            </div>
            <?php
        }
        ?>
        <div class="form-group">
            <button class="btn btn-primary"><i class="fa fa-paper-plane"></i>
                <?php echo lang('Admin.form.submit'); ?>
            </button>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>


<?php
$this->endSection();
$this->section('script_block');
include __DIR__ . '/tinymce.php';
?>
<script>
    $(document).ready(function () {
        bsCustomFileInput.init();
    });
    <?php
    if (isset($canned_response)) {
        echo 'var canned_response = ' . json_encode($canned_response) . ';';
    }
    ?>
    var KBUrl = '<?php echo site_url(route_to('staff_ajax_kb')); ?>';
</script>
<?php
$this->endSection();
