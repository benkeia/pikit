<?php

/**
 * Calcul de disponibilité du matériel.
 */

defined('ABSPATH') || exit;

/**
 * Calcule la disponibilité d'un matériel pour une période donnée.
 *
 * @param int    $equipment_id  ID du post CPT "materiels"
 * @param string $start         Datetime de début  (format: 'Y-m-d H:i:s')
 * @param string $end           Datetime de fin    (format: 'Y-m-d H:i:s')
 *
 * @return array{total: int, usable: int, reserved: int, available: int}
 */
function pikit_get_equipment_available(int $equipment_id, string $start, string $end): array
{

    // Guard : période invalide
    if ($start >= $end) {
        return ['total' => 0, 'usable' => 0, 'reserved' => 0, 'available' => 0];
    }

    // --- Stock physique ---
    $items = get_field('equipment_items', $equipment_id) ?: [];

    $total  = count($items);
    $usable = count(array_filter($items, fn($item) => $item['status'] === 'available'));

    // --- Réservations actives sur la période ---
    // On exclut draft et rejected : ils ne bloquent pas de stock.
    $active_statuses = ['pending', 'approved', 'picked_up'];

    // Pré-filtre SQL : ne ramène que les réservations qui chevauchent la période.
    // pickup < $end  ET  return > $start
    $reservations = get_posts([
        'post_type'      => 'reservations',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => 'pickup_datetime',
                'value'   => $end,
                'compare' => '<',
                'type'    => 'DATETIME',
            ],
            [
                'key'     => 'return_datetime',
                'value'   => $start,
                'compare' => '>',
                'type'    => 'DATETIME',
            ],
        ],
    ]);

    $reserved = 0;

    foreach ($reservations as $reservation_id) {

        $res_status = get_field('status', $reservation_id);

        if (! in_array($res_status, $active_statuses, true)) {
            continue;
        }

        $pickup = get_field('pickup_datetime', $reservation_id);
        $return = get_field('return_datetime', $reservation_id);

        // Chevauchement : les deux périodes se croisent si pickup < $end ET return > $start
        if ($pickup >= $end || $return <= $start) {
            continue;
        }

        $equipment_reserved = get_field('equipment_reserved', $reservation_id) ?: [];

        foreach ($equipment_reserved as $line) {
            if ((int) $line['equipment'] === $equipment_id) {
                $reserved += (int) $line['quantity'];
            }
        }
    }

    return [
        'total'     => $total,
        'usable'    => $usable,
        'reserved'  => $reserved,
        'available' => max(0, $usable - $reserved),
    ];
}