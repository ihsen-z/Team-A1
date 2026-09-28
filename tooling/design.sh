#!/usr/bin/env bash
# Étape 2 — Charte client → theme.json. Le design devient une donnée.
# Usage : tooling/design.sh clients/<slug>
set -euo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib/common.sh"

project_dir="${1:?Usage: design.sh clients/<slug>}"
brief="$project_dir/client.yml"
[[ -f "$brief" ]] || die "Brief introuvable : $brief"

require_cmd php
mkdir -p "$project_dir/build"

step "Génération de theme.json"
php "$FACTORY_ROOT/tooling/php/render-theme-json.php" "$brief" "$project_dir/build/theme.json"

# Le thème doit déjà être construit par scaffold.sh — on y dépose le fichier.
if [[ -d "$project_dir/build/theme" ]]; then
  cp "$project_dir/build/theme.json" "$project_dir/build/theme/theme.json"
  ok "theme.json déposé dans le thème"

  slug="$(yml_get "$brief" slug)"
  site_dir="${SITES_ROOT:-$HOME/sites}/$slug"
  if [[ -d "$site_dir/wp-content/themes/theme" ]]; then
    cp "$project_dir/build/theme.json" "$site_dir/wp-content/themes/theme/theme.json"
    ok "theme.json synchronisé sur le site"
  fi
else
  warn "Thème non construit — lance d'abord tooling/scaffold.sh"
fi
