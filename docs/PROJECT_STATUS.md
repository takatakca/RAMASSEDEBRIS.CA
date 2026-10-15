# PROJECT STATUS — 2026-10-15

## Source de vérité
Repository : takatakca/RAMASSEDEBRIS.CA; branche : main. Avant les changements du 14 octobre, seul README.md existait. Le code de l'installation historique WordPress/WooCommerce et de l'ancien Operations Center n'est toujours pas dans le dépôt.

## Réalisé dans le dépôt
- Extension WordPress autonome de collecte de demandes en français, affichage mobile, date souhaitée sans confirmation de réservation.
- Enregistrement de demandes privées, console administrateur limitée à manage_options, statut et historique.
- Protection de formulaire : nonce, honeypot, contrôle de champs côté serveur, limite par IP.
- Avis à l'adresse d'administration WordPress, sans données personnelles détaillées dans le corps du courriel à partir de la version 0.2.0.
- Export et effacement des dossiers par adresse courriel via les outils natifs WordPress; signalement des échecs de suppression.
- Suppression programmée des dossiers terminés depuis 180 jours (dépend de WP-Cron).
- Actions : lint PHP, tests PHP, contrôle de syntaxe JS, ZIP; workflow SSH isolé désactivé par défaut.

## Preuves et limites
- Commit c22619ba2b692add1609c613e699a18bb82a48d0 : CI réussie le 2026-10-14.
- Version 0.2.0 / nouvelle CI : vérifier les runs du nouveau commit après push.
- Tests WordPress réels, plugins historiques, notifications SMTP, sécurité de l'instance, restauration des sauvegardes : non vérifiés.
- Déploiement et activation sur ramassedebris.ca : NON confirmés; le workflow de production précédent a été ignoré.
- Intégrations WooCommerce/YITH, Operations Center et TAKATAK : hors périmètre de la version actuelle; sources et contrats à récupérer.

**Projet complet : NON.** Une extension livrable n'est pas une migration du site historique. Voir docs/TASK_QUEUE.md.
