<?php
/** Vidéo / audio externe, chargé seulement après clic ou consentement. Variable : $em */
$src = embed_src($em['url']);
?>
<figure class="embed embed--<?= e($src['provider'] ?? 'link') ?>">
  <?php if ($src): ?>
  <div class="embed__frame" data-embed="<?= e($src['src']) ?>" data-embed-title="<?= e($em['title']) ?>">
    <button type="button" class="embed__load" <?= $src['thumb'] ? 'style="--thumb:url(\'' . e($src['thumb']) . '\')"' : '' ?>>
      <span class="embed__play"><?= icon('play') ?></span>
      <span><?= te('ui.load_embed') ?></span>
    </button>
  </div>
  <?php endif; ?>
  <figcaption>
    <strong><?= e($em['title']) ?></strong><?php if ($em['date_label']): ?> <span><?= e($em['date_label']) ?></span><?php endif; ?>
    <?php if ($em['description']): ?><p><?= e($em['description']) ?></p><?php endif; ?>
    <a href="<?= e($em['url']) ?>" target="_blank" rel="noopener" class="link-arrow"><?= icon('link') ?> <?= e(parse_url($em['url'], PHP_URL_HOST)) ?></a>
  </figcaption>
</figure>
