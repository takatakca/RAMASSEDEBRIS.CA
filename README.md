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
- Notification sans coordonnées client à l'adresse administrateur WordPress par wp_mail (nécessite SMTP opérationnel).
- Purge automatique des **dossiers terminés** depuis 180 jours, sans purge des demandes ouvertes.
- Exportation et effacement des données par courriel vérifié via les outils natifs WordPress, avec signalement des échecs; demandes sans courriel à gérer manuellement.
- Historique d'audit des changements de statut (date et identifiant administrateur).

**Contact professionnel de référence** : 514 825-2825 — ramassedebris.ca@gmail.com — LaSalle, Montréal.

## Continuité des agents

Les sources historiques du site ne sont pas incluses dans ce dépôt. Lire AGENTS.md, docs/PROJECT_STATUS.md, docs/TASK_QUEUE.md, docs/AGENT_HANDOFF.md et docs/DECISIONS.md avant de modifier ou déployer quoi que ce soit.

Version actuelle du plugin : **0.2.0**.

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

## Inventaire du site historique

Lorsque l'accès à l'installation WordPress existante est disponible, lancer en utilisateur non privilégié :

    bash scripts/wp-audit-readonly.sh /chemin/absolu/du/site

Le script n'effectue aucune modification et ne collecte aucun secret. Le déploiement automatisé, une fois activé sur la bonne cible, compare également l'empreinte SHA-256 du fichier de plugin transféré. Cela ne remplace pas l'activation et les tests du site.
