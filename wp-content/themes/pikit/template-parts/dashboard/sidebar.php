<?php
/**
 * Sidebar du dashboard étudiant.
 *
 * @var array $args
 */

if (!defined('ABSPATH')) exit;

$tab = isset($args['tab']) ? sanitize_key((string) $args['tab']) : 'projets';
$dashboard_url = function_exists('pikit_get_dashboard_url') ? pikit_get_dashboard_url() : home_url('/dashboard/');
?>
<aside id="pk-dashboard-sidebar" class="pk-dashboard-sidebar" aria-label="Navigation dashboard">
    <div class="pk-dashboard-brand">
        <span class="pk-dashboard-brand-dot" aria-hidden="true"></span>
        <span>Pikit</span>
    </div>

    <nav class="pk-dashboard-nav" aria-label="Sections">
        <a class="pk-dashboard-nav-link <?php echo $tab === 'projets' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['tab' => 'projets'], $dashboard_url)); ?>">Projets</a>
        <a class="pk-dashboard-nav-link <?php echo $tab === 'demandes' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['tab' => 'demandes'], $dashboard_url)); ?>">Demandes</a>
        <a class="pk-dashboard-nav-link <?php echo $tab === 'catalogue' ? 'is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg(['tab' => 'catalogue'], $dashboard_url)); ?>">Catalogue</a>
    </nav>
</aside>
