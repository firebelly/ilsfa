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
?>

<?php
get_template_part('templates/page', 'header-tertiary');
?>

<div class="page-content">
  <div class="user-content">
    <div class="entry-content">
      <?= apply_filters('the_content', $post->post_content); ?>
    </div>
  </div>
  <ul class="icon-list contact-items -small">
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
    <?php if (!empty($post_meta['_cmb2_event_url'])): ?>
      <li class="link">
        <svg class="icon icon-link" aria-hidden="true"><use xlink:href="#icon-link"/></svg>
        <a target="_blank" rel="noopener" href="<?= $post_meta['_cmb2_event_url'][0] ?>">Event URL</a>
      </li>
    <?php endif; ?>
  </ul>
</div>
