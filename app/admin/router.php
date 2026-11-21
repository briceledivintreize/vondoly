<?php
/** Administration : routage et contrôleurs. Toutes les pages exigent une connexion. */
declare(strict_types=1);

require APP . '/admin/collections.php';
require APP . '/admin/crud.php';
require APP . '/admin/backup.php';

session_boot();
security_headers(true);
header('Cache-Control: no-store');

$sub = trim(substr($path, strlen('/admin')), '/');
$seg = $sub === '' ? [] : explode('/', $sub);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

/* ------------------------------- Connexion ------------------------------ */
if (($seg[0] ?? '') === 'login') {
    $error = null;
    if ($method === 'POST') {
        csrf_check();
        if (attempt_login((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) redirect(admin_url());
        $error = 'Identifiants incorrects, ou trop de tentatives : patientez quelques minutes.';
    }
    echo view('admin/login', ['error' => $error]);
    exit;
}
if (($seg[0] ?? '') === 'logout' && $method === 'POST') { csrf_check(); logout(); redirect(admin_url('login')); }

$user = require_login();
if ($method === 'POST') csrf_check();
backup_auto();

$admin = function (string $title, string $content, array $extra = []) {
    echo view('admin/layout', ['title' => $title, 'content' => $content] + $extra);
    exit;
};

switch ($seg[0] ?? '') {
    case '':
        $admin('Tableau de bord', view('admin/dashboard', dashboard_data()));

    case 'c':
        crud_dispatch($seg[1] ?? '', $seg[2] ?? null, $seg[3] ?? null, $admin);

    case 'textes':
        texts_page($admin);

    case 'photos':
        $admin('Photos du site', view('admin/photos', ['slots' => photo_slots()]));

    case 'messages':
        if ($method === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if (($_POST['do'] ?? '') === 'delete') { q('DELETE FROM messages WHERE id = ?', [$id]); flash('ok', 'Message supprimé.'); }
            else q('UPDATE messages SET is_read = 1 - is_read WHERE id = ?', [$id]);
            redirect(admin_url('messages'));
        }
        $msgs = rows('SELECT * FROM messages ORDER BY created_at DESC LIMIT 300');
        if (isset($_GET['lu'])) q('UPDATE messages SET is_read = 1 WHERE id = ?', [(int) $_GET['lu']]);
        $admin('Messages', view('admin/messages', ['msgs' => $msgs]));

    case 'statistiques':
        $admin('Statistiques', view('admin/stats', stats_data((int) ($_GET['jours'] ?? 30))));

    case 'reglages':
        require_admin();
        settings_page($admin);

    case 'sauvegardes':
        require_admin();
        if (($seg[1] ?? '') === 'telecharger' && isset($seg[2])) backup_download($seg[2]);
        if ($method === 'POST') {
            if (($_POST['do'] ?? '') === 'create') { $f = backup_create('manuel'); flash('ok', 'Sauvegarde créée : ' . basename($f)); }
            if (($_POST['do'] ?? '') === 'delete') { backup_delete((string) $_POST['file']); flash('ok', 'Sauvegarde supprimée.'); }
            redirect(admin_url('sauvegardes'));
        }
        $admin('Sauvegardes', view('admin/backups', ['files' => backup_list()]));

    case 'compte':
        if ($method === 'POST') {
            $u = row('SELECT * FROM users WHERE id = ?', [$user['id']]);
            $new = (string) ($_POST['new'] ?? '');
            if (!password_verify((string) ($_POST['current'] ?? ''), $u['password_hash'])) flash('error', 'Mot de passe actuel incorrect.');
            elseif (mb_strlen($new) < 12) flash('error', 'Le nouveau mot de passe doit faire au moins 12 caractères.');
            elseif ($new !== ($_POST['confirm'] ?? '')) flash('error', 'Les deux saisies ne correspondent pas.');
            else { q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $user['id']]); audit('mot de passe modifié'); flash('ok', 'Mot de passe modifié.'); }
            redirect(admin_url('compte'));
        }
        $admin('Mon compte', view('admin/account', ['user' => $user]));

    case 'cache':
        content_touched(); flash('ok', 'Caches vidés.'); redirect(admin_url());

    default:
        http_response_code(404);
        $admin('Page introuvable', '<div class="card"><p>Cette page de l’administration n’existe pas.</p></div>');
}

/* ========================================================================= */

