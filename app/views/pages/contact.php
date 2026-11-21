<?php
/** Contact. Variables : $errors, $old, $socials */
partial('page-head', ['title' => t('contact.title'), 'intro' => t('contact.intro')]);
$email = setting('site.email');
$err = fn($k) => isset($errors[$k]) ? ' aria-invalid="true"' : '';
?>
<section class="contact">
  <div class="wrap contact__grid">
    <div class="contact__direct">
      <h2 class="kicker"><?= te('contact.direct_title') ?></h2>
      <ul class="contact__channels" data-stagger>
        <?php if (wa_number()): ?>
        <li><a class="channel channel--wa" href="<?= e(wa_link(t('contact.whatsapp_msg'))) ?>" target="_blank" rel="noopener" data-magnetic>
          <?= icon('whatsapp') ?><span><strong><?= te('contact.whatsapp') ?></strong><small><?= e(setting('whatsapp.display', '+' . wa_number())) ?></small></span></a></li>
        <?php endif; ?>
        <?php if ($email): ?>
        <li><a class="channel" href="mailto:<?= e($email) ?>"><?= icon('email') ?><span><strong>E-mail</strong><small><?= e($email) ?></small></span></a></li>
        <?php endif; ?>
      </ul>
      <?php $nets = array_filter($socials, fn($s) => $s['network'] !== 'email'); if ($nets): ?>
      <h2 class="kicker"><?= te('contact.social_title') ?></h2>
      <?= social_icons(false, false, 'contact__socials', ['email']) ?>
      <?php endif; ?>
      <?php if (module_on('reconnaissance')): ?>
      <a class="btn btn--ghost btn--sm" href="<?= e(url('/dossier-de-presse.pdf')) ?>" target="_blank" data-track="presskit" data-ref="contact"><?= icon('download') ?><span><?= te('ui.press_kit') ?></span></a>
      <?php endif; ?>
    </div>

    <form class="contact__form" method="post" action="<?= e(url('/contact')) ?>#formulaire" id="formulaire" novalidate data-reveal>
      <h2><?= te('contact.form_title') ?></h2>
      <?php foreach (flashes() as [$type, $msg]): ?><p class="alert alert--<?= e($type) ?>" role="status"><?= e($msg) ?></p><?php endforeach; ?>
      <?php if ($errors): ?><p class="alert alert--error" role="alert"><?= te('contact.error') ?></p><?php endif; ?>
      <?= csrf_field() ?>
      <div class="hp" aria-hidden="true"><label>Site web <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <div class="field-row">
        <label class="field"><span><?= te('contact.name') ?> *</span><input name="name" required maxlength="120" autocomplete="name" value="<?= e($old['name'] ?? '') ?>"<?= $err('name') ?>></label>
        <label class="field"><span><?= te('contact.email') ?> *</span><input name="email" type="email" required maxlength="160" autocomplete="email" value="<?= e($old['email'] ?? '') ?>"<?= $err('email') ?>></label>
      </div>
      <div class="field-row">
        <label class="field"><span><?= te('contact.phone') ?></span><input name="phone" type="tel" maxlength="40" autocomplete="tel" value="<?= e($old['phone'] ?? '') ?>"></label>
        <label class="field"><span><?= te('contact.subject') ?></span><input name="subject" maxlength="200" value="<?= e($old['subject'] ?? '') ?>"></label>
      </div>
      <label class="field"><span><?= te('contact.message') ?> *</span><textarea name="message" rows="6" required minlength="10" maxlength="5000"<?= $err('message') ?>><?= e($old['message'] ?? '') ?></textarea></label>
      <label class="check"><input type="checkbox" name="consent" value="1" required <?= !empty($old['consent']) ? 'checked' : '' ?><?= $err('consent') ?>><span><?= te('contact.consent') ?> <a href="<?= e(url('/confidentialite')) ?>"><?= te('footer.privacy') ?></a></span></label>
      <button class="btn btn--red btn--lg" type="submit" data-magnetic><span><?= te('contact.send') ?></span><?= icon('arrow') ?></button>
    </form>
  </div>
</section>
