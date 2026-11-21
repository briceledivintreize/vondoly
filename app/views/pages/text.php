<?php /** Page de texte (mentions légales, confidentialité). Variables : $title, $body */ ?>
<?php partial('page-head', ['title' => $title]); ?>
<section class="textpage"><div class="wrap wrap--narrow prose"><?= md($body) ?></div></section>
