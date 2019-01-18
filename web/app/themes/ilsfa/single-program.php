<?php
// Get all post_meta
$post_meta = get_post_meta($post->ID);

// Get address for program...
if (!empty($post_meta['_cmb2_address'])) {
  $address = unserialize($post_meta['_cmb2_address'][0]);
  $contact_info = [
    'address' => $address['address-1'],
    'address_2' => $address['address-2'],
    'locality' => $address['city'] . (!empty($address['state']) ? ', '.$address['state'] : '') . (!empty($address['zip']) ? ' '.$address['zip'] : ''),
    'phone' => (!empty($post_meta['_cmb2_phone']) ? $post_meta['_cmb2_phone'][0] : ''),
    'fax' => (!empty($post_meta['_cmb2_fax']) ? $post_meta['_cmb2_fax'][0] : ''),
    'email' => (!empty($post_meta['_cmb2_email']) ? $post_meta['_cmb2_email'][0] : ''),
  ];
} else {
  // ... or fallback to footer info from Site Options
  $contact_info = [
    'address' => \Firebelly\SiteOptions\get_option('contact_address'),
    'address_2' => \Firebelly\SiteOptions\get_option('contact_address_2'),
    'locality' => \Firebelly\SiteOptions\get_option('contact_locality'),
    'phone' => \Firebelly\SiteOptions\get_option('contact_phone'),
    'fax' => \Firebelly\SiteOptions\get_option('contact_fax'),
    'email' => \Firebelly\SiteOptions\get_option('contact_email'),
  ];
}
?>

<?php
get_template_part('templates/page', 'header-tertiary');
?>

<?php // Main content + featured image + stat ?>
<div class="content-image-stat">
  <div class="page-content">
    <div class="user-content">
      <?= apply_filters('the_content', $post->post_content); ?>
    </div>
  </div>

  <div class="image-content">
    <div class="image-wrap -expanded -inset-shadow">
      <div class="image" <?= \Firebelly\Media\get_header_bg($post, ['size' => 'large']) ?>></div>
    </div>
  </div>
  <div class="stat-content">
    <?php if (!empty($post_meta['_cmb2_stat_figure'])): ?>
      <dl class="stat">
        <dt><?= $post_meta['_cmb2_stat_figure'][0] ?></dt>
        <?php if (!empty($post_meta['_cmb2_stat_label'])): ?>
          <dd><?= $post_meta['_cmb2_stat_label'][0] ?></dd>
        <?php endif; ?>
      </dl>
    <?php endif; ?>
  </div>
</div>

<?php // Apply ?>
<div class="apply">
  <h2>Apply</h2>
  <?php if (!empty($post_meta['_cmb2_application_body'])): ?>
    <div class="user-content">
      <?= apply_filters('the_content', $post_meta['_cmb2_application_body'][0]) ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($post_meta['_cmb2_application_url'])): ?>
    <div class="actions">
      <a href="<?= $post_meta['_cmb2_application_url'][0] ?>" class="button"><?= !empty($post_meta['_cmb2_application_prompt']) ? $post_meta['_cmb2_application_prompt'][0] : 'Apply' ?> <svg class="icon icon-arrow" aria-hidden="true"><use xlink:href="#icon-arrow"/></svg></a>
    </div>
  <?php endif; ?>
  <?php if (!empty($post_meta['_cmb2_application_supporting_copy'])): ?>
    <div class="user-content supporting-copy">
      <?= apply_filters('the_content', $post_meta['_cmb2_application_supporting_copy'][0]) ?>
    </div>
  <?php endif; ?>
</div>

<?php // Contact & Vendor + tools ?>
<div class="grid contact-vendors-tools">
  <div class="grid-item one-half contact">
    <h2>Contact us</h2>

    <?php if (!empty($post_meta['_cmb2_contact_intro'])): ?>
      <div class="user-content">
        <?= apply_filters('the_content', $post_meta['_cmb2_contact_intro'][0]) ?>
      </div>
    <?php endif; ?>

    <ul class="icon-list contact-items -small">
      <li class="address-item">
        <svg class="icon icon-location" aria-hidden="true"><use xlink:href="#icon-location"/></svg>
        <address class="vcard">
          <span class="street-address"><?= $contact_info['address'] ?></span>
          <span class="street-address-2"><?= $contact_info['address_2'] ?></span>
          <span class="locality"><?= $contact_info['locality'] ?></span>
        </address>
      </li>
      <li class="phone-item">
        <svg class="icon icon-phone" aria-hidden="true"><use xlink:href="#icon-phone"/></svg>
        <?= $contact_info['phone'] ?>
      </li>
      <li class="fax-item">
        <svg class="icon icon-fax" aria-hidden="true"><use xlink:href="#icon-fax"/></svg>
        <?= $contact_info['fax'] ?>
      </li>
      <li class="email-item">
        <svg class="icon icon-email" aria-hidden="true"><use xlink:href="#icon-email"/></svg>
        <a href="mailto:<?= $contact_info['email'] ?>"><?= $contact_info['email'] ?></a>
      </li>
    </ul>
  </div>

  <div class="grid-item one-half vendors-tools">
    <h2>Vendors & tools</h2>
    <?php if (!empty($post_meta['_cmb2_vendors_intro'])): ?>
      <div class="user-content">
        <?= apply_filters('the_content', $post_meta['_cmb2_vendors_intro'][0]) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($post_meta['_cmb2_vendors_tools'])): ?>
      <ul class="resources">
        <?php foreach (unserialize($post_meta['_cmb2_vendors_tools'][0]) as $vendor_tool): ?>
          <?php if (!empty($vendor_tool['resource'])): ?>
            <li><a download="<?= basename($vendor_tool['resource']) ?>" target="_blank" href="<?= $vendor_tool['resource'] ?>"><?= $vendor_tool['title'] ?> <svg class="icon icon-document" aria-hidden="true"><use xlink:href="#icon-document"/></svg></a></li>
          <?php else: ?>
            <li><a target="_blank" href="<?= $vendor_tool['url'] ?>"><?= $vendor_tool['title'] ?> <svg class="icon icon-link" aria-hidden="true"><use xlink:href="#icon-link"/></svg></a></li>
          <?php endif ?>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>
