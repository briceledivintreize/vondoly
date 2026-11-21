<?php
/** Accès aux contenus publics : livres, médias, liens, modules, WhatsApp… */
declare(strict_types=1);

/* ------------------------------- Modules -------------------------------- */
function modules(): array
{
    $defaults = ['parcours' => 1, 'reconnaissance' => 1, 'oeuvres' => 1, 'editeur' => 1, 'actualites' => 1, 'contact' => 1, 'bibliotheque3d' => 1];
    return array_merge($defaults, setting_json('modules', []));
}
function module_on(string $m): bool { return !empty(modules()[$m]); }

function nav_items(): array
{
    $items = [['home', '/', 'nav.home']];
    foreach ([['parcours', '/parcours'], ['reconnaissance', '/reconnaissance'], ['oeuvres', '/oeuvres'],
              ['editeur', '/editeur'], ['actualites', '/actualites'], ['contact', '/contact']] as [$m, $p]) {
        if (module_on($m)) $items[] = [$m, $p, 'nav.' . $m];
    }
    return $items;
}

/* ---------------------------- Traductions ------------------------------- */
/** Applique les traductions de contenu de la langue courante à une ligne. */
function tr(string $entity, ?array $row): ?array
{
    if (!$row || current_lang() === default_lang()) return $row;
    foreach (rows('SELECT field, value FROM translations WHERE entity = ? AND entity_id = ? AND lang = ?', [$entity, $row['id'], current_lang()]) as $t) {
        if (trim((string) $t['value']) !== '') $row[$t['field']] = $t['value'];
    }
    return $row;
}
function tr_all(string $entity, array $rows): array { return array_map(fn($r) => tr($entity, $r), $rows); }

/* -------------------------------- Médias -------------------------------- */
function media(?int $id): ?array
{
    static $cache = [];
    if (!$id) return null;
    if (!array_key_exists($id, $cache)) {
        $m = row('SELECT * FROM media WHERE id = ?', [$id]);
        if ($m) { $m['widths'] = json_decode($m['widths'], true); $m['formats'] = json_decode($m['formats'], true); }
        $cache[$id] = $m;
    }
    return $cache[$id];
}

function media_src(array $m, ?int $w = null, string $fmt = 'jpg'): string
{
    $ws = $m['widths'];
    $pick = end($ws);
    if ($w) foreach ($ws as $x) { if ($x >= $w) { $pick = $x; break; } }
    return upload_url($m['base'] . '-' . $pick . '.' . $fmt);
}

function media_orig(array $m): string { return upload_url($m['base'] . '-orig.jpg'); }

/**
 * Balise <picture> responsive (AVIF → WebP → JPEG), lazy-loading par défaut.
 * $o : alt, sizes, class, eager (bool), fetchpriority, attrs (string)
 */
function picture(?array $m, array $o = []): string
{
    if (!$m) return '';
    $sizes = $o['sizes'] ?? '100vw';
    $alt = $o['alt'] ?? ($m['alt'] ?? '');
    $srcset = fn($fmt) => implode(', ', array_map(fn($w) => upload_url($m['base'] . '-' . $w . '.' . $fmt) . ' ' . $w . 'w', $m['widths']));
    $h = '<picture' . (isset($o['pclass']) ? ' class="' . e($o['pclass']) . '"' : '') . '>';
    foreach (['avif' => 'image/avif', 'webp' => 'image/webp'] as $fmt => $type) {
        if (in_array($fmt, $m['formats'], true)) $h .= '<source type="' . $type . '" srcset="' . e($srcset($fmt)) . '" sizes="' . e($sizes) . '">';
    }
    $mid = $m['widths'][min(1, count($m['widths']) - 1)];
    $h .= '<img src="' . e(upload_url($m['base'] . '-' . $mid . '.jpg')) . '" srcset="' . e($srcset('jpg')) . '" sizes="' . e($sizes) . '"'
        . ' width="' . (int) $m['width'] . '" height="' . (int) $m['height'] . '" alt="' . e($alt) . '"'
        . (!empty($o['eager']) ? ' loading="eager"' : ' loading="lazy"') . ' decoding="async"'
        . (!empty($o['fetchpriority']) ? ' fetchpriority="' . e($o['fetchpriority']) . '"' : '')
        . (isset($o['class']) ? ' class="' . e($o['class']) . '"' : '')
        . ($o['attrs'] ?? '') . '>';
    return $h . '</picture>';
}

