<?php

/**
 * Plugin Name: Remote Manager
 * Plugin URI: https://intimation.uk
 * Description: Manage a remote sites theme and plugins
 * Version: 0.2
 * Author: Paul Spence - Intimation
 * Author URI: https://intimation.uk
 * Licence: GPL
 */

/**
 * Define Constants
 * @since 0.1
 */
define( 'BASE_FILE', basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) );
define( 'BASE_FOLDER', basename( dirname( __FILE__ ) ) );
define( 'ABS_FOLDER', dirname( __FILE__ ) );

/**
 * WP Requires
 * @since 0.1
 */
require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

/**
 * Include Files
 * @since 0.1
 */
include_once plugin_dir_path( __FILE__ ) . 'classes/class_remote_manager.php';
include_once plugin_dir_path( __FILE__ ) . '/admin/admin.php';
include_once plugin_dir_path( __FILE__ ) . 'classes/theme-pusher/class-theme-pusher.php';
include_once plugin_dir_path( __FILE__ ) . 'classes/plugin-updater/class-plugin-updater.php';


/**
 * Load the plugin
 * @since 0.1
 * @return remote_manager
 */
function remote_manager_loader()
{
    $instance = RemoteManager::instance( __FILE__, '0.1' );

    return $instance;
}

remote_manager_loader();