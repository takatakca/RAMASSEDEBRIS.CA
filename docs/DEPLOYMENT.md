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

Le workflow Deploy isolated WordPress plugin accepte un lancement manuel. Un déploiement automatique sur changement du plugin est **désactivé par défaut**. Pour l'activer après qualification, définir la variable Actions RDB_AUTO_DEPLOY=true.

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
