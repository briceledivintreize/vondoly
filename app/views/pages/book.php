<?php
/** Fiche livre. Variables : $b, $other, $related, $back, $cover */
$genre = $b['genre_label'] ?: $b['genre_name'];
$pageUrl = absolute_url(url('/oeuvres/' . $b['slug']));
$place = $b['place'] ?: $b['publisher_city'];
$details = array_filter([
    t('books.label_publisher') => trim(($b['publisher_name'] ?? '') . ($place ? ', ' . $place : ''), ', '),
    t('books.label_year') => (string) $b['year'],
    t('books.label_edition') => book_edition_label($b),
    t('books.label_genre') => $genre,
    t('books.label_collection') => $b['collection'],
    t('books.label_preface') => $b['preface'],
    t('books.label_postface') => $b['postface'],
    t('books.label_pages') => $b['pages'],
    t('books.label_isbn') => $b['isbn'],
]);
?>
<article class="book">
  <div class="wrap book__grid">
    <div class="book__visual">
      <a class="book__back-link" href="<?= e(url('/oeuvres')) ?>"><?= icon('arrow-left') ?> <?= te('nav.oeuvres') ?></a>
      <div data-tome-stage>
        <?= book_tome($b, ['class' => 'tome--detail', 'vt' => true, 'eager' => true, 'sizes' => '(min-width: 1024px) 30vw, 80vw', 'interactive' => true, 'leaves' => 9]) ?>
      </div>
      <div class="book__visual-tools">
        <?php if ($back): ?><button type="button" class="btn btn--ghost btn--sm" data-flip><?= icon('flip') ?><span><?= te('ui.flip') ?></span></button><?php endif; ?>
        <?php if ($cover): ?><button type="button" class="btn btn--ghost btn--sm" data-lightbox-group='<?= attr_json(array_values(array_filter([[media_orig($cover), $b['title']], $back ? [media_orig($back), $b['title'] . ' — verso'] : null]))) ?>'><?= icon('zoom') ?><span>Agrandir</span></button><?php endif; ?>
      </div>
    </div>

    <div class="book__info">
      <p class="kicker" data-fade><?= e($genre) ?><?= $genre ? ' · ' : '' ?><?= (int) $b['year'] ?></p>
      <h1 class="book__title" data-split-lines><?= e($b['title']) ?></h1>
      <?php if ($b['subtitle']): ?><p class="book__subtitle" data-fade><?= e($b['subtitle']) ?></p><?php endif; ?>
      <div class="book__badges" data-fade>
        <?php if ((int) $b['edition_num'] > 1): ?><span class="badge badge--ed badge--lg"><?= e(book_edition_label($b)) ?></span><?php endif; ?>
        <?php if ($b['is_collective'] || $b['special_note']): ?><span class="badge badge--col badge--lg"><?= e($b['special_note'] ?: t('ui.collective')) ?></span><?php endif; ?>
        <?php if ($other): ?><a class="badge badge--link badge--lg" href="<?= e(url('/oeuvres/' . $other['slug'])) ?>"><?= te('ui.other_edition') ?> : <?= e(book_edition_label($other)) ?>, <?= (int) $other['year'] ?> <?= icon('arrow') ?></a><?php endif; ?>
      </div>

      <div class="book__order" data-fade>
        <?php if (wa_number()): ?>
        <a class="btn btn--red btn--lg" href="<?= e(wa_book_link($b)) ?>" target="_blank" rel="noopener" data-track="whatsapp" data-ref="<?= e($b['slug']) ?>" data-magnetic><?= icon('whatsapp') ?><span><?= te('ui.want_book') ?></span></a>
        <p class="book__order-help"><?= te('books.order_help') ?></p>
        <?php endif; ?>
        <?php if ($b['publisher_link']): ?><a class="link-arrow" href="<?= e($b['publisher_link']) ?>" target="_blank" rel="noopener" data-track="publisher" data-ref="<?= e($b['slug']) ?>"><?= te('ui.publisher_link') ?> <?= icon('arrow') ?></a><?php endif; ?>
      </div>

      <div class="book__summary prose prose--drop" data-fade-children>
        <?php if ($b['summary']): ?><?= md($b['summary']) ?><?php else: ?><p class="muted"><?= te('books.no_summary') ?></p><?php endif; ?>
      </div>

      <dl class="book__details" data-fade>
        <?php foreach ($details as $k => $v): ?><div><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd></div><?php endforeach; ?>
      </dl>

      <div class="share" data-fade>
        <span><?= te('ui.share') ?></span>
        <a href="https://wa.me/?text=<?= rawurlencode($b['title'] . ' — ' . setting('site.name') . "\n" . $pageUrl) ?>" target="_blank" rel="noopener" aria-label="WhatsApp" data-track="share" data-ref="wa:<?= e($b['slug']) ?>"><?= icon('whatsapp') ?></a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($pageUrl) ?>" target="_blank" rel="noopener" aria-label="Facebook" data-track="share" data-ref="fb:<?= e($b['slug']) ?>"><?= icon('facebook') ?></a>
        <button type="button" data-copy="<?= e($pageUrl) ?>" aria-label="Copier le lien"><?= icon('link') ?></button>
      </div>
    </div>
  </div>
</article>

<?php if ($related): ?>
<section class="latest section--cream2">
  <div class="wrap">
    <div class="section-head">
      <h2 class="section-title" data-split-lines><?= te('books.related') ?></h2>
      <a class="link-arrow" href="<?= e(url('/oeuvres')) ?>"><?= te('ui.see_all_books') ?> <?= icon('arrow') ?></a>
    </div>
    <div class="book-grid book-grid--4" data-stagger><?php foreach (array_slice($related, 0, 4) as $r) partial('book-card', ['b' => $r, 'vt' => false]); ?></div>
  </div>
</section>
<?php endif; ?>
