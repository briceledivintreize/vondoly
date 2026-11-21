<?php
/**
 * Assistant de création d'un site d'auteur (section 7 du cahier des charges).
 * Copier le socle sur un nouvel hébergement, ouvrir le site : cet assistant s'affiche.
 * Aucun code à écrire ; tout le reste se règle ensuite dans l'administration.
 */
declare(strict_types=1);
require APP . '/install_lib.php';
session_boot();

if (db_installed() && (int) val("SELECT COUNT(*) FROM sqlite_master WHERE name = 'users'") && (int) val('SELECT COUNT(*) FROM users')) {
    redirect(base_path() . '/');
}

$errors = []; $in = [];
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_check();
    $in = array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);
    if (mb_strlen($in['name'] ?? '') < 2) $errors[] = 'Indiquez le nom d’usage de l’auteur.';
    if (!filter_var($in['admin_email'] ?? '', FILTER_VALIDATE_EMAIL)) $errors[] = 'Adresse e-mail de l’administrateur invalide.';
    if (mb_strlen($in['admin_password'] ?? '') < 12) $errors[] = 'Le mot de passe doit contenir au moins 12 caractères.';
    if (($in['admin_password'] ?? '') !== ($in['admin_password2'] ?? '')) $errors[] = 'Les deux mots de passe ne correspondent pas.';
    if (!is_writable(dirname(DB_FILE)) && !is_writable(ROOT)) $errors[] = 'Le dossier « data » doit être accessible en écriture.';
    if (!$errors) {
        $GLOBALS['__settings_dirty'] = true;
        install_base([
            'name' => $in['name'], 'full_name' => $in['full_name'] ?: $in['name'], 'domain' => $in['domain'] ?? '', 'email' => $in['email'] ?? '',
            'whatsapp' => $in['whatsapp'] ?? '', 'profile' => $in['profile'] ?? 'ecrivain', 'palette' => $in['palette'] ?? 'continents',
            'modules' => ['parcours' => 1, 'reconnaissance' => 1, 'oeuvres' => 1, 'editeur' => !empty($in['editeur']) ? 1 : 0, 'actualites' => 1, 'contact' => 1, 'bibliotheque3d' => 1],
        ]);
        install_user($in['admin_email'], $in['admin_name'] ?: $in['name'], $in['admin_password'], 'admin');
        flash('ok', 'Site créé. Connectez-vous pour compléter les contenus.');
        redirect(base_path() . '/admin/login');
    }
}
$v = fn($k, $d = '') => e($in[$k] ?? $d);
?><!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Créer un site d’auteur · Plume</title><link rel="stylesheet" href="<?= asset('css/admin.css') ?>"></head>
<body class="login">
<div class="login__art" aria-hidden="true"><svg viewBox="0 0 600 800" preserveAspectRatio="none"><path d="M-20 620 C 160 420, 300 760, 620 520"/><path d="M-20 660 C 180 460, 320 800, 620 560"/></svg><p class="login__name">Un nouveau site d’auteur</p></div>
<main class="login__panel">
<form method="post" class="login__form" style="max-width:520px">
  <p class="login__kicker">Plume · installation</p>
  <h1>Créer le site</h1>
  <p class="muted">Ces informations se modifient ensuite à tout moment dans l’administration.</p>
  <?php foreach ($errors as $er): ?><div class="flash flash--error"><?= e($er) ?></div><?php endforeach; ?>
  <?= csrf_field() ?>
  <h2 class="mini">L’auteur</h2>
  <label class="f"><span class="f__label">Nom d’usage (signature du site) *</span><input name="name" required value="<?= $v('name') ?>" placeholder="Ex. Sébastien Vondoly"></label>
  <label class="f"><span class="f__label">Nom complet</span><input name="full_name" value="<?= $v('full_name') ?>"></label>
  <label class="f"><span class="f__label">Profil</span><select name="profile"><option value="ecrivain">Écrivain / homme de lettres</option><option value="carriere" <?= ($in['profile'] ?? '') === 'carriere' ? 'selected' : '' ?>>Carrière + livre(s)</option></select></label>
  <label class="check"><input type="checkbox" name="editeur" value="1" <?= !empty($in['editeur']) ? 'checked' : '' ?>><span>L’auteur dirige une maison d’édition (active la page « L’éditeur »)</span></label>
  <h2 class="mini">Coordonnées</h2>
  <label class="f"><span class="f__label">Nom de domaine</span><input name="domain" value="<?= $v('domain', $_SERVER['HTTP_HOST'] ?? '') ?>" placeholder="auteur.com"></label>
  <label class="f"><span class="f__label">E-mail public</span><input type="email" name="email" value="<?= $v('email') ?>"></label>
  <label class="f"><span class="f__label">Numéro WhatsApp des commandes (format international)</span><input name="whatsapp" value="<?= $v('whatsapp') ?>" placeholder="22890000000"></label>
  <label class="f"><span class="f__label">Palette de départ</span><select name="palette"><option value="continents">Noir, ocre et rouge</option><option value="nuit">Bleu nuit et or</option><option value="bordeaux">Noir et bordeaux</option></select></label>
  <h2 class="mini">Compte administrateur</h2>
  <label class="f"><span class="f__label">Nom</span><input name="admin_name" value="<?= $v('admin_name') ?>"></label>
  <label class="f"><span class="f__label">E-mail de connexion *</span><input type="email" name="admin_email" required value="<?= $v('admin_email') ?>"></label>
  <label class="f"><span class="f__label">Mot de passe (12 caractères minimum) *</span><input type="password" name="admin_password" required minlength="12" autocomplete="new-password"></label>
  <label class="f"><span class="f__label">Confirmer le mot de passe *</span><input type="password" name="admin_password2" required minlength="12" autocomplete="new-password"></label>
  <button class="b b--primary b--block" type="submit">Créer le site</button>
</form>
</main>
</body></html>
