<?php
// 10 demandes fictives pour découvrir l'espace admin. Marquées « démo » et supprimables en un clic.
require_once __DIR__ . '/../inc/functions.php';

function demo_load(): void {
  $pdo = db();
  $now = time();
  $rows = [
    ['Sylvie Morin', '06 41 22 85 10', 'sylvie.morin@exemple.fr', '35000', '12 rue des Lilas', 'urgence', 1, 'ardoises', '50-100', 'maison', 'Fuite au plafond de la chambre depuis la tempête de cette nuit. Seau en place.', 'matin', 'Nouvelle', 2, 3],
    ['Garage Lebreton SARL', '02 99 54 12 33', 'contact@garage-lebreton.exemple.fr', '35510', 'ZA des Rives, lot 4', 'couverture', 0, 'bac-acier', '>200', 'pro', 'Remplacement de la couverture fibrociment de l’atelier (600 m²), à prévoir en 2 tranches.', 'apres-midi', 'Rappelé', 0, 26],
    ['Marc Delaunay', '07 63 18 40 92', '', '35200', '', 'demoussage', 0, 'tuiles', '100-200', 'maison', 'Toit très moussu côté nord, gouttières bouchées.', 'soir', 'Devis envoyé', 1, 72],
    ['Hélène Caradec', '06 12 77 03 58', 'h.caradec@exemple.fr', '35700', '3 impasse du Moulin', 'zinguerie', 0, 'ardoises', '50-100', 'maison', 'Gouttière qui déborde au-dessus de la porte d’entrée.', 'indifferent', 'Signé', 0, 240],
    ['Thierry Bouvier', '06 98 31 27 44', 'thierry.bouvier@exemple.fr', '22100', '8 route de Dinan', 'urgence', 1, 'tuiles', '<50', 'maison', 'Tuiles arrachées sur l’appentis, de l’eau rentre dans le garage.', 'midi', 'Rappelé', 1, 30],
    ['Camille Rouxel', '07 45 66 12 09', 'camille.rouxel@exemple.fr', '35135', '21 allée des Peupliers', 'isolation', 0, 'inconnu', 'inconnu', 'maison', 'Combles perdus non isolés, maison de 1975. Quelles aides possibles ?', 'matin', 'Nouvelle', 0, 5],
    ['Copropriété Les Mouettes', '02 99 22 48 71', 'syndic@les-mouettes.exemple.fr', '35400', '5 boulevard de la Mer', 'couverture', 0, 'zinc', '>200', 'pro', 'Demande du syndic pour devis de réfection de la toiture zinc, AG prévue en mars.', 'apres-midi', 'Perdu', 0, 400],
    ['Julien Ferrand', '06 23 54 87 61', 'julien.ferrand@exemple.fr', '35830', '14 rue du Stade', 'fenetres-de-toit', 0, 'ardoises', '50-100', 'maison', 'Ajout de 2 fenêtres de toit pour aménager les combles en chambre.', 'soir', 'Devis envoyé', 2, 120],
    ['Nathalie Guéguen', '06 77 40 19 23', '', '35740', '', 'zinguerie', 0, 'tuiles', '100-200', 'maison', '', 'indifferent', 'Nouvelle', 0, 9],
    ['Boulangerie du Centre', '02 99 31 05 66', 'boulangerie.centre@exemple.fr', '35000', '2 place de la Mairie', 'demoussage', 0, 'tuiles', '100-200', 'pro', 'Entretien annuel de la toiture et des chéneaux.', 'matin', 'Signé', 0, 500],
  ];
  $st = $pdo->prepare('INSERT INTO demandes (cree_le, statut, urgence, travaux, toiture, surface, batiment, description, nom, telephone, email, code_postal, adresse, creneau, hors_zone, consentement, demo, mail_envoye)
    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,1,1)');
  foreach ($rows as $r) {
    [$nom, $tel, $mail, $cp, $adr, $trav, $urg, $toit, $surf, $bat, $desc, $cren, $statut, $nbPhotos, $hoursAgo] = $r;
    $date = date('Y-m-d H:i:s', $now - $hoursAgo * 3600);
    $st->execute([$date, $statut, $urg, $trav, $toit, $surf, $bat, $desc, $nom, $tel, $mail, $cp, $adr, $cren, in_zone($cp) === false ? 1 : 0, 'Accepté le ' . date('d/m/Y à H:i', strtotime($date))]);
    $id = (int)$pdo->lastInsertId();
    if ($nbPhotos && function_exists('imagecreatetruecolor')) {
      $dir = DATA_DIR . "/uploads/$id"; @mkdir($dir, 0750, true); $files = [];
      for ($i = 1; $i <= $nbPhotos; $i++) {
        $im = imagecreatetruecolor(800, 600);
        imagefill($im, 0, 0, imagecolorallocate($im, 70 + 20 * $i, 90, 110));
        $w = imagecolorallocate($im, 255, 255, 255);
        imagestring($im, 5, 300, 280, "PHOTO DEMO $i - demande $id", $w);
        imagejpeg($im, "$dir/photo-$i.jpg", 80); imagedestroy($im);
        $files[] = "photo-$i.jpg";
      }
      $pdo->prepare('UPDATE demandes SET photos = ? WHERE id = ?')->execute([json_encode($files), $id]);
    }
  }
}

function demo_clear(): void {
  $pdo = db();
  foreach ($pdo->query('SELECT id FROM demandes WHERE demo = 1')->fetchAll(PDO::FETCH_COLUMN) as $id) {
    foreach (glob(DATA_DIR . "/uploads/$id/*") ?: [] as $f) unlink($f);
    @rmdir(DATA_DIR . "/uploads/$id");
  }
  $pdo->exec('DELETE FROM demandes WHERE demo = 1');
}
