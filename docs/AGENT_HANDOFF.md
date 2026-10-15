# AGENT HANDOFF — RAMASSEDEBRIS.CA

**Date de relève :** 2026-10-15  
**Dépôt :** takatakca/RAMASSEDEBRIS.CA  
**Branche :** main  
**Dernier commit confirmé avant cette reprise :** c22619ba2b692add1609c613e699a18bb82a48d0  
**Dernier commit publié et confirmé avant cette intervention :** 4cfa3a389fdfd77736fe04261ab1b7eff947268b (CI réussie, run 38777784395). **Commit en cours :** protection CI/deploy et plugin 0.4.0; relever le SHA de main après le push.  
**Version cible du plugin :** 0.4.0.

## Intervention courante — 2026-10-15 (nouvelle session)
- Audit à nouveau du dépôt main, du SHA, des documents permanents, des trois issues ouvertes et des exécutions GitHub Actions : le point de départ est 4cfa3a3.
- Refactor du déclencheur de déploiement : plus de lancement parallèle au push; uniquement après un workflow de validation réussi (mode auto conditionnel), validation réexécutée en mode manuel, rejet des commits non courants et empreintes PHP/CSS/JS.
- Plugin version 0.4.0 : le navigateur récupère un nonce frais par requête AJAX POST non mise en cache avant de soumettre un formulaire; retour d'erreur explicite sans fausse confirmation si le service est indisponible, soumission native sans JavaScript.
- Tests ajoutés : comportement navigateur, double soumission, panne réseau, jeton invalide; PHP refresh POST/GET.
- **CI du nouveau commit :** à consulter immédiatement après publication; aucun transfert en production effectué.

## Dernière tâche achevée
Le plugin WordPress indépendant a été amélioré : validation métier testable, échec d'effacement explicitement signalé, courriel de notification sans données personnelles détaillées et effacement manuel sécurisé depuis l'interface administrateur. Les tests du formulaire et de la confidentialité ont été étendus. Les documents permanents et AGENTS.md sont ajoutés.

## Tâche en cours
Version 0.4.0 en cours de validation CI. La première tâche non terminée externe reste issue #1 : retrouver les sources WordPress/Operations Center avant d'intégrer l'application d'origine. La prochaine tâche principale reste la récupération du site WordPress réel et d'Operations Center, issue #1.

## Fichiers concernés
- plugin/ramassedebris-service/ramassedebris-service.php
- tests/smoke.php
- tests/manual-delete-case.php
- tests/run-manual-deletion.sh
- README.md
- AGENTS.md
- docs/PROJECT_STATUS.md
- docs/TASK_QUEUE.md
- docs/AGENT_HANDOFF.md
- docs/DEPLOYMENT.md
- docs/DECISIONS.md
- scripts/wp-audit-readonly.sh
- .github/workflows/{validate,deploy-wordpress}.yml
- plugin/ramassedebris-service/assets/form.js
- tests/form.test.cjs

## Vérifications exécutées et à refaire
- Lecture GitHub get_repo, arborescence, documents, commits, PR, issues #1 à #3 et workflow runs.
- CI du commit c22619b : PHP lint, smoke, JS syntax et ZIP **réussis**.
- CI du lot 0.2.0 : syntaxe PHP, tests métier, syntaxe JS, ZIP **réussis** (38775895581). Contrôles du commit d'inventaire : réussis (38776338377); CI 0.3.0 réussie (38776815238); tests administrateur réussis (38777124775); CI 0.3.1 réussie (38777609101). Préproduction WordPress réelle **non disponible pour ce run**.

## Recherche inter-repositories

Recherche GitHub sur les termes Ramasse Débris et Operations Center dans les dépôts accessibles : aucune source de l'ancien site ou des anciennes opérations n'a été retrouvée hors de ce dépôt. Le référentiel knowledgeAI documente le nom de domaine, pas le code applicatif.

## Problèmes, statut réel et accès
- **Déploiement : NON effectué pendant cette reprise.** Le workflow SSH précédent est skipped; l'autodéploiement est désactivé.
- Bloqué : absence des fichiers historiques WordPress/Operations Center, sauvegarde restaurable et accès à la préproduction; chemin SSH réel non documenté et secrets GitHub Actions dédiés non confirmés.
- Accès nécessaires sans divulguer les secrets : sauvegarde et accès WordPress administrateur, serveur SSH, répertoire WordPress, secrets RDB_DEPLOY_*, test SMTP, sources antérieures.
- Ne jamais inventer de site en ligne, commandes WooCommerce, réservations ou synchronisation TAKATAK.

## Prochaine action exacte
1. Lire AGENTS.md, puis ce fichier et les derniers commits sur main.
2. Vérifier le SHA courant, le résultat GitHub Actions et le contenu de la livraison 0.2.0.
3. Si CI échoue, corriger et repusher immédiatement.
4. Sinon reprendre issue #1 depuis les sources WordPress réelles; garder issue #2 pour la validation WordPress, issue #3 pour le déploiement. Tester le renouvellement du nonce en préproduction derrière un cache/CDN.
5. Réécrire ce rapport avec les nouveaux SHA et preuves avant de quitter.
