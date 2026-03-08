<?php

/**
 * Validation des réservations avant sauvegarde.
 *
 * Bloque toute réservation dont la quantité demandée dépasse
 * le stock disponible sur la période choisie.
 */

defined('ABSPATH') || exit;

add_action('acf/validate_save_post', 'pikit_validate_reservation');

function pikit_validate_reservation(): void
{

    // Cibler uniquement le CPT reservations
    if (! isset($_POST['post_type']) || $_POST['post_type'] !== 'reservations') {
        return;
    }

    $fields = $_POST['acf'] ?? [];

    $pickup = $fields['field_67cc900011'] ?? '';
    $return = $fields['field_67cc900012'] ?? '';

    if (! $pickup || ! $return) {
        return;
    }

    if ($pickup >= $return) {
        acf_add_validation_error('acf[field_67cc900012]', 'La date de retour doit être postérieure à la date de retrait.');
        return;
    }

    // ID du post en cours d'édition (0 = nouvelle réservation)
    $current_id = (int) ($_POST['post_ID'] ?? 0);

    $equipment_reserved = $fields['field_67cc900014'] ?? [];

    foreach ($equipment_reserved as $row) {

        $equipment_id = (int) ($row['field_67cc900015'] ?? 0);
        $quantity     = (int) ($row['field_67cc900016'] ?? 0);

        if (! $equipment_id || $quantity < 1) {
            continue;
        }

        $availability = pikit_get_equipment_available($equipment_id, $pickup, $return);

        // Si on édite une réservation existante, on "rend" son stock actuel
        // pour ne pas se bloquer sur sa propre quantité
        $self_reserved = 0;

        if ($current_id) {
            $existing = get_field('equipment_reserved', $current_id) ?: [];
            foreach ($existing as $line) {
                if ((int) $line['equipment'] === $equipment_id) {
                    $self_reserved += (int) $line['quantity'];
                }
            }
        }

        $real_available = $availability['available'] + $self_reserved;

        if ($quantity > $real_available) {
            $name = get_the_title($equipment_id);
            acf_add_validation_error(
                "acf[field_67cc900014][{$row['_id']}][field_67cc900016]",
                sprintf(
                    '"%s" : quantité demandée (%d) supérieure au stock disponible (%d).',
                    esc_html($name),
                    $quantity,
                    $real_available
                )
            );
        }
    }
}