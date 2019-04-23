<?php
$event_post_meta = get_post_meta($event_post->ID);
if (!empty($event_post_meta['_cmb2_address'])) {
  $address = unserialize($event_post_meta['_cmb2_address'][0]);
}
$post_image = \Firebelly\Media\get_header_bg($event_post, ['size' => 'medium']);
?>
<article class="event">
  <?php if (!empty($post_image)): ?>
    <div class="image" <?= $post_image ?>></div>
  <?php endif; ?>
  <h3><a href="<?= get_permalink($event_post) ?>"><?= $event_post->post_title ?></a></h3>
  <?php if (!empty($event_post->post_content)): ?>
    <div class="user-content">
      <p><?= \Firebelly\Utils\get_excerpt($event_post, 25) ?></p>
    </div>
  <?php endif; ?>

  <ul class="icon-list details -small">
    <li class="date">
      <svg class="icon icon-date" aria-hidden="true"><use xlink:href="#icon-date"/></svg>
      <?= \Firebelly\PostTypes\Event\get_dates($event_post); ?>
    </li>
    <?php if (!empty($address)): ?>
      <li class="location">
        <svg class="icon icon-location" aria-hidden="true"><use xlink:href="#icon-location"/></svg>
        <?= $address['city'] ?><?= !empty($address['state']) ? ', '.$address['state'] : '' ?>
      </li>
    <?php endif; ?>
  </ul>
  <a class="button" href="<?= get_permalink($event_post) ?>">Event Details</a>
</article>
