<?php
/** L'éditeur. Variables : $figures, $collections, $distinctions, $photos */
partial('page-head', ['title' => t('house.name') ?: t('house.title'), 'intro' => t('house.intro'), 'kicker' => t('house.title')]);
?>
<section class="house">
  <div class="wrap house__grid">
    <div class="house__body prose prose--drop" data-fade-children><?= md(t('house.body')) ?></div>
    <aside class="house__aside">
      <?php if ($figures): ?>
      <h2 class="kicker"><?= te('house.figures_title') ?></h2>
      <ul class="house__figures">
        <?php foreach ($figures as $f): ?>
        <li data-reveal><strong data-count="<?= e(preg_replace('~\D~', '', $f['value'])) ?>" data-count-text="<?= e($f['value']) ?>"><?= e($f['value']) ?></strong><span><?= e($f['label']) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <p class="note"><?= te('house.note') ?></p>
      <?php if (t('house.url')): ?><a class="btn btn--red" href="<?= e(t('house.url')) ?>" target="_blank" rel="noopener" data-magnetic><span><?= te('house.cta') ?></span><?= icon('arrow') ?></a><?php endif; ?>
    </aside>
  </div>
</section>

<?php if ($photos): ?>
<section class="gallery">
  <div class="wrap gallery__grid gallery__grid--2" data-stagger>
    <?php foreach ($photos as $p): ?>
    <figure class="gallery__item">
      <?php if ($p['media']): ?>
        <button type="button" class="gallery__btn" data-lightbox-src="<?= e(media_src($p['media'], 1440)) ?>" data-lightbox-caption="<?= e($p['caption']) ?>"><?= picture($p['media'], ['alt' => $p['media']['alt'] ?: $p['caption'], 'sizes' => '(min-width: 1024px) 45vw, 90vw']) ?></button>
      <?php else: ?><div class="photo-soon photo-soon--land"><span><?= te('ui.photo_soon') ?></span></div><?php endif; ?>
      <?php if ($p['caption']): ?><figcaption><?= e($p['caption']) ?><?php if ($p['year']): ?> <span><?= e($p['year']) ?></span><?php endif; ?></figcaption><?php endif; ?>
    </figure>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($collections): ?>
<section class="collections section--ink">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('house.collections_title') ?></h2>
    <ul class="collections__list">
      <?php foreach ($collections as $i => $c): ?>
      <li data-reveal><span class="collections__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= e($c['name']) ?></h3><?php if ($c['text']): ?><p><?= e($c['text']) ?></p><?php endif; ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<?php if ($distinctions): ?>
<section class="awards awards--house">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('house.distinctions_title') ?></h2>
    <ol class="awards__list">
      <?php foreach ($distinctions as $d): ?>
      <li class="award" data-reveal>
        <span class="award__year"><?= e($d['year']) ?></span>
        <div class="award__body">
          <?php if ($d['status']): ?><span class="award__status"><?= e($d['status']) ?></span><?php endif; ?>
          <h3><?= e($d['label']) ?></h3>
          <?php if ($d['source']): ?><p class="award__source"><?= $d['source_url'] ? '<a href="' . e($d['source_url']) . '" target="_blank" rel="noopener">' . e($d['source']) . '</a>' : e($d['source']) ?></p><?php endif; ?>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>
