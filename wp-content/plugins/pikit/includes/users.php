<?php

/**
 * Gestion des utilisateurs et des rôles Pikit.
 */

defined('ABSPATH') || exit;

// ---------------------------------------------------------------------------
// Rôles
// ---------------------------------------------------------------------------

register_activation_hook(PIKIT_PLUGIN_FILE, 'pikit_register_roles');
register_activation_hook(PIKIT_PLUGIN_FILE, 'pikit_ensure_login_page');

function pikit_register_roles(): void
{
    add_role('pikit_student', 'Étudiant', ['read' => true]);
    add_role('pikit_teacher', 'Enseignant', ['read' => true]);
    add_role('pikit_staff',   'Personnel',  ['read' => true]);
}

/**
 * Crée la page de connexion front si elle n'existe pas.
 */
function pikit_ensure_login_page(): void
{
    if (get_page_by_path('connexion')) {
        return;
    }

    wp_insert_post(
        [
            'post_title'   => 'Connexion',
            'post_name'    => 'connexion',
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]
    );
}

add_filter('login_url', 'pikit_filter_login_url', 10, 3);
add_action('init', 'pikit_maybe_ensure_login_page');
add_action('init', 'pikit_handle_login_submission');
add_action('init', 'pikit_handle_logout_request');

/**
 * Crée la page de connexion si elle manque, sans nécessiter de réactiver le plugin.
 */
function pikit_maybe_ensure_login_page(): void
{
    if (get_page_by_path('connexion')) {
        return;
    }

    pikit_ensure_login_page();
}

/**
 * Redirige les URL de connexion WordPress vers la page front.
 *
 * @param string $login_url
 * @param string $redirect
 * @param bool   $force_reauth
 * @return string
 */
function pikit_filter_login_url(string $login_url, string $redirect, bool $force_reauth): string
{
    $args = [];

    if ($redirect !== '') {
        $args['redirect_to'] = $redirect;
    }

    if ($force_reauth) {
        $args['reauth'] = '1';
    }

    return pikit_get_login_page_url($args);
}

/**
 * URL de la page de connexion front.
 *
 * @param array<string, string> $args
 * @return string
 */
function pikit_get_login_page_url(array $args = []): string
{
    $url = home_url('/connexion/');

    return ! empty($args) ? add_query_arg($args, $url) : $url;
}

/**
 * Traite la soumission du formulaire de connexion front.
 */
function pikit_handle_login_submission(): void
{
    if (strtoupper((string) $_SERVER['REQUEST_METHOD']) !== 'POST') {
        return;
    }

    $action = isset($_POST['pikit_action']) ? sanitize_key(wp_unslash((string) $_POST['pikit_action'])) : '';
    if ($action !== 'login') {
        return;
    }

    $nonce = isset($_POST['pikit_login_nonce']) ? wp_unslash((string) $_POST['pikit_login_nonce']) : '';
    if (! wp_verify_nonce($nonce, 'pikit_login_submit')) {
        wp_safe_redirect(pikit_get_login_page_url(['login_error' => 'invalid_nonce']));
        exit;
    }

    $username = isset($_POST['log']) ? sanitize_text_field(wp_unslash((string) $_POST['log'])) : '';
    $password = isset($_POST['pwd']) ? wp_unslash((string) $_POST['pwd']) : '';
    $remember = ! empty($_POST['rememberme']);
    $redirect = isset($_POST['redirect_to']) ? wp_unslash((string) $_POST['redirect_to']) : '';

    if ($username === '' || $password === '') {
        $args = ['login_error' => 'empty_fields'];
        if ($redirect !== '') {
            $args['redirect_to'] = $redirect;
        }

        wp_safe_redirect(pikit_get_login_page_url($args));
        exit;
    }

    // Accepte aussi une adresse e-mail dans le champ de connexion.
    if (is_email($username)) {
        $user_from_email = get_user_by('email', $username);
        if ($user_from_email instanceof WP_User) {
            $username = $user_from_email->user_login;
        }
    }

    $creds = [
        'user_login'    => $username,
        'user_password' => $password,
        'remember'      => $remember,
    ];

    $user = wp_signon($creds, is_ssl());

    if (is_wp_error($user)) {
        $args = ['login_error' => 'invalid_credentials'];
        if ($redirect !== '') {
            $args['redirect_to'] = $redirect;
        }

        wp_safe_redirect(pikit_get_login_page_url($args));
        exit;
    }

    $redirect_url = wp_validate_redirect($redirect, '');
    if ($redirect_url === '') {
        $redirect_url = pikit_get_post_login_redirect($user);
    }

    wp_safe_redirect($redirect_url);
    exit;
}

/**
 * Déconnecte l'utilisateur via l'interface front.
 */
function pikit_handle_logout_request(): void
{
    if (! isset($_GET['pikit_action'])) {
        return;
    }

    $action = sanitize_key(wp_unslash((string) $_GET['pikit_action']));
    if ($action !== 'logout') {
        return;
    }

    if (! is_user_logged_in()) {
        wp_safe_redirect(pikit_get_login_page_url());
        exit;
    }

    $nonce = isset($_GET['_wpnonce']) ? wp_unslash((string) $_GET['_wpnonce']) : '';
    if (! wp_verify_nonce($nonce, 'pikit_logout')) {
        wp_safe_redirect(pikit_get_login_page_url(['login_error' => 'invalid_nonce']));
        exit;
    }

    wp_logout();

    wp_safe_redirect(pikit_get_login_page_url(['logged_out' => '1']));
    exit;
}

/**
 * Retourne l'URL de redirection après connexion selon le rôle.
 *
 * @param WP_User $user
 * @return string
 */
