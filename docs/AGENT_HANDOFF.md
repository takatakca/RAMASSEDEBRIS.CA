# AGENT HANDOFF — RAMASSEDEBRIS.CA

**Date de relève :** 2026-10-15
**Dépôt :** takatakca/RAMASSEDEBRIS.CA
**Branche :** main
**Dernier commit avec CI WordPress complète réussie avant vérification WP-CLI :** 6098f349a7df968f03c4a5cbeaf0ba18952a4328
**Dernière CI entièrement vérifiée :** 38848368743 — SUCCESS, deux jobs, WordPress/MySQL réel éphémère inclus.
**Dernier déploiement :** run 38848457260 — SKIPPED; aucun transfert.
**Version du plugin :** 0.5.0
**Dernier commit de script/relève :** 8b4378f88a6ad606b15f28c4a4d127a50e601fa1. CI 38848720866 rouge uniquement à cause du PATH de WP-CLI sur le runner. Correctif à publier dans le prochain commit; vérifier sa CI.

## Dernière tâche terminée

Le dépôt, la branche, les dernières issues/PR, l'ensemble des documents et les exécutions Actions ont été audités. La version 0.5.0 du plugin isole les capacités de consultation, modification et suppression du type privé rdb_demande à manage_options; elle ajoute une recherche administrateur par nom, téléphone, courriel, ville et code postal, compatible avec les statuts et la pagination. Les tests des filtres WP_Query ont été corrigés après un échec initial. Une seconde tâche de validation GitHub Actions exécute maintenant le plugin sur un WordPress/MySQL jetable et vérifie les vrais rôles WordPress, la persistance privée, les recherches, l'exportation/l'effacement et le shortcode. Les deux tâches de validation ont réussi. Un script WP-CLI read-only vérifie désormais la version et le statut actif sans modifier l'installation.

## Tâche en cours

Confirmer la CI du prochain commit intégrant scripts/wp-check-plugin-readonly.sh et les documents, corriger les éventuelles erreurs, puis reprendre issue #1 dès qu'une copie fidèle des sources WordPress/Operations Center sera disponible.

## Fichiers modifiés au cours de cette reprise

- plugin/ramassedebris-service/ramassedebris-service.php
- tests/smoke.php
- tests/wordpress-integration.php
- scripts/wp-check-plugin-readonly.sh
- .github/workflows/validate.yml
- AGENTS.md, README.md
- docs/AGENT_HANDOFF.md, docs/PROJECT_STATUS.md, docs/TASK_QUEUE.md, docs/DEPLOYMENT.md, docs/DECISIONS.md

## Vérifications, commandes et preuves

- GitHub API : get_repo, branches/main, arbre, issues #1/#2/#3, PR (aucune), code, documents, workflow runs, résultats CI et artefact ZIP.
- CI 38848024103 : FAILURE des indices d'assertions de filtres de requête (corrigé).
- CI 38848126133 : SUCCESS sur commit 1a41b8589a96063724cff9e1d131b6735ea845f5.
- CI 38848368743 : SUCCESS sur commit 6098f349a7df968f03c4a5cbeaf0ba18952a4328; jobs validate et wordpress-integration, WordPress/MySQL éphémères, ZIP du plugin présent.
- Déploiement 38848457260 : SKIPPED; le workflow est correctement bloqué lorsque RDB_AUTO_DEPLOY n'est pas activé.
- À exécuter/vérifier en CI après dernier commit : bash -n scripts/wp-check-plugin-readonly.sh et bash scripts/wp-check-plugin-readonly.sh "$WP_ROOT" 0.5.0 dans l'environnement WordPress jetable.
- Aucune vérification sur le vrai WordPress, SMTP, CDN ou ses extensions tierces n'est effectuée.

## Problèmes et déploiement réel

**Production : NON déployée ni activée par cette reprise.** Le plugin fonctionnant dans le WordPress jetable de GitHub Actions ne prouve pas le fonctionnement du site existant. Le site historique, son code source, les extensions WooCommerce/YITH, les réservations et l'Operations Center ne sont pas dans le dépôt.

## Accès nécessaires sans exposer de secrets

- Issue #1 : sauvegarde et sources réelles WordPress/Operations Center, contrats des fonctionnalités antérieures.
- Issue #2 : préproduction fidèle avec sauvegarde restaurable, accès administrateur, audit Wordfence/Elementor/WooCommerce/YITH, tests SMTP, CDN et fonctionnement de bout en bout.
- Issue #3 : cible SSH, utilisateur, port, chemin du plugin et empreinte SSH vérifiés; secrets RDB_DEPLOY_* dans l'environnement GitHub production avec approbation.
- Respecter TAKATAK comme identité centrale et isoler les données de chaque entreprise; aucune intégration inventée, projet Supabase payant ou migration destructive.

## Prochaine action exacte

1. Lire AGENTS.md et ce document, vérifier HEAD de main et les derniers résultats Actions.
2. Si le nouveau job WP-CLI est en échec, lire les logs et corriger; ne pas publier automatiquement une CI rouge.
3. Reprendre issue #1 avec les sources WordPress historiques lorsqu'elles deviennent disponibles, sans repartir de zéro.
4. Après qualification d'une préproduction : utiliser scripts/wp-audit-readonly.sh et scripts/wp-check-plugin-readonly.sh /chemin/absolu/wordpress 0.5.0; tester rôles, cache, SMTP, confidentialité et WooCommerce/YITH.
5. Avec secrets et cible validés, déployer uniquement le plugin et vérifier version active + parcours métier. Inscrire les preuves et le SHA ici avant toute autre relève.

## Résolution d'une erreur CI (script d'activation)

- L'installation WordPress éphémère et l'intégration ont réussi sur 8b4378f, mais le script en lecture seule ne trouvait pas la commande wp. Le PHAR existe sous RUNNER_TEMP et non dans le PATH, d'où le run 38848720866 en échec.
- Correctif : création d'un wrapper wp dans RUNNER_TEMP/bin et ajout au GITHUB_PATH avant l'étape de vérification. Aucun accès production et aucune donnée utilisateur.
- La première action du prochain agent est de confirmer que le correctif du job 2026-10-15 passe sur main, puis de reprendre la vraie source WordPress issue #1.
