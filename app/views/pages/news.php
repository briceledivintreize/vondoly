<?php
/** Actualités. Variables : $upcoming, $past, $embeds */
partial('page-head', ['title' => t('news.title'), 'intro' => t('news.intro')]);
$card = function (array $n, bool $big = false) {
    $img = media($n['image_id'] ? (int) $n['image_id'] : null);
    ob_start(); ?>
    <article class="news-card <?= $big ? 'news-card--big' : '' ?>" data-reveal>
      <a href="<?= e(url('/actualites/' . $n['slug'])) ?>" data-cursor="<?= te('ui.read_more') ?>">
        <div class="news-card__media">
          <?php if ($img): ?><?= picture($img, ['alt' => $img['alt'] ?: $n['title'], 'sizes' => $big ? '(min-width: 1024px) 60vw, 92vw' : '(min-width: 1024px) 30vw, 92vw']) ?>
          <?php else: ?><div class="news-card__date-art"><span><?= e(fr_date($n['date'], 'd')) ?></span><small><?= e(fr_date($n['date'], 'MMM y')) ?></small></div><?php endif; ?>
        </div>
        <div class="news-card__body">
          <p class="news-card__meta"><span class="tag tag--<?= e($n['type']) ?>"><?= e(news_type_label($n['type'])) ?></span><time datetime="<?= e($n['date']) ?>"><?= e(fr_date($n['date'])) ?></time></p>
          <h3><?= e($n['title']) ?></h3>
          <?php if ($n['place']): ?><p class="news-card__place"><?= icon('pin') ?> <?= e($n['place']) ?></p><?php endif; ?>
          <?php if ($n['excerpt']): ?><p class="news-card__excerpt"><?= e(plain($n['excerpt'], 180)) ?></p><?php endif; ?>
        </div>
      </a>
    </article>
    <?php return ob_get_clean();
};
?>
<section class="news">
  <div class="wrap">
    <?php if (!$upcoming && !$past): ?><p class="muted"><?= te('news.empty') ?></p><?php endif; ?>
    <?php if ($upcoming): ?>
      <h2 class="section-title section-title--sm" data-split-lines><?= te('news.upcoming') ?></h2>
      <div class="news__grid news__grid--upcoming"><?php foreach ($upcoming as $n) echo $card($n, true); ?></div>
    <?php endif; ?>
    <?php if ($past): ?>
      <h2 class="section-title section-title--sm" data-split-lines><?= te('news.past') ?></h2>
      <div class="news__timeline">
        <?php $year = null; foreach ($past as $n): $y = substr((string) $n['date'], 0, 4); if ($y !== $year): $year = $y; ?>
          <h3 class="news__year" data-reveal><?= e($y) ?></h3>
        <?php endif; echo $card($n); endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php if ($embeds): ?>
<section class="embeds section--cream2"><div class="wrap"><h2 class="section-title" data-split-lines><?= te('reco.media_title') ?></h2><div class="embeds__grid"><?php foreach ($embeds as $em) partial('embed', ['em' => $em]); ?></div></div></section>
<?php endif; ?>
