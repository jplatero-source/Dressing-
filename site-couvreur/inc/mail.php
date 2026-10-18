<?php
// E-mail envoyé à l'artisan pour chaque demande, photos en pièces jointes affichées dans le message.

require_once __DIR__ . '/functions.php';

function mime_header(string $s): string { return '=?UTF-8?B?' . base64_encode($s) . '?='; }

function demande_subject(array $r): string {
  $who = $r['nom'] . ' (' . $r['code_postal'] . ')';
  return $r['urgence'] ? 'URGENT – Fuite / urgence – ' . $who : 'Demande de devis – ' . travaux_label($r['travaux']) . ' – ' . $who;
}

function send_demande_mail(array $r): bool {
  $c = cfg();
  $boundary = 'b' . bin2hex(random_bytes(12));
  $related = 'r' . bin2hex(random_bytes(12));
  $rows = [
    'Urgence (fuite)' => $r['urgence'] ? 'OUI – à rappeler dans la journée' : 'Non',
    'Travaux' => travaux_label($r['travaux']),
    'Type de toit' => label(TOITURES, $r['toiture']), 'Surface' => label(SURFACES, $r['surface']), 'Bâtiment' => label(BATIMENTS, $r['batiment']),
    'Description' => $r['description'] ?: '—',
    'Nom' => $r['nom'], 'Téléphone' => $r['telephone'], 'E-mail' => $r['email'] ?: '—',
    'Code postal' => $r['code_postal'] . ($r['hors_zone'] ? ' (hors zone habituelle)' : ''), 'Adresse du chantier' => $r['adresse'] ?: '—',
    'Créneau de rappel' => label(CRENEAUX, $r['creneau']), 'Consentement RGPD' => $r['consentement'],
  ];
  $html = '<div style="font-family:Arial,sans-serif;font-size:15px;color:#1f2a33">';
  if ($r['urgence']) $html .= '<p style="background:#b3261e;color:#fff;padding:10px 14px;border-radius:6px;font-weight:bold">URGENCE : fuite signalée, à rappeler dans la journée.</p>';
  $html .= '<h2 style="margin:0 0 10px">Nouvelle demande de devis n° ' . (int)$r['id'] . '</h2><table cellpadding="6" style="border-collapse:collapse">';
  foreach ($rows as $k => $v) $html .= '<tr><td style="color:#5b6670;vertical-align:top;white-space:nowrap">' . e($k) . '</td><td><b>' . nl2br(e($v)) . '</b></td></tr>';
  $html .= '</table>';
  $tel = preg_replace('/[^0-9+]/', '', $r['telephone']);
  $html .= '<p><a href="tel:' . e($tel) . '" style="background:#1f2a33;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none">Appeler ' . e($r['telephone']) . '</a></p>';
  $parts = [];
  if ($r['photos']) {
    $html .= '<h3>Photos (' . count($r['photos']) . ')</h3>';
    foreach ($r['photos'] as $i => $file) {
      $path = DATA_DIR . "/uploads/{$r['id']}/$file";
      if (!is_file($path)) continue;
      $cid = "photo$i@devis";
      $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
      $html .= '<p><img src="cid:' . $cid . '" alt="Photo ' . ($i + 1) . '" style="max-width:560px;width:100%;border-radius:6px"></p>';
      $parts[] = "--$related\r\nContent-Type: $mime; name=\"$file\"\r\nContent-Transfer-Encoding: base64\r\nContent-ID: <$cid>\r\nContent-Disposition: inline; filename=\"$file\"\r\n\r\n" . chunk_split(base64_encode(file_get_contents($path)));
    }
  }
  $html .= '<p style="color:#5b6670">Retrouvez et suivez cette demande dans votre espace admin : <a href="' . e(rtrim($c['site_url'], '/')) . '/admin/?id=' . (int)$r['id'] . '">' . e(rtrim($c['site_url'], '/')) . '/admin/</a></p></div>';
  $text = strip_tags(str_replace(['</tr>', '<br />', '</p>'], "\n", $html));

  $alt = 'a' . bin2hex(random_bytes(12));
  $body = "--$related\r\nContent-Type: multipart/alternative; boundary=\"$alt\"\r\n\r\n"
    . "--$alt\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($text))
    . "--$alt\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n" . chunk_split(base64_encode($html))
    . "--$alt--\r\n" . implode('', $parts) . "--$related--\r\n";
  $headers = [
    'MIME-Version: 1.0',
    "Content-Type: multipart/related; boundary=\"$related\"",
    'From: ' . mime_header($c['nom'] . ' – site web') . ' <' . $c['email_expediteur'] . '>',
    'X-Mailer: site-couvreur',
  ];
  if ($r['email'] && filter_var($r['email'], FILTER_VALIDATE_EMAIL)) $headers[] = 'Reply-To: ' . mime_header($r['nom']) . ' <' . $r['email'] . '>';
  if ($r['urgence']) { $headers[] = 'X-Priority: 1 (Highest)'; $headers[] = 'Importance: High'; }
  return @mail($c['email_reception'], mime_header(demande_subject($r)), $body, implode("\r\n", $headers));
}
