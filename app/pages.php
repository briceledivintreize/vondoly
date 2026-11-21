<?php
/** Pages publiques : routage, préparation des données et métadonnées SEO. */
declare(strict_types=1);

function route_public(string $path): void
{
    // Préfixe de langue (/en/…) si plusieurs langues sont actives
    $seg = explode('/', trim($path, '/'));
    if ($seg[0] !== '' && $seg[0] !== default_lang() && in_array($seg[0], active_langs(), true)) {
        $GLOBALS['LANG'] = array_shift($seg);
        $path = '/' . implode('/', $seg);
    }
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    security_headers();

    switch (true) {
        case $path === '/robots.txt': robots(); return;
        case $path === '/sitemap.xml': sitemap(); return;
        case $path === '/api/track' && $method === 'POST': api_track(); return;
        case $path === '/dossier-de-presse.pdf': press_kit_pdf(); return;
    }

    $routes = [
        '~^/$~' => 'page_home',
        '~^/parcours$~' => module_on('parcours') ? 'page_parcours' : null,
        '~^/reconnaissance$~' => module_on('reconnaissance') ? 'page_reconnaissance' : null,
        '~^/oeuvres$~' => module_on('oeuvres') ? 'page_books' : null,
        '~^/oeuvres/([a-z0-9-]+)$~' => module_on('oeuvres') ? 'page_book' : null,
        '~^/editeur$~' => module_on('editeur') ? 'page_house' : null,
        '~^/actualites$~' => module_on('actualites') ? 'page_news' : null,
        '~^/actualites/([a-z0-9-]+)$~' => module_on('actualites') ? 'page_news_item' : null,
        '~^/contact$~' => module_on('contact') ? 'page_contact' : null,
        '~^/mentions-legales$~' => 'page_legal',
        '~^/confidentialite$~' => 'page_privacy',
    ];
    foreach ($routes as $re => $fn) {
        if ($fn && preg_match($re, $path, $m)) {
            array_shift($m);
            $res = $fn(...$m);
            if ($res === null) break;
            stats_record_view($path);
            echo render($res);
            return;
        }
    }
    http_response_code(404);
    echo render(['view' => '404', 'meta' => ['title' => t('404.title'), 'noindex' => true], 'page' => '404']);
}

/** Assemble la page dans le gabarit commun. */
function render(array $r): string
{
    $meta = $r['meta'] ?? [];
    $content = view('pages/' . $r['view'], $r['vars'] ?? []);
    return view('layout', ['content' => $content, 'meta' => $meta, 'page' => $r['page'] ?? $r['view']]);
}

function share_image_url(?array $m = null): ?string
{
    $m ??= slot_photo('share')['media'] ?? slot_photo('home_portrait')['media'] ?? null;
    return $m ? absolute_url(media_src($m, 1200)) : null;
}

/** Données structurées « Person » (sameAs = réseaux sociaux saisis dans l'administration). */
function jsonld_person(): array
{
    $p = slot_photo('home_portrait');
    return array_filter([
        '@context' => 'https://schema.org', '@type' => 'Person',
        'name' => setting('site.name'), 'alternateName' => setting('site.full_name'),
        'url' => absolute_url(url('/')), 'jobTitle' => t('home.kicker'),
        'image' => $p ? absolute_url(media_src($p['media'], 960)) : null,
        'sameAs' => array_values(array_map('social_href', array_filter(social_links(), fn($s) => $s['network'] !== 'email'))),
        'nationality' => setting('site.nationality') ?: null,
    ]);
}

