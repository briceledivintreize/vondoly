<?php
/** Actualité. Variables : $n, $img */
$pageUrl = absolute_url(url('/actualites/' . $n['slug']));
?>
<article class="article">
  <header class="article__head wrap wrap--narrow">
    <a class="book__back-link" href="<?= e(url('/actualites')) ?>"><?= icon('arrow-left') ?> <?= te('nav.actualites') ?></a>
    <p class="news-card__meta" data-fade><span class="tag tag--<?= e($n['type']) ?>"><?= e(news_type_label($n['type'])) ?></span><time datetime="<?= e($n['date']) ?>"><?= e(fr_date($n['date'])) ?><?= $n['end_date'] && $n['end_date'] !== $n['date'] ? ' → ' . e(fr_date($n['end_date'])) : '' ?></time></p>
    <h1 class="article__title" data-split-lines><?= e($n['title']) ?></h1>
    <?php if ($n['place']): ?><p class="article__place" data-fade><?= icon('pin') ?> <?= e($n['place']) ?></p><?php endif; ?>
  </header>
  <?php if ($img): ?><figure class="article__media wrap" data-parallax-img><?= picture($img, ['alt' => $img['alt'] ?: $n['title'], 'sizes' => '(min-width: 1200px) 1100px, 94vw', 'eager' => true]) ?></figure><?php endif; ?>
  <div class="article__body wrap wrap--narrow prose prose--drop" data-fade-children>
    <?php if ($n['excerpt']): ?><p class="lead"><?= e($n['excerpt']) ?></p><?php endif; ?>
    <?= md($n['body']) ?>
    <?php if ($n['link']): ?><p><a class="link-arrow" href="<?= e($n['link']) ?>" target="_blank" rel="noopener"><?= icon('link') ?> <?= e(parse_url($n['link'], PHP_URL_HOST)) ?></a></p><?php endif; ?>
  </div>
  <div class="wrap wrap--narrow">
    <div class="share">
      <span><?= te('ui.share') ?></span>
      <a href="https://wa.me/?text=<?= rawurlencode($n['title'] . "\n" . $pageUrl) ?>" target="_blank" rel="noopener" aria-label="WhatsApp" data-track="share" data-ref="wa:<?= e($n['slug']) ?>"><?= icon('whatsapp') ?></a>
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($pageUrl) ?>" target="_blank" rel="noopener" aria-label="Facebook" data-track="share" data-ref="fb:<?= e($n['slug']) ?>"><?= icon('facebook') ?></a>
      <button type="button" data-copy="<?= e($pageUrl) ?>" aria-label="Copier le lien"><?= icon('link') ?></button>
    </div>
  </div>
</article>
