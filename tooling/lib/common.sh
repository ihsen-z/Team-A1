#!/usr/bin/env bash
# Fonctions partagées par tous les scripts du pipeline.
# shellcheck shell=bash

set -euo pipefail

FACTORY_ROOT="${FACTORY_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
export FACTORY_ROOT

# --- Sortie ------------------------------------------------------------------
_c_reset=$'\033[0m'; _c_red=$'\033[31m'; _c_grn=$'\033[32m'
_c_ylw=$'\033[33m';  _c_blu=$'\033[34m'; _c_dim=$'\033[2m'

log()   { printf '%s==>%s %s\n' "$_c_blu" "$_c_reset" "$*"; }
ok()    { printf '%s  ✓%s %s\n' "$_c_grn" "$_c_reset" "$*"; }
warn()  { printf '%s  !%s %s\n' "$_c_ylw" "$_c_reset" "$*" >&2; }
die()   { printf '%s  ✗%s %s\n' "$_c_red" "$_c_reset" "$*" >&2; exit 1; }
step()  { printf '\n%s── %s ──%s\n' "$_c_dim" "$*" "$_c_reset"; }

# --- Lecture YAML ------------------------------------------------------------
# Usage : yml_get <fichier> <chemin.pointé> [défaut]
# Préfère yq ; retombe sur python3+PyYAML. Échoue clairement si aucun des deux.
_YML_ENGINE=""
_detect_yml_engine() {
  [[ -n "$_YML_ENGINE" ]] && return 0
  if command -v yq >/dev/null 2>&1; then
    _YML_ENGINE=yq
  elif python3 -c 'import yaml' >/dev/null 2>&1; then
    _YML_ENGINE=python
  else
    die "Aucun lecteur YAML. Installe yq (https://github.com/mikefarah/yq) ou 'pip install pyyaml'."
  fi
}

# Construit une expression yq à partir d'un chemin pointé.
# Les clés sont mises entre crochets : sans ça, yq lit « wp-classic » comme une
# soustraction et renvoie vide en silence.
_yq_path() {
  local expr="" part
  local IFS='.'
  for part in $1; do
    if [[ "$part" =~ ^[0-9]+$ ]]; then
      expr+="[$part]"
    else
      expr+="[\"$part\"]"
    fi
  done
  printf '.%s' "${expr#.}"
}

yml_get() {
  local file="$1" path="$2" default="${3-}"
  [[ -f "$file" ]] || die "Fichier introuvable : $file"
  _detect_yml_engine
  local out
  if [[ "$_YML_ENGINE" == yq ]]; then
    # « // "" » traiterait false comme absent : on teste null explicitement.
    out="$(yq -r "$(_yq_path "$path") | if . == null then \"\" else . end" "$file" 2>/dev/null || true)"
  else
    out="$(python3 - "$file" "$path" <<'PY' 2>/dev/null || true
import sys, yaml
doc = yaml.safe_load(open(sys.argv[1])) or {}
cur = doc
for part in sys.argv[2].split('.'):
    if isinstance(cur, list):
        try: cur = cur[int(part)]
        except (ValueError, IndexError): cur = None; break
    elif isinstance(cur, dict):
        cur = cur.get(part)
    else:
        cur = None; break
    if cur is None: break
if cur is None or cur == {}: print("")
elif isinstance(cur, bool): print("true" if cur else "false")
elif isinstance(cur, (list, dict)): print(__import__("json").dumps(cur, ensure_ascii=False))
else: print(cur)
PY
)"
  fi
  [[ -z "$out" || "$out" == "null" ]] && out="$default"
  printf '%s' "$out"
}

# Usage : yml_list <fichier> <chemin> → une entrée par ligne
yml_list() {
  local file="$1" path="$2"
  _detect_yml_engine
  if [[ "$_YML_ENGINE" == yq ]]; then
    yq -r "$(_yq_path "$path")[]? // empty" "$file" 2>/dev/null || true
  else
    python3 - "$file" "$path" <<'PY' 2>/dev/null || true
import sys, yaml
doc = yaml.safe_load(open(sys.argv[1])) or {}
cur = doc
for part in sys.argv[2].split('.'):
    cur = cur.get(part) if isinstance(cur, dict) else None
    if cur is None: break
if isinstance(cur, list):
    for item in cur:
        print(item if not isinstance(item, (dict, list)) else __import__("json").dumps(item, ensure_ascii=False))
PY
  fi
}

# --- Garde-fous ---------------------------------------------------------------
require_cmd() {
  for c in "$@"; do
    command -v "$c" >/dev/null 2>&1 || die "Commande requise absente : $c"
  done
}

# Charge l'adaptateur de stack. C'est ICI que se joue la portabilité :
# aucun script métier n'appelle wp-cli ou composer directement.
load_adapter() {
  local adapter="$1"
  local path="$FACTORY_ROOT/tooling/adapters/${adapter}.sh"
  [[ -f "$path" ]] || die "Adaptateur « $adapter » absent ($path). Stack pas encore supportée."
  # shellcheck source=/dev/null
  source "$path"
  ok "Adaptateur chargé : $adapter"
}

# Le plan doit être approuvé par un humain avant toute exécution.
require_approved_plan() {
  local plan="$1"
  [[ -f "$plan" ]] || die "Plan absent : $plan — lance d'abord /kickoff."
  local status; status="$(yml_get "$plan" "status")"
  [[ "$status" == "approved" ]] \
    || die "Plan non approuvé (status: ${status:-absent}). Relis $plan, puis passe status à « approved »."
}
