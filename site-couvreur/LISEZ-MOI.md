# Site du couvreur : guide de mise en ligne

Site en PHP, sans base de données à créer et sans installation à faire. Il fonctionne sur un hébergement mutualisé classique : OVH, o2switch, Hostinger, IONOS… Il faut PHP 8.1 ou plus, avec SQLite, ce qui est le cas par défaut chez ces hébergeurs.

## 1. Personnaliser (15 minutes)

1. Ouvrez **`config.php`** et remplacez chaque valeur entre crochets : nom, ville, téléphone, zone d'intervention, codes postaux de la zone, assureur, SIRET, e-mails, adresse du site. **Changez aussi `code_installation`.**
2. Déposez vos vraies photos dans **`assets/photos/`**, avec les noms listés dans `assets/photos/LISEZ-MOI.txt`. Chaque photo remplace automatiquement l'emplacement gris « [PHOTO CHANTIER À AJOUTER] ».
3. Réalisations : adaptez le type de travaux et la ville dans **`inc/realisations.php`**.
4. Textes encore à compléter (cherchez `[` dans ces fichiers) :
   - `entreprise.php` : histoire, équipe, certifications ;
   - `mentions-legales.php` : forme juridique, n° de TVA, médiateur.
5. Avis clients : collez le lien de votre fiche Google dans `google_avis_url`.

## 2. Mettre en ligne

1. **Hébergement et nom de domaine.** Prenez une offre mutualisée avec PHP. Par exemple o2switch, environ 7 €/mois, domaine compris ; ou OVH Perso, environ 4 €/mois, plus le domaine à environ 10 €/an. Activez le certificat SSL gratuit (Let's Encrypt) dans le panneau de l'hébergeur.
2. **Envoi des fichiers.** Envoyez **tout le contenu** du dossier `site-couvreur/` dans le dossier `www` ou `public_html` de l'hébergement. Utilisez le gestionnaire de fichiers du panneau ou FileZilla (FTP).
3. **Droits d'écriture.** Vérifiez que le dossier `data/` peut être modifié par le site (droits 750 ou 755). C'est là que sont rangées les demandes et les photos reçues. Son fichier `.htaccess` en bloque l'accès depuis Internet.
4. **E-mails.** Créez chez l'hébergeur l'adresse saisie dans `email_expediteur` (par exemple `site@votre-domaine.fr`). Les e-mails partent alors de votre domaine et n'atterrissent pas dans les spams.
5. **HTTPS.** Dans `.htaccess`, retirez les `#` devant les 3 lignes « HTTPS obligatoire ».
6. **Mot de passe admin.** Allez sur `https://votre-domaine.fr/admin/`, saisissez le code d'installation et choisissez votre mot de passe (10 caractères minimum). Cette étape n'a lieu qu'une fois.
7. **Google.**
   - Créez ou réclamez votre fiche Google Business Profile, avec la même adresse et le même téléphone que sur le site.
   - Déclarez `https://votre-domaine.fr/sitemap.php` dans Google Search Console.

> Si l'hébergeur limite la taille des envois, les photos trop lourdes sont refusées avec un message clair. Pour l'éviter, augmentez `upload_max_filesize` à 12M et `post_max_size` à 64M dans le panneau PHP de l'hébergeur. Le site réduit déjà les photos avant l'envoi.

## 3. Utiliser l'espace admin

- `https://votre-domaine.fr/admin/` : la liste des demandes, avec les urgences en haut et en rouge.
- Cliquez sur un nom pour voir le détail et les photos, et changez le statut dans la liste déroulante.
- « Exporter en Excel » télécharge un fichier `.xlsx` avec toutes les demandes.
- Quand la liste est vide, un bouton charge **10 demandes de démonstration**. Un autre bouton les supprime ensuite.
- Pour changer de mot de passe : supprimez `data/admin.json` par FTP, puis refaites l'étape 6.

## Fichiers

| Fichier | Rôle |
|---|---|
| `config.php` | toutes vos informations (le seul fichier à modifier) |
| `index.php`, `services.php`, `service-*.php`, `realisations.php`, `entreprise.php` | pages publiques |
| `devis.php`, `merci.php` | formulaire de devis et confirmation |
| `mentions-legales.php`, `confidentialite.php` | pages légales |
| `inc/` | textes des services, réalisations, envoi d'e-mail |
| `admin/` | espace admin protégé |
| `data/` | demandes et photos reçues (créé automatiquement, protégé) |