/** Photo d'un emplacement (portrait principal, etc.) ou null. */
function slot_photo(string $slot): ?array
{
    $p = row('SELECT * FROM photos WHERE slot = ? AND visible = 1 AND media_id IS NOT NULL ORDER BY sort, id LIMIT 1', [$slot]);
    if (!$p) return null;
    $p['media'] = media((int) $p['media_id']);
    return $p['media'] ? $p : null;
}

function slot_photos(string $slot): array
{
    $out = [];
    foreach (rows('SELECT * FROM photos WHERE slot = ? AND visible = 1 ORDER BY sort, id', [$slot]) as $p) {
        $p = tr('photos', $p);
        $p['media'] = media($p['media_id'] ? (int) $p['media_id'] : null);
        $out[] = $p;
    }
    return $out;
}

/** Définition des emplacements photo (section 3.5 du cahier des charges). */
function photo_slots(): array
{
    return [
        'home_portrait' => ['label' => 'Portrait principal (Accueil)', 'ratio' => '4:5', 'max' => 1, 'focus' => 'top'],
        'parcours_portrait' => ['label' => 'Portrait secondaire (Parcours)', 'ratio' => '4:5', 'max' => 1, 'focus' => 'top'],
        'parcours' => ['label' => 'Photos de parcours (salons, dédicaces…)', 'ratio' => '3:2', 'max' => 0],
        'house' => ['label' => 'Photos de la maison d’édition', 'ratio' => '3:2', 'max' => 0],
        'share' => ['label' => 'Image de partage (WhatsApp, Facebook)', 'ratio' => '1200x630', 'max' => 1],
        'presskit' => ['label' => 'Photo haute définition (dossier de presse)', 'ratio' => null, 'max' => 1, 'focus' => 'top'],
    ];
}

/* -------------------------------- Livres -------------------------------- */
function books_query(string $where = 'b.visible = 1', array $params = [], string $order = 'b.year DESC, b.sort, b.id DESC'): array
{
    $sql = "SELECT b.*, g.name AS genre_name, g.slug AS genre_slug, p.name AS publisher_name, p.slug AS publisher_slug, p.city AS publisher_city
            FROM books b LEFT JOIN genres g ON g.id = b.genre_id LEFT JOIN publishers p ON p.id = b.publisher_id
            WHERE $where ORDER BY $order";
    return tr_all('books', rows($sql, $params));
}

function book_by_slug(string $slug): ?array
{
    $b = books_query('b.slug = ? AND b.visible = 1', [$slug]);
    return $b[0] ?? null;
}

function book_edition_label(array $b): string
{
    $n = (int) ($b['edition_num'] ?? 1);
    return $n > 1 ? t('ui.second_edition', ['n' => $n]) : t('ui.first_edition');
}

/** Lien WhatsApp « Je veux ce livre » : numéro, modèle et provenance viennent des réglages. */
function wa_number(): string { return preg_replace('~\D~', '', (string) setting('whatsapp.number', '')); }

function wa_book_link(array $b): string
{
    $n = (int) ($b['edition_num'] ?? 1);
    $ed = $n > 1 ? t('ui.second_edition', ['n' => $n]) . ', ' . $b['year'] : (string) $b['year'];
    $tpl = setting('whatsapp.template', 'Bonjour, je souhaite commander le livre « {titre} » ({edition}). Pouvez-vous me dire comment procéder ?');
    $msg = strtr($tpl, ['{titre}' => $b['title'], '{edition}' => $ed, '{annee}' => (string) $b['year']]);
    $prov = setting('whatsapp.provenance', 'Depuis le site {domaine}');
    if ($prov) $msg .= "\n\n" . strtr($prov, ['{domaine}' => setting('site.domain', '')]);
    return 'https://wa.me/' . wa_number() . '?text=' . rawurlencode($msg);
}

function wa_link(string $msg = ''): string
{
    return 'https://wa.me/' . wa_number() . ($msg ? '?text=' . rawurlencode($msg) : '');
}

