<?php

/**
 * Logique metier des reservations.
 */

if (!defined('ABSPATH')) exit;

add_action('acf/save_post', 'pikit_snapshot_user_on_reservation', 5);
add_action('init', 'pikit_handle_reservation_actions');
add_action('init', 'pikit_handle_single_equipment_reservation_actions');

// ---------------------------------------------------------------------------
// Snapshot utilisateur a la creation
// ---------------------------------------------------------------------------

/**
 * Remplit les champs snapshot (promotion, groupe TD/TP) lors de la premiere
 * sauvegarde d'une reservation, a partir du profil de l'auteur.
 * Priorite 5 : s'execute avant la sauvegarde ACF normale.
 */
function pikit_snapshot_user_on_reservation($post_id): void
{
    if (get_post_type($post_id) !== 'reservations') {
        return;
    }

    // Ne remplir qu'une seule fois (champ vide = premiere creation)
    if (get_field('snapshot_promotion', $post_id)) {
        return;
    }

    $author_id = (int) get_post_field('post_author', $post_id);

    update_field('snapshot_promotion', pikit_get_user_promotion($author_id), $post_id);
    update_field('snapshot_group_td', pikit_get_user_group_td($author_id), $post_id);
    update_field('snapshot_group_tp', pikit_get_user_group_tp($author_id), $post_id);
}

// ---------------------------------------------------------------------------
// Lecture et autorisations
// ---------------------------------------------------------------------------

/**
 * Retourne les reservations d'un utilisateur.
 *
 * @param int $user_id
 * @param array<string, mixed> $filters
 * @return WP_Post[]
 */
