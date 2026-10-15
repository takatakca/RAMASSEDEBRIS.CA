<?php
/**
 * Dependency-light PHP contract tests for WordPress-specific code.
 * Runs without network, WordPress installation or persistent test records.
 */
define('ABSPATH', __DIR__ . '/');
function add_action() {}
function add_filter() {}
function add_shortcode() {}
function register_activation_hook() {}
function register_deactivation_hook() {}
function is_email($value) { return filter_var($value, FILTER_VALIDATE_EMAIL) !== false; }
function sanitize_email($value) { return strtolower(trim((string) $value)); }

$GLOBALS['rdb_test_records'] = array();
$GLOBALS['rdb_test_fail_delete'] = array();

class WP_Query {
    public $posts = array();
    public function __construct($args) {
        $records = array_values($GLOBALS['rdb_test_records']);
        $email = $args['meta_query'][0]['value'];
        $excluded_ids = isset($args['post__not_in']) ? $args['post__not_in'] : array();
        $matched = array_values(array_filter($records, function ($row) use ($email, $excluded_ids) {
            return $row['email'] === $email && !in_array($row['id'], $excluded_ids, true);
        }));
        $offset = ((int) $args['paged'] - 1) * (int) $args['posts_per_page'];
        $matched = array_slice($matched, $offset, (int) $args['posts_per_page']);
        $this->posts = array_map(function ($row) {
            return (object) array('ID' => $row['id']);
        }, $matched);
    }
}
function get_post_meta($id, $key, $single = true) {
    $row = isset($GLOBALS['rdb_test_records'][$id]) ? $GLOBALS['rdb_test_records'][$id] : array();
    return $key === '_rdb_email' ? ($row['email'] ?? '') : ($row['meta'][$key] ?? '');
}
function wp_delete_post($id, $force_delete = false) {
    if (in_array($id, $GLOBALS['rdb_test_fail_delete'], true)) {
        return false;
    }
    if (!isset($GLOBALS['rdb_test_records'][$id])) {
        return false;
    }
    unset($GLOBALS['rdb_test_records'][$id]);
    return (object) array('ID' => $id);
}

require dirname(__DIR__) . '/plugin/ramassedebris-service/ramassedebris-service.php';

function check($actual, $expected, $description) {
    if ($actual !== $expected) {
        fwrite(STDERR, 'FAIL: ' . $description . ' expected ' . var_export($expected, true) . ' got ' . var_export($actual, true) . "\n");
        exit(1);
    }
}

check(RDB_VERSION, '0.3.1', 'plugin version contract');
check(count(rdb_services()), 5, 'service catalogue');
check(count(rdb_volumes()), 4, 'volume catalogue');
check(count(rdb_statuses()), 4, 'status catalogue');
check(rdb_truncate('bonjour', 3), 'bon', 'text truncation');
check(rdb_text_length('bonjour'), 7, 'text length');
check(function_exists('rdb_export_personal_data'), true, 'WordPress privacy exporter');
check(function_exists('rdb_erase_personal_data'), true, 'WordPress privacy eraser');
check(function_exists('rdb_handle_manual_delete'), true, 'admin-only manual privacy deletion handler');

$today = '2026-10-15';
$valid = array(
    'name' => 'Client Test',
    'phone' => '+1 (514) 555-0199',
    'email' => 'test@example.org',
    'city' => 'Montréal',
    'postal' => 'H8N 1X1',
    'service' => 'meubles',
    'volume' => 'moyen',
    'date' => '2026-11-10',
    'details' => 'Un canapé, une commode et deux chaises.',
);
check(rdb_validate_request_data($valid, true, $today), true, 'valid customer intake');
check(rdb_validate_request_data($valid, false, $today), false, 'requires consent');

