# Cahier des charges — Usine à sites

> Document de référence du système. Décrit ce qu'il fait, ce qu'il ne fait pas,
> et les contraintes qu'il s'impose. Toute évolution qui contredit une exigence
> marquée **[NN]** passe par un ADR dans `decisions/`.

---

## 1. Contexte et objectif

### 1.1 Situation

Atelier WordPress produisant 3 à 8 sites par mois : vitrines, sites business et
boutiques WooCommerce. Développement en thème bloc custom, sans page builder.

Le constat qui fonde le projet, mesuré sur deux sites existants : les thèmes
partagent l'**architecture** mais pas le **code**. `inc/product.php` fait
910 lignes sur un site et 933 sur l'autre, avec 18 lignes identiques. 382
fonctions au total, toutes préfixées par site, pour des responsabilités
identiques.

La convention maison existe donc déjà. Elle est simplement réécrite à chaque
projet.

### 1.2 Objectif

Transformer une production artisanale — chaque site est un projet unique — en
chaîne de production : un socle commun, des presets par type de site, et un
pipeline qui transforme un brief client en site livré.

### 1.3 Ce que le système n'est pas

- Ce n'est pas un générateur de sites « à la demande » pour les clients finaux.
- Ce n'est pas un produit revendable en l'état.
- Ce n'est pas un remplacement du jugement de l'artisan : la relation client,
  l'arbitrage UX métier et la validation avant mise en ligne restent humains.

---

## 2. Principe directeur

**Ce qui varie d'un client à l'autre est une donnée, pas du code.**

| Ce qui varie | Forme |
|---|---|
| Charte graphique | `theme.json` généré depuis le brief |
| Structure du site | preset (`vitrine`, `business`, `ecommerce`) |
| Contenu | YAML dans `clients/<slug>/content/` |
| Choix de la stack | règle dans `governance/decision-matrix.yml` |

Un site qui exige du code sur mesure est un signal d'escalade, pas une
invitation à improviser.

---

## 3. Exigences fonctionnelles

### 3.1 Gouvernance

**[F01]** La stack d'un projet est déterminée par application de
`governance/decision-matrix.yml`. Aucun agent ne la choisit librement.

**[F02]** Quand aucune règle ne correspond, ou que la confiance de
classification est inférieure à 0,75, le système **escalade** vers l'humain
avec une recommandation argumentée et au moins deux options.

**[F03]** Chaque arbitrage rendu par l'humain devient une règle dans la matrice
et un ADR dans `decisions/`. Le système apprend dans des fichiers relus.

**[F04]** Chaque agent a des droits d'écriture explicites dans
`governance/agents-registry.yml`. Un agent de contenu ne peut pas modifier du
PHP.

**[F05]** Un seul agent accède à la production (`deploy-agent`), et uniquement
après validation humaine.

### 3.2 Point de contrôle humain

**[F06]** Le chef de projet produit un plan en `status: awaiting_human_approval`.
Aucune étape du pipeline ne s'exécute avant que l'humain ait écrit `approved`.

**[F07]** Aucun script n'écrit ce champ. `require_approved_plan()` le vérifie à
chaque entrée de pipeline.

### 3.3 Pipeline de génération

**[F08]** Le brief client (`client.yml`) est le seul artefact saisi à la main.
Il est validé contre `governance/client.schema.yml` avant toute exécution.

**[F09]** Les étapes sont : scaffold → design → contenu → composition → import
→ QA → audit sécurité → déploiement.

**[F10]** Chaque étape est **idempotente** : une relance sur un site à moitié
construit ne casse rien.

**[F11]** Aucun script n'appelle `wp` directement. Tout passe par un adaptateur
implémentant `tooling/adapters/INTERFACE.md`, pour qu'une autre stack (Bedrock)
soit un ajout et non une réécriture.

### 3.4 Bibliothèque de blocs

**[F12]** Un besoin sans bloc correspondant ne se résout **jamais** en HTML
inline ni en `core/html`. Soit il se compose avec des blocs existants, soit il
devient un bloc de `packages/blocks/`, soit il escalade.

**[F13]** Les blocs disponibles sont restreints à ceux du preset, **y compris
dans l'éditeur du client** (`allowed_block_types_all`).

**[F14]** `packages/theme-core` est identique sur tous les sites. Un besoin
client est soit un token, soit un bloc, soit une escalade.

### 3.5 Contenu

**[F15]** Aucun fait n'est inventé : prix, horaires, certifications,
témoignages, chiffres. Ce qui manque devient `[[À VALIDER: ...]]`.

**[F16]** Une donnée absente **supprime** le composant plutôt que d'afficher un
exemple. Un cadre vide en production vaut mieux qu'une promesse fausse.

---

## 4. Exigences non fonctionnelles

### 4.1 Sécurité

**[S01]** Toute sortie est échappée au point d'émission.

**[S02]** Toute action mutative vérifie un nonce **et** une capability.

**[S03]** Toute entrée est assainie avant traitement (`wp_unslash()` puis
assainissement typé).

**[S04]** Tout `phpcs:ignore` porte une justification sur la même ligne.

**[S05]** L'audit de sécurité est **bloquant** avant mise en ligne, et conduit
par un agent en lecture seule : celui qui corrige ne juge pas si c'est corrigé.

**[S06]** Aucun secret ne transite par le dépôt. Les fichiers `*env.php`,
`wp-config.php`, dumps SQL et clés sont exclus des imports, et un contrôle de
fuite bloque le commit.

### 4.2 Accessibilité

**[A01]** Cible WCAG 2.2 AA sur tous les sites, y compris les vitrines.

