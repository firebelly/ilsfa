<?php
use Roots\Sage\Titles;

// Get all post_meta
$post_meta = get_post_meta($post->ID);

// Headline set?
if (!empty($post_meta['_cmb2_intro_title'])) {
  $intro_title = $post_meta['_cmb2_intro_title'][0];
} else {
  // Fallback to page title
  $intro_title = Titles\title();
}
// Featured image?
$has_featured_image = has_post_thumbnail($post);
?>

<header class="page-header <?= $has_featured_image ? 'has-image' : '' ?>">
  <div class="title-wrap">
    <h1 class="page-title"><?= nl2br($intro_title); ?></h1>
  </div>
  <?php if ($has_featured_image): ?>
    <div class="banner-image-wrap">
      <div class="banner-image" <?= \Firebelly\Media\get_header_bg($post) ?>></div>
    </div>
  <?php endif ?>
  <?php if (!empty($post_meta['_cmb2_intro_supporting_statement'])): ?>
    <div class="supporting-statement">
      <?= apply_filters('the_content', $post_meta['_cmb2_intro_supporting_statement'][0]) ?>
    </div>
  <?php endif; ?>
</header>
