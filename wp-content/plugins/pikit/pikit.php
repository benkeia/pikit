<?php

/**
 * Plugin Name: Pikit
 * Description: Logique métier du système de réservation de matériel audiovisuel.
 * Version:     0.1.0
 * Author:      Baptiste
 */

defined('ABSPATH') || exit;

define( 'PIKIT_PLUGIN_FILE', __FILE__ );

require_once __DIR__ . '/includes/post-types.php';
require_once __DIR__ . '/includes/users.php';
require_once __DIR__ . '/includes/equipment.php';
require_once __DIR__ . '/includes/availability.php';
require_once __DIR__ . '/includes/reservations.php';
require_once __DIR__ . '/includes/validation.php';
