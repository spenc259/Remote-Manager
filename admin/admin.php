<?php 
/**
 * Admin Setup
 * @since 0.1
 */

include_once plugin_dir_path( __FILE__ ) . '/classes/class-admin.php';
include_once plugin_dir_path( __FILE__ ) . '/classes/class-list-table.php';
include_once plugin_dir_path( __FILE__ ) . '/classes/class-crud.php';

$admin_settings = new RemoteManagerAdmin(); // start admin