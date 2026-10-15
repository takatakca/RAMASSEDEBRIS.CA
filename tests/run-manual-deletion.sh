#!/usr/bin/env bash
set -euo pipefail
test_case() {
  local scenario="$1" expected="$2" actual
  actual="$(php tests/manual-delete-case.php "$scenario")"
  if [[ "$actual" != "$expected" ]]; then
    echo "FAIL: deletion $scenario expected '$expected', got '$actual'" >&2
    exit 1
  fi
}
test_case unauthorized "DENIED:403"
test_case get "DENIED:405"
test_case badnonce "DENIED:400"
test_case unconfirmed "DENIED:400"
test_case wrongtype "DENIED:400"
test_case failure "DENIED:500"
test_case success "DELETED:11:REDIRECT:303"
echo "PASS: manual deletion access control, CSRF, confirmation, post type and error handling"
