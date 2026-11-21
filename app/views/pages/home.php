<?php
/** Accueil. Variables : $portrait, $featured, $latest, $books, $next, $press, $testimonials, $fig */
$name = setting('site.name');
[$first, $last] = array_pad(explode(' ', $name, 2), 2, '');
$roles = [['I', 'home.role1_title', 'home.role1_text'], ['II', 'home.role2_title', 'home.role2_text'], ['III', 'home.role3_title', 'home.role3_text']];
?>
<!-- =============================== HERO =============================== -->
<section class="hero" data-hero>
  <canvas class="hero__ink" data-ink aria-hidden="true"></canvas>
  <svg class="hero__curves" viewBox="0 0 1440 900" preserveAspectRatio="none" aria-hidden="true">
    <path class="c1" data-draw d="M-40 720 C 220 520, 420 860, 720 640 S 1180 380, 1480 520"/>
    <path class="c2" data-draw d="M-40 770 C 260 590, 460 900, 760 690 S 1200 450, 1480 580"/>
  </svg>
  <div class="hero__grid wrap">
    <div class="hero__text">
      <p class="kicker hero__kicker" data-hero-fade><?= te('home.kicker') ?></p>
      <h1 class="hero__name" aria-label="<?= e($name) ?>">
        <span class="hero__first" data-hero-chars><?= e($first) ?></span>
        <span class="hero__last" data-hero-chars><?= e($last) ?></span>
      </h1>
      <p class="hero__tagline" data-hero-fade><?= te('home.tagline') ?></p>
      <div class="hero__cta" data-hero-fade>
        <?php if (module_on('parcours')): ?><a class="btn btn--red" href="<?= e(url('/parcours')) ?>" data-magnetic><span><?= te('home.cta_parcours') ?></span><?= icon('arrow') ?></a><?php endif; ?>
        <?php if (module_on('oeuvres')): ?><a class="btn btn--ghost" href="<?= e(url('/oeuvres')) ?>" data-magnetic><span><?= te('home.cta_books') ?></span></a><?php endif; ?>
      </div>
    </div>
    <figure class="hero__portrait" data-hero-portrait>
      <?php if ($portrait): ?>
        <?= picture($portrait['media'], ['alt' => $portrait['media']['alt'] ?: 'Portrait de ' . $name, 'sizes' => '(min-width: 1024px) 42vw, 90vw', 'eager' => true, 'fetchpriority' => 'high']) ?>
      <?php else: ?>
        <div class="photo-soon photo-soon--portrait"><span><?= te('ui.photo_soon') ?></span></div>
      <?php endif; ?>
    </figure>
  </div>
  <a class="hero__scroll" href="#roles" aria-label="<?= te('home.scroll') ?>"><span><?= te('home.scroll') ?></span><i></i></a>
</section>

<!-- ============================ TROIS RÔLES =========================== -->
<section class="roles section--ink" id="roles">
  <div class="wrap">
    <h2 class="section-title" data-split-lines><?= te('home.roles_title') ?></h2>
    <ol class="roles__list">
      <?php foreach ($roles as [$num, $tk, $xk]): ?>
      <li class="role" data-reveal>
        <span class="role__num" aria-hidden="true"><?= $num ?></span>
        <h3 class="role__title"><?= te($tk) ?></h3>
        <?php if (t($xk)): ?><p><?= te($xk) ?></p><?php endif; ?>
        <svg class="role__line" viewBox="0 0 300 20" preserveAspectRatio="none" aria-hidden="true"><path data-draw d="M2 14 C 80 2, 180 22, 298 6"/></svg>
      </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- ============================== REPÈRES ============================= -->
