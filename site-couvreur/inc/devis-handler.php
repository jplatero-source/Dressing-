<?php
// Traitement d'une demande de devis : validation, enregistrement, photos, e-mail.

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/mail.php';

function devis_values(array $src): array {
  $v = fn($k) => trim((string)($src[$k] ?? ''));
  return [
    'travaux' => $v('travaux'), 'urgence' => $v('urgence') === 'oui' ? 'oui' : 'non',
    'toiture' => $v('toiture'), 'surface' => $v('surface'), 'batiment' => $v('batiment'),
    'description' => mb_substr($v('description'), 0, 3000),
    'nom' => mb_substr($v('nom'), 0, 120), 'telephone' => mb_substr($v('telephone'), 0, 30), 'email' => mb_substr($v('email'), 0, 160),
    'code_postal' => mb_substr($v('code_postal'), 0, 10), 'adresse' => mb_substr($v('adresse'), 0, 250), 'creneau' => $v('creneau'),
    'rgpd' => isset($src['rgpd']) && $src['rgpd'] !== '' ? '1' : '',
  ];
}

function normalize_phone(string $t): string { return preg_replace('/[^0-9+]/', '', $t); }

/** Liste des fichiers photo envoyés (les champs vides sont ignorés). */
function uploaded_photos(): array {
  $f = $_FILES['photos'] ?? null;
  if (!$f || !is_array($f['name'])) return [];
  $out = [];
  foreach ($f['name'] as $i => $name) {
    if ($f['error'][$i] === UPLOAD_ERR_NO_FILE || $name === '') continue;
    $out[] = ['name' => $name, 'tmp' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
  }
  return $out;
}

function validate_devis(array $d, array $photos): array {
  $err = [];
  if ($d['travaux'] === '') $err['travaux'] = 'Choisissez le type de travaux.';
  if ($d['nom'] === '') $err['nom'] = 'Indiquez votre nom.';
  $tel = normalize_phone($d['telephone']);
  if ($tel === '') $err['telephone'] = 'Indiquez votre numéro de téléphone pour que nous puissions vous rappeler.';
  elseif (!preg_match('/^(0[1-9]\d{8}|\+33[1-9]\d{8}|\+\d{8,15})$/', $tel)) $err['telephone'] = 'Ce numéro de téléphone semble incomplet. Exemple : 06 12 34 56 78.';
  if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $err['email'] = 'Cette adresse e-mail n’est pas valide.';
  if (!preg_match('/^\d{5}$/', preg_replace('/\s/', '', $d['code_postal']))) $err['code_postal'] = 'Indiquez le code postal du chantier (5 chiffres).';
  if ($d['rgpd'] !== '1') $err['rgpd'] = 'Cochez la case pour accepter que nous utilisions vos coordonnées afin de vous recontacter.';
  if (count($photos) > MAX_PHOTOS) $err['photos'] = 'Vous pouvez envoyer 5 photos au maximum. Retirez-en ' . (count($photos) - MAX_PHOTOS) . '.';
  else foreach ($photos as $p) {
    if (in_array($p['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) || $p['size'] > MAX_PHOTO_BYTES) { $err['photos'] = 'La photo « ' . $p['name'] . ' » est trop lourde (10 Mo maximum).'; break; }
    if ($p['error'] !== UPLOAD_ERR_OK) { $err['photos'] = 'La photo « ' . $p['name'] . ' » n’a pas pu être reçue. Réessayez.'; break; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($p['tmp']);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'], true)) { $err['photos'] = 'Le fichier « ' . $p['name'] . ' » n’est pas une photo (JPEG, PNG, WebP ou HEIC).'; break; }
  }
  return $err;
}

/** Traite le POST. Renvoie ['ok'=>true,'urgent'=>bool] ou ['errors'=>[...], 'values'=>[...]] */
function handle_devis(): array {
  $d = devis_values($_POST);
  // Pièges à robots : champ caché rempli, ou envoi en moins de 3 secondes.
  if (!empty($_POST['site_web']) || (isset($_POST['t']) && time() - (int)$_POST['t'] < 3)) return ['ok' => true, 'urgent' => false];
  $photos = uploaded_photos();
  $err = validate_devis($d, $photos);
  if ($err) return ['errors' => $err, 'values' => $d];

  $cp = preg_replace('/\s/', '', $d['code_postal']);
  $row = [
    'cree_le' => date('Y-m-d H:i:s'), 'urgence' => $d['urgence'] === 'oui' ? 1 : 0,
    'travaux' => $d['travaux'], 'toiture' => $d['toiture'], 'surface' => $d['surface'], 'batiment' => $d['batiment'], 'description' => $d['description'],
    'nom' => $d['nom'], 'telephone' => $d['telephone'], 'email' => $d['email'], 'code_postal' => $cp, 'adresse' => $d['adresse'], 'creneau' => $d['creneau'],
    'hors_zone' => in_zone($cp) === false ? 1 : 0,
    'consentement' => 'Accepté le ' . date('d/m/Y à H:i'), 'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
  ];
  $pdo = db();
  $cols = array_keys($row);
  $st = $pdo->prepare('INSERT INTO demandes (' . implode(',', $cols) . ') VALUES (:' . implode(',:', $cols) . ')');
  $st->execute($row);
  $id = (int)$pdo->lastInsertId();

  $saved = [];
  if ($photos) {
    $dir = DATA_DIR . "/uploads/$id";
    mkdir($dir, 0750, true);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic', 'image/heif' => 'heif'];
    foreach ($photos as $n => $p) {
      $mime = (new finfo(FILEINFO_MIME_TYPE))->file($p['tmp']);
      $file = sprintf('photo-%d.%s', $n + 1, $ext[$mime]);
      if (move_uploaded_file($p['tmp'], "$dir/$file") || (PHP_SAPI === 'cli' && rename($p['tmp'], "$dir/$file"))) $saved[] = $file;
    }
    $pdo->prepare('UPDATE demandes SET photos = ? WHERE id = ?')->execute([json_encode($saved), $id]);
  }
  $row['id'] = $id; $row['photos'] = $saved;
  $sent = send_demande_mail($row);
  $pdo->prepare('UPDATE demandes SET mail_envoye = ? WHERE id = ?')->execute([$sent ? 1 : 0, $id]);
  return ['ok' => true, 'urgent' => (bool)$row['urgence']];
}
