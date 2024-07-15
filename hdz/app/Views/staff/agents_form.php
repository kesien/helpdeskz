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
            <?php echo lang('Admin.agents.menu'); ?>
        </span>
        <h3 class="page-title">
            <?php echo isset($agent) ? lang('Admin.agents.edit') : lang('Admin.agents.new'); ?>
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
                <?php echo lang('Admin.form.fullName'); ?>
            </label>
            <input type="text" name="fullname" class="form-control"
                value="<?php echo set_value('fullname', (isset($agent) ? $agent->fullname : '')); ?>">
        </div>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.username'); ?>
            </label>
            <input type="text" name="username" class="form-control"
                value="<?php echo set_value('username', (isset($agent) ? $agent->username : '')); ?>">
        </div>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.email'); ?>
            </label>
            <input type="text" name="email" class="form-control"
                value="<?php echo set_value('email', (isset($agent) ? $agent->email : '')); ?>">
        </div>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.password'); ?>
            </label>
            <input type="password" name="password" class="form-control">
            <?php if (isset($agent)): ?>
                <small class="text-muted form-text">
                    <?php echo lang('Admin.form.leaveBlankNotChange'); ?>
                </small>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.type'); ?>
            </label>
            <select name="admin" class="form-control custom-select">
                <?php
                $default = set_value('admin', (isset($agent) ? $agent->admin : 0));

                foreach (['0' => lang('Admin.agents.agent'), '1' => lang('Admin.agents.administrator'), '2' => lang('Admin.agents.user')] as $k => $v) {
                    if ($k == $default) {
                        echo '<option value="' . $k . '" selected>' . $v . '</option>';
                    } else {
                        echo '<option value="' . $k . '">' . $v . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.agents.assignedDepartments'); ?>
            </label>
            <?php
            $assigned = isset($agent) ? unserialize($agent->department) : array();
            $state = (isset($agent) && isset($agent->state)) ? unserialize($agent->state) : array();
            if (isset($departments)) {
                foreach ($departments as $item) {
                    ?>
                    <div class="d-flex mt-2">
                        <div class="form-check col-3">
                            <input type="checkbox" class="form-check-input" onchange="toggleSelect(<?php echo $item->id; ?>)" id="dep_<?php echo $item->id; ?>" name="department[]"
                                value="<?php echo $item->id; ?>" <?php echo in_array($item->id, $assigned) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="dep_<?php echo $item->id; ?>">
                                <?php echo $item->name; ?>
                            </label>
                        </div>
                        <div class="col-3">
                            <select class="form-control custom-select" id="<?php echo $item->id . "_state"; ?>" name="<?php echo $item->id . "_state"; ?>" <?php echo !in_array($item->id, $assigned) ? 'disabled' : ''; ?>>
                                 <option value="0" <?php echo (array_key_exists($item->id, $state) && $state[$item->id] == "0") ? 'selected' : ''; ?>><?php echo lang('Admin.agents.passive'); ?></option>
                                 <option value="1" <?php echo (array_key_exists($item->id, $state) && $state[$item->id] == "1") ? 'selected' : ''; ?>><?php echo lang('Admin.agents.active'); ?></option>
                            </select>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <div class="form-group">
            <label>
                <?php echo lang('Admin.form.status'); ?>
            </label>
            <select name="active" class="form-control custom-select">
                <?php
                $default = set_value('active', (isset($agent) ? $agent->active : 1));
                foreach (['1' => lang('Admin.form.active'), '0' => lang('Admin.form.locked')] as $k => $v) {
                    if ($k == $default) {
                        echo '<option value="' . $k . '" selected>' . $v . '</option>';
                    } else {
                        echo '<option value="' . $k . '">' . $v . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <button class="btn btn-primary">
                <?php echo lang('Admin.form.submit'); ?>
            </button>
            <a href="<?php echo site_url(route_to('staff_agents')); ?>" class="btn btn-secondary">
                <?php echo lang('Admin.form.goBack'); ?>
            </a>
        </div>
        <?php
        echo form_close();
        ?>
    </div>
</div>
<?php
$this->endSection();