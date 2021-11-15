<?php
/**
* Plugin Name: HT Job Listing
* Plugin URI: https://google.com
* Description: Job listing
* Version: 1.0.0
* Author: Hackertrial
* Author URI: https://google.com
* License: GPL2
*/

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if

// Initialize Everything
if ( file_exists( plugin_dir_path( __FILE__ ) . 'core-init.php' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'core-init.php' );
}

/*
* Plugin Activation and Deactivation hooks
*/
register_activation_hook( __FILE__ , 'htjl_plugin_activation' );
register_deactivation_hook( __FILE__ , 'htjl_plugin_deactivation' );