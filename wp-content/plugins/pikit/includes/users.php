<?php
/**
 * Gestion des utilisateurs et des rôles Pikit.
 */

defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Rôles
// ---------------------------------------------------------------------------

register_activation_hook( PIKIT_PLUGIN_FILE, 'pikit_register_roles' );

function pikit_register_roles(): void {
    add_role( 'pikit_student', 'Étudiant', [ 'read' => true ] );
    add_role( 'pikit_teacher', 'Enseignant', [ 'read' => true ] );
    add_role( 'pikit_staff',   'Personnel',  [ 'read' => true ] );
}

// ---------------------------------------------------------------------------
// Fonctions utilitaires
// ---------------------------------------------------------------------------

/**
 * Retourne tous les étudiants.
 *
 * @return WP_User[]
 */
function pikit_get_students(): array {
    return get_users( [ 'role' => 'pikit_student' ] );
}

/**
 * Retourne tous les enseignants.
 *
 * @return WP_User[]
 */
function pikit_get_teachers(): array {
    return get_users( [ 'role' => 'pikit_teacher' ] );
}

/**
 * Retourne la promotion d'un utilisateur (BUT1 / BUT2 / BUT3).
 *
 * @param int $user_id
 * @return string
 */
function pikit_get_user_promotion( int $user_id ): string {
    return (string) get_user_meta( $user_id, 'pikit_promotion', true );
}

/**
 * Retourne le groupe TD d'un utilisateur (A / B / C).
 *
 * @param int $user_id
 * @return string
 */
function pikit_get_user_group_td( int $user_id ): string {
    return (string) get_user_meta( $user_id, 'pikit_group_td', true );
}

/**
 * Retourne le groupe TP d'un utilisateur (1 → 6).
 *
 * @param int $user_id
 * @return string
 */
function pikit_get_user_group_tp( int $user_id ): string {
    return (string) get_user_meta( $user_id, 'pikit_group_tp', true );
}
