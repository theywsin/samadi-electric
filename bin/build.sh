#!/usr/bin/env bash
# Builds installable ZIPs into dist/:
#   samadi-elementor-widgets.zip  companion widgets plugin
#   samadi-core.zip               core plugin (setup wizard + full demo; bundles the widgets plugin)
#   samadi-electric.zip           theme (bundles samadi-core.zip for one-click install)
#   samadi-electric-full-package.zip  everything above + install guide
# - regenerates the plugin's standalone.css from the theme's component styles
# - regenerates translation templates (.pot)
# - lints every PHP file
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
THEME="$ROOT/samadi-electric"
PLUGIN="$ROOT/samadi-elementor-widgets"
CORE="$ROOT/samadi-core"
ACCOUNT="$ROOT/samadi-account"
DIST="$ROOT/dist"

cat "$PLUGIN/assets/css/standalone-base.css" "$THEME/assets/css/components.css" > "$PLUGIN/assets/css/standalone.css"

php "$ROOT/bin/make-pot.php" "$THEME" samadi-electric "$THEME/languages/samadi-electric.pot"
php "$ROOT/bin/make-pot.php" "$PLUGIN" samadi-elementor-widgets "$PLUGIN/languages/samadi-elementor-widgets.pot"
php "$ROOT/bin/make-pot.php" "$CORE" samadi-core "$CORE/languages/samadi-core.pot"

find "$THEME" "$PLUGIN" "$CORE" "$ACCOUNT" -name '*.php' -print0 | xargs -0 -n1 php -l > /dev/null

if [ ! -f "$CORE/demo/images/hero-1.jpg" ]; then
	python3 "$ROOT/bin/make-demo-images.py" "$CORE/demo/images"
fi

rm -rf "$DIST" && mkdir -p "$DIST"

( cd "$ROOT" && zip -qr "$DIST/samadi-account.zip" samadi-account -x '*.DS_Store' )
( cd "$ROOT" && zip -qr "$DIST/samadi-elementor-widgets.zip" samadi-elementor-widgets -x '*.DS_Store' 'samadi-elementor-widgets/assets/css/standalone-base.css' )

mkdir -p "$CORE/bundled" && cp "$DIST/samadi-elementor-widgets.zip" "$CORE/bundled/"
( cd "$ROOT" && zip -qr "$DIST/samadi-core.zip" samadi-core -x '*.DS_Store' )

mkdir -p "$THEME/bundled" && cp "$DIST/samadi-core.zip" "$THEME/bundled/"
( cd "$ROOT" && zip -qr "$DIST/samadi-electric.zip" samadi-electric -x '*.DS_Store' )

cp "$ROOT/docs/00-quick-install.md" "$DIST/README-INSTALL-FA.md"
( cd "$DIST" && zip -q samadi-electric-full-package.zip samadi-electric.zip samadi-core.zip samadi-elementor-widgets.zip README-INSTALL-FA.md )
ls -la "$DIST"
