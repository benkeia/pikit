<?php
/**
 * Header principal du site Pikit.
 */

defined('ABSPATH') || exit;

$nav_items = function_exists('pikit_get_header_navigation_items') ? pikit_get_header_navigation_items() : [];
$current_role_label = function_exists('pikit_get_current_user_role_label') ? pikit_get_current_user_role_label() : '';
$dashboard_url = function_exists('pikit_get_dashboard_url') ? pikit_get_dashboard_url() : home_url('/dashboard/');
$logout_url = is_user_logged_in()
    ? add_query_arg(
        [
            'pikit_action' => 'logout',
            '_wpnonce' => wp_create_nonce('pikit_logout'),
        ],
        function_exists('pikit_get_login_page_url') ? pikit_get_login_page_url() : home_url('/connexion/')
    )
    : '';
?>
<header class="pk-header">
    <div class="pk-header-inner">
        <div class="pk-topbar">
            <a class="pk-logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Accueil Pikit">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 12 Q8 6 12 12 Q16 18 20 12" stroke="#F2764C" stroke-width="3" stroke-linecap="round" />
                </svg>
                Pikit
            </a>

            <button
                class="pk-header-menu-btn"
                type="button"
                data-pikit-header-toggle
                aria-expanded="false"
                aria-controls="pikit-site-nav"
            >
                Menu
            </button>

            <nav class="pk-topbar-nav" id="pikit-site-nav" aria-label="Navigation principale">
                <?php foreach ($nav_items as $item) : ?>
                    <a href="<?php echo esc_url((string) ($item['url'] ?? '')); ?>"><?php echo esc_html((string) ($item['label'] ?? '')); ?></a>
                <?php endforeach; ?>
            </nav>

            <div class="pk-topbar-actions">
                <?php if (is_user_logged_in()) : ?>
                    <span class="pk-topbar-role" aria-label="Profil actuel"><?php echo esc_html($current_role_label); ?></span>
                    <a class="pk-topbar-login" href="<?php echo esc_url($dashboard_url); ?>">Mon espace</a>
                    <a class="pk-topbar-cta" href="<?php echo esc_url($logout_url); ?>">Déconnexion</a>
                <?php else : ?>
                    <a class="pk-topbar-login" href="<?php echo esc_url(function_exists('pikit_get_login_page_url') ? pikit_get_login_page_url() : home_url('/connexion/')); ?>">Connexion</a>
                    <a class="pk-topbar-cta" href="<?php echo esc_url(home_url('/inscription/')); ?>">Créer un compte gratuit</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
