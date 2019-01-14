<?php
/*
  Template name: For IL Residents
*/

// Get all post_meta
$post_meta = get_post_meta($post->ID);
?>

<?php
get_template_part('templates/page', 'header');
?>

<div class="page-content user-content">
  <?= apply_filters('the_content', $post->post_content); ?>
</div>

<?php // Eligibility Blocks ?>
<?php if (!empty($post_meta['_cmb2_eligibility_blocks'])): ?>
  <ul class="eligibility-blocks">
    <?php foreach (unserialize($post_meta['_cmb2_eligibility_blocks'][0]) as $block): ?>
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

<?php // Program Blocks ?>
<?php $programs = \Firebelly\PostTypes\Program\get_programs(['output' => 'array']); ?>
<?php  if (!empty($programs)): ?>
  <div class="cards-image-block programs">
    <h2 class="h1">Programs</h2>
    <ul class="cards">
    <?php foreach ($programs as $program): ?>
        <li>
          <h3><?= $program->post_title ?></h3>
          <a href="<?= get_permalink($post) ?>" class="button">More</a>
        </li>
    <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
