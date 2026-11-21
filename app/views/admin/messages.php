<?php /** Messages du formulaire de contact. Variable : $msgs */ ?>
<p class="muted intro">Tous les messages du formulaire sont conservés ici, même si l’envoi par e-mail échoue.</p>
<?php if (!$msgs): ?><div class="card empty"><p>Aucun message pour l’instant.</p></div><?php endif; ?>
<div class="messages">
<?php foreach ($msgs as $m): ?>
  <article class="card message <?= $m['is_read'] ? '' : 'is-unread' ?>" id="m<?= (int) $m['id'] ?>">
    <header>
      <div><strong><?= e($m['name']) ?></strong> · <a href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?: 'Votre message')) ?>"><?= e($m['email']) ?></a><?= $m['phone'] ? ' · ' . e($m['phone']) : '' ?></div>
      <small><?= e(fr_date($m['created_at'], 'd MMMM y, HH:mm')) ?><?= $m['mailed'] ? ' · transmis par e-mail' : '' ?></small>
    </header>
    <?php if ($m['subject']): ?><h3><?= e($m['subject']) ?></h3><?php endif; ?>
    <p><?= nl2br(e($m['body'])) ?></p>
    <footer>
      <a class="b b--sm b--primary" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . ($m['subject'] ?: 'Votre message')) ?>">Répondre</a>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><button class="b b--sm" type="submit"><?= $m['is_read'] ? 'Marquer non lu' : 'Marquer lu' ?></button></form>
      <form method="post" data-confirm="Supprimer ce message ?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><input type="hidden" name="do" value="delete"><button class="b b--sm b--ghost" type="submit">Supprimer</button></form>
    </footer>
  </article>
<?php endforeach; ?>
</div>
