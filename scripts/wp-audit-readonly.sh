#!/usr/bin/env bash
# Inventory a local WordPress installation without revealing secrets or changing data.
# Usage: bash scripts/wp-audit-readonly.sh /absolute/path/to/wordpress
set -euo pipefail
if ! command -v wp >/dev/null 2>&1; then
  echo "ERROR: WP-CLI is not installed or not in PATH" >&2
  exit 2
fi
ROOT="${1:-.}"
if [[ ! -f "$ROOT/wp-load.php" || ! -d "$ROOT/wp-includes" ]]; then
  echo "ERROR: expected WordPress root containing wp-load.php and wp-includes" >&2
  exit 2
fi
ROOT="$(cd "$ROOT" && pwd -P)"
if [[ "$(id -u)" -eq 0 ]]; then
  echo "ERROR: run WP-CLI as an authorized unprivileged site user, not root" >&2
  exit 2
fi
run() {
  wp --path="$ROOT" --skip-plugins --skip-themes "$@"
}
echo "=== WordPress inventory (read only) ==="
echo "Local root verified."
echo "WordPress core version:"
run core version
echo "PHP version:"
php -r 'echo PHP_VERSION, PHP_EOL;'
echo "WordPress home URL:"
run option get home
echo "Installed plugins (no options or secrets):"
run plugin list --fields=name,status,version,update --format=table
echo "Installed themes (no options or secrets):"
run theme list --fields=name,status,version,update --format=table
echo "Scheduled events (hooks only; no arguments):"
run cron event list --fields=hook,next_run_relative,recurrence --format=table
echo "=== END inventory ==="
