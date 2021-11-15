<?php 

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if

global $wpdb;

$img_url = $_POST['img_url'];
$group_id = $_POST['group_id'];
$htjl_job_listing = $wpdb->prefix . 'htjl_job_listing';

$checkIfExists = $wpdb->get_var("SELECT * FROM $htjl_job_listing WHERE group_id = '$group_id'");
if ($checkIfExists == NULL) {

	$wpdb->insert($htjl_job_listing, array(
		'group_id'		=> $group_id,
		'img_url' 		=> $img_url
	), array(
		'%s', '%s'
	));

}else{
	
	$data_array = array(
	    'img_url' 		=> $img_url,
    );
    $data_where = array('group_id' => $group_id);
	$wpdb->update($htjl_job_listing, $data_array, $data_where);

}

echo json_encode( array(
	'code'	=> 200
));

wp_die();

?>