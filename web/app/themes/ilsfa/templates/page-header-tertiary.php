<?php
use Roots\Sage\Titles;

// Pull 404 page for content areas
if (is_404()) {
  $post = get_page_by_path('/404-error/');
}

// Get all post_meta
$post_meta = get_post_meta($post->ID);

// Headline set?
if (!empty($post_meta['_cmb2_intro_title'])) {
  $intro_title = $post_meta['_cmb2_intro_title'][0];
} else {
  // Fallback to page title
  $intro_title = Titles\title();
}
?>
<header class="page-header tertiary">
  <div class="title-wrap">
    <h1 class="page-title"><?= nl2br($intro_title); ?></h1>
  </div>
  <?php if (!empty($post_meta['_cmb2_intro_supporting_statement'])): ?>
    <div class="supporting-statement">
      <div class="user-content dark-bg">
        <?= apply_filters('the_content', $post_meta['_cmb2_intro_supporting_statement'][0]) ?>
      </div>
    </div>
  <?php endif; ?>
  <?php if (empty($nojumpto)): ?>
    <?php get_template_part('templates/jumpto-links'); ?>
  <?php endif; ?>
</header>
