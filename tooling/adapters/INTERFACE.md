# Contrat des adaptateurs de stack

Aucun script du pipeline n'appelle `wp`, `composer` ou `git` directement.
Tout passe par un adaptateur, qui implémente le contrat ci-dessous.

C'est ce qui permet d'ajouter **Bedrock + Composer + Git** plus tard sans
réécrire le pipeline : tu écris `bedrock.sh`, tu actives la stack dans
`governance/decision-matrix.yml`, et les sites existants ne bougent pas.

## Fonctions à implémenter

| Fonction | Rôle | Doit être idempotente |
|---|---|---|
| `adapter_name` | Renvoie l'identifiant de l'adaptateur | — |
| `adapter_preflight` | Vérifie les prérequis (binaires, accès, versions) | oui |
| `adapter_bootstrap <dir> <url> <title> <admin_user> <admin_email>` | Installe le cœur WordPress et le rend accessible | oui |
| `adapter_install_plugins <dir> <slug...>` | Installe + active les extensions | oui |
| `adapter_remove_plugins <dir> <slug...>` | Désinstalle les extensions interdites | oui |
| `adapter_install_theme <dir> <theme_src>` | Déploie le thème et l'active | oui |
| `adapter_set_options <dir> <key=value...>` | Applique les réglages WordPress | oui |
| `adapter_import_page <dir> <slug> <title> <html_file>` | Crée ou met à jour une page | oui |
| `adapter_set_front_page <dir> <slug>` | Définit la page d'accueil | oui |
| `adapter_flush <dir>` | Vide caches et permaliens | oui |
| `adapter_backup <dir> <dest>` | Sauvegarde fichiers + base | — |
| `adapter_export_db <dir> <dest>` | Exporte la base | — |
| `adapter_search_replace <dir> <from> <to>` | Réécrit les URLs (migration) | — |

## Règles

1. **Idempotence obligatoire** là où c'est indiqué. Le pipeline doit pouvoir être
   relancé sur un site à moitié construit sans rien casser.
2. **Aucun effet de bord hors de `<dir>`.** Un adaptateur ne touche jamais la
   production sans passer par `deploy.sh`.
3. **Échec bruyant.** En cas de doute, `die` — jamais de continuation silencieuse.
4. Chaque fonction écrit ce qu'elle fait via `log` / `ok` / `warn`.

## Adaptateurs

| Fichier | Stack | État |
|---|---|---|
| `classic.sh` | WordPress classique + WP-CLI | **actif** |
| `bedrock.sh` | Bedrock + Composer + Git | à écrire (voir `decisions/0001`) |
