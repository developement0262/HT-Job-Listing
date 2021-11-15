<?php 

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if

$group_id = bp_get_group_id();
$company_name = bp_get_group_name();

// $company_id = '86c06169-6407-469d-88aa-678d992eed52';
$company_id = 'fccabcd5-e667-1234-82f5-20yuioa0daa3';
$endpoint = 'companies/'.$company_id;	
$get_listing = htjl_job_listing($endpoint);

$count = 1;
$cover_img = bp_get_group_cover_url();
if ( empty($cover_img) ) {
	$img = 'null'; 
}else{
	$cover_img = base64_encode($cover_img);
	$img = $cover_img;
}


$avatar_options = array ( 'item_id' => $group_id, 'object' => 'group', 'type' => 'full', 'avatar_dir' => 'group-avatars', 'alt' => 'Group avatar', 'class' => 'avatar', 'width' => 50, 'height' => 50, 'html' => false );

$avatar = bp_core_fetch_avatar($avatar_options);

$group_id = base64_encode($group_id);

?>
<div class="htjl_container">

	<!-- <div class="htjl_heading"><h2>Open Jobs</h2></div> -->
	<?php foreach($get_listing->jobs as $jobs){ ?>
		<?php if ( $count == 6 ) { break; } ?>
		<!-- <input type="hidden" name="job_id" value="<?php //echo $jobs->id; ?>">
		<input type="hidden" name="cover" value="<?php //echo $img; ?>"> -->
		<div class="htjl_listing">
			<div class="job_avatar">
				<img src="<?php echo $avatar; ?>" alt="Logo"></img>
			</div>
			<div class="job-listing-details">
				
			<div class="job_name">
				<div class="item-avatar">
					<a href="<?php echo site_url().'/job-detail/?job_id='.$jobs->id.'&company_name='.$company_name.'&group_id='.$group_id ?>"><?php echo $jobs->title; ?></a>
				</div>
			</div>
			<div class="job_location">
				<i class="bb-icon-map-pin" aria-hidden="true"></i> <?php echo $jobs->location; ?>
			</div><!-- .job_location -->
			</div>
		</div><!-- .htjl_listing -->
	<?php $count++; } ?>
	<p class="htjl_view_more_jobs">
		<a href="<?php echo site_url().'/jobs/?company_name='.$company_name; ?>">View More Jobs</a>
	</p>
</div><!-- .htjl_container -->
<script type="text/javascript">
	jQuery(document).ready(function(){
		var img_url = '<?php echo $img; ?>';
		var group_id = '<?php echo $group_id; ?>';
		
		jQuery.ajax({
	 		url:htjl.ajaxurl,
			type:"POST",
			data: {
				action: 'htjl_add_group_data',
				img_url: img_url,
				group_id: group_id
			},
			success : function( response ){
				console.log(response);
			},
	 	});
	});
</script>