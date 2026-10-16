# RAMASSEDEBRIS.CA — Ramasse Débris Montréal

Dépôt de développement de RAMASSEDEBRIS.CA.

> **État réel (14 octobre 2026)** : ce dépôt contenait initialement uniquement ce titre. Une installation WordPress/WooCommerce existante est connue séparément, mais **son code n'est pas présent dans ce dépôt**. La présente livraison ajoute une extension indépendante; elle ne remplace **pas** le site existant ni les outils d'exploitation non exportés.

## Module disponible : demandes de ramassage

Le répertoire plugin/ramassedebris-service contient une extension WordPress autonome qui propose :

- Formulaire responsive en français, activé par le shortcode [ramassedebris_demande].
- Collecte de demandes pour meubles, électroménagers, débris de rénovation, commerces et autres objets.
- Date souhaitée **non contractuelle** (aucun prix ni réservation automatiquement confirmé).
- Stockage privé dans WordPress; aucun compte WooCommerce créé et aucune transmission à TAKATAK par défaut.
- Protection des formulaires (nonce, honeypot, validation serveur, limite de 5 demandes par IP/heure), avec renouvellement AJAX du nonce au moment de l'envoi pour les pages en cache.
- Tableau de suivi administrateur : nouvelle demande → contactée → planifiée → terminée.
- Notification sans coordonnées client à l'adresse administrateur WordPress par wp_mail (nécessite SMTP opérationnel).
- Purge automatique des **dossiers terminés** depuis 180 jours, sans purge des demandes ouvertes.
- Exportation et effacement des données par courriel vérifié via les outils natifs WordPress, avec signalement des échecs; demandes sans courriel à gérer manuellement.
- Historique d'audit des changements de statut (date et identifiant administrateur).
- Suppression manuelle de demandes par un administrateur, avec nonce et confirmation, notamment pour les clients sans courriel.

**Contact professionnel de référence** : 514 825-2825 — ramassedebris.ca@gmail.com — LaSalle, Montréal.

## Continuité des agents

Les sources historiques du site ne sont pas incluses dans ce dépôt. Lire AGENTS.md, docs/PROJECT_STATUS.md, docs/TASK_QUEUE.md, docs/AGENT_HANDOFF.md et docs/DECISIONS.md avant de modifier ou déployer quoi que ce soit.

Version actuelle du plugin : **0.5.1**.

## Développement / vérification

Lancer localement si PHP et Node sont installés :

    find plugin tests -name '*.php' -print0 | xargs -0 -n1 php -l
    php tests/smoke.php
    bash tests/run-manual-deletion.sh
    bash tests/run-request-handler.sh
    node --check plugin/ramassedebris-service/assets/form.js
    node tests/form.test.cjs

Les tests PHP couvrent également le traitement HTTP côté serveur (nonce, anti-spam, validation, persistance privée, notification et échec SMTP). Le workflow .github/workflows/validate.yml répète ces contrôles et génère un ZIP installable en artifact GitHub Actions.

## Déploiement

Le workflow .github/workflows/deploy-wordpress.yml peut déposer uniquement le plugin sur le serveur WordPress via SSH après configuration des secrets et validation des environnements. Il **ne remplace pas** le site, n'active pas le plugin et n'altère pas WooCommerce. Voir docs/DEPLOYMENT.md.

## Plan de reprise

Les sources du site WordPress, l'état des intégrations, les règles métier définitives et les fonctions avancées Operations Center doivent être récupérées avant de pouvoir déclarer le projet terminé. Voir docs/REPRISE-AGENT.md.

## Inventaire du site historique

Lorsque l'accès à l'installation WordPress existante est disponible, lancer en utilisateur non privilégié :

    bash scripts/wp-audit-readonly.sh /chemin/absolu/du/site

Le script n'effectue aucune modification et ne collecte aucun secret. Le déploiement automatisé, une fois activé sur la bonne cible, compare également l'empreinte SHA-256 du fichier de plugin transféré. Cela ne remplace pas l'activation et les tests du site.

## Suivi des demandes en administration (0.5.0)

Seuls les administrateurs autorisés à `manage_options` peuvent consulter et traiter les demandes privées. La version 0.5.0 interdit en outre aux rôles habituels WordPress (éditeur, auteur) l'accès aux capacités natives de ce type de contenu; les capacités sont mappées explicitement à `manage_options`, et la création par l'éditeur WordPress est désactivée. Le tableau Ramasse Débris inclut une recherche interne (nom, téléphone, courriel, ville et code postal) combinable aux statuts et à la pagination. La recherche ne figure ni dans les routes publiques ni dans l'API REST.

## Intégration WordPress automatisée

Le workflow de validation comprend aussi une instance **jetable** de WordPress/MySQL isolée du site de production. Le test `tests/wordpress-integration.php` vérifie le chargement réel du plugin, les capacités admin/éditeur/auteur, les recherches WP_Query, les hooks d'accès/effacement de données et le shortcode. Aucun accès ni copie des données de ramassedebris.ca n'est utilisé. La réussite de ce test ne remplace pas l'essai sur la préproduction historique avec WooCommerce/YITH.

## Vérification de la version active (WP-CLI)

Après un déploiement autorisé sur un environnement WordPress existant, exécuter sans privilèges root :

    bash scripts/wp-check-plugin-readonly.sh /chemin/absolu/wordpress 0.5.1

Cette commande ne modifie rien; elle exige la version annoncée et le statut actif. Les tests automatisés WordPress/MySQL jetables ont réussi (run 38848368743), sans toucher au site historique.

## Validation des données (0.5.1)

Les champs mal formés, trop longs ou fournis sous forme de tableaux PHP sont maintenant rejetés avant l'écriture des demandes. Les anciennes limites HTML étaient déjà présentes, mais le serveur tronquait silencieusement certains champs avant la validation. L'interface et les enregistrements existants restent inchangés; les essais automatisés vérifient les limites et les données facultatives.
