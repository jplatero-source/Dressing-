<?php
// Fonctions communes à tout le site.

declare(strict_types=1);

const ROOT = __DIR__ . '/..';
const DATA_DIR = ROOT . '/data';
const MAX_PHOTOS = 5;
const MAX_PHOTO_BYTES = 10 * 1024 * 1024;

function cfg(?string $key = null) {
  static $c = null;
  if ($c === null) $c = require ROOT . '/config.php';
  return $key === null ? $c : ($c[$key] ?? '');
}

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// En mode aperçu statique (génération HTML), les liens .php deviennent .html
function url(string $path): string {
  if (defined('STATIC_EXPORT')) $path = preg_replace('/\.php(\?|#|$)/', '.html$1', $path);
  return $path;
}

function tel_href(): string { return 'tel:' . preg_replace('/[^0-9+]/', '', cfg('telephone_lien')); }

/** Le code postal est-il dans la zone habituelle ? (null si non renseigné) */
function in_zone(string $cp): ?bool {
  $cp = preg_replace('/\D/', '', $cp);
  if ($cp === '') return null;
  $zone = cfg('zone_cp');
  if (!$zone) return true;
  foreach ($zone as $p) if (str_starts_with($cp, preg_replace('/\D/', '', (string)$p))) return true;
  return false;
}

function services(): array { return require __DIR__ . '/services.php'; }
function service(string $slug): ?array { foreach (services() as $s) if ($s['slug'] === $slug) return $s; return null; }
function realisations(): array { return require __DIR__ . '/realisations.php'; }

/** Photo réelle si le fichier existe, sinon emplacement clairement marqué. */
function photo(string $file, string $alt, string $class = 'ph'): string {
  if ($file && is_file(ROOT . '/assets/photos/' . $file)) {
    return '<img class="' . e($class) . '" src="assets/photos/' . e($file) . '" alt="' . e($alt) . '" loading="lazy" decoding="async">';
  }
  return '<div class="' . e($class) . ' placeholder" role="img" aria-label="' . e($alt) . ' (photo à ajouter)"><span>[PHOTO CHANTIER À AJOUTER]</span><small>' . e($alt) . '<br>assets/photos/' . e($file) . '</small></div>';
}

/* ---------- base de données des demandes (SQLite) ---------- */
function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0750, true);
  $pdo = new PDO('sqlite:' . DATA_DIR . '/demandes.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
  $pdo->exec('CREATE TABLE IF NOT EXISTS demandes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cree_le TEXT NOT NULL,
    statut TEXT NOT NULL DEFAULT "Nouvelle",
    urgence INTEGER NOT NULL DEFAULT 0,
    travaux TEXT, toiture TEXT, surface TEXT, batiment TEXT, description TEXT,
    nom TEXT, telephone TEXT, email TEXT, code_postal TEXT, adresse TEXT, creneau TEXT,
    hors_zone INTEGER NOT NULL DEFAULT 0,
    photos TEXT NOT NULL DEFAULT "[]",
    consentement TEXT, ip TEXT, demo INTEGER NOT NULL DEFAULT 0,
    mail_envoye INTEGER NOT NULL DEFAULT 0
  )');
  return $pdo;
}

const STATUTS = ['Nouvelle', 'Rappelé', 'Devis envoyé', 'Signé', 'Perdu'];
const TOITURES = ['tuiles' => 'Tuiles', 'ardoises' => 'Ardoises', 'zinc' => 'Zinc', 'bac-acier' => 'Bac acier', 'inconnu' => 'Je ne sais pas'];
const BATIMENTS = ['maison' => 'Maison', 'pro' => 'Bâtiment professionnel'];
const CRENEAUX = ['matin' => 'Le matin (8h – 12h)', 'midi' => 'Entre 12h et 14h', 'apres-midi' => 'L’après-midi (14h – 18h)', 'soir' => 'En fin de journée (après 18h)', 'indifferent' => 'Peu importe'];
const SURFACES = ['<50' => 'Moins de 50 m²', '50-100' => '50 à 100 m²', '100-200' => '100 à 200 m²', '>200' => 'Plus de 200 m²', 'inconnu' => 'Je ne sais pas'];

function label(array $list, ?string $k): string { return $list[$k] ?? ($k ?: '—'); }
function travaux_label(?string $slug): string { $s = $slug ? service($slug) : null; return $s ? $s['titre'] : ($slug === 'autre' ? 'Autre / je ne sais pas' : '—'); }
