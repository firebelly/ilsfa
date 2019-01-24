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

// Featured image? (can send along $text_only=1 to just use text for header, e.g. Single Program)
$has_featured_image = empty($text_only) && has_post_thumbnail($post);
?>

<header class="page-header<?= $has_featured_image ? ' has-image' : '' ?>">
  <div class="title-wrap">
    <h1 class="page-title"><?= nl2br($intro_title); ?></h1>
  </div>
  <div class="jump-to hidden">Jump To <svg class="icon icon-arrow" aria-hidden="true"><use xlink:href="#icon-arrow"/></svg></div>
  <?php if ($has_featured_image): ?>
    <div class="image-wrap">
      <div class="image -shadow" <?= \Firebelly\Media\get_header_bg($post) ?>>
        <div class="filter white-multiply"></div><div class="filter blue-screen"></div><div class="filter blue-multiply"></div>
      </div>
    </div>
  <?php endif ?>
  <?php if (!empty($post_meta['_cmb2_intro_supporting_statement'])): ?>
    <div class="supporting-statement -white">
      <div class="user-content">
        <?= apply_filters('the_content', $post_meta['_cmb2_intro_supporting_statement'][0]) ?>
      </div>
    </div>
  <?php endif; ?>
</header>
