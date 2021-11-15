<?php 

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if

global $wpdb;
$type = $_REQUEST['listing_type'];

if ( $type == 'upload_resume' ) {
	
	$output = '';
	$fileName = preg_replace('/\s+/', '-', $_FILES["file"]["name"]);
	$fileName = preg_replace('/[^A-Za-z0-9.\-]/', '', $fileName);
	$upload_file = wp_upload_bits($fileName, null, file_get_contents($_FILES["file"]["tmp_name"]));
	$upload_url = $upload_file['url'];
	$resume = site_url() . '/wp-content/plugins/htjl/assets/img/Resume.png';
	$loader = site_url() . '/wp-content/plugins/htjl/assets/img/loader.gif';


	// Validate Resume
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://services-test.hackertrail.com/api/validate_resume/',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS => array('resume'=> new CURLFILE($upload_url)),
	  CURLOPT_HTTPHEADER => array(
	    'Accept: application/json',
	    'Content-Type: multipart/form-data'
	  ),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	$results = json_decode($response);
	
	if ( $results->exists != 1 ) {
		// Non Exist user
		$output .= '<div class="col-lg-12 col-sm-12 col-xs-12">';
	        $output .= '<div class="box__wrap" style="margin: 0 auto;">';
	        	$output .= '<div class="loader__box" style="display:none;">';
					$output .= '<img src="'.$loader.'" class="img-fluid">';
				$output .= '</div>';
	        	$output .= '<img src="'.$resume.'" style="width: 58%;">';
	        	$output .= '<h4>Is This You?</h4>';
				$output .= '<p>We have identified '.$results->email.' as your email, hope we got that right!</p>';
	        	$output .= '<div class="box__btn">';
	        		$output .= '<a href="" class="verify_btn upload_thats_right">Thats Right!</a>';
	        		$output .= '<a class="verify_btn upload_thats_not_me">Thats Not Me</a>';
	        	$output .= '</div>';
	        $output .= '</div>';
	    $output .= '</div>';

	    // Insert user resume details
		$htjl_user_resume_detail = $wpdb->prefix . 'htjl_user_resume_detail';
		$checkIfExists = $wpdb->get_var("SELECT * FROM $htjl_user_resume_detail WHERE user_ip = '".htjl_getUserIpAddr()."' ");
		if ($checkIfExists == NULL) {
			$wpdb->insert($htjl_user_resume_detail, array(
				'user_id'		=> 0,
				'user_ip' 		=> htjl_getUserIpAddr(),
				'resume_name'	=> $_FILES["file"]["name"],
				'resume_url'	=> $upload_url,
			), array(
				'%d', '%s', '%s', '%s'
			));
		}

	    // Insert user details
	    $htjl_user_upload_resume_detail = $wpdb->prefix . 'htjl_user_upload_resume_detail';
	    $checkIfExists = $wpdb->get_var("SELECT * FROM $htjl_user_upload_resume_detail WHERE user_ip = '".htjl_getUserIpAddr()."' ");
		if ($checkIfExists == NULL) {

			$wpdb->insert($htjl_user_upload_resume_detail, array(
				'user_ip'		=> htjl_getUserIpAddr(),
				'first_name' 	=> $results->first_name,
				'last_name'		=> $results->last_name,
				'email'			=> $results->email,
				'username'		=> $results->username,
				'password'		=> $results->password,
			), array(
				'%s', '%s', '%s', '%s', '%s', '%s'
			));

		}


	}else{
		// Exist user
		$output .= '<div class="col-lg-12 col-sm-12 col-xs-12">';
	        $output .= '<div class="box__wrap" style="margin: 0 auto;">';
	        	$output .= '<img src="'.$resume.'" style="width: 58%;">';
	        	$output .= '<h4>It seems like you already have an account</h4>';
				$output .= '<p>An account with the email "'.$results->email.'" already exists, sign in instead!</p>';
	        	$output .= '<div class="box__btn">';
	        		$output .= '<a href="'.site_url().'/login/" class="verify_btn new_user_sign_in">Sign In</a>';
	        	$output .= '</div>';
	        $output .= '</div>';
	    $output .= '</div>';

	}

	echo json_encode(array(
		'output' => $output,
	));

}
elseif ( $type == 'upload_thats_right' ) {
	
	$user_ip = htjl_getUserIpAddr();
	$htjl_user_upload_resume_detail = $wpdb->prefix . 'htjl_user_upload_resume_detail';
	$htjl_user_resume_detail = $wpdb->prefix . 'htjl_user_resume_detail';
	$results = $wpdb->get_results( "SELECT * FROM $htjl_user_upload_resume_detail WHERE `user_ip` = '$user_ip' ");
	$results2 = $wpdb->get_results( "SELECT * FROM $htjl_user_resume_detail WHERE `user_ip` = '$user_ip' ");
	$output = '';
	
	if ( !empty($results) ) {

		foreach ($results as $result) {
			// Registering user
			$usermeta = array();
			$usermeta['password'] = wp_hash_password( $result->password );
			bp_core_signup_user(
		        $result->first_name,
		        $result->password,
		        $result->email,
		        $usermeta
			);
		}

		// Sending mail to new register user
		$to = $result->email;
		$subject = 'New Register';
		$body = 'Thank you for your registration below are your details <br>Username: '.$result->first_name.' <br>Email: '. $result->email .' <br> Password: '. $result->password;
		$headers = array('Content-Type: text/html; charset=UTF-8');
		wp_mail( $to, $subject, $body, $headers );


		// check table and insert into table if exist
		$htmr_user_resume = $wpdb->prefix . 'htmr_user_resume';
		if($wpdb->get_var("SHOW TABLES LIKE '$htmr_user_resume'") == $htmr_user_resume) {

			foreach ($results2 as $result) {
				$wpdb->insert($htmr_user_resume, array(
					'user_id'		=> 0,
					'resume_url' 	=> $result->resume_url,
					'file_name'		=> $result->resume_name,
					'modified'		=> date("F d, Y"),
					'user_ip'		=> htjl_getUserIpAddr()
				), array(
					'%d', '%s', '%s', '%s', '%s'
				));
			}

		}
		

		// Delete row after register
		$wpdb->delete( $htjl_user_upload_resume_detail, array( 'user_ip' => $result->user_ip ) );

		$welcome = site_url() . '/wp-content/plugins/htjl/assets/img/welcome.png';
		$output .= '<div class="col-lg-12 col-sm-12 col-xs-12">';
	        $output .= '<div class="box__wrap" style="margin: 0 auto;">';
	        	$output .= '<img src="'.$welcome.'" style="width: 58%;">';
	        	$output .= '<h4>Welcome to HackerTrail Community!</h4>';
				$output .= '<p>An account has been created for you. Refer to the email we have sent for your account credentials!</p>';
	        	$output .= '<div class="box__btn">';
	        		$output .= '<a href="'.site_url().'/login/" class="verify_btn new_user_sign_in">Sign In</a>';
	        	$output .= '</div>';
	        $output .= '</div>';
	    $output .= '</div>';

	}
	echo json_encode(array(
		'message'  	=> 'User Register',
		'output'	=> $output,
	));

}
elseif ( $type == 'upload_thats_not_me' ){

	$output = '';
	$resume = site_url() . '/wp-content/plugins/htjl/assets/img/Resume.png';
	$loader = site_url() . '/wp-content/plugins/htjl/assets/img/loader.gif';+

	$output .= '<div class="col-lg-12 col-sm-12 col-xs-12">';
        $output .= '<div class="box__wrap" style="margin: 0 auto;">';
        	$output .= '<div class="loader__box" style="display:none;">';
				$output .= '<img src="'.$loader.'" class="img-fluid">';
			$output .= '</div>';
        	$output .= '<img src="'.$resume.'" style="width: 58%;">';
        	$output .= '<h4>Please provide us with your email</h4>';
			$output .= '<input type="email" id="htjl_non_user_email" placeholder="Your email address">';
        	$output .= '<div class="box__btn">';
        		$output .= '<button class="verify_btn htjl_non_user_email_btn">Submit</button>';
        	$output .= '</div>';
        $output .= '</div>';
    $output .= '</div>';

    echo json_encode(array(
		'output'	=> $output,
	));
}
elseif ( $type == 'htjl_new_email' ) {
	
	$new_email = $_REQUEST['new_email'];

	$htjl_user_resume_detail = $wpdb->prefix . 'htjl_user_resume_detail';
	$get_row = $wpdb->get_row("SELECT * FROM $htjl_user_resume_detail WHERE user_ip = '".htjl_getUserIpAddr()."' ");
	if ( !empty($get_row) ) {
		$resume_url = $get_row->resume_url;
	}

	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://services-test.hackertrail.com/api/update_user_resume/',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS => array('candidate_email' => $new_email,'resume'=> new CURLFILE($resume_url)),
	  CURLOPT_HTTPHEADER => array(
	    'Accept: application/json',
	    'Content-Type: multipart/form-data'
	  ),
	));

	$response = curl_exec($curl);

	curl_close($curl);
	$results = json_decode($response);

	// Registering user
	$usermeta = array();
	$usermeta['password'] = wp_hash_password( $results->password );
	bp_core_signup_user(
        $results->first_name,
        $results->password,
        $results->email,
        $usermeta
	);

	// Sending mail to new register user
	$to = $new_email;
	$subject = 'New Register';
	$body = 'Thank you for your registration below are your details <br>Username: '.$results->first_name.' <br>Email: '. $results->email .' <br> Password: '. $results->password;
	$headers = array('Content-Type: text/html; charset=UTF-8');
	wp_mail( $to, $subject, $body, $headers );

	// check table and insert into table if exist
	$results2 = $wpdb->get_results( "SELECT * FROM $htjl_user_resume_detail WHERE `user_ip` = '".htjl_getUserIpAddr()."' ");
	$htmr_user_resume = $wpdb->prefix . 'htmr_user_resume';
	if($wpdb->get_var("SHOW TABLES LIKE '$htmr_user_resume'") == $htmr_user_resume) {

		foreach ($results2 as $result) {
			$wpdb->insert($htmr_user_resume, array(
				'user_id'		=> 0,
				'resume_url' 	=> $result->resume_url,
				'file_name'		=> $result->resume_name,
				'modified'		=> date("F d, Y"),
				'user_ip'		=> htjl_getUserIpAddr()
			), array(
				'%d', '%s', '%s', '%s', '%s'
			));
		}

	}

	// Delete row after register
	$htjl_user_upload_resume_detail = $wpdb->prefix . 'htjl_user_upload_resume_detail';
	$wpdb->delete( $htjl_user_upload_resume_detail, array( 'user_ip' => $result->user_ip ) );

	$welcome = site_url() . '/wp-content/plugins/htjl/assets/img/welcome.png';
	$output .= '<div class="col-lg-12 col-sm-12 col-xs-12">';
        $output .= '<div class="box__wrap" style="margin: 0 auto;">';
        	$output .= '<img src="'.$welcome.'" style="width: 58%;">';
        	$output .= '<h4>Welcome to HackerTrail Community!</h4>';
			$output .= '<p>An account has been created for you. Refer to the email we have sent for your account credentials!</p>';
        	$output .= '<div class="box__btn">';
        		$output .= '<a href="'.site_url().'/login/" class="verify_btn new_user_sign_in">Sign In</a>';
        	$output .= '</div>';
        $output .= '</div>';
    $output .= '</div>';

	echo json_encode(array(
		'message'  	=> 'User Register',
		'output'	=> $output,
	));

}
elseif ( $type == 'skill_box_upload_resume' ){
	//$fileName = $_FILES["file"]["name"];

	$fileName = preg_replace('/\s+/', '-', $_FILES["file"]["name"]);
	$fileName = preg_replace('/[^A-Za-z0-9.\-]/', '', $fileName);
	$upload_file = wp_upload_bits($fileName, null, file_get_contents($_FILES["file"]["tmp_name"]));
	$upload_url = $upload_file['url'];

	// Insert user resume details
	$htjl_user_resume_detail = $wpdb->prefix . 'htjl_user_resume_detail';
	$checkIfExists = $wpdb->get_var("SELECT * FROM $htjl_user_resume_detail WHERE user_ip = '".htjl_getUserIpAddr()."'");

	if ( $checkIfExists == NULL ) {
		$wpdb->insert($htjl_user_resume_detail, array(
			'user_id'		=> get_current_user_id(),
			'user_ip' 		=> htjl_getUserIpAddr(),
			'resume_name'	=> $_FILES["file"]["name"],
			'resume_url'	=> $upload_url,
		), array(
			'%d', '%s', '%s', '%s'
		));
	}else{

		$wpdb->query( $wpdb->prepare( "UPDATE $htjl_user_resume_detail SET `resume_name` = '".$_FILES["file"]["name"]."', `resume_url`= '".$upload_url."' WHERE `user_ip` = '".htjl_getUserIpAddr()."' AND `user_id` = '".get_current_user_id()."' " ) );
	}

	echo json_encode(array(
		'name'	=> $_FILES["file"]["name"],
	));	
}
/*elseif ( $type == 'htjl_add_skill' ) {

	$skills = $_REQUEST['skill'];
	$user_id = get_current_user_id();
	$htjl_user_skills = $wpdb->prefix . 'htjl_user_skills';
	$output = '';

	$skill = explode(', ', $skills);
	array_pop($skill);
	foreach ($skill as $val) {
		$string = preg_replace('/\s+/', '', $val);
		$checkIfExists = $wpdb->get_var("SELECT * FROM $htjl_user_skills WHERE user_id = $user_id AND skills = '".$string."' ");
		if ($checkIfExists == NULL) {
			// insert
			$wpdb->insert($htjl_user_skills, array(
				'user_id'	=> $user_id,
				'skills' 	=> $val,
			), array(
				'%d', '%s'
			));
		}
	}

	$status = "Data Added";

	$get_skills = $wpdb->get_results( "SELECT * FROM $htjl_user_skills WHERE user_id = $user_id" );
	if ( !empty($get_skills) ) {

		foreach ($get_skills as $skill) {
			$output .= '<a href="javascript: void(0);">'.$skill->skills;
		    $output .= '<span class="remove-tag" id="'.$skill->id.'">';
		        $output .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" class="bi bi-plus-lg"
		          viewBox="0 0 16 16">
		          <path
		            d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z" />
		        </svg>';
		    $output .= '</span>';
	    	$output .= '</a>';
		}
	}else{ $output .= ''; }

	echo json_encode(array(
		'status'	=> $status,
		'output'	=> $output,
	));
}*/
elseif ( $type == 'htjl_add_skill' ) {

	$skills = $_REQUEST['skill'];

	//$skills = implode(', ', $skills);
	$user_id = get_current_user_id();
	$htjl_user_skills = $wpdb->prefix . 'htjl_user_skills';
	$output = '';

	foreach ($skills as $val) {
		$checkIfExists = $wpdb->get_var("SELECT * FROM $htjl_user_skills WHERE user_id = $user_id AND skills = '".$val."' ");
		if ($checkIfExists == NULL) {
			// insert
			$wpdb->insert($htjl_user_skills, array(
				'user_id'	=> $user_id,
				'skills' 	=> $val,
			), array(
				'%d', '%s'
			));
		}
	}
	$status = "Data Added";

	$get_skills = $wpdb->get_results( "SELECT * FROM $htjl_user_skills WHERE user_id = $user_id" );
	if ( !empty($get_skills) ) {

		foreach ($get_skills as $skill) {
			$output .= '<a href="javascript: void(0);">'.$skill->skills;
		    $output .= '<span class="remove-tag" id="'.$skill->id.'">';
		        $output .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" class="bi bi-plus-lg"
		          viewBox="0 0 16 16">
		          <path
		            d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z" />
		        </svg>';
		    $output .= '</span>';
	    	$output .= '</a>';
		}
	}else{ $output .= ''; }

	echo json_encode(array(
		'status'	=> $status,
		'output'	=> $output,
	));
}
elseif ( $type == 'htjl_delete_skill' ){
	$id = $_REQUEST['id'];
	$user_id = get_current_user_id();
	$htjl_user_skills = $wpdb->prefix . 'htjl_user_skills';
	$wpdb->delete( $htjl_user_skills, array( 'id' => $id ) );

	$get_skills = $wpdb->get_results( "SELECT * FROM $htjl_user_skills WHERE user_id = $user_id" );
	if ( !empty($get_skills) ) {

		foreach ($get_skills as $skill) {
			$output .= '<a href="javascript: void(0);">'.$skill->skills;
		    $output .= '<span class="remove-tag" id="'.$skill->id.'">';
		        $output .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" class="bi bi-plus-lg"
		          viewBox="0 0 16 16">
		          <path
		            d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z" />
		        </svg>';
		    $output .= '</span>';
			$output .= '</a>';
		}

	}else{ $output .= ''; }

	echo json_encode(array(
		'output'	=> $output,
	));
}
elseif ( $type == 'htjl_save_user_detail' ) {
	
	// get user skill
	$htjl_user_skills = $wpdb->prefix . 'htjl_user_skills';
	$htjl_user_resume_detail = $wpdb->prefix . 'htjl_user_resume_detail';
	$user_id = get_current_user_id();
	$results = $wpdb->get_results( "SELECT * FROM $htjl_user_skills WHERE user_id = $user_id" );
	$resume_detail = $wpdb->get_results( "SELECT * FROM $htjl_user_resume_detail WHERE user_id = $user_id" );
	$skills = $resume_url = array();
	$email = wp_get_current_user();
    $user_email = $email->data->user_email;
	if ( !empty($results) ) {
		foreach($results as $result){
			$skills[] = $result->skills;
		}
	}
	if ( !empty($resume_detail) ) {
		foreach ($resume_detail as $resume) {
			$resume_url[] = $resume->resume_url;
		}
	}
	$skills = implode(',', $skills);
	
	// Save user api
	$curl = curl_init();
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://services-test.hackertrail.com/api/save_user_details/',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS => array('candidate_email' => $user_email, 'skills' => $skills, 'resume'=> new CURLFILE($resume_url[0])),
	  CURLOPT_HTTPHEADER => array(
	    'Accept: application/json',
	    'Content-Type: multipart/form-data'
	  ),
	));

	$response = curl_exec($curl);
	curl_close($curl);
	
	echo json_encode(array(
		'status' => $response,
	));
}
elseif ( $type == 'htjl_save_jobs' ){

	$output = '';

	if ( !is_user_logged_in() ) {
		echo json_encode( array(
			'status' => '0'
		) );
	}else{

		$job_id = $_REQUEST['job_id'];
		$user_id = get_current_user_id();

		// inserting job id into table
		$htjl_saved_jobs = $wpdb->prefix . 'htjl_saved_jobs';
		$wpdb->insert($htjl_saved_jobs, array(
			'user_id'	=> $user_id,
			'job_id' 	=> $job_id,
		), array(
			'%d', '%s'
		));

		$save_jobs = $wpdb->get_results( "SELECT * FROM $htjl_saved_jobs WHERE user_id = $user_id" );
		if ( !empty( $save_jobs ) ){
			foreach( $save_jobs as $jobs ){
				$job_id = $jobs->job_id;
	            $get_job_detail = 'jobs/'.$job_id;
	            $job_detail = htjl_job_listing($get_job_detail);

	            // Get group id by name
                $saved_group = groups_get_groups( array( 'slug' => array($job_detail->company_slug) ) );
                foreach( $saved_group['groups'] as $group ){
                	$src = bp_get_group_cover_url($group->id);
                  	$avatar_options = array ( 'item_id' => $group->id, 'object' => 'group', 'type' => 'full', 'avatar_dir' => 'group-avatars', 'alt' => 'Group avatar', 'class' => 'avatar', 'width' => 50, 'height' => 50, 'html' => false );
                  	$avatar = bp_core_fetch_avatar($avatar_options);
                	$company_id = $group->id;                   
                    $company_name = $group->name;
                    $output .= '<div class="total__jobs_list__save">';
                    	$output .= '<div class="total__jobs_list_box">';
                    		$output .= '<div class="total__jobs_list_box_left">';
                    			$output .= '<div class="jobs_list_img">';
                    				$output .= '<img src="'.$avatar.'" class="img-fluid">';
                    			$output .= '</div>';
                    			$output .= '<div class="jobs_list_details">';
                    				$output .= '<h3><a href="'.site_url().'/job-detail/?job_id='.$job_detail->id.'&company_name='.$job_detail->company_slug.'&group_id='.base64_encode($company_id).'">'.$job_detail->title.'</a></h3>';
                    				$output .= '<ul>';
                    					$output .= '<li><span class="jobs-type"><b>'.$company_name.'</b></span></li>';
                    					$output .= '&bull;';
                    					$output .= '<li><span class="jobs-location">'.$job_detail->location.'</span></li>';
                    				$output .= '</ul>';
                    			$output .= '</div>';
                    		$output .= '</div>';
                    		$output .= '<div class="total__jobs_list_box_right">';
                    			$output .= '<div class="main__input__box-tags">';
                    				$output .= '<ul>';
                    					$skills = $job_detail->skills;
                    					$skills = explode(',', $skills[0]);
                    					foreach ($skills as $skill) {
                    						$output .= '<li><a href="javascript:void(0);">'.$skill.'</a></li>';
                    					} // endforeach
                    				$output .= '</ul>';
                    			$output .= '</div>';
                    			$output .= '<div class="main__input__save-tags">';
                    				$output .= '<a href="javascript: void(0);" class="remove_jobs" data-id="'.$job_detail->id.'"><i class="fa fa-bookmark" aria-hidden="true"></i></a>';
                    			$output .= '</div>';
                    		$output .= '</div>';
                    	$output .= '</div>';
                    $output .= '</div>';
                } //end foreach 
			} // end foreach
		} // endif
		else{
			$output .= '<p class="no__job_found">No Saved Jobs!</p>';
		}

		echo json_encode( array(
			'status' => 'Job Inserted',
			'output' => $output
		) );
	}

}
elseif ( $type == 'htjl_remove_jobs' ) {

	$job_id = $_REQUEST['job_id'];
	$user_id = get_current_user_id();

	$htjl_saved_jobs = $wpdb->prefix . 'htjl_saved_jobs';
	$user_id = get_current_user_id();
	$output = '';

	// Delete row after register
	$wpdb->delete( $htjl_saved_jobs, array( 'user_id' => $user_id, 'job_id' => $job_id ) );

	$save_jobs = $wpdb->get_results( "SELECT * FROM $htjl_saved_jobs WHERE user_id = $user_id" );
	if ( !empty( $save_jobs ) ){
		foreach( $save_jobs as $jobs ){
			$job_id = $jobs->job_id;
            $get_job_detail = 'jobs/'.$job_id;
            $job_detail = htjl_job_listing($get_job_detail);

            // Get group id by name
            $saved_group = groups_get_groups( array( 'slug' => array($job_detail->company_slug) ) );
            foreach( $saved_group['groups'] as $group ){
            	$src = bp_get_group_cover_url($group->id);
              	$avatar_options = array ( 'item_id' => $group->id, 'object' => 'group', 'type' => 'full', 'avatar_dir' => 'group-avatars', 'alt' => 'Group avatar', 'class' => 'avatar', 'width' => 50, 'height' => 50, 'html' => false );
              	$avatar = bp_core_fetch_avatar($avatar_options);
            	$company_id = $group->id;                   
                $company_name = $group->name;
                $output .= '<div class="total__jobs_list__save">';
                	$output .= '<div class="total__jobs_list_box">';
                		$output .= '<div class="total__jobs_list_box_left">';
                			$output .= '<div class="jobs_list_img">';
                				$output .= '<img src="'.$avatar.'" class="img-fluid">';
                			$output .= '</div>';
                			$output .= '<div class="jobs_list_details">';
                				$output .= '<h3><a href="'.site_url().'/job-detail/?job_id='.$job_detail->id.'&company_name='.$job_detail->company_slug.'&group_id='.base64_encode($company_id).'">'.$job_detail->title.'</a></h3>';
                				$output .= '<ul>';
                					$output .= '<li><span class="jobs-type"><b>'.$company_name.'</b></span></li>';
                					$output .= '&bull;';
                					$output .= '<li><span class="jobs-location">'.$job_detail->location.'</span></li>';
                				$output .= '</ul>';
                			$output .= '</div>';
                		$output .= '</div>';
                		$output .= '<div class="total__jobs_list_box_right">';
                			$output .= '<div class="main__input__box-tags">';
                				$output .= '<ul>';
                					$skills = $job_detail->skills;
                					$skills = explode(',', $skills[0]);
                					foreach ($skills as $skill) {
                						$output .= '<li><a href="javascript:void(0);">'.$skill.'</a></li>';
                					} // endforeach
                				$output .= '</ul>';
                			$output .= '</div>';
                			$output .= '<div class="main__input__save-tags">';
                				$output .= '<a href="javascript: void(0);" class="remove_jobs" data-id="'.$job_detail->id.'"><i class="fa fa-bookmark" aria-hidden="true"></i></a>';
                			$output .= '</div>';
                		$output .= '</div>';
                	$output .= '</div>';
                $output .= '</div>';
            } //end foreach 
		} // end foreach
	} // endif
	else{
		$output .= '<p class="no__job_found">No Saved Jobs!</p>';
	}

	echo json_encode( array(
		'status' 	=> 'Job Deleted',
		'output' 	=> $output,
	) );
}
elseif ( $type == 'get_pagination_list' ) {

	$endpoint = $_REQUEST['endpoint'];
	$job_list = job_listing($endpoint);
	$job_pagination = job_pagination($job_list['total'], $job_list['prev'], $job_list['cur_page'], $job_list['next']);
	//job_pagination = '';

	echo json_encode( array(
		'output' => $job_list['output'],
		'page'	 => $job_pagination,
	) );
}
wp_die();

