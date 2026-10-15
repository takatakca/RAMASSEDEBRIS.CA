# PROJECT STATUS — 2026-10-15

## Source de vérité
Repository : takatakca/RAMASSEDEBRIS.CA; branche : main. Avant les changements du 14 octobre, seul README.md existait. Le code de l'installation historique WordPress/WooCommerce et de l'ancien Operations Center n'est toujours pas dans le dépôt.

## Réalisé dans le dépôt
- Extension WordPress autonome de collecte de demandes en français, affichage mobile, date souhaitée sans confirmation de réservation.
- Enregistrement de demandes privées, console administrateur limitée à manage_options, statut et historique.
- Protection de formulaire : nonce, renouvellement AJAX du nonce pour les pages mises en cache, honeypot, contrôle de champs côté serveur, limite par IP.
- Avis à l'adresse d'administration WordPress, sans données personnelles détaillées dans le corps du courriel à partir de la version 0.2.0.
- Export et effacement des dossiers par adresse courriel via les outils natifs WordPress; signalement des échecs de suppression; effacement manuel administrateur avec vérification préalable d'identité.
- Suppression programmée des dossiers terminés depuis 180 jours (dépend de WP-Cron).
- Actions : lint PHP, tests PHP, contrôle de syntaxe JS, ZIP; workflow SSH isolé désactivé par défaut; contrôle de checksum en cas de transfert.
- Script de diagnostic WordPress en lecture seule sous scripts/wp-audit-readonly.sh.

## Preuves et limites
- Commit c22619ba2b692add1609c613e699a18bb82a48d0 : CI réussie le 2026-10-14.
- Plugin 0.2.0, commit 2f11c628757a31fab53abc0d7b0bd6eb00c1cee3 : CI **réussie** le 2026-10-15 (run 38775895581); test de validation métier, confidentialité, syntaxe et ZIP.
- Inventaire WP CLI en lecture seule et contrôle d'empreinte SHA-256 sur déploiement SSH : CI réussie, commit 2a3af882a04eec18e6c221368681b3973d113545, run 38776338377.
- Plugin version 0.3.0 : suppression manuelle depuis le tableau administrateur avec confirmation et contrôle CSRF; CI réussie, commit 97e1ec9666299d938a04effce700f5128850adbb, run 38776815238.
- Tests administrateur (droits, nonce, confirmation, type, erreurs) : CI réussie sur 2adf208da947b9f7a6772655bcbda2c8659d3069, run 38777124775.
- Version 0.3.1 : effacement par lots robuste face aux dossiers impossible à supprimer. Commit 10c94a044765166481c2f5fc4843bc30c0781337; CI réussie, run 38777609101.
- GitHub Actions Deploy isolated WordPress plugin du même commit : ignoré volontairement, production non modifiée.
- Plugin 0.4.0 au commit 52aa5881142e54f5d348bd15e35ee2f9d59bd88f : CI **réussie**, run 38791143965; scénario `workflow_run` de déploiement observé et **ignoré volontairement**, run 38791179144.
- Tests complémentaires du gestionnaire public de demande ajoutés (POST, nonce, honeypot, rate-limit, validité, sauvegarde privée, SMTP, échecs); résultat CI du prochain commit à vérifier.
- Tests WordPress réels, plugins historiques, notifications SMTP, sécurité de l'instance, restauration des sauvegardes : non vérifiés.
- Déploiement et activation sur ramassedebris.ca : NON confirmés; le workflow de production précédent a été ignoré.
- Intégrations WooCommerce/YITH, Operations Center et TAKATAK : hors périmètre de la version actuelle; sources et contrats à récupérer.

**Projet complet : NON.** Une extension livrable n'est pas une migration du site historique. Voir docs/TASK_QUEUE.md.
