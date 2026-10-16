# DECISIONS — registre architectural

## D-001 — Préserver le code original (2026-10-14)
Au moment de l'initialisation, le repo ne contenait que README.md. Les sources historiques WordPress et Operations Center doivent être récupérées et inspectées avant de tenter une reprise de ces systèmes. Il serait faux d'affirmer que le code hérité a été migré.

## D-002 — Extension WordPress indépendante (2026-10-14)
Le premier livrable est une extension isolée sous plugin/ramassedebris-service, activée par shortcode. Aucun changement à WooCommerce, YITH, ni aux comptes clients. Aucune confirmation automatique de prix ou de rendez-vous.

## D-003 — Données et identité (2026-10-14)
Les demandes locales WordPress sont des enregistrements privés; elles ne sont pas synchronisées par défaut avec TAKATAK. L'identité TAKATAK reste l'autorité principale de l'écosystème et toute intégration future doit valider les entitlements et le tenant côté serveur.

## D-004 — Publication (2026-10-14)
GitHub main est la branche de publication du code. Le déploiement physique et l'activation du plugin nécessitent le chemin réel, les secrets dédiés, la préproduction et les sauvegardes. Le workflow SSH n'écrase pas WordPress et n'est pas activé automatiquement.

## D-005 — Préserver la confidentialité du courriel (2026-10-15)
Le courriel de notification ne contient que l'identifiant de la demande et un lien vers la console administrateur. Le détail personnel reste stocké dans WordPress, et non dupliqué dans la boîte aux lettres.

## D-006 — Validation testable et suppression vérifiable (2026-10-15)
La validation métier est testée comme fonction PHP. L'effacement natif WordPress rapporte les dossiers non effacés en cas d'échec. Les tests contractuels locaux ne remplacent pas une instance WordPress.

## En suspens
Hébergement de production, chemin WordPress, SMTP, sources Operations Center, contraintes métier exactes, politique de conservation et contrat d'API TAKATAK.

## D-007 — Effacement manuel contrôlé (2026-10-15)
Un dossier sans courriel ne peut pas être retrouvé par le workflow natif WordPress fondé sur une adresse validée. L'administrateur peut le supprimer dans la console du plugin après vérification externe de l'identité et une case de confirmation. L'action est protégée par capability manage_options, POST, nonce et vérification du type de publication; aucune suppression générique hors du plugin.

## D-008 — Effacement par lots résilient (2026-10-15)
Traitement d'au plus cinq lots de 25 dossiers par invocation WordPress. La première page est réinterrogée après suppression; les dossiers non supprimables sont temporairement exclus du reste du même lot afin que les autres puissent être effacés. Les opérations plus grandes continuent à l'appel suivant. Toute défaillance est signalée.

## D-009 — Déployer après validation, pas en parallèle (2026-10-15)
Le workflow de production passe d'un déclencheur direct `push` à `workflow_run` réussi du workflow de validation sur `main`, seulement si `RDB_AUTO_DEPLOY=true`; une exécution manuelle revalide localement et exige que le SHA déployé soit le HEAD actuel de main. Le transfert compare les sommes SHA-256 du PHP, du CSS et du JavaScript. Aucune activation WordPress automatique n'est supposée.

## D-010 — Jeton anti-CSRF et caches WordPress (2026-10-15)
Une page publique mise en cache peut servir un nonce WordPress expiré. La version 0.4.0 demande via POST AJAX un jeton frais juste avant la soumission lorsque JavaScript est présent. Ce endpoint public ne vérifie pas l'identité et ne remplace ni les limites de débit ni un contrôle anti-abus serveur. Le formulaire natif demeure possible sans JavaScript.

## D-011 — Tester les comportements critiques sans base client (2026-10-15)
Le gestionnaire de formulaire public est vérifié dans des processus PHP isolés au moyen de doubles d'interfaces WordPress. Les scénarios évaluent validation, confidentialité et échecs d'infrastructure sans manipuler de demandes réelles. Ce complément à la CI ne suffit pas à certifier la compatibilité WordPress complète en production.

## D-012 — Capacités WordPress privées dédiées (2026-10-15)
Pour éviter qu'un rôle éditeur possédant `read_private_posts` n'accède aux dossiers de ramassage au moyen d'extensions tiers ou d'appels WordPress, le CPT mappe toutes ses capacités de lecture, modification et suppression à `manage_options` et interdit la création via l'interface de publications standard. Ce changement ne doit jamais étendre les droits des autres rôles. Les hooks de gestion du formulaire public, les outils de confidentialité et les tâches Cron conservent leurs actions directes autorisées.

## D-013 — Recherche interne seulement (2026-10-15)
La recherche de coordonnées des demandes est limitée à l'interface d'administration déjà protégée par `manage_options` et utilise des clauses `WP_Query` sur métadonnées. Aucun endpoint public ni index de recherche du site n'est exposé.
