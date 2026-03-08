<?php

/**
 * Fonctions utilitaires pour les réservations.
 */

defined('ABSPATH') || exit;
// ---------------------------------------------------------------------------
// Snapshot utilisateur à la création
// ---------------------------------------------------------------------------

add_action( 'acf/save_post', 'pikit_snapshot_user_on_reservation', 5 );

/**
 * Remplit les champs snapshot (promotion, groupe TD/TP) lors de la première
 * sauvegarde d'une réservation, à partir du profil de l'auteur.
 * Priorité 5 : s'exécute avant la sauvegarde ACF normale.
 */
function pikit_snapshot_user_on_reservation( $post_id ): void {

    if ( get_post_type( $post_id ) !== 'reservations' ) {
        return;
    }

    // Ne remplir qu'une seule fois (champ vide = première création)
    if ( get_field( 'snapshot_promotion', $post_id ) ) {
        return;
    }

    $author_id = (int) get_post_field( 'post_author', $post_id );

    update_field( 'snapshot_promotion', pikit_get_user_promotion( $author_id ), $post_id );
    update_field( 'snapshot_group_td',  pikit_get_user_group_td( $author_id ),  $post_id );
    update_field( 'snapshot_group_tp',  pikit_get_user_group_tp( $author_id ),  $post_id );
}
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
