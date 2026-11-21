<?php
/** Reconnaissance. Variables : $personal, $house, $press, $testimonials, $embeds */
partial('page-head', ['title' => t('reco.title'), 'intro' => t('reco.intro')]);
?>
<?php if ($personal): ?>
<section class="awards">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('reco.distinctions_title') ?></h2>
    <ol class="awards__list">
      <?php foreach ($personal as $d): $img = media($d['image_id'] ? (int) $d['image_id'] : null); ?>
      <li class="award" data-reveal>
        <span class="award__year"><?= e($d['year']) ?></span>
        <div class="award__body">
          <?php if ($d['status']): ?><span class="award__status"><?= e($d['status']) ?></span><?php endif; ?>
          <h3><?= e($d['label']) ?></h3>
          <?php if ($d['source']): ?><p class="award__source"><?= $d['source_url'] ? '<a href="' . e($d['source_url']) . '" target="_blank" rel="noopener">' . e($d['source']) . '</a>' : e($d['source']) ?></p><?php endif; ?>
        </div>
        <?php if ($img): ?><button type="button" class="award__img" data-lightbox-src="<?= e(media_src($img, 1440)) ?>" data-lightbox-caption="<?= e($d['caption']) ?>"><?= picture($img, ['alt' => $img['alt'] ?: $d['caption'], 'sizes' => '200px']) ?></button><?php endif; ?>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<?php if ($press): ?>
<section class="press section--cream2">
  <div class="wrap">
    <div class="section-head">
      <h2 class="section-title" data-split-lines><?= te('reco.press_title') ?></h2>
      <a class="btn btn--ink btn--sm" href="<?= e(url('/dossier-de-presse.pdf')) ?>" target="_blank" data-track="presskit" data-ref="reconnaissance"><?= icon('download') ?><span><?= te('ui.press_kit') ?></span></a>
    </div>
    <div class="press__list" data-stagger>
      <?php foreach ($press as $p): ?>
      <article class="press-item">
        <p class="press-item__meta"><strong><?= e($p['media_name']) ?></strong><span><?= e($p['date_label']) ?></span></p>
        <h3><?php if ($p['url']): ?><a href="<?= e($p['url']) ?>" target="_blank" rel="noopener" data-cursor="Lire"><?= e(quoted($p['title'])) ?></a><?php else: ?><?= e(quoted($p['title'])) ?><?php endif; ?></h3>
        <?php if ($p['excerpt']): ?><p class="press-item__excerpt"><?= e($p['excerpt']) ?></p><?php endif; ?>
        <?php if ($p['url']): ?><span class="press-item__arrow" aria-hidden="true"><?= icon('arrow') ?></span><?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($testimonials): ?>
<section class="testimonials section--ink">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('reco.testimonials_title') ?></h2>
    <div class="testimonials__grid" data-stagger>
      <?php foreach ($testimonials as $tq): ?>
      <figure class="testimonial">
        <blockquote>« <?= e($tq['quote']) ?> »</blockquote>
        <figcaption><strong><?= e($tq['name']) ?></strong><?php if ($tq['role']): ?><span><?= e($tq['role']) ?></span><?php endif; ?><?php if ($tq['book']): ?><em><?= e($tq['book']) ?></em><?php endif; ?></figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($embeds): ?>
<section class="embeds">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('reco.media_title') ?></h2>
    <div class="embeds__grid"><?php foreach ($embeds as $em) partial('embed', ['em' => $em]); ?></div>
  </div>
</section>
<?php endif; ?>

<?php if ($house && module_on('editeur')): ?>
<section class="awards awards--house section--cream2">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('reco.house_title') ?></h2>
    <p class="note"><?= te('house.note') ?></p>
    <ol class="awards__list">
      <?php foreach ($house as $d): ?>
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

<section class="presskit">
  <div class="wrap presskit__inner" data-reveal>
    <div>
      <h2><?= te('ui.press_kit') ?></h2>
      <p><?= te('reco.presskit_text') ?></p>
    </div>
    <a class="btn btn--red" href="<?= e(url('/dossier-de-presse.pdf')) ?>" target="_blank" data-track="presskit" data-ref="reconnaissance" data-magnetic><?= icon('download') ?><span><?= te('ui.press_kit') ?></span></a>
  </div>
</section>
