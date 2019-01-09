<?php
/*
  Template name: Homepage
*/
use Roots\Sage\Titles;

// Get all post_meta
$post_meta = get_post_meta($post->ID);

// Headline set?
if (!empty($post_meta['_cmb2_intro_title'])) {
  $intro_title = $post_meta['_cmb2_intro_title'][0];
} else {
  // Fallback to page title
  $intro_title = Titles\title();
}
?>

<header class="page-header">
  <div class="banner-image-wrap">
    <div class="banner-image" <?= \Firebelly\Media\get_header_bg($post) ?>></div>
  </div>
  <h2 class="page-title"><?= nl2br($intro_title); ?></h2>
</header>

<?php if (!empty($post_meta['_cmb2_intro_supporting_statement'])): ?>
<div class="supporting-statement user-content">
  <?= apply_filters('the_content', $post_meta['_cmb2_intro_supporting_statement'][0]) ?>
</div>
<?php endif; ?>

<?php /* Overview Blocks */ ?>
<?php if (!empty($post_meta['_cmb2_overview_blocks'])): ?>
  <ul class="overview-blocks">
    <?php foreach (unserialize($post_meta['_cmb2_overview_blocks'][0]) as $block): ?>
      <li class="grid">
        <div class="grid-item one-half">
          <div class="image-wrap inset-shadow">
            <div class="image" <?= \Firebelly\Media\get_header_bg($block['image_id'], ['size' => 'large']) ?>></div>
          </div>
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

<?php /* Highlight Blocks */ ?>
<?php if (!empty($post_meta['_cmb2_highlight_blocks'])): ?>
  <div class="highlight-blocks">
    <h2 class="h1">How the program works</h2>
    <ul class="grid">
    <?php foreach (unserialize($post_meta['_cmb2_highlight_blocks'][0]) as $block): ?>
        <li class="grid-item one-third">
          <div class="icon-<?= $block['icon'] ?>"><?= $block['icon'] ?></div>
          <div class="user-content">
            <?= apply_filters('the_content', $block['body']) ?>
          </div>
        </li>
    <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php /* Action Blocks */ ?>
<?php if (!empty($post_meta['_cmb2_action_blocks'])): ?>
  <div class="action-blocks">
    <h2 class="h1">What you can do</h2>
    <ul class="cards">
    <?php foreach (unserialize($post_meta['_cmb2_action_blocks'][0]) as $block): ?>
        <li<?= !empty($block['disabled_text']) ? ' class="inactive"' : '' ?>>
          <h4><?= $block['subhead'] ?></h4>
          <h3><?= $block['header'] ?></h3>
          <?php if (!empty($block['disabled_text'])): ?>
            <a href="#" class="button disabled"><?= $block['disabled_text'] ?></a>
          <?php else: ?>
            <a href="<?= $block['url'] ?>" class="button">More</a>
          <?php endif ?>
        </li>
    <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
