<?php
/** Liste générique. Variables : $c, $items, $scope */
$q = $scope ? '?slot=' . $scope['slot'] : '';
$sortable = !empty($c['sortable']);
?>
<div class="toolbar">
  <div class="toolbar__left">
    <?php if ($scope): ?><a class="link" href="<?= e(admin_url('photos')) ?>">← Photos du site</a><?php endif; ?>
    <p class="muted"><?= count($items) ?> élément<?= count($items) > 1 ? 's' : '' ?><?php if ($sortable): ?> · glissez <span aria-hidden="true">⠿</span> ou utilisez ↑ ↓ pour classer<?php endif; ?>
    <?php if ($c['key'] === 'books'): ?> · Sur le site, les livres sont classés du plus récent au plus ancien ; l’ordre manuel départage ceux d’une même année.<?php endif; ?></p>
  </div>
  <div class="toolbar__right">
    <?php if (count($items) > 8): ?><input type="search" class="input" placeholder="Filtrer…" data-filter-list aria-label="Filtrer la liste"><?php endif; ?>
    <a class="b b--primary" href="<?= e(admin_url('c/' . $c['key'] . '/new') . $q) ?>">+ Ajouter <?= e($c['one']) ?></a>
  </div>
</div>

<?php if (!$items): ?>
  <div class="card empty"><p>Aucun élément pour l’instant.</p><a class="b b--primary" href="<?= e(admin_url('c/' . $c['key'] . '/new') . $q) ?>">+ Ajouter <?= e($c['one']) ?></a></div>
<?php else: ?>
<ul class="rows <?= $sortable ? 'rows--sortable' : '' ?>" data-sort-url="<?= $sortable ? e(admin_url('c/' . $c['key'] . '/sort')) : '' ?>">
  <?php foreach ($items as $it): $href = admin_url('c/' . $c['key'] . '/' . $it['id']) . $q; ?>
  <li class="row <?= isset($it['visible']) && !$it['visible'] ? 'is-hidden' : '' ?>" data-id="<?= (int) $it['id'] ?>" <?= $sortable ? 'draggable="true"' : '' ?> data-text="<?= e(mb_strtolower(implode(' ', array_map(fn($k) => (string) ($it[$k] ?? ''), array_keys($c['list']))))) ?>">
    <?php if ($sortable): ?><span class="row__grip" aria-hidden="true">⠿</span><?php endif; ?>
    <?php foreach ($c['list'] as $col => $label):
      if ($label === 'thumb'):
        $m = media($it[$col] ? (int) $it[$col] : null); ?>
        <a class="row__thumb <?= $c['key'] === 'books' ? 'row__thumb--book' : '' ?>" href="<?= e($href) ?>" tabindex="-1" aria-hidden="true"><?= $m ? '<img src="' . e(media_src($m, 200)) . '" alt="" loading="lazy">' : '<span>—</span>' ?></a>
      <?php elseif ($label === 'title'): ?>
        <a class="row__title" href="<?= e($href) ?>"><?= e(crud_cell($c, $col, $it) ?: 'Sans titre') ?>
          <?php if (!empty($it['note'])): ?><em class="tag tag--warn" title="<?= e($it['note']) ?>">à valider</em><?php endif; ?>
          <?php if (!empty($it['featured'])): ?><em class="tag tag--ok">mis en avant</em><?php endif; ?>
          <?php if (($it['edition_num'] ?? 1) > 1): ?><em class="tag"><?= (int) $it['edition_num'] ?>e éd.</em><?php endif; ?>
        </a>
      <?php else: ?>
        <span class="row__cell" data-label="<?= e($label) ?>"><?= e(crud_cell($c, $col, $it)) ?></span>
      <?php endif; endforeach; ?>
    <span class="row__actions">
      <?php if ($sortable): ?><button type="button" class="ib" data-move="-1" aria-label="Monter">↑</button><button type="button" class="ib" data-move="1" aria-label="Descendre">↓</button><?php endif; ?>
      <?php if (isset($it['visible']) && empty($c['no_visible'])): ?>
        <button type="button" class="switch" role="switch" aria-checked="<?= $it['visible'] ? 'true' : 'false' ?>" data-toggle="<?= e(admin_url('c/' . $c['key'] . '/' . $it['id'] . '/toggle')) ?>" title="Visible sur le site"><span class="visually-hidden">Visible sur le site</span></button>
      <?php endif; ?>
      <?php if (!empty($c['public']) && !empty($it['slug'])): ?><a class="ib" href="<?= e(url(str_replace('{slug}', $it['slug'], $c['public']))) ?>" target="_blank" rel="noopener" aria-label="Voir sur le site">↗</a><?php endif; ?>
      <a class="b b--sm" href="<?= e($href) ?>">Modifier</a>
    </span>
  </li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
