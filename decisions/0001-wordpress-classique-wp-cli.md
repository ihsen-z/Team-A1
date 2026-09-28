# 0001 — WordPress classique + WP-CLI, avec une couche d'adaptateurs

- **Date** : 2026-09-28
- **Statut** : Acceptée

## Contexte

L'atelier produit 3 à 8 sites par mois (vitrine, business, e-commerce) avec des
thèmes bloc custom. L'objectif est d'automatiser la production sans perdre la
maintenabilité.

Deux options d'infrastructure se présentaient :

1. **WordPress classique piloté par WP-CLI** — l'installation telle que les
   hébergeurs mutualisés la servent, sans gestion de dépendances.
2. **Bedrock + Composer + Git** — arborescence structurée, extensions en
   dépendances versionnées, déploiement par Git.

## Décision

**WordPress classique + WP-CLI maintenant.** Bedrock est prévu plus tard.

Pour que ce « plus tard » ne soit pas une réécriture, **aucun script du
pipeline n'appelle `wp` directement**. Toute interaction avec l'installation
passe par un adaptateur qui implémente le contrat de
`tooling/adapters/INTERFACE.md`. `classic.sh` l'implémente aujourd'hui ;
`bedrock.sh` l'implémentera demain, sans que le reste du pipeline change.

## Raisons

- **Compatibilité hébergement.** Les clients vitrine et business sont sur du
  mutualisé, où Bedrock ne se déploie pas sans friction. Imposer un VPS à un
  site à 1800 € détruit la marge.
- **Reprise par le client.** Une installation classique peut être reprise par
  n'importe quel prestataire. Bedrock restreint ce choix — acceptable sur un
  e-commerce, discutable sur une vitrine.
- **Le gain de Bedrock est faible sans CI.** L'atelier n'a pas encore de
  pipeline de déploiement versionné : on paierait la complexité sans encaisser
  le bénéfice.
- **La standardisation est ailleurs.** Ce qui rend la production répétable, ce
  sont les presets, le socle et les blocs — pas l'arborescence des fichiers.

## Alternatives écartées

- **Bedrock tout de suite** : coût d'entrée immédiat, bénéfice différé, et
  incompatible avec la moitié du parc client actuel.
- **Un page builder (Elementor/Divi)** : rapide au premier site, mais le
  contenu devient illisible hors du builder, la performance se dégrade et
  l'automatisation par agents devient impossible — on génère du JSON opaque au
  lieu de blocs versionnables. Écarté sans réserve.

## Conséquences

- Les extensions sont installées par WP-CLI, pas déclarées en dépendances : la
  liste autorisée vit dans les presets, et `adapter_remove_plugins` supprime ce
  qui est interdit à chaque passage.
- Pas de `composer.json` côté site ; celui du dépôt ne sert qu'à l'outillage de
  développement (phpcs, phpstan).
- `wp-config.php` n'est jamais versionné (voir `.gitignore` et les permissions
  `deny` de `.claude/settings.json`).
- **Déclencheur de réexamen** : dès qu'un client impose un déploiement par Git,
  ou que l'atelier passe à un parc majoritairement VPS, écrire `bedrock.sh` et
  activer la stack `wp-bedrock` dans `governance/decision-matrix.yml`.
