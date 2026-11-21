<?php
/** Gabarit commun du site public. Variables : $content, $meta, $page */
$lang = current_lang();
$title = $meta['title'] ?? setting('site.name');
$desc = $meta['description'] ?? t('home.meta');
$canonical = absolute_url(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
$image = $meta['image'] ?? share_image_url();
$theme = [
    '--ink' => setting('theme.ink', '#0e0c0a'), '--ocre' => setting('theme.ocre', '#b8860b'),
    '--ocre-text' => setting('theme.ocre_text', '#8a6508'), '--red' => setting('theme.red', '#cc0000'),
    '--cream' => setting('theme.cream', '#fbfaf6'), '--cream-2' => setting('theme.cream_2', '#f2ece0'),
    '--paper' => setting('theme.white', '#ffffff'),
    '--font-display' => setting('theme.font_display', "'Fraunces', 'Iowan Old Style', Georgia, serif"),
    '--font-body' => setting('theme.font_body', "'Manrope', system-ui, -apple-system, 'Segoe UI', sans-serif"),
];
$css = implode(';', array_map(fn($k, $v) => $k . ':' . preg_replace('~[;{}<>]~', '', $v), array_keys($theme), $theme));
$nav = nav_items();
$current = '/' . explode('/', trim(preg_replace('~^/' . preg_quote($lang) . '(?=/|$)~', '', parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)), '/'))[0];
$name = setting('site.name');
[$first, $last] = array_pad(explode(' ', $name, 2), 2, '');
$analytics = setting('analytics.snippet', '');
?><!doctype html>
<html lang="<?= e($lang) ?>" class="no-js" data-page="<?= e($page) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php if (!empty($meta['noindex'])): ?><meta name="robots" content="noindex"><?php endif; ?>
<meta name="theme-color" content="<?= e(setting('theme.cream', '#fbfaf6')) ?>">
<meta property="og:site_name" content="<?= e($name) ?>">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:type" content="<?= e($meta['type'] ?? 'website') ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:locale" content="<?= $lang === 'fr' ? 'fr_FR' : 'en_GB' ?>">
<?php if ($image): ?><meta property="og:image" content="<?= e($image) ?>"><meta name="twitter:card" content="summary_large_image"><?php endif; ?>
<?php foreach (active_langs() as $l): ?><link rel="alternate" hreflang="<?= e($l) ?>" href="<?= e(absolute_url(url('/', $l))) ?>"><?php endforeach; ?>
<link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="' . setting('theme.ink', '#0e0c0a') . '"/><text x="32" y="44" font-family="Georgia,serif" font-style="italic" font-size="34" text-anchor="middle" fill="' . setting('theme.ocre', '#b8860b') . '">' . mb_substr($first, 0, 1) . mb_substr($last, 0, 1) . '</text></svg>') ?>">
<link rel="preload" href="<?= base_path() ?>/assets/fonts/fraunces-latin-opsz-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= base_path() ?>/assets/fonts/manrope-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
<style>:root{<?= $css ?>}</style>
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">
<script>document.documentElement.classList.replace('no-js','js');window.PLUME={base:<?= json_encode(base_path()) ?>,assets:<?= json_encode(base_path() . '/assets/') ?>,reduce:matchMedia('(prefers-reduced-motion: reduce)').matches};</script>
<?php foreach ($meta['jsonld'] ?? [] as $ld): ?><script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
<?php endforeach; ?>
</head>
<body>
<a class="skip" href="#contenu"><?= te('nav.skip') ?></a>

<?php if ($page === 'home'): ?>
<div class="loader" aria-hidden="true">
  <div class="loader__inner">
    <span class="loader__name"><?= e($name) ?></span>
    <span class="loader__line"></span>
    <span class="loader__years"><span data-count-from="<?= e((string) (author_figures()['debut'] ?? '')) ?>"><?= e((string) (author_figures()['debut'] ?? '')) ?></span> — <?= e((string) (author_figures()['fin'] ?? '')) ?></span>
  </div>
</div>
<?php endif; ?>

<header class="site-header" data-header>
  <a class="brand" href="<?= e(url('/')) ?>" aria-label="<?= e($name) ?> — <?= te('nav.home') ?>">
    <span class="brand__first"><?= e($first) ?></span> <span class="brand__last"><?= e($last) ?></span>
  </a>
  <nav class="nav" aria-label="Navigation principale">
    <ul>
      <?php foreach ($nav as [$m, $p, $k]): if ($m === 'home') continue; ?>
      <li><a href="<?= e(url($p)) ?>" <?= $current === $p ? 'aria-current="page"' : '' ?> data-magnetic><span><?= te($k) ?></span></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>
  <?php if (count(active_langs()) > 1): ?>
  <div class="langs"><?php foreach (active_langs() as $l): ?><a href="<?= e(url('/', $l)) ?>" lang="<?= e($l) ?>" <?= $l === $lang ? 'aria-current="true"' : '' ?>><?= strtoupper(e($l)) ?></a><?php endforeach; ?></div>
  <?php endif; ?>
  <button class="menu-btn" type="button" aria-expanded="false" aria-controls="menu" data-menu-btn>
    <span class="menu-btn__label"><?= te('nav.menu') ?></span><span class="menu-btn__icon" aria-hidden="true"><i></i><i></i></span>
  </button>
