<?php
/**
 * Tab détail d'un projet.
 *
 * @var array $args
 */

if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) return;

$current_user_id = isset($args['current_user_id']) ? (int) $args['current_user_id'] : get_current_user_id();
$project_id = isset($args['project_id']) ? (int) $args['project_id'] : 0;
$project = $project_id > 0 ? get_post($project_id) : null;
$dashboard_url = function_exists('pikit_get_dashboard_url') ? pikit_get_dashboard_url() : home_url('/dashboard/');

if (!($project instanceof WP_Post) || $project->post_type !== 'projet' || (int) $project->post_author !== $current_user_id) {
    echo '<div class="pk-dashboard-card"><p>Projet introuvable ou accès refusé.</p></div>';
    return;
}

$reservations = function_exists('pikit_get_project_reservations') ? pikit_get_project_reservations($project->ID) : [];
$equipments = function_exists('pikit_get_all_equipment') ? pikit_get_all_equipment() : [];
$project_description = (string) get_field('description', $project->ID);
?>
<header class="pk-dashboard-tab-header">
    <p class="pk-dashboard-eyebrow">Projet</p>
    <h1 class="pk-dashboard-title"><?php echo esc_html($project->post_title); ?></h1>
    <?php if ($project_description !== '') : ?>
        <p class="pk-dashboard-subtitle"><?php echo esc_html($project_description); ?></p>
    <?php endif; ?>
</header>

<section class="pk-dashboard-card">
    <h2>Créer une demande (brouillon)</h2>
    <form method="post" class="pk-dashboard-form" data-pikit-rows-form>
        <?php wp_nonce_field('pikit_create_reservation', 'pikit_nonce'); ?>
        <input type="hidden" name="pikit_action" value="create_reservation_draft">
        <input type="hidden" name="project_id" value="<?php echo (int) $project->ID; ?>">

        <div class="pk-dashboard-grid-2">
            <div>
                <label for="pickup_datetime">Retrait</label>
                <input id="pickup_datetime" name="pickup_datetime" type="datetime-local" required>
            </div>
            <div>
                <label for="return_datetime">Retour</label>
                <input id="return_datetime" name="return_datetime" type="datetime-local" required>
            </div>
        </div>

        <div class="pk-dashboard-items" data-pikit-rows>
            <h3>Matériels</h3>
            <div class="pk-dashboard-item-row" data-pikit-row>
                <select name="equipment_ids[]" required>
                    <option value="">Choisir un matériel</option>
                    <?php foreach ($equipments as $equipment) : ?>
                        <option value="<?php echo (int) $equipment->ID; ?>"><?php echo esc_html($equipment->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="quantities[]" min="1" value="1" required>
                <input type="text" name="notes[]" placeholder="Notes (optionnel)">
                <button type="button" data-pikit-remove-row>Retirer</button>
            </div>
        </div>

        <div class="pk-dashboard-actions-row">
            <button type="button" class="is-secondary" data-pikit-add-row>Ajouter une ligne</button>
            <button type="submit">Créer le brouillon</button>
        </div>
    </form>
</section>

<section class="pk-dashboard-card">
    <h2>Demandes de ce projet</h2>

    <?php if (empty($reservations)) : ?>
        <p>Aucune demande pour ce projet.</p>
    <?php else : ?>
        <ul class="pk-dashboard-list">
            <?php foreach ($reservations as $reservation) :
                if (!($reservation instanceof WP_Post) || (int) $reservation->post_author !== $current_user_id) {
                    continue;
                }

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
