# AGENT HANDOFF — RAMASSEDEBRIS.CA

**Date de relève :** 2026-10-16
**Dépôt :** takatakca/RAMASSEDEBRIS.CA
**Branche principale / de publication du code :** main
**Dernier commit vérifié avant le lot final :** 84b8fa887db6108701dac40de0028d504543b703
**Dernière CI du lot HTTP :** 38950507500 — SUCCESS, deux jobs incluant WordPress/MySQL et POST HTTP réels.
**Dernier run SSH :** 38950644731 — SKIPPED, production non modifiée.
**Version préparée et publiée dans le lot actuel :** 0.5.2
**Dernier SHA du lot 0.5.2 :** lire la branche main après le push de cette relève; ne pas déduire son SHA du précédent commit.

## Dernière tâche achevée

1. Audit de HEAD main, AGENTS.md, de cette relève, du plugin, des workflow runs, de la documentation, des issues #1-#3 et des PR.
2. Correction en 0.5.1 d'une validation POST incorrecte : les champs nom, téléphone, ville et description pouvaient être tronqués avant la validation. Désormais les valeurs excessives, les tableaux PHP inattendus et les champs bruts volumineux sont rejetés sans sauvegarde. Tests du gestionnaire et des limites ajoutés. CI 38950002117 SUCCESS.
3. Ajout de tests HTTP réels dans WordPress/MySQL jetable : nonce issu de admin-ajax.php, CSRF, longues valeurs, tableaux facultatifs, honeypot, création d'un seul dossier privé. Première CI 38950313111 rouge (erreur du script curl, pas du plugin); correction en 84b8fa8, CI 38950507500 SUCCESS.
4. Correctif 0.5.2 : quand l'adresse e-mail administrateur n'est pas valide, la notification est marquée comme non envoyée plutôt que laissée sans statut. Les demandes restent sauvegardées, les détails clients restent absents du courriel et l'administration présente déjà un avertissement sur la valeur 0. Cas de test invalid_admin_email ajouté.
5. Documents de projet, déploiement, décisions, file d'attente et présente relève mis à jour.

## Tâche actuellement en cours

Vérifier les résultats CI du commit 0.5.2, y compris ZIP, WordPress/MySQL, HTTP réel et run workflow_run SSH ignoré. Si échec : corriger. Sinon poursuivre l'issue #1, récupération des véritables sources WordPress/WooCommerce et Operations Center, puis la qualification de préproduction issue #2 et les accès SSH issue #3.

## Fichiers modifiés dans cette session

- plugin/ramassedebris-service/ramassedebris-service.php
- tests/smoke.php
- tests/request-case.php
- tests/run-request-handler.sh
- tests/wordpress-integration.php
- tests/run-wordpress-http.sh
- .github/workflows/validate.yml
- scripts/wp-check-plugin-readonly.sh
- AGENTS.md, README.md
- docs/PROJECT_STATUS.md, docs/TASK_QUEUE.md, docs/DECISIONS.md, docs/DEPLOYMENT.md, docs/AGENT_HANDOFF.md

## Commandes et vérifications exécutées

- GitHub API : get_repo, branches/main, arbre, PR, issues, documents, code source, logs des jobs, historique des exécutions.
- CI sur ccf9a38 (38950002117) : PHP, JS, contrat gestionnaire, WordPress/MySQL, archive ZIP — SUCCESS.
- CI sur 56b8529 (38950313111) : test HTTP rouge à cause d'un argument curl sans --data-urlencode. Correction publiée.
- CI sur 84b8fa8 (38950507500) : mêmes validations et HTTP WordPress — SUCCESS. Run SSH suivant 38950644731 SKIPPED.
- Tests du scénario d'adresse administrateur invalide ajoutés à la version 0.5.2; vérifier le résultat du nouveau run après publication.

## Problèmes, erreurs et statut de déploiement

**Production : aucun transfert SSH ni activation de cette extension confirmés.** La CI réussie utilise un WordPress/MySQL local jetable; elle ne couvre pas les extensions ni les commandes existantes du site historique.

**Bloquages humains réels :**
- Issue #1 : fichiers WordPress et outils Operations Center antérieurs absents du repository et non retrouvés dans les sources accessibles.
- Issue #2 : sauvegarde restaurable, clone de préproduction, WP-Admin, cache/CDN, SMTP, audit WooCommerce/YITH/Wordfence/Elementor et parcours E2E.
- Issue #3 : hôte/port/utilisateur SSH, chemin plugin, empreinte connue, clés RDB_DEPLOY_* et approbation d'environnement production non vérifiés.
Aucune clé ou donnée client n'a été ajoutée au dépôt.

## Prochaine action exacte

1. Relire AGENTS.md et ce fichier, demander HEAD main à GitHub et contrôler la dernière CI du commit 0.5.2.
2. Corriger tout échec CI sans réduire les protections; consulter le job wordpress-integration en particulier.
3. Récupérer les sources originales WordPress/Operations Center via un accès autorisé; ne jamais recréer ni remplacer aveuglément l'installation historique.
4. Sur clone de préproduction après sauvegarde restaurable, utiliser scripts/wp-audit-readonly.sh puis scripts/wp-check-plugin-readonly.sh /chemin/wordpress 0.5.2, tester formulaire, alertes SMTP, rôles, confidentialité, cache et parcours WooCommerce/YITH.
5. Après qualification des secrets et de la cible SSH, déployer uniquement le dossier du plugin avec l'environnement protégé et vérifier son activation et les parcours réels.
6. Actualiser ce journal avec SHA de livraison, CI et preuve serveur avant toute conclusion à 100 %.

## Contrat TAKATAK

Identité TAKATAK master, séparation stricte des tenants. Ce plugin enregistre uniquement des demandes privées locales WordPress; pas de nouvelle autorité d'identité, ni synchronisation de clients interentreprises, ni prix/rendez-vous fictifs.
