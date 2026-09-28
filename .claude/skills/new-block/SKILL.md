---
name: new-block
description: Crée un nouveau bloc Gutenberg dans la bibliothèque partagée (packages/blocks) au pattern maison — block.json, render.php sécurisé, style.css sur tokens. Utiliser quand un besoin client n'est couvert par aucun bloc existant, plutôt que d'écrire du HTML inline.
---

# Nouveau bloc de la bibliothèque

Argument : le nom du bloc en kebab-case (ex. `pricing-table`).

Un bloc créé ici sert **tous** les clients. C'est ce qui fait qu'un besoin
ponctuel devient un actif réutilisable au lieu d'une dette.

## Avant d'écrire

1. **Vérifie qu'il n'existe pas déjà**, sous un autre nom : liste
   `packages/blocks/` et lis les `block.json`. Un bloc dupliqué est pire que
   pas de bloc.
2. **Vérifie qu'un bloc natif ne suffit pas.** `core/columns` + `core/group`
   couvrent beaucoup. On ne crée un bloc que pour un motif récurrent et cadré.
3. Si le besoin est propre à un seul client et ne se reproduira pas, dis-le :
   c'est peut-être une composition de blocs existants, pas un bloc.

## Fichiers à produire

`packages/blocks/<nom>/` avec exactement :

- **`block.json`** — `apiVersion: 3`, nom `factory/<nom>`, `textdomain:
  factory-core`, attributs typés avec valeurs par défaut, `supports` explicites,
  `"render": "file:./render.php"`, `"style": "file:./style.css"`.
- **`render.php`** — `declare(strict_types=1)`, `defined('ABSPATH') || exit;`,
  en-tête `@package FactoryCore`. Lis les attributs avec un défaut et un cast,
  **échappe chaque sortie**, utilise `get_block_wrapper_attributes()`, et
  retourne `''` si les données obligatoires manquent (un bloc vide ne doit
  jamais rendre une coquille cassée en production).
- **`style.css`** — uniquement des `var(--wp--preset--*)`. Aucune couleur,
  taille ou espacement en dur. Responsive sans media query quand c'est possible
  (`grid-template-columns: repeat(auto-fit, minmax(min(NNNpx, 100%), 1fr))`).

Prends `packages/blocks/hero/` comme référence de style.

## Accessibilité — à traiter, pas à cocher

- Élément sémantique approprié (`section`, `article`, `aside`, `ul role="list"`).
- Niveau de titre **paramétrable et borné**, pour ne pas casser la hiérarchie
  de la page d'accueil.
- Focus visible sur tout interactif. Pas d'information portée par la seule couleur.
- Animations sous `@media (prefers-reduced-motion: no-preference)`.

## Pour finir

1. `php -l` sur `render.php`, validité JSON du `block.json`.
2. `composer lint` sur le dossier du bloc.
3. Ajoute le bloc aux `blocks_allowed` des presets **où il a du sens** — pas
   partout par défaut.
4. Rends compte : ce que fait le bloc, ses attributs, les presets où tu l'as
   ajouté, et ce que tu as délibérément laissé de côté.
