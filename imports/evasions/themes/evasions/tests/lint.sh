#!/usr/bin/env bash
# Vérifications statiques du thème EVASIONS : syntaxe PHP et JS, en-tête du thème.
#
# Usage : tests/lint.sh [chemin/vers/php]
#
# N'a besoin ni de WordPress ni de Composer. À lancer avant chaque livraison ;
# la suite d'intégration d'EVASIONS Core (plugins/evasions-core/tests/run.php)
# couvre le comportement, ce script n'attrape que ce qui empêcherait le thème de
# se charger. Même modèle que plugins/evasions-core/tests/lint.sh.

set -u

PHP_BIN="${1:-php}"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
fail=0

while IFS= read -r -d '' file; do
	if ! out="$("$PHP_BIN" -l "$file" 2>&1)"; then
		echo "PHP  FAIL  ${file#"$ROOT"/}"
		echo "$out"
		fail=1
	fi
done < <(find "$ROOT" -name '*.php' -print0)

if command -v node >/dev/null 2>&1; then
	if [ -d "$ROOT/assets/js" ]; then
		while IFS= read -r -d '' file; do
			if ! node --check "$file" 2>/dev/null; then
				echo "JS   FAIL  ${file#"$ROOT"/}"
				fail=1
			fi
		done < <(find "$ROOT/assets/js" -name '*.js' ! -name '*.min.js' -print0)
	fi
else
	echo "node absent : contrôle JS ignoré"
fi

for key in "Theme Name" "Text Domain" "Requires PHP" "Version"; do
	if ! grep -q "^$key:" "$ROOT/style.css"; then
		echo "HEAD FAIL  en-tête manquant dans style.css : $key"
		fail=1
	fi
done

if [ "$fail" -eq 0 ]; then
	echo "lint OK"
fi

exit "$fail"
