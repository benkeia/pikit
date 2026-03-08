<?php

/**
 * Fonctions utilitaires liées au matériel.
 */

defined('ABSPATH') || exit;

/**
 * Retourne tous les équipements publiés.
 *
 * @return WP_Post[]
 */
function pikit_get_all_equipment(): array
{
    return get_posts([
        'post_type'      => 'materiels',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
}