<section class="figures">
  <div class="wrap figures__grid">
    <div class="figures__block" data-reveal>
      <p class="kicker"><?= te('home.figures_author_title') ?></p>
      <div class="figure">
        <strong data-count="<?= (int) $fig['editions'] ?>"><?= (int) $fig['editions'] ?></strong>
        <span><?= te('home.figures_author_1', ['editions' => $fig['editions'], 'debut' => $fig['debut'], 'fin' => $fig['fin']]) ?></span>
      </div>
      <div class="figure figure--sm">
        <strong data-count="<?= (int) $fig['titres'] ?>"><?= (int) $fig['titres'] ?></strong>
        <span><?= te('home.figures_author_2', ['titres' => $fig['titres']]) ?></span>
      </div>
    </div>
    <?php if (module_on('editeur') && t('home.figures_house_value')): ?>
    <div class="figures__sep" aria-hidden="true"></div>
    <div class="figures__block figures__block--house" data-reveal>
      <p class="kicker"><?= te('home.figures_house_title') ?><?= t('house.name') ? ' — ' . te('house.name') : '' ?></p>
      <div class="figure">
        <small><?= te('home.figures_house_prefix') ?></small>
        <strong data-count="<?= (int) t('home.figures_house_value') ?>"><?= te('home.figures_house_value') ?></strong>
        <span><?= te('home.figures_house_label') ?></span>
      </div>
      <a class="link-arrow" href="<?= e(url('/editeur')) ?>"><?= te('nav.editeur') ?> <?= icon('arrow') ?></a>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($featured): $fb = $featured; ?>
<!-- ============================ DERNIER LIVRE ========================= -->
<section class="featured section--cream2">
  <div class="wrap featured__grid">
    <div class="featured__visual" data-tome-stage>
      <?= book_tome($fb, ['class' => 'tome--xl', 'vt' => true, 'sizes' => '(min-width: 1024px) 28vw, 68vw', 'interactive' => true, 'leaves' => 8]) ?>
    </div>
    <div class="featured__text">
      <p class="kicker" data-fade><?= te('home.featured_kicker') ?> · <?= (int) $fb['year'] ?></p>
      <h2 class="featured__title" data-split-lines><?= e($fb['title']) ?></h2>
      <p class="featured__meta" data-fade><?= e(trim(($fb['genre_label'] ?: $fb['genre_name']) . ' — ' . $fb['publisher_name'], ' —')) ?><?= $fb['collection'] ? ', ' . e($fb['collection']) : '' ?></p>
      <?php if ($fb['summary']): ?><div class="featured__summary prose" data-fade><p><?= e(plain($fb['summary'], 380)) ?></p></div><?php endif; ?>
      <div class="featured__cta" data-fade>
        <?php if (wa_number()): ?><a class="btn btn--red" href="<?= e(wa_book_link($fb)) ?>" target="_blank" rel="noopener" data-track="whatsapp" data-ref="<?= e($fb['slug']) ?>" data-magnetic><?= icon('whatsapp') ?><span><?= te('ui.want_book') ?></span></a><?php endif; ?>
        <a class="btn btn--ghost" href="<?= e(url('/oeuvres/' . $fb['slug'])) ?>" data-magnetic><span><?= te('ui.see_book') ?></span></a>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($latest): ?>
