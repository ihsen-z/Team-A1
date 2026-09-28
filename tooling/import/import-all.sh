#!/usr/bin/env bash
# =============================================================================
#  Import complet : sites + historique de conversations, en une commande.
# =============================================================================
#  Enchaîne l'extraction de plusieurs sites, l'élagage des thèmes commerciaux,
#  l'export expurgé des transcripts Claude Code, un contrôle de fuite
#  consolidé, puis le commit et le push.
#
#  Le push est automatique SI ET SEULEMENT SI le contrôle de fuite est propre.
#  Au moindre doute, le script s'arrête et te laisse décider.
#
#  Usage :
#    tooling/import/import-all.sh                      # auto-détection
#    tooling/import/import-all.sh "/c/.../public:famma" "/c/.../autre:evasions"
#
#  Options :
#    --no-push        prépare le commit mais ne pousse pas
#    --no-transcripts saute l'export de l'historique
#    --keep-parents   conserve les thèmes commerciaux (Kadence, Divi…)
#    --dry-run        montre ce qui serait fait, n'écrit rien
# =============================================================================

set -euo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/lib/common.sh"

PUSH=1; TRANSCRIPTS=1; PRUNE_PARENTS=1; DRY=0
TARGETS=()

while [[ $# -gt 0 ]]; do
  case "$1" in
    --no-push)        PUSH=0 ;;
    --no-transcripts) TRANSCRIPTS=0 ;;
    --keep-parents)   PRUNE_PARENTS=0 ;;
    --dry-run)        DRY=1; PUSH=0 ;;
    -h|--help)        sed -n '2,25p' "$0"; exit 0 ;;
    *)                TARGETS+=("$1") ;;
  esac
  shift
done

# Thèmes achetés ou tiers : du code qui n'apprend rien sur la façon de
# travailler de l'atelier, et qui pèse lourd. Les enfants sont conservés.
readonly PARENT_THEMES=(
  kadence astra generatepress divi Divi avada betheme bricks blocksy
  oceanwp neve hello-elementor storefront flatsome woodmart salient enfold
)

# -----------------------------------------------------------------------------
#  0. Préflight — échouer maintenant plutôt qu'après dix minutes de travail
# -----------------------------------------------------------------------------
step "Vérifications préalables"

require_cmd git tar find grep

# L'identité Git manquante n'apparaîtrait qu'au commit, après toute
# l'extraction. On la vérifie avant de toucher au moindre fichier.
if ! git -C "$FACTORY_ROOT" config user.email >/dev/null 2>&1; then
  cat >&2 <<'AIDE'
Ton identité Git n'est pas configurée : le commit échouerait à la fin.

Règle-la une fois pour toutes :

  git config --global user.name  "Ton Nom"
  git config --global user.email "ton@email.fr"

Puis relance cette commande.
AIDE
  exit 1
fi
ok "identité Git : $(git -C "$FACTORY_ROOT" config user.name) <$(git -C "$FACTORY_ROOT" config user.email)>"

# Un dépôt en désordre rendrait le commit final ambigu.
if [[ -n "$(git -C "$FACTORY_ROOT" status --porcelain --untracked-files=no)" ]]; then
  warn "Des modifications non committées existent hors de imports/ — elles ne seront pas incluses."
fi

branch="$(git -C "$FACTORY_ROOT" rev-parse --abbrev-ref HEAD)"
ok "branche : $branch"

# -----------------------------------------------------------------------------
#  1. Localiser les sites
# -----------------------------------------------------------------------------
step "Repérage des sites"

declare -a SITES=()   # entrées "chemin:slug"

