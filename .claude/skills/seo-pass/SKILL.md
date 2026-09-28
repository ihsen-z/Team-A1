---
name: seo-pass
description: Passe SEO technique et éditoriale sur un site généré — titles, meta, données structurées, maillage interne, images, sitemap. Utiliser avant mise en ligne ou lors d'un audit de site existant.
---

# Passe SEO

Argument : un dossier client (`clients/<slug>`).

## Technique

- `<title>` unique par page, 50–60 caractères, marque en suffixe.
- Meta description 140–160 caractères, incitative, sans bourrage.
- Un seul `h1` par page ; hiérarchie sans saut de niveau.
- URLs en `/%postname%/`, slugs courts, sans mot vide.
- Canonical sur chaque page ; `noindex` sur les pages légales et les
  résultats de recherche interne.
- Sitemap XML généré et déclaré ; `robots.txt` cohérent.
- Redirections 301 pour toute URL modifiée depuis un site existant — c'est
  l'oubli qui coûte le plus cher lors d'une refonte.

## Données structurées

Schema.org adapté au type : `LocalBusiness` (commerce de proximité),
`Organization` (business), `Product` + `Offer` (e-commerce), `BreadcrumbList`
partout. **Uniquement avec les faits du brief** — un horaire ou un avis inventé
dans un balisage structuré est une déclaration publique fausse.

Valide la sortie avec le Rich Results Test avant de conclure.

## Contenu

- Une intention de recherche par page, explicite.
- Maillage interne : chaque page atteignable en 3 clics depuis l'accueil.
- Images : `alt` descriptif (pas de bourrage), `width`/`height` présents,
  `loading="lazy"` sauf l'image du hero, format moderne.
- Ancres de liens explicites — jamais « cliquez ici ».

## Local (si zone géographique au brief)

Cohérence NAP (nom, adresse, téléphone) entre site et Google Business Profile.
Page contact avec adresse en texte sélectionnable, pas seulement en image.

## Sortie

Écris `reports/<slug>/seo.md` : ce qui est conforme, ce qui ne l'est pas avec
le correctif exact, et ce qui demande une décision du client. Applique les
correctifs techniques sûrs ; laisse les arbitrages éditoriaux à l'humain.
