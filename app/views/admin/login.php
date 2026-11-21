<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title>Connexion · Administration</title>
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="login">
  <div class="login__art" aria-hidden="true">
    <svg viewBox="0 0 600 800" preserveAspectRatio="none"><path d="M-20 620 C 160 420, 300 760, 620 520"/><path d="M-20 660 C 180 460, 320 800, 620 560"/><path d="M-20 180 C 200 60, 380 320, 620 140"/></svg>
    <p class="login__name"><?= e(setting('site.name', 'Plume')) ?></p>
  </div>
  <main class="login__panel">
    <form method="post" class="login__form" autocomplete="on">
      <p class="login__kicker">Administration</p>
      <h1>Bonjour.</h1>
      <p class="muted">Connectez-vous pour mettre à jour le site.</p>
      <?php if ($error): ?><div class="flash flash--error" role="alert"><?= e($error) ?></div><?php endif; ?>
      <?= csrf_field() ?>
      <label class="f"><span>E-mail</span><input type="email" name="email" required autocomplete="username" autofocus value="<?= e($_POST['email'] ?? '') ?>"></label>
      <label class="f"><span>Mot de passe</span><input type="password" name="password" required autocomplete="current-password"></label>
      <button class="b b--primary b--block" type="submit">Se connecter</button>
      <a class="login__back" href="<?= e(url('/')) ?>">← Retour au site</a>
    </form>
  </main>
</body>
</html>
