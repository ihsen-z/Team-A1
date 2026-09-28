# Feuille de route

L'ordre compte. Chaque palier doit être utilisé sur un vrai projet avant de
passer au suivant — c'est l'usage qui révèle ce qui manque, pas la réflexion.

## Palier 1 — Le socle (fait : structure ; à faire : matière)

- [x] Structure du dépôt, gouvernance, presets, adaptateurs
- [x] Thème socle + 3 premiers blocs
- [x] Hooks de qualité, skills, subagents
- [ ] **Extraire les blocs de tes 3 derniers sites** → viser 12-15 blocs
- [ ] Ajuster les seuils de `seuils_rentabilite` avec tes vrais chiffres
- [ ] `composer install` et faire passer `composer check` au vert

## Palier 2 — Premier site réel

- [ ] Faire un vrai projet client de bout en bout avec le pipeline
- [ ] Noter chaque friction : c'est la feuille de route réelle, pas celle-ci
- [ ] Compléter la matrice avec les cas rencontrés

## Palier 3 — Contenu et composition

- [ ] `tooling/content.sh` et `tooling/compose.sh` (aujourd'hui pilotés par les subagents)
- [ ] Génération et optimisation des images, alt text
- [ ] Preset `ecommerce` éprouvé sur un vrai catalogue

## Palier 4 — QA et déploiement automatisés

- [ ] Suite Playwright dans `tooling/qa/` (captures multi-breakpoints, axe)
- [ ] `tooling/deploy.sh` : sauvegarde → staging → prod, avec rollback
- [ ] Gates qualité bloquants en CI

## Palier 5 — Maintenance (le meilleur retour sur investissement)

- [ ] Cron hebdo : updates sur staging → regression → PR si vert
- [ ] Rapport mensuel client automatique (Lighthouse, uptime, sauvegardes)
- [ ] Triage des tickets support

## Palier 6 — Bedrock

Déclencheur : un client impose un déploiement par Git, ou le parc devient
majoritairement VPS. Voir `decisions/0001`.

- [ ] Écrire `tooling/adapters/bedrock.sh` contre `INTERFACE.md`
- [ ] Activer `wp-bedrock` dans la matrice
- [ ] Ajouter une règle d'aiguillage entre les deux stacks

---

## Ce qui n'est délibérément pas automatisé

La relation client, l'arbitrage UX métier, la validation finale avant mise en
ligne, et le choix de la stack sur un cas hors matrice.

Ce n'est pas une limite technique — c'est là que se trouve ta valeur, et c'est
ce que tu factures.
