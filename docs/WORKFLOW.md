# Workflow d'un projet, de bout en bout

## 0. Prérequis (une seule fois)

```bash
composer install                 # phpcs, phpstan
cp .env.example .env             # puis renseigne la base et l'admin
wp --version                     # WP-CLI installé
```

Lecteur YAML requis par les scripts : `yq`, ou `pip install pyyaml`.

## 1. Brief

```bash
mkdir -p clients/mon-client
cp clients/_example/client.yml clients/mon-client/client.yml
```

Remplis-le. Les champs `[CLASSIF]` déterminent la stack — c'est la partie qui
compte. Ce que tu ne sais pas encore, laisse-le vide : la validation te dira
ce qui manque, et c'est la liste de questions à poser au client.

## 2. Cadrage — le chef de projet

```
/kickoff clients/mon-client
```

Il classifie, applique `governance/decision-matrix.yml`, compose l'équipe,
écrit `clients/mon-client/project-plan.yml` et un ADR dans `decisions/`.

Trois sorties possibles :

| `status` | Ce que ça veut dire | Ce que tu fais |
|---|---|---|
| `awaiting_human_approval` | Une règle a matché, le plan est prêt | Tu relis et tu approuves |
| `escalated` | Cas hors matrice | Tu tranches, puis tu ajoutes une règle |
| — (erreur) | Brief incomplet | Tu complètes le brief |

## 3. Approbation — le point de contrôle humain

Relis le plan. Regarde en particulier :
- la **confiance** de classification (sous 0.85, vérifie le raisonnement) ;
- les **risques** et les **questions ouvertes** ;
- l'équipe : un agent en trop coûte des tokens, un agent en moins coûte une reprise.

Puis :

```bash
sed -i 's/^status: .*/status: approved/' clients/mon-client/project-plan.yml
```

Rien ne s'exécute avant. `require_approved_plan()` le vérifie à chaque script.

## 4. Génération

```
/new-site clients/mon-client
```

Ou étape par étape, ce qui est préférable les premières fois :

```bash
tooling/scaffold.sh clients/mon-client    # WordPress + extensions + thème + blocs
tooling/design.sh   clients/mon-client    # charte → theme.json
# contenu     → subagent content-writer
# composition → subagent block-composer
tooling/qa.sh       clients/mon-client    # Lighthouse + Playwright + axe
```

## 5. Avant livraison

1. **Traiter les `[[À VALIDER]]`.** C'est ta liste de questions au client. Un
   placeholder livré en production est une erreur visible ; un fait inventé qui
   passe inaperçu est pire.
2. `/wp-audit clients/mon-client` — verdict bloquant.
3. `/seo-pass clients/mon-client`.
4. Relecture humaine des pages. Non négociable : l'IA ne voit pas qu'une photo
   de vitrine montre le mauvais magasin.

## 6. Déploiement

Validation humaine explicite. Préconditions (registre des agents) :
QA verte, aucun finding de sécurité bloquant, sauvegarde de la cible vérifiée.

---

## Quand ça coince

**Un besoin sans bloc existant** → `/new-block <nom>`. Le bloc entre dans la
bibliothèque et sert tous les projets suivants. Ne laisse jamais passer du HTML
inline : c'est ainsi qu'un site sort du système.

**Un cas hors matrice** → tranche, puis **ajoute la règle**. Une escalade qui ne
devient pas une règle se représentera à l'identique au projet suivant.

**Un agent qui dérape** → regarde d'abord ses permissions dans
`governance/agents-registry.yml`, puis ses consignes dans `.claude/agents/`.
Corrige la consigne, pas le symptôme.

**Les hooks bloquent une édition** → c'est leur rôle. Lis la sortie, corrige.
`composer fix` règle la plupart des écarts de style automatiquement.
