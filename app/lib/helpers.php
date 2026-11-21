<?php
/** Fonctions utilitaires : échappement, URLs, vues, sécurité, mise en forme. */
declare(strict_types=1);

/* ------------------------------ Échappement ------------------------------ */
function e($s): string { return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function attr_json($data): string { return e(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); }

/* --------------------------------- URLs ---------------------------------- */
function base_path(): string
{
    static $b = null;
    if ($b === null) {
        $b = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if ($b === '.') $b = '';
        // Réécriture vers public/ depuis la racine : le préfixe n'apparaît pas dans l'URL visible
        if ($b !== '' && !str_starts_with((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), $b)) $b = '';
    }
    return $b;
}

/** URL publique, préfixée par la langue si elle n'est pas la langue par défaut. */
function url(string $path = '/', ?string $lang = null): string
{
    $lang ??= current_lang();
    $prefix = ($lang !== default_lang()) ? '/' . $lang : '';
    $path = '/' . ltrim($path, '/');
    return base_path() . $prefix . ($path === '/' && $prefix ? '' : $path);
}

function admin_url(string $path = ''): string { return base_path() . '/admin' . ($path ? '/' . ltrim($path, '/') : ''); }

function asset(string $path): string
{
    $file = PUB . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? substr(base_convert((string) filemtime($file), 10, 36), -5) : '0';
    return base_path() . '/assets/' . ltrim($path, '/') . '?v=' . $v;
}

function upload_url(string $rel): string { return base_path() . '/' . ltrim($rel, '/'); }

function site_origin(): string
{
    $domain = setting('site.domain');
    if ($domain && PHP_SAPI !== 'cli-server') return 'https://' . preg_replace('~^https?://~', '', rtrim($domain, '/'));
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

function absolute_url(string $path): string { return site_origin() . $path; }

function redirect(string $to, int $code = 303): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

/* ------------------------------- Langues --------------------------------- */
function default_lang(): string { return setting('lang.default', 'fr'); }
function active_langs(): array { return setting_json('lang.active', ['fr']); }
function current_lang(): string { return $GLOBALS['LANG'] ?? default_lang(); }

/* -------------------------------- Vues ----------------------------------- */
function view(string $name, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    include APP . '/views/' . $name . '.php';
    return ob_get_clean();
}

function partial(string $name, array $vars = []): void { echo view('partials/' . $name, $vars); }

/* ------------------------------- Sécurité -------------------------------- */
function session_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name('plume_sid');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => base_path() ?: '/', 'httponly' => true, 'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

function csrf_token(): string
{
    session_boot();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }

function csrf_check(): void
{
    session_boot();
    $t = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(419);
        exit('Session expirée. Rechargez la page et réessayez.');
    }
}

function flash(string $type, string $msg): void { session_boot(); $_SESSION['flash'][] = [$type, $msg]; }
function flashes(): array { session_boot(); $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

function client_ip(): string { return $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }

/** Limitation de débit simple : $max actions par $seconds pour un « seau ». */
function throttle(string $bucket, int $max, int $seconds): bool
{
    $now = time();
    q('DELETE FROM throttle WHERE ts < ?', [$now - 86400]);
    $n = (int) val('SELECT COUNT(*) FROM throttle WHERE bucket = ? AND ts > ?', [$bucket, $now - $seconds]);
    if ($n >= $max) return false;
    q('INSERT INTO throttle(bucket, ts) VALUES(?, ?)', [$bucket, $now]);
    return true;
}

function security_headers(bool $admin = false): void
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    if ($admin) header('X-Robots-Tag: noindex, nofollow');
}

/* ----------------------------- Mise en forme ----------------------------- */
function slugify(string $s): string
{
    $s = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $s) ?: strtolower($s);
    $s = preg_replace('~[^a-z0-9]+~', '-', $s);
    return trim($s, '-') ?: 'element';
}

/**
 * Mini-Markdown sûr pour les textes longs saisis dans l'administration :
 * paragraphes, **gras**, *italique*, [lien](https://…), ### intertitre, > citation, - listes.
 */
