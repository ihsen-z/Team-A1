# Inventaire des blocs à construire

Issu de l'analyse des sites existants (`imports/`). Ce fichier est la source
de vérité de ce qui reste à écrire dans `packages/blocks/`.

> État : evasions et famma analysés.

---

## Ce que les deux sites ont en commun

C'est le critère de priorité : un composant présent dans **les deux** thèmes
est déjà prouvé réutilisable. Il passe avant tout le reste.

| Besoin | evasions | famma | Bloc |
|---|---|---|---|
| Barre de promesses | `topbar.php` | `header-parts.php` + `shop.php` | `factory/promise-bar` |
| Réassurance | `benefits.php`, `why.php` | `famma_child_reassurance_items()` | `factory/arguments-band` |
| Newsletter | `community.php` | `home-social.php` | `factory/newsletter-panel` |
| Découpe d'un document légal | `evasions_split_by_heading()` | `famma_child_document_sections()` | `factory/doc-sections` |
| Tuiles de catégories | `universes.php` | carrousel de rayons | `factory/taxonomy-tiles` |
| Carte produit | `product-card.php` ×3 | `inc/shop.php` | `factory/product-card` |
| Registre d'icônes | `inc/icons.php` | `inc/icons.php` + allowlist SVG | service de socle |
| WhatsApp | `Evasions\Core\WhatsApp` | `Famma\Core\WhatsApp` | service optionnel |

Les deux sites ont **résolu les mêmes problèmes séparément**. Aucun n'a copié
l'autre : c'est la définition d'un besoin générique.

---

## La convention d'atelier existe déjà

Les deux thèmes appliquent, chacun dans son coin, la règle que le socle
formalise : **rien n'est inventé, une donnée absente supprime le bloc plutôt
que d'afficher un exemple.** famma la cite explicitement (« §58, §60 »).

Les deux séparent aussi la copie commerciale du thème, dans un plugin
compagnon (`evasions-core`, `famma-core`, tous deux en 0.2.0), avec accès
gardé par `class_exists()` et repli silencieux.

C'est le modèle le plus directement transposable — et la principale dépendance
qui empêche de reprendre les composants tels quels.

---

## Blocs retenus

Six blocs remplacent quatorze fichiers d'evasions. Chacun absorbe plusieurs
composants qui n'étaient distincts que par accident.

| Bloc | Absorbe (evasions) | Vague |
|---|---|---|
| `factory/hero` *(étendre l'existant)* | `hero.php`, `page-banner.php`, bannière packs, hero d'univers | 1 |
| `factory/cta-band` *(étendre l'existant)* | `banner.php`, `product-cta.php`, éditorial d'univers | 1 |
| `factory/arguments-band` *(généralise services-grid)* | `benefits.php`, `why.php`, réassurance produit | 1 |
| `factory/newsletter-panel` | `community.php` | 1 |
| `factory/doc-sections` | `page-faq.php`, `page-cgv.php` | 2 |
| `factory/taxonomy-tiles` | `universes.php` + tuiles | 2 |
| `factory/reviews-wall` | `reviews.php` | 2 |
| `factory/product-card` | `product-card.php`, `-compact.php`, `listing-item.php` | 3 |
| `factory/product-grid` | `featured-products.php`, grille packs, sélection d'univers | 3 |

**Vague 1** : aucune dépendance métier, gains immédiats.
**Vague 2** : valeur forte, mais la source de données doit être refondue.
**Vague 3** : dépend de WooCommerce — à isoler dans un paquet séparé.

### Preuves que certains composants sont le même

- `why.php` retombe explicitement sur `evasions_benefits()` quand aucun item
  n'est rempli. Le code dit lui-même que c'est le même composant.
- `banner.php` est `product-cta.php` amputé : même structure, mais l'URL du
  bouton y est codée en dur au lieu d'être éditable.

### Apports propres à famma (e-commerce)

| Composant | Fichier | Valeur | Forme cible |
|---|---|---|---|
| Tunnel d'achat (fil d'Ariane, stepper, réassurance) | `inc/tunnel.php` | ⭐⭐⭐⭐⭐ | Bloc + réglage partagé |
| Checkout en cartes numérotées + aside récap | `inc/checkout-layout.php` | ⭐⭐⭐⭐⭐ | Module PHP, pas un bloc |
| Colonne de filtres (5 widgets) | `inc/shop-widgets.php` | ⭐⭐⭐⭐ | Module e-commerce |
| États vides (catalogue, filtres, panier) | `no-products-found.php` | ⭐⭐⭐⭐ | `factory/empty-state` |
| Onglets produit (specs / FAQ / livraison) | `inc/product.php` | ⭐⭐⭐⭐ | Module + bloc FAQ |
| Sélecteur de quantité − / + | `product-quantity.js` | ⭐⭐⭐ | Module e-commerce |

Le checkout et le tunnel sont à 100 % WooCommerce, sans aucune adhérence à
Kadence : c'est le plus gros gain immédiat sur le prochain client marchand.

---

## La leçon sur `theme.json`

famma possède un `theme.json`, mais **il n'est pas la source de vérité** :
toutes ses valeurs sont des indirections `var(--…)` vers un `tokens.css` de
314 lignes écrit à la main et vers les variables de Kadence. Conséquences
observées :

- l'éditeur affiche des pastilles vides ou noires (aucun `add_editor_style()`
  sur le fichier de jetons) ;
