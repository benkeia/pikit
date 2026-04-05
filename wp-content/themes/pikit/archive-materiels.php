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
<main class="pk-archive-materiels" aria-label="Catalogue matériel">
    <section class="pk-archive-hero">
        <p class="pk-archive-eyebrow">Catalogue matériel</p>
        <h1 class="pk-archive-title">Choisis ton matériel</h1>
        <p class="pk-archive-subtitle">Des cartes simples, lisibles, et pensées pour pointer vers un futur single matériel.</p>
    </section>

    <?php if (empty($equipments)) : ?>

        <div class="pk-archive-empty">
            <p>Aucun matériel disponible.</p>
        </div>

    <?php else : ?>

        <section class="pk-archive-grid" aria-label="Liste du matériel">

        <?php foreach ($equipments as $equipment) :

            $avail = pikit_get_equipment_available($equipment->ID, $start, $end);
            $cover_image = '';
            $gallery = get_field('gallery', $equipment->ID) ?: [];

            if (! empty($gallery) && is_array($gallery)) {
                $first_image = $gallery[0] ?? null;
                if (is_array($first_image) && ! empty($first_image['ID'])) {
                    $cover_image = wp_get_attachment_image((int) $first_image['ID'], 'medium_large', false, ['class' => 'pk-archive-card-image']);
                }
            }

        ?>
            <?php
            get_template_part(
                'template-parts/materiel/card',
                null,
                [
                    'equipment' => $equipment,
                    'availability' => $avail,
                    'cover_image' => $cover_image,
                ]
            );
            ?>
        <?php endforeach; ?>

        </section>

    <?php endif; ?>
</main>

<?php get_footer(); ?>