# Échéancier fiscal : version autonome (sans Claude)

C'est la même appli que la version Claude, mais hébergée par le cabinet. Les collaborateurs l'ouvrent dans leur navigateur avec une adresse du type `https://echeances.votre-cabinet.fr`, et se connectent avec un mot de passe commun. Tout ce que l'un modifie est enregistré sur le serveur et apparaît chez les autres en quelques secondes.

Il faut un hébergement PHP 8.1 ou plus, avec SQLite, ce qui est le cas par défaut chez OVH, o2switch, IONOS ou Hostinger. Il n'y a pas de base de données à créer.

## 1. Mettre en ligne (20 minutes, une seule fois)

1. **Hébergement.** Choisissez une offre mutualisée en France, par exemple o2switch ou OVH Perso (quelques euros par mois). Les données des clients y restent en France. Activez le certificat SSL gratuit (Let's Encrypt) dans le panneau de l'hébergeur.
2. **Réglages.** Ouvrez `config.php`. Mettez le nom du cabinet et **changez `code_installation`**.
3. **Envoi des fichiers.** Envoyez tout le contenu de ce dossier dans `www` ou `public_html`, ou dans un sous-dossier comme `www/echeances`. Passez par le gestionnaire de fichiers de l'hébergeur ou par FileZilla.
4. **Dossier `data/`.** Vérifiez que le site peut y écrire (droits 750 ou 755). C'est là que sont rangées les données. Son fichier `.htaccess` en bloque l'accès depuis Internet.
5. **HTTPS.** Dans `.htaccess`, retirez les `#` devant les 3 lignes « HTTPS obligatoire ».
6. **Mot de passe du cabinet.** Ouvrez l'adresse du site, saisissez le code d'installation et choisissez le mot de passe (10 caractères minimum).
7. **Chargement des dossiers.** Dans l'onglet **Dossiers**, cliquez sur « Importer » et choisissez votre fichier « Suivi échéance fiscale ». Comptez une vingtaine de secondes pour environ 1 000 dossiers.

## 2. Donner l'accès aux collaborateurs

Envoyez-leur l'adresse et le mot de passe. Chacun ouvre l'adresse, se connecte, puis choisit son nom dans « Je suis ». Ils restent connectés 7 jours sur leur ordinateur ; vous pouvez changer cette durée dans `config.php`.

Pour couper l'accès à quelqu'un, changez le mot de passe : supprimez `data/acces.json` par FTP, refaites l'étape 6, puis communiquez le nouveau mot de passe aux autres. Tout le monde est déconnecté, mais aucune donnée n'est perdue.

## 3. Sauvegardes

- Toutes les données tiennent dans un seul fichier, `data/echeancier.sqlite`. Téléchargez-le par FTP de temps en temps : c'est une sauvegarde complète.
- Le bouton « Exporter en Excel » de l'appli fait une copie lisible.
- La plupart des hébergeurs sauvegardent aussi le site chaque jour.

## Fichiers

| Fichier | Rôle |
|---|---|
| `config.php` | nom du cabinet, code d'installation, durée de connexion (le seul fichier à modifier) |
| `index.php` | page de connexion, puis l'appli |
| `api.php` | lecture et enregistrement des données partagées |
| `inc.php` | fonctions communes (mot de passe, base) |
| `app/app.html` | l'appli, identique à la version Claude |
| `app/partage.js` | branche l'appli sur `api.php` et synchronise toutes les 5 secondes |
| `app/xlsx.full.min.js` | lecture et écriture des fichiers Excel, sans service externe |
| `data/` | la base `echeancier.sqlite` et le mot de passe haché (créés automatiquement, protégés) |
