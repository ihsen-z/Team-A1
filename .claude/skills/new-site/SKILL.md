---
name: new-site
description: Exécute le pipeline de génération d'un site à partir d'un plan approuvé — scaffold, design, contenu, composition, QA. Utiliser après /kickoff et après validation humaine du plan.
---

# Génération d'un site

Argument : un dossier client (`clients/<slug>`).

## Préalable bloquant

Vérifie que `clients/<slug>/project-plan.yml` porte `status: approved`.
Sinon, **arrête-toi** et dis à l'humain de relire le plan. Ne propose pas de
passer outre : le gate existe parce qu'un site construit sur la mauvaise stack
se paie en jours de reprise.

## Étapes

Exécute dans l'ordre, en t'arrêtant à la première qui échoue.

1. **Scaffold** — `tooling/scaffold.sh clients/<slug>`
   WordPress, extensions du preset, thème socle, blocs autorisés, réglages.
2. **Design** — `tooling/design.sh clients/<slug>`
   Charte → `theme.json`. Délègue à `design-tokens` si la charte demande un
   arbitrage (contraste insuffisant, typo sans fallback correct).
3. **Contenu** — délègue à `content-writer`, un lot de pages à la fois.
   Sortie : `clients/<slug>/content/<page>.yml`. Tout fait absent du brief
   devient `[[À VALIDER: ...]]`. Relis les placeholders avant de continuer.
4. **Composition** — délègue à `block-composer`.
   Assemble les pages avec les `page_templates` du preset et **uniquement** les
   blocs de `blocks_allowed`. Sortie : `clients/<slug>/build/pages/<slug>.html`.
5. **Import** — via l'adaptateur (`adapter_import_page`, `adapter_set_front_page`).
6. **QA** — `tooling/qa.sh clients/<slug>`, puis délègue à `qa-visual` pour le
   responsive et l'accessibilité. Compare aux `quality_gates` du preset.
7. **Audit sécurité** — délègue à `wp-security-reviewer`. Son verdict est
   **bloquant** : un finding ouvert interdit le déploiement.

## Règles d'exécution

- Chaque étape est idempotente : une relance ne doit rien casser.
- Un agent signale un besoin hors périmètre → tu remontes à l'humain, tu
  n'élargis pas le projet de ta propre initiative.
- Surveille le budget de tokens du plan. À 80 %, préviens avant de continuer.
- Tu ne déploies jamais depuis cette skill. Le déploiement est une étape
  séparée, avec validation humaine.

## Compte rendu

Étapes réussies, seuils qualité atteints ou manqués (avec les chiffres), liste
des `[[À VALIDER]]` restants, findings de sécurité, et ce qui reste à faire à
la main avant livraison.
