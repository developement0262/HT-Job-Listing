<?php 

// If this file is called directly, abort. //
if ( ! defined( 'ABSPATH' ) ) {die;} // end if

global $wpdb;
$company_name = '';
$search_val = '';
$checked = '';
$check_location = $check_job_track = $check_tech_stack = $check_job_type = $check_company = $check_industry = array();
$count_location = $count_job_track = $count_tech_stack = $count_job_type = $count_company = $count_industry = 0;
$user_email = '';
$text_filter = '';
$down_arrow = site_url() . '/wp-content/plugins/htjl/assets/img/down-arrow.png';
$loader = site_url() . '/wp-content/plugins/htjl/assets/img/loader.gif';

if ( is_user_logged_in() ) {
   $email = wp_get_current_user();
   $user_email = $email->data->user_email;
}

if ( isset($_REQUEST['company_name']) ) {
	$company_name = $_REQUEST['company_name'];
}else{
	$company_name = '';
}


$all_parameters = isset($_REQUEST['search']) || isset($_REQUEST['location']) || isset($_REQUEST['job_track']) || isset($_REQUEST['tech_stack']) || isset($_REQUEST['job_type']) || isset($_REQUEST['company']) || isset($_REQUEST['industry']) || isset( $_REQUEST['sort_by'] );

if ( $all_parameters ) {

   if ( is_user_logged_in() ) {
      $endpoint = 'list_jobs?user_email=' . $user_email . '&search_query=' . $_REQUEST['search'] . '&location=' . $_REQUEST['location'] . '&job_track=' . $_REQUEST['job_track'] . '&tech_stack=' . $_REQUEST['tech_stack'] . '&job_type=' . $_REQUEST['job_type'] . '&company=' . $_REQUEST['company'] . '&industry=' . $_REQUEST['industry'] . '&sort='. $_REQUEST['sort_by'];
      $search_val = $_REQUEST['search'];
   }else{
      $endpoint = 'list_jobs?search_query=' . $_REQUEST['search'] . '&location=' . $_REQUEST['location'] . '&job_track=' . $_REQUEST['job_track'] . '&tech_stack=' . $_REQUEST['tech_stack'] . '&job_type=' . $_REQUEST['job_type'] . '&company=' . $_REQUEST['company'] . '&industry=' . $_REQUEST['industry'] . '&sort='. $_REQUEST['sort_by'];
      $search_val = $_REQUEST['search'];
   }

   if ( $_REQUEST['search'] != '' && ($_REQUEST['location'] != '' || $_REQUEST['job_track'] != '' || $_REQUEST['tech_stack'] != '' || $_REQUEST['job_type'] != '' || $_REQUEST['company'] != '' || $_REQUEST['industry'] != '') ) {

      $text_filter = " based on your filters and search for '".$_REQUEST['search']."'";
      $search_val = $_REQUEST['search'];

   }elseif ( $_REQUEST['location'] != '' || $_REQUEST['job_track'] != '' || $_REQUEST['tech_stack'] != '' || $_REQUEST['job_type'] != '' || $_REQUEST['company'] != '' || $_REQUEST['industry'] != '' ){

      $text_filter = ' based on your search filters';
   }elseif ( $_REQUEST['search'] != '' ){

      $text_filter = " based on your search term '".$_REQUEST['search']."'";
      $search_val = $_REQUEST['search'];

   }else{

      $text_filter = "";

   }

   $check_location = explode(',', $_REQUEST['location']);
   if ( !empty($check_location[0]) ) {$count_location = count($check_location);}

   $check_job_track = explode(',', $_REQUEST['job_track']);
   if ( !empty($check_job_track[0]) ) {$count_job_track = count($check_job_track);}

   $check_tech_stack = explode(',', $_REQUEST['tech_stack']);
   if ( !empty($check_tech_stack[0]) ) {$count_tech_stack = count($check_tech_stack);}

   $check_job_type = explode(',', $_REQUEST['job_type']);
   if ( !empty($check_job_type[0]) ) {$count_job_type = count($check_job_type);}

   $check_company = explode(',', $_REQUEST['company']);
   if ( !empty($check_company[0]) ) {$count_company = count($check_company);}

   $check_industry = explode(',', $_REQUEST['industry']);
   if ( !empty($check_industry[0]) ) {$count_industry = count($check_industry);}
   

}
else{
	$endpoint = 'list_jobs?company=' . $company_name . '&user_email='. $user_email;
	$search_val = '';
}

$get_job_list = htjl_job_listing($endpoint);
$total_jobs_found = $get_job_list->total;

// Get skills
$htjl_user_skills = $wpdb->prefix . 'htjl_user_skills';
$user_id = get_current_user_id();
$get_skills = $wpdb->get_results( "SELECT * FROM $htjl_user_skills WHERE user_id = $user_id" );

// Get filters
$get_filters = htjl_job_listing('get_filters');

