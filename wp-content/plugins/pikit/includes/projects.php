<?php

/**
 * Logique métier des projets.
 */

if (!defined('ABSPATH')) exit;

/**
 * URL du dashboard étudiant.
 *
 * @param array<string, string|int> $args
 * @return string
 */
function pikit_get_dashboard_url(array $args = []): string
{
    $page = get_page_by_path('dashboard');

    $url = $page instanceof WP_Post
        ? get_permalink($page)
        : home_url('/dashboard/');

    return !empty($args) ? add_query_arg($args, $url) : $url;
}

/**
 * Retourne les projets d'un utilisateur.
 *
 * @param int $user_id
 * @return WP_Post[]
 */
function pikit_get_user_projects(int $user_id): array
{
    return get_posts([
        'post_type'      => 'projet',
        'post_status'    => 'publish',
        'author'         => $user_id,
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
}

/**
 * Crée un projet pour un utilisateur.
 *
 * @param int                         $user_id
 * @param array<string, mixed>        $data
 * @return int|WP_Error
 */
function pikit_create_project(int $user_id, array $data)
{
    $title = isset($data['title']) ? sanitize_text_field((string) $data['title']) : '';
    $description = isset($data['description']) ? sanitize_textarea_field((string) $data['description']) : '';
    $status = isset($data['status']) ? sanitize_key((string) $data['status']) : 'draft';

    $allowed_statuses = ['draft', 'not_started', 'in_progress', 'completed'];

    if ($title === '') {
        return new WP_Error('missing_title', 'Le nom du projet est obligatoire.');
    }

    if (!in_array($status, $allowed_statuses, true)) {
        $status = 'draft';
    }

    $project_id = wp_insert_post([
        'post_type'   => 'projet',
        'post_status' => 'publish',
        'post_title'  => $title,
        'post_author' => $user_id,
    ], true);

    if (is_wp_error($project_id)) {
        return $project_id;
    }

    update_field('description', $description, $project_id);
    update_field('status', $status, $project_id);

    return (int) $project_id;
}

/**
 * Retourne les réservations d'un projet donné.
 *
 * @param int $project_id
 * @return WP_Post[]
 */
function pikit_get_project_reservations(int $project_id): array
{
    $reservations = get_posts([
        'post_type'      => 'reservations',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    return array_values(array_filter(
        $reservations,
        static fn($reservation) => (int) get_field('project', $reservation->ID) === $project_id
    ));
}

add_action('init', 'pikit_handle_project_actions');

/**
 * Handle dashboard project form actions.
 */
function pikit_handle_project_actions(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        return;
    }

    $action = isset($_POST['pikit_action']) ? sanitize_key((string) wp_unslash($_POST['pikit_action'])) : '';
    if ($action !== 'create_project') {
        return;
    }

    if (!is_user_logged_in()) {
        wp_safe_redirect(pikit_get_login_page_url(['login_error' => 'auth_required']));
        exit;
    }

    $nonce = isset($_POST['pikit_nonce']) ? (string) wp_unslash($_POST['pikit_nonce']) : '';
    if (!wp_verify_nonce($nonce, 'pikit_create_project')) {
        wp_safe_redirect(pikit_get_dashboard_url(['tab' => 'projets', 'notice' => 'invalid_nonce']));
        exit;
    }

    $user_id = get_current_user_id();

    $project_id = pikit_create_project($user_id, [
        'title'       => isset($_POST['project_title']) ? (string) wp_unslash($_POST['project_title']) : '',
        'description' => isset($_POST['project_description']) ? (string) wp_unslash($_POST['project_description']) : '',
        'status'      => isset($_POST['project_status']) ? (string) wp_unslash($_POST['project_status']) : 'draft',
    ]);

    if (is_wp_error($project_id)) {
        wp_safe_redirect(pikit_get_dashboard_url([
            'tab' => 'projets',
            'notice' => 'project_error',
            'notice_message' => rawurlencode($project_id->get_error_message()),
        ]));
        exit;
    }

    wp_safe_redirect(pikit_get_dashboard_url([
        'tab' => 'projets',
        'project_id' => (int) $project_id,
        'notice' => 'project_created',
    ]));
    exit;
}