/* ================================ Accueil ================================ */
function page_home(): array
{
    $books = books_query();
    $featured = null;
    foreach ($books as $b) if ($b['featured']) { $featured = $b; break; }
    $featured ??= $books[0] ?? null;
    $latest = array_slice(array_values(array_filter($books, fn($b) => !$featured || $b['id'] !== $featured['id'])), 0, 4);
    $press = tr_all('press', rows('SELECT * FROM press WHERE visible = 1 ORDER BY sort, date_sort DESC'));
    $testimonials = tr_all('testimonials', rows('SELECT * FROM testimonials WHERE visible = 1 ORDER BY sort, id'));
    return [
        'view' => 'home', 'page' => 'home',
        'vars' => [
            'portrait' => slot_photo('home_portrait'), 'featured' => $featured, 'latest' => $latest, 'books' => $books,
            'next' => module_on('actualites') ? next_appearance() : null, 'press' => $press, 'testimonials' => $testimonials,
            'fig' => author_figures(),
        ],
        'meta' => [
            'title' => setting('site.name') . ' — ' . t('home.kicker'),
            'description' => t('home.meta'), 'jsonld' => [jsonld_person()], 'image' => share_image_url(),
        ],
    ];
}

/* ================================ Parcours =============================== */
function page_parcours(): array
{
    return [
        'view' => 'parcours',
        'vars' => [
            'portrait' => slot_photo('parcours_portrait'),
            'timeline' => tr_all('timeline', rows('SELECT * FROM timeline WHERE visible = 1 ORDER BY sort, id')),
            'assocs' => tr_all('associations', rows('SELECT * FROM associations WHERE visible = 1 ORDER BY sort, id')),
            'photos' => slot_photos('parcours'),
            'books' => setting('profile', 'ecrivain') === 'carriere' ? books_query() : [],
        ],
        'meta' => ['title' => t('parcours.title') . ' — ' . setting('site.name'), 'description' => t('parcours.meta'), 'jsonld' => [jsonld_person()]],
    ];
}

/* ============================= Reconnaissance ============================ */
function page_reconnaissance(): array
{
    $d = tr_all('distinctions', rows('SELECT * FROM distinctions WHERE visible = 1 ORDER BY sort, year DESC'));
    return [
        'view' => 'reconnaissance',
        'vars' => [
            'personal' => array_values(array_filter($d, fn($x) => $x['scope'] === 'personal')),
            'house' => array_values(array_filter($d, fn($x) => $x['scope'] === 'house')),
            'press' => tr_all('press', rows('SELECT * FROM press WHERE visible = 1 ORDER BY sort, date_sort DESC')),
            'testimonials' => tr_all('testimonials', rows('SELECT * FROM testimonials WHERE visible = 1 ORDER BY sort, id')),
            'embeds' => tr_all('embeds', rows('SELECT * FROM embeds WHERE visible = 1 ORDER BY sort, id')),
        ],
        'meta' => ['title' => t('reco.title') . ' — ' . setting('site.name'), 'description' => t('reco.meta')],
    ];
}

/* ================================= Œuvres ================================ */
function page_books(): array
{
    $books = books_query();
    $genres = rows('SELECT g.* FROM genres g WHERE EXISTS (SELECT 1 FROM books b WHERE b.genre_id = g.id AND b.visible = 1) ORDER BY sort, name');
    $pubs = rows('SELECT p.* FROM publishers p WHERE EXISTS (SELECT 1 FROM books b WHERE b.publisher_id = p.id AND b.visible = 1) ORDER BY sort, name');
    $ld = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'itemListElement' => array_map(fn($b, $i) => [
        '@type' => 'ListItem', 'position' => $i + 1, 'url' => absolute_url(url('/oeuvres/' . $b['slug'])), 'name' => $b['title']], $books, array_keys($books))];
    return [
        'view' => 'books', 'vars' => compact('books', 'genres', 'pubs'),
        'meta' => ['title' => t('books.title') . ' — ' . setting('site.name'), 'description' => t('books.meta'), 'jsonld' => [$ld]],
    ];
}

