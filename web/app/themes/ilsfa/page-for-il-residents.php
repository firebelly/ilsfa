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
  <div class="cards-image-block programs" <?= !empty($post_meta['_cmb2_programs_background']) ? ' style="background-image: url('.$post_meta['_cmb2_programs_background'][0].')"' : '' ?>>
    <h2 class="h1">Programs</h2>
    <ul class="cards compact-grid">
    <?php foreach ($programs as $program): ?>
      <?php $program_post_meta = get_post_meta($program->ID); ?>
      <li>
        <h3><?= $program->post_title ?></h3>
        <ul class="icon-list requirements">
          <?php foreach([
            'income' => 'income_requirements',
            'household-size' => 'household_size',
            'install-cost' => 'installation_cost',
            'savings' => 'savings',
          ] as $requirement_icon => $requirement): ?>

            <?php if (!empty($program_post_meta['_cmb2_'.$requirement])): ?>
              <li><svg class="icon icon-<?= $requirement_icon ?>" aria-hidden="true"><use xlink:href="#icon-<?= $requirement_icon ?>"/></svg><?= $program_post_meta['_cmb2_'.$requirement][0] ?></li>
            <?php endif; ?>

          <?php endforeach; ?>
        </ul>
        <a href="<?= get_permalink($program) ?>" class="button">More</a>
      </li>
    <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
