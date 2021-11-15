<?php

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if
global $wpdb;
global $bp;

$htjl_job_listing = $wpdb->prefix . 'htjl_job_listing';

if ( !empty($_REQUEST['job_id']) ) {
	$group_id = $_REQUEST['group_id'];
	$get_group_id = $wpdb->get_row("SELECT * FROM $htjl_job_listing WHERE group_id = '$group_id'");
	$job_id = $_REQUEST['job_id'];

	$real_group_id = base64_decode($group_id);
	$group = groups_get_group( array( 'group_id' => $real_group_id ) );
	$src = bp_get_group_cover_url($group);
	
	$avatar_options = array ( 'item_id' => $real_group_id, 'object' => 'group', 'type' => 'full', 'avatar_dir' => 'group-avatars', 'alt' => 'Group avatar', 'class' => 'avatar', 'width' => 50, 'height' => 50, 'html' => false );

	$avatar = bp_core_fetch_avatar($avatar_options);
	
	$group = groups_get_group( array( 'group_id' => $real_group_id ) );
	$group_url = home_url( '/company/' . $group -> slug );
	
	?>

	<?php
	
	$endpoint = 'jobs/' . $job_id;
	$job_detail = htjl_job_listing($endpoint);
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		$username = bp_core_get_username($user_id);
		$useremail = bp_core_get_user_email($user_id);
	}else{
		$username = '';
		$useremail = '';
	}
	$resume_url = '';
	$file_name = '';
	$table_name = $wpdb->base_prefix.'htmr_user_resume';
	$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );

	if ( is_user_logged_in() ) {
		if ( $wpdb->get_var( $query ) == $table_name ) {
		  $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE user_id = $user_id " );
		  $resume_url = $results[0]->resume_url;
		  $file_name = $results[0]->file_name;
		}	
	}

?>

<style type="text/css">
	h1.entry-title {
	    display: none;
	}
</style>
<div class="htjl_job_detail_container">
	<div class="htjl_detail_title">
		<h1><?php echo $job_detail->title; ?><span><!--(<?php echo $job_detail->track_name; ?>)--></span></h1>
	</div>
	<div class="htjl_job_meta">
		<!--<?php if (!empty($job_detail->industry)) { ?>
			<span class="industry"><?php echo $job_detail->industry; ?></span>-->
		<?php } if (!empty($job_detail->location)) { ?>
			<span class="job_location"><i class="bb-icon-map-pin" aria-hidden="true"></i> <?php echo $job_detail->location; ?></span>
		<?php } ?>
	</div>
	<div class="job_main_box">
		<div class="job_desc_box">
			<div class="job_desc">
				<h1>Job Description</h1>
				<?php echo $job_detail->job_description; ?>
			</div>
			<hr>
			<div class="job_req">
				<h1>Job Requirement</h1>
				<?php echo $job_detail->job_requirement; ?>
			</div>
			<div class="job_apply">
				<button class="btn_job_apply" id="btn_job_apply">Apply for job</button>
			</div>
		</div><!-- .job_desc_box -->
		<div class="htjl_job_meta_box">
			<div class="job_group_img">
				<img src="<?php echo $src; ?>">
			</div>
			
			<div class="job-group-avatar">
				<img src="<?php echo $avatar; ?>" class="job-group-avatar-photo" alt="Logo"></img>
			</div>
			<div class="job_apply">
				<button class="btn_job_apply" id="btn_job_apply2">Apply for job</button>
			</div>
			<div class="detail_view_more">
				<a href="<?php echo site_url().'/jobs/?company_name='. $_REQUEST['company_name']; ?>" class="detail_view_more_jobs">View More Jobs</a>
			</div>
			<div class="side_bar_meta">
				<?php $real_group_id = base64_decode($group_id);
					global $bp;
					$group = groups_get_group( array( 'group_id' => $real_group_id ) );
					$group_url = home_url( '/company/' . $group -> slug );
					$group_name = $group -> name;
					$group_description = $group -> description;
					echo '<div class="job-group-name">'. $group_name . '</div>';
					echo '<div class="group-description-job">' . $group_description . '</div><br/>';
	
					
					echo '<div class="job-group-contact">Contact Us</div>';
					echo '<div class="group-link">';
					echo '<a style="display:block" href="'.$group_url.'">';
					echo '<img class="group-link-img" src="https://img.icons8.com/ios-glyphs/20/000000/link--v2.png"/>   ';
					echo '<a class="group-link-url" href="'.$group_url.'">Website</a>';
					echo '</a>';
					echo '</div>';
				?>
			<!--
				<?php if (!empty($job_detail->positions_available)) { ?>
					<p>Position Available : <?php echo $job_detail->positions_available; ?></p>
				<?php } ?>
				<?php if (!empty($job_detail->salary_currency)) { ?>
					<p>Salary Currency : <?php echo $job_detail->salary_currency; ?></p>
				<?php } ?>
				<?php if (!empty($job_detail->salary_range)) { ?>
					<p>Salary Range : <?php echo $job_detail->salary_range; ?></p>
				<?php } ?>
			-->
			</div>
		</div><!-- .htjl_job_meta_box -->
	</div><!-- .job_main_box -->
		
