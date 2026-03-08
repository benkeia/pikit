<?php

/**
 * Catalogue du matériel — archive-materiels.php
 *
 * Affiche tous les équipements avec leur disponibilité pour aujourd'hui.
 * Logique métier : plugin pikit (includes/availability.php, includes/equipment.php)
 */

get_header();

$equipments = pikit_get_all_equipment();

$start = date('Y-m-d 00:00:00');
$end   = date('Y-m-d 23:59:59');

?>
<main>
    <h1>Catalogue matériel</h1>

    <?php if (empty($equipments)) : ?>

        <p>Aucun matériel disponible.</p>

    <?php else : ?>

        <?php foreach ($equipments as $equipment) :

            $avail = pikit_get_equipment_available($equipment->ID, $start, $end);

        ?>
            <article>
                <h2><?php echo esc_html($equipment->post_title); ?></h2>
                <p>
                    Disponible : <strong><?php echo $avail['available']; ?></strong>
                    <small>(stock : <?php echo $avail['total']; ?> | utilisables : <?php echo $avail['usable']; ?> | réservés : <?php echo $avail['reserved']; ?>)</small>
                </p>
            </article>
        <?php endforeach; ?>

    <?php endif; ?>
</main>

<?php get_footer(); ?>