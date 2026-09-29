# Feuille de route

> État au 29/09/2026. Les paliers sont ordonnés : chacun doit être **éprouvé
> sur un vrai projet** avant d'attaquer le suivant. C'est l'usage qui révèle ce
> qui manque, pas la réflexion.

---

## Où on en est

| | Fait | Reste |
|---|---|---|
| Gouvernance | matrice, registre, schéma du brief | seuils à caler sur tes vrais chiffres |
| Socle | thème, durcissement, découpe par titre, `screen-reader-text` | `add_editor_style()`, garde-fous éditoriaux |
| Bibliothèque | **10 blocs** | aucun n'a encore tourné |
| Pipeline | scaffold, design, QA, import | contenu, composition, déploiement |
| Analyse | 2 sites extraits, 3 rapports | — |

**Le risque principal aujourd'hui n'est pas ce qui manque, c'est que rien n'a
été exécuté.** Dix blocs validés syntaxiquement ne valent pas un bloc vu à
l'œuvre.

---

## Palier 0 — Faire tourner (prioritaire)

Objectif : qu'un WordPress rende les dix blocs sur une page.

- [ ] Monter un site de test jetable (Local, à côté de famma)
- [ ] `composer install` et faire passer `composer check` au vert
- [ ] Activer `theme-core`, poser les dix blocs sur une page
- [ ] **Vérifier en priorité le garde-fou de récursion de `doc-sections`** :
      le bloc lit le contenu de la page où il se trouve
- [ ] Vérifier que `factory_core_allowed_blocks()` restreint bien l'éditeur
- [ ] Corriger ce qui casse, et consigner chaque défaut trouvé

Ce palier ne produit aucune fonctionnalité. Il transforme dix hypothèses en
dix certitudes — ou en une liste de corrections, ce qui vaut mieux que
l'ignorance.

---

## Palier 1 — Le socle tient

- [ ] `add_editor_style()` sur les jetons : aujourd'hui l'éditeur ne voit pas
      ce que voit le front
- [ ] Garde-fous éditoriaux : retirer les notes `[[À VALIDER]]` du rendu et les
      rappeler à l'administrateur (pendant runtime du placeholder)
- [ ] Contrôle de contraste **bloquant** dans le générateur de `theme.json`
      (exigence [A04] du cahier des charges)
- [ ] Porter `svg_allowed_html()` et `kses_image()` de `famma-core` dans
      `theme-core/inc/security.php`
- [ ] Caler `seuils_rentabilite` sur tes chiffres réels

---

## Palier 2 — Premier site réel de bout en bout

- [ ] Un vrai projet client mené avec le pipeline
- [ ] Noter chaque friction : **c'est la feuille de route réelle, pas celle-ci**
- [ ] Compléter la matrice de décision avec les cas rencontrés
- [ ] Mesurer le temps passé, par étape

Critère de réussite : moins de 20 % d'intervention manuelle hors collecte de
contenu factuel.

---

## Palier 3 — Contenu et composition

- [ ] `tooling/content.sh` et `tooling/compose.sh` (aujourd'hui pilotés à la
      main par les subagents)
- [ ] Génération et optimisation des images, texte alternatif
- [ ] Blocs restants de l'inventaire : `empty-state`, `product-faq`,
      `preuve-sociale`, `carrousel-categories`

---

## Palier 4 — Module e-commerce

Le plus gros gain immédiat sur le prochain client marchand, d'après l'analyse
de famma : 100 % WooCommerce, aucune adhérence à Kadence à retirer.

- [ ] Tunnel d'achat : fil d'Ariane, stepper, réassurance
- [ ] Checkout en cartes numérotées + aside récapitulatif
- [ ] Panier redessiné par hooks (aucun gabarit copié)
- [ ] Sélecteur de quantité, états vides, onglets produit
- [ ] Colonne de filtres de boutique

---

## Palier 5 — QA et déploiement automatisés

- [ ] Suite Playwright dans `tooling/qa/` : captures multi-breakpoints, axe
- [ ] `tooling/deploy.sh` : sauvegarde → staging → prod, avec retour arrière
- [ ] Seuils qualité bloquants en intégration continue

---

## Palier 6 — Maintenance

Revenu récurrent, à marge quasi nulle en effort une fois automatisé.

- [ ] Cron hebdomadaire : mises à jour sur staging → tests de non-régression →
      PR si vert, alerte si rouge
- [ ] Rapport mensuel client généré (Lighthouse, Search Console, uptime,
      sauvegardes)
- [ ] Triage des tickets support

---

## Palier 7 — Bedrock

**Déclencheur** : un client impose un déploiement par Git, ou le parc devient
majoritairement VPS. Pas avant — voir `decisions/0001`.

- [ ] Écrire `tooling/adapters/bedrock.sh` contre `INTERFACE.md`
- [ ] Activer `wp-bedrock` dans la matrice
- [ ] Ajouter la règle d'aiguillage entre les deux stacks

---

## Dette connue

| Sujet | Où | Gravité |
|---|---|---|
| Aucun bloc n'a tourné | `packages/blocks/` | **élevée** |
| `rest_endpoints` vs casse des routes REST : non vérifié sur une installation | `theme-core/inc/security.php` | moyenne |
| Anonymisation des avis : dernier mot ou deuxième ? Écart avec le code d'origine | `reviews-wall` | faible, à trancher |
| mu-plugins de famma non importés (lien symbolique) — corrigé, à relancer | `imports/famma/` | faible |
| Historique Claude Code non exporté (Python absent sous Git Bash) | — | faible |

---

## À corriger sur les sites en production

Relevé pendant l'analyse, sans rapport avec le socle mais à ne pas perdre :

1. **famma** — `page-retours.php` : formulaire public sans nonce. Le traitement
   est dans `famma-core`, à auditer.
2. **famma** — `inc/product-video.php:75` : `echo $player` non échappé.
3. **famma** — `inc/footer-parts.php:29` : `get_option(strtolower($key))` sans
   liste blanche.
4. **evasions** — `inc/brand.php` : `#165A32` en dur deux fois, alors que
   `--ev-forest` porte la même valeur.

---

## Ce qui n'est délibérément pas automatisé

La relation client, l'arbitrage UX métier, la validation finale avant mise en
ligne, et le choix de la stack sur un cas hors matrice.

Ce n'est pas une limite technique : c'est là qu'est ta valeur, et c'est ce que
tu factures.
