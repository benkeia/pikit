<?php
/**
 * Template Name: Mes réservations
 */

if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
    $login_url = function_exists('pikit_get_login_page_url')
        ? pikit_get_login_page_url(['redirect_to' => get_permalink()])
        : wp_login_url(get_permalink());

    wp_safe_redirect($login_url);
    exit;
}

$current_user_id = get_current_user_id();
$notice = isset($_GET['reservation_created']) ? 'reservation_created' : '';
$reservation_query = new WP_Query([
    'post_type' => 'reservations',
    'post_status' => 'publish',
    'author' => $current_user_id,
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
]);

function pikit_reservation_status_meta(string $status): array
{
    return match ($status) {
        'pending' => ['label' => 'En attente', 'class' => 'is-pending'],
        'approved' => ['label' => 'Approuvée', 'class' => 'is-approved'],
        'rejected' => ['label' => 'Refusée', 'class' => 'is-rejected'],
        'picked_up' => ['label' => 'Retrait effectué', 'class' => 'is-picked-up'],
        'returned' => ['label' => 'Rendue', 'class' => 'is-returned'],
        default => ['label' => 'Brouillon', 'class' => 'is-draft'],
    };
}

get_header();
?>
<main class="pk-reservations-page" aria-label="Mes réservations">
    <section class="pk-reservations-hero">
        <p class="pk-reservations-eyebrow">Espace personnel</p>
        <h1 class="pk-reservations-title">Mes réservations</h1>
        <p class="pk-reservations-subtitle">Retrouve ici toutes tes demandes, avec leur statut et les dates prévues.</p>
    </section>

    <?php if ($notice === 'reservation_created') : ?>
        <div class="pk-reservations-notice is-success">Réservation créée avec succès.</div>
    <?php endif; ?>

    <section class="pk-reservations-list">
        <?php if ($reservation_query->have_posts()) : ?>
            <?php while ($reservation_query->have_posts()) : $reservation_query->the_post();
                $status = (string) get_field('status', get_the_ID());
                $status_meta = pikit_reservation_status_meta($status);
                $pickup = (string) get_field('pickup_datetime', get_the_ID());
                $return = (string) get_field('return_datetime', get_the_ID());
                $rows = get_field('equipment_reserved', get_the_ID()) ?: [];
                $details_url = function_exists('pikit_get_dashboard_url')
                    ? pikit_get_dashboard_url(['tab' => 'demandes', 'reservation_id' => get_the_ID()])
                    : home_url('/dashboard/');
            ?>
                <article class="pk-reservation-card">
                    <div class="pk-reservation-card-head">
                        <div>
                            <p class="pk-reservation-card-kicker">Réservation #<?php echo (int) get_the_ID(); ?></p>
                            <h2 class="pk-reservation-card-title"><?php echo esc_html(get_the_title()); ?></h2>
                        </div>
                        <span class="pk-reservation-badge <?php echo esc_attr($status_meta['class']); ?>"><?php echo esc_html($status_meta['label']); ?></span>
                    </div>

                    <dl class="pk-reservation-meta">
                        <div>
                            <dt>Retrait</dt>
                            <dd><?php echo esc_html($pickup !== '' ? date_i18n('d/m/Y H:i', strtotime($pickup)) : '—'); ?></dd>
                        </div>
                        <div>
                            <dt>Retour</dt>
                            <dd><?php echo esc_html($return !== '' ? date_i18n('d/m/Y H:i', strtotime($return)) : '—'); ?></dd>
                        </div>
                        <div>
                            <dt>Matériels</dt>
                            <dd><?php echo esc_html((string) count($rows)); ?></dd>
                        </div>
                    </dl>

                    <div class="pk-reservation-actions">
                        <a class="pk-reservation-link" href="<?php echo esc_url($details_url); ?>">Voir le détail</a>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php else : ?>
            <div class="pk-reservations-empty">
                <p>Aucune réservation pour le moment.</p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php get_footer(); ?>
