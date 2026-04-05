<?php

/**
 * Fonctions utilitaires liées au matériel.
 */

defined('ABSPATH') || exit;

/**
 * Retourne tous les équipements publiés.
 *
 * @return WP_Post[]
 */
function pikit_get_all_equipment(): array
{
    return get_posts([
        'post_type'      => 'materiels',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
}

/**
 * Retourne les disponibilités journalières d'un matériel sur N jours.
 *
 * @param int $equipment_id
 * @param int $days
 * @return array<int, array{date: string, available: int, total: int}>
 */
function pikit_get_equipment_next_days_availability(int $equipment_id, int $days = 7): array
{
    $days = max(1, min(30, $days));
    $result = [];

    for ($i = 0; $i < $days; $i++) {
        $day_ts = strtotime('+' . $i . ' day');
        if ($day_ts === false) {
            continue;
        }

        $start = wp_date('Y-m-d 00:00:00', $day_ts);
        $end = wp_date('Y-m-d 23:59:59', $day_ts);
        $availability = pikit_get_equipment_available($equipment_id, $start, $end, 0);

        $result[] = [
            'date' => wp_date('Y-m-d', $day_ts),
            'available' => (int) $availability['available'],
            'total' => (int) $availability['total'],
        ];
    }

    return $result;
}