if [[ ${#TARGETS[@]} -gt 0 ]]; then
  for t in "${TARGETS[@]}"; do
    if [[ "$t" == *:* && -d "${t%:*}" ]]; then
      SITES+=("$t")
    elif [[ -d "$t" ]]; then
      SITES+=("$t:$(basename "$t" | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9' '-' | sed 's/^-//;s/-$//')")
    else
      warn "Chemin ignoré (introuvable) : $t"
    fi
  done
else
  # Auto-détection des emplacements habituels sous Windows, macOS et Linux.
  shopt -s nullglob
  for candidate in \
    "$HOME/Local Sites"/*/app/public \
    "$HOME/Local Sites"/*/app/public/. \
    "$HOME"/htdocs/* "$HOME"/www/* \
    /c/xampp/htdocs/* /c/wamp64/www/* /c/laragon/www/* \
    /Applications/MAMP/htdocs/*
  do
    [[ -d "$candidate/wp-content/themes" ]] || continue
    # Le nom du site vient du dossier Local, pas de « public ».
    slug="$(printf '%s' "$candidate" | sed -E 's#/app/public/?$##' | xargs basename \
            | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9' '-' | sed 's/^-//;s/-$//')"
    SITES+=("$candidate:$slug")
  done
  shopt -u nullglob
fi

if [[ ${#SITES[@]} -eq 0 ]]; then
  die "Aucun site trouvé. Passe les chemins explicitement :
  $0 \"/c/Users/DELL/Local Sites/famma/app/public:famma\""
fi

for entry in "${SITES[@]}"; do
  ok "${entry##*:}  ←  ${entry%:*}"
done

if [[ $DRY -eq 1 ]]; then
  warn "--dry-run : rien n'est écrit."
  exit 0
fi

# -----------------------------------------------------------------------------
#  2. Extraire chaque site
# -----------------------------------------------------------------------------
extracted=()
for entry in "${SITES[@]}"; do
  path="${entry%:*}"; slug="${entry##*:}"
  step "Extraction — $slug"

  if ! "$FACTORY_ROOT/tooling/import/extract-site.sh" "$path" "$slug"; then
    warn "Échec sur « $slug » — on continue avec les autres."
    continue
  fi

  if [[ $PRUNE_PARENTS -eq 1 ]]; then
    for parent in "${PARENT_THEMES[@]}"; do
      target="$FACTORY_ROOT/imports/$slug/themes/$parent"
      if [[ -d "$target" ]]; then
        size="$(du -sh "$target" | cut -f1)"
        rm -rf "$target"
        ok "thème commercial retiré : $parent ($size libérés)"
      fi
    done
    # Un import vidé de tout thème n'a plus d'intérêt : préviens.
    remaining="$(find "$FACTORY_ROOT/imports/$slug/themes" -mindepth 1 -maxdepth 1 -type d 2>/dev/null | wc -l)"
    [[ "$remaining" -eq 0 ]] && warn "$slug : plus aucun thème après élagage — seuls les patterns restent."
  fi

  extracted+=("$slug")
done

[[ ${#extracted[@]} -gt 0 ]] || die "Aucun site extrait. Rien à committer."

# -----------------------------------------------------------------------------
#  3. Exporter l'historique des conversations
# -----------------------------------------------------------------------------
if [[ $TRANSCRIPTS -eq 1 ]]; then
  step "Historique Claude Code"
  if command -v python3 >/dev/null 2>&1; then
    if [[ -d "$HOME/.claude/projects" ]]; then
      python3 "$FACTORY_ROOT/tooling/import/export-transcripts.py" \
        --out "$FACTORY_ROOT/imports/_historique" || warn "Export d'historique en échec."
    else
      warn "~/.claude/projects absent — aucun historique local à exporter."
    fi
  else
    warn "python3 absent : historique sauté."
    warn "Sous Windows : « winget install Python.Python.3 », ou refais cette étape depuis WSL."
  fi
fi

# -----------------------------------------------------------------------------
#  4. Contrôle de fuite consolidé
# -----------------------------------------------------------------------------
step "Contrôle de fuite sur l'ensemble de imports/"

# Motifs de vrais secrets. « password » seul matche des classes CSS WooCommerce :
# on cible des formes qui ne laissent pas de doute.
readonly SECRET_RE='DB_PASSWORD|DB_USER|AUTH_SALT|SECURE_AUTH_KEY|NONCE_SALT|LOGGED_IN_SALT|sk_live_[A-Za-z0-9]{10}|pk_live_[A-Za-z0-9]{10}|ghp_[A-Za-z0-9]{20}|AKIA[0-9A-Z]{16}|BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY|AWS_SECRET|-----BEGIN CERTIFICATE'

suspects=0
while IFS= read -r hit; do
  [[ -z "$hit" ]] && continue
  warn "SUSPECT : $hit"
  grep -inE "$SECRET_RE" "$hit" 2>/dev/null | head -3 | sed 's/^/        /' >&2
  suspects=$((suspects + 1))
done < <(grep -rilE "$SECRET_RE" "$FACTORY_ROOT/imports" 2>/dev/null || true)

if [[ $suspects -gt 0 ]]; then
  printf '\n' >&2
  die "$suspects fichier(s) suspect(s). Rien n'a été committé.
Examine les extraits ci-dessus. S'ils sont anodins, relance avec --no-push et
committe toi-même après vérification."
fi
ok "aucun secret détecté"

# -----------------------------------------------------------------------------
#  5. Récapitulatif
# -----------------------------------------------------------------------------
step "Récapitulatif"
for slug in "${extracted[@]}"; do
  dir="$FACTORY_ROOT/imports/$slug"
  themes="$(find "$dir/themes" -mindepth 1 -maxdepth 1 -type d 2>/dev/null | wc -l)"
  patterns="$(find "$dir/patterns" -type f 2>/dev/null | wc -l)"
  printf '  %-20s %-8s %s thème(s), %s pattern(s)\n' \
    "$slug" "$(du -sh "$dir" | cut -f1)" "$themes" "$patterns"
done
if [[ -d "$FACTORY_ROOT/imports/_historique" ]]; then
  sessions="$(find "$FACTORY_ROOT/imports/_historique" -name '*.md' ! -name INDEX.md 2>/dev/null | wc -l)"
  printf '  %-20s %-8s %s session(s)\n' "_historique" \
    "$(du -sh "$FACTORY_ROOT/imports/_historique" | cut -f1)" "$sessions"
fi

# -----------------------------------------------------------------------------
#  6. Commit et push
# -----------------------------------------------------------------------------
step "Enregistrement"
cd "$FACTORY_ROOT"

git add -f imports/
if git diff --cached --quiet; then
  ok "Rien de nouveau à committer."
  exit 0
fi

git commit -q -m "Import : $(IFS=', '; echo "${extracted[*]}") pour extraction de blocs"
ok "Commit créé"

if [[ $PUSH -eq 1 ]]; then
  for attempt in 1 2 3 4; do
    if git push -u origin "$branch"; then
      ok "Poussé sur $branch"
      exit 0
    fi
    delay=$((2 ** attempt))
    warn "Push en échec, nouvelle tentative dans ${delay}s"
    sleep "$delay"
  done
  die "Push impossible après 4 tentatives. Le commit est en local : « git push » quand le réseau revient."
else
  ok "Commit prêt. Pousse avec : git push -u origin $(git rev-parse --abbrev-ref HEAD)"
fi
