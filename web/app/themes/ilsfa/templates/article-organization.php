<?php
$organization_post->meta = get_post_meta($organization_post->ID);
$org_type = \Firebelly\Utils\get_first_term($organization_post, 'organization_type');
$address = [];

if (!empty($organization_post->meta['_cmb2_address'])) {
  $address = unserialize($organization_post->meta['_cmb2_address'][0]);
  $org_address = [
    'address' => $address['address-1'],
    'address_2' => $address['address-2'],
    'locality' => $address['city'] . (!empty($address['state']) ? ', '.$address['state'] : '') . (!empty($address['zip']) ? ' '.$address['zip'] : ''),
  ];
}
$region_links = [];
if ($regions = get_the_terms($organization_post->ID, 'region')) {
  foreach ($regions as $term) {
    $region_links[] = '<a href="'.add_query_arg('region', $term->slug, (\Firebelly\Ajax\is_ajax() ? '' : '/'.$org_type->slug.'/')).'#organizations">'.$term->name.'</a>';
  }
}
$org_category_links = [];
if ($categories = get_the_terms($organization_post->ID, 'organization_category')) {
  foreach ($categories as $term) {
    $org_category_links[] = '<a href="'.add_query_arg('org_category', $term->slug, (\Firebelly\Ajax\is_ajax() ? '' : '/'.$org_type->slug.'/')).'#organizations">'.$term->name.'</a>';
  }
}
?>
<article class="organization">
  <?php if (!empty($org_category_links)): ?>
    <h4 class="category">
      <svg class="icon icon-category" aria-hidden="true"><use xlink:href="#icon-category"/></svg>
      <?= implode(', ', $org_category_links) ?>
    </h4>
  <?php endif ?>
  <h3 class="toggler">
    <?= $organization_post->post_title ?>
  </h3>

  <ul class="icon-list contact-items -small">
    <?php if (!empty($address)): ?>
      <li class="address-item">
        <svg class="icon icon-location" aria-hidden="true"><use xlink:href="#icon-location"/></svg>
        <address class="vcard"><a rel="noopener" target="_blank" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($org_address['address'].' '.$org_address['address_2'].' '.$org_address['locality']) ?>">
          <span class="street-address"><?= $org_address['address'] ?></span><br>
          <?php if (!empty($org_address['address_2'])): ?><span class="street-address-2"><?= $org_address['address_2'] ?></span><br><?php endif; ?>
          <span class="locality"><?= $org_address['locality'] ?></span>
        </a></address>
      </li>
    <?php endif; ?>

    <?php if (!empty($regions)): ?>
      <li class="region-item">
        <svg class="icon icon-location" aria-hidden="true"><use xlink:href="#icon-location"/></svg>
        <?= implode(', ', $region_links) ?>
      </li>
    <?php endif ?>

    <?php if (!empty($organization_post->meta['_cmb2_email'])): ?>
      <li class="email-item">
        <svg class="icon icon-email" aria-hidden="true"><use xlink:href="#icon-email"/></svg>
        <a href="mailto:<?= $organization_post->meta['_cmb2_email'][0] ?>">Email</a>
      </li>
    <?php endif; ?>

    <?php if (!empty($organization_post->meta['_cmb2_phone'])): ?>
      <li class="phone-item">
        <svg class="icon icon-phone" aria-hidden="true"><use xlink:href="#icon-phone"/></svg>
        <?= $organization_post->meta['_cmb2_phone'][0] ?>
      </li>
    <?php endif; ?>

    <?php if (!empty($organization_post->meta['_cmb2_website'])): ?>
      <li class="website-item">
        <svg class="icon icon-link" aria-hidden="true"><use xlink:href="#icon-link"/></svg>
        <a rel="noopener" target="_blank" href="<?= $organization_post->meta['_cmb2_website'][0] ?>">Website</a>
      </li>
    <?php endif; ?>
  </ul>

  <?php if (!empty($organization_post->meta['_cmb2_description'])): ?>
    <div class="user-content description">
      <?= apply_filters('the_content', $organization_post->meta['_cmb2_description'][0]) ?>
    </div>
  <?php endif; ?>

  <a href="#" class="toggler">
    <span class="-closed">
      <h5>Details</h5>
      <svg class="icon icon-plus" aria-hidden="true"><use xlink:href="#icon-plus"/></svg>
    </span>
    <span class="-opened">
      <h5>Show Less</h5>
      <svg class="icon icon-minus" aria-hidden="true"><use xlink:href="#icon-minus"/></svg>
    </span>
  </a>
</article>
