<?php
/**
 * Dependency-light PHP contract tests for WordPress-specific code.
 * Runs without network, WordPress installation or persistent test records.
 */
define('ABSPATH', __DIR__ . '/');
function add_action($hook, $callback) { $GLOBALS['rdb_test_actions'][$hook][] = $callback; }
function register_post_type($name, $settings) { $GLOBALS['rdb_registered_types'][$name] = $settings; }
function add_filter() {}
function add_shortcode() {}
function register_activation_hook() {}
function register_deactivation_hook() {}
function is_email($value) { return filter_var($value, FILTER_VALIDATE_EMAIL) !== false; }
function sanitize_email($value) { return strtolower(trim((string) $value)); }
function wp_create_nonce($action) { return 'nonce-for:' . $action; }
function wp_send_json_success($payload) {
    $GLOBALS['rdb_ajax_response'] = array('status' => 200, 'success' => true, 'data' => $payload);
}
function wp_send_json_error($payload, $status) {
    $GLOBALS['rdb_ajax_response'] = array('status' => $status, 'success' => false, 'data' => $payload);
}


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

check(RDB_VERSION, '0.5.0', 'plugin version contract');

foreach ($GLOBALS['rdb_test_actions']['init'] as $callback) { $callback(); }
$registered = $GLOBALS['rdb_registered_types']['rdb_demande'];
check($registered['public'], false, 'request post type is not public');
check($registered['show_in_rest'], false, 'request post type not exposed to REST');
check($registered['show_ui'], false, 'default WordPress editor not exposed');
check($registered['map_meta_cap'], false, 'no default post meta capability mapping');
foreach (array('read_post', 'read_private_posts', 'edit_posts', 'edit_post', 'delete_post', 'delete_posts') as $cap) {
    check($registered['capabilities'][$cap], 'manage_options', 'request cap: ' . $cap);
}
check($registered['capabilities']['create_posts'], 'do_not_allow', 'cannot create CPT from native UI');

check(count(rdb_services()), 5, 'service catalogue');
check(count(rdb_volumes()), 4, 'volume catalogue');
check(count(rdb_statuses()), 4, 'status catalogue');
check(rdb_truncate('bonjour', 3), 'bon', 'text truncation');
check(rdb_text_length('bonjour'), 7, 'text length');
check(function_exists('rdb_export_personal_data'), true, 'WordPress privacy exporter');
check(function_exists('rdb_erase_personal_data'), true, 'WordPress privacy eraser');
check(function_exists('rdb_handle_manual_delete'), true, 'admin-only manual privacy deletion handler');
$plain_query = rdb_admin_query_args('', '', 1);
check($plain_query['post_type'], 'rdb_demande', 'admin query restricted to requests');
check($plain_query['post_status'], 'private', 'admin query restricted to private records');
check(isset($plain_query['meta_query']), false, 'no metadata filter when not requested');
$status_query = rdb_admin_query_args('contacte', '', 2);
check($status_query['paged'], 2, 'admin filter page');
check($status_query['meta_query'][1]['key'], '_rdb_status', 'status filter metadata');
check($status_query['meta_query'][1]['value'], 'contacte', 'status filter value');
$search_query = rdb_admin_query_args('planifie', '514-555', 3);
check($search_query['meta_query']['relation'], 'AND', 'status and contact search combined');
check($search_query['meta_query'][2]['relation'], 'OR', 'search fields combined as OR');
check(count($search_query['meta_query'][2]), 6, 'five searchable contact fields');
check($search_query['meta_query'][2][1]['key'], '_rdb_name', 'name searchable');
check($search_query['meta_query'][2][2]['key'], '_rdb_phone', 'phone searchable');
check($search_query['meta_query'][2][3]['key'], '_rdb_email', 'email searchable');
check($search_query['meta_query'][2][4]['key'], '_rdb_city', 'city searchable');
check($search_query['meta_query'][2][5]['key'], '_rdb_postal', 'postal searchable');
check(rdb_admin_query_args('invalid', '', 1)['meta_query'] ?? null, null, 'unknown statuses ignored');

$_SERVER['REQUEST_METHOD'] = 'POST';
rdb_refresh_nonce();
check($GLOBALS['rdb_ajax_response']['success'], true, 'nonce refresh succeeds with POST');
check($GLOBALS['rdb_ajax_response']['data']['nonce'], 'nonce-for:rdb_new_request', 'nonce uses correct WP action');
$_SERVER['REQUEST_METHOD'] = 'GET';
rdb_refresh_nonce();
check($GLOBALS['rdb_ajax_response']['success'], false, 'nonce refresh rejects GET');
check($GLOBALS['rdb_ajax_response']['status'], 405, 'nonce refresh HTTP method response');
unset($_SERVER['REQUEST_METHOD']);


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
