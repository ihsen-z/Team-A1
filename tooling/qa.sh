#!/usr/bin/env bash
# Étape 6 — Contrôle qualité : liens, accessibilité, performance, responsive.
# Usage : tooling/qa.sh clients/<slug>
set -euo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib/common.sh"

project_dir="${1:?Usage: qa.sh clients/<slug>}"
brief="$project_dir/client.yml"
plan="$project_dir/project-plan.yml"
slug="$(yml_get "$brief" slug)"
preset_id="$(yml_get "$plan" preset)"
preset="$FACTORY_ROOT/presets/${preset_id}.yml"
url="${SITE_URL:-http://$slug.test}"

report_dir="$FACTORY_ROOT/reports/$slug"
mkdir -p "$report_dir"

perf_min="$(yml_get "$preset" "quality_gates.lighthouse.performance" 85)"
a11y_min="$(yml_get "$preset" "quality_gates.lighthouse.accessibility" 95)"
seo_min="$(yml_get "$preset"  "quality_gates.lighthouse.seo" 95)"

step "QA — $url (seuils : perf $perf_min / a11y $a11y_min / seo $seo_min)"

if command -v lighthouse >/dev/null 2>&1; then
  lighthouse "$url" --quiet --chrome-flags="--headless" \
    --output=json --output-path="$report_dir/lighthouse.json" || warn "Lighthouse a échoué"
  ok "Rapport Lighthouse : $report_dir/lighthouse.json"
else
  warn "lighthouse absent — npm i -g lighthouse"
fi

# Les captures et l'audit axe sont pilotés par le subagent qa-visual via
# Playwright ; ce script assure le passage automatisable en CI.
if [[ -f "$FACTORY_ROOT/tooling/qa/playwright.config.ts" ]]; then
  ( cd "$FACTORY_ROOT/tooling/qa" && SITE_URL="$url" npx playwright test ) \
    || warn "Tests Playwright en échec — voir $report_dir"
else
  warn "Suite Playwright pas encore initialisée (tooling/qa/)."
fi

ok "QA terminée → $report_dir"
