<?php

/**
 * Fonctions utilitaires pour les réservations.
 */

defined('ABSPATH') || exit;

/**
 * Retourne toutes les réservations actives qui se chevauchent avec une période.
 *
 * @param string $start Datetime de début (format: 'Y-m-d H:i:s')
 * @param string $end   Datetime de fin   (format: 'Y-m-d H:i:s')
 *
 * @return int[] IDs des réservations
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

        if (! in_array(get_field('status', $reservation_id), $active_statuses, true)) {
            continue;
        }

        $pickup = get_field('pickup_datetime', $reservation_id);
        $return = get_field('return_datetime', $reservation_id);

        if ($pickup < $end && $return > $start) {
            $result[] = $reservation_id;
        }
    }

    return $result;
}

/**
 * Retourne les réservations associées à un projet.
 *
 * @param int $project_id ID du post CPT "projet"
 *
 * @return int[] IDs des réservations
 */
function pikit_get_project_reservations(int $project_id): array
{

    $reservations = get_posts([
        'post_type'      => 'reservations',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ]);

    return array_values(array_filter(
        $reservations,
        fn($id) => (int) get_field('project', $id) === $project_id
    ));
}
