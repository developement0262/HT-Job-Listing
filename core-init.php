<?php 

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if

// Define Our Constants
define( 'HTJL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

/****************************
* Plugin activation function
*****************************/
function htjl_plugin_activation(){
	include( HTJL_PLUGIN_PATH . 'inc/activation.php' );
}

/*****************************
* Plugin deactivation function
*****************************/
function htjl_plugin_deactivation(){

}

/*****************************
* Plugin init function
*****************************/
function htjl_init(){
	include( HTJL_PLUGIN_PATH . 'inc/api.php');
}
add_action('init','htjl_init');

/*****************************
* Enqueue styles and scripts
*****************************/
add_action('wp_enqueue_scripts', 'htjl_scripts');
function htjl_scripts(){

	// htmr css
	wp_enqueue_style( 'htjl_css', plugins_url( 'assets/css/htjl.css', __FILE__ ) );
	wp_enqueue_style( 'htjl_multiselect_css', plugins_url( 'assets/css/htjl_multiselect.css', __FILE__ ) );
	wp_enqueue_style( 'htjl_all_jobs_css', plugins_url( 'assets/css/htjl_all_jobs.css', __FILE__ ) );
	
	//htmr js
	wp_enqueue_script( 'htjl_js', plugins_url( 'assets/js/htjl.js', __FILE__ ) );
	wp_localize_script( 'htjl_js', 'htjl', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	wp_enqueue_script( 'htjl_pagination', 'https://cdnjs.cloudflare.com/ajax/libs/simplePagination.js/1.6/jquery.simplePagination.js');
	wp_enqueue_script ('htjl_all_job_listing', plugins_url( 'assets/js/htjl_all_job_listing.js', __FILE__ ) );
	wp_enqueue_script ('htjl_multiselect', plugins_url( 'assets/js/htjl_multiselect.js', __FILE__ ) );

}

/*****************************
* Jobs listing shortcode
*****************************/
add_shortcode('company_job_listing', 'htmr_company_job_listing');
function htmr_company_job_listing(){
	ob_start();
	include( HTJL_PLUGIN_PATH . 'inc/htmr_company_job_listing.php');
	$return_string = ob_get_clean();
	return $return_string;
}

/*****************************
* Job detail shortcode
*****************************/
add_shortcode('company_job_detail', 'htmr_company_job_detail');
function htmr_company_job_detail(){
	ob_start();
	include( HTJL_PLUGIN_PATH . 'inc/htmr_company_job_detail.php');
	$return_string = ob_get_clean();
	return $return_string;
}

/*****************************
* All Jobs listing shortcode
*****************************/
add_shortcode('company_all_jobs_listing', 'htjl_all_job_listing');
function htjl_all_job_listing(){
	ob_start();
	include( HTJL_PLUGIN_PATH . 'inc/htjl_all_job_listing.php');
	$return_string = ob_get_clean();
	return $return_string;
}

/*****************************
* Ajax
*****************************/
add_action('wp_ajax_nopriv_job_detail_form', 'job_detail_form_func');
add_action( 'wp_ajax_job_detail_form', 'job_detail_form_func' );
function job_detail_form_func(){
	include( HTJL_PLUGIN_PATH . 'inc/job_detail_form.php');
}

add_action('wp_ajax_nopriv_htjl_add_group_data', 'htjl_add_group_data_func');
add_action( 'wp_ajax_htjl_add_group_data', 'htjl_add_group_data_func' );
function htjl_add_group_data_func(){
	include( HTJL_PLUGIN_PATH . 'inc/add_group_data.php');
}

add_action('wp_ajax_nopriv_htjl_all_job_listing', 'htjl_all_job_listing_func');
add_action( 'wp_ajax_htjl_all_job_listing', 'htjl_all_job_listing_func' );
function htjl_all_job_listing_func(){
	include( HTJL_PLUGIN_PATH . 'inc/htjl_all_job_lisint_ajax.php');
}

/*****************************
* Get user IP Address
*****************************/
function htjl_getUserIpAddr(){
    if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    // static ip for test
    // $ip = '103.53.164.08';
    return $ip;
}

/*****************************
* non logged in user after login insert user id
*****************************/
add_action('wp_login', 'htjl_get_user_id', 10, 2);
function htjl_get_user_id($user_login, $user){
	global $wpdb;
	$user_id = $user->ID;

	// Update user resume details
	$htjl_user_resume_detail = $wpdb->prefix . 'htjl_user_resume_detail';
	$checkIfExists = $wpdb->get_var("SELECT * FROM $htjl_user_resume_detail WHERE user_ip = '".htjl_getUserIpAddr()."' AND user_id = 0 ");
	if ($checkIfExists != NULL) {
		$wpdb->query( $wpdb->prepare( " UPDATE $htjl_user_resume_detail SET `user_id`= $user_id WHERE `user_ip` = '".htjl_getUserIpAddr()."' " ) );
	}

	$htmr_user_resume = $wpdb->prefix . 'htmr_user_resume';
	if($wpdb->get_var("SHOW TABLES LIKE '$htmr_user_resume'") == $htmr_user_resume) {
		$checkIfExists = $wpdb->get_var("SELECT * FROM $htmr_user_resume WHERE user_id = 0 AND user_ip = '".htjl_getUserIpAddr()."' ");
		if ($checkIfExists != NULL) {
			$wpdb->query( $wpdb->prepare( " UPDATE $htmr_user_resume SET `user_id`= $user_id WHERE user_ip = '".htjl_getUserIpAddr()."' " ) );
		}
	}
}


/*******************************/


function my_prefix_gamipress_earners_count_shortcode( $atts ) {

    global $wpdb;

    $atts = shortcode_atts( array(
        'id' => '',
    ), $atts, 'gamipress_earners_count' );

    $id = absint( $atts['id'] );

    $user_earnings = GamiPress()->db->user_earnings;

    // Return the sum of achievements earned of all or specific points types
    return absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$user_earnings} AS ue WHERE ue.post_id = {$id}" ) );

}
add_shortcode( 'gamipress_earners_count', 'my_prefix_gamipress_earners_count_shortcode' );

add_shortcode('wid', function(){
	$user_id = get_current_user_id();
	//$user_name = bp_core_get_core_userdata( $user_id );

	/*$args = array(
		'item_id' => $user_id,
	);
	$user_cover_image = bp_attachments_get_attachment( 'url', $args );*/

	/*$args = array( 'item_id' => $user_id, 'html' => false, );
	$user_avatar = bp_get_displayed_user_avatar($args);*/

	//$connection = friends_get_total_friend_count( $user_id );

	//$connection_req = bp_friend_get_total_requests_count( $user_id );

	echo do_shortcode( '[gamipress_earners_count id="710"]' );

	/*echo '<pre>';
	print_r();
	echo '</pre>';*/

});

?>