</header>

<div class="menu" id="menu" data-menu hidden>
  <svg class="menu__curve" viewBox="0 0 400 800" preserveAspectRatio="none" aria-hidden="true"><path d="M380 -20 C 120 200, 420 420, 60 820"/></svg>
  <nav aria-label="Menu">
    <ol>
      <?php foreach ($nav as $i => [$m, $p, $k]): ?>
      <li><a href="<?= e(url($p)) ?>" <?= $current === $p ? 'aria-current="page"' : '' ?>><small><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></small><span><?= te($k) ?></span></a></li>
      <?php endforeach; ?>
    </ol>
  </nav>
  <div class="menu__foot">
    <?= social_icons(true, true, 'socials--light') ?>
  </div>
</div>

<main id="contenu" tabindex="-1">
<?= $content ?>
</main>

<footer class="site-footer">
  <div class="footer__top">
    <div class="footer__sign">
      <a href="<?= e(url('/')) ?>" class="footer__name" data-split-reveal><?= e($name) ?></a>
      <?php if (t('footer.tagline')): ?><p><?= te('footer.tagline') ?></p><?php endif; ?>
    </div>
    <div class="footer__cols">
      <div>
        <h2><?= te('footer.links_title') ?></h2>
        <ul><?php foreach ($nav as [$m, $p, $k]): ?><li><a href="<?= e(url($p)) ?>"><?= te($k) ?></a></li><?php endforeach; ?></ul>
      </div>
      <div>
        <h2><?= te('footer.follow_title') ?></h2>
        <?= social_icons(true, true, 'socials--light') ?>
      </div>
    </div>
  </div>
  <div class="footer__bottom">
    <p>© <?= date('Y') ?> <?= e($name) ?></p>
    <p><a href="<?= e(url('/mentions-legales')) ?>"><?= te('footer.legal') ?></a> · <a href="<?= e(url('/confidentialite')) ?>"><?= te('footer.privacy') ?></a> · <button type="button" class="linklike" data-cookie-open><?= te('footer.cookies') ?></button></p>
    <?php if (setting('footer.credit')): ?><p class="credit"><a href="<?= e(setting('footer.credit_url', '#')) ?>" target="_blank" rel="noopener"><?= e(setting('footer.credit')) ?></a></p><?php endif; ?>
  </div>
  <svg class="footer__curve" viewBox="0 0 1440 220" preserveAspectRatio="none" aria-hidden="true"><path d="M0 160 C 360 40, 720 260, 1080 120 S 1440 80, 1440 80"/></svg>
</footer>

<div class="cookie" data-cookie hidden role="dialog" aria-live="polite" aria-label="Cookies">
  <p><?= te('cookies.text') ?> <a href="<?= e(url('/confidentialite')) ?>"><?= te('footer.privacy') ?></a></p>
  <div><button type="button" class="btn btn--ghost btn--sm" data-cookie-choice="refuse"><?= te('cookies.refuse') ?></button><button type="button" class="btn btn--ink btn--sm" data-cookie-choice="accept"><?= te('cookies.accept') ?></button></div>
</div>

<div class="lightbox" data-lightbox hidden role="dialog" aria-modal="true" aria-label="Visionneuse">
  <button type="button" class="lightbox__close" data-lightbox-close aria-label="<?= te('nav.close') ?>"><?= icon('close') ?></button>
  <figure><img alt=""><figcaption></figcaption></figure>
  <button type="button" class="lightbox__nav lightbox__prev" data-lightbox-prev aria-label="Précédente"><?= icon('arrow-left') ?></button>
  <button type="button" class="lightbox__nav lightbox__next" data-lightbox-next aria-label="Suivante"><?= icon('arrow') ?></button>
</div>

<div class="cursor" aria-hidden="true"><span class="cursor__dot"></span><span class="cursor__ring"><em></em></span></div>
<div class="grain" aria-hidden="true"></div>

<script src="<?= asset('vendor/gsap/gsap.min.js') ?>" defer></script>
<script src="<?= asset('vendor/gsap/ScrollTrigger.min.js') ?>" defer></script>
<script src="<?= asset('vendor/gsap/SplitText.min.js') ?>" defer></script>
<script src="<?= asset('vendor/gsap/Flip.min.js') ?>" defer></script>
<script src="<?= asset('vendor/gsap/DrawSVGPlugin.min.js') ?>" defer></script>
<script src="<?= asset('vendor/gsap/CustomEase.min.js') ?>" defer></script>
<script src="<?= asset('vendor/lenis/lenis.min.js') ?>" defer></script>
<script src="<?= asset('js/site.js') ?>" defer></script>
<?php if ($analytics): ?><template data-analytics data-consent="<?= setting('analytics.needs_consent', '0') === '1' ? '1' : '0' ?>"><?= $analytics ?></template><?php endif; ?>
</body>
</html>
