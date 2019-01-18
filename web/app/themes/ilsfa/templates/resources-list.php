<?php if (!empty($post_meta['_cmb2_post_resources'])): ?>
  <ul class="icon-list">
    <?php foreach (unserialize($post_meta['_cmb2_post_resources'][0]) as $resource): ?>
      <?php
      $title = !empty($resource['title']) ? $resource['title'] : basename($resource['file']);
    	$icon = preg_match('/mp4|mov|mkv$/', $resource['file']) ? 'video' : 'document'
      ?>
      <li><a <?= $icon=='document' ? 'download="'.basename($resource['file']).'" ' : '' ?>target="_blank" href="<?= $resource['file'] ?>"><?= $title ?> <svg class="icon icon-<?= $icon ?>" aria-hidden="true"><use xlink:href="#icon-<?= $icon ?>"/></svg></a></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