- 15 entrées `theme-paletteN` sont un artefact Kadence pur, sans aucun sens
  dans un thème maison ;
- 60+ jetons réels (`--famma-surface-*`, élévations, durées, z-index, rayons)
  sont invisibles de `theme.json`.

**Le socle fait déjà l'inverse, et c'est le bon sens** : `render-theme-json.php`
écrit des valeurs littérales dans `theme.json`, et le CSS ne référence que des
`var(--wp--preset--*)`. Rien à changer — mais il faut ajouter
`add_editor_style()` pour que l'éditeur voie la même chose que le front.

### Ce que `tokens.css` fait mieux que le socle

Chaque couleur y porte **son ratio de contraste mesuré et son usage autorisé
en commentaire** :

```css
--famma-orange: #FF7A00;     /* SURFACE uniquement — fond de CTA, badges */
--famma-orange-700: #B85300; /* TEXTE sur fond clair · 4,91:1 */
```

Cinq jetons y sont documentés comme **non conformes AA sur décision client
explicite et datée** (le plus bas à 2,58:1). Le `CLAUDE.md` du socle vise AA
sans exception.

**Décision retenue** : le générateur doit *refuser* de produire un jeton marqué
« texte » sous 4,5:1, et exiger une dérogation explicite, datée et signée dans
le brief client pour passer outre — plutôt que de documenter l'écart après
coup. La pratique de famma est bonne ; il lui manque d'être bloquante.

---

## À remonter dans `packages/theme-core`

Ce ne sont pas des blocs mais des services, déjà bien faits dans les sites
existants et qui n'ont rien à faire dupliqués par client.

| Service | Origine | Pourquoi |
|---|---|---|
| Découpe de contenu par niveau de titre | `evasions_split_by_heading()` | L'admin écrit une page normale, zéro donnée dupliquée. La meilleure idée du thème. |
| Politique « aucun lien mort » | `evasions_page_link()` | Vérifie `post_status === 'publish'` avant d'afficher un lien. Une page absente disparaît au lieu de produire un 404. |
| Garde-fou de configuration | `community.php` | L'avertissement « formulaire non configuré » ne s'affiche qu'aux `manage_options`. |
| Une seule image prioritaire par page | `evasions_listing_item_rank()` | `fetchpriority`/preload : c'est un service global, pas un attribut de bloc. |
| Libellés en attributs `data-` | `evasions_readmore_attrs()` | Le JS n'a pas de canal de traduction ; faire voyager les libellés en `data-` est le bon contournement. |
| Étoiles de notation | `evasions_stars()` + `--ev-rating` | Remplissage en pourcentage, sans image ni police d'icônes. Utilisé par trois composants. |
| Panneau de filtres ouvert par `:target` | `#ev-filters` | Fonctionne sans JavaScript. À documenter. |
| Garde-fous éditoriaux | `famma/inc/editorial-guards.php` | Retire les notes `famma-todo` du rendu et les rappelle à l'admin. C'est le pendant runtime du `[[À VALIDER]]`. À écrire via `render_block`, pas en regex. |
| Allowlist SVG pour `wp_kses` | `famma_child_svg_allowed_html()` | Permet aux blocs de rendre du SVG sans `phpcs:ignore`. |
| Enqueue avec `filemtime()` + garde `file_exists()` | `famma/inc/assets.php` | Convention de cache-busting, systématique. |
| Réassurance à source unique | `famma_child_reassurance_items()` | « Trois listes écrites à la main finiraient par promettre trois choses différentes. » |

---

## Verrous de réutilisation identifiés

Par gravité décroissante.

