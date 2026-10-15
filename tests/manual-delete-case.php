<?php
// One-process contract test of the sensitive admin-only permanent deletion endpoint.
// The stubs imitate the WordPress contract; a WordPress site is still needed for E2E.
define('ABSPATH', __DIR__ . '/');
$scenario = isset($argv[1]) ? $argv[1] : 'success';
if (!in_array($scenario, array('success','unauthorized','get','badnonce','unconfirmed','wrongtype','failure'), true)) {
    fwrite(STDERR, "Unknown case\n");
    exit(2);
}
$_SERVER['REQUEST_METHOD'] = $scenario === 'get' ? 'GET' : 'POST';
$_POST = array(
    'rdb_id' => '11',
    'rdb_nonce' => $scenario === 'badnonce' ? 'invalid' : 'valid',
    'rdb_confirm' => $scenario === 'unconfirmed' ? '' : 'yes',
);

function add_filter() {}
function add_action() {}
function add_shortcode() {}
function register_activation_hook() {}
function register_deactivation_hook() {}
function current_user_can($capability) {
    return $GLOBALS['scenario'] !== 'unauthorized' && $capability === 'manage_options';
}
function wp_die($message, $title = '', $args = array()) {
    echo 'DENIED:' . (isset($args['response']) ? $args['response'] : 'unspecified');
    exit(0);
}
function absint($value) { return abs((int) $value); }
function sanitize_text_field($value) { return trim((string) $value); }
function wp_unslash($value) { return $value; }
function wp_verify_nonce($nonce, $action) {
    return $nonce === 'valid' && $action === 'rdb_delete_11';
}
function get_post_type($id) {
    if ($GLOBALS['scenario'] === 'wrongtype') {
        return 'post';
    }
    return (int) $id === 11 ? 'rdb_demande' : false;
}
function wp_delete_post($id, $force_delete = false) {
    if ($GLOBALS['scenario'] === 'failure') {
        return false;
    }
    echo 'DELETED:' . $id . ':';
    return (object) array('ID' => $id);
}
function admin_url($relative) { return 'https://site.example/wp-admin/' . $relative; }
function wp_safe_redirect($url, $code = 302) {
    echo 'REDIRECT:' . $code;
}

require dirname(__DIR__) . '/plugin/ramassedebris-service/ramassedebris-service.php';
rdb_handle_manual_delete();
fwrite(STDERR, "Handler unexpectedly returned\n");
exit(3);
