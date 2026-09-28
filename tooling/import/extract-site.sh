#!/usr/bin/env bash
# =============================================================================
#  Extraction d'un site WordPress existant, pour alimenter la bibliothèque.
# =============================================================================
#  Ne sort QUE ce qui sert à construire le socle : thèmes, patterns, liste des
#  extensions, structure. Refuse activement de copier les secrets, la base et
#  les fichiers envoyés par les clients.
#
#  Fonctionne sur une copie locale ou une sauvegarde décompressée — aucun accès
#  à la base ni à WP-CLI n'est nécessaire.
#
#  Usage : tooling/import/extract-site.sh /chemin/vers/site [nom-court]
#  Sortie : imports/<nom-court>/
# =============================================================================

set -euo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/lib/common.sh"

src="${1:?Usage: extract-site.sh /chemin/vers/site [nom-court]}"
[[ -d "$src" ]] || die "Dossier introuvable : $src"

slug="${2:-$(basename "$src" | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9' '-' | sed 's/^-//;s/-$//')}"
out="$FACTORY_ROOT/imports/$slug"

# Localise wp-content, que la racine soit le site ou une sauvegarde.
content=""
for candidate in "$src/wp-content" "$src/public_html/wp-content" "$src"; do
  [[ -d "$candidate/themes" ]] && { content="$candidate"; break; }
done
if [[ -z "$content" ]]; then
  warn "wp-content/themes introuvable sous : $src"
  cat >&2 <<'AIDE'

Deux causes habituelles :

  1. Tu as visé trop profond. Passe la RACINE du site (le dossier qui contient
     wp-content), pas un thème :
       ✗ .../wp-content/themes/mon-theme
       ✓ .../app/public

  2. Chemin Windows avec des antislashs. Dans Git Bash, utilise des slashs et
     le préfixe /c/ :
       ✗ "C:\Users\DELL\Local Sites\famma\app\public"
       ✓ "/c/Users/DELL/Local Sites/famma/app/public"
     Les guillemets sont nécessaires si le chemin contient un espace.

AIDE
  exit 1
fi

step "Extraction de « $slug » depuis $content"
rm -rf "$out"; mkdir -p "$out"/{themes,patterns,notes}

# --- Ce qui n'est JAMAIS copié ----------------------------------------------
# Ces motifs sont la raison d'être du script. Ne les allège pas.
readonly EXCLUDE_PATTERNS=(
  'wp-config.php' 'wp-config-sample.php'
  '.env' '.env.*'
  '*.sql' '*.sql.gz' '*.log'
  '.git' '.svn'
  'node_modules' 'vendor'
  '*.zip' '*.tar.gz'
  '*.pem' '*.key' '*.p12' '.htpasswd'
)

# Copie filtrée. Un outil de copie qui perdrait les exclusions ferait fuiter
# des secrets en silence : on n'accepte donc aucun repli non filtré, et on
# purge systématiquement la destination derrière la copie.
copy_filtered() {
  local from="$1" to="$2"
  mkdir -p "$to"

  local tar_args=()
  local pattern
  for pattern in "${EXCLUDE_PATTERNS[@]}"; do
    tar_args+=(--exclude="$pattern")
  done

  if command -v tar >/dev/null 2>&1; then
    ( cd "$from" && tar cf - "${tar_args[@]}" . ) | ( cd "$to" && tar xf - )
  else
    die "tar est requis pour une copie filtrée (rsync seul ne suffit pas ici)."
  fi

  prune_excluded "$to"
}

# Deuxième barrière : même si la copie laissait passer quelque chose, ces
# chemins disparaissent de la destination.
prune_excluded() {
  local target="$1" pattern
  for pattern in "${EXCLUDE_PATTERNS[@]}"; do
    find "$target" -depth -name "$pattern" -exec rm -rf {} + 2>/dev/null || true
  done
}

