# Déploiement sécurisé — RAMASSEDEBRIS.CA

## Contexte

Le dépôt a été initialisé le 14 octobre 2026 avec un seul README, sans le code source du site WordPress déjà actif. **Ne jamais écraser la racine WordPress ni le répertoire wp-content.** Le plugin livré ici est isolé de WooCommerce, des réservations YITH, des comptes clients et de TAKATAK.

## Préparation

1. Faire une sauvegarde vérifiable des fichiers WordPress et de la base de données.
2. Corriger les mises à jour de sécurité WordPress/extensions, en commençant par les correctifs critiques.
3. Vérifier PHP 7.4+ avec les fonctions WordPress requises, SMTP, permissions, HTTPS et le bon chemin du site (non documenté dans le dépôt).
4. Tester le ZIP ramassedebris-service.zip produit par le workflow Validate and package WordPress extension sur un clone de préproduction.
5. Dans WordPress, installer/activer le plugin puis insérer le shortcode [ramassedebris_demande] dans une page de demande. Le menu Ramasse Débris sera visible uniquement aux administrateurs WordPress.
6. Valider formulaire public, anti-spam, notification SMTP, statut admin, affichage mobile, conservation des données, compatibilité cache et absence de conflit WooCommerce.

## Connexion CI/CD GitHub Actions

Le workflow Deploy isolated WordPress plugin accepte un lancement manuel. Un déploiement automatique est **désactivé par défaut**. Une fois activé, il ne s'exécute qu'après le succès du workflow `Validate and package WordPress extension` sur `main` (événement `workflow_run`). Les lancements manuels revérifient également la syntaxe et les tests du dépôt avant le transfert. Un SHA obsolète par rapport à `main` est refusé. Pour l'activer après qualification, définir la variable Actions RDB_AUTO_DEPLOY=true.

Configurer les secrets GitHub dans l'environnement production :

| Secret | Description |
| --- | --- |
| RDB_DEPLOY_HOST | Hôte SSH confirmé par l'hébergeur |
| RDB_DEPLOY_PORT | Port SSH (laisser vide pour 22) |
| RDB_DEPLOY_USER | Utilisateur SSH autorisé |
| RDB_DEPLOY_PLUGIN_PATH | Chemin absolu finissant strictement par /wp-content/plugins/ramassedebris-service |
| RDB_DEPLOY_SSH_PRIVATE_KEY | Clé privée dédiée avec accès minimal |
| RDB_DEPLOY_KNOWN_HOSTS | Empreinte SSH connue et vérifiée hors bande |

Définir une règle d'approbation pour l'environnement production avant l'activation automatique. Le workflow ne supprime aucun autre plugin et n'active pas automatiquement l'extension.

## Retour arrière

Désactiver l'extension WordPress depuis l'administration (les demandes restent enregistrées). Pour restaurer une version précédente, déployer le ZIP d'un workflow validé ou restaurer les fichiers sauvegardés. Aucun changement de table WooCommerce ou de schéma TAKATAK n'est effectué par ce plugin.

## Données et confidentialité

Les demandes sont enregistrées comme publications privées de type rdb_demande et métadonnées WordPress; accès au tableau de suivi réservé aux administrateurs. Les alertes sont envoyées à l'adresse administrateur configurée dans WordPress. Confirmer la politique de confidentialité, les durées de conservation et les processus de demande d'accès/suppression avant mise en production. Les dossiers marqués « terminé » depuis plus de 180 jours sont supprimés par tâche WordPress quotidienne (50 par exécution).

## Exportation et suppression sur demande

Le plugin est raccordé aux outils natifs WordPress de confidentialité par adresse courriel vérifiée. Les demandes déposées sans courriel exigent une vérification d'identité et un traitement administratif séparés. Les changements de statut créent un historique d'audit interne.

## Contrôles avant activation

- Vérifier la version 0.5.0 dans la liste des extensions WordPress et la compatibilité des extensions existantes.
- Envoyer une demande test : l'enregistrement doit être privé, visible uniquement aux administrateurs et le courriel d'alerte ne doit pas divulguer la description ou les coordonnées.
- Tester l'export et l'effacement des dossiers par courriel et le signalement des erreurs de suppression; prévoir un processus manuel pour les demandes sans courriel.
- Vérifier les protections du site (cache/CDN, nonce, honeypot, HTTP 303, SMTP, permissions) et la politique de rétention selon les obligations applicables.
- Vérifier la santé de WordPress, les opérations WooCommerce/YITH existantes et restaurer la sauvegarde en cas de régression.
- N'autoriser RDB_AUTO_DEPLOY que lorsque l'environnement de production, la sauvegarde et le rollback sont réellement qualifiés.

