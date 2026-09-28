# Inventaire des blocs à construire

Issu de l'analyse des sites existants (`imports/`). Ce fichier est la source
de vérité de ce qui reste à écrire dans `packages/blocks/`.

> État : evasions analysé · famma en cours.

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