/** Chiffres de l'auteur calculés depuis les fiches (toutes éditions, y compris masquées). */
function author_figures(): array
{
    $r = row('SELECT COUNT(*) n, MIN(year) a, MAX(year) b, SUM(CASE WHEN edition_num > 1 THEN 1 ELSE 0 END) re FROM books');
    return ['editions' => (int) $r['n'], 'debut' => $r['a'], 'fin' => $r['b'], 'titres' => (int) $r['n'] - (int) $r['re']];
}

/**
 * Couverture d'un livre : image (sans étirement, sur fond flouté si trop petite ou carrée)
 * ou carte typographique provisoire (titre, auteur, éditeur).
 */
function book_cover(array $b, array $o = []): string
{
    $m = media($b['cover_id'] ? (int) $b['cover_id'] : null);
    $vt = !empty($o['vt']) ? ' style="view-transition-name: cover-' . (int) $b['id'] . '"' : '';
    $cls = 'cover' . (isset($o['class']) ? ' ' . $o['class'] : '');
    if (!$m) {
        $hue = (crc32($b['title']) % 3);
        return '<div class="' . $cls . ' cover--placeholder cover--p' . $hue . '"' . $vt . ' role="img" aria-label="' . e(t('ui.cover_soon') . ' : ' . $b['title']) . '">'
            . '<svg class="cover__curve" viewBox="0 0 200 300" preserveAspectRatio="none" aria-hidden="true"><path d="M-10 230 C 60 180, 120 300, 210 210" /><path d="M-10 255 C 70 205, 130 320, 210 238" /></svg>'
            . '<span class="cover__author">' . e(setting('site.name')) . '</span>'
            . '<span class="cover__title">' . e($b['title']) . '</span>'
            . '<span class="cover__pub">' . e($b['publisher_name'] ?? '') . '</span>'
            . '<span class="cover__soon">' . te('ui.cover_soon') . '</span></div>';
    }
    $ratio = $m['width'] / max(1, $m['height']);
    $small = $m['width'] < 500 || $ratio > 0.8;   // trop petite, ou carrée : affichage « contenu » sans étirement
    $alt = $b['cover_alt'] ?: ($m['alt'] ?: 'Couverture du livre « ' . $b['title'] . ' »');
    $img = picture($m, ['alt' => $alt, 'sizes' => $o['sizes'] ?? '(min-width: 1024px) 22vw, 46vw', 'eager' => !empty($o['eager'])]);
    $style = $small ? ' style="--iw:' . (int) $m['width'] . 'px; --bg:url(\'' . e(media_src($m, 200)) . '\')"' : '';
    return '<div class="' . $cls . ($small ? ' cover--small' : '') . '"' . $vt . $style . '>' . $img . '</div>';
}

/**
 * Livre modélisé en volume (CSS 3D) : couverture, dos, tranche de pages, quatrième,
 * et feuillets « sous la couverture » qui se feuillettent par le coin au survol.
 * L'épaisseur suit le nombre de pages quand il est connu.
 * $o : class, sizes, eager, vt (view-transition), interactive (survol), leaves (nb de feuillets)
 */
