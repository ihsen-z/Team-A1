#!/usr/bin/env bash
# Étape 1 — Installe WordPress, les extensions du preset, et le thème socle.
# Usage : tooling/scaffold.sh clients/<slug>
set -euo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib/common.sh"
source "$FACTORY_ROOT/tooling/lib/validate.sh"

project_dir="${1:?Usage: scaffold.sh clients/<slug>}"
brief="$project_dir/client.yml"
plan="$project_dir/project-plan.yml"

validate_brief "$brief"
require_approved_plan "$plan"

slug="$(yml_get "$brief" slug)"
nom="$(yml_get "$brief" nom)"
preset_id="$(yml_get "$plan" preset)"
stack="$(yml_get "$plan" stack)"
preset="$FACTORY_ROOT/presets/${preset_id}.yml"
[[ -f "$preset" ]] || die "Preset introuvable : $preset"

adapter="$(yml_get "$FACTORY_ROOT/governance/decision-matrix.yml" "stacks.${stack}.adapter")"
[[ -n "$adapter" ]] || die "Stack « $stack » absente de la matrice."
load_adapter "$adapter"
adapter_preflight

site_dir="${SITES_ROOT:-$HOME/sites}/$slug"
site_url="${SITE_URL:-http://$slug.test}"

step "Scaffold — $nom ($preset_id / $stack)"
adapter_bootstrap "$site_dir" "$site_url" "$nom" \
  "${WP_ADMIN_USER:-admin}" "${WP_ADMIN_EMAIL:?WP_ADMIN_EMAIL requis}"

step "Extensions"
mapfile -t required < <(yml_list "$preset" "plugins.required")
[[ ${#required[@]} -gt 0 ]] && adapter_install_plugins "$site_dir" "${required[@]}"

mapfile -t extra < <(yml_list "$plan" "extra_plugins")
[[ ${#extra[@]} -gt 0 ]] && adapter_install_plugins "$site_dir" "${extra[@]}"

mapfile -t forbidden < <(yml_list "$preset" "plugins.forbidden")
[[ ${#forbidden[@]} -gt 0 ]] && adapter_remove_plugins "$site_dir" "${forbidden[@]}"

step "Thème socle"
build_theme="$project_dir/build/theme"
rm -rf "$build_theme"; mkdir -p "$build_theme"
cp -R "$FACTORY_ROOT/packages/theme-core/." "$build_theme/"
mkdir -p "$build_theme/blocks"

# Seuls les blocs autorisés par le preset sont copiés : ce qui n'est pas
# dans le système n'existe pas sur le site.
while IFS= read -r block; do
  [[ "$block" == factory/* ]] || continue
  name="${block#factory/}"
  src="$FACTORY_ROOT/packages/blocks/$name"
  [[ -d "$src" ]] || { warn "Bloc déclaré mais absent : $block"; continue; }
  cp -R "$src" "$build_theme/blocks/$name"
  ok "bloc $block"
done < <(yml_list "$preset" "blocks_allowed")

adapter_install_theme "$site_dir" "$build_theme"

step "Réglages WordPress"
opts=()
while IFS= read -r line; do
  [[ "$line" =~ ^[[:space:]]*# || -z "${line// }" ]] && continue
  [[ "$line" =~ ^[[:space:]]{2}([a-z_]+):[[:space:]]*(.+)$ ]] || continue
  opts+=("${BASH_REMATCH[1]}=${BASH_REMATCH[2]//\"/}")
done < <(sed -n '/^wp_options:/,/^[a-z]/p' "$preset")
[[ ${#opts[@]} -gt 0 ]] && adapter_set_options "$site_dir" "${opts[@]}"

adapter_flush "$site_dir"
ok "Scaffold terminé → $site_dir  ($site_url)"
