<?php
/*
  Template name: Homepage
*/
use Roots\Sage\Titles;

// Get all post_meta
$post_meta = get_post_meta($post->ID);

// Headline set?
if (!empty($post_meta['_cmb2_intro_headline'])) {
  $intro_title = $post_meta['_cmb2_intro_headline'][0];
} else {
  // Fallback to page title
  $intro_title = Titles\title();
}

// Intro body set?
if (!empty($post_meta['_cmb2_intro_body'])) {
  $intro_body = $post_meta['_cmb2_intro_body'][0];
}

// Intro Links?
if (!empty(get_post_meta($post->ID, '_cmb2_intro_links', true))) {
  $intro_link = get_post_meta($post->ID, '_cmb2_intro_links', true)[0];
}

?>

<header class="page-header">
  <div class="banner-image" <?= \Firebelly\Media\get_header_bg($post) ?>></div>
  <h2 class="page-title fb-container-content"><?= $intro_title; ?></h2>
</header>

<div class="content">
  <?= apply_filters('the_content', $post->post_content) ?>
</div>

<div class="page-section fb-container-md">
  <h2 class="h1 text-center">Featured</h2>
  <div class="stories mobile-gutter">
  </div>
</div>
