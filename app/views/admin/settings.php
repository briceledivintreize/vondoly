<?php
/** Réglages (administrateur). Variable : $schema */
$mods = modules();
$modLabels = ['parcours' => 'Parcours', 'reconnaissance' => 'Reconnaissance', 'oeuvres' => 'Œuvres', 'editeur' => 'L’éditeur (si l’auteur dirige une maison d’édition)',
    'actualites' => 'Actualités', 'contact' => 'Contact', 'bibliotheque3d' => 'Bibliothèque 3D animée sur l’accueil'];
$palettes = [
    'Continents (noir, ocre, rouge)' => ['theme.ink' => '#0e0c0a', 'theme.cream' => '#fbfaf6', 'theme.cream_2' => '#f2ece0', 'theme.ocre' => '#b8860b', 'theme.ocre_text' => '#8a6508', 'theme.red' => '#cc0000'],
    'Bleu nuit et or' => ['theme.ink' => '#0f1a2e', 'theme.cream' => '#faf8f3', 'theme.cream_2' => '#eef0f4', 'theme.ocre' => '#c9a227', 'theme.ocre_text' => '#7d6413', 'theme.red' => '#1f4fa3'],
    'Noir et bordeaux' => ['theme.ink' => '#111111', 'theme.cream' => '#faf7f4', 'theme.cream_2' => '#f1e9e4', 'theme.ocre' => '#a8823c', 'theme.ocre_text' => '#7a5c25', 'theme.red' => '#7b1e2b'],
];
?>
<form method="post" class="settings" data-settings>
  <?= csrf_field() ?>
  <input type="hidden" name="tab" value="" data-tab-input>
  <nav class="tabs" role="tablist">
    <?php foreach (array_keys($schema) as $i => $tab): ?><a href="#<?= e(slugify($tab)) ?>" role="tab" data-tab="<?= e(slugify($tab)) ?>" <?= $i === 0 ? 'aria-current="page"' : '' ?>><?= e($tab) ?></a><?php endforeach; ?>
  </nav>
  <?php foreach ($schema as $tab => $fields): ?>
  <section class="card tabpanel" id="<?= e(slugify($tab)) ?>" data-panel="<?= e(slugify($tab)) ?>">
    <h2><?= e($tab) ?></h2>
    <?php if ($tab === 'Identité visuelle'): ?>
      <p class="muted">Les couleurs et polices sont centralisées : les changer ici modifie tout le site, sans toucher au code. Gardez un contraste suffisant (texte noir sur fond crème).</p>
      <div class="palettes"><?php foreach ($palettes as $pn => $pv): ?><button type="button" class="palette" data-palette='<?= attr_json($pv) ?>'><?php foreach (['theme.ink', 'theme.ocre', 'theme.red', 'theme.cream'] as $k): ?><i style="background:<?= e($pv[$k]) ?>"></i><?php endforeach; ?><span><?= e($pn) ?></span></button><?php endforeach; ?></div>
    <?php endif; ?>
    <?php if ($tab === 'Commande WhatsApp'): ?>
      <p class="muted">Ces réglages s’appliquent à tous les boutons « Je veux ce livre » à la fois.</p>
    <?php endif; ?>
    <?php foreach ($fields as $f): [$key, $label, $type] = $f; $opts = $f[3] ?? null; $help = $f[4] ?? null; $v = setting($key, ''); ?>
    <div class="f <?= $type === 'color' ? 'f--color' : '' ?>">
      <?php switch ($type):
        case 'checkbox': ?>
          <label class="check"><input type="checkbox" name="s[<?= e($key) ?>]" value="1" <?= setting($key, $key === 'stats.internal' ? '1' : '0') === '1' ? 'checked' : '' ?>><span><?= e($label) ?></span></label>
          <?php break;
        case 'color': ?>
          <label><span class="f__label"><?= e($label) ?></span><span class="color"><input type="color" name="s[<?= e($key) ?>]" value="<?= e($v ?: '#000000') ?>" data-key="<?= e($key) ?>"><code><?= e($v) ?></code></span></label>
          <?php break;
        case 'select': ?>
          <label><span class="f__label"><?= e($label) ?></span><select name="s[<?= e($key) ?>]"><?php foreach ($opts as $k => $o): ?><option value="<?= e($k) ?>" <?= $v === $k ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?></select></label>
          <?php break;
        case 'textarea': case 'code': ?>
          <label><span class="f__label"><?= e($label) ?></span><textarea name="s[<?= e($key) ?>]" rows="<?= $type === 'code' ? 4 : 3 ?>" <?= $type === 'code' ? 'class="mono" spellcheck="false"' : '' ?> data-autosize <?= $key === 'whatsapp.template' ? 'data-wa-template' : '' ?>><?= e($v) ?></textarea></label>
          <?php break;
        case 'modules': ?>
          <span class="f__label"><?= e($label) ?></span>
          <div class="checks"><?php foreach ($modLabels as $mk => $ml): ?><label class="check"><input type="checkbox" name="s[modules][<?= e($mk) ?>]" value="1" <?= !empty($mods[$mk]) ? 'checked' : '' ?>><span><?= e($ml) ?></span></label><?php endforeach; ?></div>
          <small class="f__help">Une page désactivée disparaît du menu, du plan du site et renvoie une page 404.</small>
          <?php break;
        case 'langs': ?>
          <span class="f__label"><?= e($label) ?></span>
          <div class="checks">
            <label class="check"><input type="checkbox" checked disabled><span>Français (langue par défaut)</span><input type="hidden" name="s[lang.active][fr]" value="1"></label>
            <label class="check"><input type="checkbox" name="s[lang.active][en]" value="1" <?= in_array('en', active_langs(), true) ? 'checked' : '' ?>><span>Anglais</span></label>
          </div>
          <small class="f__help">Une fois l’anglais activé, chaque texte et chaque fiche présente un champ « Anglais ». Les contenus non traduits s’affichent en français, sans bloquer la mise en ligne.</small>
          <?php break;
        default: ?>
          <label><span class="f__label"><?= e($label) ?></span><input type="<?= $type === 'email' ? 'email' : ($type === 'url' ? 'url' : 'text') ?>" name="s[<?= e($key) ?>]" value="<?= e($v) ?>" <?= $key === 'whatsapp.provenance' ? 'data-wa-prov' : '' ?>></label>
      <?php endswitch; ?>
      <?php if ($help): ?><small class="f__help"><?= e($help) ?></small><?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if ($tab === 'Commande WhatsApp'): ?>
      <div class="wa-bubble" aria-live="polite"><small>Aperçu pour « Senghor ou rien » (2025)</small><p data-wa-preview></p></div>
    <?php endif; ?>
  </section>
  <?php endforeach; ?>
  <div class="form__actions form__actions--bar"><button class="b b--primary" type="submit">Enregistrer les réglages</button></div>
</form>
<p class="muted small">Plume <?= e(PLUME_VERSION) ?> · PHP <?= e(PHP_VERSION) ?> · <a href="<?= e(admin_url('cache')) ?>">Vider les caches</a></p>
