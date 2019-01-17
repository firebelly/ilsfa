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
?>
<header class="page-header tertiary">
  <div class="title-wrap">
    <h1 class="page-title"><?= nl2br($intro_title); ?></h1>
  </div>
  <?php if (!empty($post_meta['_cmb2_intro_supporting_statement'])): ?>
    <div class="supporting-statement user-content">
      <?= apply_filters('the_content', $post_meta['_cmb2_intro_supporting_statement'][0]) ?>
    </div>
  <?php endif; ?>
  <div class="jump-to hidden">Jump To <svg class="icon icon-arrow" aria-hidden="true"><use xlink:href="#icon-arrow"/></svg></div>
</header>
