<?php
// Execute the public request handler in a fresh isolated PHP process.
define('ABSPATH', __DIR__ . '/');
define('HOUR_IN_SECONDS', 3600);
$scenario = $argv[1] ?? 'success';
$allowed = array('success','invalid_method','invalid_nonce','honeypot','limited','invalid_fields','missing_consent','array_input','array_optional','oversized_name','oversized_details','huge_payload','db_error','smtp_failure');
if (!in_array($scenario, $allowed, true)) exit(2);
$GLOBALS['scenario'] = $scenario;
$GLOBALS['saved'] = null;
$GLOBALS['mail'] = null;
$GLOBALS['meta'] = array();
$GLOBALS['throttle'] = false;
$_SERVER['REQUEST_METHOD'] = $scenario === 'invalid_method' ? 'GET' : 'POST';
$_SERVER['REMOTE_ADDR'] = '203.0.113.12';
$_POST = array(
    'rdb_nonce' => $scenario === 'invalid_nonce' ? 'bad' : 'valid',
    'rdb_website' => $scenario === 'honeypot' ? 'http://spam.invalid' : '',
    'rdb_name' => 'Client Test',
    'rdb_phone' => '514-555-0199',
    'rdb_email' => 'client@example.test',
    'rdb_city' => 'Montréal',
    'rdb_postal' => 'H8N 1X1',
    'rdb_service' => 'meubles',
    'rdb_volume' => 'petit',
    'rdb_date' => '2026-11-01',
    'rdb_details' => 'Un divan et une petite commode.',
    'rdb_consent' => $scenario === 'missing_consent' ? '' : '1',
);
if ($scenario === 'invalid_fields') $_POST['rdb_phone'] = 'abc';
if ($scenario === 'array_input') $_POST['rdb_details'] = array('unexpected');
if ($scenario === 'array_optional') $_POST['rdb_email'] = array('unexpected');
if ($scenario === 'oversized_name') $_POST['rdb_name'] = str_repeat('a', 101);
if ($scenario === 'oversized_details') $_POST['rdb_details'] = str_repeat('x', 1501);
if ($scenario === 'huge_payload') $_POST['rdb_details'] = str_repeat('x', 100000);


function add_filter() {}
function add_action() {}
function add_shortcode() {}
function register_activation_hook() {}
function register_deactivation_hook() {}
function wp_die($message, $title = '', $args = array()) {
    echo 'DENIED:' . (int) ($args['response'] ?? 0);
    exit;
}
function sanitize_text_field($value) { return is_string($value) ? trim($value) : ''; }
function sanitize_textarea_field($value) { return is_string($value) ? trim($value) : ''; }
function wp_unslash($value) { return $value; }
function sanitize_email($email) { return strtolower(trim($email)); }
function is_email($email) { return filter_var($email, FILTER_VALIDATE_EMAIL) !== false; }
function wp_verify_nonce($nonce, $action) { return $nonce === 'valid' && $action === 'rdb_new_request'; }
function wp_salt($scheme) { return 'test-only-salt'; }
function get_transient($key) { return $GLOBALS['scenario'] === 'limited' ? 5 : 0; }
function set_transient($key, $value, $expiration) { $GLOBALS['throttle'] = $value === 1; }
function current_time($format) { return $format === 'Y-m-d' ? '2026-10-15' : '2026-10-15 13:00:00'; }
function is_wp_error($value) { return false; }
function wp_insert_post($data, $wp_error = false) {
    if ($GLOBALS['scenario'] === 'db_error') return false;
    $GLOBALS['saved'] = $data;
    return 42;
}
function get_option($key) { return $key === 'admin_email' ? 'admin@example.test' : ''; }
function admin_url($path) { return 'https://test.example/wp-admin/' . $path; }
function wp_mail($to, $subject, $body, $headers) {
    $GLOBALS['mail'] = array($to, $subject, $body);
    return $GLOBALS['scenario'] !== 'smtp_failure';
}
function update_post_meta($id, $key, $value) { $GLOBALS['meta'][$key] = $value; }
function wp_get_referer() { return false; }
function home_url($path) { return 'https://test.example/'; }
function remove_query_arg($key, $url) { return $url; }
function add_query_arg($key, $value, $url) { return $url . '?rdb_result=' . $value; }
function wp_safe_redirect($url, $status) {
    $saved = $GLOBALS['saved'];
    $mail = $GLOBALS['mail'];
    $private = $saved && $saved['post_type'] === 'rdb_demande' && $saved['post_status'] === 'private';
    $mail_safe = $mail && strpos($mail[2], 'Client Test') === false &&
        strpos($mail[2], '514-555') === false && strpos($mail[2], 'Un divan') === false;
    preg_match('/rdb_result=([a-z_]+)/', $url, $match);
    echo 'RESULT:' . ($match[1] ?? '-') .
        '|SAVED:' . ($saved ? '1' : '0') .
        '|PRIVATE:' . ($private ? '1' : '0') .
        '|MAIL:' . ($mail ? '1' : '0') .
        '|MAIL_SAFE:' . ($mail_safe ? '1' : '0') .
        '|THROTTLED:' . ($GLOBALS['throttle'] ? '1' : '0') .
        '|DELIVERY:' . ($GLOBALS['meta']['_rdb_notification_sent'] ?? '-');
}
require dirname(__DIR__) . '/plugin/ramassedebris-service/ramassedebris-service.php';
rdb_handle_request();
fwrite(STDERR, "Handler unexpectedly returned\n");
exit(3);
