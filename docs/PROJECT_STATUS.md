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
- Tests complémentaires du gestionnaire public ajoutés. Première exécution CI `38791669042` : échec par constante WordPress `HOUR_IN_SECONDS` manquante dans le harnais PHP; test isolé corrigé dans la livraison suivante, nouveau run à vérifier.
- Tests WordPress réels, plugins historiques, notifications SMTP, sécurité de l'instance, restauration des sauvegardes : non vérifiés.
- Déploiement et activation sur ramassedebris.ca : NON confirmés; le workflow de production précédent a été ignoré.
- Intégrations WooCommerce/YITH, Operations Center et TAKATAK : hors périmètre de la version actuelle; sources et contrats à récupérer.

**Projet complet : NON.** Une extension livrable n'est pas une migration du site historique. Voir docs/TASK_QUEUE.md.

## Contrôle final de la reprise (2026-10-15)

Plugin 0.4.0, dernier commit applicatif valide : 1c9adb52f88a8318e9f61a944c3ea3942e6a7337. CI 38792023520 : SUCCESS, y compris les tests du gestionnaire de soumission après correction des doubles WordPress manquants (HOUR_IN_SECONDS et admin_url). Déploiement workflow_run 38792081070 : SKIPPED, production inchangée. Deux exécutions intermédiaires rouges (38791669042 et 38791853207) sont documentées et ont été corrigées. Aucun test de l'installation WordPress réelle ou publication sur le domaine n'est prouvé.

## Livraison 0.5.0 (présente session, CI à confirmer)

- Capacités du type privé `rdb_demande` mappées explicitement à `manage_options`; création par l'interface WordPress désactivée et REST toujours non exposé. Cette modification renforce l'isolation des dossiers face aux autres extensions pouvant effectuer des requêtes sur les types de publications.
- Recherche interne des demandes par coordonnées, ville ou code postal; combinaison avec filtre de statut et pagination, sans requête publique. Tests de configuration des capacités et de construction des filtres inclus.
- Déploiement et activation réels toujours non vérifiés : aucun secret d'accès ni source WordPress historique n'est apparu dans le dépôt.

## Vérification WordPress réelle en CI (en cours)

Un deuxième job du workflow `Validate and package WordPress extension` installe WordPress et MySQL **temporaires**, active le plugin et exerce les capacités WordPress, les filtres WP_Query, l'export/effacement et le shortcode. Son succès est une condition du résultat de la CI, donc de l'autodéploiement dépendant de cette CI. Vérifier le premier run après publication; ne pas confondre cet environnement jetable avec la production ou avec le WordPress historique et ses extensions.

## Vérification de la présente session (2026-10-15)

- Version 0.5.0 : permissions privées de WordPress et recherche interne. Commit 1a41b8589a96063724cff9e1d131b6735ea845f5; CI 38848126133 réussie.
- Intégration WordPress/MySQL jetable : commit 6098f349a7df968f03c4a5cbeaf0ba18952a4328, run 38848368743; **deux jobs réussis**, ZIP créé.
- Déploiement associé 38848457260 : **skipped**, aucune modification de production.
- Script WP-CLI de vérification de version et d'activation : inclus dans la livraison suivante, CI à confirmer.

## État vérifié final du contrôle WP-CLI

- Le commit `d9de6b71e701e99589ce30eee17232805ae678de` a passé la CI GitHub `38848878976` : `validate` SUCCESS et `wordpress-integration` SUCCESS. Le plugin 0.5.0 a été activé et sa version contrôlée sur un WordPress jetable.
- Le premier essai `38848720866` avait échoué : WP-CLI non exposé au PATH du runner; erreur corrigée et traçable dans GitHub.
- La cible WordPress historique et son activation réelle demeurent **non vérifiées**; aucune publication de production n'est déduite de cette réussite CI.

## Reprise et correctif du 2026-10-16

- Base confirmée : `main` au commit `c1af30a7533cc5142c9fbca34f3b313ce1d7c2e7`, CI `38849030701` réussie sur les deux jobs, déploiement `38849109860` ignoré volontairement.
- Problème découvert : le traitement POST tronquait nom, téléphone, ville et description avant la validation; les limites pouvaient être contournées en envoyant directement des données trop longues. Un champ facultatif de type tableau pouvait aussi être traité comme absent.
- Correctif version 0.5.1 : préparation centralisée, rejet de champs non scalaires, rejet des valeurs trop longues sans troncature et limite de taille brute; tests PHP des frontières et de la soumission réelle du gestionnaire, vérifications WordPress CI intégrées. **CI de ce lot : à vérifier après le push.**
- Hébergement WordPress historique, sauvegardes, comptes administrateurs et secrets SSH dédiés toujours non confirmés. Aucune prétention de déploiement production.

## Reprise du 2026-10-16 — preuves CI

- Validation stricte sans troncature : commit `ccf9a38aa5f6b1cceb6a093d00284d6839f781c5`, run `38950002117` **SUCCESS** (deux jobs).
- Tests HTTP réels sur le WordPress/MySQL éphémère : le premier commit `56b852997537efc6bceef93896fc979fe7223ff3` avait une erreur de syntaxe des arguments curl facultatifs (`38950313111` FAILED), corrigée dans `84b8fa887db6108701dac40de0028d504543b703`. Run `38950507500` **SUCCESS** (PHP/JS/ZIP et WP/HTTP). Déploiement `38950644731` **SKIPPED**.
- Nouvelle version 0.5.2 : l'absence d'adresse courriel administrateur valide est enregistrée comme échec d'avis (`_rdb_notification_sent=0`) au lieu de rester invisible. Cas de test ajouté. **Nouvelle CI à vérifier après push.**
- Sources historiques, sauvegarde restaurable, préproduction réelle et SSH restent indisponibles/non vérifiés : aucune preuve de mise en ligne.
