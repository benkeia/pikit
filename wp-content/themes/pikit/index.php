<?php get_header(); ?>

<style>
    body {
        font-family: monospace;
        padding: 2rem;
        background: #f5f5f5;
    }

    h1 {
        font-size: 1.4rem;
        margin-bottom: 2rem;
    }

    h2 {
        font-size: 1.1rem;
        margin: 2rem 0 .5rem;
        border-bottom: 2px solid #333;
        padding-bottom: .25rem;
    }

    .equipment {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    .equipment h3 {
        margin: 0 0 .75rem;
        font-size: 1rem;
    }

    .period {
        margin-bottom: 1rem;
        padding: .75rem 1rem;
        background: #f9f9f9;
        border-left: 4px solid #999;
    }

    .period p {
        margin: .2rem 0;
        font-size: .9rem;
    }

    .label {
        color: #555;
    }

    .available {
        color: #1a7f37;
        font-weight: bold;
    }

    .zero {
        color: #cf222e;
        font-weight: bold;
    }

    .tag-period {
        font-weight: bold;
        font-size: .85rem;
        background: #e8f4fd;
        color: #0969da;
        padding: 2px 8px;
        border-radius: 4px;
        margin-bottom: .5rem;
        display: inline-block;
    }
</style>

<h1>🧪 Test de disponibilité — Pikit</h1>

<?php

$materiels = get_posts([
    'post_type'      => 'materiels',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
]);

// Deux périodes : une qui chevauche la réservation, une hors réservation
$periods = [
    [
        'label' => 'Pendant la réservation (16/03 → 20/03)',
        'start' => '2026-03-16 08:30:00',
        'end'   => '2026-03-20 16:00:00',
    ],
    [
        'label' => 'Hors réservation (01/04 → 05/04)',
        'start' => '2026-04-01 08:30:00',
        'end'   => '2026-04-05 16:00:00',
    ],
];

foreach ($materiels as $materiel) :

    $items = get_field('equipment_items', $materiel->ID) ?: [];

?>
    <div class="equipment">
        <h3>
            📦 <?php echo esc_html($materiel->post_title); ?>
            <small style="color:#666;font-weight:normal;">
                — ID <?php echo $materiel->ID; ?>
                | <?php echo count($items); ?> items
            </small>
        </h3>

        <p style="margin:.25rem 0 .75rem;font-size:.85rem;color:#555;">
            Items :
            <?php foreach ($items as $item) : ?>
                <span style="margin-right:.75rem;">
                    <?php echo esc_html($item['name']); ?>
                    <em>(<?php echo esc_html($item['status']); ?>)</em>
                </span>
            <?php endforeach; ?>
        </p>

        <?php foreach ($periods as $period) :
            $avail = pikit_get_equipment_available($materiel->ID, $period['start'], $period['end']);
            $css   = $avail['available'] > 0 ? 'available' : 'zero';
        ?>
            <div class="period">
                <span class="tag-period"><?php echo esc_html($period['label']); ?></span>
                <p><span class="label">Stock total :</span> <?php echo $avail['total']; ?></p>
                <p><span class="label">Utilisables :</span> <?php echo $avail['usable']; ?> <small>(hors maintenance / indisponible)</small></p>
                <p><span class="label">Déjà réservé :</span> <?php echo $avail['reserved']; ?></p>
                <p><span class="label">Disponible :</span>
                    <span class="<?php echo $css; ?>"><?php echo $avail['available']; ?></span>
                </p>
            </div>
        <?php endforeach; ?>

    </div>
<?php endforeach; ?>

<?php get_footer(); ?>