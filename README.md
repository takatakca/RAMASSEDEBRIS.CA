# RAMASSEDEBRIS.CA — Ramasse Débris Montréal

Dépôt de développement de RAMASSEDEBRIS.CA.

> **État réel (14 octobre 2026)** : ce dépôt contenait initialement uniquement ce titre. Une installation WordPress/WooCommerce existante est connue séparément, mais **son code n'est pas présent dans ce dépôt**. La présente livraison ajoute une extension indépendante; elle ne remplace **pas** le site existant ni les outils d'exploitation non exportés.

## Module disponible : demandes de ramassage

Le répertoire plugin/ramassedebris-service contient une extension WordPress autonome qui propose :

- Formulaire responsive en français, activé par le shortcode [ramassedebris_demande].
- Collecte de demandes pour meubles, électroménagers, débris de rénovation, commerces et autres objets.
- Date souhaitée **non contractuelle** (aucun prix ni réservation automatiquement confirmé).
- Stockage privé dans WordPress; aucun compte WooCommerce créé et aucune transmission à TAKATAK par défaut.
- Protection des formulaires (nonce, honeypot, validation serveur, limite de 5 demandes par IP/heure).
- Tableau de suivi administrateur : nouvelle demande → contactée → planifiée → terminée.
- Notification à l'adresse administrateur WordPress par wp_mail (nécessite SMTP opérationnel).
- Purge automatique des **dossiers terminés** depuis 180 jours, sans purge des demandes ouvertes.

**Contact professionnel de référence** : 514 825-2825 — ramassedebris.ca@gmail.com — LaSalle, Montréal.

## Développement / vérification

Lancer localement si PHP et Node sont installés :

    find plugin tests -name '*.php' -print0 | xargs -0 -n1 php -l
    php tests/smoke.php
    node --check plugin/ramassedebris-service/assets/form.js

Le workflow .github/workflows/validate.yml répète ces contrôles et génère un ZIP installable en artifact GitHub Actions.

## Déploiement

Le workflow .github/workflows/deploy-wordpress.yml peut déposer uniquement le plugin sur le serveur WordPress via SSH après configuration des secrets et validation des environnements. Il **ne remplace pas** le site, n'active pas le plugin et n'altère pas WooCommerce. Voir docs/DEPLOYMENT.md.

## Plan de reprise

Les sources du site WordPress, l'état des intégrations, les règles métier définitives et les fonctions avancées Operations Center doivent être récupérées avant de pouvoir déclarer le projet terminé. Voir docs/REPRISE-AGENT.md.
