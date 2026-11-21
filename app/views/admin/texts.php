<?php
/** Textes du site. Variables : $reg, $groups, $group, $langs, $values */
$langNames = ['fr' => 'Français', 'en' => 'Anglais'];
?>
<p class="muted intro">Chaque texte visible sur le site se modifie ici : accroche, biographie, titres, boutons, mentions légales… Les mises en forme : **gras**, *italique*, [lien](https://…), ligne vide = nouveau paragraphe.</p>
<nav class="tabs" aria-label="Pages">
  <?php foreach ($groups as $g): ?><a href="<?= e(admin_url('textes') . '?groupe=' . rawurlencode($g)) ?>" <?= $g === $group ? 'aria-current="page"' : '' ?>><?= e($g) ?></a><?php endforeach; ?>
</nav>
<form method="post" class="card form-texts">
  <?= csrf_field() ?>
  <?php foreach ($reg as $key => [$grp, $label, $kind, $default]): if ($grp !== $group) continue; ?>
  <div class="f">
    <span class="f__label"><?= e($label) ?></span>
    <?php foreach ($langs as $l):
      $val = $values[$l][$key] ?? ($l === default_lang() ? $default : '');
      $name = 't[' . e($l) . '][' . e($key) . ']'; ?>
      <?php if (count($langs) > 1): ?><small class="f__lang"><?= e($langNames[$l] ?? $l) ?></small><?php endif; ?>
      <?php if ($kind === 'line'): ?><input type="text" name="<?= $name ?>" value="<?= e($val) ?>">
      <?php else: ?>
        <?php if ($kind === 'rich'): ?><div class="md-tools" data-md-for="t-<?= e($l . $key) ?>"><button type="button" data-md="bold"><b>G</b></button><button type="button" data-md="italic"><i>I</i></button><button type="button" data-md="link">Lien</button><button type="button" data-md="h3">Titre</button><button type="button" data-md="quote">« »</button><button type="button" data-md="list">• Liste</button></div><?php endif; ?>
        <textarea id="t-<?= e($l . $key) ?>" name="<?= $name ?>" rows="<?= $kind === 'rich' ? 12 : 3 ?>" data-autosize><?= e($val) ?></textarea>
      <?php endif; ?>
    <?php endforeach; ?>
    <small class="f__help f__key"><?= e($key) ?></small>
  </div>
  <?php endforeach; ?>
  <div class="form__actions form__actions--bar"><button class="b b--primary" type="submit">Enregistrer « <?= e($group) ?> »</button></div>
</form>
