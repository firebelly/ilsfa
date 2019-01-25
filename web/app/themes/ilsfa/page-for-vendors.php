<?php
/*
  Template name: For Vendors
*/

// Get all post_meta
$post_meta = get_post_meta($post->ID);
?>

<?php
get_template_part('templates/page', 'header');
?>

<div class="page-content -breakout-images">
  <div class="user-content">
    <?= apply_filters('the_content', $post->post_content); ?>
  </div>
</div>

<?php // Vendor Requirement Blocks ?>
<?php if (!empty($post_meta['_cmb2_vendor_requirements_blocks'])): ?>
<div data-jumpto="requirements" class="cards-image-block vendor-requirements" <?= !empty($post_meta['_cmb2_vendor_requirements_background']) ? ' style="background-image: url('.$post_meta['_cmb2_vendor_requirements_background'][0].')"' : '' ?>>
  <div class="filter white-multiply"></div><div class="filter blue-screen"></div><div class="filter blue-multiply"></div>
  <h2 class="h1">Requirements of being an approved vendor</h2>
  <ul class="cards compact-grid">
    <?php foreach (unserialize($post_meta['_cmb2_vendor_requirements_blocks'][0]) as $block): ?>
      <li class="item">
        <h3><?= $block['headline'] ?></h3>
        <div class="user-content">
          <?= apply_filters('the_content', $block['body']) ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div data-jumpto="Apply"></div>
