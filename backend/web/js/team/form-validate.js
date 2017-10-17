$().ready(function() {

	// validate signup form on keyup and submit
	$("#formTeam").validate({
		rules: {
			modalTeamName: {
				required: true
			}
		},
		messages: {
			modalTeamName: {
				required: "กรุณากรอกชื่อทีม"
			}
		}
	});
	
});
	