<?php
/** Parcours. Variables : $portrait, $timeline, $assocs, $photos, $books */
$career = setting('profile', 'ecrivain') === 'carriere';
partial('page-head', ['title' => t('parcours.title'), 'intro' => t('parcours.intro'), 'kicker' => setting('site.full_name')]);
?>
<section class="bio">
  <div class="wrap bio__grid">
    <figure class="bio__portrait" data-bio-portrait>
      <span class="bio__frame" aria-hidden="true"></span>
      <?php if ($portrait): ?>
        <?= picture($portrait['media'], ['alt' => $portrait['media']['alt'] ?: 'Portrait de ' . setting('site.name'), 'sizes' => '(min-width: 1024px) 34vw, 90vw']) ?>
        <?php if ($portrait['caption']): ?><figcaption><?= e($portrait['caption']) ?></figcaption><?php endif; ?>
      <?php else: ?>
        <div class="photo-soon photo-soon--portrait"><span><?= te('ui.photo_soon') ?></span></div>
      <?php endif; ?>
    </figure>
    <div class="bio__text prose" data-bio-read>
      <?= md(t('parcours.bio')) ?>
    </div>
  </div>
</section>

<?php if (t('parcours.statement')): ?>
<section class="dive" data-dive>
  <div class="dive__stage">
    <p class="dive__text" data-dive-text><?= te('parcours.statement') ?></p>
  </div>
</section>
<?php endif; ?>

<?php if ($timeline): $n = count($timeline); ?>
<section class="journey section--ink" data-journey aria-labelledby="journey-title">
  <div class="journey__stage">
    <canvas class="journey__dust" aria-hidden="true"></canvas>
    <div class="journey__glow" aria-hidden="true"></div>
    <header class="journey__head wrap">
      <p class="kicker"><?= te('parcours.timeline_kicker') ?></p>
      <h2 class="section-title" id="journey-title" data-split-lines><?= te('parcours.timeline_title') ?></h2>
      <p class="journey__hint" aria-hidden="true"><i></i><?= te('parcours.timeline_hint') ?></p>
    </header>
    <div class="journey__counter" aria-hidden="true">
      <span class="journey__count"><b data-journey-index>01</b> / <?= str_pad((string) $n, 2, '0', STR_PAD_LEFT) ?></span>
      <span class="journey__year" data-journey-year><?= e($timeline[0]['year_label']) ?></span>
    </div>
    <div class="journey__viewport">
      <div class="journey__track" data-journey-track>
        <svg class="journey__path" aria-hidden="true"><path class="journey__path-bg"/><path class="journey__path-fg"/></svg>
        <ol class="journey__stops">
          <?php foreach ($timeline as $i => $t): ?>
          <li class="stop <?= $i % 2 ? 'stop--down' : 'stop--up' ?>" style="--wy:<?= [-.55, .6, -.35, .45, -.6, .3][$i % 6] ?>" data-year="<?= e($t['year_label']) ?>">
            <span class="stop__ghost" aria-hidden="true"><?= e($t['year_label']) ?></span>
            <span class="stop__dot" aria-hidden="true"><i></i></span>
            <div class="stop__card">
              <span class="stop__year"><?= e($t['year_label']) ?></span>
              <?php if ($t['title']): ?><h3><?= e($t['title']) ?></h3><?php endif; ?>
              <div class="stop__text"><?= md($t['text']) ?></div>
            </div>
          </li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
    <div class="journey__progress" aria-hidden="true"><i></i></div>
  </div>
</section>
<?php endif; ?>

<?php if ($assocs): ?>
<section class="assocs">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('parcours.assoc_title') ?></h2>
    <div class="assocs__grid" data-stagger>
      <?php foreach ($assocs as $a): ?>
      <article class="assoc">
        <p class="assoc__since"><?= e($a['since']) ?></p>
        <h3><?= e($a['name']) ?></h3>
        <?php if ($a['role']): ?><p class="assoc__role"><?= e($a['role']) ?></p><?php endif; ?>
        <?php if ($a['text']): ?><div class="prose"><?= md($a['text']) ?></div><?php endif; ?>
        <?php if ($a['url']): ?><a class="link-arrow" href="<?= e($a['url']) ?>" target="_blank" rel="noopener"><?= icon('link') ?> <?= e(parse_url($a['url'], PHP_URL_HOST)) ?></a><?php endif; ?>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($photos): ?>
<section class="gallery section--cream2">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('parcours.photos_title') ?></h2>
    <div class="gallery__grid" data-stagger>
      <?php foreach ($photos as $p): ?>
      <figure class="gallery__item">
        <?php if ($p['media']): ?>
          <button type="button" class="gallery__btn" data-lightbox-src="<?= e(media_src($p['media'], 1440)) ?>" data-lightbox-caption="<?= e(trim($p['caption'] . ($p['year'] ? ' (' . $p['year'] . ')' : ''))) ?>" data-cursor="<?= e('Agrandir') ?>">
            <?= picture($p['media'], ['alt' => $p['media']['alt'] ?: $p['caption'], 'sizes' => '(min-width: 1024px) 30vw, 90vw']) ?>
          </button>
        <?php else: ?>
          <div class="photo-soon photo-soon--land"><span><?= te('ui.photo_soon') ?></span></div>
        <?php endif; ?>
        <?php if ($p['caption'] || $p['year']): ?><figcaption><?= e($p['caption']) ?><?php if ($p['year']): ?> <span><?= e($p['year']) ?></span><?php endif; ?></figcaption><?php endif; ?>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($career && $books): ?>
<section class="latest">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('parcours.books_title') ?></h2>
    <div class="book-grid book-grid--4" data-stagger><?php foreach ($books as $b) partial('book-card', ['b' => $b]); ?></div>
  </div>
</section>
<?php endif; ?>