function page_book(string $slug): ?array
{
    $b = book_by_slug($slug);
    if (!$b) return null;
    $other = $b['other_edition_id'] ? books_query('b.id = ? AND b.visible = 1', [$b['other_edition_id']])[0] ?? null : null;
    $all = books_query();
    $idx = array_search($b['id'], array_column($all, 'id'));
    $related = [];
    for ($i = 1; $i <= 4 && count($all) > 1; $i++) $related[] = $all[($idx + $i) % count($all)];
    $related = array_values(array_filter($related, fn($r) => $r['id'] !== $b['id']));
    $cover = media($b['cover_id'] ? (int) $b['cover_id'] : null);
    $ld = array_filter([
        '@context' => 'https://schema.org', '@type' => 'Book', 'name' => $b['title'],
        'alternativeHeadline' => $b['subtitle'] ?: null,
        'author' => ['@type' => 'Person', 'name' => setting('site.name'), 'url' => absolute_url(url('/'))],
        'datePublished' => (string) $b['year'], 'bookEdition' => book_edition_label($b),
        'publisher' => $b['publisher_name'] ? ['@type' => 'Organization', 'name' => $b['publisher_name']] : null,
        'genre' => $b['genre_label'] ?: $b['genre_name'], 'isbn' => $b['isbn'] ? preg_replace('~[^\dX]~', '', explode('/', $b['isbn'])[0]) : null,
        'numberOfPages' => is_numeric($b['pages']) ? (int) $b['pages'] : null,
        'description' => $b['summary'] ? plain($b['summary'], 300) : null,
        'image' => $cover ? absolute_url(media_src($cover, 960)) : null,
        'url' => absolute_url(url('/oeuvres/' . $b['slug'])), 'inLanguage' => 'fr',
    ]);
    return [
        'view' => 'book', 'vars' => ['b' => $b, 'other' => $other, 'related' => $related, 'back' => media($b['back_id'] ? (int) $b['back_id'] : null), 'cover' => $cover],
        'meta' => [
            'title' => $b['title'] . ' (' . $b['year'] . ') — ' . setting('site.name'),
            'description' => $b['summary'] ? plain($b['summary'], 160) : trim(($b['genre_label'] ?: $b['genre_name']) . ', ' . $b['year'] . ', ' . $b['publisher_name'] . '.', ', .'),
            'image' => $cover ? absolute_url(media_src($cover, 960)) : share_image_url(), 'type' => 'book', 'jsonld' => [$ld],
        ],
    ];
}

/* ================================ L'éditeur ============================== */
function page_house(): array
{
    $ld = array_filter(['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => t('house.name'), 'url' => t('house.url') ?: null,
        'founder' => ['@type' => 'Person', 'name' => setting('site.name')]]);
    return [
        'view' => 'house',
        'vars' => [
            'figures' => rows('SELECT * FROM house_figures WHERE visible = 1 ORDER BY sort, id'),
            'collections' => tr_all('house_collections', rows('SELECT * FROM house_collections WHERE visible = 1 ORDER BY sort, id')),
            'distinctions' => tr_all('distinctions', rows("SELECT * FROM distinctions WHERE visible = 1 AND scope = 'house' ORDER BY sort, year DESC")),
            'photos' => slot_photos('house'),
        ],
        'meta' => ['title' => (t('house.name') ?: t('house.title')) . ' — ' . setting('site.name'), 'description' => t('house.meta'), 'jsonld' => [$ld]],
    ];
}

/* =============================== Actualités ============================== */
function page_news(): array
{
    $all = tr_all('news', rows('SELECT * FROM news WHERE visible = 1 ORDER BY date DESC, id DESC'));
    $today = date('Y-m-d');
    $upcoming = array_reverse(array_values(array_filter($all, fn($n) => ($n['end_date'] ?: $n['date']) >= $today)));
    $past = array_values(array_filter($all, fn($n) => ($n['end_date'] ?: $n['date']) < $today));
    $embeds = module_on('reconnaissance') ? [] : tr_all('embeds', rows('SELECT * FROM embeds WHERE visible = 1 ORDER BY sort, id'));
    return [
        'view' => 'news', 'vars' => compact('upcoming', 'past', 'embeds'),
        'meta' => ['title' => t('news.title') . ' — ' . setting('site.name'), 'description' => t('news.meta')],
    ];
}

