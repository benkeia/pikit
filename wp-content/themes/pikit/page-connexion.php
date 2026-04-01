<?php
/**
 * Template de la page connexion.
 * Affichage uniquement: la logique metier reste dans le plugin Pikit.
 */

defined('ABSPATH') || exit;

$error_message  = function_exists('pikit_get_login_error_message') ? pikit_get_login_error_message() : '';
$notice_message = function_exists('pikit_get_login_notice_message') ? pikit_get_login_notice_message() : '';
$redirect_to    = isset($_GET['redirect_to']) ? wp_unslash((string) $_GET['redirect_to']) : '';
$redirect_to    = wp_validate_redirect($redirect_to, '');

$logout_url = add_query_arg(
    [
        'pikit_action' => 'logout',
        '_wpnonce' => wp_create_nonce('pikit_logout'),
    ],
    home_url('/connexion/')
);

$is_logged_in = is_user_logged_in();
$current_user = wp_get_current_user();

get_header();
?>
<main class="pikit-login-layout">
    <?php get_template_part('template-parts/login/page', 'header'); ?>

    <section class="pk-main" aria-label="Connexion">
        <?php
        get_template_part(
            'template-parts/login/auth',
            'card',
            [
                'error_message' => $error_message,
                'notice_message' => $notice_message,
                'redirect_to' => $redirect_to,
                'is_logged_in' => $is_logged_in,
                'current_user' => $current_user,
                'logout_url' => $logout_url,
            ]
        );
        ?>
    </section>

    <?php get_template_part('template-parts/login/page', 'footer'); ?>
</main>

<?php get_footer(); ?>
