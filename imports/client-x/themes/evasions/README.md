# Thème EVASIONS

Thème WordPress/WooCommerce de la boutique EVASIONS, implémenté d'après la
maquette **EVASIONS Homepage** (mobile 390 px, desktop 1440 px).

Il ne fait que de la **présentation**. La logique métier (commande COD, checkout,
tracking, SEO, prix TND) vit dans le plugin `evasions-core` : changer de thème ne
détruit rien (§26 du document directeur).

## Installation

1. Copier `themes/evasions/` dans `wp-content/themes/`.
2. Copier `plugins/evasions-core/` dans `wp-content/plugins/` et
   `plugins/evasions-mu-plugins/*.php` dans `wp-content/mu-plugins/`.
3. Activer le thème. Les catégories **Plage** (`plage`) et **Camping & Randonnée**
   (`camping-randonnee`) sont créées si elles n'existent pas.
4. WooCommerce → Réglages → Général : devise **TND**. Le plugin affiche alors
   « 79,900 TND ».

## Ce qui s'affiche, et d'où ça vient

Règle du projet (§60) : rien n'est inventé. Un bloc sans contenu réel disparaît.

| Bloc | Source | Disparaît si… |
|---|---|---|
| Barre de promesses | Plugin (« EVASIONS → Pages », éditable) | le plugin est absent : deux promesses de repli |
| Nos produits mis en avant | Produits WooCommerce : mis en avant, sinon plus vendus, sinon récents | aucun produit publié |
| Badge « −25 % » | Plugin (`\Evasions\Core\Catalog::badge()`), calculé sur les vrais prix | pas de remise, **ou plugin absent** |
| Badge « Nouveau » | Plugin : produit publié depuis moins de 30 jours (filtre `evasions_new_badge_days`) | plugin absent |
| Badge « Best-seller » | Plugin : **étiquette produit** `best-seller`, posée à la main | étiquette absente, ou plugin absent |
| Note et « (n avis) » | Avis WooCommerce | le produit n'a aucun avis |
| Avis de nos clients | Avis WooCommerce approuvés, notés 4 ou 5 | aucun avis |
| « Service client » | Numéro WhatsApp ou téléphone configuré | aucun contact configuré |
| Communauté (bas de l'accueil) | Customizer, section « Accueil — communauté » | case décochée, ou titre et bouton vides |
| Pourquoi choisir… (bas de la boutique et des catégories) | Customizer, section « Boutique — pourquoi choisir » ; à défaut, la réassurance | case décochée |
| Envoi du formulaire newsletter | Customizer, sinon `EVASIONS_NEWSLETTER_FORM_ACTION` | non renseigné : le champ s'affiche mais n'envoie nulle part (avertissement en administration) |
| Réseaux sociaux | `EVASIONS_SOCIAL_FACEBOOK` / `_INSTAGRAM` / `_TIKTOK` / `_YOUTUBE` | non renseignés |
| Liens utiles (pied de page) | Pages `a-propos`, `livraison`, `retours`, `faq`, `contact` | la page n'existe pas |
| CGV, confidentialité, mentions | Pages WooCommerce / WordPress / `mentions-legales` | la page n'existe pas |

Les pictogrammes VISA / MC / D17 du design ne sont **pas** repris : la boutique
encaisse en espèces à la livraison, elle ne doit pas laisser croire à un paiement
en ligne qu'elle ne propose pas.

## Fiche produit

Maquette « Fiche produit » implémentée par les **hooks** de WooCommerce
(`inc/product.php`) et `assets/css/product.css`, sans copier ses gabarits : une
copie ne recevrait pas les correctifs de WooCommerce.

| Élément | Source | Remarque |
|---|---|---|
| Galerie, vignettes | Galerie WooCommerce | vignettes en colonne (desktop) ou en rangée (mobile) |
| Note, « (n avis) » | Avis WooCommerce | masquée sans avis |
| Prix, ancien prix, « −25 % » | Prix réels du produit | pourcentage calculé |
| « En stock » / « Sur commande » / « Rupture » | Statut de stock | jamais la quantité (pas de compteur d'urgence) |
| Points forts (coches) | Puces de la **description courte** | sans liste, texte simple |
| Formulaire d'achat WooCommerce | Rendu, puis **masqué** quand la carte express le double | `.ev-js .ev-has-express form.cart` : sans JavaScript il réapparaît, c'est le seul chemin restant |
| Pictogrammes livraison / paiement | Appliqués par le tunnel COD | — |
| Pictogramme « Politique de retour » | Page `retours` publiée | la maquette dit « sous 7 jours » : durée non reprise |
| Onglets Description, Caractéristiques, Avis (n), FAQ | WooCommerce + plugin | FAQ : champ « Product FAQ » du produit |
| Vidéo (colonne de droite) | Champ vidéo du produit (plugin) | absente sans adresse |
| Avis : moyenne, répartition par étoiles | Avis réels | pourcentages calculés |
| Produits complémentaires | **Ventes incitatives** du produit, sinon produits proches | le titre dit lequel des deux cas ; cartes du listing, **sans bouton** (§6.2) |
| Bloc WhatsApp pré-rempli | Numéro du plugin (`\Evasions\Core\WhatsApp::link()`) | `evasions_single_whatsapp()` ; absent sans plugin ou sans numéro |

### Sous les photos : `evasions_product_aside()`

Depuis le 27 septembre 2026, trois blocs sont rendus ensemble dans un conteneur
`.ev-product-aside`, dans cet ordre : **« pour qui / pas pour qui », les icônes de
réassurance, le bloc WhatsApp**.

- **Desktop (≥ 1024 px)** : le conteneur prend la colonne de la galerie, une rangée sous
  les photos, et le résumé enjambe les deux rangées (`grid-row: 1 / span 2`). C'est la
  raison d'être du conteneur unique : le résumé doit enjamber un nombre de rangées
  connu d'avance, et il vaut toujours deux, qu'un produit n'ait pas de « pour qui » ou
  que la boutique n'ait pas de WhatsApp.
- **Mobile** : une simple pile après le formulaire de commande. Les trois blocs ne
  passent pas avant le titre et le prix — décision du 27 septembre 2026, pour ne pas
  enterrer le prix et le bouton sous le pli.

Le **bouton « Commander maintenant » a été retiré** le même jour : il renommait le bouton
d'ajout au panier et détournait la redirection vers le checkout. La carte express fait la
même promesse sans quitter la page. Rien à remettre en place côté WooCommerce, le réglage
n'avait jamais été touché.

La **colonne de vignettes** de la galerie est posée hors flux sur desktop
(`position: absolute`, `overflow-y: auto`) : en flux, huit vignettes faisaient 1 068 px
là où la photo en fait 680, et la galerie débordait de près de 400 px sous l'image.
`div.images` porte `align-self: start`, sans quoi la rangée enjambée par le résumé
l'étirait et les vignettes suivaient cette hauteur gonflée.

### Galerie au clavier

`main.js` habille le carrousel de WooCommerce sans le remplacer : chaque vignette reçoit un
`<button>` autour de son image (le clic est relayé à l'image, que flexslider écoute), les
vignettes partagent **un seul arrêt de tabulation** et les flèches ↑ ↓ ← → circulent
(`Home` / `End` aux extrémités), et **seule la diapositive affichée garde un lien
focusable** — sans quoi tabuler emmenait le focus sur une photo située des milliers de
pixels plus loin et désynchronisait le carrousel. Les libellés (« Image %1$s sur %2$s »,
« Images du produit ») sont traduits côté PHP et passés par `wp_add_inline_script` dans
`inc/setup.php`.

### « Lire la suite »

Les trois surfaces de description — points forts en liste (`.ev-checks`), description courte
en paragraphes (`.ev-single-short`) et onglet Description (`.ev-desc__text`) — portent
`evasions_readmore_attrs()`, qui pose `data-ev-readmore` et les deux libellés traduits.

Le pli est posé **par le script, et seulement si le texte dépasse vraiment** : la classe
`is-clamped` est appliquée, la hauteur mesurée, et le pli retiré si le contenu tient. Un
bouton « Lire la suite » sous trois lignes entières serait un mensonge. **Sans JavaScript,
rien n'est replié** — un texte tronqué sans bouton pour l'ouvrir serait pire que pas de pli.
La hauteur vient du CSS (`--ev-readmore-max`), pour qu'elle change selon l'écran sans
toucher au script.

Le **bloc WhatsApp** de la maquette « Fiche Produit & Tunnel » (27 septembre 2026)
ouvre la conversation avec le nom et le prix du produit déjà écrits, et montre le
message au client avant qu'il ne l'envoie. Le thème n'écrit ni le numéro ni l'URL :
il passe un texte au plugin, à qui le numéro appartient. La ligne « Réponse du lundi
au samedi, 9h–18h » de la maquette n'est **pas** reprise — aucune source n'établit
ces horaires (§60).

La **barre d'achat collante** de cette même maquette n'est pas reprise non plus :
l'interdiction de CLAUDE.md tient (décision du 27 septembre 2026).

### Appel à l'aventure (avant le pied de page)

Maquette « EVASIONS CTA Aventure » : `template-parts/product-cta.php`, styles en fin
de `assets/css/product.css`, rendu par `woocommerce.php` juste avant le pied de page
de chaque fiche produit. Photo plein cadre, dégradé sombre (vertical en mobile,
latéral à partir de 900 px), titre, sous-titre, un bouton.

Tout est réglable depuis le thème, **Apparence → Personnaliser → EVASIONS → Fiche
produit — appel à l'aventure** : affichage (case à cocher), titre, sous-titre, texte
et lien du bouton (vide = la boutique), couleurs du titre, du sous-titre, du bouton
et de son texte, image de fond. Une couleur laissée vide garde le jeton `--ev-*` du
thème ; une image vide garde le visuel par défaut (`banner-randonneur.webp`) — la
maquette demande un randonneur face aux montagnes au coucher du soleil, à fournir.
Sans titre ni bouton, le bandeau n'est pas rendu (§60).

Le formulaire de **commande express** du plugin est **actif par défaut** depuis la
maquette « Fiche Produit & Tunnel » (27 septembre 2026), qui le prescrit. Il s'insère
dans le résumé de la fiche produit en priorité 26, entre la réassurance et le
formulaire d'achat de WooCommerce — lequel reste en place en dessous et sert de repli
sans JavaScript. Il ne sort que sur un produit **simple, achetable et en stock**.

Sa carte reprend la maquette : titre « Commander ce produit », cinq champs dans l'ordre
**téléphone (indicatif +216 hors du champ), nom, gouvernorat, ville facultative,
adresse**, la quantité en pas de un (bornée à 10), le bandeau vert du total à payer à la
livraison, les conditions de vente, puis deux boutons — commander, ou mettre au panier.

Cet ordre s'écarte du §9.1, qui ouvre par le téléphone : le §9.1 décrit la page
Commander, cette maquette-ci décrit la fiche produit. **La page Commander reste tenue au
§9.1** (contrôlé par `cases-cart.php`). La **ville** n'existe que sur ce formulaire, elle
est facultative, et elle ne tranche pas la question ouverte de la délégation : comme
`Tunisia::checkout_fields()` la retire du checkout, `process_checkout()` la jetterait —
c'est `Express_Order::keep_city()` qui l'inscrit sur la commande.

Tout cela appartient au **plugin** : le thème n'écrit aucun de ces champs et n'en règle
pas l'ordre. L'interrupteur aussi (`Express_Order::enabled()`), qui l'applique à ses
trois points d'entrée d'un coup ; `evasions_express_order_enabled()` ne fait que le
lire. Pour l'éteindre :
`add_filter( 'evasions_express_order_enabled', '__return_false' );`

## Pourquoi choisir… (boutique, catégories, univers)

`template-parts/why.php`, styles dans `assets/css/main.css`, rendu par
`evasions_listing_footer_band()` en bas de la boutique, de chaque catégorie et de
chaque univers. Photo de fond assombrie, titre, puis les arguments en
pictogrammes ronds.

Réglable dans **Apparence → Personnaliser → EVASIONS → Boutique — pourquoi
choisir** : affichage, titre (vide = « Pourquoi choisir <nom du site> ? »),
quatre arguments (pictogramme au choix dans la bibliothèque du thème, titre,
sous-titre), couleurs du titre, du texte, de la pastille et des pictogrammes ;
l'image de fond est dans « Accueil — images de fond ». Un argument sans titre
n'est pas affiché ; les quatre vides, le bandeau reprend les arguments de la
réassurance de l'accueil (`evasions_benefits()`), qui n'énonce que des faits
appliqués par le tunnel COD.

## Communauté (accueil)

Maquette « EVASIONS Newsletter » : `template-parts/community.php`, styles dans
`assets/css/main.css`. Photo à gauche (en haut sur mobile), panneau vert à droite
avec titre, sous-titre et un bouton. Rendu en dernier bloc de **l'accueil** (`front-page.php`). La
boutique, les catégories et les univers finissent, eux, par le bandeau
« Pourquoi choisir… » ci-dessous.

**Formulaire d'inscription** : champ e-mail + bouton, toujours affichés, comme la
maquette. Le formulaire poste chez le fournisseur d'e-mailing, dans une nouvelle
fenêtre : **le site ne stocke aucune adresse**, il n'y a donc pas de donnée métier
à faire survivre à un changement de thème, et la confirmation est celle du
fournisseur (§60 : pas de message de succès inventé).

L'adresse d'action se règle dans le Customizer (« Communauté — adresse du
formulaire ») ; à défaut, la constante `EVASIONS_NEWSLETTER_FORM_ACTION` est
utilisée. `EVASIONS_NEWSLETTER_FIELD` nomme le champ e-mail attendu par le
fournisseur (« EMAIL » par défaut, valeur de Mailchimp et Brevo). Tant qu'aucune
adresse n'est renseignée, le champ s'affiche mais **n'envoie nulle part** (le
formulaire recharge la page) : un avertissement le rappelle dans le bloc, visible
des seuls administrateurs.

Réglable dans **Apparence → Personnaliser → EVASIONS → Accueil — communauté** :
affichage, titre, sous-titre, texte du bouton, texte indicatif du champ e-mail,
adresse du formulaire, couleurs du titre, du sous-titre, du panneau, du bouton et
de son texte, image.
Une couleur vide garde le jeton `--ev-*` du thème ; une image vide garde le
visuel par défaut (`univers-plage.webp`) — la maquette demande une plage aux eaux
turquoise en 960 × 400, à fournir.

## Panier et checkout

Maquettes « Panier » et « Checkout », en **shortcodes classiques**
(`inc/cart-checkout.php`, `assets/css/cart-checkout.css`).

**Pourquoi le classique.** WooCommerce 11 crée ces pages en blocs. Le design groupe
les champs en sections et préfixe le téléphone par +216 ; le plugin décide seul
des champs, de leur ordre et de leur caractère obligatoire — quatre champs (§9.1) :
téléphone, nom complet, gouvernorat, adresse, plus l'e-mail facultatif en dernier.
Les blocs n'offrent pas ces leviers dans le code. Les **règles** du plugin
(téléphone, coût interne, UTM, CAPI) s'appliquent aux deux modes.

**À la mise en service** (plugin EVASIONS Core, `Evasions\Core\Install`, à
l'activation du plugin ou par `livraison/evasions-setup.php`), la page Panier et
la page Commander sont converties en shortcode **seulement si elles ne contiennent
que le bloc par défaut**. Une page personnalisée n'est jamais touchée. L'ancien
contenu est gardé dans la méta `_evasions_previous_content` pour revenir en
arrière. Le thème ne fait plus aucune écriture en base à son activation.

| Élément | Choix |
|---|---|
| Liste du panier | Tableau WooCommerce transformé en cartes par CSS ; quantité − / + avec mise à jour automatique |
| Barre « Total + Passer à la commande » | Fixe en bas sur mobile, masquée sur desktop |
| Récapitulatif | Sticky sur desktop ; le calculateur de livraison est masqué (une seule destination) |
| Ventes croisées | Celles choisies à la main sur le produit ; jamais déduites |
| Checkout | Cartes « 1. Vos informations » (ouverte au téléphone), « 2. Adresse de livraison » (ouverte au gouvernorat), notes, « 3. Paiement » ; récapitulatif sticky |
| Gouvernorat | `<select>` natif (select2 retiré) : sélecteur du système sur mobile |
| Livraison à une autre adresse | Masquée (la maquette n'a qu'une adresse) |
| En-tête du tunnel | Allégé sur les **trois** écrans (panier, commande, confirmation) : logo + « Commande sécurisée », ni menu, ni recherche, ni panier (§9.1) |
| Frise d'étapes | Panier → Commande → Confirmation, `evasions_tunnel_steps()` ; l'étape courante vient de la page affichée, jamais d'un état calculé. Absente du panier vide |

**Non repris de la maquette** (rien n'est inventé, §60) : « économisez 10 % avec un
accessoire » (aucune offre n'existe), « livraison estimée 2 à 4 jours » (aucun délai
établi), « informations cryptées » (rien ne permet de garantir la formule ; la
mention retenue dit « Commande sécurisée »), et « Retours sous 7 jours » /
« Service client 24/7 ».

Le bandeau d'étapes 1-2-3, écarté tant qu'il ne devait coiffer que la page Commander
— un indicateur qui ne change jamais est trompeur — est repris depuis la maquette
« Fiche Produit & Tunnel », qui le pose sur les trois écrans, où il avance
réellement d'un cran à chaque page.

## Listing et filtres

Boutique, catégories, recherche : `template-parts/listing.php` (et
`listing-filters.php`), logique dans `inc/listing.php`, styles dans
`assets/css/listing.css`. Sidebar de filtres en desktop ; en mobile, une barre
collante **Filtres (n) + tri** ouvre un panneau plein écran (il s'ouvre aussi sans
JavaScript, par l'ancre `#ev-filters`).

WooCommerce fait la requête, le thème présente : `min_price` / `max_price`,
`filter_{attribut}`, `orderby` et la pagination sont ceux de WooCommerce. Le thème
n'ajoute qu'un filtre, **« En stock »** (`?en_stock=1`). Les cases d'attributs
sont envoyées au format du formulaire (`ev_f[poids][]=leger`) puis redirigées vers
l'adresse propre de WooCommerce (`filter_poids=leger,standard`) : le filtre marche
sans JavaScript et l'adresse se partage. Avec JavaScript, en desktop, un
changement de filtre s'applique tout de suite ; en mobile, on applique d'un coup.

**Chaque filtre vient d'une donnée réelle** : les bornes du curseur de prix sont
celles des produits de la catégorie — calculées par le plugin
(`\Evasions\Core\Catalog::price_bounds()`), le groupe « Prix » disparaît si
evasions-core est absent — les groupes sont les attributs qui existent
et dont une valeur est utilisée, les catégories sont celles qui contiennent des
produits. La maquette montre « Léger » et « En stock » à titre d'exemple : « Léger »
n'est écrit nulle part dans le code, il apparaîtra si la boutique a un attribut
qui le porte. Le cœur « favoris » de la maquette n'est pas repris : rien ne le
ferait fonctionner.

La carte produit de la boucle est la carte de la homepage (`product-card.php`),
branchée par le filtre officiel `wc_get_template_part`. 12 produits par page.

**Indexation des adresses de filtres** : elle est décidée par evasions-core, seul
propriétaire du `<head>`. Une adresse qui porte une facette (prix, attribut, tri,
« En stock », « Voir tous les produits ») est servie en `noindex, follow` et
renvoie par un canonique vers le listing propre ; la pagination, elle, reste
indexable et se désigne elle-même. Le thème ne pose aucune balise : il déclare
seulement au plugin le nom des paramètres qu'il invente
(`evasions_declare_facet_params()`, filtre `evasions_seo_facet_params`).

## Page univers

Une catégorie de premier niveau qui a des sous-catégories non vides s'affiche
comme un univers (`template-parts/universe.php`, `inc/universe.php`) : hero,
pastilles de sous-catégories, bloc éditorial, « Sélection de l'univers »
(produits mis en avant de la catégorie, sinon meilleures ventes, sinon les plus
récents), bandeau « Pourquoi choisir… ». « Voir tous les produits » mène au
listing complet (`?tous=1`) ; tout filtre, tri ou page demandé affiche aussi le
listing.

- **Titre** : le nom de la catégorie. **Phrase du hero** : sa description dans
  WooCommerce ; à défaut, celle de la maquette pour Camping & Randonnée ; rien pour
  les autres. **Photo du hero** : l'image de la catégorie, sinon la photo de
  l'univers de la maquette, sinon un fond uni.
- **Bloc éditorial** (« Le plein d'aventures vous attend… ») : seulement pour
  Camping & Randonnée, l'univers que la maquette dessine. Plage n'a pas de
  maquette : aucun texte n'est écrit à sa place (voir `evasions_universes()` dans
  `inc/content.php` pour en ajouter un).
- **Pastilles** : l'image de la sous-catégorie si elle en a une, sinon son initiale.

## Pages Packs, FAQ et CGV

Maquette « EVASIONS Pages Infos ». Trois gabarits de page, `page-packs.php`,
`page-faq.php` et `page-cgv.php`, plus `inc/info-pages.php` et
`assets/css/info.css`. **L'en-tête et le pied de page ne changent pas** : seul le
corps est mis en scène.

Le **contenu reste celui de la page WordPress** (installée par evasions-core,
modifiable en administration) : le thème le lit et le découpe, il n'écrit aucun
texte (§60). Une page qui ne suit pas la structure attendue s'affiche telle
quelle, en prose.

| Page | Structure attendue | Rendu |
|---|---|---|
| FAQ | un `<h2>` par rubrique, un `<h3>` par question | accordéon ; rubriques en pastilles (mobile) ou en colonne (desktop) à partir de deux `<h2>` ; bloc « Nous contacter » si la page `contact` existe |
| CGV | un `<h2>` par article (« 4. Paiement » ou « Paiement ») | sommaire collant + cartes numérotées en desktop, accordéon en mobile ; le texte avant le premier `<h2>` devient la note d'avertissement |
| Packs | — | bannière photo, puis les **produits de la catégorie `packs`** en cartes, filtrés par ses sous-catégories ; sans catégorie ni produit, le contenu rédigé de la page |

- **Un pack est un produit** : prix, photo et composition viennent de la
  boutique, pas du thème (§26). La maquette montre un prix barré « somme des
  articles » et un bouton « Ajouter le pack au panier » : ils supposent un type
  de produit « pack » côté evasions-core, qui n'existe pas encore.
- Pictogramme des articles de CGV : choisi d'après le **titre** (paiement →
  carte, livraison → camion, retours → flèche, données → cadenas…), avec un
  document en repli. Cinq pictogrammes ont été ajoutés à `inc/icons.php`.
- Le bandeau « points clés » des CGV n'est pas repris : ses libellés (délais,
  frais, délai de retour) ne sont pas confirmés.
- Accordéons en `<details>` natifs : **sans JavaScript**, les questions
  s'ouvrent et les CGV restent dépliées. Avec JavaScript, une seule question
  ouverte à la fois, et le filtre des packs recompte « N packs ».
- Bannière des packs : surtitre et image dans **Personnaliser → EVASIONS → Page
  Packs**, titre = titre de la page, phrase = extrait saisi à la main.

## Pages sans maquette

Le design ne dessine ni la 404, ni le compte client, ni les e-mails, ni la page de
remerciement. Ces écrans reprennent les jetons du thème et le contenu natif de
WordPress et de WooCommerce ; aucun texte n'est inventé.

- **404** (`404.php`) : « Page introuvable », champ de recherche de produits, un bouton
  par univers **dont la catégorie existe**, « Voir la boutique », puis « Nos
  produits mis en avant » (vrais produits).
- **Recherche sans résultat** (`index.php`) : message, champ de recherche, boutique.
- **Article** (`single.php`) : titre, date, contenu complet. Sans lui WordPress
  n'affichait que l'extrait.
- **Compte client** (`assets/css/account.css`) : connexion en carte, navigation en
  pastilles (mobile) ou colonne (desktop), commandes, adresses. Le bouton « afficher
  le mot de passe » est redessiné (`woocommerce-base.css`) : son icône venait de la
  feuille de WooCommerce retirée.
- **Commande reçue** : voir « Page de remerciement » plus bas.
- **E-mails WooCommerce** (`Evasions\Core\Install::style_emails()`, dans le plugin,
  à la mise en service) : vert de la marque, fond crème, texte encre, logo en
  en-tête. **Une couleur n'est changée que si elle vaut encore la valeur d'origine
  de WooCommerce** : un réglage du propriétaire n'est jamais écrasé. C'est un
  réglage de la boutique : il vit dans le plugin, pas dans le thème.
- **Champ de recherche** (`template-parts/search-form.php`) : un seul gabarit pour
  l'en-tête, la 404 et la recherche vide ; son `id` est un paramètre (un `id` doit
  être unique dans la page). Dessiné en pilule blanche, comme les boutons du
  thème, avec un bouton d'envoi rond ambre (44 px sur mobile, 34 px en desktop) :
  la touche « Entrée » seule ne se devine pas au doigt. Le rembourrage vertical de
  `.ev-nav__search` le détache des filets de la barre de navigation.

Les fichiers de mise en service (archives, script de réglages, guide) sont dans
`livraison/` à la racine du projet ; `livraison/build.sh` les reconstruit.

## Accessibilité et performance

Passe d'audit faite avec un script automatique (titres, textes alternatifs, noms
accessibles, étiquettes de champs, doublons d'id, contrastes, zones tactiles) sur
l'accueil, la fiche produit, l'univers, le listing, le panier et le checkout.

- **Titres** : un seul h1 par page ; sous le h1 d'un listing, les cartes ont un h2
  (`title_tag` de `product-card.php`), sous un h2 de section elles gardent un h3.
- **Contrastes** : le gris `#8A8F8B` (3,06:1) est remplacé par `#676E6A` (≥ 4,5:1)
  pour les textes (ancien prix, marque des champs, options du panier) ; la ligne
  légale du pied de page passe de `#7E9689` (3,75:1) à `#9DB3A6`.
- **Zones tactiles** (WCAG 2.2, 2.5.8) : icônes compte et panier à 44 px de haut
  sans bouger l'icône (marge négative), liens de titres de carte, fil d'Ariane,
  « Voir tous » et « Réinitialiser » à 24 px au moins ; poignées du curseur de prix
  à 24 px sur écran tactile.
- **Étoiles** : la note utilise le jeton `--ev-star` (`#A6740A`, ≥ 3:1 sur blanc,
  WCAG 1.4.11) — l'or plus foncé que l'accent des boutons `--ev-sun`, qui reste
  inchangé. La note reste aussi écrite en toutes lettres à côté (« 4,8 (24 avis) »).
- **Champs et cibles** : champ quantité (panier et fiche) à 16 px, recherche et tri
  desktop remontés à 16 px (plus de zoom iOS sur iPad), lignes de filtres `.ev-check`
  à 44 px de haut (WCAG 2.5.8).
- **Poids** : logos SVG optimisés avec svgo (`logo-full-white` 61 → 33 Ko,
  `logo-compact` 40 → 29 Ko) ; CSS et JS **minifiés** en production
  (`livraison/build-assets.sh`, servis par `evasions_use_minified_assets()` ;
  ~124 → 84 Ko de CSS, JS −53 %) ; **images AVIF** avec repli WebP
  (`livraison/build-images.sh` + `evasions_picture()` et les `<picture>` des
  gabarits ; hero −48 %, préfooter −66 %). Script des émojis retiré (23 Ko/page),
  polices auto-hébergées et préchargées, version des ressources liée à la date du
  fichier.
- **Découverte et priorité des images** (mesure Lighthouse du 28 septembre 2026 :
  sur l'accueil, 59 % du LCP passés à attendre que la requête de l'image parte,
  3 % à la télécharger — le problème n'est pas le poids). L'image du hero est
  **préchargée en tête du `<head>`** (`evasions_hero_preload()`, priorité 0, avant
  les polices) ; le gabarit et le préchargement lisent la **même** liste
  (`evasions_hero_media()`) pour ne jamais désigner deux images différentes, et les
  deux `media` (`(min-width: 900px)` et `(max-width: 899.98px)`, dérivés de
  `evasions_hero_breakpoint()`) s'excluent pour qu'un seul préchargement parte.
  Sur le listing, la **première carte** est la seule en `loading="eager"` +
  `fetchpriority="high"` (`lcp` de `product-card.php`, rang donné par
  `evasions_listing_item_rank()`) ; toutes les autres restent en `lazy`.
- **JavaScript de l'accueil** : en plus de `wc-add-to-cart` et
  `wc-cart-fragments`, le thème y retire **`woocommerce`** (`evasions_lean_home_assets()`).
  Ce script ne sert qu'aux écrans de la boutique (tri de la boucle, calculateur
  de livraison, champs quantité), absents de l'accueil, et il tire derrière lui
  jQuery, jQuery Migrate, blockUI et js-cookie — dont jQuery, que WordPress place
  dans le `<head>` sans `defer`, donc bloquant. `evasions_home_drop_jquery()` est
  le filet de sécurité : il ne retire jQuery qu'après avoir vérifié, sur la page
  rendue, qu'aucun script en file ne le déclare en dépendance (directe ou
  indirecte, `evasions_script_needs_jquery()`) et qu'aucun code en ligne n'y est
  accroché. Rien n'est touché sur la boutique, la fiche produit, le panier ni le
  checkout, où jQuery sert.
- **À venir** : le `srcset` 400/800/1200 (§17) attend des originaux 1 400 px ;
  les visuels actuels plafonnent à 612 px, `build-images.sh` ne fait que convertir
  le format sans sur-échantillonner. Le texte blanc sur photo est protégé par un
  dégradé ; le contraste réel dépend de la photo.

## CSS de WooCommerce

Le thème retire `woocommerce-general`, `woocommerce-layout` et
`woocommerce-smallscreen` (elles flottent la galerie à 48 %, colorent les boutons
en violet…) et les remplace par `assets/css/woocommerce-base.css` : notices,
champs, tableaux, étoiles, pagination. Les styles des blocs WooCommerce ne sont
pas touchés.

## Structure

```
front-page.php            homepage, un template-part par bloc
template-parts/           topbar, site-header, hero, universes, featured-products,
                          product-card, banner, benefits, reviews,
                          community, why, site-footer, listing, listing-filters,
                          listing-item, universe, product-cta
inc/setup.php             fonctionnalités, menus, ressources
inc/content.php           données (promesses, liens, avis, accroche, réassurance)
inc/woocommerce.php       produits, habillage des badges (règle : plugin), étoiles,
                          panier, bouton AJAX
inc/product.php           fiche produit (hooks, onglets, achat direct)
inc/cart-checkout.php     panier et checkout classiques (champs, cartes, libellés)
inc/listing.php           listing, filtres, tri, pagination
inc/universe.php          page univers (hero, sous-catégories, sélection)
inc/pages.php             pages sans maquette : feuilles 404 et compte, e-mails
inc/info-pages.php        pages Packs, FAQ, CGV (lecture et découpage du contenu)
inc/brand.php             icône du site, favicon.ico, image de remplacement
inc/icons.php             icônes SVG en ligne
assets/css/main.css       styles (mobile d'abord, rupture à 900 px)
assets/css/listing.css    listing, filtres, page univers
assets/css/product.css    fiche produit (grille nommée, rupture à 1024 px)
assets/css/cart-checkout.css  panier et checkout (cartes, récapitulatif sticky)
assets/css/woocommerce-base.css  base des écrans WooCommerce
assets/js/main.js         menu mobile, quantités, filtres du listing (sans dépendance)
assets/img/               logos SVG, visuel de partage, photos de la maquette
assets/img/icons/         favicon, icônes iOS et Android, manifeste
assets/fonts/             Manrope, DM Sans (WOFF2, auto-hébergées)
```

## À savoir

- **Photos** : celles de la maquette font 612 px de large au plus. Elles sont
  floues sur un écran 1440 px : à remplacer par des fichiers d'au moins
  2400 × 1040 (hero) dans `assets/img/`, sous les mêmes noms.
- **Logos et icônes** : vectorisés depuis `logo evasions trsp.png` (dont le « fond
  transparent » était un damier cuit dans les pixels) : SVG avec transparences réelles
  et lettrage en contours ; voir `brand/README.md`. En-tête : `logo-compact.svg` ; pied
  de page : `logo-full-white.svg` ; e-mails : `logo-email.png` (les messageries
  n'affichent pas le SVG). L'icône de l'onglet, les icônes iOS et Android, le manifeste
  et le visuel de partage sont émis par `inc/brand.php` ; une icône choisie dans
  WordPress prime. Le tracé part d'une image de 1536 px : pour l'impression grand
  format, faire reprendre le SVG par un graphiste.
- **Hero** : une seule photo (celle du design). Le carrousel de trois photos de la
  maquette n'a que sa première image remplie.
- **Polices** : Manrope et DM Sans sont **auto-hébergées** (`assets/fonts/`, WOFF2
  variables, sous-ensemble latin, licence OFL, voir `assets/fonts/README.md`) et
  préchargées : aucune requête vers Google. L'arabe utilise la police système.
- **Page de remerciement** : elle n'a pas de maquette ; elle affiche le contenu natif
  de WooCommerce dans l'en-tête et le pied de page du thème, avec la feuille de base.
- **Photos de l'univers** : la maquette montre une tente au bord d'un lac ; l'export
  ne contient pas cette photo, le hero réutilise la photo existante de l'univers.
  Poser une image sur la catégorie dans WooCommerce la remplace.
- **Listing** : « En stock » est le seul filtre ajouté à WooCommerce. Un produit
  masqué du catalogue compte dans les bornes de prix (l'index de prix de WooCommerce
  ignore la visibilité) : le curseur peut alors monter un peu plus haut que le
  produit le plus cher affiché.
- **Photos des bandeaux** panier et checkout : la maquette utilise deux photos qui ne
  faisaient pas partie de l'export ; les photos existantes sont réutilisées.
- **Textes à valider** : « et testés » (produits) vient de la maquette. Le plugin
  n'affirme que ce que le code applique ; à confirmer si c'est bien vrai.
