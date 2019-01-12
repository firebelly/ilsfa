<?php
$event_post->meta = get_post_meta($event_post->ID);
$address = unserialize($event_post->meta['_cmb2_address'][0]);
$post_image = \Firebelly\Media\get_header_bg($event_post, ['size' => 'medium']);
?>
<article class="event">
  <?php if (!empty($post_image)): ?>
    <div class="image" <?= $post_image ?>></div>
  <?php endif; ?>
  <h3><?= $event_post->post_title ?></h3>
  <ul class="details">
  	<li class="date">
  		<?= \Firebelly\PostTypes\Event\get_dates($event_post); ?>
  	</li>
  	<li class="location">
			<?= $address['city'] ?>, <?= $address['state'] ?>
  	</li>
  </ul>

  <a class="button icon round" href="<?= $event_post->meta['_cmb2_event_url'][0] ?>" title="Event Details"><svg class="icon-arrow" aria-hidden="true"><use xlink:href="#icon-arrow"/></svg></a>
</article>
