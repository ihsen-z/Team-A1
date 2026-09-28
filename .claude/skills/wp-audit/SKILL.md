---
name: wp-audit
description: Audit sécurité et performance d'un site WordPress — permissions, extensions, durcissement, cache, poids des pages. Utiliser avant mise en ligne, lors d'une reprise de site existant, ou en revue de maintenance.
---

# Audit WordPress

Argument : un dossier client (`clients/<slug>`) ou un chemin d'installation.

## Sécurité

- Versions : cœur, extensions, thèmes, PHP. Toute version en fin de support est
  un finding bloquant, pas une remarque.
- Extensions : chacune est-elle dans le preset ? maintenue ? une CVE connue ?
  Les extensions désactivées mais installées restent exploitables — à supprimer.
- Comptes : pas d'utilisateur `admin`, pas de compte administrateur inactif,
  rôles au minimum nécessaire.
- Fichiers : `wp-config.php` hors du dépôt et non lisible publiquement,
  `DISALLOW_FILE_EDIT` actif, pas de fichier de sauvegarde ou `.sql` exposé,
  `xmlrpc.php` neutralisé, énumération des auteurs bloquée.
- Code : grep sur les motifs dangereux — `$_GET`/`$_POST` non assainis, `echo`
  d'une variable non échappée, `$wpdb->query` sans `prepare`, `eval`,
  `base64_decode`, `unserialize` sur une entrée externe.
- Transport : HTTPS forcé, HSTS, en-têtes de sécurité présents.

## Performance

- Poids et nombre de requêtes de la page d'accueil et d'une page profonde.
- Images : format, dimensions réelles vs affichées, `lazy` correctement posé.
- Requêtes SQL lentes, `autoload` de la table `options` (seuil d'alerte : 1 Mo).
- Cache page actif et réellement servi ; cache objet si VPS.
- Ressources bloquantes dans le `<head>`, polices auto-hébergées ou préchargées.

## Restitution

Écris `reports/<slug>/audit.md`. Classe chaque finding :

- **Bloquant** — interdit la mise en ligne.
- **Important** — à corriger sous 7 jours.
- **Amélioration** — à planifier.

Pour chacun : où (fichier:ligne quand c'est du code), pourquoi c'est un
problème concret, et le correctif exact. Pas de liste générique de bonnes
pratiques : ce rapport doit être actionnable tel quel.

**Tu audites, tu ne corriges pas.** La séparation est volontaire : celui qui
corrige ne doit pas être celui qui juge si c'est corrigé.
