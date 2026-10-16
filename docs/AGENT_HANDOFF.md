# AGENT HANDOFF — RAMASSEDEBRIS.CA

**Date de relève :** 2026-10-15
**Dépôt :** takatakca/RAMASSEDEBRIS.CA
**Branche :** main
**Dernier commit vérifié au début de la session :** 4c8e75187866aa87b948c2f24fc7cf7673f310f2
**Dernier commit applicatif publié dans cette session :** 1a41b8589a96063724cff9e1d131b6735ea845f5 (CI 38848126133 : SUCCESS). **Lot actuel :** ajout d'un job de validation WordPress/MySQL réel mais jetable; relever le SHA et la CI après sa publication.
**CI dernière vérifiée avant modification :** run 38848126133 — SUCCESS (0.5.0).
**Déploiement précédent :** run 38792527572 — SKIPPED.
**Version cible du plugin :** 0.5.0.

## Nouveau travail CI en cours
- Une installation WordPress/MySQL jetable dans GitHub Actions valide le CPT réel, les capacités des rôles, les recherches sur les métadonnées, le workflow natif d'export/effacement et le shortcode.
- La CI devra être entièrement verte avant tout déploiement automatique. Aucune donnée ni serveur de production n'est utilisé.
- Le déploiement et la préproduction du site historique restent bloqués par les accès et sources documentés aux issues #1 à #3.

## Dernière tâche terminée
Audit GitHub de la branche, arborescence, documents, issues #1 à #3 et exécutions CI. Renforcement des capacités natives du type privé `rdb_demande` : permissions de lecture/écriture/suppression mappées à `manage_options`, création standard interdite. Recherche interne dans les demandes par nom, téléphone, courriel, ville et code postal, combinable aux statuts et à la pagination. Tests PHP des permissions et de construction des clauses WP_Query ajoutés.

## Tâche actuellement en cours
Confirmer le commit/push du job WordPress/MySQL et les résultats de sa CI; corriger toute erreur, puis reprendre les issues #1 à #3 si leurs accès deviennent disponibles. Puis reprendre issue #1 avec les sources originales du site WordPress et d'Operations Center; les recherches dans les dépôts accessibles et Google Drive des sessions précédentes n'ont pas fourni le code.

## Fichiers modifiés
- plugin/ramassedebris-service/ramassedebris-service.php
- tests/wordpress-integration.php
- .github/workflows/validate.yml
- tests/smoke.php
- README.md
- docs/PROJECT_STATUS.md
- docs/TASK_QUEUE.md
- docs/DECISIONS.md
- docs/DEPLOYMENT.md
- docs/AGENT_HANDOFF.md

## Commandes et vérifications
- GitHub : get_repo, branches/main, tree/main, issues ouvertes, PR, derniers workflow runs, AGENTS.md et documents permanents, lecture du PHP et tests.
- CI 38848024103 : rouge sur indices d'assertions WP_Query simulées, corrigées sans modifier les données de production.
- CI 38848126133 : verte après correction.
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
