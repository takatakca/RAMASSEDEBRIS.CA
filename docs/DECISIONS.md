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
