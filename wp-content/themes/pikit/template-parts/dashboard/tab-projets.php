<?php
/**
 * Tab Projets du dashboard.
 *
 * @var array $args
 */

if (!defined('ABSPATH')) exit;
if (!is_user_logged_in()) return;

$current_user_id = isset($args['current_user_id']) ? (int) $args['current_user_id'] : get_current_user_id();
$projects = function_exists('pikit_get_user_projects') ? pikit_get_user_projects($current_user_id) : [];
$dashboard_url = function_exists('pikit_get_dashboard_url') ? pikit_get_dashboard_url() : home_url('/dashboard/');
?>
<header class="pk-dashboard-tab-header">
    <p class="pk-dashboard-eyebrow">Dashboard étudiant</p>
    <h1 class="pk-dashboard-title">Mes projets</h1>
</header>

<section class="pk-dashboard-card">
    <h2>Créer un projet</h2>
    <form method="post" class="pk-dashboard-form">
        <?php wp_nonce_field('pikit_create_project', 'pikit_nonce'); ?>
        <input type="hidden" name="pikit_action" value="create_project">

        <label for="project_title">Nom du projet</label>
        <input id="project_title" name="project_title" type="text" required>

        <label for="project_description">Description</label>
        <textarea id="project_description" name="project_description" rows="4"></textarea>

        <label for="project_status">Statut</label>
        <select id="project_status" name="project_status">
            <option value="draft">Brouillon</option>
            <option value="not_started">Non commencé</option>
            <option value="in_progress">En cours</option>
            <option value="completed">Terminé</option>
        </select>

        <button type="submit">Créer le projet</button>
    </form>
</section>

<section class="pk-dashboard-card">
    <h2>Projets existants</h2>

    <?php if (empty($projects)) : ?>
        <p>Aucun projet pour le moment.</p>
    <?php else : ?>
        <ul class="pk-dashboard-list">
            <?php foreach ($projects as $project) :
                $project_status = (string) get_field('status', $project->ID);
                $project_link = add_query_arg([
                    'tab' => 'projets',
                    'project_id' => (int) $project->ID,
                ], $dashboard_url);
            ?>
                <li>
                    <a href="<?php echo esc_url($project_link); ?>" class="pk-dashboard-project-link">
                        <span class="pk-dashboard-project-title"><?php echo esc_html($project->post_title); ?></span>
                        <span class="pk-dashboard-tag"><?php echo esc_html($project_status !== '' ? $project_status : 'draft'); ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