**[A02]** Hiérarchie des titres jamais cassée : un seul `h1`, aucun saut de
niveau. Les blocs à niveau paramétrable sont bornés.

**[A03]** Focus visible sur tout élément interactif.

**[A04]** Le générateur **refuse** de produire un jeton marqué « texte » sous
un contraste de 4,5:1. Une dérogation exige une mention explicite et datée dans
le brief. Documenter l'écart après coup ne suffit pas.

**[A05]** Aucune information portée par la seule couleur. Les animations sont
sous `prefers-reduced-motion`.

### 4.3 Performance

**[P01]** Seuils Lighthouse par preset, vérifiés en QA et bloquants :

| Preset | Performance | Accessibilité | SEO |
|---|---|---|---|
| vitrine | 90 | 95 | 95 |
| business | 88 | 95 | 95 |
| ecommerce | 80 | 95 | 95 |

**[P02]** Une seule image par page porte `fetchpriority="high"`. C'est un
service du socle, pas une décision de bloc.

**[P03]** Aucune police d'icônes ni image pour les éléments décoratifs
répétés : masque SVG et variables CSS.

### 4.4 Qualité de code

**[Q01]** PHP 8.1+, `declare(strict_types=1)`, WordPress Coding Standards.

**[Q02]** Les hooks `PostToolUse` lancent `php -l`, `phpcs` et `phpstan` après
chaque édition. Leur sortie ne se contourne pas.

**[Q03]** CSS : uniquement des tokens `var(--wp--preset--*)`. Aucune valeur en
dur.

**[Q04]** Tout texte est traduisible, domaine `factory-core`.

### 4.5 Traçabilité

**[T01]** Chaque décision structurante est un ADR dans `decisions/`, jamais
réécrit : une décision remplacée est marquée, pas effacée.

**[T02]** Les conventions durables sont consignées dans `CLAUDE.md`, **avec
leur raison**. Une règle sans motif ne se retient pas et finit contournée.

---

## 5. Contraintes

### 5.1 Périmètre technique

- **Aucun page builder** (Elementor, Divi, WPBakery) : le contenu devient
  illisible hors du builder et l'automatisation par agents devient impossible.
- **Aucune extension** hors de la liste `required`/`optional` du preset.
- Stack actuelle : WordPress classique + WP-CLI. Bedrock est reporté
  (voir `decisions/0001`), sa stack est marquée `enabled: false` et toute règle
  qui y pointe escalade.

### 5.2 Économiques

Seuils de rentabilité par type, sous lesquels le projet escalade :
vitrine 1 200 €, business 2 500 €, ecommerce 4 500 €. *À ajuster : ce sont des
valeurs de départ, pas des mesures.*

Plafonds de jetons par projet, avec alerte à 80 % : 800 k (vitrine),
1,5 M (business), 3 M (ecommerce).

### 5.3 Légales

- RGPD : les données des clients finaux ne transitent jamais par le dépôt.
- E-commerce FR : CGV, mentions légales, politique de confidentialité, droit de
  rétractation 14 jours et bandeau cookies conforme sont **bloquants** avant
  mise en ligne.

---

## 6. Architecture

```
governance/   décision de stack · registre des agents · schéma du brief
presets/      vitrine · business · ecommerce
packages/     theme-core (socle) + blocks (bibliothèque partagée)
tooling/      pipeline ; adapters/ = seul endroit qui connaît WP-CLI
clients/      un dossier par projet : brief, plan, contenu, build
decisions/    ADR
imports/      sites existants extraits, pour alimenter la bibliothèque
docs/         ce document, workflow, inventaire, feuille de route
```

### 6.1 Rôles des agents

| Agent | Modèle | Écrit dans | Bloquant |
|---|---|---|---|
| `project-manager` | opus | plan, ADR | — |
| `design-tokens` | opus | `build/theme.json` | — |
| `content-writer` | sonnet | `content/**.yml` | — |
| `block-composer` | opus | `build/pages/` | — |
| `woo-specialist` | opus | `build/woo/` | — |
| `qa-visual` | sonnet | `reports/` | oui |
| `wp-security-reviewer` | opus | `reports/` (lecture seule sur le code) | oui |
| `deploy-agent` | opus | prod, après validation humaine | — |
| `maintenance-agent` | sonnet | `reports/maintenance/` | — |
| `memoire` | sonnet | `CLAUDE.md` | — |

Le chef de projet est la **session principale**, pas un subagent : un subagent
ne peut pas déléguer de façon fiable.

---

## 7. Critères d'acceptation

Le système est considéré comme opérationnel quand :

1. Un site vitrine complet est produit depuis un `client.yml` avec moins de
   20 % d'intervention manuelle, hors contenu factuel à collecter.
2. Les seuils qualité du preset sont atteints sans retouche.
3. L'audit de sécurité ne remonte aucun finding bloquant.
4. Un second site du même type réutilise le socle sans le modifier.
5. La reprise d'un site généré par un autre prestataire est possible : le code
   est standard, documenté, et sans dépendance propriétaire.

---

## 8. Hors périmètre, assumé

| Exclu | Raison |
|---|---|
| Relation client, devis, facturation | Ce n'est pas un problème technique |
| Arbitrage UX métier | Demande la connaissance du client, pas du code |
| Validation finale avant mise en ligne | L'IA ne voit pas qu'une photo montre le mauvais magasin |
| Design original « inventé » | L'IA décline un système, elle ne le crée pas |
| Marketplace, ERP, app mobile, SSO | Hors du modèle économique de l'atelier |
