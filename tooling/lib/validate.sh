#!/usr/bin/env bash
# Valide un brief client contre governance/client.schema.yml.
# Échouer ici coûte 10 secondes ; échouer à l'étape 6 coûte une demi-journée.
# shellcheck shell=bash
# shellcheck source=./common.sh

set -euo pipefail
source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/common.sh"

validate_brief() {
  local brief="$1"
  local schema="$FACTORY_ROOT/governance/client.schema.yml"
  local errors=0

  step "Validation du brief"

  while IFS= read -r field; do
    [[ -z "$field" ]] && continue
    local value; value="$(yml_get "$brief" "$field")"
    if [[ -z "$value" ]]; then
      warn "Champ obligatoire manquant : $field"
      errors=$((errors + 1))
    fi
  done < <(yml_list "$schema" "required")

  local slug; slug="$(yml_get "$brief" "slug")"
  [[ "$slug" =~ ^[a-z0-9]([a-z0-9-]*[a-z0-9])?$ ]] \
    || { warn "slug invalide : « $slug » (attendu [a-z0-9-])"; errors=$((errors + 1)); }

  local type; type="$(yml_get "$brief" "type")"
  case "$type" in
    vitrine|business|ecommerce) ;;
    *) warn "type invalide : « $type » (vitrine | business | ecommerce)"; errors=$((errors + 1)) ;;
  esac

  for c in primaire fond texte; do
    local hex; hex="$(yml_get "$brief" "charte.couleurs.$c")"
    [[ "$hex" =~ ^#[0-9a-fA-F]{6}$ || "$hex" =~ ^#[0-9a-fA-F]{3}$ ]] \
      || { warn "charte.couleurs.$c : « $hex » n'est pas une couleur hex"; errors=$((errors + 1)); }
  done

  local pages; pages="$(yml_get "$brief" "pages" 0)"
  [[ "$pages" =~ ^[0-9]+$ && "$pages" -gt 0 ]] \
    || { warn "pages doit être un entier > 0 (reçu : « $pages »)"; errors=$((errors + 1)); }

  # Le budget est comparé au seuil de rentabilité : un avertissement, pas un
  # blocage — c'est ta décision commerciale, pas celle du script.
  local budget seuil
  budget="$(yml_get "$brief" "budget" 0)"
  seuil="$(yml_get "$FACTORY_ROOT/governance/decision-matrix.yml" "seuils_rentabilite.$type" 0)"
  if [[ "$budget" =~ ^[0-9]+$ && "$seuil" =~ ^[0-9]+$ && "$budget" -lt "$seuil" ]]; then
    warn "Budget ${budget}€ sous le seuil de rentabilité ${seuil}€ pour « $type » → escalade recommandée."
  fi

  if [[ "$errors" -gt 0 ]]; then
    die "$errors erreur(s) dans le brief. Corrige $brief avant de relancer."
  fi
  ok "Brief valide"
}
