#!/usr/bin/env bash
# HTTP end-to-end checks against the disposable WordPress created in CI.
# NEVER use with a production or staging WordPress installation.
set -euo pipefail

: "$WP_ROOT"
if [[ "$WP_ROOT" != "$RUNNER_TEMP"/rdb-wordpress || ! -f "$WP_ROOT/wp-load.php" ]]; then
  echo "ERROR: HTTP tests only accept the disposable CI WordPress root." >&2
  exit 2
fi
command -v wp >/dev/null
command -v jq >/dev/null
command -v curl >/dev/null

port=18671
base="http://127.0.0.1:$port"
headers="$RUNNER_TEMP/rdb-http-response-headers"
log="$RUNNER_TEMP/rdb-http-server.log"

php -S "127.0.0.1:$port" -t "$WP_ROOT" > "$log" 2>&1 &
server_pid=$!
cleanup() {
  kill "$server_pid" >/dev/null 2>&1 || true
  wait "$server_pid" >/dev/null 2>&1 || true
}
trap cleanup EXIT

ready=false
for _ in $(seq 1 20); do
  if curl --max-time 3 -fsS "$base/wp-login.php" -o /dev/null 2>/dev/null; then
    ready=true
    break
  fi
  sleep 1
done
if [[ "$ready" != true ]]; then
  echo "FAIL: WordPress local HTTP server did not become ready." >&2
  tail -n 20 "$log" >&2
  exit 1
fi

count_requests() {
  wp --path="$WP_ROOT" post list \
    --post_type=rdb_demande --post_status=private --format=json | jq -r 'length'
}
assert_count() {
  local actual
  actual="$(count_requests)"
  if [[ "$actual" != "$1" ]]; then
    printf 'FAIL: expected %s private records; got %s.\n' "$1" "$actual" >&2
    exit 1
  fi
}

nonce="$(curl --max-time 10 -fsS \
  --data 'action=rdb_refresh_nonce' "$base/wp-admin/admin-ajax.php" | jq -r '.data.nonce // empty')"
if [[ ! "$nonce" =~ ^[A-Za-z0-9]{10}$ ]]; then
  echo "FAIL: the real WordPress AJAX endpoint did not issue a usable nonce." >&2
  exit 1
fi

submit_case() {
  local expected="$1" name="$2" details="$3" token="$4" website="$5" status
  shift 5
  status="$(curl -sS --max-time 12 -D "$headers" -o /dev/null -w '%{http_code}' \
    --data-urlencode 'action=rdb_submit_request' \
    --data-urlencode "rdb_nonce=$token" \
    --data-urlencode "rdb_website=$website" \
    --data-urlencode "rdb_name=$name" \
    --data-urlencode 'rdb_phone=514-555-0101' \
    --data-urlencode 'rdb_city=Verdun' \
    --data-urlencode 'rdb_service=meubles' \
    --data-urlencode 'rdb_volume=petit' \
    --data-urlencode "rdb_details=$details" \
    --data-urlencode 'rdb_consent=1' \
    "$@" "$base/wp-admin/admin-post.php")"
  if [[ "$status" != "303" ]] || ! grep -qi "location: .*rdb_result=$expected" "$headers"; then
    echo "FAIL: expected HTTP 303 with result '$expected', received HTTP $status." >&2
    tail -n 20 "$log" >&2
    exit 1
  fi
}

assert_count 0
submit_case invalid "$(printf 'X%.0s' {1..101})" "Un divan et deux commodes." "$nonce" ""
assert_count 0
submit_case invalid "Client de test" "$(printf 'X%.0s' {1..1501})" "$nonce" ""
assert_count 0
submit_case invalid "Client de test" "Un divan et deux commodes." "$nonce" "" 'rdb_email[]=bad'
assert_count 0
submit_case expired "Client de test" "Un divan et deux commodes." invalid ""
assert_count 0
submit_case sent "Robot de test" "Un divan et deux commodes." "$nonce" "spam.example.invalid"
assert_count 0
submit_case sent "Client de test" "Un divan et deux commodes." "$nonce" "" 'rdb_email=client@example.invalid'
assert_count 1

echo "PASS: real HTTP nonce, CSRF rejection, oversized fields, malformed optional data and private POST persistence"
