<?php
require __DIR__ . '/inc/functions.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/devis-handler.php';
$c = cfg();

$errors = []; $v = devis_values($_GET);
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $res = handle_devis();
  $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
  if (!empty($res['ok'])) {
    $to = 'merci.php' . ($res['urgent'] ? '?urgence=1' : '');
    if ($wantsJson) { header('Content-Type: application/json'); echo json_encode(['ok' => true, 'redirect' => $to]); exit; }
    header('Location: ' . $to, true, 303); exit;
  }
  if ($wantsJson) { http_response_code(422); header('Content-Type: application/json'); echo json_encode(['errors' => $res['errors']], JSON_UNESCAPED_UNICODE); exit; }
  $errors = $res['errors']; $v = $res['values'];
}
$err = fn($k) => isset($errors[$k]) ? '<p class="field-error" id="err-' . $k . '">' . e($errors[$k]) . '</p>' : '<p class="field-error" id="err-' . $k . '" hidden></p>';
$chk = fn($k, $val) => ($v[$k] ?? '') === $val ? 'checked' : '';

page_start(
  "Devis gratuit couvreur à {$c['ville']} | {$c['nom']}",
  "Demandez un devis gratuit pour vos travaux de toiture à {$c['ville']} : couverture, fuite, zinguerie, démoussage, isolation. Réponse sous 48 h, dans la journée en cas d’urgence.",
  'devis.php'
);
?>
<section class="wrap page-head narrow">
  <h1>Demande de devis gratuit</h1>
  <p class="lead">4 étapes courtes, environ 2 minutes. Nous vous rappelons sous 48 h, dans la journée en cas de fuite.</p>
</section>

