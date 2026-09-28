# Me transmettre tes sites et ton historique

Deux outils, deux règles : **n'envoie que l'utile**, et **vérifie toi-même
avant de committer**. Les scripts sont une barrière, pas une garantie.

---

## En une seule commande

Si tu veux tout faire d'un coup — sites, historique, contrôle de fuite, commit
et push :

```bash
git config --global user.name  "Ton Nom"     # une seule fois
git config --global user.email "ton@email.fr"

./tooling/import/import-all.sh "/c/Users/DELL/Local Sites/famma/app/public:famma" \
                               "/c/Users/DELL/extraction-temporaire:evasions"
```

Sans argument, le script cherche seul dans les emplacements habituels
(`~/Local Sites/*/app/public`, htdocs, www, Laragon, MAMP).

Ce qu'il fait, dans l'ordre : vérifie ton identité Git **avant** de travailler,
extrait chaque site, retire les thèmes commerciaux (Kadence, Divi, Astra…) qui
ne sont pas ton travail, exporte l'historique Claude Code expurgé, passe un
contrôle de fuite sur l'ensemble, puis committe et pousse.

**Le push n'a lieu que si le contrôle est propre.** Au moindre secret détecté,
il s'arrête sans rien committer et te montre les lignes en cause.

Options : `--dry-run` (montre sans écrire), `--no-push`, `--no-transcripts`,
`--keep-parents` (conserve les thèmes commerciaux).

Les sections ci-dessous détaillent chaque étape si tu préfères les faire une
par une.

---

## 1. Tes sites existants

### Ce dont j'ai besoin

Les **thèmes**, les **patterns**, la **liste des extensions**. C'est tout.
Sur un site WordPress typique, ça représente quelques centaines de Ko sur
plusieurs centaines de Mo.

### Ce que je ne veux pas — et pourquoi

| Ce que c'est | Pourquoi ça reste chez toi |
|---|---|
| `wp-config.php` | Identifiants de base, clés de salage |
| `.env`, `*.key`, `*.pem` | Secrets applicatifs |
| Dumps SQL | Comptes, commandes, adresses de **tes clients finaux** |
| `wp-content/uploads` | Parfois des documents clients ; toujours du poids inutile |
| `node_modules`, `vendor` | Reconstructibles, énormes |
| Thèmes `twenty*` | Livrés avec WordPress, n'apprennent rien sur ta façon de faire |

Les dumps SQL et les uploads ne sont pas seulement encombrants : tu es
responsable de traitement pour les données de tes clients. Un dépôt Git, même
privé, n'est pas l'endroit où elles doivent atterrir.

### Étapes

**1. Récupère une copie du site** (FTP, cPanel, ou un dossier local).
Tu n'as besoin ni de la base, ni d'un accès SSH.

**2. Extrais** — une commande par site :

```bash
cd ~/Team-A1
./tooling/import/extract-site.sh /chemin/vers/site-client nom-court
```

Le script trouve `wp-content` tout seul, copie les thèmes et patterns **en
filtrant**, recense les extensions avec leur version, relève des statistiques
sur la médiathèque (sans copier une seule image), puis **cherche des secrets
dans ce qu'il vient d'écrire et s'arrête si un seul remonte**.

**3. Vérifie de tes propres yeux.** Le script attrape les motifs connus, pas un
mot de passe écrit en toutes lettres dans un commentaire :

```bash
du -sh imports/*/
grep -ril 'password\|secret\|api_key\|clé' imports/
```

**4. Committe et pousse :**

```bash
git add -f imports/nom-court
git commit -m "Import : nom-court pour extraction de blocs"
git push -u origin claude/admiring-newton-0l5yok
```

Le `-f` est volontaire : `imports/` est ignoré par défaut, pour qu'un
`git add .` distrait n'emporte jamais un import non vérifié. Tu dois nommer
explicitement ce que tu ajoutes — c'est le dernier point de contrôle.

Fais-le pour **3 ou 4 sites représentatifs** — un vitrine, un business, un
e-commerce. Au-delà, les motifs se répètent et je n'apprends plus rien.

---

## 2. Ton historique Claude Code

### Où il se trouve

Claude Code écrit une session par fichier :

```
~/.claude/projects/<chemin-du-projet-encodé>/<uuid-session>.jsonl
```

Le chemin du projet est encodé en remplaçant les `/` par des `-` :
`/Users/toi/sites/client` devient `-Users-toi-sites-client`.

Sous Windows : `%USERPROFILE%\.claude\projects\`.

### Pourquoi ne pas envoyer les `.jsonl` bruts

Ils contiennent bien plus que la conversation : pièces jointes, contenus de
fichiers lus au passage, sorties de commandes, métadonnées de facturation. Sur
une session mesurée, **1,4 Mo de brut pour 40 Ko de conversation utile** — et
les 1,36 Mo de différence sont précisément ce qui peut contenir un secret lu
dans un `wp-config.php`.

### Étapes

**1. Exporte :**

```bash
python3 tooling/import/export-transcripts.py --out ./export-transcripts
```

Pour ne prendre que certains projets :

```bash
python3 tooling/import/export-transcripts.py --filter boulangerie --out ./export-transcripts
```

Le script ne garde que tes tours et ceux de Claude, résume chaque appel d'outil
en une ligne, jette les résultats d'outils et les tours de sous-agents, et
masque les motifs de secrets (clés Stripe, tokens GitHub, JWT, `DB_PASSWORD`,
`password=`, emails, clés privées).

**2. Relis.** Le compteur de masquages s'affiche par session — s'il est à zéro
partout, c'est soit propre, soit suspect :

```bash
grep -rin 'mot de passe\|identifiant\|clé\|accès' export-transcripts/ | head -30
```

**3. Dépose dans le dépôt :**

```bash
mkdir -p imports/_historique
cp -R export-transcripts/* imports/_historique/
git add -f imports/_historique
git commit -m "Historique Claude Code expurgé"
git push -u origin claude/admiring-newton-0l5yok
```

---

## Ce que j'en ferai

**Des sites** : j'identifie les motifs qui reviennent d'un projet à l'autre et
j'en fais des blocs dans `packages/blocks/`. C'est ce qui manque le plus
aujourd'hui — trois blocs ne suffisent à aucun site réel.

**De l'historique** : je repère ce que tu refais à la main à chaque projet.
Une tâche qui revient dans trois sessions est une skill à écrire. Tes points de
friction récurrents valent mieux qu'une feuille de route théorique.

---

## Si tu préfères ne rien committer

Tu peux aussi joindre les fichiers directement dans la conversation. C'est
adapté à quelques fichiers (un `functions.php`, un thème zippé et allégé), pas
à plusieurs sites — et les mêmes règles de nettoyage s'appliquent.
