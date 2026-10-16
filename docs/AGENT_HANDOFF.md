# AGENT HANDOFF — RAMASSEDEBRIS.CA

**Date de relève :** 2026-10-16  
**Repository :** takatakca/RAMASSEDEBRIS.CA  
**Branche autorisée :** main  
**Dernier commit HEAD vérifié avant cette session :** c1af30a7533cc5142c9fbca34f3b313ce1d7c2e7 (CI 38849030701 SUCCESS, déploiement 38849109860 SKIPPED).  
**Version préparée :** 0.5.1.  
**SHA de la livraison présente :** lire directement `branches/main` après le push, puisque le fichier de relève fait partie du commit.

## Dernière tâche terminée

Audit GitHub du repository, `main`, `AGENTS.md`, de cette relève, de l'arborescence et du code, des PR et issues #1 à #3 et des exécutions CI. Correction d'un défaut de validation du formulaire : auparavant les champs `name`, `phone`, `city`, `details` étaient tronqués côté serveur **avant** validation, rendant possible l'acceptation d'une requête hors limites. Une fonction `rdb_prepare_request_values` valide les types et limites de longueur après nettoyage, impose une limite de taille brute avant sanitisation et rejette les tableaux fournis dans les champs facultatifs. Les données existantes et WordPress/WooCommerce ne sont pas modifiés.

## Tâche actuellement en cours

Déployer en dépôt la version 0.5.1 et vérifier la CI PHP/JavaScript et l'intégration WordPress/MySQL jetable. Une fois la CI verte, la prochaine tâche majeure est issue #1 : retrouver et inventorier les sources historiques WordPress et Operations Center, non présentes dans ce dépôt.

## Fichiers modifiés dans cette reprise

- `plugin/ramassedebris-service/ramassedebris-service.php`
- `tests/smoke.php`, `tests/request-case.php`, `tests/run-request-handler.sh`, `tests/wordpress-integration.php`
- `scripts/wp-check-plugin-readonly.sh`, `.github/workflows/validate.yml`
- `README.md`, `AGENTS.md`
- `docs/AGENT_HANDOFF.md`, `docs/PROJECT_STATUS.md`, `docs/TASK_QUEUE.md`, `docs/DEPLOYMENT.md`, `docs/DECISIONS.md`

## Commandes et vérifications

- Lecture du dépôt, HEAD `main`, fichiers de relève, du plugin, des tests, de la configuration CI, des issues et des workflows via GitHub.
- CI précédente `38849030701` : SUCCESS (deux jobs incluant WP-CLI et WordPress/MySQL réels mais temporaires).
- Correctif 0.5.1 : tests PHP des tailles et des types, tests du gestionnaire de formulaire en processus isolés, intégration WordPress et ZIP. **Résultat de la nouvelle CI non encore confirmé au moment de rédaction de ce fichier** : vérifier immédiatement sur GitHub.
- Déploiement production : NE PAS activer automatiquement, vérifier son état par les runs GitHub.

## Blocages et statut du déploiement

**Pas de déploiement WordPress réel vérifié.** La CI exécute WordPress/MySQL éphémères, pas le site historique. Les sources WordPress/WooCommerce et Operations Center n'ont pas été retrouvées dans les dépôts accessibles. Les issues #1 (sources originales), #2 (préproduction, sauvegardes, essais réels) et #3 (SSH isolé, secrets et approbation) demeurent ouvertes.

Accès nécessaires, sans secrets dans Git : export des fichiers historiques et du schéma utiles, sauvegarde restaurable, préproduction fidèle, WP Admin et WP-CLI autorisé, test SMTP/CDN, chemin et hôte SSH confirmés, clés et empreinte SSH dans l'environnement GitHub `production`. Ne jamais supposer que MochaHost ou Coolify héberge ce site avant inventaire.

TAKATAK reste l'autorité d'identité de l'écosystème. Aucune connexion inter-tenant, réservation ou facturation fictive n'a été ajoutée.

## Prochaine action exacte

1. Lire `AGENTS.md` et ce fichier; vérifier le SHA courant de `main`, son CI et son run de déploiement.
2. Si la nouvelle CI est rouge, lire les logs et corriger sans contourner les garde-fous.
3. Avec les vraies sources et accès, reprendre issue #1; ne jamais recréer WordPress/WooCommerce de zéro.
4. En préproduction vérifiée, lancer `bash scripts/wp-audit-readonly.sh /chemin/wordpress` puis `bash scripts/wp-check-plugin-readonly.sh /chemin/wordpress 0.5.1`, tester le formulaire, les rôles, la confidentialité, SMTP/CDN et les réservations WooCommerce.
5. Ne déclarer la publication sur ramassedebris.ca réussie qu'après transfert et tests sur le serveur réel; mettre à jour ce journal avec les preuves.
