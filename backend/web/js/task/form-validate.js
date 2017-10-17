$().ready(function() {

	// validate signup form on keyup and submit
	$("form[name='formcreateTask']").validate({
	    // Specify validation rules
	    rules: {
	      // The key name on the left side is the name attribute
	      // of an input field. Validation rules are defined
	      // on the right side
	      modalTaskName: {
	    	  required : true,
	      },
	      startdate: {
	    	  required : true,
	      },
	      starttime: {
	    	  required : true,
	      },
	      stopdate: {
	    	  required : true,
	      },
	      stoptime: {
	    	  required : true,
	      },
	     
	    },
	    // Specify validation error messages
	    messages: {
	    	modalTaskName: "กรุณากรอกชื่องาน",
	    	startdate: "กรุณากรอกวันที่",
	        stopdate: "กรุณากรอกวันที่",
	        starttime: "กรุณากรอกเวลา",
	        stoptime: "กรุณากรอกเวลา"
	    },
	
 });
	 $('#save').click( function() { 
	        $("form[name='formcreateTask']").valid();  // test the form for validity
	 });
});
	