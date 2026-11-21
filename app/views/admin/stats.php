<?php
/** Statistiques. Variables : $days, $daily, $pages, $countries, $wa, $events */
$maxV = max(1, ...array_column($daily, 'v'));
$tot = array_sum(array_column($daily, 'v')); $totU = array_sum(array_column($daily, 'u'));
$ev = array_column($events, 'n', 'event');
$w = 720; $h = 180; $n = count($daily); $bw = $w / $n;
$regions = class_exists('Locale') ? fn($c) => $c === '—' ? 'Inconnu' : (Locale::getDisplayRegion('-' . $c, 'fr') ?: $c) : fn($c) => $c;
?>
<nav class="tabs"><?php foreach ([7 => '7 jours', 30 => '30 jours', 90 => '3 mois', 365 => '12 mois'] as $d => $l): ?><a href="?jours=<?= $d ?>" <?= $d === $days ? 'aria-current="page"' : '' ?>><?= $l ?></a><?php endforeach; ?></nav>
<section class="kpis">
  <div class="kpi"><span>Pages vues</span><strong><?= number_format($tot, 0, ',', ' ') ?></strong></div>
  <div class="kpi"><span>Visiteurs uniques (par jour)</span><strong><?= number_format($totU, 0, ',', ' ') ?></strong></div>
  <div class="kpi kpi--red"><span>Clics « Je veux ce livre »</span><strong><?= (int) ($ev['whatsapp'] ?? 0) ?></strong></div>
  <div class="kpi"><span>Dossiers de presse · partages</span><strong><?= (int) ($ev['presskit'] ?? 0) ?> · <?= (int) ($ev['share'] ?? 0) ?></strong></div>
</section>
<section class="card">
  <header class="card__head"><h2>Fréquentation</h2><small class="muted">barres : pages vues · ligne : visiteurs</small></header>
  <svg class="chart" viewBox="0 0 <?= $w ?> <?= $h + 24 ?>" role="img" aria-label="Pages vues par jour">
    <?php foreach (array_values($daily) as $i => $d): $bh = $h * $d['v'] / $maxV; ?>
      <rect x="<?= round($i * $bw + 1, 1) ?>" y="<?= round($h - $bh, 1) ?>" width="<?= round(max(1, $bw - 2), 1) ?>" height="<?= round($bh, 1) ?>"><title><?= e(array_keys($daily)[$i]) ?> : <?= $d['v'] ?> vues, <?= $d['u'] ?> visiteurs</title></rect>
    <?php endforeach; ?>
    <polyline fill="none" stroke="var(--red)" stroke-width="2" points="<?= implode(' ', array_map(fn($d, $i) => round($i * $bw + $bw / 2, 1) . ',' . round($h - $h * $d['u'] / $maxV, 1), array_values($daily), array_keys(array_values($daily)))) ?>"/>
    <text x="0" y="<?= $h + 18 ?>"><?= e(fr_date(array_key_first($daily), 'd MMM')) ?></text>
    <text x="<?= $w ?>" y="<?= $h + 18 ?>" text-anchor="end"><?= e(fr_date(array_key_last($daily), 'd MMM')) ?></text>
  </svg>
</section>
<div class="grid2">
  <section class="card"><header class="card__head"><h2>Pages les plus vues</h2></header>
    <table class="table"><tbody><?php foreach ($pages as $p): ?><tr><td><a href="<?= e(url($p['path'])) ?>" target="_blank"><?= e($p['path']) ?></a></td><td class="num"><?= (int) $p['v'] ?></td></tr><?php endforeach; ?></tbody></table>
    <?php if (!$pages): ?><p class="muted">Pas encore de données.</p><?php endif; ?></section>
  <section class="card"><header class="card__head"><h2>Pays des visiteurs</h2></header>
    <table class="table"><tbody><?php foreach ($countries as $c): ?><tr><td><?= e($regions($c['country'])) ?></td><td class="num"><?= (int) $c['n'] ?></td></tr><?php endforeach; ?></tbody></table>
    <p class="muted small">Pays déduit de l’hébergeur (Cloudflare) ou de la langue du navigateur ; aucune adresse IP n’est conservée.</p></section>
  <section class="card"><header class="card__head"><h2>Commandes WhatsApp par livre</h2></header>
    <table class="table"><tbody><?php foreach ($wa as $r): ?><tr><td><?= e($r['title'] ?: $r['ref']) ?></td><td class="num"><?= (int) $r['n'] ?></td></tr><?php endforeach; ?></tbody></table>
    <?php if (!$wa): ?><p class="muted">Aucun clic sur la période.</p><?php endif; ?></section>
</div>
