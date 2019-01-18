<?php
// Defaults
$external_link = '';
$excerpt = \Firebelly\Utils\get_excerpt($post);

switch ($post->post_type) {
  case 'program':
    break;

  default:
    if ($post->post_parent) {
      $parent_post = get_post($post->post_parent);
      $subtitle = $parent_post->post_title;
    }
}

// If no custom $permalink set (e.g. Research), get post permalink
if (empty($permalink)) {
  $permalink = get_permalink($post->ID);
}
?>
<article class="search-result">
  <?php if (0 && $post->post_type=='post'): ?>
    <h4><?= date('m/d/Y', strtotime($post->post_date)) ?></h4>
  <?php endif ?>
  <h2><a href="<?= get_permalink($post) ?>"><?= $post->post_title ?></a></h2>
  <p class="post-url h4"><a <?= $external_link ?>href="<?= $permalink ?>"><?= $permalink ?></a></p>
  <p><?= \Firebelly\Utils\get_excerpt($post, 25) ?></p>
</article>
