<?php
/*
  Template name: EJC
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

<?php // Midpage Prompt with Image ?>
<?php if (!empty($post_meta['_cmb2_midpage_prompt'])): ?>
  <div class="midpage-prompt-with-image">
    <div class="grid">
      <div class="grid-item one-half">
        <div class="text user-content dark-bg">
          <?= apply_filters('the_content', $post_meta['_cmb2_midpage_prompt'][0]) ?>
        </div>
      </div>
      <?php if (!empty($post_meta['_cmb2_midpage_prompt_image'])): ?>
        <div class="grid-item one-half">
          <div class="image-wrap -inset-shadow -expanded">
            <div class="image" <?= \Firebelly\Media\get_header_bg($post_meta['_cmb2_midpage_prompt_image_id'][0], ['size' => 'large']) ?>>
              <div class="filter white-multiply"></div><div class="filter blue-screen"></div><div class="filter blue-multiply"></div>
            </div>
          </div>
        </div>
      <?php endif ?>
    </div>
  </div>
<?php endif; ?>

<?php // Eligibility Blocks ?>
<?php if (!empty($post_meta['_cmb2_ejc_blocks'])): ?>
  <?php if (!empty($post_meta['_cmb2_ejc_blocks_intro'])): ?>
    <div class="page-content block-intro -breakout-images">
      <div class="user-content">
        <?= apply_filters('the_content', $post_meta['_cmb2_ejc_blocks_intro'][0]) ?>
      </div>
    </div>
  <?php endif; ?>
  <ul class="midpage-blocks">
    <?php foreach (unserialize($post_meta['_cmb2_ejc_blocks'][0]) as $block): ?>
      <li class="grid">
        <div class="grid-item one-half">
          <h3><?= $block['headline'] ?></h3>
        </div>
        <div class="grid-item one-half">
          <div class="text user-content">
            <?= apply_filters('the_content', $block['body']) ?>
          </div>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
