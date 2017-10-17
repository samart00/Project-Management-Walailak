// Wait for the DOM to be ready
$(function() {
  $.validator.addMethod(
      "formatDate",
      function(value, element) {
          // put your own logic here, this is just a (crappy) example
          return value.match(/^\d\d?\/\d\d?\/\d\d\d\d$/);
      },
      "กรุณาเลือกวันที่ในรูปแบบ dd/mm/yyyy."
  );

  $.validator.addMethod(
      "formatTime",
      function(value, element) {
          // put your own logic here, this is just a (crappy) example
          return value.match(/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/);
      },
      "กรุณาเลือกเวลาในรูปแบบ hh:mm"
  );
  // Initialize form validation on the registration form.
  // It has the name attribute "registration"
  $("#event_name").change(function(){
      var value=($(this).val()).trim();
      $(this).val(value);
  });
//  $("#event_type").change(function(){
//      var value=($(this).val()).trim();
//      $(this).val(value);
//  });
  $("#comment").change(function(){
      var value=($(this).val()).trim();
      $(this).val(value);
  });
  

  
  $("form[id='validate']").validate({
    // Specify validation rules
    rules: {
      // The key name on the left side is the name attribute
      // of an input field. Validation rules are defined
      // on the right side
      eventName: {
        required : true,
      },
//      eventType: {
//          required : true,
//        },
      startDate: {
        required : true,
        formatDate : true,
//        formatTime: true,
      },
      endDate: {
        required : true,
        formatDate : true,
//        formatTime: true,
      },
      startTime: {
          required : true,
//          formatDate : true,
          formatTime: true,
      },
      endTime: {
          required : true,
//          formatDate : true,
          formatTime: true,
       },
      
//      email: {
//        required: true,
        // Specify that email should be validated
        // by the built-in "email" rule
//        email: true
//      },
    },
    // Specify validation error messages
    messages: {
    	eventName: "กรุณากรอกชื่อกิจกรรม",
//        startdate: {
//          required : "กรุณาเลือกวันที่",
//          formatDate : jQuery.validator.format("กรุณาเลือกวันที่ในรูปแบบ dd/mm/yyyy"),
//        },
        startDate: {
          required : "กรุณาเลือกวันที่เริ่ม",
          formatDate : jQuery.validator.format("กรุณาเลือกวันที่ในรูปแบบ dd/mm/yyyy"),
//          formatTime : jQuery.validator.format("กรุณาเลือกเวลาในรูปแบบ hh:mm"),
        },
        endDate:  {
          required : "กรุณาเลือกวันที่สิ้นสุด",
          formatDate : jQuery.validator.format("กรุณาเลือกวันที่ในรูปแบบ dd/mm/yyyy"),
//          formatTime : jQuery.validator.format("กรุณาเลือกเวลาในรูปแบบ hh:mm"),
          
        },
        startTime:  {
            required : "กรุณาเลือกเวลาที่เริ่มต้น",
//            formatDate : jQuery.validator.format("กรุณาเลือกวันที่ในรูปแบบ dd/mm/yyyy"),
            formatTime : jQuery.validator.format("กรุณาเลือกเวลาในรูปแบบ hh:mm"),
            
         },
         endTime:  {
            required : "กรุณาเลือกเวลาที่สิ้นสุด",
//            formatDate : jQuery.validator.format("กรุณาเลือกวันที่ในรูปแบบ dd/mm/yyyy"),
            formatTime : jQuery.validator.format("กรุณาเลือกเวลาในรูปแบบ hh:mm"),
              
         },
//         eventType: {
//        	 required : "กรุณาเลือกชื่อประเภทกิจกรรม", 
//         },
        
    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form) {
      form.submit();
    }
  });
});