<!-- ========================= DERNIÈRES PARUTIONS ====================== -->
<section class="latest">
  <div class="wrap">
    <div class="section-head">
      <h2 class="section-title" data-split-lines><?= te('home.latest_title') ?></h2>
      <a class="link-arrow" href="<?= e(url('/oeuvres')) ?>"><?= te('ui.see_all_books') ?> <?= icon('arrow') ?></a>
    </div>
    <div class="book-grid book-grid--4" data-stagger>
      <?php foreach ($latest as $b) partial('book-card', ['b' => $b]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (module_on('bibliotheque3d') && module_on('oeuvres') && count($books) > 3):
  $lib = array_map(function ($b) {
      $m = media($b['cover_id'] ? (int) $b['cover_id'] : null);
      return ['t' => $b['title'], 'y' => (int) $b['year'], 'p' => $b['publisher_name'], 'u' => url('/oeuvres/' . $b['slug']),
              'c' => $m ? media_src($m, 480) : null, 'w' => $m['width'] ?? null, 'h' => $m['height'] ?? null, 'col' => $m['color'] ?? null];
  }, $books); ?>
<!-- ======================= BIBLIOTHÈQUE (Three.js) ===================== -->
<section class="library section--ink" data-library>
  <div class="library__sticky">
    <canvas class="library__canvas" aria-hidden="true"></canvas>
    <div class="library__overlay wrap">
      <h2 class="section-title" data-split-lines><?= te('home.library_title') ?></h2>
      <p data-fade><?= te('home.library_text') ?></p>
      <p class="library__current" aria-live="polite"><span data-lib-title></span><small data-lib-meta></small></p>
      <a class="btn btn--cream" href="<?= e(url('/oeuvres')) ?>" data-magnetic><span><?= te('ui.see_all_books') ?></span><?= icon('arrow') ?></a>
    </div>
  </div>
  <noscript><style>.library{display:none}</style></noscript>
  <script type="application/json" data-library-data><?= json_encode($lib, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
</section>
<?php endif; ?>

<?php if ($next): ?>
<!-- ======================== PROCHAINE APPARITION ====================== -->
<section class="next">
  <div class="wrap">
    <a class="next__card" href="<?= e(url('/actualites/' . $next['slug'])) ?>" data-reveal>
      <div class="next__date">
        <span class="next__day"><?= e(fr_date($next['date'], 'd')) ?></span>
        <span class="next__month"><?= e(fr_date($next['date'], 'MMMM y')) ?></span>
      </div>
      <div class="next__body">
        <p class="kicker"><?= te('home.next_title') ?> · <?= e(news_type_label($next['type'])) ?></p>
        <h2><?= e($next['title']) ?></h2>
        <?php if ($next['place']): ?><p class="next__place"><?= icon('pin') ?> <?= e($next['place']) ?></p><?php endif; ?>
      </div>
      <span class="next__arrow"><?= icon('arrow') ?></span>
    </a>
  </div>
</section>
<?php endif; ?>

<?php if ($press || $testimonials): ?>
<!-- =============================== PRESSE ============================= -->
<section class="press-band">
  <h2 class="visually-hidden"><?= te('home.press_title') ?></h2>
  <?php if ($press): ?>
  <div class="marquee" data-marquee aria-hidden="true">
    <div class="marquee__track">
      <?php for ($i = 0; $i < 2; $i++): foreach ($press as $p): ?>
      <span class="marquee__item"><em><?= e($p['media_name']) ?></em> <?= e(quoted($p['title'])) ?></span><span class="marquee__dot">✦</span>
      <?php endforeach; endfor; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php if ($testimonials): $tq = $testimonials[0]; ?>
  <figure class="quote wrap" data-reveal>
    <svg class="quote__mark" viewBox="0 0 80 60" aria-hidden="true"><path d="M8 58V34C8 16 18 5 34 2l3 7C27 13 22 20 22 30h14v28H8zm42 0V34C50 16 60 5 76 2l3 7C69 13 64 20 64 30h14v28H50z"/></svg>
    <blockquote data-split-lines><?= e($tq['quote']) ?></blockquote>
    <figcaption><strong><?= e($tq['name']) ?></strong><?= $tq['role'] ? ', ' . e($tq['role']) : '' ?></figcaption>
  </figure>
  <?php endif; ?>
  <?php if (module_on('reconnaissance')): ?><p class="press-band__more"><a class="link-arrow" href="<?= e(url('/reconnaissance')) ?>"><?= te('nav.reconnaissance') ?> <?= icon('arrow') ?></a></p><?php endif; ?>
</section>
<?php endif; ?>

<?php if (module_on('contact')): ?>
<!-- =============================== CONTACT ============================ -->
<section class="cta section--red">
  <svg class="cta__curve" viewBox="0 0 1440 400" preserveAspectRatio="none" aria-hidden="true"><path data-draw d="M-20 330 C 300 80, 640 420, 980 180 S 1400 60, 1460 120"/></svg>
  <div class="wrap cta__inner">
    <h2 class="cta__title" data-split-lines><?= te('home.contact_title') ?></h2>
    <p data-fade><?= te('home.contact_text') ?></p>
    <div class="cta__buttons" data-fade>
      <a class="btn btn--cream" href="<?= e(url('/contact')) ?>" data-magnetic><span><?= te('home.contact_cta') ?></span><?= icon('arrow') ?></a>
      <?php if (wa_number()): ?><a class="btn btn--outline-cream" href="<?= e(wa_link(t('contact.whatsapp_msg'))) ?>" target="_blank" rel="noopener" data-magnetic><?= icon('whatsapp') ?><span>WhatsApp</span></a><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>
