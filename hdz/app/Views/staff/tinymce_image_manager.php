<?php
/**
 * @var $pager \CodeIgniter\Pager\Pager
 */
?><!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title><?php echo lang('Admin.form.uploadImage');?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php
    #JS
    echo script_tag('assets/components/tinymce-img-uploader/js/jquery.min.js');
    #CSS
    echo link_tag('assets/components/tinymce-img-uploader/css/lightbox.css').
        link_tag('assets/components/tinymce-img-uploader/css/featherlight.css').
        link_tag('assets/components/tinymce-img-uploader/css/dropzone.css').
        link_tag('assets/components/tinymce-img-uploader/css/styles.css').
        link_tag('assets/components/font-awesome/css/font-awesome.min.css');
    ?>
    <style>
        #loading {
            position: absolute;
            top: 0;
            left: 0;
            font-size: 25px;
            height: 100%;
            width: 100%;
            z-index: 10000;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loading-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #loading p {
            margin: 0;
        }
        #loading i {
            margin-right: 10px;
            animation: rotate 2s infinite;
        }
        @keyframes blink {
            0% {
                opacity: 0;
            }
            50% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }
        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>
<div id="loading">
    <div class="loading-wrap">
        <i class="fa fa-spinner"></i>
        <p>Loading ...</p>
    </div>
</div>
<div class="_mt15 _mr15 _ml15">
    <!-- BUTTONS -->
    <div class="row">
        <a class="action" id="btnToggleUploader"><?php echo lang('Admin.form.uploadImage');?></a>
    </div>
    <!-- UPLOADER -->
    <div class="row">
        <div id="uploader" class="_hide">
            <form action="<?php echo site_url(route_to('staff_editor_uploader'));?>" class="dropzone">
                <?php echo csrf_field('tokenInput');?>
                <input type="hidden" name="do" value="upload">
            </form>
        </div>
    </div>


    <div class="row">
        <div class="_mb25">
            <div class="_mb10 _mt10"><?php echo lang('Admin.form.total');?> <span id="Total"><?php echo $total_images ?></span></div>
            <div id="partial_container"></div>
        </div>

    </div>
    <?php
    echo $pagination;
    ?>

</div>
<?php
echo script_tag('assets/components/tinymce-img-uploader/js/lightbox.js').
    script_tag('assets/components/tinymce-img-uploader/js/featherlight.js').
    script_tag('assets/components/tinymce-img-uploader/js/dropzone.js');
?>
<script type="text/javascript">
    Dropzone.autoDiscover = false;
    updateImageList().then(() => {
        $('#uploadToken').attr('name', $('#tokenInput').attr('name'));
        $('#uploadToken').val($('#tokenInput').val());
        var fl = null;
        $.featherlight.autoBind = false;

        //--------------------------------------
        // DELETE
        //--------------------------------------
        $(document).on("click","#btnDelete",function(e){
            e.preventDefault();
            var DivID 	= $(this).data("id");
            var FileName 	= $(this).data("file");
            var Total 	= $('#Total').text()-1;		// deduct total count
            var CONTAINER = $('#'+DivID);
            var STRING = 'do=delete&file='+ FileName + "&"+$("#tokenInput").attr('name')+"="+$("#tokenInput").val();
            $.ajax({
                type: "POST",
                url: "<?php echo site_url(route_to('staff_editor_uploader'));?>",
                data: STRING,
                dataType: 'json',
                cache: false,
                success: function(msg){
                    $('#Total').text(Total);
                    $('#tokenInput').val(msg.token_value);
                    CONTAINER.fadeOut('25', function() {$(this).remove();});
                    CONTAINER.animate({
                        height: 1,          // Avoiding sliding to 0px (flash on IE)
                        paddingTop: "hide",
                        paddingBottom: "hide"
                    })
                        // Then hide
                        .animate({display:"hide"},{queue:true});
                }
            });

        });

        //--------------------------------------
        // toggle uploader
        //--------------------------------------
        $(document).on("click","#btnToggleUploader",function(e){
            //$('#uploader').toggleClass('_hide');
            $('#uploader').slideToggle('fast');
        });


        //--------------------------------------
        // send picked img to tinymce editor
        //--------------------------------------
        $(document).on("click","img#btnInsertFile",function(e){

            var url = $(this).data("url");

            // detect if image dialog opened
            var title = $('.tox-dialog__title', window.parent.document).text();
            var ImageDialog = title.search("Insert/Edit Image");

            if (ImageDialog == 0) {						// 0 = image dialog present because insert/edit text found
                window.parent.postMessage({
                    mceAction: 'customAction',
                    url: url,
                    token: $('#tokenInput').val()
                });
            }else{
                // insert image in Editor
                if (typeof(parent.tinymce) !== "undefined") {
                    parent.tinymce.activeEditor.insertContent('<img src="'+ url +'" width="'+$(this).data('width')+'"  height="'+$(this).data('height')+'">');
                    $('[name=csrf_test_name]', parent.document).val($('#tokenInput').val());
                    parent.tinymce.activeEditor.windowManager.close();
                }
            }
        });


        //--------------------------------------
        // DROPZONE - uploader
        //--------------------------------------


        // DropZone Options
        var dropzoneOptions = {
            dictDefaultMessage: '<div><span class="_bold"><?php echo lang('Admin.form.dropImageHere');?></span>',
            acceptedFiles: "<?php echo $allowed_extensions;  # ".jpeg,.jpg,.png,.gif" ?>",
            paramName: "file",
            maxFilesize: <?php echo $max_upload_size/1000 ?>,
            addRemoveLinks: false,
            init: function () {
                this.on("success", function (file) {
                    if(file.xhr.response !== 'undefined'){
                        xhrResponse = JSON.parse(file.xhr.response)
                        $('#tokenInput').val(xhrResponse.token_value);
                    }
                });
            }
        };
        var myDropzone = new Dropzone(".dropzone", dropzoneOptions);					// manual attach it instead

        // check all files uploaded

        myDropzone.on("success", async function(file, res) {
            if (myDropzone.files[0].status == Dropzone.SUCCESS ) {
                await updateImageList();
                const total = (+$('#Total').text())+1;
                $('#Total').text(total);
                myDropzone.removeAllFiles();
            }
        });
    });
        
    async function updateImageList() {
        try {
            const resp = await fetch('<?php echo site_url(route_to('staff_partial_editor_uploader')); ?>');
            if (!resp.ok) {
                throw new Error(`Response status: ${response.status}`);
                $('#loading').hide();
            }
            const data = await resp.text();
            $('#partial_container').html(data);
            $('#loading').hide();
        } catch (error) {
            $('#loading').hide();
            console.error(error.message);
        }
    }
</script>

</body>
</html>