function pikit_get_post_login_redirect(WP_User $user): string
{
    if (in_array('administrator', (array) $user->roles, true)) {
        return admin_url();
    }

    if (array_intersect(['pikit_student', 'pikit_teacher', 'pikit_staff'], (array) $user->roles)) {
        return home_url('/materiels/');
    }

    return home_url('/');
}

/**
 * Message d'erreur lisible pour le formulaire front.
 *
 * @return string
 */
function pikit_get_login_error_message(): string
{
    if (! isset($_GET['login_error'])) {
        return '';
    }

    $code = sanitize_key(wp_unslash((string) $_GET['login_error']));

    if ($code === 'empty_fields') {
        return 'Merci de renseigner votre identifiant et votre mot de passe.';
    }

    if ($code === 'invalid_credentials') {
        return 'Identifiants incorrects. Vérifiez vos informations.';
    }

    if ($code === 'invalid_nonce') {
        return 'La session a expiré. Merci de réessayer.';
    }

    return 'Une erreur est survenue lors de la connexion.';
}

/**
 * Message d'information du formulaire front.
 *
 * @return string
 */
function pikit_get_login_notice_message(): string
{
    if (isset($_GET['logged_out']) && sanitize_key(wp_unslash((string) $_GET['logged_out'])) === '1') {
        return 'Vous êtes maintenant déconnecté.';
    }

    return '';
}

// ---------------------------------------------------------------------------
// Fonctions utilitaires
// ---------------------------------------------------------------------------

/**
 * Retourne tous les étudiants.
 *
 * @return WP_User[]
 */
function pikit_get_students(): array
{
    return get_users(['role' => 'pikit_student']);
}

/**
 * Retourne tous les enseignants.
 *
 * @return WP_User[]
 */
function pikit_get_teachers(): array
{
    return get_users(['role' => 'pikit_teacher']);
}

/**
 * Retourne la promotion d'un utilisateur (BUT1 / BUT2 / BUT3).
 *
 * @param int $user_id
 * @return string
 */
function pikit_get_user_promotion(int $user_id): string
{
    return (string) get_user_meta($user_id, 'pikit_promotion', true);
}

/**
 * Retourne le groupe TD d'un utilisateur (A / B / C).
 *
 * @param int $user_id
 * @return string
 */
function pikit_get_user_group_td(int $user_id): string
{
    return (string) get_user_meta($user_id, 'pikit_group_td', true);
}

/**
 * Retourne le groupe TP d'un utilisateur (1 → 6).
 *
 * @param int $user_id
 * @return string
 */
function pikit_get_user_group_tp(int $user_id): string
{
    return (string) get_user_meta($user_id, 'pikit_group_tp', true);
}

/**
 * Retourne le role principal courant de l'utilisateur.
 *
 * @return string
 */
function pikit_get_current_user_role(): string
{
    $user = wp_get_current_user();

    if (!($user instanceof WP_User) || empty($user->roles)) {
        return '';
    }

    $roles = (array) $user->roles;

    foreach (['administrator', 'pikit_teacher', 'pikit_student', 'pikit_staff'] as $role) {
        if (in_array($role, $roles, true)) {
            return $role;
        }
    }

    return (string) reset($roles);
}

/**
 * Retourne un libelle lisible pour le role courant.
 *
 * @return string
 */
function pikit_get_current_user_role_label(): string
{
    $role = pikit_get_current_user_role();

    return match ($role) {
        'administrator' => 'Admin',
        'pikit_teacher' => 'Prof',
        'pikit_staff' => 'Technicien',
        'pikit_student' => 'Étudiant',
        default => $role !== '' ? ucfirst(str_replace('_', ' ', $role)) : 'Invité',
    };
}

/**
 * Retourne les liens principaux du header selon le role.
 *
 * @return array<int, array{label: string, url: string}>
 */
function pikit_get_header_navigation_items(): array
{
    if (!is_user_logged_in()) {
        return [
            [
                'label' => 'Catalogue',
                'url' => home_url('/materiels/'),
            ],
            [
                'label' => 'Fonctionnalités',
                'url' => home_url('/fonctionnalites/'),
            ],
            [
                'label' => 'Connexion',
                'url' => pikit_get_login_page_url(),
            ],
        ];
    }

    $role = pikit_get_current_user_role();

    $common = [
        [
            'label' => 'Accueil',
            'url' => home_url('/'),
        ],
        [
            'label' => 'Catalogue',
            'url' => home_url('/materiels/'),
        ],
        [
            'label' => 'Dashboard',
            'url' => function_exists('pikit_get_dashboard_url') ? pikit_get_dashboard_url() : home_url('/dashboard/'),
        ],
    ];

    if ($role === 'administrator') {
        return array_merge($common, [
            [
                'label' => 'Admin',
                'url' => admin_url(),
            ],
        ]);
    }

    if ($role === 'pikit_teacher') {
        return array_merge($common, [
            [
                'label' => 'Demandes',
                'url' => function_exists('pikit_get_dashboard_url') ? pikit_get_dashboard_url(['tab' => 'demandes']) : home_url('/dashboard/?tab=demandes'),
            ],
        ]);
    }

    if ($role === 'pikit_staff') {
        return array_merge($common, [
            [
                'label' => 'Gestion stock',
                'url' => function_exists('pikit_get_dashboard_url') ? pikit_get_dashboard_url(['tab' => 'catalogue']) : home_url('/dashboard/?tab=catalogue'),
            ],
        ]);
    }

    return array_merge($common, [
        [
            'label' => 'Mes projets',
            'url' => function_exists('pikit_get_dashboard_url') ? pikit_get_dashboard_url(['tab' => 'projets']) : home_url('/dashboard/?tab=projets'),
        ],
    ]);
}
