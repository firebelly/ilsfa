<?php
$program_post->meta = get_post_meta($program_post->ID);
$post_image = \Firebelly\Media\get_header_bg($program_post, ['size' => 'medium']);
?>
<article class="event">
  <?php if (!empty($post_image)): ?>
    <div class="image" <?= $post_image ?>></div>
  <?php endif; ?>
  <h3><a href="<?= get_permalink($program_post) ?>"><?= $program_post->post_title ?></a></h3>
  <ul class="details">
  </ul>
  <a class="button" href="<?= get_permalink($program_post) ?>">More</a>
</article>
