$().ready(function() {

	// validate signup form on keyup and submit
	$("#formCategory").validate({
		rules: {
			modalCategoryName: {
				required: true
			}
		},
		messages: {
			modalCategoryName: {
				required: "กรุณากรอกชื่อประเภทโครงการ"
			}
		}
	});
	
	 $('#save').click( function() { 
	        $("#formCategory").valid();  // test the form for validity
	 });
	
});
	