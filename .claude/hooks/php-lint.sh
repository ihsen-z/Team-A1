#!/usr/bin/env bash
# Hook PostToolUse — contrôle qualité automatique après chaque édition PHP.
#
# C'est la brique qui rend le code généré fiable : l'agent voit ses erreurs
# immédiatement et se corrige seul, sans que tu aies à relire chaque diff.
#
# Entrée : JSON du hook sur stdin.
# Sortie : code 2 = bloquant, l'agent reçoit stderr et doit corriger.

set -uo pipefail

payload="$(cat)"

file="$(printf '%s' "$payload" | python3 -c '
import json, sys
try:
    data = json.load(sys.stdin)
except Exception:
    print(""); sys.exit(0)
inp = data.get("tool_input") or {}
print(inp.get("file_path") or inp.get("path") or "")
' 2>/dev/null)"

[[ -z "$file" || ! -f "$file" ]] && exit 0

root="${CLAUDE_PROJECT_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)}"
status=0

case "$file" in
  *.php)
    if ! out="$(php -l "$file" 2>&1)"; then
      printf 'Erreur de syntaxe PHP :\n%s\n' "$out" >&2
      status=2
    fi

    phpcs="$root/vendor/bin/phpcs"
    if [[ $status -eq 0 && -x "$phpcs" ]]; then
      if ! out="$("$phpcs" --standard="$root/phpcs.xml" --report=full "$file" 2>&1)"; then
        printf 'WordPress Coding Standards — écarts à corriger :\n%s\n' "$out" >&2
        printf '\nCorrection automatique possible : vendor/bin/phpcbf %s\n' "$file" >&2
        status=2
      fi
    fi

    phpstan="$root/vendor/bin/phpstan"
    if [[ $status -eq 0 && -x "$phpstan" ]]; then
      if ! out="$("$phpstan" analyse --no-progress --error-format=raw "$file" 2>&1)"; then
        printf 'PHPStan :\n%s\n' "$out" >&2
        status=2
      fi
    fi
    ;;

  *.json)
    if ! out="$(python3 -m json.tool "$file" /dev/null 2>&1)"; then
      printf 'JSON invalide :\n%s\n' "$out" >&2
      status=2
    fi
    # Un block.json doit rester conforme au pattern maison.
    if [[ "$(basename "$file")" == "block.json" ]]; then
      name="$(python3 -c "import json;print(json.load(open('$file')).get('name',''))" 2>/dev/null)"
      [[ "$name" == factory/* ]] || {
        printf 'block.json : le nom doit être préfixé « factory/ » (trouvé : %s)\n' "${name:-absent}" >&2
        status=2
      }
    fi
    ;;

  *.sh)
    if ! out="$(bash -n "$file" 2>&1)"; then
      printf 'Erreur de syntaxe shell :\n%s\n' "$out" >&2
      status=2
    fi
    ;;
esac

exit $status
