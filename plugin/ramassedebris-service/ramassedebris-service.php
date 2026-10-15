<?php
/**
 * Plugin Name: Ramasse Débris — Demandes de ramassage
 * Description: Formulaire de demande de ramassage et suivi privé dans WordPress. Aucun changement à WooCommerce.
 * Version: 0.2.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: RAMASSE DÉBRIS MONTRÉAL
 * Text Domain: ramassedebris
 */

if (!defined('ABSPATH')) {
    exit;
}

define('RDB_VERSION', '0.2.0');
define('RDB_PLUGIN_FILE', __FILE__);

function rdb_services() {
    return array(
        'meubles' => 'Meubles et encombrants',
        'electromenagers' => 'Électroménagers',
        'renovation' => 'Débris de rénovation',
        'commercial' => 'Débarras commercial',
        'autre' => 'Autre type de ramassage',
    );
}

function rdb_volumes() {
    return array(
        'petit' => 'Quelques articles',
        'moyen' => 'Une petite pièce',
        'grand' => 'Plusieurs pièces',
        'incertain' => 'À évaluer',
    );
}

function rdb_statuses() {
    return array(
        'nouveau' => 'Nouvelle demande',
        'contacte' => 'Client contacté',
        'planifie' => 'Intervention planifiée',
        'termine' => 'Dossier terminé',
    );
}


/**
 * Connect the plugin's stored requests to the native WordPress privacy tools.
 * The standard WordPress workflow verifies the requester's email address.
 * Phone-only requests need an administrator-assisted privacy request.
 */
add_filter('wp_privacy_personal_data_exporters', function ($exporters) {
    $exporters['ramassedebris'] = array(
        'exporter_friendly_name' => 'Demandes Ramasse Débris',
        'callback' => 'rdb_export_personal_data',
    );
    return $exporters;
});
add_filter('wp_privacy_personal_data_erasers', function ($erasers) {
    $erasers['ramassedebris'] = array(
        'eraser_friendly_name' => 'Demandes Ramasse Débris',
        'callback' => 'rdb_erase_personal_data',
    );
    return $erasers;
});

function rdb_privacy_query($email, $page = 1) {
    return new WP_Query(array(
        'post_type' => 'rdb_demande',
        'post_status' => 'private',
        'posts_per_page' => 25,
        'paged' => max(1, (int) $page),
        'meta_query' => array(array(
            'key' => '_rdb_email',
            'value' => sanitize_email($email),
            'compare' => '=',
        )),
        'no_found_rows' => true,
    ));
}

function rdb_export_personal_data($email, $page = 1) {
    if (!is_email($email)) {
        return array('data' => array(), 'done' => true);
    }
    $query = rdb_privacy_query($email, $page);
    $data = array();
    $fields = array(
        'name' => 'Nom',
        'phone' => 'Téléphone',
        'email' => 'Courriel',
        'city' => 'Ville',
        'postal' => 'Code postal',
        'service' => 'Type de ramassage',
        'volume' => 'Volume',
        'date' => 'Date souhaitée',
        'details' => 'Détails',
        'consent_at' => 'Consentement enregistré le',
    );
    foreach ($query->posts as $request) {
        $items = array();
        foreach ($fields as $key => $label) {
            $items[] = array(
                'name' => $label,
                'value' => (string) get_post_meta($request->ID, '_rdb_' . $key, true),
            );
        }
        $data[] = array(
            'group_id' => 'ramassedebris',
            'group_label' => 'Demandes de ramassage',
            'item_id' => 'rdb-' . $request->ID,
            'data' => $items,
        );
    }
    return array('data' => $data, 'done' => count($query->posts) < 25);
}

function rdb_erase_personal_data($email, $page = 1) {
    if (!is_email($email)) {
        return array('items_removed' => false, 'items_retained' => true, 'messages' => array(), 'done' => true);
    }
    // Always process the first page: deleting records shrinks the result set.
    $query = rdb_privacy_query($email, 1);
    $removed = false;
    $retained = false;
    foreach ($query->posts as $request) {
        if (wp_delete_post($request->ID, true)) {
            $removed = true;
        } else {
            $retained = true;
        }
    }
    return array(
        'items_removed' => $removed,
        'items_retained' => $retained,
        'messages' => $retained ? array('Certaines demandes n’ont pas pu être supprimées. Vérifiez manuellement.') : array(),
        // Do not repeatedly retry a failed record in an endless page-one loop.
        'done' => $retained || count($query->posts) < 25,
    );
}