// Checked save jobs
$htjl_saved_jobs = $wpdb->prefix . 'htjl_saved_jobs';
$save_jobs = $wpdb->get_results( "SELECT * FROM $htjl_saved_jobs WHERE user_id = $user_id" );

// Get user detail
// get_user_details/email
/*if ( is_user_logged_in() ) {
   $current_user = wp_get_current_user();
   $endpoint = 'get_user_details/email=' . $current_user->user_email;
   $get_user_details = htjl_job_listing($endpoint);
   $user_skills_chck = $get_user_details->skills[0];
}*/

?>
<style type="text/css">
	.ui-widget-content {
	    border: 1px solid #000;
	    width: 340px !important;
	    background: #fff;
	}
   .total__jobs_title_row select{
      background-image: url(<?php echo $down_arrow; ?>);
      appearance: inherit;
      background-size: 15px 15px;
      width: 220px;
   }
   .table__loader__bg{
      display: none;
   }
</style>

<div class="main__tabs">
<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <a class="nav-link active" id="jobs-tab" data-toggle="tab" href="#jobs" role="tab" aria-controls="jobs" aria-selected="true">Jobs</a>
  </li>
  <?php if ( is_user_logged_in() ) { ?>
     <li class="nav-item" role="presentation">
       <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Job Application</a>
     </li>
     <li class="nav-item" role="presentation">
       <a class="nav-link" id="save_job-tab" data-toggle="tab" href="#save_job" role="tab" aria-controls="save_job" aria-selected="false">Saved Jobs</a>
      </li>
   <?php } ?>
