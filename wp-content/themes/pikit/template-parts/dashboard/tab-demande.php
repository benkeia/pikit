<?php
/**
 * Tab d'une demande de réservation.
 *
 * @var array $args
 */

if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) return;

$current_user_id = isset($args['current_user_id']) ? (int) $args['current_user_id'] : get_current_user_id();
$reservation_id = isset($args['reservation_id']) ? (int) $args['reservation_id'] : 0;
$dashboard_url = function_exists('pikit_get_dashboard_url') ? pikit_get_dashboard_url() : home_url('/dashboard/');
$user_reservations = function_exists('pikit_get_user_reservations') ? pikit_get_user_reservations($current_user_id) : [];
$equipments = function_exists('pikit_get_all_equipment') ? pikit_get_all_equipment() : [];

if ($reservation_id <= 0) :
?>
<header class="pk-dashboard-tab-header">
    <p class="pk-dashboard-eyebrow">Demandes</p>
    <h1 class="pk-dashboard-title">Mes demandes</h1>
</header>
<section class="pk-dashboard-card">
    <?php if (empty($user_reservations)) : ?>
        <p>Aucune demande pour le moment.</p>
    <?php else : ?>
        <ul class="pk-dashboard-list">
            <?php foreach ($user_reservations as $reservation) :
                $status = (string) get_field('status', $reservation->ID);
                $link = add_query_arg([
                    'tab' => 'demandes',
                    'reservation_id' => (int) $reservation->ID,
                ], $dashboard_url);
            ?>
                <li>
                    <a href="<?php echo esc_url($link); ?>" class="pk-dashboard-project-link">
                        <span class="pk-dashboard-project-title">Demande #<?php echo (int) $reservation->ID; ?></span>
                        <span class="pk-dashboard-tag"><?php echo esc_html($status !== '' ? $status : 'draft'); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
<?php
return;
endif;

$reservation = get_post($reservation_id);
if (!($reservation instanceof WP_Post) || $reservation->post_type !== 'reservations' || (int) $reservation->post_author !== $current_user_id) {
    echo '<div class="pk-dashboard-card"><p>Demande introuvable ou accès refusé.</p></div>';
    return;
}

$is_editable = function_exists('pikit_can_edit_reservation') ? pikit_can_edit_reservation($reservation_id) : false;
$status = (string) get_field('status', $reservation_id);
$pickup = (string) get_field('pickup_datetime', $reservation_id);
$return = (string) get_field('return_datetime', $reservation_id);
$rows = get_field('equipment_reserved', $reservation_id) ?: [];
?>
<header class="pk-dashboard-tab-header">
    <p class="pk-dashboard-eyebrow">Demande</p>
    <h1 class="pk-dashboard-title">Demande #<?php echo (int) $reservation_id; ?></h1>
    <p class="pk-dashboard-subtitle">Statut : <strong><?php echo esc_html($status !== '' ? $status : 'draft'); ?></strong></p>
</header>

<section class="pk-dashboard-card">
    <?php if ($is_editable) : ?>
        <h2>Modifier le brouillon</h2>
        <form method="post" class="pk-dashboard-form" data-pikit-rows-form>
            <?php wp_nonce_field('pikit_update_reservation_' . $reservation_id, 'pikit_nonce'); ?>
            <input type="hidden" name="pikit_action" value="update_reservation_draft">
            <input type="hidden" name="reservation_id" value="<?php echo (int) $reservation_id; ?>">

            <div class="pk-dashboard-grid-2">
                <div>
                    <label for="pickup_datetime_edit">Retrait</label>
                    <input id="pickup_datetime_edit" name="pickup_datetime" type="datetime-local" value="<?php echo esc_attr(str_replace(' ', 'T', $pickup)); ?>" required>
                </div>
                <div>
                    <label for="return_datetime_edit">Retour</label>
                    <input id="return_datetime_edit" name="return_datetime" type="datetime-local" value="<?php echo esc_attr(str_replace(' ', 'T', $return)); ?>" required>
                </div>
            </div>

            <div class="pk-dashboard-items" data-pikit-rows>
                <h3>Matériels</h3>
                <?php if (empty($rows)) : $rows = [['equipment' => 0, 'quantity' => 1, 'notes' => '']]; endif; ?>
                <?php foreach ($rows as $row) : ?>
                    <div class="pk-dashboard-item-row" data-pikit-row>
                        <select name="equipment_ids[]" required>
                            <option value="">Choisir un matériel</option>
                            <?php foreach ($equipments as $equipment) : ?>
                                <option value="<?php echo (int) $equipment->ID; ?>" <?php selected((int) ($row['equipment'] ?? 0), (int) $equipment->ID); ?>>
                                    <?php echo esc_html($equipment->post_title); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="quantities[]" min="1" value="<?php echo (int) ($row['quantity'] ?? 1); ?>" required>
                        <input type="text" name="notes[]" value="<?php echo esc_attr((string) ($row['notes'] ?? '')); ?>" placeholder="Notes (optionnel)">
                        <button type="button" data-pikit-remove-row>Retirer</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pk-dashboard-actions-row">
                <button type="button" class="is-secondary" data-pikit-add-row>Ajouter une ligne</button>
                <button type="submit">Enregistrer le brouillon</button>
            </div>
        </form>

        <div class="pk-dashboard-actions-row pk-dashboard-form-spaced">
            <form method="post">
                <?php wp_nonce_field('pikit_submit_reservation_' . $reservation_id, 'pikit_nonce'); ?>
                <input type="hidden" name="pikit_action" value="submit_reservation">
                <input type="hidden" name="reservation_id" value="<?php echo (int) $reservation_id; ?>">
                <button type="submit">Soumettre la demande</button>
            </form>

            <form method="post" data-pikit-confirm-delete>
                <?php wp_nonce_field('pikit_delete_reservation_' . $reservation_id, 'pikit_nonce'); ?>
                <input type="hidden" name="pikit_action" value="delete_reservation">
                <input type="hidden" name="reservation_id" value="<?php echo (int) $reservation_id; ?>">
                <button type="submit" class="is-danger">Supprimer le brouillon</button>
            </form>
        </div>
    <?php else : ?>
        <h2>Détail de la demande</h2>
        <p>Retrait : <?php echo esc_html($pickup); ?></p>
        <p>Retour : <?php echo esc_html($return); ?></p>
        <h3>Matériels demandés</h3>
        <ul class="pk-dashboard-list">
            <?php foreach ($rows as $row) :
                $equipment_title = get_the_title((int) ($row['equipment'] ?? 0));
            ?>
                <li>
                    <span class="pk-dashboard-project-title"><?php echo esc_html($equipment_title !== '' ? $equipment_title : 'Matériel'); ?></span>
                    <span class="pk-dashboard-tag">x<?php echo (int) ($row['quantity'] ?? 0); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
