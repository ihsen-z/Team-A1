# Site Factory

Usine à sites WordPress : un socle commun, des presets par type de projet, et
un pipeline qui transforme un brief client en site livré.

**Principe :** ce qui varie d'un client à l'autre est une *donnée*, pas du code.
La charte devient `theme.json`, la structure devient un preset, le contenu
devient du YAML. Le code, lui, est le même pour tout le monde.

## Démarrage

```bash
composer install
cp .env.example .env          # base de données, compte admin
cp clients/_example/client.yml clients/mon-client/client.yml
```

Puis, dans Claude Code :

```
/kickoff clients/mon-client        # cadrage → plan à valider
# tu relis le plan, tu passes status à « approved »
/new-site clients/mon-client       # génération
```

Le détail : [`docs/WORKFLOW.md`](docs/WORKFLOW.md).

## Organisation

| Dossier | Rôle |
|---|---|
| `governance/` | Matrice de décision, registre des agents, schéma du brief |
| `presets/` | vitrine · business · ecommerce |
| `packages/` | `theme-core` (socle) et `blocks` (bibliothèque partagée) |
| `tooling/` | Pipeline ; `adapters/` est le seul endroit qui connaît WP-CLI |
| `clients/` | Un dossier par projet : brief, plan, contenu, build |
| `decisions/` | ADR — pourquoi chaque choix a été fait |
| `.claude/` | Skills, subagents, hooks de qualité |

## Les trois règles qui tiennent le système

1. **La stack n'est jamais choisie librement.** Elle est déterminée par
   `governance/decision-matrix.yml`. Hors matrice → escalade.
2. **Un plan non approuvé ne s'exécute pas.** Le `status: approved` est la
   signature humaine, et aucun script ne l'écrit.
3. **Rien n'est inventé.** Ni un fait client, ni un bloc, ni une extension.
   Ce qui manque est signalé, pas comblé.

Stack actuelle : WordPress classique + WP-CLI. Bedrock est prévu via la couche
d'adaptateurs — voir [`decisions/0001`](decisions/0001-wordpress-classique-wp-cli.md).
