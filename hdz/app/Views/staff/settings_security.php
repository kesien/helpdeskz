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
            <span class="text-uppercase page-subtitle"><?php echo lang('Admin.settings.menu');?></span>
            <h3 class="page-title"><?php echo lang('Admin.settings.security');?></h3>
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
?>
    <div class="card">
        <div class="card-body">
            <?php
            echo form_open('',[],['do' => 'submit']);
            ?>
            <div class="form-group">
                <label><?php echo lang('Admin.settings.recaptchaStatus');?></label>
                <select name="recaptcha" class="form-control custom-select" id="recaptcha">
                    <?php
                    $default = set_value('recaptcha', site_config('recaptcha'));
                    foreach ([0=>'Disable',1=>'Enable'] as $k => $v){
                        if($default == $k){
                            echo '<option value="'.$k.'" selected>'.$v.'</option>';
                        }else{
                            echo '<option value="'.$k.'">'.$v.'</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div id="recaptcha_details">
                <div class="form-group">
                    <label><?php echo lang('Admin.settings.siteKey');?></label>
                    <input type="text" name="recaptcha_sitekey" class="form-control" value="<?php echo set_value('recaptcha_sitekey', (defined('HDZDEMO') ? '[Hidden in demo]' : site_config('recaptcha_sitekey')));?>">
                </div>
                <div class="form-group">
                    <label><?php echo lang('Admin.settings.privateKey');?></label>
                    <input type="text" name="recaptcha_privatekey" class="form-control" value="<?php echo set_value('recaptcha_privatekey', (defined('HDZDEMO') ? '[Hidden in demo]' : site_config('recaptcha_privatekey')));?>">
                </div>
            </div>

            <div class="form-group">
                <label><?php echo lang('Admin.settings.lockOutStatus'); ?></label>
                <select name="lockout" class="form-control custom-select" id="lockout">
                    <?php
                    $default = set_value('lockout', site_config('login_attempt') == 0 ? 0 : 1);
                    foreach ([0 => 'Disable', 1 => 'Enable'] as $k => $v) {
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
                <label><?php echo lang('Admin.settings.maxLoginAttempts');?></label>
                <input type="number" step="1" min="1" name="login_attempt" id="login_attempt" class="form-control" value="<?php echo set_value('login_attempt', site_config('login_attempt'));?>">
            </div>
            <div class="form-group">
                <label><?php echo lang('Admin.settings.minutesIpLocking');?></label>
                <input type="number" step="1" min="1" name="login_attempt_minutes" id="login_attempt_minutes" class="form-control" value="<?php echo set_value('login_attempt_minutes', site_config('login_attempt_minutes'));?>">
            </div>
            <div class="form-group">
                <button class="btn btn-primary"><?php echo lang('Admin.form.save');?></button>
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
<script>
    $(function (){
        recaptcha_status();
        lockout_status();
        $('#recaptcha').on('change', function(){
            recaptcha_status();
        });
        $('#lockout').on('change', function(){
            lockout_status();
        });
    })
    function recaptcha_status()
    {
        if($('#recaptcha').val() === '1'){
            $('#recaptcha_details').show();
        }else{
            $('#recaptcha_details').hide();
        }
    }
    function lockout_status()
    {
        const login_attempt = $('#login_attempt');
        const login_attempt_minutes = $('#login_attempt_minutes');
        if($('#lockout').val() === '1'){
            login_attempt.removeAttr("readonly");
            login_attempt_minutes.removeAttr("readonly");
        }else{
            login_attempt.attr("readonly", "readonly");
            login_attempt_minutes.attr("readonly", "readonly");
        }
    }
</script>
<?php
$this->endSection();