function dashboard_data(): array
{
    $since = date('Y-m-d', strtotime('-29 days'));
    $checks = [];
    foreach (['books' => 'title', 'timeline' => 'year_label', 'distinctions' => 'label', 'press' => 'title', 'news' => 'title',
              'associations' => 'name', 'testimonials' => 'name', 'embeds' => 'title', 'photos' => 'caption'] as $t => $col) {
        foreach (rows("SELECT id, $col AS label, note, visible" . ($t === 'photos' ? ', slot' : '') . " FROM $t WHERE note IS NOT NULL AND trim(note) <> '' ORDER BY id") as $r) {
            $r['coll'] = $t; $r['url'] = admin_url("c/$t/{$r['id']}") . ($t === 'photos' ? '?slot=' . $r['slot'] : '');
            $checks[] = $r;
        }
    }
    return [
        'views' => (int) val('SELECT COALESCE(SUM(views),0) FROM stats_views WHERE day >= ?', [$since]),
        'visitors' => (int) val('SELECT COUNT(*) FROM stats_visitors WHERE day >= ?', [$since]),
        'wa' => (int) val("SELECT COALESCE(SUM(count),0) FROM stats_events WHERE event = 'whatsapp' AND day >= ?", [$since]),
        'spark' => rows('SELECT day, SUM(views) v FROM stats_views WHERE day >= ? GROUP BY day ORDER BY day', [$since]),
        'checks' => $checks,
        'noCover' => rows('SELECT id, title, year FROM books WHERE cover_id IS NULL ORDER BY year'),
        'lowCover' => rows('SELECT b.id, b.title, b.year, m.orig_width w FROM books b JOIN media m ON m.id = b.cover_id WHERE m.orig_width < 600 ORDER BY b.year'),
        'noSummary' => (int) val("SELECT COUNT(*) FROM books WHERE summary IS NULL OR trim(summary) = ''"),
        'books' => (int) val('SELECT COUNT(*) FROM books'),
        'unread' => rows('SELECT * FROM messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5'),
        'next' => next_appearance(),
        'emptySlots' => array_keys(array_filter(photo_slots(), fn($s, $k) => !slot_photo($k), ARRAY_FILTER_USE_BOTH)),
    ];
}

function stats_data(int $days): array
{
    $days = in_array($days, [7, 30, 90, 365], true) ? $days : 30;
    $since = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
    $daily = [];
    for ($i = $days - 1; $i >= 0; $i--) $daily[date('Y-m-d', strtotime("-$i days"))] = ['v' => 0, 'u' => 0];
    foreach (rows('SELECT day, SUM(views) v FROM stats_views WHERE day >= ? GROUP BY day', [$since]) as $r) $daily[$r['day']]['v'] = (int) $r['v'];
    foreach (rows('SELECT day, COUNT(*) u FROM stats_visitors WHERE day >= ? GROUP BY day', [$since]) as $r) $daily[$r['day']]['u'] = (int) $r['u'];
    return [
        'days' => $days, 'daily' => $daily,
        'pages' => rows('SELECT path, SUM(views) v FROM stats_views WHERE day >= ? GROUP BY path ORDER BY v DESC LIMIT 15', [$since]),
        'countries' => rows('SELECT country, COUNT(*) n FROM stats_visitors WHERE day >= ? GROUP BY country ORDER BY n DESC LIMIT 15', [$since]),
        'wa' => rows("SELECT e.ref, SUM(e.count) n, b.title FROM stats_events e LEFT JOIN books b ON b.slug = e.ref WHERE e.event = 'whatsapp' AND e.day >= ? GROUP BY e.ref ORDER BY n DESC", [$since]),
        'events' => rows("SELECT event, SUM(count) n FROM stats_events WHERE day >= ? GROUP BY event", [$since]),
    ];
}

/* ----------------------------- Textes du site ---------------------------- */
function texts_page(callable $admin): never
{
    $reg = text_registry();
    $groups = array_values(array_unique(array_column($reg, 0)));
    $group = $_GET['groupe'] ?? $groups[0];
    if (!in_array($group, $groups, true)) $group = $groups[0];
    $langs = active_langs();
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        foreach ($reg as $key => $def) {
            if ($def[0] !== $group) continue;
            foreach ($langs as $l) {
                $v = $_POST['t'][$l][$key] ?? null;
                if ($v === null) continue;
                q('INSERT INTO texts(key, lang, value) VALUES(?,?,?) ON CONFLICT(key, lang) DO UPDATE SET value = excluded.value', [$key, $l, str_replace("\r\n", "\n", trim((string) $v))]);
            }
        }
        content_touched(); audit('textes modifiés', $group);
        flash('ok', 'Textes « ' . $group . ' » enregistrés.');
        redirect(admin_url('textes') . '?groupe=' . rawurlencode($group));
    }
    $values = [];
    foreach (rows('SELECT key, lang, value FROM texts') as $r) $values[$r['lang']][$r['key']] = $r['value'];
    $admin('Textes du site', view('admin/texts', compact('reg', 'groups', 'group', 'langs', 'values')));
}

