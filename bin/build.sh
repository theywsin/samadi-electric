#!/usr/bin/env bash
# Builds installable ZIPs into dist/: theme + companion plugin.
# - regenerates the plugin's standalone.css from the theme's component styles
# - regenerates translation templates (.pot)
# - lints every PHP file
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
THEME="$ROOT/samadi-electric"
PLUGIN="$ROOT/samadi-elementor-widgets"
DIST="$ROOT/dist"

cat "$PLUGIN/assets/css/standalone-base.css" "$THEME/assets/css/components.css" > "$PLUGIN/assets/css/standalone.css"

php "$ROOT/bin/make-pot.php" "$THEME" samadi-electric "$THEME/languages/samadi-electric.pot"
php "$ROOT/bin/make-pot.php" "$PLUGIN" samadi-elementor-widgets "$PLUGIN/languages/samadi-elementor-widgets.pot"

find "$THEME" "$PLUGIN" -name '*.php' -print0 | xargs -0 -n1 php -l > /dev/null

rm -rf "$DIST" && mkdir -p "$DIST"
( cd "$ROOT" && zip -qr "$DIST/samadi-electric.zip" samadi-electric -x '*.DS_Store' )
( cd "$ROOT" && zip -qr "$DIST/samadi-elementor-widgets.zip" samadi-elementor-widgets -x '*.DS_Store' 'samadi-elementor-widgets/assets/css/standalone-base.css' )
ls -la "$DIST"
