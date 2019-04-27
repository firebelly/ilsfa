<?php
// Various pre-footer content
if (!is_search() && !is_404()):

  // Try to get post_meta
  $post_meta = !empty($post_meta) ? $post_meta : (!empty($post) && is_object($post) ? get_post_meta($post->ID) : []);

  // Check for page_resources
  if (!empty($post_meta['_cmb2_page_resources'])): ?>
    <div class="page-resources">
      <div class="grid">
        <div class="one-half">
          <?php if (!empty($post_meta['_cmb2_page_resources_intro'])): ?>
            <div class="user-content">
              <?= apply_filters('the_content', $post_meta['_cmb2_page_resources_intro'][0]) ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="one-half tail">
          <?php if (!empty($post_meta['_cmb2_page_resources'])): ?>
            <?php \Firebelly\Utils\get_template_part_with_vars('templates/resources', 'list', ['resources_list' => $post_meta['_cmb2_page_resources'][0], 'class' => 'resources']); ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
  <?php

  // Check for footer_outro
  if (!empty($post_meta['_cmb2_footer_outro'])): ?>
    <div class="footer-outro">
      <div class="user-content dark-bg">
        <?= apply_filters('the_content', $post_meta['_cmb2_footer_outro'][0]) ?>
      </div>
    </div>
  <?php endif; ?>

<?php endif; ?>

<footer class="site-footer">
  <div class="wrap grid">

    <div class="grid-item one-half">
      <ul class="orgs">
        <li class="footer-brand">
          <a href="<?= esc_url(home_url('/')); ?>">
            <svg class="icon logo-icon" aria-hidden="true"><use xlink:href="#logo-icon"/></svg>
            <svg title="<?= get_bloginfo('name'); ?>" class="icon logo-wordmark" aria-hidden="true"><use xlink:href="#logo-wordmark"/></svg>
          </a>
        </li>
        <li>
          <?php if (!empty(\Firebelly\SiteOptions\get_option('ipa_url'))): ?>
            <a href="<?= \Firebelly\SiteOptions\get_option('ipa_url'); ?>" target="_blank"><img alt="IPA logo" src="<?= \Roots\Sage\Assets\asset_path('images/logo-ipa.png'); ?>"></a>
          <?php else: ?>
            <img alt="IPA logo" src="<?= \Roots\Sage\Assets\asset_path('images/logo-ipa.png'); ?>">
          <?php endif; ?>
        </li>
      </ul>
    </div>

    <div class="grid-item one-half contact">
      <ul class="contact-blocks">
        <li>
          <h3>Mailing Address</h3>
          <?php
          $address = \Firebelly\SiteOptions\get_option('contact_address');
          $address_2 = \Firebelly\SiteOptions\get_option('contact_address_2');
          $locality = \Firebelly\SiteOptions\get_option('contact_locality');
          ?>
          <address class="vcard"><a rel="noopener" target="_blank" href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($address.' '.$address_2.' '.$locality) ?>">
            <span class="street-address"><?= $address ?></span>
            <?php if (!empty($address_2)): ?><span class="street-address-2"><?= $address_2 ?></span><?php endif; ?>
            <span class="locality"><?= $locality ?></span></a>
          </address>
        </li>
        <li>
          <h3>Phone</h3>
          <p><?= \Firebelly\SiteOptions\get_option('contact_phone'); ?></p>
          <?php if (!empty(\Firebelly\SiteOptions\get_option('contact_phone_text'))): ?>
            <p><?= \Firebelly\SiteOptions\get_option('contact_phone_text'); ?></p>
          <?php endif; ?>
        </li>
        <li>
          <h3>Fax</h3>
          <p><?= \Firebelly\SiteOptions\get_option('contact_fax'); ?></p>
        </li>
        <li>
          <h3>Email</h3>
          <p><a href="mailto:<?= \Firebelly\SiteOptions\get_option('contact_email'); ?>"><?= \Firebelly\SiteOptions\get_option('contact_email'); ?></a></p>
        </li>
      </ul>

      <div class="footer-copy">
        <?= apply_filters('the_content', \Firebelly\SiteOptions\get_option('footer_copy')) ?>
      </div>
    </div>

  </div>
</footer>
