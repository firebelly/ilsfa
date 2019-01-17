<?php if (!empty($post_meta['_cmb2_post_resources'])): ?>
  <ul class="icon-list">
    <?php foreach (unserialize($post_meta['_cmb2_post_resources'][0]) as $resource): ?>
    	<?php $icon = preg_match('/mp4|mov|mkv$/', $resource['file']) ? 'video' : 'document' ?>
      <li><a target="_blank" href="<?= $resource['file'] ?>"><?= $resource['title'] ?> <svg class="icon icon-<?= $icon ?>" aria-hidden="true"><use xlink:href="#icon-<?= $icon ?>"/></svg></a></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
