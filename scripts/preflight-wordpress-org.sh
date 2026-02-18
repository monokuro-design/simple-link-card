#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_SLUG="${PLUGIN_SLUG:-simple-link-embed}"
VERSION="${VERSION:-$(sed -n 's/^ \* Version: //p' "$ROOT_DIR/simple-link-embed.php" | head -n 1)}"
RELEASE_DIR="${SLEMB_RELEASE_DIR:-/tmp/${PLUGIN_SLUG}-release}"
ZIP_PATH="${SLEMB_ZIP_PATH:-/tmp/${PLUGIN_SLUG}-${VERSION}.zip}"
PHPCS_BIN="${SLEMB_PHPCS_BIN:-/Users/tadashi/local/develop/app/public/wp-content/plugins/plugin-check/vendor/squizlabs/php_codesniffer/bin/phpcs}"

PLUGINCHECK_REPORT="/tmp/${PLUGIN_SLUG}-plugincheck-preflight.json"
WPCS_REPORT="/tmp/${PLUGIN_SLUG}-wpcs-preflight.json"

if [ ! -f "$PHPCS_BIN" ]; then
  echo "PHPCS binary not found: $PHPCS_BIN" >&2
  echo "Set SLEMB_PHPCS_BIN to your phpcs path." >&2
  exit 1
fi

if [ -z "$VERSION" ]; then
  echo "Could not detect plugin version from simple-link-embed.php." >&2
  exit 1
fi

cd "$ROOT_DIR"

echo "[1/4] Build release directory: $RELEASE_DIR"
rm -rf "$RELEASE_DIR"
mkdir -p "$RELEASE_DIR"
rsync -a --delete --exclude-from=".distignore" ./ "$RELEASE_DIR/"

echo "[2/4] PluginCheck standard"
php "$PHPCS_BIN" --standard=PluginCheck --report=json --extensions=php "$RELEASE_DIR" > "$PLUGINCHECK_REPORT"
php -r '$r=json_decode(file_get_contents($argv[1]), true); if(!$r){fwrite(STDERR,"Invalid PluginCheck JSON\n"); exit(2);} $e=(int)($r["totals"]["errors"]??0); $w=(int)($r["totals"]["warnings"]??0); echo "PluginCheck errors:$e warnings:$w\n"; if($e>0 || $w>0){exit(1);} ' "$PLUGINCHECK_REPORT"

echo "[3/4] WPCS focus sniffs (PrefixAllGlobals, DirectDatabaseQuery)"
php "$PHPCS_BIN" --standard=WordPress --sniffs=WordPress.NamingConventions.PrefixAllGlobals,WordPress.DB.DirectDatabaseQuery --report=json --extensions=php "$RELEASE_DIR" > "$WPCS_REPORT"
php -r '$r=json_decode(file_get_contents($argv[1]), true); if(!$r){fwrite(STDERR,"Invalid WPCS JSON\n"); exit(2);} $e=(int)($r["totals"]["errors"]??0); $w=(int)($r["totals"]["warnings"]??0); echo "WPCS errors:$e warnings:$w\n"; if($e>0 || $w>0){exit(1);} ' "$WPCS_REPORT"

echo "[4/4] Build zip: $ZIP_PATH"
rm -f "$ZIP_PATH"
PKG_DIR="$(mktemp -d /tmp/${PLUGIN_SLUG}-package-XXXX)"
mkdir -p "$PKG_DIR/$PLUGIN_SLUG"
rsync -a --delete "$RELEASE_DIR/" "$PKG_DIR/$PLUGIN_SLUG/"
( cd "$PKG_DIR" && zip -qr "$ZIP_PATH" "$PLUGIN_SLUG" )

echo "Done."
echo "ZIP: $ZIP_PATH"
echo "Reports:"
echo "  $PLUGINCHECK_REPORT"
echo "  $WPCS_REPORT"
