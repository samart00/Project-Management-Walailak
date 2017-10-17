$().ready(function() {

	// validate signup form on keyup and submit
	$("#formRole").validate({
		rules: {
			modalRoleName: {
				required: true
			}
		},
		messages: {
			modalRoleName: {
				required: "กรุณากรอกชื่อบทบาท"
			}
		}
	});
	
	 $('#save').click( function() {
	        $("#formRole").valid();  // test the form for validity
	 });

});
