jQuery(document).ready(function(){

	/**********************************
	 * Job Detail page form submission
	 **********************************/
	 jQuery('#job_detail_form').on('submit', function(e){
	 	e.preventDefault();

	 	var fileInputElement = document.getElementById("job_detail_resume2");
	 	var job_detail_online_resume = jQuery('#job_detail_online_resume').val();
	 	
	 	if ( job_detail_online_resume == 'null' && fileInputElement.files.length == 0 ) {

	 		jQuery('p.upload_err').text('*Please upload your resume or select from online resume if available');
	 		return false;

	 	}else{

	 		/*jQuery.each(jQuery('#job_detail_resume2').prop("files"), function(k,v){
		        var filename = v['name'];    
		        var ext = filename.split('.').pop().toLowerCase();
		        if($.inArray(ext, ['pdf']) == -1) {
		            jQuery('p.upload_err').text('*Please upload PDF file only');
	 				return false;
		        }else{}
		    });*/
			 
	 		jQuery.ajax({
		 		url:htjl.ajaxurl,
				type:"POST",
				processData: false,
				contentType: false,
				data: new FormData(this),
				success : function( response ){
					var returnedData = JSON.parse(response);
					if(returnedData.code == 200){
						jQuery('div.job_application_form').html('<p class="success_application">Your application has been submitted successfully.</p>');
					}else{
						alert(returnedData.msg);
					}
				},
		 	});

	 	}

		 	
	});

	jQuery("#job_detail_resume2").change(function() {
    	var filename = jQuery('#job_detail_resume2').val().split('\\').pop();
    	if ( filename != '' ) {
    		jQuery('.htjl_upload_success').text(filename);
    	}else{
    		jQuery('.htjl_upload_success').text('Choose a file');
    	}
    });

});