function book_tome(array $b, array $o = []): string
{
    $pages = is_numeric($b['pages'] ?? null) ? (int) $b['pages'] : 130;
    $t = round(max(5.5, min(14, $pages / 15)), 2);            // épaisseur en % de la largeur
    $back = media(!empty($b['back_id']) ? (int) $b['back_id'] : null);
    $n = $o['leaves'] ?? 6;
    $leaves = '';
    for ($i = 0; $i < $n; $i++) {
        $folio = max(3, $pages - 1 - $i * 2);
        $leaves .= '<div class="tome__leaf" style="--i:' . $i . '"><span class="tome__lines"></span><span class="tome__folio">' . $folio . '</span><i class="tome__flap"></i></div>';
    }
    $leaves .= '<div class="tome__leaf tome__leaf--base" style="--i:' . $n . '"><span class="tome__lines"></span></div>';
    $backFace = $back
        ? picture($back, ['alt' => '', 'sizes' => $o['sizes'] ?? '30vw'])
        : '<span class="tome__backart"><span>' . e(setting('site.name')) . '</span><em>' . e($b['title']) . '</em></span>';
    $cm = media(!empty($b['cover_id']) ? (int) $b['cover_id'] : null);
    $spine = $cm['color'] ?? '#1a1612';
    return '<div class="tome ' . e($o['class'] ?? '') . '" data-tome' . (!empty($o['interactive']) ? ' data-tome-hover' : '') . ($back ? ' data-tome-back' : '')
        . ' style="--t:' . $t . ';--spine:' . e($spine) . '">'
        . '<div class="tome__body">'
        . '<div class="tome__back" aria-hidden="true">' . $backFace . '</div>'
        . '<div class="tome__spine" aria-hidden="true"><span>' . e($b['title']) . '</span></div>'
        . '<div class="tome__edge tome__edge--fore" aria-hidden="true"></div>'
        . '<div class="tome__edge tome__edge--top" aria-hidden="true"></div>'
        . '<div class="tome__edge tome__edge--bottom" aria-hidden="true"></div>'
        . '<div class="tome__leaves" aria-hidden="true">' . $leaves . '</div>'
        . '<div class="tome__front"><div class="tome__face">' . book_cover($b, ['vt' => $o['vt'] ?? false, 'sizes' => $o['sizes'] ?? null, 'eager' => !empty($o['eager'])]) . '</div><i class="tome__flap tome__flap--cover" aria-hidden="true"></i><i class="tome__sheen" aria-hidden="true"></i></div>'
        . '</div><div class="tome__shadow" aria-hidden="true"></div></div>';
}

/* ------------------------- Réseaux, apparitions ------------------------- */
function social_links(bool $footerOnly = false): array
{
    $sql = 'SELECT * FROM social_links WHERE visible = 1 AND url IS NOT NULL AND url <> \'\'' . ($footerOnly ? ' AND in_footer = 1' : '') . ' ORDER BY sort, id';
    return rows($sql);
}

function social_href(array $s): string
{
    $u = trim($s['url']);
    if ($s['network'] === 'email' && !str_starts_with($u, 'mailto:')) return 'mailto:' . $u;
    return $u;
}

function social_label(array $s): string
{
    return $s['label'] ?: ['x' => 'X', 'facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram',
        'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'email' => 'E-mail', 'link' => 'Lien'][$s['network']] ?? $s['network'];
}

/** Rangée d'icônes de réseaux sociaux (icônes seules, nom accessible en aria-label et info-bulle). */
function social_icons(bool $footerOnly = false, bool $whatsapp = true, string $class = '', array $skip = []): string
{
    $items = [];
    if ($whatsapp && wa_number()) $items[] = [wa_link(t('contact.whatsapp_msg')), 'whatsapp', 'WhatsApp'];
    foreach (social_links($footerOnly) as $s) if (!in_array($s['network'], $skip, true)) $items[] = [social_href($s), $s['network'], social_label($s)];
    if (!$items) return '';
    $h = '<ul class="socials ' . e($class) . '">';
    foreach ($items as [$href, $net, $label]) {
        $h .= '<li><a href="' . e($href) . '" target="_blank" rel="noopener" aria-label="' . e($label) . '" title="' . e($label) . '">' . icon($net) . '</a></li>';
    }
    return $h . '</ul>';
}

function next_appearance(): ?array
{
    $n = row("SELECT * FROM news WHERE visible = 1 AND is_appearance = 1 AND COALESCE(end_date, date) >= date('now') ORDER BY date ASC LIMIT 1");
    return tr('news', $n);
}

function news_type_label(string $type): string { return t('news.type_' . $type); }

/* --------------------------- Intégrations média ------------------------- */
/** Convertit un lien YouTube / SoundCloud en adresse d'intégration. */
function embed_src(string $url): ?array
{
    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
        return ['provider' => 'youtube', 'src' => 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0', 'thumb' => 'https://i.ytimg.com/vi/' . $m[1] . '/hqdefault.jpg'];
    }
    if (str_contains($url, 'soundcloud.com')) {
        return ['provider' => 'soundcloud', 'src' => 'https://w.soundcloud.com/player/?url=' . rawurlencode($url) . '&color=%23b8860b&visual=false', 'thumb' => null];
    }
    return null;
}
