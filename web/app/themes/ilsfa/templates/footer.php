<footer class="site-footer">
  <div class="wrap grid">

    <div class="grid-item one-half">
      <ul class="orgs">
        <li>
          <h1 class="brand"><a href="<?= esc_url(home_url('/')); ?>">
            <svg class="icon icon-logo" aria-hidden="true"><use xlink:href="#icon-logo"/></svg>
            <span class="name"><?= get_bloginfo('name'); ?></span>
          </a></h1>
        </li>
        <li>
          IPA
        </li>
      </ul>
    </div>

    <div class="grid-item one-half contact">
      <ul class="contact-items">
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

      <div class="partners">
        <h3>Our Partners</h3>
        <ul>
          <li>Grid</li>
          <li>AECOM</li>
          <li>Shelton Solutions</li>
        </ul>
      </div>
    </div>

  </div>
</footer>
