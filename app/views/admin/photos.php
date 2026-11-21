<?php
/** Emplacements photo. Variable : $slots */
?>
<p class="muted intro">Chaque emplacement a un format fixe : l’image est recadrée et compressée automatiquement à l’envoi. Tant qu’aucune photo n’est fournie, une image provisoire « Photo à venir » s’affiche.</p>
<div class="slots">
  <?php foreach ($slots as $key => $s):
    $items = rows('SELECT * FROM photos WHERE slot = ? ORDER BY sort, id', [$key]);
    $first = $items[0] ?? null; $m = $first ? media($first['media_id'] ? (int) $first['media_id'] : null) : null;
    $href = admin_url('c/photos') . '?slot=' . $key;
    $ar = $s['ratio'] ? str_replace([':', 'x'], ' / ', $s['ratio']) : '4 / 5'; ?>
  <a class="slot card" href="<?= e($href) ?>">
    <span class="slot__img" style="--ar: <?= e($ar) ?>"><?= $m ? '<img src="' . e(media_src($m, 480)) . '" alt="">' : '<em>Photo à venir</em>' ?></span>
    <span class="slot__body">
      <strong><?= e($s['label']) ?></strong>
      <small>Format <?= e($s['ratio'] ? ($s['ratio'] === '1200x630' ? '1200 × 630 px' : str_replace(':', ' : ', $s['ratio'])) : 'libre (haute définition)') ?> · <?= $s['max'] === 1 ? '1 photo' : count($items) . ' photo' . (count($items) > 1 ? 's' : '') ?></small>
      <span class="b b--sm"><?= $s['max'] === 1 ? ($m ? 'Remplacer' : 'Ajouter') : 'Gérer' ?></span>
    </span>
  </a>
  <?php endforeach; ?>
</div>
<p class="muted small">Les couvertures de livres se remplacent depuis chaque fiche (rubrique Livres).</p>
