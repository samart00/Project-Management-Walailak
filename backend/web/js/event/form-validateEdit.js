$(function() {
  $.validator.addMethod(
      "formatDate",
      function(value, element) {
          // put your own logic here, this is just a (crappy) example
          return value.match(/^\d\d?\/\d\d?\/\d\d\d\d$/);
      },
      "กรุณากรอกวันที่ในรูปแบบ dd/mm/yyyy."
  );

  $.validator.addMethod(
      "formatTime",
      function(value, element) {
          // put your own logic here, this is just a (crappy) example
          return value.match(/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])(:([0-5]?[0-9]))?$/);
      },
      "กรุณากรอกเวลาในรูปแบบ hh:mm"
  );
  // Initialize form validation on the registration form.
  // It has the name attribute "registration"
  $("#event_name_Edit").change(function(){
      var value=($(this).val()).trim();
      $(this).val(value);
  });

  $("#comment").change(function(){
      var value=($(this).val()).trim();
      $(this).val(value);
  });
  

  
  $("form[id='formEditEvent']").validate({
    // Specify validation rules
    rules: {
      // The key name on the left side is the name attribute
      // of an input field. Validation rules are defined
      // on the right side
      eventName: {
        required : true,
      },
 
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
      
    },
    // Specify validation error messages
    messages: {
    	eventName: "กรุณากรอกชื่อกิจกรรม",
    	
        startDate: {
          required : "กรุณาเลือกวันที่เริ่มต้น",
          formatDate : jQuery.validator.format("กรุณาเลือกวันที่ในรูปแบบ dd/mm/yyyy"),
        },
          
        endDate:  {
          required : "กรุณาเลือกวันที่สิ้นสุด",
          formatDate : jQuery.validator.format("กรุณาเลือกวันที่ในรูปแบบ dd/mm/yyyy"),
          
        },
          
        startTime:  {
            required : "กรุณาเลือกเวลาที่เริ่มต้น",
            formatTime : jQuery.validator.format("กรุณาเลือกเวลาในรูปแบบ hh:mm"),
            
         },

         endTime:  {
            required : "กรุณาเลือกเวลาที่สิ้นสุด",
            formatTime : jQuery.validator.format("กรุณาเลือกเวลาในรูปแบบ hh:mm"),
              
         },
        
    },
    // Make sure the form is submitted to the destination defined
    // in the "action" attribute of the form when valid
    submitHandler: function(form) {
      form.save();
    }
  });
});

	