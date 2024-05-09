<?php
/**
 * @var $this \CodeIgniter\View\View
 * @var $validation \CodeIgniter\Validation\Validation
 */
$this->extend('client/template');
$this->section('window_title');
echo lang('Client.submitTicket.menu');
$this->endSection();
$this->section('content');
?>
    <div class="container mt-5">
        <h1 class="heading mb-5">
            <?php echo lang('Client.submitTicket.title');?>
        </h1>
        <p><?php echo lang('Client.submitTicket.selectAgent');?></p>
        <?php
        if(isset($error_msg)){
            echo '<div class="alert alert-danger">'.$error_msg.'</div>';
        }

        echo form_open('',[],[
            'do' => 'submit'
        ]);
        ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="form-group">
                    <div class="form-group">
                        <div class="form-group">
                            <label>
                                <?php echo lang('Client.form.agents');?>
                            </label>

                                <?php
                                if(isset($agents)){
                                    foreach ($agents as $item){
                                        ?>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="agent<?php echo $item->id;?>" name="agent" value="<?php echo $item->id;?>" class="custom-control-input">
                                            <label class="custom-control-label" for="agent<?php echo $item->id;?>"><?php echo $item->fullname;?></label>
                                        </div>
                                        <?php
                                    }
                                }
                                ?>

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button class="btn btn-primary"><?php echo lang('Client.form.next');?></button>
                </div>
            </div>
        </div>

        <?php
        echo form_close();
        ?>
    </div>



<?php
$this->endSection();
$this->section('script_block');
?>
    <script type="text/javascript" src="<?php echo base_url('assets/components/bs-custom-file-input/bs-custom-file-input-min.js');?>"></script>
    <script>
        $(function(){
            $(document).ready(function () {
                bsCustomFileInput.init();
            });
        })
    </script>
<?php
$this->endSection();