function page_news_item(string $slug): ?array
{
    $n = tr('news', row('SELECT * FROM news WHERE slug = ? AND visible = 1', [$slug]));
    if (!$n) return null;
    $img = media($n['image_id'] ? (int) $n['image_id'] : null);
    $ld = ['@context' => 'https://schema.org', '@type' => in_array($n['type'], ['dedicace', 'salon', 'apparition']) ? 'Event' : 'NewsArticle',
        'name' => $n['title'], 'headline' => $n['title'], 'startDate' => $n['date'], 'datePublished' => $n['date']];
    if ($ld['@type'] === 'Event') { unset($ld['headline'], $ld['datePublished']); if ($n['place']) $ld['location'] = ['@type' => 'Place', 'name' => $n['place']]; }
    else unset($ld['name'], $ld['startDate']);
    return [
        'view' => 'news-item', 'vars' => ['n' => $n, 'img' => $img],
        'meta' => ['title' => $n['title'] . ' — ' . setting('site.name'), 'description' => plain($n['excerpt'] ?: $n['body'], 160),
            'image' => $img ? absolute_url(media_src($img, 1200)) : share_image_url(), 'type' => 'article', 'jsonld' => [$ld]],
    ];
}

/* ================================ Contact ================================ */
function page_contact(): array
{
    session_boot();
    $errors = []; $old = [];
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        csrf_check();
        $old = array_map(fn($v) => is_string($v) ? trim($v) : '', $_POST);
        // Anti-spam : champ piège invisible + délai minimal de saisie + limitation par IP
        $spam = ($old['website'] ?? '') !== '' || (time() - (int) ($_SESSION['contact_ts'] ?? 0)) < 3;
        if (mb_strlen($old['name'] ?? '') < 2) $errors['name'] = true;
        if (!filter_var($old['email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors['email'] = true;
        if (mb_strlen($old['message'] ?? '') < 10) $errors['message'] = true;
        if (empty($old['consent'])) $errors['consent'] = true;
        if (!$errors && !$spam && throttle('contact:' . hash('sha256', client_ip()), 5, 3600)) {
            $id = insert('messages', [
                'name' => mb_substr($old['name'], 0, 120), 'email' => mb_substr($old['email'], 0, 160), 'phone' => mb_substr($old['phone'] ?? '', 0, 40),
                'subject' => mb_substr($old['subject'] ?? '', 0, 200), 'body' => mb_substr($old['message'], 0, 5000),
            ]);
            $to = setting('mail.to') ?: setting('site.email');
            if ($to) {
                $subject = '=?UTF-8?B?' . base64_encode('[' . setting('site.domain') . '] ' . ($old['subject'] ?: 'Nouveau message')) . '?=';
                $body = "Nom : {$old['name']}\nE-mail : {$old['email']}\nTéléphone : " . ($old['phone'] ?? '') . "\n\n{$old['message']}";
                $from = 'no-reply@' . preg_replace('~^www\.~', '', (string) setting('site.domain', 'localhost'));
                $ok = @mail($to, $subject, $body, "From: $from\r\nReply-To: {$old['email']}\r\nContent-Type: text/plain; charset=UTF-8");
                if ($ok) q('UPDATE messages SET mailed = 1 WHERE id = ?', [$id]);
            }
            flash('ok', t('contact.sent'));
            redirect(url('/contact') . '#formulaire');
        } elseif (!$errors) {
            flash('ok', t('contact.sent')); // réponse identique pour ne pas renseigner les robots
            redirect(url('/contact') . '#formulaire');
        }
    }
    $_SESSION['contact_ts'] = time();
    return [
        'view' => 'contact', 'vars' => ['errors' => $errors, 'old' => $old, 'socials' => social_links()],
        'meta' => ['title' => t('contact.title') . ' — ' . setting('site.name'), 'description' => t('contact.meta')],
    ];
}

function page_legal(): array
{
    return ['view' => 'text', 'vars' => ['title' => t('legal.title'), 'body' => t('legal.body')], 'meta' => ['title' => t('legal.title') . ' — ' . setting('site.name'), 'noindex' => false]];
}
function page_privacy(): array
{
    return ['view' => 'text', 'vars' => ['title' => t('privacy.title'), 'body' => t('privacy.body')], 'meta' => ['title' => t('privacy.title') . ' — ' . setting('site.name')]];
}

/* ============================ Points techniques ========================== */
function robots(): void
{
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nDisallow: /admin\nDisallow: /api/\n\nSitemap: " . absolute_url(base_path() . '/sitemap.xml') . "\n";
}

function sitemap(): void
{
    header('Content-Type: application/xml; charset=utf-8');
    $urls = ['/'];
    foreach (['parcours', 'reconnaissance', 'oeuvres', 'editeur', 'actualites', 'contact'] as $m) if (module_on($m)) $urls[] = '/' . $m;
    if (module_on('oeuvres')) foreach (rows('SELECT slug FROM books WHERE visible = 1') as $b) $urls[] = '/oeuvres/' . $b['slug'];
    if (module_on('actualites')) foreach (rows('SELECT slug FROM news WHERE visible = 1') as $n) $urls[] = '/actualites/' . $n['slug'];
    $urls[] = '/mentions-legales'; $urls[] = '/confidentialite';
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach (active_langs() as $lang) foreach ($urls as $u) echo '<url><loc>' . e(absolute_url(url($u, $lang))) . '</loc></url>';
    echo '</urlset>';
}

function api_track(): void
{
    $in = json_decode(file_get_contents('php://input') ?: '[]', true) ?: $_POST;
    $event = (string) ($in['event'] ?? '');
    if (in_array($event, ['whatsapp', 'share', 'presskit', 'publisher'], true) && throttle('track:' . hash('sha256', client_ip()), 120, 3600)) {
        stats_record_event($event, (string) ($in['ref'] ?? ''));
    }
    http_response_code(204);
}

/** Dossier de presse PDF, généré à partir des contenus saisis (mis en cache par version). */
function press_kit_pdf(): void
{
    $dir = DATA . '/cache';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $file = $dir . '/dossier-de-presse-' . current_lang() . '-' . setting('content_version', '0') . '.pdf';
    if (!is_file($file)) {
        foreach (glob($dir . '/dossier-de-presse-' . current_lang() . '-*.pdf') ?: [] as $old) @unlink($old);
        $photo = slot_photo('presskit') ?? slot_photo('home_portrait');
        $html = view('presskit', [
            'photo' => $photo ? PUB . '/' . $photo['media']['base'] . '-orig.jpg' : null,
            'books' => books_query(), 'fig' => author_figures(),
            'distinctions' => tr_all('distinctions', rows("SELECT * FROM distinctions WHERE visible = 1 AND scope = 'personal' ORDER BY sort, year DESC")),
            'press' => tr_all('press', rows('SELECT * FROM press WHERE visible = 1 ORDER BY sort, date_sort DESC')),
            'socials' => social_links(),
        ]);
        $opt = new Dompdf\Options();
        $opt->set('isRemoteEnabled', false);
        $opt->set('chroot', [PUB, APP]);
        $opt->set('defaultFont', 'DejaVu Serif');
        $pdf = new Dompdf\Dompdf($opt);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->setPaper('A4');
        $pdf->render();
        file_put_contents($file, $pdf->output());
    }
    if (function_exists('stats_record_event')) stats_record_event('presskit', 'pdf');
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="dossier-de-presse-' . slugify(setting('site.name', 'auteur')) . '.pdf"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
}
