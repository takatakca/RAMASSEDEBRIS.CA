# TASK QUEUE — 2026-10-15

| Priorité | Travail | État / condition de sortie |
| --- | --- | --- |
| P0 | Récupérer les sources originales WordPress/WooCommerce | Bloqué par l'absence d'export; aucun code source trouvé dans les autres dépôts GitHub; issue #1 |
| P0 | Localiser et inventorier Operations Center, tournées, camion, MCP antérieurs | Bloqué; issue #1; ne pas réinventer le code |
| P0 | Préparer une sauvegarde restaurable et une préproduction du site actuel | Bloqué par l'accès à l'installation; issue #2 |
| P0 | Fiabiliser validation, effacement manuel et lots partiellement en échec | Terminé côté code et CI en version 0.3.1; test E2E WordPress toujours nécessaire |
| P0 | Renouveler le nonce des formulaires publics en cache; ne pas envoyer si indisponible | Code 0.4.0 + tests navigateur réussis en CI; tests WordPress/CDN en préproduction restent à faire |
| P0 | Empêcher le déploiement auto avant validation CI | Workflow workflow_run observé après CI réussie; branche auto ignorée selon variable désactivée; transfert SSH reste non testé |
| P0 | Vérifier l'installation WordPress, les rôles, la demande, les alertes, la gestion admin, l'export et l'effacement | À faire en préproduction; issue #2 |
| P0 | Auditer WooCommerce, YITH, Elementor, Wordfence et leur sécurité | Bloqué par inventaire effectif; issue #2 |
| P1 | Valider SMTP et mécanisme de notification | À faire en préproduction |
| P1 | Confirmer le chemin SSH et secrets GitHub Actions RDB_DEPLOY_* | Bloqué; script WP-CLI lecture seule disponible pour inventaire; issue #3 |
| P1 | Déployer le plugin sans toucher aux autres fichiers et vérifier la version activée | Bloqué; issue #3 |
| P2 | Contrat d'API TAKATAK tenant/identité et réconciliation des anciens systèmes | Attendre sources/contrat validés |
| P2 | Examiner performance, fonctionnement cron, sécurité et confidentialité opérationnelle | À faire sur instance qualifiée |

## Vérifications indépendantes en cours

- Contrats du gestionnaire de soumission publique avec des dépendances WordPress simulées : tests livrés; CI initiale rouge sur constante WordPress simulée absente, correctif préparé et à revalider. Cela ne prouve pas une exécution WordPress réelle.

## Continuité
Sur reprise : contrôler HEAD, AGENTS.md, docs/AGENT_HANDOFF.md, status CI; corriger les échecs puis continuer la première tâche exécutable. Ne pas considérer les problèmes d'accès non résolus comme un prétexte pour simuler le système en production.

Issues actives : #1 (sources), #2 (préproduction/sécurité), #3 (déploiement).

## État vérifié de la dernière session

Contrôles du plugin 0.4.0 réussis sur 1c9adb52f88a8318e9f61a944c3ea3942e6a7337, run 38792023520. Les issues #1, #2 et #3 restent ouvertes; la prochaine tâche principale est la récupération des sources historiques du site. Les tests intégrés à WordPress et la qualification de production ne sont pas encore possibles sans ces accès.