</ul><!-- #myTab -->
<div class="tab-content" id="myTabContent">
  <div class="tab-pane fade show active" id="jobs" role="tabpanel" aria-labelledby="jobs-tab">
      <div class="main__box">
         <form method="post" id="main__filter_form" action="<?php echo site_url().'/jobs/' ?>">
            <input type="hidden" id="endpoint" value="<?php echo $endpoint; ?>">
            <div class="container-fluid">
               <div class="search__input">
                  <div class="input-group mb-3 mt-4">
                     <div class="search__icon">
                        <button type="submit" class="btn">
                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search"
                              viewBox="0 0 16 16">
                              <path
                                 d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                           </svg>
                        </button>
                     </div>
                     <!-- .search__icon -->
                     <input type="text" class="form-control" value="<?php echo $search_val; ?>" name="search" id="search__input" placeholder="Search Jobs, Companies and More.." />
                  </div>
               </div>
               <!-- .search__input -->
               <div class="filter_by">
                  <div class="filter_title">
                     <h6>Filter By</h6>
                  </div>
                  <div class="filter__by__tag">
                     <div class="row">
                        <div class="tag__here">
                           <div class="dropdown htjl__location">
                              <button type="button" class="btn mt-2"  type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                 <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                       class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
                                       <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z" />
                                    </svg>
                                 </div>
                                 <div class="text">Location </div>
                                 <div class="span__round location">
                                    <span><?php echo $count_location; ?></span>
                                 </div>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                 <div class="text__area__inner mt-1">
                                    <input type="text" class="dropdown__location" value="<?php if(isset($_REQUEST['location'])){echo $_REQUEST['location'];} ?>" name="location" placeholder="Select Locations" readonly/>
                                    <div class="dropdown-content__location dropdown-filter">
                                       <ul>
                                          <?php foreach($get_filters->location as $location){ 
                                                if ( in_array($location, $check_location) ) {
                                                   echo '<li><input checked="checked" type="checkbox" value="'.$location.'" /><span>'.$location.'</span></li>';
                                                }else{
                                                   echo '<li><input type="checkbox" value="'.$location.'" /><span>'.$location.'</span></li>';
                                                }
                                             ?>
                                             
                                          <?php } ?>
                                       </ul>
                                    </div>
                                    <a href="javascript: void(0);" class="clear_selection clear__location">Clear Selections</a>
                                 </div>
                              </div>
                           </div>
                           <div class="dropdown">
                              <button type="button" class="btn mt-2"  id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                 <div class="icon">
                                    <svg enable-background="new 0 0 24 24" fill="currentColor" height="16" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg">
                                       <circle cx="12" cy="12" r="5"></circle>
                                       <path d="m23 11h-1.051c-.47-4.717-4.232-8.479-8.949-8.949v-1.051c0-.552-.447-1-1-1s-1 .448-1 1v1.051c-4.717.47-8.479 4.232-8.949 8.949h-1.051c-.553 0-1 .448-1 1s.447 1 1 1h1.051c.471 4.717 4.232 8.479 8.949 8.949v1.051c0 .552.447 1 1 1s1-.448 1-1v-1.051c4.717-.471 8.479-4.232 8.949-8.949h1.051c.553 0 1-.448 1-1s-.447-1-1-1zm-10.014 8.933c-.036-.519-.457-.933-.986-.933s-.95.414-.986.933c-3.622-.448-6.498-3.324-6.946-6.946.519-.037.932-.459.932-.987s-.413-.95-.933-.986c.448-3.622 3.324-6.498 6.946-6.946.037.518.458.932.987.932s.95-.414.986-.933c3.622.448 6.498 3.324 6.946 6.946-.519.037-.932.459-.932.987s.413.95.933.986c-.449 3.623-3.324 6.498-6.947 6.947z"></path>
                                    </svg>
                                 </div>
                                 <div class="text">Job Track</div>
                                 <div class="span__round jobtrack">
                                    <span><?php echo $count_job_track; ?></span>
                                 </div>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                 <div class="text__area__inner mt-1">
                                    <input type="text" class="dropdown__jobtrack" value="<?php if(isset($_REQUEST['job_track'])){echo $_REQUEST['job_track'];} ?>" name="job_track" placeholder="Select Job Track" readonly />
                                    <div class="dropdown-content__jobtrack dropdown-filter">
                                       <ul>
                                          <?php foreach($get_filters->job_track as $job_track){ 
                                             if ( in_array($job_track, $check_job_track) ) {
                                                   echo '<li><input checked="checked" type="checkbox" value="'.$job_track.'" /><span>'.$job_track.'</span></li>';
                                                }else{
                                                   echo '<li><input type="checkbox" value="'.$job_track.'" /><span>'.$job_track.'</span></li>';
                                                }
                                          ?>
                                             <!-- <li><input type="checkbox"/><span><?php //echo $job_track; ?></span></li> -->  
                                          <?php } ?>
                                       </ul>
                                    </div>
                                    <a href="javascript: void(0);" class="clear_selection clear__jobtrack">Clear Selections</a>
                                 </div>
                              </div>
                           </div>
                           <div class="dropdown htjl__techstack">
                              <button type="button" class="btn mt-2"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                 <div class="icon">
                                    <svg id="Capa_1" enable-background="new 0 0 509.738 509.738" height="16" viewBox="0 0 509.738 509.738" width="16" xmlns="http://www.w3.org/2000/svg">
                                       <g id="XMLID_1_">
                                          <path id="XMLID_290_" d="m165.182 402.647 237.464-237.464c28.37 7.996 60.11.85 82.435-21.475 24.879-24.879 30.932-61.455 18.152-92.021l-55.086 55.086-45.182-45.182 55.086-55.086c-30.566-12.78-67.142-6.727-92.021 18.152-22.62 22.62-29.675 54.909-21.167 83.561l-236.645 236.645c-28.653-8.507-60.942-1.453-83.561 21.167-24.879 24.879-30.932 61.455-18.152 92.021l55.086-55.086 45.182 45.182-55.086 55.086c30.566 12.78 67.142 6.727 92.021-18.152 22.324-22.324 29.47-54.064 21.474-82.434z"></path>
                                       </g>
                                    </svg>
                                 </div>
                                 <div class="text">Tech Stack</div>
                                 <div class="span__round techstack">
                                    <span><?php echo $count_tech_stack; ?></span>
                                 </div>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                 <div class="text__area__inner mt-1">
                                    <input type="text" class="dropdown__techstack" value="<?php if(isset($_REQUEST['tech_stack'])){echo $_REQUEST['tech_stack'];} ?>" name="tech_stack" placeholder="Select Tech Stack" readonly />
                                    <div class="dropdown-content__techstack dropdown-filter">
                                       <ul>
                                          <?php foreach($get_filters->tech_stack as $tech_stack){ 
                                             if ( in_array($tech_stack, $check_tech_stack) ) {
                                                echo '<li><input checked="checked" type="checkbox" value="'.$tech_stack.'" /><span>'.$tech_stack.'</span></li>';
                                             }else{
                                                echo '<li><input type="checkbox" value="'.$tech_stack.'" /><span>'.$tech_stack.'</span></li>';
                                             }
                                          ?>
                                             <!-- <li><input type="checkbox"/><span><?php //echo $tech_stack; ?></span></li> -->  
                                          <?php } ?>
                                       </ul>
                                    </div>
                                    <a href="javascript: void(0);" class="clear_selection clear__techstack">Clear Selections</a>
                                 </div>
                              </div>
                           </div>
                           <div class="dropdown">
                              <button type="button" class="btn mt-2"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                 <div class="icon">
                                    <svg version="1.1" height="16" width="16" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                                       <g>
                                          <g>
                                             <g>
                                                <path d="M444.862,109.779L338.221,3.138C336.29,1.201,333.621,0,330.667,0h-224C83.146,0,64,19.135,64,42.667v426.667
                                                   C64,492.865,83.146,512,106.667,512h298.667C428.854,512,448,492.865,448,469.333v-352
                                                   C448,114.379,446.799,111.71,444.862,109.779z M138.667,106.667h128c5.896,0,10.667,4.771,10.667,10.667
                                                   c0,5.896-4.771,10.667-10.667,10.667h-128c-5.896,0-10.667-4.771-10.667-10.667C128,111.438,132.771,106.667,138.667,106.667z
                                                   M295.479,402.271C249.042,448,238.229,448,234.667,448c-13.25,0-18-15.208-22.917-31.5c-7.438,15.094-16,31.5-30.417,31.5
                                                   c-7.417,0-16.292-4.885-24.646-12.323c-3.979,3.958-7.938,7.427-11.625,10.188c-1.917,1.438-4.167,2.135-6.396,2.135
                                                   c-3.229,0-6.438-1.469-8.542-4.271c-3.521-4.708-2.563-11.396,2.146-14.927c3.375-2.542,6.813-5.604,10.188-8.979
                                                   C133.979,408.125,128,394.792,128,384c0-24.344,19.417-42.667,32-42.667c12.375,0,32,8.688,32,32
                                                   c0,13.583-9.438,31.156-21.188,46.333c4.354,4.063,8.313,6.76,10.625,7c2.354-1.656,8.833-14.844,11.958-21.177
                                                   c6.375-12.938,10.583-21.49,19.938-21.49c10.938,0,14.333,11.344,18.667,25.708c1.271,4.26,3.375,11.281,5.229,15.479
                                                   c7.833-5.292,27.604-22.688,43.292-38.125c4.208-4.115,10.958-4.094,15.083,0.125
                                                   C299.729,391.385,299.688,398.135,295.479,402.271z M373.333,320H224c-5.896,0-10.667-4.771-10.667-10.667
                                                   c0-5.896,4.771-10.667,10.667-10.667h149.333c5.896,0,10.667,4.771,10.667,10.667C384,315.229,379.229,320,373.333,320z
                                                   M373.333,256H138.667c-5.896,0-10.667-4.771-10.667-10.667c0-5.896,4.771-10.667,10.667-10.667h234.667
                                                   c5.896,0,10.667,4.771,10.667,10.667C384,251.229,379.229,256,373.333,256z M373.333,192H138.667
                                                   c-5.896,0-10.667-4.771-10.667-10.667c0-5.896,4.771-10.667,10.667-10.667h234.667c5.896,0,10.667,4.771,10.667,10.667
                                                   C384,187.229,379.229,192,373.333,192z M362.667,106.667c-11.771,0-21.333-9.573-21.333-21.333V36.417l70.25,70.25H362.667z"></path>
                                                <path d="M160,362.667c-1.125,0.708-10.667,8.323-10.667,21.333c0,5.458,3.083,12.313,7.417,18.917
                                                   c8.167-11.302,13.917-22.781,13.917-29.583C170.667,363.177,161.771,362.667,160,362.667z"></path>
                                             </g>
                                          </g>
                                       </g>
                                    </svg>
                                 </div>
                                 <div class="text">Job Type</div>
                                 <div class="span__round jobtype">
                                    <span><?php echo $count_job_type; ?></span>
                                 </div>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                 <div class="text__area__inner mt-1">
                                    <input type="text" class="dropdown__jobtype" value="<?php if(isset($_REQUEST['job_type'])){echo $_REQUEST['job_type'];} ?>" name="job_type" placeholder="Select Job Type" readonly />
                                    <div class="dropdown-content__jobtype dropdown-filter">
                                       <ul>
                                          <?php foreach($get_filters->job_type as $job_type){
                                             if ( in_array($job_type, $check_job_type) ) {
                                                echo '<li><input checked="checked" type="checkbox" value="'.$job_type.'" /><span>'.$job_type.'</span></li>';
                                             }else{
                                                echo '<li><input type="checkbox" value="'.$job_type.'" /><span>'.$job_type.'</span></li>';
                                             }
                                          ?>
                                             <!-- <li><input type="checkbox"/><span><?php //echo $job_type; ?></span></li> -->  
                                          <?php } ?>
                                       </ul>
                                    </div>
                                    <a href="javascript: void(0);" class="clear_selection clear__jobtype">Clear Selections</a>
                                 </div>
                              </div>
                           </div>
                           <div class="dropdown">
                              <button type="button" class="btn mt-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                 <div class="icon">
                                    <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16" height="16" viewBox="0 0 31.666 31.666" style="enable-background:new 0 0 31.666 31.666;" xml:space="preserve">
                                       <g>
                                          <path d="M11.452,31.666v-5.879h8.763v5.879h6.604V0H4.847v31.666H11.452z M20.215,2.909h3.696v3.696h-3.696V2.909z M20.215,8.282
                                             h3.696v3.697h-3.696V8.282z M20.215,13.656h3.696v3.695h-3.696V13.656z M20.215,19.028h3.696v3.698h-3.696V19.028z M13.986,2.909
                                             h3.697v3.696h-3.697V2.909z M13.986,8.282h3.697v3.697h-3.697V8.282z M13.986,13.656h3.697v3.695h-3.697V13.656z M13.986,19.028
                                             h3.697v3.698h-3.697V19.028z M7.757,2.909h3.696v3.696H7.757V2.909z M7.757,8.282h3.696v3.697H7.757V8.282z M7.757,13.656h3.696
                                             v3.695H7.757V13.656z M7.757,19.028h3.696v3.698H7.757V19.028z"></path>
                                       </g>
                                    </svg>
                                 </div>
                                 <div class="text">Company</div>
                                 <div class="span__round company">
                                    <span><?php echo $count_company; ?></span>
                                 </div>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                 <div class="text__area__inner mt-1">
                                    <input type="text" class="dropdown__company" value="<?php if(isset($_REQUEST['company'])){echo $_REQUEST['company'];} ?>" name="company" placeholder="Select Company" readonly />
                                    <div class="dropdown-content__company dropdown-filter">
                                       <ul>
                                          <?php foreach($get_filters->company as $company){ 
                                             if ( in_array($company, $check_company) ) {
                                                echo '<li><input checked="checked" type="checkbox" value="'.$company.'" /><span>'.$company.'</span></li>';
                                             }else{
                                                echo '<li><input type="checkbox" value="'.$company.'" /><span>'.$company.'</span></li>';
                                             }
                                          ?>
                                             <!-- <li><input type="checkbox"/><span><?php //echo $company; ?></span></li> -->  
                                          <?php } ?>
                                       </ul>
                                    </div>
                                    <a href="javascript: void(0);" class="clear_selection clear__company">Clear Selections</a>
                                 </div>
                              </div>
                           </div>
                           <div class="dropdown">
                              <button type="button" class="btn mt-2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                 <div class="icon">
                                    <svg version="1.1" height="16" width="16" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 54.303 54.303" style="enable-background:new 0 0 54.303 54.303;" xml:space="preserve">
                                       <g>
                                          <g>
                                             <path d="M51.687,43.313l-1.72-36.849H38.788l-1.224,36.849H34.33l0.645-19.394h-5.236v-8.404l-13.576,8.404v-8.404L1.939,23.919
                                                v19.394H0v4.525h54.303v-4.525H51.687z M12.283,36.849H7.758v-4.525h4.525V36.849z M20.04,36.849h-4.525v-4.525h4.525V36.849z
                                                M27.798,36.849h-4.525v-4.525h4.525V36.849z"></path>
                                          </g>
                                       </g>
                                    </svg>
                                 </div>
                                 <div class="text">Industry</div>
                                 <div class="span__round industry">
                                    <span><?php echo $count_industry; ?></span>
                                 </div>
                              </button>
                              <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                 <div class="text__area__inner mt-1">
                                    <input type="text" class="dropdown__industry" value="<?php if(isset($_REQUEST['industry'])){echo $_REQUEST['industry'];} ?>" name="industry" placeholder="Select Industry" readonly />
                                    <div class="dropdown-content__industry dropdown-filter">
                                       <ul>
                                          <?php foreach($get_filters->industry as $industry){
                                             if ( in_array($industry, $check_industry) ) {
                                                echo '<li><input checked="checked" type="checkbox" value="'.$industry.'" /><span>'.$industry.'</span></li>';
                                             }else{
                                                echo '<li><input type="checkbox" value="'.$industry.'" /><span>'.$industry.'</span></li>';
                                             } 
                                          } ?>
                                       </ul>
                                    </div>
                                    <a href="javascript: void(0);" class="clear_selection clear__industry">Clear Selections</a>
                                 </div>
                              </div>
                           </div>
                           <div class="dropdown filter__btn">
                              <button class="btn-filter">Filter</button>
                           </div>
                        </div><!-- .tag__here -->
                     </div><!-- .row -->
                     <p class="clear__all__selections">Clear All</p>
                  </div>
                  <!-- .filter_by -->
                  <?php if ( is_user_logged_in() ) { ?>
                        
                     <?php if ( empty($user_skills_chck) ){ ?>
                        <div class="main__form">
                           <div class="upload-box">
                              <h3>Upload a copy of your resume</h3>
                              <p>We highly recommend you to leave a copy of your resume with us. It speeds up the process of job applications on our Community!</p>
                              <div class="upload-input-outer">
                                 <div class="upload-input">
                                    <?php 
                                       $user_id = get_current_user_id();
                                       $htjl_user_resume_detail = $wpdb->prefix . 'htjl_user_resume_detail';
                                       $res = $wpdb->get_row("SELECT * FROM $htjl_user_resume_detail WHERE user_id = $user_id ");
                                       if ($res != NULL) {$resume_name = $res->resume_name;}else{$resume_name='';}
                                       ?>
                                    <input type="text" name="user_resume_name" value="<?php echo $resume_name; ?>" id="user_resume_name" readonly>
                                    <span class="upload-cancle">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg"
                                          viewBox="0 0 16 16">
                                          <path
                                             d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z" />
                                       </svg>
                                    </span>
                                    <div class="upload-btn-row">
                                       <button class="upload-resume-btn">Upload Resume</button>
                                       <input type="file" id="upload-box-resume" name="upload-box-resume" accept="application/pdf">
                                    </div>
                                 </div>
                              </div>
                           </div><!-- .upload-box -->
                           <h3 class="main__title">What are your top skills ?</h3>
                           <p class="main__title_p">By uploading your resume, we have parsed and automatically identified some of your top skills! You may add or remove the skills as required.</p>
                           <div class="row">
                              <div class="col-12">
                                 <div class="main__input__box">
                                    <div class="main__input__box-inner">
                                       <!-- <input type="text" class="form-control" id="skills_input"
                                          placeholder="Input your top skills" /> -->
                                       <select name="langOpt[]" multiple id="langOpt">
                                          <option value="C++">C++</option>
                                          <option value="C#">C#</option>
                                          <option value="Java">Java</option>
                                          <option value="Objective-C">Objective-C</option>
                                          <option value="JavaScript">JavaScript</option>
                                          <option value="Perl">Perl</option>
                                          <option value="PHP">PHP</option>
                                          <option value="Ruby on Rails">Ruby on Rails</option>
                                          <option value="Android">Android</option>
                                          <option value="iOS">iOS</option>
                                          <option value="HTML">HTML</option>
                                          <option value="XML">XML</option>
                                       </select>
                                    </div>
                                    <button id="htjl_skill_add" style="display: none;">Add</button>
                                    <div class="main__input__box-tags htjl_skills">
                                       <?php if ( !empty($get_skills) ) {
                                          foreach ($get_skills as $skill) {
                                             ?>
                                       <a href="javascript: void(0);">
                                          <?php echo $skill->skills; ?>
                                          <span class="remove-tag" id="<?php echo $skill->id; ?>">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#000000" class="bi bi-plus-lg"
                                                viewBox="0 0 16 16">
                                                <path
                                                   d="M8 0a1 1 0 0 1 1 1v6h6a1 1 0 1 1 0 2H9v6a1 1 0 1 1-2 0V9H1a1 1 0 0 1 0-2h6V1a1 1 0 0 1 1-1z" />
                                             </svg>
                                          </span>
                                       </a>
                                       <?php
                                          }
                                          } ?>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="upload-btn-row mt-20">
                              <button type="button" class="upload-resume-save">Save</button>
                           </div>
                        </div><!-- .main__form -->
                     <?php }else{ ?>
                        <div class="main__recommendation">
                           <p>
                              <img src="<?php echo site_url() . '/wp-content/plugins/htjl/assets/img/mark.png'; ?>">Recommended jobs are based on the information and resume you have saved in your profile. You may make changes to receive better recommendations <a href="javascript:void(0);">here</a>.
                           </p>
                        </div>
                     <?php } ?>

                  <?php } //endif ?>
                  <div class="total__jobs">
                     <div class="total__jobs_title_row">
                        <?php if ( !empty($total_jobs_found) ) { ?>
                           <h3><?php echo $total_jobs_found; ?> Jobs <?php echo $text_filter; ?></h3>
                        <?php }else{ ?>
                           <h3>0 Jobs <?php echo $text_filter; ?></h3>
                        <?php } ?>
                        <div>
                           <label>Sort By</label>
                              <select class="form-control sort__by" name="sort_by">
                                 <option <?php if ( isset($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'relevance' ) {echo "selected='selected'";} ?> value="relevance">Relevance</option>
                                 <option <?php if ( isset($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'most-recent' ) {echo "selected='selected'";} ?> value="most-recent">Most Recent</option>
                                 <option <?php if ( isset($_REQUEST['sort_by']) && $_REQUEST['sort_by'] == 'current-location' ) {echo "selected='selected'";} ?> value="current-location">Current Location</option>
                              </select>
                        </div>
                     </div>
                     <!-- .total__jobs_title_row -->
                     
                        <div class="total__jobs_list_wrp">
                           <div class="table__loader__bg">
                              <div class="table__loader">
                                 <img src="<?php echo $loader;?>">
                              </div>
                           </div>
                           <?php 
                              if ( !empty( $get_job_list->results ) ){
                                 $count = 1;
                                 foreach( $get_job_list->results as $result ){
                                    // Get group id by name
                                    $group = groups_get_groups( array( 'slug'=> $result->company_slug ) );
                                    $company_id = $group['groups'][0]->id;
                                    if ( !is_user_logged_in() && $count == 6 ) {
                                       break;
                                    }
                                    ?>
                           <div class="total__jobs_list">
                              <div class="total__jobs_list_box">
                                 <div class="total__jobs_list_box_left">
                                    <div class="jobs_list_img">
                                       <img src="<?php echo $result->image; ?>" class="img-fluid">
                                    </div>
                                    <div class="jobs_list_details">
                                       <h3>
                                          <a href="<?php echo site_url().'/job-detail/?job_id='.$result->job_id.'&company_name='.$result->company_slug.'&group_id='.base64_encode($company_id); ?>"><?php echo $result->job_title; ?></a>
                                       </h3>
                                       <ul>
                                          <li>
                                             <span class="jobs-type"><b><?php echo $result->company_name; ?></b></span>
                                          </li>
                                          &bull;
                                          <li>
                                             <span class="jobs-location"><?php echo $result->location; ?></span>
                                          </li>
                                          <?php if ( $result->fast_response == true ) {
                                             ?>
                                          <li>
                                             <span class="fast-responce"><i class="fa fa-bolt"></i>Fast Response</span>
                                          </li>
                                          <?php
                                             } ?>
                                       </ul>
                                    </div>
                                     <?php if ( is_user_logged_in() && $result->match >= 80 ) { ?>
                                       <div class="top__jobs">
                                          <?php $top = site_url() . '/wp-content/plugins/htjl/assets/img/top.png'; ?>
                                          <img src="<?php echo $top; ?>">
                                       </div>
                                    <?php } ?>
                                 </div>

                                 <div class="total__jobs_list_box_right">
                                   
                                    <div class="main__input__box-tags">
                                       <ul>
                                          <?php 
                                             $skills = $result->skills;
                                             $skills = explode(',', $skills[0]);
                                             foreach ($skills as $skill) { 
                                                ?>
                                          <li><a href="javascript:void(0);"><?php echo $skill; ?></a></li>
                                          <?php
                                             }
                                             ?>
                                       </ul>
                                    </div>
                                    <div class="main__input__save-tags">
                                       <?php
                                       $get_save_job = $wpdb->get_row("SELECT * FROM $htjl_saved_jobs WHERE user_id = $user_id AND job_id = '".$result->job_id."' ");
                                       if ( !empty($get_save_job) ) {
                                          ?>
                                          <a href="javascript: void(0);" class="remove_jobs" data-id="<?php echo $result->job_id; ?>"><i class="fa fa-bookmark" aria-hidden="true"></i></a>
                                          <?php
                                       }else{
                                          ?>
                                          <a href="javascript: void(0);" class="save_jobs" data-id="<?php echo $result->job_id; ?>"><i class="fa fa-bookmark-o" aria-hidden="true"></i></a>
                                          <?php
                                       }
                                       ?>
                                       
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <!-- .total__jobs_list -->
                           <?php
                              $count++;
                              } // end foreach
                              
                              if ( !is_user_logged_in() ) { 
                              $sign_in = site_url() . '/wp-content/plugins/htjl/assets/img/Signin.png';
                              $resume = site_url() . '/wp-content/plugins/htjl/assets/img/Resume.png';
                              ?>
                                 <div class="non-loggedin__box">
                                    <h4>Sign in or upload your resume to view our jobs</h4>
                                    <div class="row">
                                       <div class="col-lg-6 col-sm-12 col-xs-12 left_sign">
                                          <div class="box__wrap">
                                             <img src="<?php echo $sign_in; ?>" style="width: 70%;">
                                             <h4>Sign In</h4>
                                             <p>Sign in with us here on the community and get access to all our available jobs!</p>
                                             <div class="box__btn">
                                                <!-- <button class="verify_btn box_btn1 new_user_sign_in">Sign In</button> -->
                                                <a href="<?php echo site_url() . '/login'; ?>" class="verify_btn box_btn1 new_user_sign_in">Sign In</a>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-lg-6 col-sm-12 col-xs-12 right_sign">
                                          <div class="box__wrap left_side">
                                             <div class="loader__box">
                                                <img src="<?php echo $loader; ?>" class="img-fluid">
                                             </div>
                                             <img src="<?php echo $resume; ?>" style="width: 57%;">
                                             <h4>Upload Resume</h4>
                                             <p>Alternatively, upload your resume seamlessly and get highly matching job recommendations!</p>
                                             <div class="box__btn upload-btn-wrapper">
                                                <button class="btn htjl_upload_resume_btn">Upload resume</button>
                                                <input type="file" id="htjl_upload_resume" name="htjl_upload_resume" accept="application/pdf">
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              <?php }
                              
                              } // endif
                              else{
                                 echo '<h3>No Jobs Found!</h3>';
                              }
                              ?>
                        </div>

                     <!-- .total__jobs_list_wrp -->
                     <?php if ( is_user_logged_in() ) { ?>
                        <div id="htjl_pagination_container" class="simple-pagination">
                           <ul>
                              <?php
                              $total_pagination = ceil($total_jobs_found / 10);
                              if ( $get_job_list->prev_page == null ) {
                                 echo "<li class='active'><span class='current prev'>«</span></li>";
                              }else{
                                 echo "<li><a href='javascript:void(0);' data-id=".$get_job_list->prev_page." class='page-link prev'>«</a></li>";
                              }

                              for ($i=1; $i <= $total_pagination; $i++) {
   
                                 if ( $get_job_list->cur_page == $i ) {
                                    echo '<li><span class="current">'.$i.'</span></li>';
                                 }else{
                                    echo '<li><a href="javascript:void(0);" data-id='.$i.' class="page-link">'.$i.'</a></li>';
                                 }
                                 
                              }

                              if ( $get_job_list->next_page == null ) {
                                 echo "<li class='active'><span class='current next'>»</span></li>";
                              }else{
                                 echo "<li><a href='javascript:void(0);' data-id=".$get_job_list->next_page." class='page-link next'>»</a></li>";
                              } ?>
                           </ul>
                        </div>
                     <?php } ?>
                  </div>
                  <!-- .total__jobs -->
               </div>
               <!-- .container-fluid -->
            </div><!-- .container-fluid -->
         </form>
         
      </div><!-- .main-box -->
   </div>
  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">...</div>
  <div class="tab-pane fade" id="save_job" role="tabpanel" aria-labelledby="save_job-tab">
      <div class="main__box">
         <div class="container-fluid">
            <h2 class="saved_job_title">Your Saved Jobs</h2>
            <!-- .search__input -->
            <div class="filter_by">
               <div class="total__jobs">
                  <div class="total__jobs_list_wrp">
                  <?php 
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
                           ?>
                           <div class="total__jobs_list__save">
                              <div class="total__jobs_list_box">
                                 <div class="total__jobs_list_box_left">
                                    <div class="jobs_list_img">
                                       <img src="<?php echo $avatar; ?>" class="img-fluid">
                                    </div>
                                    <div class="jobs_list_details">
                                       <h3>
                                          <a href="<?php echo site_url().'/job-detail/?job_id='.$job_detail->id.'&company_name='.$job_detail->company_slug.'&group_id='.base64_encode($company_id); ?>"><?php echo $job_detail->title; ?></a>
                                       </h3>
                                       <ul>
                                          <li>
                                             <span class="jobs-type"><b><?php echo $company_name; ?></b></span>
                                          </li>
                                          &bull;
                                          <li>
                                             <span class="jobs-location"><?php echo $job_detail->location; ?></span>
                                          </li>
                                       </ul>
                                    </div>
                                 </div>

                                 <div class="total__jobs_list_box_right">
                                    <div class="main__input__box-tags">
                                       <ul>
                                          <?php 
                                             $skills = $job_detail->skills;
                                             $skills = explode(',', $skills[0]);
                                             foreach ($skills as $skill) { 
                                                ?>
                                          <li><a href="javascript:void(0);"><?php echo $skill; ?></a></li>
                                          <?php
                                             }
                                             ?>
                                       </ul>
                                    </div>
                                    <div class="main__input__save-tags">
                                       <a href="javascript: void(0);" class="remove_jobs" data-id="<?php echo $job_detail->id; ?>"><i class="fa fa-bookmark" aria-hidden="true"></i></a>
                                    </div>
                                 </div>
                              </div>
                           </div><!-- .total__jobs_list -->
                  <?php
                     $count++;
                           } // end foreach
                        } // end foreac
                     } // endif
                     else{

                        if ( !is_user_logged_in() ) {
                           echo '<p class="no__job_found">Please login to save your favorite jobs!</p>';
                        }else{
                           echo '<p class="no__job_found">No Saved Jobs!</p>';
                        }
                     }
                     ?>
                  </div><!-- .total__jobs_list_wrp -->
               </div><!-- .total__jobs -->
            </div><!-- .container-fluid -->
         </div><!-- .container-fluid -->
      </div><!-- .main-box -->
  </div>
</div>
</div>
<script type="text/javascript">
// Pagination
jQuery('.table__loader__bg').hide();
jQuery(document).on('click', 'a.page-link', function(){
   var id = jQuery(this).attr('data-id');
   var endpoint = '<?php echo $endpoint; ?>';
   get_job_list(id, endpoint);
});



function get_job_list(id=1, endpoint) {
   jQuery.ajax({
      type : "get",
      dataType : "json",
      url : htjl.ajaxurl,
      data : {
         action: "htjl_all_job_listing",
         listing_type: 'get_pagination_list',
         endpoint: endpoint + '&page=' + id,
      },
      beforeSend: function() {
         jQuery('.table__loader__bg').show();        
      },
      success : function( response ) {
         jQuery('.table__loader__bg').hide();  
         jQuery('#jobs .total__jobs_list_wrp').html(response.output);     
         jQuery('#htjl_pagination_container').html(response.page);     
      }
   });
}
</script>