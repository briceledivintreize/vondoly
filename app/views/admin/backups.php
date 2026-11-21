<?php /** Sauvegardes. Variable : $files */ ?>
<p class="muted intro">Une sauvegarde automatique (base de données + images) est créée chaque jour à la première connexion ; les 14 dernières sont conservées. Téléchargez-en une de temps en temps pour la garder hors du serveur.</p>
<form method="post" class="toolbar"><?= csrf_field() ?><input type="hidden" name="do" value="create"><button class="b b--primary" type="submit">Créer une sauvegarde maintenant</button></form>
<div class="card">
  <table class="table">
    <thead><tr><th>Fichier</th><th>Date</th><th class="num">Taille</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($files as $f): ?>
      <tr><td><?= e($f['name']) ?></td><td><?= e(date('d/m/Y H:i', $f['date'])) ?></td><td class="num"><?= number_format($f['size'] / 1048576, 1, ',', ' ') ?> Mo</td>
        <td class="actions"><a class="b b--sm" href="<?= e(admin_url('sauvegardes/telecharger/' . $f['name'])) ?>">Télécharger</a>
          <form method="post" data-confirm="Supprimer cette sauvegarde ?"><?= csrf_field() ?><input type="hidden" name="do" value="delete"><input type="hidden" name="file" value="<?= e($f['name']) ?>"><button class="b b--sm b--ghost">Supprimer</button></form></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php if (!$files): ?><p class="muted">Aucune sauvegarde pour l’instant.</p><?php endif; ?>
</div>
