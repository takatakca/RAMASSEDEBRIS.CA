# AGENTS — RAMASSEDEBRIS.CA

## Reprise obligatoire
Relire AGENTS.md, docs/AGENT_HANDOFF.md, docs/PROJECT_STATUS.md, docs/TASK_QUEUE.md, docs/DECISIONS.md et docs/DEPLOYMENT.md. Consulter la branche main, le dernier SHA, les derniers commits, les issues et GitHub Actions.

## Instructions durables
1. Ne jamais écraser l'installation WordPress/WooCommerce historique avec ce dépôt : les sources originales ne s'y trouvent pas encore.
2. Reprendre les développements WordPress et Operations Center réellement existants avant toute reconstruction de ces systèmes.
3. Garder le plugin plugin/ramassedebris-service isolé de WooCommerce/YITH et de l'autorité d'identité TAKATAK. Ne pas inventer de paiements, réservations ou authentifications.
4. Protéger toute donnée client. Aucune sauvegarde SQL, clé SSH, secret ni export de demandes dans Git. Vérifier droits côté serveur et séparation entre entreprises.
5. Effectuer des modifications ciblées, exécuter les vérifications puis pousser sans force-push, avec contrôle du HEAD attendu.
6. Ne déclarer une fonctionnalité en production qu'après preuve côté serveur et vérification de bout en bout.
7. Actualiser docs/AGENT_HANDOFF.md à chaque session, avec le dernier commit, tests, blocages et prochaine action.

## Commandes de validation
    find plugin tests -name '*.php' -print0 | xargs -0 -n1 php -l
    php tests/smoke.php
    bash tests/run-manual-deletion.sh
    node --check plugin/ramassedebris-service/assets/form.js
    node tests/form.test.cjs

Le workflow GitHub .github/workflows/validate.yml produit un ZIP installable. L'autodéploiement reste inactif tant que les préconditions documentées dans docs/DEPLOYMENT.md ne sont pas réunies.

Un orchestrateur externe autorisé est indispensable pour relancer automatiquement un agent interrompu : le présent prompt ne peut pas le faire seul.