$invalid_cases = array(
    'missing name' => array('name', ''),
    'invalid phone' => array('phone', 'abc'),
    'invalid email' => array('email', 'invalid@'),
    'city required' => array('city', ''),
    'unexpected service' => array('service', 'inconnu'),
    'unexpected volume' => array('volume', 'très-grand'),
    'malformed postal' => array('postal', 'foobar'),
    'past date' => array('date', '2026-01-01'),
    'impossible date' => array('date', '2026-02-30'),
    'future date too far' => array('date', '2028-01-01'),
    'description too short' => array('details', 'abc'),
    'oversized description' => array('details', str_repeat('x', 1501)),
    'oversized name' => array('name', str_repeat('x', 101)),
);
foreach ($invalid_cases as $reason => $case) {
    $input = $valid;
    $input[$case[0]] = $case[1];
    check(rdb_validate_request_data($input, true, $today), false, $reason);
}
$missing = $valid;
unset($missing['phone']);
check(rdb_validate_request_data($missing, true, $today), false, 'missing field');
$no_optional = $valid;
$no_optional['email'] = '';
$no_optional['postal'] = '';
$no_optional['date'] = '';
check(rdb_validate_request_data($no_optional, true, $today), true, 'optional fields may be empty');
$last_day = $valid;
$last_day['date'] = (new DateTimeImmutable($today))->modify('+180 days')->format('Y-m-d');
check(rdb_validate_request_data($last_day, true, $today), true, 'last allowed day');

for ($i = 1; $i <= 27; $i++) {
    $GLOBALS['rdb_test_records'][$i] = array(
        'id' => $i,
        'email' => $i === 27 ? 'other@example.org' : 'test@example.org',
        'meta' => array('_rdb_name' => 'Demande #' . $i, '_rdb_email' => 'test@example.org'),
    );
}
$export1 = rdb_export_personal_data('test@example.org', 1);
check(count($export1['data']), 25, 'export batch size');
check($export1['done'], false, 'export has second page');
check($export1['data'][0]['data'][0]['value'], 'Demande #1', 'export field value');
$export2 = rdb_export_personal_data('test@example.org', 2);
check(count($export2['data']), 1, 'export second batch');
check($export2['done'], true, 'export completed');

$erase1 = rdb_erase_personal_data('test@example.org', 1);
check($erase1['items_removed'], true, 'erasure deletes valid batches');
check($erase1['done'], true, 'erasure completed in bounded batches');
check(count($GLOBALS['rdb_test_records']), 1, 'unrelated customer records preserved');
check(isset($GLOBALS['rdb_test_records'][27]), true, 'unrelated customer preserved by id');

// A failed deletion must not prevent the next valid row from being erased.
$GLOBALS['rdb_test_records'][28] = array(
    'id' => 28,
    'email' => 'blocked@example.org',
    'meta' => array('_rdb_email' => 'blocked@example.org'),
);
$GLOBALS['rdb_test_records'][29] = array(
    'id' => 29,
    'email' => 'blocked@example.org',
    'meta' => array('_rdb_email' => 'blocked@example.org'),
);
$GLOBALS['rdb_test_fail_delete'][] = 28;
$failure = rdb_erase_personal_data('blocked@example.org');
check($failure['items_retained'], true, 'erasure reports retained record');
check($failure['items_removed'], true, 'erasure removes following valid record');
check($failure['done'], true, 'erasure avoids infinite loop on failed record');
check(count($failure['messages']) > 0, true, 'erasure reports error');
check(isset($GLOBALS['rdb_test_records'][28]), true, 'failed row retained');
check(isset($GLOBALS['rdb_test_records'][29]), false, 'following valid row deleted');

for ($i = 100; $i < 250; $i++) {
    $GLOBALS['rdb_test_records'][$i] = array(
        'id' => $i,
        'email' => 'large@example.org',
        'meta' => array('_rdb_email' => 'large@example.org'),
    );
}
$large1 = rdb_erase_personal_data('large@example.org', 1);
check($large1['done'], false, 'bounded erasure requests another run for large volume');
$large2 = rdb_erase_personal_data('large@example.org', 2);
check($large2['done'], true, 'second erasure callback finishes remaining rows');
check(count(array_filter($GLOBALS['rdb_test_records'], function ($row) {
    return $row['email'] === 'large@example.org';
})), 0, 'large erasure fully cleared');

$invalid_export = rdb_export_personal_data('invalid', 1);
check($invalid_export['done'], true, 'invalid identity export completed safely');
echo "PASS: WordPress plugin field validation, privacy batches, erasure failures and safety contracts\n";
