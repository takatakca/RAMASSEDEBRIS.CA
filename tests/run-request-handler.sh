#!/usr/bin/env bash
set -euo pipefail
test_case() {
  local scenario="$1" expected="$2" actual
  actual="$(php tests/request-case.php "$scenario")"
  if [[ "$actual" != "$expected" ]]; then
    printf "FAIL %s | expected: %s | actual: %s\n" "$scenario" "$expected" "$actual" >&2
    exit 1
  fi
}
test_case invalid_method 'DENIED:405'
test_case invalid_nonce 'RESULT:expired|SAVED:0|PRIVATE:0|MAIL:0|MAIL_SAFE:0|THROTTLED:0|DELIVERY:-'
test_case honeypot 'RESULT:sent|SAVED:0|PRIVATE:0|MAIL:0|MAIL_SAFE:0|THROTTLED:0|DELIVERY:-'
test_case limited 'RESULT:limited|SAVED:0|PRIVATE:0|MAIL:0|MAIL_SAFE:0|THROTTLED:0|DELIVERY:-'
test_case invalid_fields 'RESULT:invalid|SAVED:0|PRIVATE:0|MAIL:0|MAIL_SAFE:0|THROTTLED:0|DELIVERY:-'
test_case missing_consent 'RESULT:invalid|SAVED:0|PRIVATE:0|MAIL:0|MAIL_SAFE:0|THROTTLED:0|DELIVERY:-'
test_case array_input 'RESULT:invalid|SAVED:0|PRIVATE:0|MAIL:0|MAIL_SAFE:0|THROTTLED:0|DELIVERY:-'
test_case db_error 'RESULT:error|SAVED:0|PRIVATE:0|MAIL:0|MAIL_SAFE:0|THROTTLED:0|DELIVERY:-'
test_case success 'RESULT:sent|SAVED:1|PRIVATE:1|MAIL:1|MAIL_SAFE:1|THROTTLED:1|DELIVERY:1'
test_case smtp_failure 'RESULT:sent|SAVED:1|PRIVATE:1|MAIL:1|MAIL_SAFE:1|THROTTLED:1|DELIVERY:0'
echo "PASS: request intake, validation, notification and failure handling"
