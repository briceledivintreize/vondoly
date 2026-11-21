<?php
/** Formulaire générique. Variables : $c, $item, $errors, $trs, $scope, $heading */
$id = $item['id'] ?? null;
$q = $scope ? '?slot=' . $scope['slot'] : '';
$extraLangs = array_values(array_diff(active_langs(), [default_lang()]));
$langNames = ['en' => 'Anglais', 'fr' => 'Français'];
$err = fn($n) => isset($errors[$n]) ? '<span class="f__err">' . e($errors[$n]) . '</span>' : '';
$ratioLabel = fn($r) => $r ? ($r === '1200x630' ? '1200 × 630 px' : str_replace(':', ' : ', $r)) : 'libre';
?>
<form method="post" enctype="multipart/form-data" class="form" novalidate data-form>
  <?= csrf_field() ?>
  <?php if ($scope): ?><input type="hidden" name="slot" value="<?= e($scope['slot']) ?>"><?php endif; ?>
  <div class="form__head">
    <?php $back = !$scope ? admin_url('c/' . $c['key']) : ($scope['max'] === 1 ? admin_url('photos') : admin_url('c/photos') . $q); ?>
    <a class="link" href="<?= e($back) ?>">← <?= e($scope ? $scope['label'] : $c['label']) ?></a>
    <h2><?= e($heading) ?></h2>
    <?php if ($errors): ?><div class="flash flash--error" role="alert">Merci de corriger les champs signalés.</div><?php endif; ?>
  </div>

  <div class="form__grid">
    <div class="form__main card">
      <?php foreach ($c['fields'] as $f):
        $n = $f['name']; $v = $item[$n] ?? null; $col = $f['col'] ?? 'full';
        $req = !empty($f['required']) ? ' required' : '';
        $lbl = '<span class="f__label">' . e($f['label']) . (!empty($f['required']) ? ' <b aria-hidden="true">*</b>' : '') . '</span>';
        $help = !empty($f['help']) ? '<small class="f__help">' . e($f['help']) . '</small>' : '';
      ?>
      <div class="f f--<?= e($col) ?> <?= isset($errors[$n]) ? 'f--error' : '' ?>">
        <?php switch ($f['type']):
          case 'textarea': case 'rich': ?>
            <label for="f-<?= e($n) ?>"><?= $lbl ?></label>
            <?php if ($f['type'] === 'rich'): ?><div class="md-tools" data-md-for="f-<?= e($n) ?>"><button type="button" data-md="bold" title="Gras"><b>G</b></button><button type="button" data-md="italic" title="Italique"><i>I</i></button><button type="button" data-md="link" title="Lien">Lien</button><button type="button" data-md="h3" title="Intertitre">Titre</button><button type="button" data-md="quote" title="Citation">« »</button><button type="button" data-md="list" title="Liste">• Liste</button></div><?php endif; ?>
            <textarea id="f-<?= e($n) ?>" name="<?= e($n) ?>" rows="<?= $f['type'] === 'rich' ? 10 : 4 ?>"<?= $req ?> data-autosize><?= e($v) ?></textarea>
            <?= $help ?><?= $err($n) ?>
            <?php break;
          case 'checkbox': ?>
            <label class="check"><input type="checkbox" name="<?= e($n) ?>" value="1" <?= $v || (!$id && $n === 'active') || (!$id && $n === 'in_footer') ? 'checked' : '' ?>><span><?= e($f['label']) ?></span></label><?= $help ?>
            <?php break;
          case 'select': case 'relation': $opts = field_options($f, $item); ?>
            <label for="f-<?= e($n) ?>"><?= $lbl ?></label>
            <select id="f-<?= e($n) ?>" name="<?= e($n) ?>"<?= $req ?>>
              <?php if (empty($f['options']) || $f['type'] === 'relation'): ?><option value="">—</option><?php endif; ?>
              <?php foreach ($opts as $k => $o): ?><option value="<?= e($k) ?>" <?= (string) $v === (string) $k ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?>
            </select>
            <?= $help ?><?= $err($n) ?>
            <?php if (isset($f['source']) && in_array($f['source'][0], ['genres', 'publishers'], true)): ?><small class="f__help"><a href="<?= e(admin_url('c/' . $f['source'][0])) ?>" target="_blank">Gérer la liste ↗</a></small><?php endif; ?>
            <?php break;
          case 'image':
            $ratio = $f['ratio'] === 'slot' ? ($scope['ratio'] ?? null) : ($f['ratio'] ?? null);
            $m = media($v ? (int) $v : null); ?>
            <span class="f__label"><?= e($f['label']) ?> <small class="muted">· format <?= e($ratioLabel($ratio)) ?></small></span>
            <div class="img-field" data-img-field>
              <label class="drop" style="--ar: <?= $ratio ? e(str_replace([':', 'x'], ' / ', $ratio)) : ($c['key'] === 'books' ? '2 / 3' : '4 / 3') ?>">
                <input type="file" name="<?= e($n) ?>_file" accept="image/jpeg,image/png,image/webp,image/avif" data-img-input>
                <?php if ($m): ?><img src="<?= e(media_src($m, 480)) ?>" alt="" data-img-preview><?php else: ?><img alt="" data-img-preview hidden><?php endif; ?>
                <span class="drop__hint"><?= $m ? 'Remplacer l’image' : 'Choisir une image' ?><small>ou la déposer ici · JPEG, PNG, WebP · 20 Mo max.</small></span>
              </label>
              <?php if ($m): ?>
              <p class="img-meta">Original : <?= (int) $m['orig_width'] ?> × <?= (int) $m['orig_height'] ?> px<?php if ($m['orig_width'] < 600): ?> · <b class="warn">basse définition</b><?php endif; ?> · <a href="<?= e(media_orig($m)) ?>" target="_blank">Voir</a></p>
              <label class="check check--sm"><input type="checkbox" name="<?= e($n) ?>_remove" value="1"><span>Retirer l’image (l’image provisoire s’affichera)</span></label>
              <?php endif; ?>
              <label class="f__sub"><span>Texte alternatif (description de l’image)</span><input type="text" name="<?= e($n) ?>_alt" value="<?= e($m['alt'] ?? '') ?>" placeholder="Ex. Sébastien Vondoly dédicaçant son roman au salon de Lomé"></label>
            </div>
            <?= $help ?><?= $err($n) ?>
            <?php break;
          case 'password': ?>
            <label for="f-<?= e($n) ?>"><?= $lbl ?></label>
            <input id="f-<?= e($n) ?>" type="password" name="<?= e($n) ?>" autocomplete="new-password" minlength="12">
            <?= $help ?><?= $err($n) ?>
            <?php break;
          default:
            $type = ['number' => 'number', 'url' => 'url', 'date' => 'date', 'email' => 'email'][$f['type']] ?? 'text'; ?>
            <label for="f-<?= e($n) ?>"><?= $lbl ?></label>
            <input id="f-<?= e($n) ?>" type="<?= $type ?>" name="<?= e($n) ?>" value="<?= e($v) ?>"<?= $req ?> <?= $f['type'] === 'slug' ? 'pattern="[a-z0-9-]*" placeholder="générée automatiquement"' : '' ?>>
            <?= $help ?><?= $err($n) ?>
        <?php endswitch; ?>
        <?php if (!empty($f['tr']) && $extraLangs): foreach ($extraLangs as $l): ?>
          <label class="f__tr"><span><?= e($f['label']) ?> · <?= e($langNames[$l] ?? strtoupper($l)) ?></span>
            <?php if (in_array($f['type'], ['textarea', 'rich'], true)): ?><textarea name="tr[<?= e($l) ?>][<?= e($n) ?>]" rows="4" data-autosize><?= e($trs[$l][$n] ?? '') ?></textarea>
            <?php else: ?><input type="text" name="tr[<?= e($l) ?>][<?= e($n) ?>]" value="<?= e($trs[$l][$n] ?? '') ?>"><?php endif; ?>
          </label>
        <?php endforeach; endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <aside class="form__side">
      <div class="card sticky">
        <?php if (empty($c['no_visible'])): ?>
        <label class="check check--switch"><input type="checkbox" name="visible" value="1" <?= ($item['visible'] ?? 1) ? 'checked' : '' ?>><span><b>Visible sur le site</b><small>Décochez pour masquer sans supprimer.</small></span></label>
        <?php endif; ?>
        <?php if (has_note($c['table'])): ?>
        <label class="f"><span class="f__label">Note interne <small class="muted">(jamais publiée)</small></span>
          <textarea name="note" rows="3" placeholder="Ex. À confirmer par l’auteur avant publication." data-autosize><?= e($item['note'] ?? '') ?></textarea>
          <small class="f__help">Une note non vide fait apparaître l’élément dans « Points à valider ».</small></label>
        <?php endif; ?>
        <div class="form__actions">
          <button class="b b--primary b--block" type="submit">Enregistrer</button>
          <?php if (!$scope): ?><button class="b b--block" type="submit" name="_next" value="list">Enregistrer et revenir à la liste</button><?php endif; ?>
          <?php if ($id && !empty($c['public']) && !empty($item['slug'])): ?><a class="b b--ghost b--block" href="<?= e(url(str_replace('{slug}', $item['slug'], $c['public']))) ?>" target="_blank">Voir sur le site ↗</a><?php endif; ?>
        </div>
        <?php if ($c['key'] === 'books' && $id && wa_number()): ?>
        <?php $wl = wa_book_link(books_query('b.id = ?', [$id])[0] ?? $item); ?>
        <details class="wa-preview"><summary>Aperçu du message WhatsApp</summary><pre><?= e(rawurldecode(substr($wl, strpos($wl, 'text=') + 5))) ?></pre><a class="link" href="<?= e($wl) ?>" target="_blank" rel="noopener">Tester le lien ↗</a></details>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</form>
<?php if ($id): ?>
<form method="post" action="<?= e(admin_url('c/' . $c['key'] . '/' . $id . '/delete') . $q) ?>" class="danger-zone" data-confirm="Supprimer définitivement cet élément ? Cette action est irréversible (une sauvegarde quotidienne existe toutefois).">
  <?= csrf_field() ?>
  <button type="submit" class="b b--danger">Supprimer <?= e($c['one']) ?></button>
  <span class="muted">Pour le retirer du site sans le perdre, décochez plutôt « Visible sur le site ».</span>
</form>
<?php endif; ?>
