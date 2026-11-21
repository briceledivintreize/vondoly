<?php
/** Gabarit de l'administration. Variables : $title, $content */
$u = current_user();
$req = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$unread = (int) val('SELECT COUNT(*) FROM messages WHERE is_read = 0');
$nav = [
    '' => [['Tableau de bord', admin_url(), 'home']],
    'Contenus' => [], 'Pages' => [['Textes du site', admin_url('textes'), 'text']], 'Parcours' => [], 'Reconnaissance' => [], 'L’éditeur' => [],
    'Médias' => [['Photos du site', admin_url('photos'), 'image']], 'Listes' => [],
    'Suivi' => [['Messages', admin_url('messages'), 'mail', $unread], ['Statistiques', admin_url('statistiques'), 'chart']],
    'Réglages' => [],
];
foreach (collections() as $k => $c) {
    if ($k === 'photos') continue;
    if (!empty($c['admin']) && !is_admin()) continue;
    if (!empty($c['module']) && !module_on($c['module'])) continue;
    $nav[$c['group']][] = [$c['label'], admin_url('c/' . $k), $c['icon']];
}
if (!module_on('editeur')) unset($nav['L’éditeur']);
if (is_admin()) { array_unshift($nav['Réglages'], ['Réglages du site', admin_url('reglages'), 'settings']); $nav['Réglages'][] = ['Sauvegardes', admin_url('sauvegardes'), 'save']; }
$ico = fn($n) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ([
    'home' => '<path d="M3 11l9-7 9 7v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/>',
    'book' => '<path d="M4 4h6a3 3 0 0 1 3 3v13a2 2 0 0 0-2-2H4zM20 4h-4a3 3 0 0 0-3 3v13a2 2 0 0 1 2-2h5z"/>',
    'news' => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 8h10M7 12h10M7 16h6"/>',
    'text' => '<path d="M4 6h16M4 12h10M4 18h14"/>',
    'timeline' => '<path d="M4 12h16"/><circle cx="7" cy="12" r="2"/><circle cx="14" cy="12" r="2"/><path d="M7 6v4M14 14v4"/>',
    'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0M16 4a3 3 0 0 1 0 6M21 20a6 6 0 0 0-4-5.6"/>',
    'award' => '<circle cx="12" cy="9" r="5"/><path d="M8.5 13.5L7 21l5-3 5 3-1.5-7.5"/>',
    'press' => '<path d="M4 5h12v14H6a2 2 0 0 1-2-2zM16 9h4v8a2 2 0 0 1-2 2"/><path d="M7 9h6M7 13h6"/>',
    'quote' => '<path d="M7 7h4v4c0 3-2 5-4 6M15 7h4v4c0 3-2 5-4 6"/>',
    'play' => '<circle cx="12" cy="12" r="9"/><path d="M10 8.5l5 3.5-5 3.5z"/>',
    'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
    'layers' => '<path d="M12 3l9 5-9 5-9-5zM3 13l9 5 9-5"/>',
    'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-9 9"/>',
    'link' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
    'tag' => '<path d="M3 12V4h8l10 10-8 8z"/><circle cx="7.5" cy="8.5" r="1.5"/>',
    'building' => '<rect x="4" y="3" width="16" height="18" rx="1"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2"/>',
    'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
    'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
    'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
    'save' => '<path d="M5 3h11l5 5v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/><path d="M7 3v6h8M7 21v-7h10v7"/>',
][$n] ?? '<circle cx="12" cy="12" r="8"/>') . '</svg>';
?><!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · Administration · <?= e(setting('site.name')) ?></title>
<link rel="icon" href="data:image/svg+xml,<?= rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="14" fill="#0e0c0a"/><path d="M18 46c10-20 18-26 30-30-6 8-10 16-12 26" stroke="#b8860b" stroke-width="4" fill="none" stroke-linecap="round"/></svg>') ?>">
<style>:root{--ocre:<?= e(setting('theme.ocre', '#b8860b')) ?>;--red:<?= e(setting('theme.red', '#cc0000')) ?>;--ink:<?= e(setting('theme.ink', '#0e0c0a')) ?>}</style>
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
</head>
<body>
<a class="skip" href="#main">Aller au contenu</a>
<div class="shell">
  <aside class="side" id="side">
    <div class="side__brand">
      <span class="side__logo">Plume</span>
      <span class="side__site"><?= e(setting('site.name')) ?></span>
    </div>
    <nav aria-label="Administration">
      <?php foreach ($nav as $group => $links): if (!$links) continue; ?>
        <?php if ($group): ?><p class="side__group"><?= e($group) ?></p><?php endif; ?>
        <ul>
          <?php foreach ($links as $l): $active = rtrim($req, '/') === rtrim(parse_url($l[1], PHP_URL_PATH), '/') || ($l[1] !== admin_url() && str_starts_with($req, parse_url($l[1], PHP_URL_PATH) . '/')); ?>
          <li><a href="<?= e($l[1]) ?>" <?= $active ? 'aria-current="page"' : '' ?>><?= $ico($l[2]) ?><span><?= e($l[0]) ?></span><?php if (!empty($l[3])): ?><b class="pill"><?= (int) $l[3] ?></b><?php endif; ?></a></li>
          <?php endforeach; ?>
        </ul>
      <?php endforeach; ?>
    </nav>
    <div class="side__foot">
      <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="side__view">Voir le site ↗</a>
      <a href="<?= e(admin_url('compte')) ?>" class="side__user"><?= $ico('user') ?><span><?= e($u['name'] ?: $u['email']) ?><small><?= $u['role'] === 'admin' ? 'Administrateur' : 'Éditeur de contenu' ?></small></span></a>
      <form method="post" action="<?= e(admin_url('logout')) ?>"><?= csrf_field() ?><button type="submit" class="side__logout">Se déconnecter</button></form>
    </div>
  </aside>
  <div class="main-wrap">
    <header class="topbar">
      <button type="button" class="topbar__menu" aria-controls="side" aria-expanded="false" data-side-toggle><span></span><span></span><span></span><b class="visually-hidden">Menu</b></button>
      <h1 class="topbar__title"><?= e($title) ?></h1>
      <a class="topbar__view" href="<?= e(url('/')) ?>" target="_blank" rel="noopener">Voir le site ↗</a>
    </header>
    <main id="main" class="main" tabindex="-1">
      <?php foreach (flashes() as [$type, $msg]): ?><div class="flash flash--<?= e($type) ?>" role="status"><?= e($msg) ?></div><?php endforeach; ?>
      <?= $content ?>
    </main>
  </div>
</div>
<div class="side-backdrop" data-side-toggle></div>
<script src="<?= asset('js/admin.js') ?>" defer></script>
</body>
</html>
