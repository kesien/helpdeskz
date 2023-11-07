<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('staff/template');
$this->section('content');
?>
<div class="container mt-5">
    <a class="inactive_link" href="<?php echo site_url(); ?>">
        <?php echo lang('Client.kb.title'); ?> &nbsp; /
    </a>
    <?php
    if ($article->category > 0) {
        if ($parents = kb_parents($category->parent)) {
            foreach ($parents as $item) {
                echo ' &nbsp; <a class="inactive_link" href="' . site_url(route_to('staff_kb_view_category', $item->id)) . '">' . $item->name . ' &nbsp; /</a>';
            }
        }
        echo ' &nbsp; <a class="static_link" href="' . site_url(route_to('staff_kb_view_category', $category->id)) . '">' . $category->name . '</a>';
    }
    ?>
    <h2 class="sub_heading mt-3 mb-3"><i class="fas fa-file-alt kb_article_icon_lg"></i>
        <?php echo $article->title ?>
    </h2>
    <div class="article_description mb-5">
        <?php echo lang_replace('Client.kb.postedOn', ['%date%' => dateFormat($article->date)]); ?>
        <hr>
    </div>
    <div>
        <?php echo $article->content; ?>
    </div>

    <?php if ($attachments = article_files($article->id)): ?>
        <div class="knowledgebasearticleattachment">
            <?php echo lang('Client.form.attachments'); ?>
        </div>
        <?php foreach ($attachments as $item): ?>
            <div>
                <span class="knowledgebaseattachmenticon"></span>
                <a href="<?php echo site_url(route_to('kb_view_article', $article->id) . '?download=' . $item->id); ?>"
                    target="_blank">
                    <?php echo $item->name; ?> (
                    <?php echo $item->filesize; ?>)
                </a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php
$this->endSection();