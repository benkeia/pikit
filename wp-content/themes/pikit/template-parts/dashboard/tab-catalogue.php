<?php
/**
 * Tab catalogue avec disponibilité sur 7 jours.
 *
 * @var array $args
 */

if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) return;

$equipments = function_exists('pikit_get_all_equipment') ? pikit_get_all_equipment() : [];
?>
<header class="pk-dashboard-tab-header">
    <p class="pk-dashboard-eyebrow">Catalogue</p>
    <h1 class="pk-dashboard-title">Disponibilité à 7 jours</h1>
</header>

<section class="pk-dashboard-card">
    <?php if (empty($equipments)) : ?>
        <p>Aucun matériel publié.</p>
    <?php else : ?>
        <div class="pk-catalogue-grid">
            <?php foreach ($equipments as $equipment) :
                $next_days = function_exists('pikit_get_equipment_next_days_availability')
                    ? pikit_get_equipment_next_days_availability((int) $equipment->ID, 7)
                    : [];
                $min_available = null;

                foreach ($next_days as $day) {
                    $value = (int) ($day['available'] ?? 0);
                    $min_available = $min_available === null ? $value : min($min_available, $value);
                }

                $brand = (string) get_field('brand', $equipment->ID);
                $model = (string) get_field('model', $equipment->ID);
            ?>
                <article class="pk-catalogue-card">
                    <h2><?php echo esc_html($equipment->post_title); ?></h2>
                    <?php if ($brand !== '' || $model !== '') : ?>
                        <p class="pk-catalogue-meta"><?php echo esc_html(trim($brand . ($brand !== '' && $model !== '' ? ' · ' : '') . $model)); ?></p>
                    <?php endif; ?>
                    <p class="pk-catalogue-highlight">
                        Min dispo (7j) : <strong><?php echo esc_html((string) ($min_available ?? 0)); ?></strong>
                    </p>
                    <ul>
                        <?php foreach ($next_days as $day) : ?>
                            <li>
                                <span><?php echo esc_html((string) $day['date']); ?></span>
                                <strong><?php echo esc_html((string) $day['available']); ?>/<?php echo esc_html((string) $day['total']); ?></strong>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
