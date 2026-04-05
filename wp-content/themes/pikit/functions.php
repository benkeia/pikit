<?php

/**
 * Thème Pikit — affichage uniquement.
 * La logique métier est dans le plugin wp-content/plugins/pikit.
 */

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', 'pikit_theme_enqueue_assets');

/**
 * Charge les assets du theme et des templates specifiques.
 */
function pikit_theme_enqueue_assets(): void
{
	wp_enqueue_style('pikit-theme-style', get_stylesheet_uri(), [], '1.0.0');

	wp_enqueue_style(
		'pikit-site-header-style',
		get_template_directory_uri() . '/assets/css/site-header.css',
		['pikit-theme-style'],
		'1.0.0'
	);

	wp_enqueue_script(
		'pikit-site-header-script',
		get_template_directory_uri() . '/assets/js/site-header.js',
		[],
		'1.0.0',
		true
	);

	if (is_page('connexion') || is_page_template('page-connexion.php')) {
		wp_enqueue_style(
			'pikit-login-style',
			get_template_directory_uri() . '/assets/css/page-connexion.css',
			['pikit-theme-style'],
			'1.0.0'
		);
	}

	if (is_page_template('page-accueil-pikit.php')) {
		wp_enqueue_style(
			'pikit-home-style',
			get_template_directory_uri() . '/assets/css/page-accueil-pikit.css',
			['pikit-theme-style'],
			'1.0.0'
		);

		wp_enqueue_script(
			'pikit-home-script',
			get_template_directory_uri() . '/assets/js/page-accueil-pikit.js',
			[],
			'1.0.0',
			true
		);
	}

	if (is_post_type_archive('materiels')) {
		wp_enqueue_style(
			'pikit-archive-materiels-style',
			get_template_directory_uri() . '/assets/css/archive-materiels.css',
			['pikit-theme-style'],
			'1.0.0'
		);

		wp_enqueue_script(
			'pikit-archive-materiels-script',
			get_template_directory_uri() . '/assets/js/archive-materiels.js',
			[],
			'1.0.0',
			true
		);
	}

	if (is_page_template('page-dashboard.php')) {
		wp_enqueue_style(
			'pikit-dashboard-style',
			get_template_directory_uri() . '/assets/css/page-dashboard.css',
			['pikit-theme-style'],
			'1.0.0'
		);

		wp_enqueue_script(
			'pikit-dashboard-script',
			get_template_directory_uri() . '/assets/js/page-dashboard.js',
			[],
			'1.0.0',
			true
		);
	}
}