</div><!-- .htjl_job_detail_container -->


<!-- Model start here -->
<div id="job_detail_myModal" class="job_detail_modal">

  <!-- Modal content -->
  <div class="job_detail_modal-content" style="overflow-x:auto;">
    <span class="job_detail_close">&times;</span>
    <div class="job_application_form">
 	
	    <form enctype="multipart/form-data" id="job_detail_form" method="post">
	    	<input name="security" value="<?php echo wp_create_nonce("uploadingFile"); ?>" type="hidden">
				<input name="action" value="job_detail_form" type="hidden"/>
	    	<input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
	    	<input type="hidden" name="resume_url" value="<?php echo $resume_url; ?>">
	    	<p class="upload_err"></p>
	    	<table id="job_detail_table">
	    		<tr>
	    			<td style="width: 30%">Full Name*</td>
	    			<td><input type="text" name="job_detail_full_name" class="htjl_wd" value="<?php echo $username; ?>" required></td>
	    		</tr>
	    		<tr>
	    			<td style="width: 30%">Email address*</td>
	    			<td><input type="email" name="job_detail_email" class="htjl_wd" value="<?php echo $useremail; ?>" required></td>
	    		</tr>
	    		<?php if ( is_user_logged_in() ) { ?>
	    		<tr>
	    			<td style="width: 30%">Online Resume <small>(optional)</small></td>
	    			<td>
	    				<select name="job_detail_online_resume" id="job_detail_online_resume" class="htjl_wd">
	    					<option value="null">Select Resume</option>
	    					<?php if ( !empty($file_name) ) { ?>
	    					<option value="1"><?php echo $file_name; ?></option>
	    					<?php } ?>
	    				</select>
	    			</td>
	    		</tr>
	    		<?php } ?>
	    		<tr>
	    			<td style="width: 30%">Upload a different Resume</td>
	    			<td class="htjl_upload_file_td">
	    				<small>(Your application will be submitted using this resume instead)</small>
	    				<div class="htjl-file-area">
		    				<input type="file" name="job_detail_resume2" id="job_detail_resume2" accept="application/pdf">
		    				<div class="file-dummy">
						      <div class="htjl_upload_success">Choose a file</div>
						      
						    </div>
		    				<small>Only .pdf is allowed</small>
		    			</div>
	    			</td>
	    		</tr>
	    	</table>
	    	<div class="btn_center">
	    		<button class="btn_send_application">Send Application</button>
	    	</div>
	    </form>

    </div><!-- .job_application_form -->
  </div>

</div><!-- #job_detail_myModal -->
<!-- Model ends here -->

<?php
} //endif
?>
<script type="text/javascript">
	/**********************************
	 * Model popup
	 **********************************/
	// Get the modal
	var modal = document.getElementById("job_detail_myModal");

	// Get the button that opens the modal
	var btn = document.getElementById("btn_job_apply");
	var btn2 = document.getElementById("btn_job_apply2");

	// Get the <span> element that closes the modal
	var span = document.getElementsByClassName("job_detail_close")[0];

	// When the user clicks the button, open the modal 
	btn.onclick = function() {
	  modal.style.display = "block";
	}
	btn2.onclick = function() {
	  modal.style.display = "block";
	}

	// When the user clicks on <span> (x), close the modal
	span.onclick = function() {
	  modal.style.display = "none";
	}

	// When the user clicks anywhere outside of the modal, close it
	window.onclick = function(event) {
	  if (event.target == modal) {
	    modal.style.display = "none";
	  }
	}
</script>