function rdb_truncate($value, $length) {
    return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
}

function rdb_text_length($value) {
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}


/**
 * Domain validation is shared by the live handler and executable contract tests.
 * This does not assign prices, scheduling confirmations or customer identities.
 */
function rdb_validate_request_data($values, $consent, $today) {
    $fields = array('name', 'phone', 'email', 'city', 'postal', 'service', 'volume', 'date', 'details');
    if (!$consent || !is_array($values)) {
        return false;
    }
    foreach ($fields as $field) {
        if (!isset($values[$field]) || !is_string($values[$field])) {
            return false;
        }
    }
    if (
        rdb_text_length($values['name']) < 2 ||
        rdb_text_length($values['name']) > 100 ||
        rdb_text_length($values['city']) < 2 ||
        rdb_text_length($values['city']) > 100 ||
        rdb_text_length($values['details']) < 10 ||
        rdb_text_length($values['details']) > 1500 ||
        rdb_text_length($values['email']) > 150 ||
        !preg_match('/^[0-9+().\s-]{7,30}$/', $values['phone']) ||
        !array_key_exists($values['service'], rdb_services()) ||
        !array_key_exists($values['volume'], rdb_volumes()) ||
        ($values['email'] !== '' && !is_email($values['email'])) ||
        ($values['postal'] !== '' && !preg_match('/^[A-Z]\d[A-Z][ ]?\d[A-Z]\d$/', $values['postal']))
    ) {
        return false;
    }
    if ($values['date'] !== '') {
        // WordPress date input is YYYY-MM-DD without a timezone suffix.
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $values['date']);
        $today_date = DateTimeImmutable::createFromFormat('!Y-m-d', $today);
        if (
            !$parsed ||
            !$today_date ||
            $parsed->format('Y-m-d') !== $values['date'] ||
            $parsed < $today_date ||
            $parsed > $today_date->modify('+180 days')
        ) {
            return false;
        }
    }
    return true;
}

add_action('init', function () {
    register_post_type('rdb_demande', array(
        'label' => 'Demandes de ramassage',
        'public' => false,
        'show_ui' => false,
        'show_in_rest' => false,
        'exclude_from_search' => true,
        'supports' => array('title'),
    ));
});

register_activation_hook(__FILE__, function () {
    if (!wp_next_scheduled('rdb_daily_retention')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'rdb_daily_retention');
    }
});

register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('rdb_daily_retention');
});

/**
 * Remove only completed records after 180 days. Open requests remain untouched.
 * WordPress cron depends on site traffic or the host's cron integration.
 */
add_action('rdb_daily_retention', function () {
    $query = new WP_Query(array(
        'post_type' => 'rdb_demande',
        'post_status' => 'private',
        'posts_per_page' => 50,
        'fields' => 'ids',
        'date_query' => array(array(
            'column' => 'post_modified',
            'before' => '180 days ago',
        )),
        'meta_query' => array(array(
            'key' => '_rdb_status',
            'value' => 'termine',
        )),
        'no_found_rows' => true,
    ));
    foreach ($query->posts as $id) {
        wp_delete_post((int) $id, true);
    }
});

add_action('wp_enqueue_scripts', function () {
    wp_register_style('rdb-service-form', plugins_url('assets/form.css', RDB_PLUGIN_FILE), array(), RDB_VERSION);
    wp_register_script('rdb-service-form', plugins_url('assets/form.js', RDB_PLUGIN_FILE), array(), RDB_VERSION, true);
});

