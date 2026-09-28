# 0002 — Le chef de projet applique une politique, il ne décide pas

- **Date** : 2026-09-28
- **Statut** : Acceptée

## Contexte

L'atelier veut un agent « chef de projet » qui chapeaute la production :
cadrer un projet, choisir la stack, affecter les agents d'exécution.

La tentation est de lui donner cette décision en propre : lui décrire le client
et le laisser choisir la technologie.

## Décision

**Le chef de projet classifie le brief et applique
`governance/decision-matrix.yml`. Il ne choisit pas librement une stack.**

Quand aucune règle ne matche, ou que la confiance de classification est faible,
il **escalade vers l'humain avec une recommandation argumentée** — il ne
tranche pas.

Son plan sort systématiquement en `status: awaiting_human_approval`. Aucune
étape du pipeline ne s'exécute avant que l'humain ait écrit `approved`.

## Raisons

- **Déterminisme.** Deux briefs identiques doivent produire la même stack. Une
  décision inférée ne le garantit pas, et la variabilité détruit la
  standardisation — donc la rentabilité.
- **Auditabilité.** « Pourquoi cette stack ? » doit avoir une réponse
  vérifiable : une règle, dans un fichier, versionnée.
- **Les critères réels ne sont pas dans le contexte du modèle.** La marge, la
  capacité de maintenance à trois ans, ce que l'atelier sait supporter : ce sont
  des décisions de propriétaire.
- **Le système apprend quand même** — mais dans un fichier relu par un humain.
  Chaque escalade tranchée devient une règle. C'est un apprentissage lisible.

## Alternatives écartées

- **Chef de projet pleinement autonome** : non-déterministe, non auditable, et
  capable d'engager la production sur une mauvaise base sans qu'on le voie.
- **Pas de chef de projet du tout** : le cadrage resterait manuel sur chaque
  projet, alors que classification et composition d'équipe sont répétables.

## Conséquences

- La matrice de décision est un fichier de première classe, maintenu à la main.
- Le chef de projet est la **session principale** (skill `/kickoff`), pas un
  subagent : un subagent ne peut pas déléguer de façon fiable.
- Le champ `status` du plan est le point de contrôle humain du système. Aucun
  script ne doit l'écrire — `require_approved_plan()` le vérifie.
- Une stack marquée `enabled: false` (Bedrock aujourd'hui) est une escalade,
  jamais une option retenue automatiquement.
