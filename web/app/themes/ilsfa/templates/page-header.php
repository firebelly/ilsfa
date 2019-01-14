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

<div class="page-header">
  <h1></h1>
</div>
