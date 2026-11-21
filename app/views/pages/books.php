<?php
/** Catalogue des œuvres. Variables : $books, $genres, $pubs */
partial('page-head', ['title' => t('books.title'), 'intro' => t('books.intro')]);
?>
<section class="catalog" data-catalog>
  <div class="wrap">
    <div class="catalog__tools">
      <label class="search">
        <?= icon('search') ?>
        <span class="visually-hidden"><?= te('books.search') ?></span>
        <input type="search" placeholder="<?= te('books.search') ?>" data-search autocomplete="off">
      </label>
      <div class="filters" role="group" aria-label="<?= te('books.filter_genre') ?>" data-swipe data-lenis-prevent>
        <button type="button" class="chip" aria-pressed="true" data-filter-genre=""><?= te('books.all') ?></button>
        <?php foreach ($genres as $g): ?><button type="button" class="chip" aria-pressed="false" data-filter-genre="<?= e($g['slug']) ?>"><?= e($g['name']) ?></button><?php endforeach; ?>
      </div>
      <label class="select">
        <span><?= te('books.filter_publisher') ?></span>
        <select data-filter-publisher>
          <option value=""><?= te('books.all') ?></option>
          <?php foreach ($pubs as $p): ?><option value="<?= e($p['slug']) ?>"><?= e($p['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <p class="catalog__count" aria-live="polite" data-count-label data-tpl="<?= te('books.count') ?>"><?= te('books.count', ['n' => count($books)]) ?></p>
    </div>
    <div class="book-grid book-grid--catalog" data-grid>
      <?php foreach ($books as $b) partial('book-card', ['b' => $b]); ?>
    </div>
    <p class="catalog__empty" data-empty hidden><?= te('books.empty') ?></p>
  </div>
</section>
