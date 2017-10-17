$(function() {

  $("form[name='usernameForm']").validate({
    rules: {
      username: {
        required : true
      },
    },
    messages: {
       username: "กรุณากรอกชื่อผู้ใช้งาน",
    }

  });
  
  $("form[name='passwordForm']").validate({
	    rules: {
	      username: {
	        required : true
	      },
	      currentPassword: {
	    	  minlength : 8,
	    	  maxlength: 20,
	          required : true
	      },
	      newPassword: {
	    	  minlength : 8,
	    	  maxlength: 20,
	          required : true
	      },
	      confirmPassword : {
	    	  required : true,
	    	  minlength : 8,
	    	  maxlength: 20,
              equalTo : "#newPassword"
          }
	    },
	    messages: {
	       username: "กรุณากรอกชื่อผู้ใช้งาน",
	       currentPassword: {
	           required : "กรุณากรอกรหัสผ่านเดิม",
	           minlength : "รหัสผ่านอย่างน้อย8ตัว",
	           maxlength: "รหัสผ่านมากสุดไม่เกิน10ตัว"
	         },
	         newPassword: {
		           required : "กรุณากรอกรหัสผ่านใหม่",
		           minlength : "รหัสผ่านอย่างน้อย8ตัว",
		           maxlength: "รหัสผ่านมากสุดไม่เกิน10ตัว"
		         },
		     confirmPassword: {
			      required : "กรุณากรอกยืนยันรหัสผ่าน",
			      minlength : "รหัสผ่านอย่างน้อย8ตัว",
			      maxlength: "รหัสผ่านมากสุดไม่เกิน10ตัว",
			      equalTo :"รหัสผ่านไม่ตรงกัน"
			   },
		      
	    },
	   
	  });
  
  $("form[name='formUpload']").validate({
	    rules: {
	    	avatar: {
	        required : true
	      },
	    },
	    messages: {
	       username: "กรุณาเลือกไฟล์",
	    }

	  });
  
  $('#submitChangePassword').click( function() { 
      $("form[name='passwordForm']").valid();  // test the form for validity
  });
  
  $('#submitChangeUsername').click( function() { 
      $("form[name='usernameForm']").valid();  // test the form for validity
  });
  
  $('#upload').click( function() { 
      $("form[name='formUpload']").valid();  // test the form for validity
  });
});