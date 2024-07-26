<div id="images" class="images">
    <?php $counter = 0; ?>
    <?php foreach ($thumb_files as $file): ?>
        <?php
        $counter ++;
        $filePath = str_replace(array('../','thumbs/'),'',$file);
        if(!file_exists($filePath)){
            continue;
        }
        $fileName = basename($filePath);
        $LargeImageURL = str_replace(FCPATH, base_url().'/', $filePath);

        # check if file exists
        list($width, $height, $type, $attr) = getimagesize($filePath);

        $img_url = str_replace(FCPATH, base_url().'/', $file);
        ?>
        <div id="IMG<?php echo $counter;?>" class="wrap">
            <?php
                echo img($img_url, false, [
                    'class' => 'img-thumbnail',
                    'id' => 'btnInsertFile',
                    'title' => $fileName,
                    'alt' => $fileName,
                    'data-url' => $LargeImageURL,
                    'data-width' => $width,
                    'data-height' => $height
                ]);
            ?>
            <div class="info">
                <div class="buttons">
                    <a id="btnDelete" class="tooltip" title="<?php echo lang('Admin.form.delete'); ?>" data-id="IMG<?php echo $counter;?>" data-file="<?php echo $fileName; ?>"><span><i class="fa fa-trash-o"></i></span></a>
                    <a class="tooltip" title="<?php echo lang('Admin.form.download'); ?>" href="<?php echo $LargeImageURL; ?>" download><i class="fa fa-download"></i></a>
                    <a rel="lightbox" class="tooltip" href="<?php echo $LargeImageURL; ?>" title="<?php echo $fileName; ?>" data-title="<?php echo $fileName; ?>"><i class="fa fa-eye"></i></a>
                </div>
                <div class="name" title="<?php echo $fileName; ?>"><?php echo resume_content($fileName,13,'..'); ?></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>