# --- Thèmes ------------------------------------------------------------------
step "Thèmes"
shopt -s nullglob
for theme_dir in "$content"/themes/*/; do
  name="$(basename "$theme_dir")"
  # Les thèmes livrés avec WordPress n'apprennent rien sur ta façon de faire.
  case "$name" in twenty*|index.php) ok "ignoré (thème par défaut) : $name"; continue ;; esac
  copy_filtered "$theme_dir" "$out/themes/$name"
  size="$(du -sh "$out/themes/$name" | cut -f1)"
  ok "thème $name ($size)"
done

# --- Patterns et compositions réutilisables ---------------------------------
step "Patterns"
found_patterns=0
while IFS= read -r p; do
  rel="${p#$content/}"
  mkdir -p "$out/patterns/$(dirname "$rel")"
  cp "$p" "$out/patterns/$rel"
  found_patterns=$((found_patterns + 1))
done < <(find "$content/themes" -path '*/patterns/*' \( -name '*.php' -o -name '*.html' \) 2>/dev/null)
ok "$found_patterns fichier(s) de pattern"

# --- Inventaire des extensions (noms de dossiers, pas le code) --------------
step "Extensions"
{
  echo "# Extensions présentes sur $slug"
  echo "# Relevé le $(date -I) — noms de dossiers, le code n'est pas copié."
  echo
  if [[ -d "$content/plugins" ]]; then
    for d in "$content/plugins"/*/; do
      [[ -d "$d" ]] || continue
      n="$(basename "$d")"
      # La version se lit dans l'en-tête du fichier principal.
      v="$(grep -rhoiE '^\s*\*?\s*Version:\s*\S+' "$d"/*.php 2>/dev/null | head -1 | awk '{print $NF}')"
      printf '%-40s %s\n' "$n" "${v:-version inconnue}"
    done
  fi
  if [[ -d "$content/mu-plugins" ]]; then
    echo
    echo "# mu-plugins"
    ls -1 "$content/mu-plugins" 2>/dev/null
  fi
} > "$out/notes/plugins.txt"
ok "$(grep -cv '^#\|^$' "$out/notes/plugins.txt" || echo 0) extension(s) recensée(s)"

# --- Empreinte de la médiathèque (compte et poids, pas les fichiers) --------
step "Médiathèque (statistiques seules)"
{
  echo "# Médiathèque de $slug — aucune image n'est copiée."
  if [[ -d "$content/uploads" ]]; then
    echo "Fichiers : $(find "$content/uploads" -type f 2>/dev/null | wc -l)"
    echo "Poids    : $(du -sh "$content/uploads" 2>/dev/null | cut -f1)"
    echo
    echo "# Répartition par extension"
    find "$content/uploads" -type f 2>/dev/null | sed 's/.*\.//' | tr '[:upper:]' '[:lower:]' \
      | sort | uniq -c | sort -rn | head -15
  else
    echo "Pas de dossier uploads."
  fi
} > "$out/notes/medias.txt"
ok "statistiques médias relevées"

# --- Contrôle : aucun secret ne doit avoir traversé -------------------------
step "Contrôle de fuite"
leaks=0
while IFS= read -r hit; do
  warn "SECRET POTENTIEL : $hit"
  leaks=$((leaks + 1))
done < <(grep -rilE 'DB_PASSWORD|AUTH_SALT|sk_live_|pk_live_|BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY|AWS_SECRET' "$out" 2>/dev/null || true)

if [[ $leaks -gt 0 ]]; then
  die "$leaks fichier(s) suspect(s) dans $out. Inspecte-les et supprime-les AVANT de committer."
fi
ok "aucun secret détecté"

printf '\n'
ok "Extraction terminée → $out  ($(du -sh "$out" | cut -f1))"
cat <<TXT

Avant de committer, vérifie toi-même :
  du -sh $out/*
  grep -ril 'password\|secret\|api_key' $out/

Puis :
  git add -f imports/$slug && git commit -m "Import : $slug pour extraction de blocs"
TXT