function md(?string $text): string
{
    $text = trim(str_replace("\r\n", "\n", (string) $text));
    if ($text === '') return '';
    $blocks = preg_split("~\n{2,}~", $text);
    $out = '';
    foreach ($blocks as $b) {
        $b = trim($b);
        if (preg_match('~^###\s+(.+)$~s', $b, $m)) { $out .= '<h3>' . md_inline($m[1]) . '</h3>'; continue; }
        if (preg_match('~^>\s?~', $b)) {
            $q = preg_replace('~^>\s?~m', '', $b);
            $out .= '<blockquote><p>' . nl2br(md_inline($q)) . '</p></blockquote>'; continue;
        }
        if (preg_match('~^[-•]\s+~', $b)) {
            $items = preg_split('~\n(?=[-•]\s+)~', $b);
            $out .= '<ul>' . implode('', array_map(fn($i) => '<li>' . md_inline(preg_replace('~^[-•]\s+~', '', $i)) . '</li>', $items)) . '</ul>';
            continue;
        }
        $out .= '<p>' . nl2br(md_inline($b)) . '</p>';
    }
    return $out;
}

function md_inline(string $s): string
{
    $s = e($s);
    $s = preg_replace('~\*\*(.+?)\*\*~s', '<strong>$1</strong>', $s);
    $s = preg_replace('~(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?!\w)~s', '<em>$1</em>', $s);
    $s = preg_replace_callback('~\[([^\]]+)\]\((https?://[^)\s]+|/[^)\s]*|mailto:[^)\s]+)\)~', function ($m) {
        $ext = str_starts_with($m[2], 'http');
        return '<a href="' . $m[2] . '"' . ($ext ? ' target="_blank" rel="noopener"' : '') . '>' . $m[1] . '</a>';
    }, $s);
    return $s;
}

function plain(?string $s, int $max = 0): string
{
    $s = trim(preg_replace('~\s+~', ' ', strip_tags(md((string) $s))));
    $s = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
    if ($max && mb_strlen($s) > $max) $s = rtrim(mb_substr($s, 0, $max - 1), " ,;:.") . '…';
    return $s;
}

function fr_date(?string $iso, string $fmt = 'd MMMM y'): string
{
    if (!$iso) return '';
    $ts = strtotime($iso);
    if (!$ts) return (string) $iso;
    $f = new IntlDateFormatter(current_lang() === 'en' ? 'en_GB' : 'fr_FR', IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Africa/Lome', null, $fmt);
    return $f->format($ts) ?: $iso;
}

/** Met un titre entre guillemets français, sauf s'il en contient déjà en tête. */
function quoted(?string $t): string
{
    $t = trim((string) $t);
    return str_starts_with($t, '«') ? $t : '« ' . $t . ' »';
}

function json_out($data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Icônes SVG (réseaux sociaux et interface), tracées à la main, cohérentes entre elles. */
function icon(string $name, string $class = 'icon'): string
{
    $p = [
        'x' => '<path d="M4 4h4.6l11.4 16h-4.6zM19.4 4l-6.2 6.9M4.6 20l6.2-6.9" stroke-width="1.5"/>',
        'facebook' => '<path d="M14 8h3V4h-3a4 4 0 0 0-4 4v3H7v4h3v7h4v-7h3l1-4h-4V8z"/>',
        'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5v.01"/>',
        'youtube' => '<rect x="2.5" y="5" width="19" height="14" rx="4"/><path d="M10 9l5 3-5 3z"/>',
        'tiktok' => '<path d="M14 3v11a4 4 0 1 1-4-4M14 3c.5 3 2.5 5 5 5"/>',
        'email' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'whatsapp' => '<path d="M4 20l1.3-4A8 8 0 1 1 8 18.7L4 20z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 .8a4 4 0 0 1-1.8-1.8l.8-1-1-2L9 9.5z"/>',
        'link' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
        'soundcloud' => '<path d="M3 15v2M6 12v5M9 10v7M12 8v9h6a3 3 0 0 0 0-6 5 5 0 0 0-6-3z"/>',
        'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left' => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'search' => '<circle cx="11" cy="11" r="6"/><path d="M20 20l-4.5-4.5"/>',
        'download' => '<path d="M12 4v11M7 10l5 5 5-5M5 20h14"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'play' => '<path d="M7 5l12 7-12 7z"/>',
        'flip' => '<path d="M4 12a8 8 0 0 1 14-5.3M20 12a8 8 0 0 1-14 5.3M18 3v4h-4M6 21v-4h4"/>',
        'zoom' => '<circle cx="11" cy="11" r="6"/><path d="M20 20l-4.5-4.5M11 8v6M8 11h6"/>',
        'share' => '<circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="18" cy="18" r="2.5"/><path d="M8.2 10.8l7.6-3.6M8.2 13.2l7.6 3.6"/>',
    ][$name] ?? '<circle cx="12" cy="12" r="8"/>';
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p . '</svg>';
}
