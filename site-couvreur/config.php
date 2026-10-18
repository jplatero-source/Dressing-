<?php
/*
 * ============================================================
 *  CONFIGURATION DU SITE — c'est le SEUL fichier à modifier.
 *  Remplacez chaque valeur entre crochets par vos informations.
 * ============================================================
 */
return [
  // Entreprise
  'nom'            => '[NOM ENTREPRISE]',
  'ville'          => '[VILLE]',
  'adresse'        => '[ADRESSE DE L’ENTREPRISE]',
  'code_postal'    => '[CODE POSTAL]',
  'annees'         => '[ANNÉES D’EXPÉRIENCE]',          // ex : 15
  'certifications' => ['[RGE]', '[QUALIBAT…]'],         // ex : ['RGE', 'Qualibat 3111']
  'assureur'       => '[ASSUREUR]',                     // assureur de la garantie décennale
  'numero_police'  => '[N° DE POLICE]',
  'siret'          => '[SIRET]',
  'dirigeant'      => '[NOM DU DIRIGEANT]',
  'horaires'       => 'Du lundi au vendredi, 8h – 18h',

  // Téléphone : affichage et numéro international pour le bouton « Appeler »
  'telephone'      => '[NUMÉRO]',                       // ex : 06 12 34 56 78
  'telephone_lien' => '+33000000000',                   // ex : +33612345678

  // Zone d'intervention
  'zone_texte'     => '[VILLES / RAYON EN KM]',         // ex : Rennes et 40 km autour
  // Codes postaux acceptés sans avertissement : codes complets ou débuts de code.
  // ex : ['35', '22100', '56'] — laissez [] pour ne jamais afficher d'avertissement.
  'zone_cp'        => ['35'],

  // E-mails
  'email_contact'    => 'contact@exemple.fr',           // adresse publique affichée sur le site
  'email_reception'  => 'contact@exemple.fr',           // où arrivent les demandes de devis
  'email_expediteur' => 'site@exemple.fr',              // adresse de votre domaine (sinon risque de spam)

  // Adresse du site, sans « / » final (sert au référencement et aux liens des e-mails)
  'site_url'       => 'https://www.exemple.fr',

  // Lien vers votre fiche Google (avis). Laissez vide tant que vous n'en avez pas.
  'google_avis_url' => '',

  // Hébergeur du site (obligatoire dans les mentions légales)
  'hebergeur'      => '[NOM, ADRESSE ET TÉLÉPHONE DE L’HÉBERGEUR]',

  // Code demandé UNE SEULE FOIS pour créer le mot de passe de l'espace admin.
  // Changez-le avant la mise en ligne.
  'code_installation' => 'CHANGEZ-MOI-2026',
];
