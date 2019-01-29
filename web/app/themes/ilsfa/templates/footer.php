<?php
// Check for footer_outro
$post_meta = !empty($post_meta) ? $post_meta : (!empty($post) && is_object($post) ? get_post_meta($post->ID) : []);
if (!is_search() && !empty($post_meta['_cmb2_footer_outro'])): ?>
<div class="footer-outro">
  <div class="user-content">
    <?= apply_filters('the_content', $post_meta['_cmb2_footer_outro'][0]) ?>
  </div>
</div>
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
          <a href="#"><img alt="IPA logo" src="<?= \Roots\Sage\Assets\asset_path('images/logo-ipa.png'); ?>"></a>
        </li>
      </ul>
    </div>

    <div class="grid-item one-half contact">
      <ul class="contact-blocks">
        <li>
          <h3>Mailing Address</h3>
          <address class="vcard">
            <span class="street-address"><?= \Firebelly\SiteOptions\get_option('contact_address'); ?></span>
            <span class="street-address-2"><?= \Firebelly\SiteOptions\get_option('contact_address_2'); ?></span>
            <span class="locality"><?= \Firebelly\SiteOptions\get_option('contact_locality'); ?></span>
          </address>
        </li>
        <li>
          <h3>Phone</h3>
          <p><?= \Firebelly\SiteOptions\get_option('contact_phone'); ?></p>
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
