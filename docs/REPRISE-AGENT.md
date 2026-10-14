# Relève de développement — RAMASSEDEBRIS.CA

## Source de vérité inspectée le 2026-10-14

- GitHub : takatakca/RAMASSEDEBRIS.CA, branche main.
- À l'audit : seulement README.md, un commit initial, aucune issue/PR ou code exploitable, aucun workflow.
- Des informations de projet antérieures font état d'un WordPress/WooCommerce déjà actif, et de fonctionnalités d'exploitation développées **en dehors de ce dépôt**. Leur code, schéma et contrats API ne sont pas disponibles ici; aucune migration fictive.
- Ne pas remplacer le site WordPress existant sans reprise des sources réelles, sauvegardes, inventaire des extensions et validation de préproduction.

## Livraison du présent dépôt

Extension WordPress autonome de collecte de demandes (formulaire public, honeypot, nonce, limite IP, validation, enregistrement privé, suivi des statuts, notifications administrateur et purge des dossiers terminés), tests syntaxiques et packaging GitHub Actions, déploiement SSH isolé sur configuration explicite.

## Prochaines tâches prioritaires

1. Récupérer une exportation fidèle du site WordPress actuel (thème enfant personnalisé, extensions spécifiques, configuration hors secrets) et les sources de l'ancienne application Operations Center/MCP si elles existent.
2. Auditer Wordfence, Elementor, WooCommerce, YITH, sauvegardes et conflit potentiel de réservation; installer les mises à jour de sécurité dans un environnement de test.
3. Effectuer les tests WordPress réels : demande depuis mobile, envoi SMTP, visibilité admin, rate-limit, confidentialité/export/effacement, cache, permissions des différents rôles.
4. Qualifier le chemin SSH de production et les secrets GitHub Actions; activer la synchronisation uniquement après une préproduction vérifiée.
5. Reprendre depuis les sources réelles la planification d'équipes, profil camion/carburant, Operations Center V2, conflits, surcharges de droits et intégrations TAKATAK si toujours au périmètre.
6. Décider si les demandes du nouveau formulaire doivent devenir des commandes/réservations WooCommerce via une API/contrat confirmé, sans doublons et sans créer de CRM maître parallèle.

## Règles de continuité

Toujours inspecter HEAD, les workflows, issues et rapports précédents. Préserver les données des clients et le contrat TAKATAK : isoler les marchands par tenant, vérifier les droits côté serveur, ne jamais désactiver RLS, ne pas activer Stripe sans validation du périmètre. Ne jamais considérer une écriture GitHub, un ZIP CI ou un transfert SSH comme une preuve de mise en production fonctionnelle.