<section class="wrap narrow section-tight">
  <?php if ($errors): ?><div class="alert" role="alert"><b>La demande n’a pas pu être envoyée.</b> Corrigez les points signalés ci-dessous.</div><?php endif; ?>
  <form class="quote" id="quote" method="post" enctype="multipart/form-data" action="devis.php" novalidate>
    <ol class="progress" aria-label="Étapes"><li class="on">Besoin</li><li>Toit</li><li>Photos</li><li>Coordonnées</li></ol>
    <input type="hidden" name="t" value="<?= time() ?>">
    <div class="hp" aria-hidden="true"><label>Ne pas remplir <input type="text" name="site_web" tabindex="-1" autocomplete="off"></label></div>

    <fieldset class="step" data-step="1">
      <legend>1. Votre besoin</legend>
      <p class="q">Quel type de travaux ? <span class="req">obligatoire</span></p>
      <div class="options">
        <?php foreach (services() as $s): ?>
          <label class="opt"><input type="radio" name="travaux" value="<?= e($s['slug']) ?>" <?= $chk('travaux', $s['slug']) ?> required><span><?= e($s['titre']) ?></span></label>
        <?php endforeach; ?>
        <label class="opt"><input type="radio" name="travaux" value="autre" <?= $chk('travaux', 'autre') ?>><span>Autre / je ne sais pas</span></label>
      </div>
      <?= $err('travaux') ?>
      <p class="q">C’est une urgence (fuite) ?</p>
      <div class="options two">
        <label class="opt opt-urgent"><input type="radio" name="urgence" value="oui" <?= $chk('urgence', 'oui') ?>><span>Oui, il y a une fuite</span></label>
        <label class="opt"><input type="radio" name="urgence" value="non" <?= ($v['urgence'] ?? 'non') !== 'oui' ? 'checked' : '' ?>><span>Non</span></label>
      </div>
      <p class="hint urgent-hint" hidden>Pour une fuite active, le plus rapide est de nous appeler : <a href="<?= e(tel_href()) ?>"><?= e($c['telephone']) ?></a>. Vous pouvez aussi terminer ce formulaire, nous vous rappellerons dans la journée.</p>
    </fieldset>

    <fieldset class="step" data-step="2">
      <legend>2. Votre toit</legend>
      <p class="q">Type de couverture</p>
      <div class="options">
        <?php foreach (TOITURES as $k => $l): ?><label class="opt"><input type="radio" name="toiture" value="<?= $k ?>" <?= $chk('toiture', $k) ?>><span><?= e($l) ?></span></label><?php endforeach; ?>
      </div>
      <label class="q" for="surface">Surface approximative</label>
      <select id="surface" name="surface"><option value="">Choisir…</option><?php foreach (SURFACES as $k => $l): ?><option value="<?= e($k) ?>" <?= ($v['surface'] ?? '') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
      <p class="q">Il s’agit de…</p>
      <div class="options two">
        <?php foreach (BATIMENTS as $k => $l): ?><label class="opt"><input type="radio" name="batiment" value="<?= $k ?>" <?= $chk('batiment', $k) ?>><span><?= e($l) ?></span></label><?php endforeach; ?>
      </div>
    </fieldset>

    <fieldset class="step" data-step="3">
      <legend>3. Photos et description</legend>
      <p class="q">Photos du toit <span class="opt-label">facultatif, 5 maximum</span></p>
      <label class="drop" for="photos"><b>Ajouter des photos</b><span>Prenez-les depuis le sol ou une fenêtre, sans monter sur le toit.</span></label>
      <input type="file" id="photos" name="photos[]" accept="image/*" multiple class="visually-hidden">
      <div class="thumbs" id="thumbs" aria-live="polite"></div>
      <?= $err('photos') ?>
      <label class="q" for="description">Décrivez votre besoin <span class="opt-label">facultatif</span></label>
      <textarea id="description" name="description" rows="4" maxlength="3000" placeholder="Ex : tache au plafond de la chambre depuis la tempête, tuiles déplacées côté rue…"><?= e($v['description'] ?? '') ?></textarea>
    </fieldset>

    <fieldset class="step" data-step="4">
      <legend>4. Vos coordonnées</legend>
      <div class="grid2">
        <div><label class="q" for="nom">Nom <span class="req">obligatoire</span></label><input type="text" id="nom" name="nom" autocomplete="name" value="<?= e($v['nom'] ?? '') ?>" required><?= $err('nom') ?></div>
        <div><label class="q" for="telephone">Téléphone <span class="req">obligatoire</span></label><input type="tel" id="telephone" name="telephone" autocomplete="tel" inputmode="tel" value="<?= e($v['telephone'] ?? '') ?>" required><?= $err('telephone') ?></div>
        <div><label class="q" for="email">E-mail</label><input type="email" id="email" name="email" autocomplete="email" value="<?= e($v['email'] ?? '') ?>"><?= $err('email') ?></div>
        <div><label class="q" for="code_postal">Code postal du chantier <span class="req">obligatoire</span></label><input type="text" id="code_postal" name="code_postal" autocomplete="postal-code" inputmode="numeric" maxlength="5" value="<?= e($v['code_postal'] ?? '') ?>" required><?= $err('code_postal') ?>
          <p class="hint zone-hint" id="zone-hint" <?= isset($v['code_postal']) && in_zone($v['code_postal']) === false ? '' : 'hidden' ?>>Ce code postal est en dehors de notre zone habituelle (<?= e($c['zone_texte']) ?>). Vous pouvez quand même envoyer votre demande : nous vous dirons rapidement si nous pouvons intervenir.</p></div>
      </div>
      <label class="q" for="adresse">Adresse du chantier</label>
      <input type="text" id="adresse" name="adresse" autocomplete="street-address" value="<?= e($v['adresse'] ?? '') ?>">
      <label class="q" for="creneau">Quand préférez-vous être rappelé ?</label>
      <select id="creneau" name="creneau"><?php foreach (CRENEAUX as $k => $l): ?><option value="<?= e($k) ?>" <?= ($v['creneau'] ?? 'indifferent') === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
      <label class="consent"><input type="checkbox" id="rgpd" name="rgpd" value="1" <?= ($v['rgpd'] ?? '') === '1' ? 'checked' : '' ?> required>
        <span>J’accepte que <?= e($c['nom']) ?> utilise ces informations pour me recontacter au sujet de ma demande. Elles ne sont ni vendues ni transmises. <a href="<?= url('confidentialite.php') ?>" target="_blank">Politique de confidentialité</a>. <span class="req">obligatoire</span></span></label>
      <?= $err('rgpd') ?>
    </fieldset>

    <div class="form-nav">
      <button type="button" class="btn btn-outline" data-prev hidden>Retour</button>
      <button type="button" class="btn btn-primary" data-next>Continuer</button>
      <button type="submit" class="btn btn-primary btn-lg" data-submit>Envoyer ma demande</button>
    </div>
    <p class="hint center">Gratuit et sans engagement. Réponse sous 48 h.</p>
  </form>
</section>
<?php page_end();
