<?php
/** En-tête de page intérieure. Variables : $title, $intro, $kicker (facultatif), $num */
?>
<header class="page-head">
  <svg class="page-head__curve" viewBox="0 0 1200 300" preserveAspectRatio="none" aria-hidden="true"><path data-draw d="M-20 240 C 260 60, 520 320, 820 150 S 1180 40, 1240 90"/></svg>
  <div class="wrap">
    <?php if (!empty($kicker)): ?><p class="kicker" data-fade><?= e($kicker) ?></p><?php endif; ?>
    <h1 class="page-head__title" data-split-chars><?= e($title) ?></h1>
    <?php if (!empty($intro)): ?><p class="page-head__intro" data-fade><?= e($intro) ?></p><?php endif; ?>
  </div>
</header>
