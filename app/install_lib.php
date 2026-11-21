<?php
/** Création d'un nouveau site d'auteur (utilisé par l'assistant web et par les scripts). */
declare(strict_types=1);

function install_base(array $o): void
{
    if (!is_dir(DATA)) mkdir(DATA, 0775, true);
    db_migrate();
    $palettes = [
        'continents' => ['#0e0c0a', '#fbfaf6', '#f2ece0', '#b8860b', '#8a6508', '#cc0000'],
        'nuit' => ['#0f1a2e', '#faf8f3', '#eef0f4', '#c9a227', '#7d6413', '#1f4fa3'],
        'bordeaux' => ['#111111', '#faf7f4', '#f1e9e4', '#a8823c', '#7a5c25', '#7b1e2b'],
    ];
    $p = $palettes[$o['palette'] ?? 'continents'] ?? $palettes['continents'];
    $domain = preg_replace('~^https?://|/.*$~', '', trim((string) ($o['domain'] ?? '')));
    $settings = [
        'site.name' => $o['name'], 'site.full_name' => $o['full_name'] ?? $o['name'], 'site.domain' => $domain,
        'site.email' => $o['email'] ?? '', 'profile' => $o['profile'] ?? 'ecrivain',
        'whatsapp.number' => preg_replace('~\D~', '', (string) ($o['whatsapp'] ?? '')),
        'whatsapp.display' => $o['whatsapp_display'] ?? '',
        'whatsapp.template' => 'Bonjour, je souhaite commander le livre « {titre} » ({edition}). Pouvez-vous me dire comment procéder ?',
        'whatsapp.provenance' => 'Depuis le site {domaine}',
        'theme.ink' => $p[0], 'theme.cream' => $p[1], 'theme.cream_2' => $p[2], 'theme.ocre' => $p[3], 'theme.ocre_text' => $p[4], 'theme.red' => $p[5],
        'theme.font_display' => "'Fraunces', 'Iowan Old Style', Georgia, serif",
        'theme.font_body' => "'Manrope', system-ui, -apple-system, 'Segoe UI', sans-serif",
        'modules' => json_encode($o['modules'] ?? ['parcours' => 1, 'reconnaissance' => 1, 'oeuvres' => 1, 'editeur' => 0, 'actualites' => 1, 'contact' => 1, 'bibliotheque3d' => 1]),
        'lang.default' => 'fr', 'lang.active' => '["fr"]',
        'stats.internal' => '1', 'stats.salt' => bin2hex(random_bytes(16)),
        'footer.credit' => 'Développé par Darollo Technologies Corporation © ' . date('Y'),
        'footer.credit_url' => 'https://www.darollotech.com',
        'content_version' => (string) time(),
    ];
    foreach ($settings as $k => $v) q('INSERT OR REPLACE INTO settings(key, value) VALUES(?, ?)', [$k, (string) $v]);
    // Textes par défaut (neutres) : l'auteur les remplace depuis l'administration
    foreach (text_registry() as $key => $def) q('INSERT OR IGNORE INTO texts(key, lang, value) VALUES(?, ?, ?)', [$key, 'fr', $def[3]]);
    q('INSERT OR REPLACE INTO texts(key, lang, value) VALUES(?, ?, ?)', ['legal.body', 'fr', default_legal($o)]);
    q('INSERT OR REPLACE INTO texts(key, lang, value) VALUES(?, ?, ?)', ['privacy.body', 'fr', default_privacy($o)]);
    // Listes de départ
    if (!(int) val('SELECT COUNT(*) FROM genres')) {
        foreach (['Poésie', 'Essai', 'Roman', 'Biographie', 'Hommages', 'Anthologie', 'Récit'] as $i => $g) insert('genres', ['name' => $g, 'slug' => slugify($g), 'sort' => $i + 1]);
    }
    foreach (['x', 'facebook', 'linkedin', 'instagram', 'youtube', 'tiktok', 'email'] as $i => $n) {
        if (!(int) val('SELECT COUNT(*) FROM social_links WHERE network = ?', [$n])) insert('social_links', ['network' => $n, 'url' => null, 'in_footer' => $n === 'email' ? 0 : 1, 'sort' => $i + 1]);
    }
    foreach (array_keys(photo_slots()) as $i => $s) {
        if (in_array($s, ['home_portrait', 'parcours_portrait', 'share', 'presskit'], true) && !(int) val('SELECT COUNT(*) FROM photos WHERE slot = ?', [$s])) {
            insert('photos', ['slot' => $s, 'media_id' => null, 'sort' => 1]);
        }
    }
}

function install_user(string $email, string $name, string $password, string $role = 'admin'): int
{
    return insert('users', ['email' => $email, 'name' => $name, 'password_hash' => password_hash($password, PASSWORD_DEFAULT), 'role' => $role, 'active' => 1]);
}

function default_legal(array $o): string
{
    $n = $o['full_name'] ?? $o['name'];
    return "### Éditeur du site\n\nLe site " . ($o['domain'] ?? '') . " est édité par **$n**" . (!empty($o['email']) ? ", joignable à l’adresse " . $o['email'] : '') . ".\n\nDirecteur de la publication : $n.\n\n### Conception et hébergement\n\nConception et développement : Darollo Technologies Corporation (DTC) SARL, BE Kpota, rue BD de l’OTI, Lomé (Togo) — [darollotech.com](https://www.darollotech.com).\n\nHébergement : *à compléter (nom, adresse et téléphone de l’hébergeur)*.\n\n### Propriété intellectuelle\n\nLes textes, photographies et couvertures reproduits sur ce site sont la propriété de leurs auteurs et éditeurs respectifs. Toute reproduction sans autorisation est interdite.";
}

function default_privacy(array $o): string
{
    return "Ce site collecte le minimum de données.\n\n### Formulaire de contact\n\nLes informations saisies (nom, e-mail, téléphone, message) servent uniquement à vous répondre. Elles ne sont ni vendues ni cédées, et sont supprimées sur simple demande" . (!empty($o['email']) ? ' à ' . $o['email'] : '') . ".\n\n### Mesure d’audience\n\nLe site mesure sa fréquentation sans cookie et sans conserver votre adresse IP : seules des statistiques anonymes et agrégées (pages vues, pays, clics sur les boutons de commande) sont enregistrées.\n\n### Contenus externes\n\nLes vidéos et sons intégrés (YouTube, SoundCloud) ne se chargent qu’après votre accord, via le bandeau de consentement ou un clic sur le lecteur.\n\n### Commandes par WhatsApp\n\nLe bouton « Je veux ce livre » ouvre WhatsApp : l’échange qui suit relève des conditions de ce service.";
}
