<?php
$post_image = \Firebelly\Media\get_header_bg($announcement_post, ['size' => 'medium']);
?>
<article class="announcement">
  <h4><?= date('m/d/Y', strtotime($announcement_post->post_date)) ?></h4>
  <?php if (!empty($post_image)): ?>
    <div class="image" <?= $post_image ?>></div>
  <?php endif; ?>
  <h3><a href="<?= get_permalink($announcement_post) ?>"><?= $announcement_post->post_title ?></a></h3>
  <p><?= \Firebelly\Utils\get_excerpt($announcement_post, 25) ?></p>
</article>
