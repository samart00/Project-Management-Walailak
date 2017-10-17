$(document).ready(function(){
   
	$(".changePassword").hide();
	$(".changeUsername").hide();
		
	$("#btnEdit").click(function(){
		$("form[name='passwordForm']").validate().resetForm();
        $(".changePassword").show();
		$("#btnEdit").hide();
   	});
		
	$("#editUsername").click(function(){
		$("form[name='usernameForm']").validate().resetForm();
        $(".changeUsername").show();
		$("#editUsername").hide();
   	});
		
	$("#cancelSave").click(function(){
        $(".changePassword").hide();
		$("#btnEdit").show();
   	});
		
	$("#cancelEdit").click(function(){
        $(".changeUsername").hide();
		$("#editUsername").show();
		$('#duplicateUsername').hide();
   	});
		
   	$("#btnEditAvatar").click(function(){
        $('#w0').trigger("reset");
        $('#uploadAvatar').modal('show');
	});
		
});
	
$("#upload").click(function(){
    var value=($(this).val()).trim();
    if(value == ""){
        $("#error-name").hide();
    }else{
    	upload();
	}
});
	
$("#submitChangeUsername").click(function(){
    var value=($(this).val()).trim();
    if(value == ""){
        $("#error-name").hide();
    }else{
    	submitusername();
	}
});
	
$("#submitChangePassword").click(function(){
    var value=($(this).val()).trim();
    if(value == ""){
        $("#error-name").hide();
    }else{
    	submitpassword();
	}
});