<?php
// Sert une photo de demande, uniquement à l'admin connecté.
require __DIR__ . '/auth.php';
require_admin();
$id = (int)($_GET['id'] ?? 0); $f = basename((string)($_GET['f'] ?? ''));
$path = DATA_DIR . "/uploads/$id/$f";
if (!$id || !preg_match('/^photo-\d+\.(jpg|png|webp|heic|heif)$/', $f) || !is_file($path)) { http_response_code(404); exit('Photo introuvable'); }
header('Content-Type: ' . (new finfo(FILEINFO_MIME_TYPE))->file($path));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
readfile($path);