| Où | Quoi | Gravité |
|---|---|---|
| `evasions_universes()` | Tableau codé en dur (slugs `plage`, `camping-randonnee`, titres, images), **consommé à 5 endroits** : tuiles, footer, page d'univers, 404, navigation | **bloquant** |
| `evasions_nav_items()` | Mêmes slugs, dupliqués | élevée |
| `evasions_footer_columns()` | 9 slugs de pages en dur — devrait venir de `wp_nav_menu` | élevée |
| `hero.php` | URL du CTA secondaire → `evasions_category_url('camping-randonnee')` | élevée |
| `evasions_benefits()` + `evasions_single_reassurance()` | Les mêmes promesses commerciales écrites **deux fois** en dur | moyenne |
| `page-banner.php` | Tout en dur : titres, sous-titres, images. Seul template-part qui viole la règle que le thème s'impose ailleurs | moyenne |
| `inc/brand.php` | `#165A32` en dur **deux fois**, alors que `--ev-forest` porte la même valeur | contradiction directe |

La donnée doit venir de la taxonomie. `evasions_universe_hero()` fait déjà
exactement cela correctement, dans le même thème — le bon modèle est à côté
du mauvais.

---

## Sécurité des sites existants

Aucune sortie non échappée, aucune injection SQL, aucun `wp_kses` manquant.
Les 47 `phpcs:ignore` d'evasions sont justifiés en commentaire et vérifiés.

Remarques mineures, aucune exploitable — à corriger dans les sites sources,
pas ici :

1. `$_GET` lu sans `wp_unslash()` (`listing-filters.php:21`, `inc/listing.php:187`) —
   les clés servent à retirer des paramètres, impact nul, mais incohérent avec
   la ligne du dessus qui nettoie.
2. `evasions_carried_params()` recopie tout paramètre inconnu dans des champs
   cachés : échappé, donc pas de XSS, mais liste noire là où une liste blanche
   serait plus sûre.
3. `evasions_filter_keys()` accepte toute clé `filter_*` sans vérifier qu'une
   taxonomie correspond.
4. `product-card-compact.php` écrase `$GLOBALS['post']` puis le restaure —
   correct, sauf si une exception survient entre les deux.

### famma — à corriger sur le site en production

Vérifiés dans le code, par gravité décroissante. Aucun n'est une faille
exploitable, mais les deux premiers méritent une correction.

1. **Formulaire de retours sans nonce** — `page-retours.php:220-277`. Un
   `<form method="post">` public sans `wp_nonce_field()`. Le traitement est
   dans `famma-core`, absent du dépôt : impossible de vérifier d'ici si un
   nonce y est contrôlé. Contraire à la règle « nonce **et** capability ».
   **À auditer côté plugin.**
2. **Sortie non échappée déléguée au plugin** — `inc/product-video.php:75`,
   `echo $player`. Le commentaire affirme que la source échappe ; non
   vérifiable depuis le thème. Le socle imposera un `wp_kses()` avec allowlist
   `video`/`iframe` au point d'émission, plutôt qu'une confiance contractuelle.
3. **Option lue depuis un nom de constante** — `inc/footer-parts.php:29`,
   `get_option( strtolower( $key ) )` sans `sanitize_key()` ni liste blanche.
   Tous les appelants actuels passent des littéraux : non exploitable en
   l'état, mais la fonction est publique.
4. **Paramètres de filtre en tableau** — `inc/shop-widgets.php:147` et `:538`,
   `array_map( 'sanitize_text_field', wp_unslash( (array) $_GET ) )`.
   **Ce n'est PAS une erreur fatale** : `sanitize_text_field()` n'a pas de
   déclaration de type, donc une URL `?product_cat[]=a&product_cat[]=b`
   produit au pire la chaîne `"Array"` et un avertissement. Vérifié sur
   PHP 8.4. Le filtre devient silencieusement incohérent, sans planter.
   `map_deep()` règle le cas proprement.
5. **Substitutions `preg_replace` sur le HTML de WooCommerce** — trois
   endroits. Fragile face à un changement de balisage, mais échec silencieux,
   pas une faille.
6. **Pas de `.pot`** dans `languages/` : aucune extraction documentée pour
   les nouvelles chaînes.

---

## À ne pas porter

Fiche produit COD/WhatsApp (`inc/product.php`), tunnel (`inc/cart-checkout.php`),
en-tête allégé du tunnel, et les gabarits WordPress triviaux (`404.php`,
`index.php`, `single.php`, `page.php`).

---

## Constat transverse

Les deux thèmes partagent l'architecture mais pas le code : `inc/product.php`
fait 910 lignes chez evasions et 933 chez famma, avec **18 lignes identiques**.
382 fonctions au total, toutes préfixées par site.

La convention maison existe déjà et elle est rigoureuse — même noms de
fichiers, mêmes responsabilités. Il ne reste qu'à la factoriser, ce qui est
une bien meilleure situation qu'un copier-coller anarchique.
