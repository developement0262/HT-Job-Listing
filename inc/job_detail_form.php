<?php 

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if

// check security nonce which one we created in html form and sending with data.
check_ajax_referer('uploadingFile', 'security');

$fullname = $_POST['job_detail_full_name'];
$email = $_POST['job_detail_email'];
$online_resume_url = $_POST['resume_url'];
$job_id = $_POST['job_id'];


if ( !empty($_FILES["job_detail_resume2"]["name"]) ){
	
	// Not empty
	// removing white space
	$fileName = preg_replace('/\s+/', '-', $_FILES["job_detail_resume2"]["name"]);

	// removing special character but keep . character because . seprate to extantion of file
	$fileName = preg_replace('/[^A-Za-z0-9.\-]/', '', $fileName);
	$upload_file = wp_upload_bits($fileName, null, file_get_contents($_FILES["job_detail_resume2"]["tmp_name"]));

	// upload file
	if( $upload_file )
	{	
		$endpoint = 'apply';
		$check = htjl_job_listing($endpoint, $job_id, $email, $upload_file['url']);
		echo json_encode(['code'=>200]);
	}
	else{
		echo json_encode(['code'=>404, 'msg'=>'Sorry, something went wrong. Please try again later.']);
	}

}else{
	// Empty
	$endpoint = 'apply';
	$check = htjl_job_listing($endpoint, $job_id, $email, $online_resume_url);
	if ( !empty($check) ) {
		echo json_encode(['code'=>200]);
	}else{
		echo json_encode(['code'=>404, 'msg'=>'Sorry, something went wrong. Please try again later.']);
	}
}

wp_die();
?>