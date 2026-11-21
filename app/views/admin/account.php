<?php /** Mon compte. Variable : $user */ ?>
<div class="card narrow">
  <h2>Changer mon mot de passe</h2>
  <p class="muted"><?= e($user['email']) ?> · <?= $user['role'] === 'admin' ? 'Administrateur' : 'Éditeur de contenu' ?></p>
  <form method="post">
    <?= csrf_field() ?>
    <label class="f"><span class="f__label">Mot de passe actuel</span><input type="password" name="current" required autocomplete="current-password"></label>
    <label class="f"><span class="f__label">Nouveau mot de passe (12 caractères minimum)</span><input type="password" name="new" required minlength="12" autocomplete="new-password"></label>
    <label class="f"><span class="f__label">Confirmer</span><input type="password" name="confirm" required minlength="12" autocomplete="new-password"></label>
    <button class="b b--primary" type="submit">Enregistrer</button>
  </form>
</div>
