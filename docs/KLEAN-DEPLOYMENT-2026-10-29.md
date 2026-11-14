# KLEAN — Carte de déploiement RAMASSEDEBRIS.CA — 2026-10-29

Note de travail KLEAN, docs seulement. Aucun code fonctionnel modifié, aucun déploiement
en production, aucun changement DNS, aucun secret dans cette note.

## Vérifié 2026-10-29
- Main : 197e82a (2026-10-16, plugin v0.5.2), inchangée. PR ouvertes : 0.
  Issues ouvertes : #3 P1 « Brancher et vérifier le déploiement SSH isolé »,
  #2 P0 « Valider la préproduction et la sécurité du formulaire WordPress »,
  #1 P0 « Récupérer les sources réelles WordPress et Operations Center » (toutes du
  2026-10-14).
- Contenu réel : PAS un site complet. Plugin WordPress autonome v0.5.2
  (`plugin/ramassedebris-service`, shortcode [ramassedebris_demande]) + docs, scripts,
  tests. README : le code de l'installation WordPress/WooCommerce existante n'est pas
  dans ce dépôt ; le plugin ne remplace pas le site existant. Aucun Dockerfile,
  vercel.json, Coolify ou app web autonome à la racine.
- Cible déclarée : l'installation WordPress historique existante (hôte et chemin non
  documentés dans le repo), via SSH, plugin seul. Workflows : `validate.yml` (tests +
  ZIP installable) et `deploy-wordpress.yml` (« Deploy isolated WordPress plugin »).
- Auto-déploiement : NON. Déploiement automatique DÉSACTIVÉ par défaut
  (docs/DEPLOYMENT.md). Activation uniquement en panneau : variable Actions
  RDB_AUTO_DEPLOY=true + secrets d'environnement production (noms seulement, aucune
  valeur ici) + règle d'approbation de l'environnement production, et seulement après
  qualification (sauvegarde, préproduction, rollback). Le workflow ne supprime aucun
  autre plugin, n'active pas le plugin automatiquement, et un transfert réussi
  (empreinte SHA-256) ne prouve ni activation ni site fonctionnel.

## Code déployable tel quel ?
- Plugin seul : OUI, transférable sur une cible WordPress qualifiée (tests CI sur
  WordPress/MySQL jetable, ZIP d'artifact, version 0.5.2).
- Site ramassedebris.ca : NON, pas déployable à partir de ce repo. Les sources
  WordPress P0 (#1) bloquent tout : sans inventaire réel (thèmes, plugins,
  WooCommerce/YITH, Operations Center), sans sauvegarde restaurable et sans
  préproduction validée (#2), aucun déploiement du site ne peut être prétendu.
  Le plugin seul ne corrige pas l'Apache 403 actuel du domaine.

## Checklist propriétaire (panneau, résumé)
1. Clore P0 #1 : inventaire réel WordPress, export sans secrets, matrice
   terminé / incomplet / bloqué dans docs/REPRISE-AGENT.md.
2. Sauvegarde restaurable fichiers + base, préproduction validée (P0 #2).
3. Tester le ZIP v0.5.2 sur un clone de préproduction ; installer/activer
   manuellement, shortcode, formulaire, alerte SMTP sans coordonnées, statuts, rollback.
4. SEULEMENT après 1–3 : confirmer hôte, port, utilisateur SSH et vrai chemin
   WordPress (sans deviner), secrets d'environnement production + approbation,
   premier déploiement manuel (P1 #3), vérification SHA-256, version 0.5.2, activation.
5. Après réussite manuelle : définir RDB_AUTO_DEPLOY=true (auto uniquement après
   succès du workflow Validate sur main).
6. Apache 403 du domaine : l'inventaire doit d'abord établir si le domaine pointe
   vers le bon hébergement ; aucun changement DNS à l'aveugle.

## État en ligne réel
- Non vérifié en ligne au-delà du constat mission 17:01–17:03 EDT (Apache 403).
  Aucun health endpoint avec SHA, aucune notification de déploiement lue pour
  ramassedebris.ca. Aucun déploiement prétendu ici.

Signé KLEAN, 2026-10-29. Copie Drive dans « 00 — Opérations TAKATAK »
(note déploiement 1/10 locactif + ramassedebris, 2026-10-29).
