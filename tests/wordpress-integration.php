<?php
/**
 * Integration checks against a disposable real WordPress + MySQL install.
 * Loaded by WP-CLI eval-file; never point this at staging/production databases.
 */
if (!defined('ABSPATH') || !defined('WP_CLI')) {
    fwrite(STDERR, "Requires isolated WP-CLI WordPress installation.\n");
    exit(2);
}
function rdb_ci_assert($condition, $message) {
    if (!$condition) {
        WP_CLI::error('Integration failure: ' . $message);
    }
}
rdb_ci_assert(in_array('ramassedebris-service/ramassedebris-service.php', (array) get_option('active_plugins'), true), 'local plugin activated');
rdb_ci_assert(defined('RDB_VERSION') && RDB_VERSION === '0.5.2', 'plugin version');

$post_type = get_post_type_object('rdb_demande');
rdb_ci_assert($post_type !== null, 'private request type registered');
rdb_ci_assert($post_type->public === false, 'request type not public');
rdb_ci_assert($post_type->show_in_rest === false, 'request type absent from REST');
rdb_ci_assert($post_type->show_ui === false, 'native editor disabled');
rdb_ci_assert($post_type->cap->read_private_posts === 'manage_options', 'private read requires manage_options');
rdb_ci_assert($post_type->cap->edit_posts === 'manage_options', 'private edits require manage_options');
rdb_ci_assert($post_type->cap->delete_posts === 'manage_options', 'private deletions require manage_options');
rdb_ci_assert($post_type->cap->create_posts === 'do_not_allow', 'native creation disabled');

$admin = get_user_by('login', 'rdb_ci_admin');
rdb_ci_assert($admin !== false, 'test administrator present');
wp_set_current_user($admin->ID);
rdb_ci_assert(current_user_can('manage_options'), 'administrator permissions');
rdb_ci_assert(current_user_can($post_type->cap->read_private_posts), 'admin may read private requests');

$uid = wp_create_user('rdb_ci_editor', 'NotAnActualPassword123!', 'editor@example.invalid');
rdb_ci_assert(!is_wp_error($uid), 'create test editor');
$editor = get_user_by('id', $uid);
$editor->set_role('editor');
wp_set_current_user($uid);
rdb_ci_assert(current_user_can('edit_posts'), 'editor retains normal post role');
rdb_ci_assert(current_user_can('read_private_posts'), 'editor has ordinary private post capability');
rdb_ci_assert(!current_user_can($post_type->cap->read_private_posts), 'editor cannot read private intake data');
rdb_ci_assert(!current_user_can($post_type->cap->edit_posts), 'editor cannot edit intake data');
rdb_ci_assert(!current_user_can($post_type->cap->delete_posts), 'editor cannot delete intake data');

$author_id = wp_create_user('rdb_ci_author', 'NotAnActualPassword123!', 'author@example.invalid');
rdb_ci_assert(!is_wp_error($author_id), 'create test author');
$author = get_user_by('id', $author_id);
$author->set_role('author');
wp_set_current_user($author_id);
rdb_ci_assert(!current_user_can($post_type->cap->read_private_posts), 'author cannot read private intake data');

wp_set_current_user($admin->ID);
$alice = wp_insert_post(array(
    'post_type' => 'rdb_demande',
    'post_status' => 'private',
    'post_title' => 'CI example 1',
    'meta_input' => array(
        '_rdb_name' => 'Alice Exemple',
        '_rdb_phone' => '514 555 0101',
        '_rdb_email' => 'alice@example.invalid',
        '_rdb_city' => 'LaSalle',
        '_rdb_status' => 'nouveau',
    ),
), true);
rdb_ci_assert(!is_wp_error($alice) && $alice > 0, 'first private test request persisted');
$bob = wp_insert_post(array(
    'post_type' => 'rdb_demande',
    'post_status' => 'private',
    'post_title' => 'CI example 2',
    'meta_input' => array(
        '_rdb_name' => 'Bob Exemple',
        '_rdb_phone' => '514 555 0102',
        '_rdb_email' => 'bob@example.invalid',
        '_rdb_city' => 'Verdun',
        '_rdb_status' => 'termine',
    ),
), true);
rdb_ci_assert(!is_wp_error($bob) && $bob > 0, 'second private test request persisted');

$query = new WP_Query(rdb_admin_query_args('nouveau', 'Alice', 1));
rdb_ci_assert(count($query->posts) === 1 && (int) $query->posts[0]->ID === $alice, 'admin search finds correct named client and status');
$query = new WP_Query(rdb_admin_query_args('termine', 'Alice', 1));
rdb_ci_assert(count($query->posts) === 0, 'status AND search excludes other statuses');
$query = new WP_Query(rdb_admin_query_args('termine', 'Verdun', 1));
rdb_ci_assert(count($query->posts) === 1 && (int) $query->posts[0]->ID === $bob, 'admin search by city and status');
$query = new WP_Query(rdb_admin_query_args('nouveau', 'alice@example.invalid', 1));
rdb_ci_assert(count($query->posts) === 1, 'admin search by email');

$export = rdb_export_personal_data('alice@example.invalid', 1);
rdb_ci_assert(count($export['data']) === 1, 'native privacy export of private request');
$erase = rdb_erase_personal_data('alice@example.invalid', 1);
rdb_ci_assert($erase['items_removed'] && !$erase['items_retained'] && $erase['done'], 'native privacy erase of private request');
rdb_ci_assert(get_post($alice) === null, 'erased request removed from WordPress');

wp_set_current_user(0);
rdb_ci_assert(shortcode_exists('ramassedebris_demande'), 'public shortcode is registered');
$html = do_shortcode('[ramassedebris_demande]');
rdb_ci_assert(strpos($html, 'data-rdb-nonce-endpoint=') !== false, 'form contains nonce refresh target');
rdb_ci_assert(strpos($html, 'rdb_consent') !== false, 'form requires consent');

wp_set_current_user($admin->ID);
wp_delete_post($bob, true);
require_once ABSPATH . 'wp-admin/includes/user.php';
wp_delete_user($uid);
wp_delete_user($author_id);
WP_CLI::success('WordPress integration: access control, private data, search, privacy, and shortcode');