function job_listing($endpoint)
{
	global $wpdb;
	$get_job_list = htjl_job_listing($endpoint);
	$total_jobs_found = $get_job_list->total;
	$user_id = get_current_user_id();
	$top = site_url() . '/wp-content/plugins/htjl/assets/img/top.png';
	$loader = site_url() . '/wp-content/plugins/htjl/assets/img/loader.gif';
	$htjl_saved_jobs = $wpdb->prefix . 'htjl_saved_jobs';
	$arr = [];

	$output = '';
	$output .= '<div class="table__loader__bg"><div class="table__loader"><img src="'.$loader.'"></div></div>';
	if ( !empty( $get_job_list->results ) ){
		foreach( $get_job_list->results as $result ){
			// Get group id by name
            $group = groups_get_groups( array( 'slug'=> $result->company_slug ) );
            $company_id = $group['groups'][0]->id;

            $output .= '<div class="total__jobs_list"><div class="total__jobs_list_box"><div class="total__jobs_list_box_left"><div class="jobs_list_img"><img src="'.$result->image.'" class="img-fluid"></div><div class="jobs_list_details"><h3><a href="'.site_url().'/job-detail/?job_id='.$result->job_id.'&company_name='.$result->company_slug.'&group_id='.base64_encode($company_id).'">'.$result->job_title.'</a></h3><ul><li><span class="jobs-type"><b>'.$result->company_name.'</b></span></li>&bull; <li><span class="jobs-location">'.$result->location.'</span></li>';
				if ( $result->fast_response == true ) {
					$output .= '<li><span class="fast-responce"><i class="fa fa-bolt"></i>Fast Response</span></li>';
				}
            	$output .= '</ul></div>';
    			if ( is_user_logged_in() && $result->match >= 80 ) {
    				$output .= '<div class="top__jobs"><img src="'.$top.'"></div>';
    			}
    			$output .= '</div><div class="total__jobs_list_box_right"><div class="main__input__box-tags"><ul>';
				$skills = $result->skills;
                $skills = explode(',', $skills[0]);
                foreach ($skills as $skill) {
                	$output .= '<li><a href="javascript:void(0);">'.$skill.'</a></li>';
                }
                $output .= '</ul></div><div class="main__input__save-tags">';
                $get_save_job = $wpdb->get_row("SELECT * FROM $htjl_saved_jobs WHERE user_id = $user_id AND job_id = '".$result->job_id."' ");
            	if ( !empty($get_save_job) ) {
            		$output .= '<a href="javascript: void(0);" class="remove_jobs" data-id="'.$result->job_id.'"><i class="fa fa-bookmark" aria-hidden="true"></i></a>';
            	}else{
            		$output .= '<a href="javascript: void(0);" class="save_jobs" data-id="'.$result->job_id.'"><i class="fa fa-bookmark-o" aria-hidden="true"></i></a>';
            	}
                $output .= '</div></div></div></div>';
		} //endforeach
	}// endif

	$arr['output']  = $output;
	$arr['total']  	= $get_job_list->total;
	$arr['prev']  	= $get_job_list->prev_page;
	$arr['cur_page']  	= $get_job_list->cur_page;
	$arr['next']  	= $get_job_list->next_page;

	return $arr;
}

function job_pagination($total, $prev, $cur_page, $next){
	
	$page = '';
	//$get_job_list = htjl_job_listing($endpoint);
	$total_jobs_found = $total;
	$page .= '<ul>';
		$total_pagination = ceil($total_jobs_found / 10);
		if ( $prev == null ) {$page .= "<li class='active'><span class='current prev'>«</span></li>";
		}else{$page .= "<li><a href='javascript:void(0);' data-id=".$prev." class='page-link prev'>«</a></li>";}

		for ($i=1; $i <= $total_pagination; $i++) {
			if ( $cur_page == $i ) {$page .= '<li><span class="current">'.$i.'</span></li>';
			}else{$page .= '<li><a href="javascript:void(0);" data-id='.$i.' class="page-link">'.$i.'</a></li>';}
		}

		if ( $next == null ) {$page .= "<li class='active'><span class='current next'>»</span></li>";
		}else{$page .= "<li><a href='javascript:void(0);' data-id=".$next." class='page-link next'>»</a></li>";}
	$page .= '</ul>';

	return $page;
}
?>