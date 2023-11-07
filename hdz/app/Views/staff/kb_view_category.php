<?php
/**
 * @var $this \CodeIgniter\View\View
 */
$this->extend('staff/template');
$this->section('content');
?>

<div class="container mt-5">
    <?php if ($category->id == 0): ?>
        <h1 class="heading mb-4">
            <?php echo lang('Client.kb.title'); ?>
        </h1>
    <?php else: ?>
        <div class="mb-5">
            <a class="inactive_link" href="<?php echo site_url(route_to('staff_kb_categories')); ?>">
                <?php echo lang('Client.kb.title'); ?>
                &nbsp;
                /
            </a>
            <?php
            if ($parents = kb_parents($category->parent)) {
                foreach ($parents as $item) {
                    echo ' &nbsp; <a class="inactive_link" href="' . site_url(route_to('staff_kb_view_category', $item->id)) . '">' . $item->name . ' &nbsp; /</a>';
                }
            }
            echo ' &nbsp; <a class="static_link" href="' . site_url(route_to('staff_kb_view_category', $category->id)) . '">' . $category->name . '</a>';
            ?>
        </div>
        <h2 class="sub_heading mb-3">
            <?php echo $category->name ?>
        </h2>
    <?php endif; ?>
    <div class="row">
        <div class="col">
            <?php if ($categories = kb_categories($category->id)): ?>
                <div class="row">
                    <?php foreach ($categories as $item): ?>
                        <?php $total_articles = kb_count_articles($item->id); ?>
                        <?php if ($total_articles > 0): ?>
                            <div class="col-lg-6 mt-4">
                                <div class="pt-2">
                                    <a class="kb_category"
                                        href="<?php echo site_url(route_to('staff_kb_view_category', $item->id)); ?>">
                                        <i class="fas fa-folder-open kb_article_icon pr-2"></i>
                                        <?php echo $item->name; ?>
                                    </a>
                                    <span class="text-muted float-right">
                                        <?php echo '(' . $total_articles . ')'; ?>
                                    </span>
                                    <hr>
                                </div>
                                <?php foreach (kb_articles_category($item->id) as $article): ?>
                                    <div class="py-2">
                                        <i class="fas fa-file-alt kb_article_icon pr-3"></i>
                                        <a href="<?php echo site_url(route_to('staff_kb_view_article', $article->id)); ?>">
                                            <?php echo $article->title; ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($total_articles > site_config('kb_articles')): ?>
                                    <div class="py-2">
                                        <a class="static_link"
                                            href="<?php echo site_url(route_to('staff_kb_view_category', $item->id)); ?>">
                                            &raquo;
                                            <?php echo lang('Client.kb.moreTopics'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Articles -->
            <?php if ($articles = kb_articles($category->id)): ?>
                <div class="list-group mt-5">
                    <?php foreach ($articles as $item): ?>
                        <div class="list-group-item border-left-0  border-right-0">

                            <div class="float-left">
                                <div class="float-left mr-3">
                                    <i class="fas fa-file-alt kb_article_icon_lg"></i>
                                </div>
                                <div class="mb-1">
                                    <a class="font-weight-bold"
                                        href="<?php echo site_url(route_to('staff_kb_view_article', $item->id)); ?>">
                                        <?php echo $item->title; ?>
                                    </a>
                                </div>

                                <div class="text-muted">
                                    <?php echo resume_content($item->content, site_config('kb_maxchar')); ?>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <?php echo lang('Admin.kb.noArticle'); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$this->endSection();