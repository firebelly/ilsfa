<?php
/*
  Template name: Events
*/

// Get all post_meta
$post = get_page_by_path('/events/');
$post_meta = get_post_meta($post->ID);
?>

<?php
get_template_part('templates/page', 'header-tertiary');
?>

<div class="events-listing">
  <?php if (0 && $events = \Firebelly\PostTypes\Event\get_events(['output' => 'array'])): ?>
    <ul class="cards compact-grid">
    <?php foreach ($events as $event): ?>
      <li class="item">
        <?php \Firebelly\Utils\get_template_part_with_vars('templates/article', 'event', ['event_post' => $event]); ?>
      </li>
    <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
