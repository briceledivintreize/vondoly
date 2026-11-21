<?php
/** Tableau de bord. */
$u = current_user();
$h = (int) date('G');
$hello = $h < 12 ? 'Bonjour' : ($h < 18 ? 'Bon après-midi' : 'Bonsoir');
$max = max(1, ...array_map(fn($r) => (int) $r['v'], $spark ?: [['v' => 1]]));
$slots = photo_slots();
?>
<section class="hello">
  <div>
    <p class="kicker"><?= e(fr_date(date('Y-m-d'), 'EEEE d MMMM y')) ?></p>
    <h2><?= $hello ?>, <?= e(explode(' ', $u['name'] ?: 'à vous')[0]) ?>.</h2>
    <p class="muted">Tout ce que vous modifiez ici est publié immédiatement sur le site.</p>
  </div>
  <div class="hello__actions">
    <a class="b b--primary" href="<?= e(admin_url('c/news/new')) ?>">+ Ajouter une actualité</a>
    <a class="b" href="<?= e(admin_url('c/books/new')) ?>">+ Ajouter un livre</a>
    <a class="b" href="<?= e(admin_url('photos')) ?>">Remplacer une photo</a>
  </div>
</section>

<section class="kpis">
  <div class="kpi"><span>Pages vues (30 j)</span><strong><?= number_format($views, 0, ',', ' ') ?></strong>
    <svg class="spark" viewBox="0 0 120 32" preserveAspectRatio="none" aria-hidden="true"><?php foreach ($spark as $i => $r): $bh = max(1, 30 * $r['v'] / $max); ?><rect x="<?= $i * 4 ?>" y="<?= 32 - $bh ?>" width="3" height="<?= $bh ?>"/><?php endforeach; ?></svg></div>
  <div class="kpi"><span>Visiteurs (30 j)</span><strong><?= number_format($visitors, 0, ',', ' ') ?></strong></div>
  <div class="kpi kpi--red"><span>Clics « Je veux ce livre » (30 j)</span><strong><?= number_format($wa, 0, ',', ' ') ?></strong></div>
  <div class="kpi"><span>Fiches livres</span><strong><?= $books ?></strong><small><?= $noSummary ?> sans résumé</small></div>
</section>

<div class="grid2">
  <section class="card">
    <header class="card__head"><h2>Points à valider</h2><span class="pill pill--soft"><?= count($checks) ?></span></header>
    <?php if (!$checks): ?><p class="muted">Rien en attente. Les notes internes des fiches apparaissent ici.</p><?php else: ?>
    <ul class="checklist">
      <?php foreach ($checks as $ck): ?>
      <li><a href="<?= e($ck['url']) ?>">
        <span class="checklist__label"><?= e(plain((string) $ck['label'], 70)) ?><?php if (!$ck['visible']): ?> <em class="tag">masqué</em><?php endif; ?></span>
        <small><?= e(plain((string) $ck['note'], 140)) ?></small></a></li>
      <?php endforeach; ?>
    </ul>
    <?php endif; ?>
  </section>

  <section class="card">
    <header class="card__head"><h2>Couvertures</h2></header>
    <p class="muted">Remplacer une couverture : ouvrez la fiche, envoyez la nouvelle image. Rien d’autre ne change.</p>
    <?php if ($noCover): ?>
    <h3 class="mini">Manquantes (<?= count($noCover) ?>)</h3>
    <ul class="chips"><?php foreach ($noCover as $b): ?><li><a href="<?= e(admin_url('c/books/' . $b['id'])) ?>"><?= e($b['title']) ?> <small><?= (int) $b['year'] ?></small></a></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <?php if ($lowCover): ?>
    <h3 class="mini">En basse définition (<?= count($lowCover) ?>)</h3>
    <ul class="chips chips--warn"><?php foreach ($lowCover as $b): ?><li><a href="<?= e(admin_url('c/books/' . $b['id'])) ?>"><?= e($b['title']) ?> <small><?= (int) $b['w'] ?> px</small></a></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <?php if ($emptySlots): ?>
    <h3 class="mini">Emplacements photo vides</h3>
    <ul class="chips"><?php foreach ($emptySlots as $s): ?><li><a href="<?= e(admin_url('c/photos') . '?slot=' . $s) ?>"><?= e($slots[$s]['label']) ?></a></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </section>

  <section class="card">
    <header class="card__head"><h2>Messages non lus</h2><a href="<?= e(admin_url('messages')) ?>" class="link">Tout voir</a></header>
    <?php if (!$unread): ?><p class="muted">Aucun nouveau message.</p><?php else: ?>
    <ul class="msglist"><?php foreach ($unread as $m): ?><li><a href="<?= e(admin_url('messages')) ?>#m<?= (int) $m['id'] ?>"><strong><?= e($m['name']) ?></strong> <small><?= e(fr_date($m['created_at'], 'd MMM, HH:mm')) ?></small><span><?= e(plain($m['subject'] ?: $m['body'], 90)) ?></span></a></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </section>

  <section class="card">
    <header class="card__head"><h2>Prochaine apparition</h2></header>
    <?php if ($next): ?>
      <p><strong><?= e($next['title']) ?></strong><br><span class="muted"><?= e(fr_date($next['date'])) ?><?= $next['place'] ? ' · ' . e($next['place']) : '' ?></span></p>
      <a class="link" href="<?= e(admin_url('c/news/' . $next['id'])) ?>">Modifier</a>
    <?php else: ?>
      <p class="muted">Aucune : le bloc est masqué sur l’accueil. Ajoutez une actualité datée dans le futur et cochez « Prochaine apparition ».</p>
      <a class="b b--sm" href="<?= e(admin_url('c/news/new')) ?>">+ Ajouter</a>
    <?php endif; ?>
  </section>
</div>
