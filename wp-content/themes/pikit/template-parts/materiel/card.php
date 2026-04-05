<?php
/**
 * Carte matériel de l'archive.
 *
 * @var array $args
 */

defined('ABSPATH') || exit;

$equipment = isset($args['equipment']) && $args['equipment'] instanceof WP_Post ? $args['equipment'] : null;
$availability = isset($args['availability']) && is_array($args['availability']) ? $args['availability'] : [];
$cover_image = isset($args['cover_image']) ? (string) $args['cover_image'] : '';

if (! $equipment instanceof WP_Post) {
    return;
}

$brand = (string) get_field('brand', $equipment->ID);
$model = (string) get_field('model', $equipment->ID);
$status_label = (int) ($availability['available'] ?? 0) > 0 ? 'Disponible' : 'Indisponible';
$status_class = (int) ($availability['available'] ?? 0) > 0 ? 'is-available' : 'is-unavailable';
$available = (int) ($availability['available'] ?? 0);
$total = (int) ($availability['total'] ?? 0);
$usable = (int) ($availability['usable'] ?? 0);
$reserved = (int) ($availability['reserved'] ?? 0);
$permalink = get_permalink($equipment);
?>
<article class="pk-archive-card">
    <a class="pk-archive-card-link" href="<?php echo esc_url($permalink); ?>" aria-label="Voir le matériel <?php echo esc_attr($equipment->post_title); ?>">
        <div class="pk-archive-card-media">
            <?php if ($cover_image !== '') : ?>
                <?php echo $cover_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php else : ?>
                <div class="pk-archive-card-placeholder" aria-hidden="true">
                    <span>Matériel</span>
                </div>
            <?php endif; ?>
        </div>

        <div class="pk-archive-card-body">
            <div class="pk-archive-card-head">
                <h2 class="pk-archive-card-title"><?php echo esc_html($equipment->post_title); ?></h2>
                <span class="pk-archive-card-badge <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
            </div>

            <?php if ($brand !== '' || $model !== '') : ?>
                <p class="pk-archive-card-meta">
                    <?php echo esc_html(trim($brand . ($brand !== '' && $model !== '' ? ' · ' : '') . $model)); ?>
                </p>
            <?php endif; ?>

            <p class="pk-archive-card-stock">
                <strong><?php echo esc_html((string) $available); ?></strong> disponible<?php echo $available > 1 ? 's' : ''; ?>
            </p>

            <p class="pk-archive-card-detail">
                Stock <?php echo esc_html((string) $total); ?> · Utilisables <?php echo esc_html((string) $usable); ?> · Réservés <?php echo esc_html((string) $reserved); ?>
            </p>
        </div>
    </a>
</article>