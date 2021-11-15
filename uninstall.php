<?php 

// If this file is called directly, abort. //
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();

/****************************
* Delete custom created table
*****************************/
global $wpdb;

$htjl_job_listing = $wpdb->prefix . 'htjl_job_listing';
$sql = "DROP TABLE IF EXISTS $htjl_job_listing";
$wpdb->query($sql);

?>