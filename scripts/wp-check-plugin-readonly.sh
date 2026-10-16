#!/usr/bin/env bash
# Read-only release verification. Never activates, deletes or updates plugins.
# Usage: bash scripts/wp-check-plugin-readonly.sh /absolute/wordpress/root 0.5.0
set -euo pipefail

ROOT="${1:-}"
EXPECTED="${2:-0.5.0}"
if [[ -z "$ROOT" || "$ROOT" != /* || ! -f "$ROOT/wp-load.php" || ! -d "$ROOT/wp-includes" ]]; then
  echo "ERROR: Provide the verified absolute root of an existing WordPress install." >&2
  exit 2
fi
if [[ "$(id -u)" -eq 0 ]]; then
  echo "ERROR: Run as an authorized unprivileged WordPress user." >&2
  exit 2
fi
if ! command -v wp >/dev/null 2>&1; then
  echo "ERROR: WP-CLI is required for read-only verification." >&2
  exit 2
fi
ROOT="$(cd "$ROOT" && pwd -P)"
if ! wp --path="$ROOT" --skip-plugins --skip-themes core is-installed >/dev/null; then
  echo "ERROR: No installed WordPress instance at the verified root." >&2
  exit 2
fi
VERSION="$(wp --path="$ROOT" --skip-plugins --skip-themes plugin get ramassedebris-service --field=version)"
STATUS="$(wp --path="$ROOT" --skip-plugins --skip-themes plugin get ramassedebris-service --field=status)"
printf 'Plugin: ramassedebris-service\nVersion: %s\nStatus: %s\n' "$VERSION" "$STATUS"
if [[ "$VERSION" != "$EXPECTED" ]]; then
  echo "ERROR: Installed plugin version does not match the expected release." >&2
  exit 1
fi
if [[ "$STATUS" != "active" && "$STATUS" != "active-network" ]]; then
  echo "ERROR: Plugin files exist but the plugin is not activated." >&2
  exit 1
fi
echo "PASS: WordPress reports the expected active plugin version."
