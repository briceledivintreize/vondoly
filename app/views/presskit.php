<?php
/** Dossier de presse (rendu PDF par dompdf : CSS volontairement simple). */
$ink = setting('theme.ink', '#0e0c0a'); $ocre = setting('theme.ocre', '#b8860b'); $red = setting('theme.red', '#cc0000');
$email = setting('site.email'); $domain = setting('site.domain');
?><!doctype html>
<html lang="<?= e(current_lang()) ?>"><head><meta charset="utf-8"><title>Dossier de presse — <?= e(setting('site.name')) ?></title>
<style>
@page { margin: 18mm 16mm 20mm; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.6pt; color: <?= $ink ?>; line-height: 1.5; }
h1, h2, h3 { font-family: 'DejaVu Serif', serif; font-weight: normal; margin: 0; }
h1 { font-size: 30pt; line-height: 1.05; letter-spacing: -0.5pt; }
h2 { font-size: 15pt; margin: 18pt 0 6pt; padding-bottom: 4pt; border-bottom: 1.2pt solid <?= $ocre ?>; }
.kicker { text-transform: uppercase; letter-spacing: 1.6pt; font-size: 7.5pt; color: <?= $red ?>; margin: 0 0 6pt; }
.full { font-style: italic; color: #5c5850; margin: 4pt 0 10pt; }
.cover td { vertical-align: top; }
.photo { width: 62mm; height: 74mm; }
.bio p { margin: 0 0 6pt; text-align: justify; }
table.list { width: 100%; border-collapse: collapse; }
table.list td { padding: 3.2pt 4pt; border-bottom: 0.4pt solid #ddd5c4; vertical-align: top; }
table.list td.y { width: 13mm; color: <?= $red ?>; font-weight: bold; }
.muted { color: #5c5850; }
.figs td { padding: 6pt 10pt 6pt 0; }
.figs strong { font-family: 'DejaVu Serif', serif; font-size: 22pt; display: block; color: <?= $ink ?>; }
.contact { background: <?= $ink ?>; color: #fbfaf6; padding: 10pt 12pt; margin-top: 16pt; }
.contact a { color: #fbfaf6; text-decoration: none; }
.footer { position: fixed; bottom: -12mm; left: 0; right: 0; font-size: 7pt; color: #8b857a; text-align: center; }
.bar { height: 4pt; background: <?= $ocre ?>; margin-bottom: 12pt; }
</style></head>
<body>
<div class="footer"><?= e(setting('site.name')) ?> — <?= e($domain) ?> — dossier de presse généré le <?= e(fr_date(date('Y-m-d'))) ?></div>
<div class="bar"></div>
<table class="cover" width="100%"><tr>
  <td>
    <p class="kicker">Dossier de presse</p>
    <h1><?= e(setting('site.name')) ?></h1>
    <p class="full"><?= e(setting('site.full_name')) ?> — <?= e(t('home.kicker')) ?></p>
    <table class="figs"><tr>
      <td><strong><?= (int) $fig['editions'] ?></strong><?= e(t('home.figures_author_1', ['editions' => $fig['editions'], 'debut' => $fig['debut'], 'fin' => $fig['fin']])) ?></td>
      <td><strong><?= (int) $fig['titres'] ?></strong><?= e(t('home.figures_author_2')) ?></td>
    </tr></table>
    <p><?= e(t('home.tagline')) ?></p>
  </td>
  <?php if ($photo && is_file($photo)): ?><td width="66mm" align="right"><img class="photo" src="<?= e($photo) ?>" style="object-fit: cover"></td><?php endif; ?>
</tr></table>

<h2>Biographie</h2>
<div class="bio"><?= md(t('parcours.bio')) ?></div>

<?php if ($distinctions): ?>
<h2>Distinctions</h2>
<table class="list"><?php foreach ($distinctions as $d): ?><tr><td class="y"><?= e($d['year']) ?></td><td><?= $d['status'] ? '<b>' . e($d['status']) . '</b> — ' : '' ?><?= e($d['label']) ?><?= $d['source'] ? ' <span class="muted">(' . e($d['source']) . ')</span>' : '' ?></td></tr><?php endforeach; ?></table>
<?php endif; ?>

<h2>Bibliographie</h2>
<table class="list"><?php foreach ($books as $b): ?><tr><td class="y"><?= (int) $b['year'] ?></td><td><b><?= e($b['title']) ?></b><?= (int) $b['edition_num'] > 1 ? ' (' . e(book_edition_label($b)) . ')' : '' ?> — <span class="muted"><?= e(trim(($b['genre_label'] ?: $b['genre_name']) . ', ' . $b['publisher_name'] . ($b['place'] ? ', ' . $b['place'] : ''), ', ')) ?><?= $b['is_collective'] ? ', ' . e(t('ui.collective')) : '' ?></span></td></tr><?php endforeach; ?></table>

<?php if ($press): ?>
<h2>Presse et entretiens</h2>
<table class="list"><?php foreach ($press as $p): ?><tr><td class="y" style="width:30mm"><?= e($p['date_label']) ?></td><td><b><?= e($p['media_name']) ?></b> — <?= e(quoted($p['title'])) ?><?= $p['url'] ? '<br><span class="muted">' . e($p['url']) . '</span>' : '' ?></td></tr><?php endforeach; ?></table>
<?php endif; ?>

<div class="contact">
  <b>Contact presse</b><br>
  <?php if (wa_number()): ?>WhatsApp : <?= e(setting('whatsapp.display', '+' . wa_number())) ?><br><?php endif; ?>
  <?php if ($email): ?>E-mail : <?= e($email) ?><br><?php endif; ?>
  Site : <?= e($domain) ?>
  <?php foreach ($socials as $s): if ($s['network'] === 'email') continue; ?><br><?= e(social_label($s)) ?> : <?= e($s['url']) ?><?php endforeach; ?>
</div>
</body></html>
