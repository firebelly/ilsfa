<?php
$organization_post->meta = get_post_meta($organization_post->ID);
$post_image = \Firebelly\Media\get_header_bg($organization_post, ['size' => 'medium']);

if (!empty($organization_post->meta['_cmb2_address'])) {
  $address = unserialize($organization_post->meta['_cmb2_address'][0]);
  $org_address = [
    'address' => $address['address-1'],
    'address_2' => $address['address-2'],
    'locality' => $address['city'] . (!empty($address['state']) ? ', '.$address['state'] : '') . (!empty($address['zip']) ? ' '.$address['zip'] : ''),
  ];
}
?>
<article class="organization">
  <?php if (!empty($post_image)): ?>
    <div class="image" <?= $post_image ?>></div>
  <?php endif; ?>
  <h3>
    <?= $organization_post->post_title ?>
  </h3>

  <ul class="icon-list contact-items -small">
    <?php if (!empty($address)): ?>
      <li class="address-item">
        <svg class="icon icon-location" aria-hidden="true"><use xlink:href="#icon-location"/></svg>
        <address class="vcard">
          <span class="street-address"><?= $org_address['address'] ?></span>
          <span class="street-address-2"><?= $org_address['address_2'] ?></span>
          <span class="locality"><?= $org_address['locality'] ?></span>
        </address>
      </li>
    <?php endif; ?>

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
