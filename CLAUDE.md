# Usine à sites — conventions de l'atelier

Dépôt de production d'un atelier WordPress : un socle commun, des presets par
type de site, et un pipeline qui transforme un brief client en site livré.

**Principe directeur : ce qui varie d'un client à l'autre est une *donnée*, pas
du code.** La charte devient `theme.json`, la structure devient un preset, le
contenu devient du YAML. Un site qui exige du code sur mesure est un signal
d'escalade, pas une invitation à improviser.

## Stack actuelle

WordPress classique piloté par WP-CLI, thème bloc custom (`packages/theme-core`),
pas de page builder. Bedrock + Composer + Git est prévu plus tard : c'est
pourquoi **aucun script n'appelle `wp` directement** — tout passe par
`tooling/adapters/`. Voir `decisions/0001`.

## Règles non négociables

### Sécurité PHP
- Toute sortie est échappée au point d'émission : `esc_html()`, `esc_attr()`,
  `esc_url()`, `wp_kses_post()`. Jamais d'`echo` d'une variable brute.
- Toute entrée est assainie : `sanitize_text_field()`, `absint()`,
  `sanitize_key()`, `wp_unslash()` avant traitement.
- Toute action mutative vérifie un nonce **et** une capability. Les deux, pas l'un.
- Requêtes SQL via `$wpdb->prepare()` — jamais d'interpolation.
- Un `phpcs:ignore` doit porter une justification sur la même ligne.

### Périmètre
- **Aucune extension hors de la liste `required`/`optional` du preset.** Un
  besoin non couvert → escalade, pas une installation opportuniste.
- **Aucun page builder** (Elementor, Divi, WPBakery). On fait du bloc natif.
- **Aucun bloc inventé en HTML inline.** Un besoin sans bloc → on crée un bloc
  dans `packages/blocks/` via `/new-block`, réutilisable par tous les clients.
- `packages/theme-core` est identique sur tous les sites. Un besoin client qui
  voudrait le modifier est soit un token, soit un bloc, soit une escalade.

### Contenu
- Aucun fait inventé : prix, horaires, certifications, témoignages, chiffres.
  Ce qui manque devient `[[À VALIDER: ...]]` dans le YAML, jamais une invention
  plausible. Un faux horaire publié coûte la confiance du client.
- Tout texte est traduisible : `__()`, `esc_html__()`, domaine `factory-core`.

### Accessibilité
- Cible WCAG 2.2 AA sur tous les sites, y compris les vitrines.
- Hiérarchie des titres jamais cassée (un seul `h1`, pas de saut de niveau).
- Focus visible sur tout élément interactif. Contraste vérifié sur la charte
  *avant* de générer `theme.json`.

### Conventions de code
- PHP 8.1+, `declare(strict_types=1)`, `defined('ABSPATH') || exit;` en tête.
- WordPress Coding Standards (`composer lint`). Les hooks les lancent
  automatiquement après chaque édition PHP — lis leur sortie, ne la contourne pas.
- Préfixe `factory_` pour les fonctions, `factory/` pour les blocs,
  `.factory-*` pour les classes CSS.
- CSS : uniquement des tokens `var(--wp--preset--*)`. Aucune valeur en dur.
- Toute URL dans `url()` passe entre guillemets : `url("...")`. `esc_url_raw()`
  encode les guillemets mais pas les parenthèses : sans guillemets, une image
  nommée `photo(1).jpg` ferme `url()` prématurément.

### Outillage
- Dans les scripts, tout `find` qui lit une source WordPress utilise `find -L`.
  Un dossier de `wp-content` est souvent un lien symbolique (ex. `mu-plugins`
  vers un autre disque) : quand le point de départ est un lien, `find` le traite
  comme un fichier et renvoie zéro résultat, alors que `[[ -d ]]` et `ls`
  déréférencent. Le garde passe, la recherche est vide, rien n'explique pourquoi.
- Toute copie par `tar` utilise `tar -h` : sans lui l'archive ne contient que le
  lien, inutilisable une fois importée ailleurs.

## Structure

```
governance/     décision de stack, registre des agents, schéma du brief
presets/        vitrine | business | ecommerce (plugins, blocs, gates qualité)
packages/       theme-core (socle) + blocks (bibliothèque partagée)
tooling/        pipeline ; adapters/ = seul endroit qui connaît WP-CLI
clients/<slug>/ brief, plan approuvé, contenu, build
decisions/      ADR — pourquoi chaque choix technique a été fait
reports/        sorties QA, sécurité, maintenance
```

## Flux de travail

1. `/kickoff clients/<slug>` — classifie, applique la matrice, produit un plan.
2. **Tu relis le plan et passes `status` à `approved`.** Rien ne s'exécute avant.
3. `tooling/scaffold.sh` → `design.sh` → contenu → composition → `qa.sh`
4. Audit sécurité (bloquant) → déploiement (validation humaine).

Le détail est dans `docs/WORKFLOW.md`.

## Escalade

Un agent qui rencontre l'un de ces cas **s'arrête et demande** — il ne tranche pas :
- aucune règle de la matrice ne matche le brief ;
- un besoin exige une extension, un bloc ou une techno hors périmètre ;
- le budget est sous le seuil de rentabilité ;
- une donnée factuelle manque et serait à inventer ;
- une contrainte légale ou réglementaire spécifique apparaît.

Chaque arbitrage rendu devient une règle dans `governance/decision-matrix.yml`
et un ADR dans `decisions/`. Le système apprend dans des fichiers relus, pas
dans un contexte opaque.
