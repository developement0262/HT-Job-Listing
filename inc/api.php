<?php 

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if

function htjl_job_listing($endpoint, $job_id=null, $email=null, $resume=null){

	if ( $job_id == null || empty($job_id) ) {
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://services-test.hackertrail.com/api/' . $endpoint,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));

		$response = curl_exec($curl);
		curl_close($curl);
		return json_decode($response);

	}else{

		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://services-test.hackertrail.com/api/' . $endpoint,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => array('email' => $email, 'job' => $job_id, 'resume'=> new CURLFILE($resume)),
		));
		$response = curl_exec($curl);

		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);
		return $httpcode;

	}

		
}

?>