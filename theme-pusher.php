<?php

/**
 * Plugin Name: Theme Pusher
 * Plugin URI: https://intimation.uk
 * Description: Update a remote theme
 * Version: 0.1
 * Author: Paul Spence - Intimation
 * Author URI: https://intimation.uk
 * Licence: GPL
 */

/**
 * Include Files
 */
include_once plugin_dir_path( __FILE__ ) . 'classes/class_theme_pusher.php';

/**
 * Define Constants
 */
define( 'BASE_FILE', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'BASE_FOLDER', basename( dirname( __FILE__ ) ) );
define( 'ABS_FOLDER', dirname( __FILE__ ) );

/**
 * Load the plugin
 *
 * @since 0.1
 * @return theme_pusher
 */
function theme_pusher_loader()
{
    $instance = ThemePusher::instance( __FILE__, '1.0' );

    return $instance;
}

theme_pusher_loader();