<?php

/**
 * Thème Pikit — affichage uniquement.
 * La logique métier est dans le plugin wp-content/plugins/pikit.
 */

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', 'pikit_theme_enqueue_styles');

/**
 * Charge les styles du theme, puis les styles de la page connexion.
 */
function pikit_theme_enqueue_styles(): void
{
	wp_enqueue_style('pikit-theme-style', get_stylesheet_uri(), [], '1.0.0');

	if (is_page('connexion') || is_page_template('page-connexion.php')) {
		wp_enqueue_style(
			'pikit-login-style',
			get_template_directory_uri() . '/assets/css/page-connexion.css',
			['pikit-theme-style'],
			'1.0.0'
		);
	}
}
