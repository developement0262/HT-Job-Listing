jQuery(document).ready(function(){

	jQuery('div.loader__box').hide();

	// Pagination
	/*jQuery(document).on('click', 'a.page-link', function(){
	    var id = jQuery(this).attr('data-id');
	    jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'get_pagination_list',
				id: id,
			},
			success : function( response ) {
				// jQuery('div.loader__box').hide();
				// jQuery('.non-loggedin__box .row').html(response.output);       
			}
		});
	});*/
	/*var items = jQuery(".total__jobs_list_wrp .total__jobs_list");
    var numItems = items.length;
    var perPage = 5;

    items.slice(perPage).hide();

    jQuery('#htjl_pagination_container').pagination({
        items: numItems,
        itemsOnPage: perPage,
        prevText: "&laquo;",
        nextText: "&raquo;",
        onPageClick: function (pageNumber) {
            var showFrom = perPage * (pageNumber - 1);
            var showTo = showFrom + perPage;
            items.hide().slice(showFrom, showTo).show();
        }
    });*/


    // Upload resume
	jQuery(document).on('change', '#htjl_upload_resume', function(){
		var name = document.getElementById("htjl_upload_resume").files[0].name;
		var ext = name.split('.').pop().toLowerCase();
		var form_data = new FormData();
		
		if(jQuery.inArray(ext, ['gif','png','jpg','jpeg']) != -1){
		   	alert("Please use only PDF format!");
			return false;
		}
		form_data.append("file", document.getElementById('htjl_upload_resume').files[0]);
		form_data.append("action", "htjl_all_job_listing");
		form_data.append("listing_type", "upload_resume");
		// console.log(form_data);
		// return false;
		jQuery.ajax({
	 		url:htjl.ajaxurl,
			type:"POST",
			processData: false,
			contentType: false,
			data: form_data,
			beforeSend: function() {
		        jQuery('div.loader__box').show();
		        jQuery('button.btn.htjl_upload_resume_btn').attr('disabled', 'disabled');        
		    },
			success : function( response ){
				jQuery('div.loader__box').hide();
				var returnedData = JSON.parse(response);
				jQuery('.non-loggedin__box .row').html(returnedData.output);
			},
	 	});
	});
	

	jQuery(document).on('click', '.upload_thats_right', function(){
		jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'upload_thats_right',
			},
			beforeSend: function() {
		        jQuery('div.loader__box').show();
		        jQuery('button.upload_thats_right').prop('disabled', true);      
		        //jQuery('button.upload_thats_not_me').prop('disabled', true);       
		    },
			success : function( response ) {
				jQuery('div.loader__box').hide();
				jQuery('.non-loggedin__box .row').html(response.output);       
			}
		});
	});

	jQuery(document).on('click', '.upload_thats_not_me', function(){
		jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'upload_thats_not_me',
			},
			beforeSend: function() {
		        jQuery('div.loader__box').show();
		        jQuery('button.upload_thats_right').prop('disabled', true);      
		        //jQuery('button.upload_thats_not_me').prop('disabled', true);       
		    },
			success : function( response ) {
				jQuery('div.loader__box').hide();
				jQuery('.non-loggedin__box .row').html(response.output);       
			}
		});
	});

	/*jQuery(document).on('click', '.new_user_sign_in', function(){
		var url = localStorage.getItem('site_url');
		window.location.href = url + "/login";
	});*/

	jQuery(document).on('click', '.htjl_non_user_email_btn', function() {
		
		var new_email = jQuery('#htjl_non_user_email').val();
		jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'htjl_new_email',
				new_email: new_email,
			},
			beforeSend: function() {
		        jQuery('div.loader__box').show();
		        jQuery('button.verify_btn.htjl_non_user_email_btn').prop('disabled', true);       
		    },
			success : function( response ) {
				jQuery('div.loader__box').hide();
				jQuery('.non-loggedin__box .row').html(response.output);       
			}
		});
		
	});

	jQuery(document).on('click', 'span.upload-cancle', function(){
	    jQuery('input#user_resume_name').val('');
	})

	// Upload resume from skill box
	jQuery(document).on('change', '#upload-box-resume', function(){
		var name = document.getElementById("upload-box-resume").files[0].name;
		var ext = name.split('.').pop().toLowerCase();
		var form_data = new FormData();
		
		if(jQuery.inArray(ext, ['gif','png','jpg','jpeg']) != -1){
		   	alert("Please use only PDF format!");
			return false;
		}
		form_data.append("file", document.getElementById('upload-box-resume').files[0]);
		form_data.append("action", "htjl_all_job_listing");
		form_data.append("listing_type", "skill_box_upload_resume");
		jQuery.ajax({
	 		url:htjl.ajaxurl,
			type:"POST",
			processData: false,
			contentType: false,
			data: form_data,
			success : function( response ){
				var returnedData = JSON.parse(response);
				jQuery('input#user_resume_name').val(returnedData.name);
			},
	 	});
	});

	// Adding skills
	jQuery('#skills_input').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            jQuery('button#htjl_skill_add').trigger('click');
            return false;
        }
        event.stopPropagation();
    });
	jQuery(document).on('click', 'button#htjl_skill_add', function(){
	    var skill = jQuery('input#skills_input').val();
	    if ( skill == '' ) {
	    	alert('Please Enter your skill');
	    	return false;
	    }
	    jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'htjl_add_skill',
				skill: skill,
			},
			beforeSend: function() {
		        jQuery('div.loader__box').show();
		        jQuery('button#htjl_skill_add').attr('disabled', 'disabled');           
		    },
			success : function( response ) {
				jQuery('button#htjl_skill_add').prop("disabled", false);
				jQuery('input#skills_input').val('');
		        jQuery('div.main__input__box-tags.htjl_skills').html(response.output);  
			}
		});
	});

	// Delete skill
	jQuery(document).on('click', 'span.remove-tag', function(){
	    var id = jQuery(this).attr('id');
	    jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'htjl_delete_skill',
				id: id,
			},
			success : function( response ) {  
		        jQuery('div.main__input__box-tags.htjl_skills').html(response.output);  
			}
		});
	});

	// Adding filter checkbox into textbox

	// Location
	jQuery('.dropdown__location').click(function(){
		jQuery('.dropdown-content__location').fadeToggle();
	}); 
	jQuery(".dropdown-content__location input:checkbox").click(function() {
		var len = jQuery('.dropdown-content__location input[type=checkbox]').filter(':checked').length;
		jQuery('div.span__round.location span').text(len);
        var output = "";
        jQuery(".dropdown-content__location input:checked").each(function() {
            output += jQuery(this).next('span').text() + ",";
        });
        jQuery(".dropdown__location").val(output.trim().slice(0,-1));  
	}); 

	jQuery('a.clear__location').click(function(e){
		e.preventDefault();
		jQuery('.dropdown-content__location input:checkbox').removeAttr('checked');
		jQuery('div.span__round.location span').text('0');
		jQuery('input.dropdown__location').val('');
	});

	// Job Track
	jQuery('.dropdown__jobtrack').click(function(){
		jQuery('.dropdown-content__jobtrack').fadeToggle();
	}); 
	jQuery(".dropdown-content__jobtrack input:checkbox").click(function() {
			var len = jQuery('.dropdown-content__jobtrack input[type=checkbox]').filter(':checked').length;
			jQuery('div.span__round.jobtrack span').text(len);
	        var output = "";
	        jQuery(".dropdown-content__jobtrack input:checked").each(function() {
	            output += jQuery(this).next('span').text() + ",";
	        });
	        jQuery(".dropdown__jobtrack").val(output.trim().slice(0,-1));  
	}); 

	jQuery('a.clear__jobtrack').click(function(e){
		e.preventDefault();
		jQuery('.dropdown-content__jobtrack input:checkbox').removeAttr('checked');
		jQuery('div.span__round.jobtrack span').text('0');
		jQuery('input.dropdown__jobtrack').val('');
	});

	// Tech Stack
	jQuery('.dropdown__techstack').click(function(){
		jQuery('.dropdown-content__techstack').fadeToggle();
	}); 
	jQuery(".dropdown-content__techstack input:checkbox").click(function() {
			var len = jQuery('.dropdown-content__techstack input[type=checkbox]').filter(':checked').length;
			jQuery('div.span__round.techstack span').text(len);
	        var output = "";
	        jQuery(".dropdown-content__techstack input:checked").each(function() {
	            output += jQuery(this).next('span').text() + ",";
	        });
	        jQuery(".dropdown__techstack").val(output.trim().slice(0,-1));  
	}); 

	jQuery('a.clear__techstack').click(function(e){
		e.preventDefault();
		jQuery('.dropdown-content__techstack input:checkbox').removeAttr('checked');
		jQuery('div.span__round.techstack span').text('0');
		jQuery('input.dropdown__techstack').val('');
	});

	// Job Type
	jQuery('.dropdown__jobtype').click(function(){
		jQuery('.dropdown-content__jobtype').fadeToggle();
	}); 
	jQuery(".dropdown-content__jobtype input:checkbox").click(function() {
			var len = jQuery('.dropdown-content__jobtype input[type=checkbox]').filter(':checked').length;
			jQuery('div.span__round.jobtype span').text(len);
	        var output = "";
	        jQuery(".dropdown-content__jobtype input:checked").each(function() {
	            output += jQuery(this).next('span').text() + ",";
	        });
	        jQuery(".dropdown__jobtype").val(output.trim().slice(0,-1));  
	}); 

	jQuery('a.clear__jobtype').click(function(e){
		e.preventDefault();
		jQuery('.dropdown-content__jobtype input:checkbox').removeAttr('checked');
		jQuery('div.span__round.jobtype span').text('0');
		jQuery('input.dropdown__jobtype').val('');
	});

	// Company
	jQuery('.dropdown__company').click(function(){
		jQuery('.dropdown-content__company').fadeToggle();
	}); 
	jQuery(".dropdown-content__company input:checkbox").click(function() {
			var len = jQuery('.dropdown-content__company input[type=checkbox]').filter(':checked').length;
			jQuery('div.span__round.company span').text(len);
	        var output = "";
	        jQuery(".dropdown-content__company input:checked").each(function() {
	            output += jQuery(this).next('span').text() + ",";
	        });
	        jQuery(".dropdown__company").val(output.trim().slice(0,-1));  
	}); 

	jQuery('a.clear__company').click(function(e){
		e.preventDefault();
		jQuery('.dropdown-content__company input:checkbox').removeAttr('checked');
		jQuery('div.span__round.company span').text('0');
		jQuery('input.dropdown__company').val('');
	});

	// Industry
	jQuery('.dropdown__industry').click(function(){
		jQuery('.dropdown-content__industry').fadeToggle();
	}); 
	jQuery(".dropdown-content__industry input:checkbox").click(function() {
			var len = jQuery('.dropdown-content__industry input[type=checkbox]').filter(':checked').length;
			jQuery('div.span__round.industry span').text(len);
	        var output = "";
	        jQuery(".dropdown-content__industry input:checked").each(function() {
	            output += jQuery(this).next('span').text() + ",";
	        });
	        jQuery(".dropdown__industry").val(output.trim().slice(0,-1));  
	}); 

	jQuery('a.clear__industry').click(function(e){
		e.preventDefault();
		jQuery('.dropdown-content__industry input:checkbox').removeAttr('checked');
		jQuery('div.span__round.industry span').text('0');
		jQuery('input.dropdown__industry').val('');
	});

	// Save button
	/*if ( localStorage.getItem('main_form') == 1 ) {
   		jQuery('.main__form').addClass('hide');
   		jQuery('.main__recommendation').removeClass('hide');

   	}else{
   		jQuery('.main__form').removeClass('hide');
   		jQuery('.main__recommendation').addClass('hide');
   	}*/
	jQuery(document).on('click', 'button.upload-resume-save', function(){
		jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'htjl_save_user_detail',
			},
			success : function( response ) {  
				console.log(response);
				jQuery('div.main__form').hide();
		       	//localStorage.setItem('main_form', '1');
		       	//if ( localStorage.getItem('main_form') == 1 ) {
		       		// jQuery('.main__form').addClass('hide');
		       		// jQuery('.main__recommendation').removeClass('hide');
		       	//}

			}
		});
	});

	/*jQuery(document).on('click', '.main__recommendation a', function(){
	    //localStorage.setItem('main_form', 0);
	    jQuery('.main__form').removeClass('hide');
	    jQuery('.main__recommendation').addClass('hide');
	});*/

	// Save jobs
	jQuery(document).on('click', 'a.save_jobs', function(){
		var $this = jQuery(this);
	    var job_id = $this.attr('data-id');
	    jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'htjl_save_jobs',
				job_id: job_id,
			},
			success : function( response ) {
				if ( response.status == 0 ) {
					alert('Please login to save this job');
				}else{
					$this.removeClass('save_jobs');
					$this.addClass('remove_jobs');
					$this.children('i').removeClass('fa-bookmark-o');
					$this.children('i').addClass('fa-bookmark');
					jQuery('div#save_job .total__jobs_list_wrp').html(response.output);
				}

			}
		});
	});

	// Remove jobs
	jQuery(document).on('click', 'a.remove_jobs', function(){
		var $this = jQuery(this);
	    var job_id = $this.attr('data-id');
	    var endpoint = jQuery('#endpoint').val();
	    jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'htjl_remove_jobs',
				job_id: job_id,
			},
			success : function( response ) {
				$this.removeClass('remove_jobs');
				$this.addClass('save_jobs');
				get_job_list(1, endpoint);
				$this.children('i').removeClass('fa-bookmark');
				$this.children('i').addClass('fa-bookmark-o');
				jQuery('div#save_job .total__jobs_list_wrp').html(response.output);  
			}
		});
	});

	// Sort by Dropdown
	jQuery(document).on('change', 'select.sort__by', function(){
	    jQuery('form#main__filter_form').submit();
	});

	// Clear all filters
	jQuery(document).on('click', 'p.clear__all__selections', function(){
	    jQuery('a.clear__location').trigger('click');
	    jQuery('a.clear__jobtrack').trigger('click');
	    jQuery('a.clear__techstack').trigger('click');
	    jQuery('a.clear__jobtype').trigger('click');
	    jQuery('a.clear__company').trigger('click');
	    jQuery('a.clear__industry').trigger('click');
	});

	// Multiselect Skills
	jQuery('#langOpt').multiselect({
	    columns: 1,
	    placeholder: 'Select Languages',
	    search: true,
	    //selectAll: true
	});

	jQuery(document).on('change', 'select#langOpt', function(){
	    var val = jQuery(this).val();
	    jQuery.ajax({
		   	type : "get",
			dataType : "json",
			url : htjl.ajaxurl,
			data : {
				action: "htjl_all_job_listing",
				listing_type: 'htjl_add_skill',
				skill: val,
			},
			success : function( response ) {
		        jQuery('div.main__input__box-tags.htjl_skills').html(response.output);
			}
		});
	});


});