## Inventaire de l'installation réelle (lecture seule)

Sur l'hébergement ayant une installation WordPress et WP-CLI fonctionnels, exécuter, en utilisateur non root autorisé :

    bash scripts/wp-audit-readonly.sh /chemin/absolu/du/site

Cette commande ne lit pas les mots de passe ni les valeurs de wp-config.php; elle affiche versions WordPress, PHP, thèmes, extensions, URL publique et hooks cron. Les résultats peuvent contenir des métadonnées internes : ne les rendre publics qu'après examen et épuration.

## Preuve du transfert

En cas de déploiement SSH autorisé, le workflow compare maintenant l'empreinte SHA-256 du fichier principal du plugin entre GitHub Actions et le serveur distant. Ce contrôle prouve uniquement le transfert identique du fichier : **il ne prouve pas que l'extension soit active ni que le site soit fonctionnel**.

## Traitement des demandes de confidentialité sans adresse courriel

L'administrateur WordPress qui a personnellement vérifié la légitimité d'une demande d'effacement peut ouvrir Ramasse Débris > Demandes, afficher « Supprimer cette demande », cocher la confirmation et envoyer le formulaire de suppression. Le serveur exige le rôle administrateur, un nonce, POST, le bon type de dossier et la confirmation. La suppression est définitive et ne concerne que l'entrée WordPress du plugin. Sauvegardes, copies d'alerte des versions antérieures et autres intégrations éventuelles doivent faire l'objet de procédures séparées.

L'extension correspond à la version 0.5.0; la présence du fichier sur le serveur n'est pas une preuve de son activation. Tester les lots d'effacement volumineux et les suppressions partiellement en erreur en préproduction.

## Pages en cache et nonce invité

En version 0.4.0, le shortcode fournit une URL WordPress `admin-ajax.php`. Avec JavaScript, le formulaire récupère un nonce frais par POST `action=rdb_refresh_nonce` juste avant l'envoi; une panne du service affiche une erreur sans soumettre ni annoncer une demande réussie. Sans JavaScript, le formulaire WordPress original reste disponible, avec le risque normal d'un nonce expiré si la page était mise en cache longtemps. Exclure `wp-admin/admin-ajax.php` et `wp-admin/admin-post.php` du cache/CDN, confirmer HTTP POST et tester la soumission sur une page anciennement mise en cache. L'endpoint public de nonce ne constitue pas une authentification ni une protection anti-spam autonome.

## Tests de sécurité du gestionnaire public

Le script `bash tests/run-request-handler.sh` vérifie la soumission du formulaire avec des interfaces WordPress simulées : méthode POST, nonce, honeypot, limite des demandes, champs invalides ou tableau inattendu, consentement, sauvegarde privée, contenu sans coordonnées dans la notification et échec SMTP. Ces tests ne publient aucune information personnelle, n'envoient aucun courriel réel et ne remplacent pas une préproduction WordPress intégrée.

## Contrôle de séparation des rôles (0.5.0)

En préproduction, après installation ou mise à jour du plugin, vérifier avec un utilisateur administrateur que le tableau privé s'affiche et que la recherche par coordonnées fonctionne. Vérifier avec un éditeur et un auteur que l'accès à `rdb_demande` est refusé via les capacités WordPress, les interfaces d'administration et les plugins tiers; ne pas se limiter à masquer le menu. Les données WooCommerce/YITH et l'authentification TAKATAK ne sont pas modifiées.

## Validation native WordPress en CI

Le job `wordpress-integration` de GitHub Actions crée un WordPress et une base MySQL temporaires. Il y active la version courante du plugin et effectue les tests de droits, recherche, export/effacement et shortcode sans toucher au serveur historique. Le workflow de transfert en production ne doit fonctionner automatiquement qu'après réussite de **tous** les jobs de validation, mais demeure inactif tant que l'environnement production n'est pas qualifié. Sur une préproduction réelle, comparer WordPress, PHP, cache, SMTP et plugins WooCommerce/YITH avec le site existant.

## Vérification réelle de version et activation

Utiliser WP-CLI en lecture seule avec un utilisateur non root et un chemin WordPress vérifié :

    bash scripts/wp-check-plugin-readonly.sh /chemin/absolu/wordpress 0.5.0

Ce contrôle échoue si la version ou l'activation ne correspond pas, sans modifier le site. Il est exercé dans le WordPress CI temporaire, mais doit aussi être utilisé après une installation réelle approuvée. Le résultat seul ne remplace pas un test fonctionnel complet avec SMTP, cache et réservations existantes.