/* -------------------------------- Réglages ------------------------------- */
function settings_schema(): array
{
    return [
        'Identité' => [
            ['site.name', 'Nom d’usage (signature du site)', 'text'],
            ['site.full_name', 'Nom complet (biographie, mentions légales)', 'text'],
            ['site.domain', 'Nom de domaine (sans https://)', 'text'],
            ['site.email', 'Adresse e-mail publique', 'email'],
            ['mail.to', 'E-mail de réception des messages du formulaire (si différent)', 'email'],
            ['site.nationality', 'Nationalité (données structurées Google)', 'text'],
            ['profile', 'Profil d’auteur', 'select', ['ecrivain' => 'Écrivain / homme de lettres (frise littéraire)', 'carriere' => 'Carrière + livre(s) (frise de carrière, section « Ses livres »)']],
        ],
        'Commande WhatsApp' => [
            ['whatsapp.number', 'Numéro WhatsApp (format international, chiffres seulement)', 'text', null, 'Ex. 22899592629. Utilisé par tous les boutons « Je veux ce livre ».'],
            ['whatsapp.display', 'Numéro affiché', 'text', null, 'Ex. +228 99 59 26 29'],
            ['whatsapp.template', 'Modèle du message de commande', 'textarea', null, 'Variables : {titre}, {edition} (année, ou « 2e édition, année »), {annee}.'],
            ['whatsapp.provenance', 'Ligne de provenance', 'text', null, 'Variable : {domaine}. Ajoutée en fin de message.'],
        ],
        'Identité visuelle' => [
            ['theme.ink', 'Noir (textes, fonds sombres)', 'color'],
            ['theme.cream', 'Crème (fond principal)', 'color'],
            ['theme.cream_2', 'Crème secondaire (sections alternées)', 'color'],
            ['theme.ocre', 'Ocre / doré (formes courbes, accents)', 'color'],
            ['theme.ocre_text', 'Ocre foncé lisible (chiffres sur fond clair)', 'color'],
            ['theme.red', 'Rouge (titres d’impact, appels à l’action)', 'color'],
            ['theme.font_display', 'Police des titres', 'select', [
                "'Fraunces', 'Iowan Old Style', Georgia, serif" => 'Fraunces (serif littéraire expressive)',
                "Georgia, 'Times New Roman', serif" => 'Georgia (système)',
                "'Iowan Old Style', 'Palatino Linotype', Palatino, serif" => 'Palatino (système)']],
            ['theme.font_body', 'Police du texte', 'select', [
                "'Manrope', system-ui, -apple-system, 'Segoe UI', sans-serif" => 'Manrope (sans empattement)',
                "system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif" => 'Police du système']],
        ],
        'Pages et modules' => [
            ['modules', 'Pages et modules activés', 'modules'],
        ],
        'Langues' => [
            ['lang.active', 'Langues actives', 'langs'],
        ],
        'Mesure d’audience' => [
            ['stats.internal', 'Statistiques intégrées sans cookie (pages vues, pays, clics WhatsApp)', 'checkbox'],
            ['analytics.snippet', 'Code d’un outil externe (Plausible, Matomo…) — facultatif', 'code'],
            ['analytics.needs_consent', 'Ce code dépose des cookies : ne le charger qu’après accord du visiteur', 'checkbox'],
        ],
        'Pied de page' => [
            ['footer.credit', 'Mention de développement', 'text'],
            ['footer.credit_url', 'Lien de la mention', 'url'],
        ],
    ];
}

function settings_page(callable $admin): never
{
    $schema = settings_schema();
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $in = $_POST['s'] ?? [];
        foreach ($schema as $fields) foreach ($fields as $f) {
            [$key, , $type] = $f;
            $v = $in[$key] ?? null;
            switch ($type) {
                case 'checkbox': setting_set($key, !empty($v) ? '1' : '0'); break;
                case 'color': if (is_string($v) && preg_match('~^#[0-9a-fA-F]{6}$~', $v)) setting_set($key, strtolower($v)); break;
                case 'modules': setting_set($key, array_map(fn($x) => 1, array_flip(array_keys(array_filter((array) $v)))) + array_fill_keys(array_keys(modules()), 0)); break;
                case 'langs':
                    $langs = array_values(array_intersect(['fr', 'en'], array_keys(array_filter((array) $v))));
                    if (!$langs) $langs = ['fr'];
                    $def = default_lang(); if (!in_array($def, $langs, true)) array_unshift($langs, $def);
                    setting_set($key, array_values(array_unique($langs)));
                    break;
                case 'select': if (isset($f[3][$v])) setting_set($key, $v); break;
                default: if ($v !== null) setting_set($key, trim(str_replace("\r\n", "\n", (string) $v)));
            }
        }
        if (isset($in['whatsapp.number'])) setting_set('whatsapp.number', preg_replace('~\D~', '', (string) $in['whatsapp.number']));
        content_touched(); audit('réglages modifiés');
        flash('ok', 'Réglages enregistrés.');
        redirect(admin_url('reglages') . '#' . ($_POST['tab'] ?? ''));
    }
    $admin('Réglages', view('admin/settings', ['schema' => $schema]));
}
