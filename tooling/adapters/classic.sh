#!/usr/bin/env bash
# Adaptateur : WordPress classique piloté par WP-CLI.
# Implémente tooling/adapters/INTERFACE.md
# shellcheck shell=bash

adapter_name() { printf 'classic'; }

_wp() { wp --path="$1" --skip-plugins --skip-themes "${@:2}"; }
_wpf() { wp --path="$1" "${@:2}"; }   # avec plugins/thèmes chargés

adapter_preflight() {
  require_cmd wp php mysql
  local v; v="$(php -r 'echo PHP_VERSION;')"
  log "PHP $v · WP-CLI $(wp --version | awk '{print $2}')"
  php -r 'exit(version_compare(PHP_VERSION, "8.1", ">=") ? 0 : 1);' \
    || die "PHP >= 8.1 requis (détecté $v)."
  ok "Préflight classic OK"
}

adapter_bootstrap() {
  local dir="$1" url="$2" title="$3" admin_user="$4" admin_email="$5"
  mkdir -p "$dir"
  if [[ ! -f "$dir/wp-load.php" ]]; then
    log "Téléchargement du cœur WordPress"
    wp core download --path="$dir" --locale=fr_FR
  else
    ok "Cœur déjà présent"
  fi
  if ! _wp "$dir" core is-installed 2>/dev/null; then
    [[ -n "${WP_DB_NAME:-}" ]] || die "WP_DB_NAME/WP_DB_USER/WP_DB_PASS requis (voir .env.example)."
    log "Création de wp-config.php"
    wp config create --path="$dir" \
      --dbname="$WP_DB_NAME" --dbuser="$WP_DB_USER" --dbpass="${WP_DB_PASS:-}" \
      --dbhost="${WP_DB_HOST:-localhost}" --dbprefix="${WP_DB_PREFIX:-wp_}" \
      --locale=fr_FR --skip-check --force
    wp config set WP_DEBUG       "${WP_DEBUG:-false}" --path="$dir" --raw
    wp config set DISALLOW_FILE_EDIT true --path="$dir" --raw
    wp config set AUTOMATIC_UPDATER_DISABLED true --path="$dir" --raw
    wp db create --path="$dir" 2>/dev/null || ok "Base déjà existante"
    log "Installation de WordPress"
    wp core install --path="$dir" --url="$url" --title="$title" \
      --admin_user="$admin_user" --admin_email="$admin_email" \
      --admin_password="${WP_ADMIN_PASS:?WP_ADMIN_PASS requis}" --skip-email
    ok "WordPress installé"
  else
    ok "WordPress déjà installé"
  fi
}

adapter_install_plugins() {
  local dir="$1"; shift
  [[ $# -eq 0 ]] && return 0
  for slug in "$@"; do
    if _wp "$dir" plugin is-installed "$slug" 2>/dev/null; then
      _wp "$dir" plugin activate "$slug" >/dev/null 2>&1 || true
      ok "plugin $slug (déjà là)"
    else
      log "plugin $slug"
      _wp "$dir" plugin install "$slug" --activate \
        || warn "Échec d'installation : $slug (à traiter manuellement)"
    fi
  done
}

adapter_remove_plugins() {
  local dir="$1"; shift
  for slug in "$@"; do
    if _wp "$dir" plugin is-installed "$slug" 2>/dev/null; then
      warn "Extension interdite trouvée, suppression : $slug"
      _wp "$dir" plugin delete "$slug" || true
    fi
  done
}

adapter_install_theme() {
  local dir="$1" src="$2"
  local name; name="$(basename "$src")"
  local dest="$dir/wp-content/themes/$name"
  mkdir -p "$(dirname "$dest")"
  rm -rf "$dest"
  cp -R "$src" "$dest"
  _wp "$dir" theme activate "$name"
  ok "Thème activé : $name"
}

adapter_set_options() {
  local dir="$1"; shift
  for kv in "$@"; do
    local k="${kv%%=*}" v="${kv#*=}"
    _wp "$dir" option update "$k" "$v" >/dev/null
    ok "option $k = $v"
  done
}

adapter_import_page() {
  local dir="$1" slug="$2" title="$3" html="$4"
  [[ -f "$html" ]] || die "Contenu introuvable : $html"
  local id
  id="$(_wp "$dir" post list --post_type=page --name="$slug" --field=ID --format=ids 2>/dev/null | head -1)"
  if [[ -n "$id" ]]; then
    _wp "$dir" post update "$id" "$html" --post_title="$title" >/dev/null
    ok "page mise à jour : $slug"
  else
    _wp "$dir" post create "$html" --post_type=page --post_name="$slug" \
      --post_title="$title" --post_status=publish >/dev/null
    ok "page créée : $slug"
  fi
}

adapter_set_front_page() {
  local dir="$1" slug="$2"
  local id; id="$(_wp "$dir" post list --post_type=page --name="$slug" --field=ID --format=ids | head -1)"
  [[ -n "$id" ]] || die "Page d'accueil introuvable : $slug"
  _wp "$dir" option update show_on_front page >/dev/null
  _wp "$dir" option update page_on_front "$id" >/dev/null
  ok "Page d'accueil : $slug (#$id)"
}

adapter_flush() {
  local dir="$1"
  _wp "$dir" rewrite flush --hard >/dev/null
  _wpf "$dir" cache flush >/dev/null 2>&1 || true
  ok "Caches et permaliens vidés"
}

adapter_backup() {
  local dir="$1" dest="$2"
  mkdir -p "$dest"
  local stamp; stamp="$(date +%Y%m%d-%H%M%S)"
  _wp "$dir" db export "$dest/db-$stamp.sql" >/dev/null
  tar -czf "$dest/files-$stamp.tar.gz" -C "$dir" wp-content
  ok "Sauvegarde : $dest (db-$stamp.sql + files-$stamp.tar.gz)"
}

adapter_export_db() { _wp "$1" db export "$2" >/dev/null && ok "Base exportée : $2"; }

adapter_search_replace() {
  local dir="$1" from="$2" to="$3"
  _wp "$dir" search-replace "$from" "$to" --all-tables --precise --report-changed-only
}
