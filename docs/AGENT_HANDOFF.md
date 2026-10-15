# AGENT HANDOFF — RAMASSEDEBRIS.CA

Date de relève : 2026-10-15
Dépôt : takatakca/RAMASSEDEBRIS.CA
Branche : main
Dernier commit applicatif vérifié : 1c9adb52f88a8318e9f61a944c3ea3942e6a7337
Dernier run CI vérifié : 38792023520 — SUCCESS
Dernier run de déploiement : 38792081070 — SKIPPED (autodéploiement désactivé)
Version du plugin : 0.4.0
Dernier commit documentaire : consulter le HEAD de main après cette relève.

## Dernière tâche terminée

- Audit du repository, de la branche, des documents, des issues #1 à #3, des PR (aucune) et des dernières Actions.
- Formulaire 0.4.0 : nonce WordPress renouvelé par AJAX POST avant soumission pour les pages en cache, message d'erreur explicite, soumission classique préservée sans JavaScript.
- Déploiement SSH : déclenchement uniquement après une CI réussie sur main en mode auto, revalidation avant SSH, rejet d'un SHA devenu obsolète, empreintes PHP/CSS/JS contrôlées.
- Tests JS du nonce frais, double soumission, pannes et soumission sans fetch.
- Tests PHP du traitement public : méthode POST, nonce, honeypot, limite par IP, champs/consentement, sauvegarde privée, notification sans données personnelles, panne SMTP et panne de persistance.
- Correction des doubles WordPress de tests (constante HOUR_IN_SECONDS et fonction admin_url); CI finale verte.

## Tâche en cours

Issue #1 : récupérer les sources originales WordPress/WooCommerce et Operations Center. Aucune source historique n'a été retrouvée dans les autres repositories accessibles; ne pas la reconstruire sans inventaire.

## Fichiers modifiés pendant la reprise

- plugin/ramassedebris-service/ramassedebris-service.php
- plugin/ramassedebris-service/assets/form.js
- tests/smoke.php, tests/form.test.cjs, tests/request-case.php, tests/run-request-handler.sh
- .github/workflows/validate.yml, .github/workflows/deploy-wordpress.yml
- AGENTS.md, README.md
- docs/AGENT_HANDOFF.md, docs/PROJECT_STATUS.md, docs/TASK_QUEUE.md, docs/DEPLOYMENT.md, docs/DECISIONS.md

## Commandes et vérifications exécutées

- Inspection via GitHub : metadata, branches/main, tree récursif, commits, issues, PR, documents, workflow runs et logs.
- CI 38791143965 : succès, plugin 0.4.0 avec tests navigateur.
- CI 38791669042 : échec du harnais PHP, constante HOUR_IN_SECONDS non simulée.
- CI 38791853207 : échec du harnais PHP, admin_url non simulée.
- CI 38792023520 : succès, syntaxe PHP, tests métier, tests JS, tests de suppression et de soumission, scripts shell, empaquetage ZIP.
- Déploiement 38792081070 : déclenché par workflow_run après succès mais skipped par configuration.

## Blocages et état du déploiement

Production NON DEPLOYÉE et NON ACTIVÉE par cette session. Cible SSH, chemin WordPress, autorisation d'installation, sauvegardes et tests de préproduction non vérifiés. L'outil de consultation externe n'a pas pu accéder au domaine pour une validation publique.

Les accès nécessaires sont : export du site historique, sauvegarde restaurable, accès WordPress administrateur, préproduction, SMTP, host/user/path SSH et secrets GitHub Actions RDB_DEPLOY_* en environnement approuvé. Ne jamais enregistrer les secrets ni les données personnelles dans GitHub.

TAKATAK reste l'autorité d'identité de l'écosystème; ne pas créer un deuxième référentiel client interentreprises, ni simuler les réservations WooCommerce/YITH.

## Prochaine action exacte

1. Relire AGENTS.md et ce fichier, vérifier le dernier SHA sur main et les runs récents.
2. Reprendre issue #1 avec les vraies sources WordPress et Operations Center, sans repartir de zéro.
3. Quand les accès deviennent disponibles : inventaire en lecture seule avec scripts/wp-audit-readonly.sh, sauvegarde et restauration en préproduction, tests sur page en cache, soumission, SMTP, droits admin et confidentialité.
4. Qualifier les secrets, l'empreinte SSH et le chemin réel; activer le déploiement autorisé; vérifier la version WordPress active et le site réel.
5. Réactualiser ce rapport avec le prochain SHA, les preuves, blocages et action suivante.