add_shortcode('ramassedebris_demande', function () {
    wp_enqueue_style('rdb-service-form');
    wp_enqueue_script('rdb-service-form');

    $result = isset($_GET['rdb_result']) ? sanitize_key(wp_unslash($_GET['rdb_result'])) : '';
    $messages = array(
        'sent' => 'Merci ! Votre demande a été reçue. Notre équipe vous contactera pour confirmer les détails.',
        'invalid' => 'Certains renseignements sont invalides ou manquants. Veuillez réessayer.',
        'expired' => 'Le formulaire a expiré. Actualisez la page et réessayez.',
        'limited' => 'Trop de demandes ont été envoyées. Veuillez réessayer plus tard.',
        'error' => 'Votre demande n’a pas pu être enregistrée. Vous pouvez nous téléphoner.',
    );

    ob_start();
    ?>
    <section class="rdb" id="demande" aria-labelledby="rdb-heading">
      <div class="rdb-intro">
        <span class="rdb-eyebrow">RAMASSE DÉBRIS MONTRÉAL</span>
        <h2 id="rdb-heading">Demandez votre ramassage</h2>
        <p>Décrivez ce qu’il faut enlever. Une personne de notre équipe communiquera avec vous pour évaluer la demande et convenir d’une disponibilité. Aucun prix ni rendez-vous n’est confirmé par ce formulaire.</p>
        <div class="rdb-phone">Vous préférez appeler ? <a href="tel:+15148252825">514 825-2825</a></div>
      </div>
      <?php if (isset($messages[$result])) : ?>
        <div class="rdb-notice <?php echo $result === 'sent' ? 'rdb-notice--success' : 'rdb-notice--error'; ?>" role="status">
          <?php echo esc_html($messages[$result]); ?>
        </div>
      <?php endif; ?>
      <form class="rdb-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="rdb_submit_request">
        <?php wp_nonce_field('rdb_new_request', 'rdb_nonce'); ?>
        <div class="rdb-trap" aria-hidden="true"><label>Ne pas remplir <input type="text" name="rdb_website" tabindex="-1" autocomplete="off"></label></div>
        <div class="rdb-grid">
          <div class="rdb-field">
            <label for="rdb-name">Nom complet <span aria-hidden="true">*</span></label>
            <input id="rdb-name" type="text" name="rdb_name" maxlength="100" autocomplete="name" required>
          </div>
          <div class="rdb-field">
            <label for="rdb-phone">Téléphone <span aria-hidden="true">*</span></label>
            <input id="rdb-phone" type="tel" name="rdb_phone" maxlength="30" autocomplete="tel" required>
          </div>
          <div class="rdb-field">
            <label for="rdb-email">Courriel (facultatif)</label>
            <input id="rdb-email" type="email" name="rdb_email" maxlength="150" autocomplete="email">
          </div>
          <div class="rdb-field">
            <label for="rdb-city">Ville ou arrondissement <span aria-hidden="true">*</span></label>
            <input id="rdb-city" type="text" name="rdb_city" maxlength="100" autocomplete="address-level2" required placeholder="Ex. : LaSalle">
          </div>
          <div class="rdb-field">
            <label for="rdb-postal">Code postal (facultatif)</label>
            <input id="rdb-postal" type="text" name="rdb_postal" maxlength="7" autocomplete="postal-code" placeholder="H8N 1X1">
          </div>
          <div class="rdb-field">
            <label for="rdb-service">Type de ramassage <span aria-hidden="true">*</span></label>
            <select id="rdb-service" name="rdb_service" required>
              <option value="">Choisir un service</option>
              <?php foreach (rdb_services() as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="rdb-field">
            <label for="rdb-volume">Volume approximatif <span aria-hidden="true">*</span></label>
            <select id="rdb-volume" name="rdb_volume" required>
              <option value="">Sélectionnez une estimation</option>
              <?php foreach (rdb_volumes() as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="rdb-field">
            <label for="rdb-date">Date souhaitée (facultatif)</label>
            <input id="rdb-date" type="date" name="rdb_date" min="<?php echo esc_attr(current_time('Y-m-d')); ?>">
            <small>Cette date n’est pas une réservation confirmée.</small>
          </div>
        </div>
        <div class="rdb-field">
          <label for="rdb-details">Décrivez les articles à ramasser <span aria-hidden="true">*</span></label>
          <textarea id="rdb-details" name="rdb_details" rows="5" maxlength="1500" required placeholder="Ex. : divan trois places, deux commodes, accès par escalier..."></textarea>
          <small><output id="rdb-counter">0</output> / 1500 caractères</small>
        </div>
        <label class="rdb-consent"><input type="checkbox" name="rdb_consent" value="1" required> <span>J’accepte que mes coordonnées soient utilisées pour répondre à cette demande de ramassage. <span aria-hidden="true">*</span></span></label>
        <div class="rdb-actions"><button type="submit">Envoyer ma demande <span aria-hidden="true">→</span></button><span>Sans engagement</span></div>
        <p class="rdb-privacy">Les renseignements sont enregistrés sur ce site WordPress, accessibles uniquement aux administrateurs autorisés. Ils ne sont pas transmis à un réseau publicitaire. Les demandes terminées sont supprimées après 180 jours.</p>
      </form>
    </section>
    <?php
    return ob_get_clean();
});

function rdb_form_redirect($code) {
    $source = wp_get_referer();
    $target = $source ? wp_validate_redirect($source, home_url('/')) : home_url('/');
    $target = remove_query_arg('rdb_result', $target);
    $target = add_query_arg('rdb_result', $code, $target);
    wp_safe_redirect($target . '#demande', 303);
    exit;
}

add_action('admin_post_nopriv_rdb_submit_request', 'rdb_handle_request');
add_action('admin_post_rdb_submit_request', 'rdb_handle_request');

function rdb_handle_request() {
    if (strtoupper(isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : '') !== 'POST') {
        wp_die('Méthode non autorisée.', '', array('response' => 405));
    }

    $nonce = isset($_POST['rdb_nonce']) ? sanitize_text_field(wp_unslash($_POST['rdb_nonce'])) : '';
    if (!wp_verify_nonce($nonce, 'rdb_new_request')) {
        rdb_form_redirect('expired');
    }
    if (!empty($_POST['rdb_website'])) {
        rdb_form_redirect('sent');
    }

    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    $ip = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
    $key = 'rdb_rate_' . substr(hash_hmac('sha256', $ip, wp_salt('auth')), 0, 30);
    if ((int) get_transient($key) >= 5) {
        rdb_form_redirect('limited');
    }

    $values = array();
    foreach (array('name', 'phone', 'email', 'city', 'postal', 'service', 'volume', 'date', 'details') as $field) {
        $raw = isset($_POST['rdb_' . $field]) && is_string($_POST['rdb_' . $field]) ? wp_unslash($_POST['rdb_' . $field]) : '';
        $values[$field] = $field === 'details' ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
    }
    $values['name'] = rdb_truncate($values['name'], 100);
    $values['phone'] = rdb_truncate($values['phone'], 30);
    $values['city'] = rdb_truncate($values['city'], 100);
    $values['details'] = rdb_truncate($values['details'], 1500);
    $values['email'] = sanitize_email($values['email']);
    $values['postal'] = strtoupper(trim($values['postal']));
    $consent = isset($_POST['rdb_consent']) && wp_unslash($_POST['rdb_consent']) === '1';

    $valid = rdb_validate_request_data($values, $consent, current_time('Y-m-d'));
    if (!$valid) {
        rdb_form_redirect('invalid');
    }

    $id = wp_insert_post(array(
        'post_type' => 'rdb_demande',
        'post_status' => 'private',
        'post_title' => 'Demande de ramassage — ' . current_time('Y-m-d H:i:s'),
        'meta_input' => array(
            '_rdb_name' => $values['name'],
            '_rdb_phone' => $values['phone'],
            '_rdb_email' => $values['email'],
            '_rdb_city' => $values['city'],
            '_rdb_postal' => $values['postal'],
            '_rdb_service' => $values['service'],
            '_rdb_volume' => $values['volume'],
            '_rdb_date' => $values['date'],
            '_rdb_details' => $values['details'],
            '_rdb_status' => 'nouveau',
            '_rdb_source' => 'wordpress_shortcode',
            '_rdb_consent_at' => current_time('mysql'),
        ),
    ), true);

    if (is_wp_error($id) || !$id) {
        rdb_form_redirect('error');
    }
    set_transient($key, (int) get_transient($key) + 1, HOUR_IN_SECONDS);

    // Do not reflect user-controlled text into an email header.
    $admin = sanitize_email(get_option('admin_email'));
    if (is_email($admin)) {
        // Notification deliberately excludes contact details and descriptions:
        // email is not the system of record for personal information.
        $body = "Nouvelle demande de ramassage #" . $id . "\n\n";
        $body .= "Connectez-vous à votre administration WordPress pour consulter et traiter la demande :\n";
        $body .= admin_url('admin.php?page=rdb-demandes') . "\n";
        $sent = wp_mail($admin, 'Nouvelle demande de ramassage #' . $id, $body, array('Content-Type: text/plain; charset=UTF-8'));
        update_post_meta($id, '_rdb_notification_sent', $sent ? '1' : '0');
    }
    rdb_form_redirect('sent');
}

add_action('admin_menu', function () {
    add_menu_page(
        'Demandes de ramassage',
        'Ramasse Débris',
        'manage_options',
        'rdb-demandes',
        'rdb_render_admin',
        'dashicons-trash',
        26
    );
});

function rdb_render_admin() {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé.', '', array('response' => 403));
    }
    $status = isset($_GET['statut']) ? sanitize_key(wp_unslash($_GET['statut'])) : '';
    if ($status && !isset(rdb_statuses()[$status])) {
        $status = '';
    }
    $page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
    $args = array(
        'post_type' => 'rdb_demande',
        'post_status' => 'private',
        'posts_per_page' => 20,
        'paged' => $page,
        'orderby' => 'date',
        'order' => 'DESC',
    );
    if ($status) {
        $args['meta_query'] = array(array('key' => '_rdb_status', 'value' => $status));
    }
    $query = new WP_Query($args);
    ?>
    <div class="wrap">
      <h1>Demandes de ramassage</h1>
      <p>Les dates demandées ne confirment pas de réservation. Les changements ici ne modifient ni les commandes WooCommerce ni les données TAKATAK.</p>
      <p><a href="<?php echo esc_url(admin_url('admin.php?page=rdb-demandes')); ?>">Toutes</a>
        <?php foreach (rdb_statuses() as $code => $label) : ?>
          | <a href="<?php echo esc_url(add_query_arg(array('page' => 'rdb-demandes', 'statut' => $code), admin_url('admin.php'))); ?>"><?php echo esc_html($label); ?></a>
        <?php endforeach; ?>
      </p>
      <table class="widefat striped">
        <thead><tr><th>Reçue</th><th>Client / coordonnées</th><th>Service / détails</th><th>Statut et actions</th></tr></thead>
        <tbody>
        <?php if (!$query->have_posts()) : ?>
          <tr><td colspan="4">Aucune demande.</td></tr>
        <?php endif; ?>
        <?php foreach ($query->posts as $request) : ?>
          <?php
          $id = $request->ID;
          $current = get_post_meta($id, '_rdb_status', true);
          $service = get_post_meta($id, '_rdb_service', true);
          $volume = get_post_meta($id, '_rdb_volume', true);
          ?>
          <tr>
            <td><strong>#<?php echo esc_html($id); ?></strong><br><?php echo esc_html(get_the_date('Y-m-d H:i', $id)); ?></td>
            <td>
              <strong><?php echo esc_html(get_post_meta($id, '_rdb_name', true)); ?></strong><br>
              <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', get_post_meta($id, '_rdb_phone', true))); ?>"><?php echo esc_html(get_post_meta($id, '_rdb_phone', true)); ?></a><br>
              <?php $email = get_post_meta($id, '_rdb_email', true); if ($email) : ?>
                <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a><br>
              <?php endif; ?>
              <?php echo esc_html(get_post_meta($id, '_rdb_city', true) . ' ' . get_post_meta($id, '_rdb_postal', true)); ?>
            </td>
            <td>
              <strong><?php echo esc_html(isset(rdb_services()[$service]) ? rdb_services()[$service] : 'Autre'); ?></strong><br>
              <?php echo esc_html(isset(rdb_volumes()[$volume]) ? rdb_volumes()[$volume] : ''); ?><br>
              Date demandée : <?php echo esc_html(get_post_meta($id, '_rdb_date', true) ?: 'Non précisée'); ?><br>
              <details><summary>Description</summary><p style="white-space:pre-wrap"><?php echo esc_html(get_post_meta($id, '_rdb_details', true)); ?></p></details>
            </td>
            <td>
              <strong><?php echo esc_html(isset(rdb_statuses()[$current]) ? rdb_statuses()[$current] : 'Nouvelle demande'); ?></strong>
              <?php if (get_post_meta($id, '_rdb_notification_sent', true) === '0') : ?>
                <p><em>Courriel d’alerte non confirmé. Consultez le réglage SMTP.</em></p>
              <?php endif; ?>
              <?php $history = get_post_meta($id, '_rdb_status_history', false); ?>
              <?php if ($history) : ?>
                <details><summary>Historique des statuts</summary><ul>
                  <?php foreach ($history as $entry) : ?>
                    <li><?php echo esc_html(isset($entry['changed_at']) ? $entry['changed_at'] : ''); ?> —
                      <?php echo esc_html(isset($entry['from']) ? $entry['from'] : ''); ?> →
                      <?php echo esc_html(isset($entry['to']) ? $entry['to'] : ''); ?>
                      (<?php echo esc_html(isset($entry['by_user_id']) ? '#' . $entry['by_user_id'] : ''); ?>)
                    </li>
                  <?php endforeach; ?>
                </ul></details>
              <?php endif; ?>
              <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <input type="hidden" name="action" value="rdb_update_status">
                <input type="hidden" name="rdb_id" value="<?php echo esc_attr($id); ?>">
                <?php wp_nonce_field('rdb_update_' . $id, 'rdb_nonce'); ?>
                <label class="screen-reader-text" for="rdb-status-<?php echo esc_attr($id); ?>">Statut de la demande <?php echo esc_html($id); ?></label>
                <select id="rdb-status-<?php echo esc_attr($id); ?>" name="rdb_status">
                  <?php foreach (rdb_statuses() as $code => $label) : ?>
                    <option value="<?php echo esc_attr($code); ?>" <?php selected($current, $code); ?>><?php echo esc_html($label); ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="button button-secondary" type="submit">Mettre à jour</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php
      if ($query->max_num_pages > 1) {
          echo wp_kses_post(paginate_links(array(
              'base' => add_query_arg(array('page' => 'rdb-demandes', 'statut' => $status, 'paged' => '%#%'), admin_url('admin.php')),
              'format' => '',
              'current' => $page,
              'total' => $query->max_num_pages,
              'type' => 'list',
          )));
      }
      wp_reset_postdata();
      ?>
    </div>
    <?php
}

add_action('admin_post_rdb_update_status', function () {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé.', '', array('response' => 403));
    }
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_die('Méthode non autorisée.', '', array('response' => 405));
    }
    $id = isset($_POST['rdb_id']) ? absint($_POST['rdb_id']) : 0;
    $nonce = isset($_POST['rdb_nonce']) ? sanitize_text_field(wp_unslash($_POST['rdb_nonce'])) : '';
    $status = isset($_POST['rdb_status']) ? sanitize_key(wp_unslash($_POST['rdb_status'])) : '';
    if (!$id || !wp_verify_nonce($nonce, 'rdb_update_' . $id) || !isset(rdb_statuses()[$status]) || get_post_type($id) !== 'rdb_demande') {
        wp_die('Demande invalide.', '', array('response' => 400));
    }
    $old_status = get_post_meta($id, '_rdb_status', true);
    if ($old_status !== $status) {
        add_post_meta($id, '_rdb_status_history', array(
            'from' => $old_status,
            'to' => $status,
            'by_user_id' => get_current_user_id(),
            'changed_at' => current_time('mysql'),
        ));
        update_post_meta($id, '_rdb_status', $status);
        // Updating post_modified also starts the completed-case retention clock.
        wp_update_post(array('ID' => $id));
    }
    wp_safe_redirect(admin_url('admin.php?page=rdb-demandes&updated=1'), 303);
    exit;
});
