# AGENT HANDOFF — RAMASSEDEBRIS.CA

**Date de relève :** 2026-10-15
**Dépôt :** takatakca/RAMASSEDEBRIS.CA
**Branche :** main
**Dernier commit vérifié au début de la session :** 4c8e75187866aa87b948c2f24fc7cf7673f310f2
**Dernier commit de cette session :** publication du plugin 0.5.0 en cours; relever le SHA réel à la reprise.
**CI dernière vérifiée avant modification :** run 38792476872 — SUCCESS.
**Déploiement précédent :** run 38792527572 — SKIPPED.
**Version cible du plugin :** 0.5.0.

## Dernière tâche terminée
Audit GitHub de la branche, arborescence, documents, issues #1 à #3 et exécutions CI. Renforcement des capacités natives du type privé `rdb_demande` : permissions de lecture/écriture/suppression mappées à `manage_options`, création standard interdite. Recherche interne dans les demandes par nom, téléphone, courriel, ville et code postal, combinable aux statuts et à la pagination. Tests PHP des permissions et de construction des clauses WP_Query ajoutés.

## Tâche actuellement en cours
Confirmer le commit/push 0.5.0 et son résultat de CI, corriger immédiatement toute erreur. Puis reprendre issue #1 avec les sources originales du site WordPress et d'Operations Center; les recherches dans les dépôts accessibles et Google Drive des sessions précédentes n'ont pas fourni le code.

## Fichiers modifiés
- plugin/ramassedebris-service/ramassedebris-service.php
- tests/smoke.php
- README.md
- docs/PROJECT_STATUS.md
- docs/TASK_QUEUE.md
- docs/DECISIONS.md
- docs/DEPLOYMENT.md
- docs/AGENT_HANDOFF.md

## Commandes et vérifications
- GitHub : get_repo, branches/main, tree/main, issues ouvertes, PR, derniers workflow runs, AGENTS.md et documents permanents, lecture du PHP et tests.
- Exécuter la CI GitHub pour `php -l`, `php tests/smoke.php`, tests des gestionnaires POST, `node --check`, tests navigateur, `bash -n`, ZIP.
- À vérifier : run de la version 0.5.0 après le push.
- Tests réels du site WordPress, droits éditeur/auteur, SMTP, CDN, WooCommerce/YITH : non effectués.

## Problèmes et état réel du déploiement
- **Production : NON déployée par cette session.** Déploiement SSH désactivé tant que les secrets et chemins ne sont pas vérifiés.
- **Issue #1 :** code WordPress/Operations Center original absent du repository.
- **Issue #2 :** préproduction/sauvegarde restaurable et tests du site réel manquants.
- **Issue #3 :** accès SSH, chemin absolu du plugin, empreinte SSH et secrets RDB_DEPLOY_* non confirmés.
- Ne pas inventer l'intégration TAKATAK, de réservations ou de commandes WooCommerce.

## Accès nécessaires (sans secrets dans Git)
Export des sources historiques, sauvegarde restaurable, accès administrateur et à la préproduction WordPress, test SMTP, host/utilisateur/chemin/empreinte SSH autorisés et secrets GitHub Actions de l'environnement production.

## Prochaine action exacte
1. Lire AGENTS.md et le présent document, récupérer le HEAD de main et la CI associée.
2. Si le nouveau commit 0.5.0 est rouge, lire les logs, corriger, pousser puis revalider.
3. Reprendre issue #1 et ne pas reconstruire l'ancien site sans sources originales.
4. Dès que préproduction et accès existent, exécuter `bash scripts/wp-audit-readonly.sh /chemin/du/site`, valider les rôles, le formulaire et les données, sauvegarder et préparer le déploiement isolé.
5. Ne déclarer le déploiement réussi qu'avec la version vérifiée sur serveur, plugin activé et tests fonctionnels réussis.
