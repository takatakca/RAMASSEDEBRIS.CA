<?php
// Standalone smoke test: no WordPress installation required.
define('ABSPATH', __DIR__ . '/');
function add_action() {}
function add_filter() {}
function add_shortcode() {}
function register_activation_hook() {}
function register_deactivation_hook() {}
require dirname(__DIR__) . '/plugin/ramassedebris-service/ramassedebris-service.php';

function assert_true($ok, $message) {
    if (!$ok) {
        fwrite(STDERR, "FAIL: " . $message . "\n");
        exit(1);
    }
}
assert_true(count(rdb_services()) >= 5, 'service catalogue');
assert_true(count(rdb_volumes()) >= 3, 'volume catalogue');
assert_true(isset(rdb_statuses()['nouveau']), 'initial state');
assert_true(isset(rdb_statuses()['termine']), 'completed state');
assert_true(count(array_unique(array_keys(rdb_statuses()))) === count(rdb_statuses()), 'unique status identifiers');
assert_true(rdb_truncate('bonjour', 3) === 'bon', 'text truncation');
assert_true(rdb_text_length('bonjour') === 7, 'text length');
assert_true(function_exists('rdb_export_personal_data'), 'WordPress privacy exporter');
assert_true(function_exists('rdb_erase_personal_data'), 'WordPress privacy eraser');
echo "PASS: standalone plugin contract smoke checks\n";
