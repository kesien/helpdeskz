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
            <span class="text-uppercase page-subtitle"><?php echo lang('Admin.tickets.departments');?></span>
            <h3 class="page-title"><?php echo isset($department) ? lang('Admin.tickets.editDepartment') : lang('Admin.tickets.newDepartment');?></h3>
        </div>
    </div>
    <!-- End Page Header -->


<?php
if(isset($error_msg)){
    echo '<div class="alert alert-danger">'.$error_msg.'</div>';
}
if(isset($success_msg)){
    echo '<div class="alert alert-success">'.$success_msg.'</div>';
}
echo form_open('', ['id' => 'deleteForm'], ['do' => 'removeFilter']) .
    '<input type="hidden" name="filter_id" id="filter_id">' .
    form_close();
?>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs border-bottom" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="department-tab" data-toggle="tab" href="#department-content" role="tab" aria-controls="contact" aria-selected="false"><?php echo lang('Admin.settings.settings');?></a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="filters-tab" data-toggle="tab" href="#filters-content" role="tab" aria-controls="contact" aria-selected="false"><?php echo lang('Admin.settings.filters');?></a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <?php
            echo form_open('',[],['do' => 'submit']);
            ?>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="department-content" role="tabpanel" aria-labelledby="department-tab">
                    <div class="form-group">
                        <label><?php echo lang('Admin.form.department');?></label>
                        <input type="text" name="name" class="form-control" value="<?php echo set_value('name', isset($department) ? $department->name : '');?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo lang('Admin.form.type');?></label>
                        <select name="private" class="form-control custom-select">
                            <?php
                            $default = set_value('private', isset($department) ? $department->private : 0);
                            foreach (['0' => lang('Admin.form.public'),'1' => lang('Admin.form.private')] as $k => $v){
                                if($k == $default){
                                    echo '<option value="'.$k.'" selected>'.$v.'</option>';
                                }else{
                                    echo '<option value="'.$k.'">'.$v.'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <?php if(isset($department)) :?>
                    <div class="form-group">
                        <label><?php echo lang('Admin.form.defaultAgent');?></label>
                        <select name="default_agent" class="form-control custom-select">
                            <?php
                            $default = set_value('default_agent', (isset($department) && $department->default_agent_id == 0) ? 0 : $department->default_agent_id);
                            echo '<option value="0"'. ($default == 0 ? "selected" : "") . '>'. lang('Admin.form.none') .'</option>';
                            foreach ($agents_for_department as $agent) {
                                if($agent->id == $default){
                                    echo '<option value="'.$agent->id.'" selected>'.$agent->fullname.'</option>';
                                }else{
                                    echo '<option value="'.$agent->id.'">'.$agent->fullname.'</option>';
                                }
                            }
                            ?>
                        </select>
                        <?php if (empty($agents_for_department)) echo "<small class='text-muted'>" . lang('Admin.form.addAgents') . "</small>"; ?>
                    </div>
                    <?php endif; ?>
                    <?php if(!isset($department) || (isset($department) && count($list_departments) > 1)):?>
                        <div class="form-group">
                            <label><?php echo lang('Admin.form.displayOrder');?></label>
                            <select name="position" class="form-control custom-select">
                                <?php
                                if(isset($department)){
                                    echo '<option value="">'.lang('Admin.form.notModify').'</option>';
                                }
                                ?>
                                <option value="start"><?php echo lang('Admin.form.beginningList');?></option>
                                <option value="end"><?php echo lang('Admin.form.endList');?></option>
                                <?php
                                $default = set_value('position', isset($department) ? $department->dep_order : '');
                                if(isset($list_departments)){
                                    foreach ($list_departments as $item){
                                        if(!isset($department) || (isset($department) && $department->id != $item->id)){
                                            echo '<option value="'.$item->id.'">'.lang_replace('Admin.form.afterItem',['%item%' => $item->name]).'</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    <?php endif;?>
                </div>
                <!-- Filter rules -->
                <div class="tab-pane fade" id="filters-content" role="tabpanel" aria-labelledby="filters-tab">
                    <div class="form-group">
                        <label><?php echo lang('Admin.form.type'); ?></label>
                        <select name="rule_type" class="form-control custom-select" id="rule_type">
                            <?php
                            $default = '0';
                            foreach (['0' => lang('Admin.form.rules.types.subject'), 
                                      '1' => lang('Admin.form.rules.types.body')] as $k => $v) {
                                if ($default == $k) {
                                    echo '<option value="' . $k . '" selected>' . $v . '</option>';
                                } else {
                                    echo '<option value="' . $k . '">' . $v . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php echo lang('Admin.form.rules.rule'); ?></label>
                        <select name="rule_condition" class="form-control custom-select" id="rule_condition">
                            <?php
                            $default = '0';
                            foreach (['0' => lang('Admin.form.rules.rules.contains'), '1' => lang('Admin.form.rules.rules.does-not-contain'), '2' => lang('Admin.form.rules.rules.matches'), '3' => lang('Admin.form.rules.rules.does-not-match')] as $k => $v) {
                                if ($default == $k) {
                                    echo '<option value="' . $k . '" selected>' . $v . '</option>';
                                } else {
                                    echo '<option value="' . $k . '">' . $v . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                            <label><?php echo lang('Admin.form.rules.value'); ?></label>
                        <input type="text" name="rule_value" class="form-control"
                            value="">
                    </div>
                    <div class="form-group">
                        <label><?php echo lang('Admin.form.rules.action'); ?></label>
                        <select name="rule_action" class="form-control custom-select" id="rule_action">
                            <?php
                            $default = '0';
                            foreach (['0' => lang('Admin.form.rules.actions.send-copy'), '1' => lang('Admin.form.rules.actions.assign-to-agent'), '2' => lang('Admin.form.rules.actions.set-priority')] as $k => $v) {
                                if ($default == $k) {
                                    echo '<option value="' . $k . '" selected>' . $v . '</option>';
                                } else {
                                    echo '<option value="' . $k . '">' . $v . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group" id="rule_send_copy">
                            <label><?php echo lang('Admin.form.rules.email'); ?></label>
                            <input type="email" name="rule_email" class="form-control"
                                value="">
                    </div>
                    <div class="form-group" id="rule_assign_to_agent">
                        <label><?php echo lang('Admin.form.rules.assign-to-agent'); ?></label>
                        <select name="rule_assign_to" class="form-control custom-select" id="rule_assign_to">
                            <?php
                            $default = 1;
                            foreach($agents as $agent) {
                                if ($default == $agent->id) {
                                    echo '<option value="' . $agent->id . '" selected>' . $agent->fullname . '</option>';
                                } else {
                                    echo '<option value="' . $agent->id . '">' . $agent->fullname . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group" id="rule_set_priority">
                        <label>
                            <?php echo lang('Admin.form.priority'); ?>
                        </label>
                        <select name="priority" class="form-control custom-select">
                            <?php
                            if (isset($ticket_priorities)) {
                                foreach ($ticket_priorities as $item) {
                                    if ($item->id == 1) {
                                        echo '<option value="' . $item->id . '" selected>' . $item->name . '</option>';
                                    } else {
                                        echo '<option value="' . $item->id . '">' . $item->name . '</option>';
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <h5><?php echo lang('Admin.form.rules.rulesTitle'); ?></h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 15%">
                                            <?php echo lang('Admin.form.type'); ?>
                                        </th>
                                        <th style="width: 10%">
                                            <?php echo lang('Admin.form.rules.rule'); ?>
                                        </th>
                                        <th>
                                            <?php echo lang('Admin.form.rules.value'); ?>
                                        </th>
                                        <th>
                                            <?php echo lang('Admin.form.rules.action'); ?>
                                        </th>
                                        <th>
                                            <?php echo lang('Admin.form.rules.outcome'); ?>
                                        </th>
                                        <th>&nbsp;</th>
                                    </tr>
                                </thead>
                                <?php if (!isset($rules) || empty($rules)): ?>
                                    <tr>
                                        <td colspan="5">
                                            <i>
                                                <?php echo lang('Admin.form.rules.noRulesFound'); ?>
                                            </i>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($rules as $item): ?>
                                        <tr>
                                            <td><?php echo lang(getItemType($item->type)); ?></td>
                                            <td><?php echo lang(getItemRule($item->rule_condition)); ?></td>
                                            <td><?php echo $item->rule_value; ?></td>
                                            <td><?php echo lang(getItemAction($item->rule_action)); ?></td>
                                            <td><?php echo $item->outcome; ?></td>
                                            <td><button type='button' onclick='removeFilter(<?php echo $item->id; ?>)'
                                    class='btn btn-danger btn-sm'><i class='fa fa-trash'></i></button></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Filter rules end -->
            <div class="form-group">
                <button class="btn btn-primary"><?php echo lang('Admin.form.submit');?></button>
                <a href="<?php echo site_url(route_to('staff_departments'));?>" class="btn btn-secondary"><?php echo lang('Admin.form.goBack');?></a>
            </div>
            <?php
            echo form_close();
            ?>
        </div>
    </div>
<?php
$this->endSection();
$this->section('script_block');
?>
<script type="text/javascript">
    $(function () {
        rule_action();
        $("#rule_action").on('change', function () {
            rule_action();
        })
    });
</script>
<?php
$this->endSection();