function pikit_get_user_reservations(int $user_id, array $filters = []): array
{
    $all = get_posts([
        'post_type'      => 'reservations',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $status_filter = isset($filters['status']) ? sanitize_key((string) $filters['status']) : '';
    $project_filter = isset($filters['project_id']) ? (int) $filters['project_id'] : 0;

    return array_values(array_filter($all, static function ($reservation) use ($status_filter, $project_filter) {
        if (!($reservation instanceof WP_Post)) {
            return false;
        }

        if ($status_filter !== '' && sanitize_key((string) get_field('status', $reservation->ID)) !== $status_filter) {
            return false;
        }

        if ($project_filter > 0 && (int) get_field('project', $reservation->ID) !== $project_filter) {
            return false;
        }

        return true;
    }));
}

/**
 * Indique si la reservation peut etre modifiee.
 *
 * @param int $reservation_id
 * @return bool
 */
function pikit_can_edit_reservation(int $reservation_id): bool
{
    $reservation = get_post($reservation_id);

    if (!($reservation instanceof WP_Post) || $reservation->post_type !== 'reservations') {
        return false;
    }

    if ((int) $reservation->post_author !== get_current_user_id()) {
        return false;
    }

    $status = sanitize_key((string) get_field('status', $reservation_id));

    return $status === 'draft';
}

/**
 * Supprime une reservation en brouillon.
 *
 * @param int $reservation_id
 * @return bool|WP_Error
 */
function pikit_delete_reservation(int $reservation_id)
{
    if (!pikit_can_edit_reservation($reservation_id)) {
        return new WP_Error('forbidden', 'Seules les demandes en brouillon peuvent etre supprimees.');
    }

    $deleted = wp_delete_post($reservation_id, true);

    if (!($deleted instanceof WP_Post)) {
        return new WP_Error('delete_failed', 'Impossible de supprimer la demande.');
    }

    return true;
}

// ---------------------------------------------------------------------------
// Cycle de vie des demandes
// ---------------------------------------------------------------------------

/**
 * Retourne toutes les reservations actives qui se chevauchent avec une periode.
 *
 * @param string $start Datetime de debut (format: 'Y-m-d H:i:s')
 * @param string $end   Datetime de fin   (format: 'Y-m-d H:i:s')
 * @return int[] IDs des reservations
 */
function pikit_get_reservations_between(string $start, string $end): array
{
    $active_statuses = ['pending', 'approved', 'picked_up'];

    $all = get_posts([
        'post_type'      => 'reservations',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    $result = [];

    foreach ($all as $reservation_id) {
        if (!in_array(get_field('status', $reservation_id), $active_statuses, true)) {
            continue;
        }

        $pickup = (string) get_field('pickup_datetime', $reservation_id);
        $return = (string) get_field('return_datetime', $reservation_id);

        if ($pickup < $end && $return > $start) {
            $result[] = (int) $reservation_id;
        }
    }

    return $result;
}

/**
 * Soumet une reservation (draft -> pending).
 *
 * @param int $reservation_id
 * @return bool|WP_Error
 */
function pikit_submit_reservation(int $reservation_id)
{
    $reservation = get_post($reservation_id);

    if (!($reservation instanceof WP_Post) || $reservation->post_type !== 'reservations') {
        return new WP_Error('invalid_reservation', 'Reservation introuvable.');
    }

    if ((int) $reservation->post_author !== get_current_user_id()) {
        return new WP_Error('forbidden', 'Vous ne pouvez pas soumettre cette reservation.');
    }

    if (!pikit_can_edit_reservation($reservation_id)) {
        return new WP_Error('invalid_status', 'Cette demande ne peut plus etre modifiee.');
    }

    $pickup = (string) get_field('pickup_datetime', $reservation_id);
    $return = (string) get_field('return_datetime', $reservation_id);
    $equipment_rows = get_field('equipment_reserved', $reservation_id) ?: [];

    $period_check = pikit_validate_iut_period($pickup, $return);
    if (is_wp_error($period_check)) {
        return $period_check;
    }

    if (empty($equipment_rows)) {
        return new WP_Error('missing_items', 'Ajoutez au moins un materiel avant soumission.');
    }

    $availability_check = pikit_validate_rows_availability($equipment_rows, $pickup, $return, $reservation_id);
    if (is_wp_error($availability_check)) {
        return $availability_check;
    }

    update_field('status', 'pending', $reservation_id);

    return true;
}

/**
 * Cree une reservation en brouillon.
 *
 * @param int $user_id
 * @param array<string, mixed> $data
 * @return int|WP_Error
 */
function pikit_create_reservation_draft(int $user_id, array $data)
{
    $project_id = isset($data['project_id']) ? (int) $data['project_id'] : 0;
    $pickup = isset($data['pickup_datetime']) ? sanitize_text_field((string) $data['pickup_datetime']) : '';
    $return = isset($data['return_datetime']) ? sanitize_text_field((string) $data['return_datetime']) : '';
    $rows = isset($data['rows']) && is_array($data['rows']) ? $data['rows'] : [];

    if ($project_id > 0) {
        $project = get_post($project_id);
        if (!($project instanceof WP_Post) || $project->post_type !== 'projet') {
            return new WP_Error('invalid_project', 'Projet invalide.');
        }

        if ((int) $project->post_author !== $user_id) {
            return new WP_Error('forbidden_project', 'Vous ne pouvez pas creer de demande pour ce projet.');
        }
    }

    $period_check = pikit_validate_iut_period($pickup, $return);
    if (is_wp_error($period_check)) {
        return $period_check;
    }

    $normalized_rows = pikit_normalize_equipment_rows($rows);
    if (empty($normalized_rows)) {
        return new WP_Error('missing_items', 'Ajoutez au moins un materiel a la demande.');
    }

    $availability_check = pikit_validate_rows_availability($normalized_rows, $pickup, $return, 0);
    if (is_wp_error($availability_check)) {
        return $availability_check;
    }

    $reservation_id = wp_insert_post([
        'post_type'   => 'reservations',
        'post_status' => 'publish',
        'post_author' => $user_id,
        'post_title'  => sprintf('Demande %s', wp_date('d/m/Y H:i')),
    ], true);

    if (is_wp_error($reservation_id)) {
        return $reservation_id;
    }

    if ($project_id > 0) {
        update_field('project', $project_id, $reservation_id);
    }
    update_field('pickup_datetime', $pickup, $reservation_id);
    update_field('return_datetime', $return, $reservation_id);
    update_field('status', 'draft', $reservation_id);
    update_field('equipment_reserved', $normalized_rows, $reservation_id);

    return (int) $reservation_id;
}

/**
 * Met a jour une reservation en brouillon.
 *
 * @param int $reservation_id
 * @param array<string, mixed> $data
 * @return bool|WP_Error
 */
function pikit_update_reservation_draft(int $reservation_id, array $data)
{
    if (!pikit_can_edit_reservation($reservation_id)) {
        return new WP_Error('forbidden', 'Cette demande ne peut pas etre modifiee.');
    }

    $pickup = isset($data['pickup_datetime']) ? sanitize_text_field((string) $data['pickup_datetime']) : '';
    $return = isset($data['return_datetime']) ? sanitize_text_field((string) $data['return_datetime']) : '';
    $rows = isset($data['rows']) && is_array($data['rows']) ? $data['rows'] : [];

    $period_check = pikit_validate_iut_period($pickup, $return);
    if (is_wp_error($period_check)) {
        return $period_check;
    }

    $normalized_rows = pikit_normalize_equipment_rows($rows);
    if (empty($normalized_rows)) {
        return new WP_Error('missing_items', 'Ajoutez au moins un materiel a la demande.');
    }

    $availability_check = pikit_validate_rows_availability($normalized_rows, $pickup, $return, $reservation_id);
    if (is_wp_error($availability_check)) {
        return $availability_check;
    }

    update_field('pickup_datetime', $pickup, $reservation_id);
    update_field('return_datetime', $return, $reservation_id);
    update_field('equipment_reserved', $normalized_rows, $reservation_id);

    return true;
}

// ---------------------------------------------------------------------------
// Validation metier
// ---------------------------------------------------------------------------

/**
 * Valide une periode de reservation selon les contraintes IUT.
 *
 * @param string $pickup
 * @param string $return
 * @return true|WP_Error
 */
function pikit_validate_iut_period(string $pickup, string $return)
{
    if ($pickup === '' || $return === '') {
        return new WP_Error('missing_dates', 'Les dates de retrait et de retour sont obligatoires.');
    }

    if ($pickup >= $return) {
        return new WP_Error('invalid_dates', 'La date de retour doit etre posterieure a la date de retrait.');
    }

    if (!pikit_is_iut_slot($pickup) || !pikit_is_iut_slot($return)) {
        return new WP_Error('invalid_slot', 'Les creneaux doivent etre en jours ouvres, entre 08:30 et 17:30.');
    }

    return true;
}

/**
 * Verifie qu'une date respecte les horaires IUT (jours ouvres 8h30-17h30).
 *
 * @param string $datetime
 * @return bool
 */
function pikit_is_iut_slot(string $datetime): bool
{
    $ts = strtotime($datetime);
    if ($ts === false) {
        return false;
    }

    $day = (int) wp_date('N', $ts);
    if ($day < 1 || $day > 5) {
        return false;
    }

    $minutes = ((int) wp_date('H', $ts) * 60) + (int) wp_date('i', $ts);

    return $minutes >= 510 && $minutes <= 1050;
}

/**
 * Normalise les lignes materiel d'une demande.
 *
 * @param array<int, array<string, mixed>> $rows
 * @return array<int, array<string, mixed>>
 */
function pikit_normalize_equipment_rows(array $rows): array
{
    $normalized = [];

    foreach ($rows as $row) {
        $equipment_id = isset($row['equipment']) ? (int) $row['equipment'] : 0;
        $quantity = isset($row['quantity']) ? (int) $row['quantity'] : 0;
        $notes = isset($row['notes']) ? sanitize_textarea_field((string) $row['notes']) : '';

        if ($equipment_id <= 0 || $quantity < 1) {
            continue;
        }

        $normalized[] = [
            'equipment' => $equipment_id,
            'quantity'  => $quantity,
            'notes'     => $notes,
        ];
    }

    return $normalized;
}

/**
 * Verifie la disponibilite sur une liste de lignes materiel.
 *
 * @param array<int, array<string, mixed>> $rows
 * @param string $pickup
 * @param string $return
 * @param int $exclude_reservation_id
 * @return true|WP_Error
 */
function pikit_validate_rows_availability(array $rows, string $pickup, string $return, int $exclude_reservation_id)
{
    foreach ($rows as $row) {
        $equipment_id = (int) ($row['equipment'] ?? 0);
        $quantity = (int) ($row['quantity'] ?? 0);

        if ($equipment_id <= 0 || $quantity < 1) {
            continue;
        }

        $availability = pikit_get_equipment_available($equipment_id, $pickup, $return, $exclude_reservation_id);

        if ($quantity > (int) $availability['available']) {
            return new WP_Error(
                'stock_unavailable',
                sprintf(
                    'Stock insuffisant pour "%s" (demande: %d, disponible: %d).',
                    get_the_title($equipment_id),
                    $quantity,
                    (int) $availability['available']
                )
            );
        }
    }

    return true;
}

// ---------------------------------------------------------------------------
// Actions dashboard (POST)
// ---------------------------------------------------------------------------

/**
 * Handle dashboard reservation form actions.
 */
function pikit_handle_reservation_actions(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        return;
    }

    $action = isset($_POST['pikit_action']) ? sanitize_key((string) wp_unslash($_POST['pikit_action'])) : '';
    if (!in_array($action, ['create_reservation_draft', 'update_reservation_draft', 'submit_reservation', 'delete_reservation'], true)) {
        return;
    }

    if (!is_user_logged_in()) {
        wp_safe_redirect(pikit_get_login_page_url(['login_error' => 'auth_required']));
        exit;
    }

    $user_id = get_current_user_id();

    if ($action === 'create_reservation_draft') {
        $nonce = isset($_POST['pikit_nonce']) ? (string) wp_unslash($_POST['pikit_nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pikit_create_reservation')) {
            wp_safe_redirect(pikit_get_dashboard_url(['tab' => 'projets', 'notice' => 'invalid_nonce']));
            exit;
        }

        $project_id = isset($_POST['project_id']) ? (int) wp_unslash($_POST['project_id']) : 0;

        $reservation_id = pikit_create_reservation_draft($user_id, [
            'project_id'      => $project_id,
            'pickup_datetime' => isset($_POST['pickup_datetime']) ? (string) wp_unslash($_POST['pickup_datetime']) : '',
            'return_datetime' => isset($_POST['return_datetime']) ? (string) wp_unslash($_POST['return_datetime']) : '',
            'rows'            => pikit_collect_rows_from_post(),
        ]);

        if (is_wp_error($reservation_id)) {
            wp_safe_redirect(pikit_get_dashboard_url([
                'tab' => 'projets',
                'project_id' => $project_id,
                'notice' => 'reservation_error',
                'notice_message' => rawurlencode($reservation_id->get_error_message()),
            ]));
            exit;
        }

        wp_safe_redirect(pikit_get_dashboard_url([
            'tab' => 'demandes',
            'reservation_id' => (int) $reservation_id,
            'notice' => 'reservation_created',
        ]));
        exit;
    }

    $reservation_id = isset($_POST['reservation_id']) ? (int) wp_unslash($_POST['reservation_id']) : 0;

    if ($reservation_id <= 0) {
        wp_safe_redirect(pikit_get_dashboard_url(['tab' => 'demandes', 'notice' => 'reservation_missing']));
        exit;
    }

    if ($action === 'update_reservation_draft') {
        $nonce = isset($_POST['pikit_nonce']) ? (string) wp_unslash($_POST['pikit_nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pikit_update_reservation_' . $reservation_id)) {
            wp_safe_redirect(pikit_get_dashboard_url(['tab' => 'demandes', 'reservation_id' => $reservation_id, 'notice' => 'invalid_nonce']));
            exit;
        }

        $updated = pikit_update_reservation_draft($reservation_id, [
            'pickup_datetime' => isset($_POST['pickup_datetime']) ? (string) wp_unslash($_POST['pickup_datetime']) : '',
            'return_datetime' => isset($_POST['return_datetime']) ? (string) wp_unslash($_POST['return_datetime']) : '',
            'rows'            => pikit_collect_rows_from_post(),
        ]);

        if (is_wp_error($updated)) {
            wp_safe_redirect(pikit_get_dashboard_url([
                'tab' => 'demandes',
                'reservation_id' => $reservation_id,
                'notice' => 'reservation_error',
                'notice_message' => rawurlencode($updated->get_error_message()),
            ]));
            exit;
        }

        wp_safe_redirect(pikit_get_dashboard_url(['tab' => 'demandes', 'reservation_id' => $reservation_id, 'notice' => 'reservation_updated']));
        exit;
    }

    if ($action === 'submit_reservation') {
        $nonce = isset($_POST['pikit_nonce']) ? (string) wp_unslash($_POST['pikit_nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pikit_submit_reservation_' . $reservation_id)) {
            wp_safe_redirect(pikit_get_dashboard_url(['tab' => 'demandes', 'reservation_id' => $reservation_id, 'notice' => 'invalid_nonce']));
            exit;
        }

        $submitted = pikit_submit_reservation($reservation_id);

        if (is_wp_error($submitted)) {
            wp_safe_redirect(pikit_get_dashboard_url([
                'tab' => 'demandes',
                'reservation_id' => $reservation_id,
                'notice' => 'reservation_error',
                'notice_message' => rawurlencode($submitted->get_error_message()),
            ]));
            exit;
        }

        wp_safe_redirect(pikit_get_dashboard_url(['tab' => 'demandes', 'reservation_id' => $reservation_id, 'notice' => 'reservation_submitted']));
        exit;
    }

    if ($action === 'delete_reservation') {
        $nonce = isset($_POST['pikit_nonce']) ? (string) wp_unslash($_POST['pikit_nonce']) : '';
        if (!wp_verify_nonce($nonce, 'pikit_delete_reservation_' . $reservation_id)) {
            wp_safe_redirect(pikit_get_dashboard_url(['tab' => 'demandes', 'reservation_id' => $reservation_id, 'notice' => 'invalid_nonce']));
            exit;
        }

        $deleted = pikit_delete_reservation($reservation_id);
        if (is_wp_error($deleted)) {
            wp_safe_redirect(pikit_get_dashboard_url([
                'tab' => 'demandes',
                'reservation_id' => $reservation_id,
                'notice' => 'reservation_error',
                'notice_message' => rawurlencode($deleted->get_error_message()),
            ]));
            exit;
        }

        wp_safe_redirect(pikit_get_dashboard_url(['tab' => 'demandes', 'notice' => 'reservation_deleted']));
        exit;
    }
}

/**
 * Collecte les lignes materiel postees depuis les formulaires dashboard.
 *
 * @return array<int, array<string, mixed>>
 */
function pikit_collect_rows_from_post(): array
{
    $equipment_ids = isset($_POST['equipment_ids']) && is_array($_POST['equipment_ids'])
        ? wp_unslash($_POST['equipment_ids'])
        : [];
    $quantities = isset($_POST['quantities']) && is_array($_POST['quantities'])
        ? wp_unslash($_POST['quantities'])
        : [];
    $notes = isset($_POST['notes']) && is_array($_POST['notes'])
        ? wp_unslash($_POST['notes'])
        : [];

    $rows = [];
    $count = max(count($equipment_ids), count($quantities), count($notes));

    for ($i = 0; $i < $count; $i++) {
        $rows[] = [
            'equipment' => isset($equipment_ids[$i]) ? (int) $equipment_ids[$i] : 0,
            'quantity'  => isset($quantities[$i]) ? (int) $quantities[$i] : 0,
            'notes'     => isset($notes[$i]) ? sanitize_textarea_field((string) $notes[$i]) : '',
        ];
    }

    return $rows;
}

/**
 * Normalise une date issue d'un champ datetime-local.
 *
 * @param string $datetime
 * @return string
 */
function pikit_normalize_datetime_local(string $datetime): string
{
    $timestamp = strtotime(str_replace('T', ' ', $datetime));

    return $timestamp !== false ? wp_date('Y-m-d H:i:s', $timestamp) : '';
}

/**
 * Cree une reservation directe pour une fiche materiel.
 *
 * @param int $user_id
 * @param int $equipment_id
 * @param string $pickup
 * @param string $return
 * @return int|WP_Error
 */
function pikit_create_single_equipment_reservation(int $user_id, int $equipment_id, string $pickup, string $return)
{
    $equipment = get_post($equipment_id);

    if (!($equipment instanceof WP_Post) || $equipment->post_type !== 'materiels') {
        return new WP_Error('invalid_equipment', 'Matériel introuvable.');
    }

    $pickup = pikit_normalize_datetime_local($pickup);
    $return = pikit_normalize_datetime_local($return);

    $period_check = pikit_validate_iut_period($pickup, $return);
    if (is_wp_error($period_check)) {
        return $period_check;
    }

    $rows = [[
        'equipment' => $equipment_id,
        'quantity' => 1,
        'notes' => '',
    ]];

    $availability_check = pikit_validate_rows_availability($rows, $pickup, $return, 0);
    if (is_wp_error($availability_check)) {
        return $availability_check;
    }

    $reservation_id = wp_insert_post([
        'post_type'   => 'reservations',
        'post_status' => 'publish',
        'post_author' => $user_id,
        'post_title'  => sprintf('%s - %s', get_the_title($equipment_id), wp_date('d/m/Y H:i')),
    ], true);

    if (is_wp_error($reservation_id)) {
        return $reservation_id;
    }

    update_field('pickup_datetime', $pickup, $reservation_id);
    update_field('return_datetime', $return, $reservation_id);
    update_field('status', 'pending', $reservation_id);
    update_field('equipment_reserved', $rows, $reservation_id);

    return (int) $reservation_id;
}

/**
 * Handle direct reservation from a material single page.
 */
function pikit_handle_single_equipment_reservation_actions(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        return;
    }

    $action = isset($_POST['pikit_action']) ? sanitize_key((string) wp_unslash($_POST['pikit_action'])) : '';
    if ($action !== 'create_single_equipment_reservation') {
        return;
    }

    if (!is_user_logged_in()) {
        wp_safe_redirect(pikit_get_login_page_url(['login_error' => 'auth_required', 'redirect_to' => wp_get_referer() ?: home_url('/')]));
        exit;
    }

    $nonce = isset($_POST['pikit_nonce']) ? (string) wp_unslash($_POST['pikit_nonce']) : '';
    if (!wp_verify_nonce($nonce, 'pikit_create_single_equipment_reservation')) {
        wp_safe_redirect(add_query_arg(['reservation_error' => 'invalid_nonce'], wp_get_referer() ?: home_url('/')));
        exit;
    }

    $user_id = get_current_user_id();
    $equipment_id = isset($_POST['equipment_id']) ? (int) wp_unslash($_POST['equipment_id']) : 0;
    $pickup = isset($_POST['pickup_datetime']) ? sanitize_text_field((string) wp_unslash($_POST['pickup_datetime'])) : '';
    $return = isset($_POST['return_datetime']) ? sanitize_text_field((string) wp_unslash($_POST['return_datetime'])) : '';

    $reservation_id = pikit_create_single_equipment_reservation($user_id, $equipment_id, $pickup, $return);

    if (is_wp_error($reservation_id)) {
        wp_safe_redirect(add_query_arg([
            'reservation_error' => $reservation_id->get_error_code(),
            'reservation_error_message' => rawurlencode($reservation_id->get_error_message()),
        ], wp_get_referer() ?: home_url('/')));
        exit;
    }

    wp_safe_redirect(pikit_get_mes_reservations_url([
        'reservation_created' => '1',
        'reservation_id' => (int) $reservation_id,
    ]));
    exit;
}
