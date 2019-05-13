<?php
// Get all post_meta
$post_meta = get_post_meta($post->ID);

// Get address for program...
if (!empty($post_meta['_cmb2_address'])) {
  $address = unserialize($post_meta['_cmb2_address'][0]);
  $address_info = [
    'address' => $address['address-1'],
    'address_2' => $address['address-2'],
    'locality' => $address['city'] . (!empty($address['state']) ? ', '.$address['state'] : '') . (!empty($address['zip']) ? ' '.$address['zip'] : ''),
  ];
}
$topic_links = [];
if ($topics = get_the_terms($post->ID, 'topic')) {
  foreach ($topics as $term) {
    // $topic_links[] = '<a href="'.add_query_arg('org_category', $term->slug, (\Firebelly\Ajax\is_ajax() ? '' : '/events/')).'#events">'.$term->name.'</a>';
    $topic_links[] = '<span class="term">' . $term->name . '</span>';
  }
}
$region_links = [];
if ($regions = get_the_terms($post->ID, 'region')) {
  foreach ($regions as $term) {
    $region_links[] = '<a href="'.add_query_arg('region', $term->slug, (\Firebelly\Ajax\is_ajax() ? '' : '/events/')).'#events">'.$term->name.'</a>';
  }
}
?>

<?php
get_template_part('templates/page', 'header');
?>

<div class="page-content">
  <div class="user-content">
    <div class="entry-content">
      <?= apply_filters('the_content', $post->post_content); ?>
    </div>
  </div>
  <ul class="icon-list contact-items -small">
    <?php if (!empty($topic_links)): ?>
      <li class="category">
        <svg class="icon icon-category" aria-hidden="true"><use xlink:href="#icon-category"/></svg>
        <?= implode(', ', $topic_links) ?>
      </li>
    <?php endif ?>
    <li class="date">
        <svg class="icon icon-date" aria-hidden="true"><use xlink:href="#icon-date"/></svg>
        <?= \Firebelly\PostTypes\Event\get_dates($post); ?>
    </li>
    <li class="address-item">
      <svg class="icon icon-location" aria-hidden="true"><use xlink:href="#icon-location"/></svg>
      <address class="vcard"><a rel="noopener" target="_blank" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($address_info['address'].' '.$address_info['address_2'].' '.$address_info['locality']) ?>">
        <span class="street-address"><?= $address_info['address'] ?></span><br>
        <?php if (!empty($address_info['address_2'])): ?><span class="street-address-2"><?= $address_info['address_2'] ?></span><br><?php endif; ?>
        <span class="locality"><?= $address_info['locality'] ?></span>
      </a></address>
    </li>
    <?php if (!empty($regions)): ?>
      <li class="region-item">
        <svg class="icon icon-location" aria-hidden="true"><use xlink:href="#icon-location"/></svg>
        <?= implode(', ', $region_links) ?>
      </li>
    <?php endif ?>
    <?php if (!empty($post_meta['_cmb2_event_url'])): ?>
      <li class="link">
        <svg class="icon icon-link" aria-hidden="true"><use xlink:href="#icon-link"/></svg>
        <a target="_blank" rel="noopener" href="<?= $post_meta['_cmb2_event_url'][0] ?>">Event URL</a>
      </li>
    <?php endif; ?>
  </ul>
</div>
