<?php 

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if
global $wpdb;

/****************************
* Creating table for job id and username
*****************************/

$htjl_job_listing = $wpdb->prefix . 'htjl_job_listing';
$query = "CREATE TABLE $htjl_job_listing(
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `group_id` TEXT NOT NULL ,
  `img_url` TEXT NOT NULL ,
  PRIMARY KEY (`id`)
)";

require_once(ABSPATH ."wp-admin/includes/upgrade.php");
dbDelta( $query );

/****************************
* Creating table for saved jobs
*****************************/

$htjl_saved_jobs = $wpdb->prefix . 'htjl_saved_jobs';
$query = "CREATE TABLE $htjl_saved_jobs(
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NOT NULL ,
  `job_id` TEXT NOT NULL ,
  PRIMARY KEY (`id`)
)";

require_once(ABSPATH ."wp-admin/includes/upgrade.php");
dbDelta( $query );


/****************************
* Creating table for non logged in user for upload resume
*****************************/

$htjl_user_upload_resume_detail = $wpdb->prefix . 'htjl_user_upload_resume_detail';
$query = "CREATE TABLE $htjl_user_upload_resume_detail(
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `user_ip` VARCHAR(50) NOT NULL,
  `first_name` VARCHAR(150) NOT NULL ,
  `last_name` VARCHAR(150) NOT NULL ,
  `email` VARCHAR(150) NOT NULL ,
  `username` VARCHAR(150) NOT NULL ,
  `password` VARCHAR(150) NOT NULL ,
  PRIMARY KEY (`id`)
)";

require_once(ABSPATH ."wp-admin/includes/upgrade.php");
dbDelta( $query );


/****************************
* Creating table for non logged in user resume name
*****************************/

$htjl_user_resume_detail = $wpdb->prefix . 'htjl_user_resume_detail';
$query = "CREATE TABLE $htjl_user_resume_detail(
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NOT NULL,
  `user_ip` VARCHAR(50) NOT NULL,
  `resume_name` VARCHAR(150) NOT NULL ,
  `resume_url` VARCHAR(250) NOT NULL ,
  PRIMARY KEY (`id`)
)";

require_once(ABSPATH ."wp-admin/includes/upgrade.php");
dbDelta( $query );

/****************************
* Creating table for users skill
*****************************/

$htjl_user_skills = $wpdb->prefix . 'htjl_user_skills';
$query = "CREATE TABLE $htjl_user_skills(
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `user_id` INT(11) NOT NULL,
  `skills` VARCHAR(250) NOT NULL,
  PRIMARY KEY (`id`)
)";

require_once(ABSPATH ."wp-admin/includes/upgrade.php");
dbDelta( $query );


/****************************
* Alter table for resume in profile
*****************************/
$htmr_user_resume = $wpdb->prefix . 'htmr_user_resume';
$sql = "ALTER TABLE $htmr_user_resume ADD `user_ip` VARCHAR(100) NULL DEFAULT NULL;";
$query_result = $wpdb->query( $sql );


/****************************
* Creating job detail page
*****************************/
$page = get_page_by_title( 'Job Detail' );
if ( !$page ) {
  
  $my_post = array(
    'post_title'    => wp_strip_all_tags( 'Job Detail' ),
    'post_content'  => '[company_job_detail]',
    'post_status'   => 'publish',
    'post_author'   => get_current_user_id(),
    'post_type'     => 'page',
  );

  // Insert the post into the database
  wp_insert_post( $my_post );

}

/****************************
* Creating All job listing page
*****************************/
$page = get_page_by_title( 'Job listing' );
if ( !$page ) {
  
  $my_post = array(
    'post_title'    => wp_strip_all_tags( 'Job listing' ),
    'post_name'     => 'jobs',
    'post_content'  => '[company_all_jobs_listing]',
    'post_status'   => 'publish',
    'post_author'   => get_current_user_id(),
    'post_type'     => 'page',
  );

  // Insert the post into the database
  wp_insert_post( $my_post );

}

?>