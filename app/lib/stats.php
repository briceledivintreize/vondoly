<?php
/**
 * Mesure d'audience intégrée, sans cookie ni donnée personnelle :
 *  - pages vues par jour ;
 *  - visiteurs uniques du jour (empreinte salée renouvelée chaque jour, irréversible) ;
 *  - pays (en-tête Cloudflare si disponible, sinon région de la langue du navigateur) ;
 *  - clics sur « Je veux ce livre ».
 * Un outil externe (Plausible, Matomo) peut s'ajouter dans les réglages.
 */
declare(strict_types=1);

function is_bot(): bool
{
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return $ua === '' || (bool) preg_match('~bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp|curl|wget|python|headless|lighthouse~i', $ua);
}

function visitor_country(): string
{
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY']) && preg_match('~^[A-Z]{2}$~', $_SERVER['HTTP_CF_IPCOUNTRY'])) return $_SERVER['HTTP_CF_IPCOUNTRY'];
    if (preg_match('~^[a-z]{2}-([A-Z]{2})~', $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', $m)) return $m[1];
    return '—';
}

function stats_record_view(string $path): void
{
    if (!setting('stats.internal', '1') || is_bot() || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return;
    try {
        $day = date('Y-m-d');
        q('INSERT INTO stats_views(day, path, views) VALUES(?, ?, 1) ON CONFLICT(day, path) DO UPDATE SET views = views + 1', [$day, mb_substr($path, 0, 200)]);
        $salt = setting('stats.salt', 'plume') . $day;
        $hash = substr(hash('sha256', $salt . client_ip() . ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 24);
        q('INSERT OR IGNORE INTO stats_visitors(day, hash, country) VALUES(?, ?, ?)', [$day, $hash, visitor_country()]);
    } catch (Throwable) { /* la mesure ne doit jamais bloquer l'affichage */ }
}

function stats_record_event(string $event, string $ref): void
{
    if (is_bot()) return;
    q('INSERT INTO stats_events(day, event, ref, count) VALUES(?, ?, ?, 1) ON CONFLICT(day, event, ref) DO UPDATE SET count = count + 1',
        [date('Y-m-d'), mb_substr($event, 0, 40), mb_substr($ref, 0, 120)]);
}
