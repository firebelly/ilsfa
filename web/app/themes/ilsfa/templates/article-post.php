<?php
$post_image = \Firebelly\Media\get_header_bg($post, ['size' => 'medium']);
$post_meta = get_post_meta($post->ID);
?>
<article class="announcement">
  <h4><?= date('m/d/Y', strtotime($post->post_date)) ?></h4>
  <?php if (!empty($post_image)): ?>
    <div class="image" <?= $post_image ?>></div>
  <?php endif; ?>
  <h3><a href="<?= get_permalink($post) ?>"><?= $post->post_title ?></a></h3>
  <p><?= \Firebelly\Utils\get_excerpt($post, 25) ?></p>


  <?php if (empty($noresources)): ?>
    <?php \Firebelly\Utils\get_template_part_with_vars('templates/resources', 'list', ['post_meta' => $post_meta]); ?>
  <?php endif; ?>
</article>
