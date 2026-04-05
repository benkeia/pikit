<?php
/**
 * Template Name: Dashboard Étudiant
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
$tab = isset($_GET['tab']) ? sanitize_key((string) wp_unslash($_GET['tab'])) : 'projets';
$allowed_tabs = ['projets', 'catalogue', 'demandes'];

if (!in_array($tab, $allowed_tabs, true)) {
    $tab = 'projets';
}

$project_id = isset($_GET['project_id']) ? (int) wp_unslash($_GET['project_id']) : 0;
$reservation_id = isset($_GET['reservation_id']) ? (int) wp_unslash($_GET['reservation_id']) : 0;
$notice = isset($_GET['notice']) ? sanitize_key((string) wp_unslash($_GET['notice'])) : '';
$notice_message = isset($_GET['notice_message']) ? sanitize_text_field((string) wp_unslash($_GET['notice_message'])) : '';

get_header();
?>
<main class="pk-dashboard" aria-label="Dashboard étudiant">
    <div class="pk-dashboard-shell">
        <div class="pk-dashboard-mobile-topbar">
            <button
                type="button"
                class="pk-dashboard-menu-btn"
                data-pikit-sidebar-toggle
                aria-expanded="false"
                aria-controls="pk-dashboard-sidebar"
            >
                Menu dashboard
            </button>
        </div>

        <?php
        get_template_part('template-parts/dashboard/sidebar', null, [
            'tab' => $tab,
        ]);
        ?>

        <section class="pk-dashboard-content" aria-live="polite">
            <?php if ($notice !== '') : ?>
                <div class="pk-dashboard-notice <?php echo str_contains($notice, 'error') || $notice === 'invalid_nonce' ? 'is-error' : 'is-success'; ?>">
                    <?php
                    if ($notice_message !== '') {
                        echo esc_html(rawurldecode($notice_message));
                    } else {
                        echo esc_html($notice);
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php
            if ($tab === 'catalogue') {
                get_template_part('template-parts/dashboard/tab-catalogue', null, [
                    'current_user_id' => $current_user_id,
                ]);
            } elseif ($tab === 'demandes') {
                get_template_part('template-parts/dashboard/tab-demande', null, [
                    'current_user_id' => $current_user_id,
                    'reservation_id' => $reservation_id,
                ]);
            } elseif ($project_id > 0) {
                get_template_part('template-parts/dashboard/tab-projet-detail', null, [
                    'current_user_id' => $current_user_id,
                    'project_id' => $project_id,
                ]);
            } else {
                get_template_part('template-parts/dashboard/tab-projets', null, [
                    'current_user_id' => $current_user_id,
                ]);
            }
            ?>
        </section>
    </div>
</main>

<?php get_footer(); ?>
