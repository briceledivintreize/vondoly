<?php
/** Carte livre (grille Œuvres, dernières parutions). Variables : $b, $vt (view-transition), $sizes */
$genre = $b['genre_label'] ?: ($b['genre_name'] ?? '');
$search = mb_strtolower(implode(' ', [$b['title'], $b['subtitle'], $genre, $b['genre_name'], $b['year'], $b['publisher_name'], $b['collection']]));
?>
<article class="book-card" data-book data-genre="<?= e($b['genre_slug'] ?? '') ?>" data-publisher="<?= e($b['publisher_slug'] ?? '') ?>" data-year="<?= (int) $b['year'] ?>" data-search="<?= e($search) ?>">
  <a class="book-card__link" href="<?= e(url('/oeuvres/' . $b['slug'])) ?>" data-cursor="<?= te('ui.see_book') ?>">
    <div class="book-card__cover">
      <?= book_tome($b, ['vt' => $vt ?? true, 'sizes' => $sizes ?? '(min-width: 1024px) 20vw, 40vw', 'interactive' => true, 'leaves' => 5]) ?>
      <?php if ((int) $b['edition_num'] > 1): ?><span class="badge badge--ed"><?= e(book_edition_label($b)) ?></span><?php endif; ?>
      <?php if ($b['is_collective']): ?><span class="badge badge--col"><?= te('ui.collective') ?></span><?php endif; ?>
    </div>
    <h3 class="book-card__title"><?= e($b['title']) ?></h3>
    <p class="book-card__meta"><span><?= (int) $b['year'] ?></span><?php if ($b['publisher_name']): ?> · <span><?= e($b['publisher_name']) ?></span><?php endif; ?></p>
  </a>
  <?php if (wa_number()): ?>
  <a class="btn btn--red btn--sm book-card__buy" href="<?= e(wa_book_link($b)) ?>" target="_blank" rel="noopener" data-track="whatsapp" data-ref="<?= e($b['slug']) ?>"><?= icon('whatsapp') ?><span><?= te('ui.want_book') ?></span></a>
  <?php endif; ?>
</article>
