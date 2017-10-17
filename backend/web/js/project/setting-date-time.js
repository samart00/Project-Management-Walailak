$(function(){
	var newdate = new Date();
		newdate.setMonth(newdate.getMonth() - 3);
	var	minDate = new Date(newdate);
    var optsDate = {  
        format:'d/m/Y', // รูปแบบวันที่ 
        formatDate:'d/m/Y',
        lang:'th',
        timepicker:false,  
        closeOnDateSelect:true,
    } 
    var optsTime = {
        format:'H:i', // รูปแบบเวลา
        step:15,  // step เวลาของนาที แสดงค่าทุก 30 นาที 
        formatTime:'H:i',
        datepicker:false,
    }    
    var setDateFunc = function(ct,obj){
        var minDateSet = $("#from").val();
        var maxDateSet = $("#to").val();
         
        if($(obj).attr("id")=="from"){
            this.setOptions({
                minDate:minDate,
                maxDate:maxDateSet?maxDateSet:false
            })                   
        }
        if($(obj).attr("id")=="to"){
            this.setOptions({
                maxDate:false,
                minDate:minDateSet?minDateSet:minDate
            })                   
        }
    }
     
    var setTimeFunc = function(ct,obj){
        var minDateSet = $("#from").val();
        var maxDateSet = $("#to").val();        
        var minTimeSet = $("#fromTime").val();
        var maxTimeSet = $("#toTime").val();
         
        if(minDateSet!=maxDateSet){
            minTimeSet = false;
            maxTimeSet = false;
        }
         
        if($(obj).attr("id")=="fromTime"){
            this.setOptions({
                defaultDate:minDateSet?minDateSet:false,
                minTime:false,
                maxTime:maxTimeSet?maxTimeSet:false        
            })                   
        }
        if($(obj).attr("id")=="toTime"){
            this.setOptions({
                defaultDate:maxDateSet?maxDateSet:false,
                maxTime:false,
                minTime:minTimeSet?minTimeSet:false      
            })                   
        }
    }    
     
    $("#from,#to").datetimepicker($.extend(optsDate,{  
        onShow:setDateFunc,
        onSelectDate:setDateFunc,
    }));
     
    $("#fromTime,#toTime").datetimepicker($.extend(optsTime,{  
        onShow:setTimeFunc,
        onSelectTime:setTimeFunc,
    }));

   // $("#from").change(function(){
   //      var dateValue=$(this).val();
   //      if(dateValue!=""){
   //          var arr_date=dateValue.split("/");
   //          var yearT=parseInt(arr_date[2])+543;
   //      }
   //      dateValue=dateValue.replace(arr_date[2],yearT);

   //      $(this).val(dateValue);

   //      changeTo();
   //  });

   //  $("#to").change(function(){
   //      var dateValue=$(this).val();
   //      if(dateValue!=""){
   //          var arr_date=dateValue.split("/");
   //          var yearT=parseInt(arr_date[2])+543;
   //      }
   //      dateValue=dateValue.replace(arr_date[2],yearT);

   //      $(this).val(dateValue);

   //      changeFrom();
   //  });    
     
   //  var changeFrom = function(){
   //      var dateValue=$("#from").val();
   //      if(dateValue != undefined){
   //          if(dateValue!=""){
   //              var arr_date=dateValue.split("/");
   //              var yearT=parseInt(arr_date[2])+543;
   //              dateValue=dateValue.replace(arr_date[2],yearT);
   //          }

   //          $("#from").val(dateValue);
   //      }
   //  };

   //  var changeTo = function(){
   //      var dateValue=$("#to").val();
   //      if(dateValue != undefined){
   //          if(dateValue!=""){
   //              var arr_date=dateValue.split("/");
   //              var yearT=parseInt(arr_date[2])+543;
   //              dateValue=dateValue.replace(arr_date[2],yearT);
   //          }

   //          $("#to").val(dateValue);
   //      }
   //  };

   //  $("#from,#to").click(function(){
   //      var dateValueFrom=$("#from").val();
   //      var dataValueTo=$("#to").val();
   //      if(dateValueFrom != undefined && dataValueTo != undefined){
   //          if(dateValueFrom!=""){
   //              var arr_date_form=dateValueFrom.split("/");
   //              var yearTf=parseInt(arr_date_form[2])-543;
   //              dateValueFrom=dateValueFrom.replace(arr_date_form[2],yearTf);
   //          }
   //          $("#from").val(dateValueFrom);

   //          if(dataValueTo!=""){
   //              var arr_date_to=dataValueTo.split("/");
   //              var yearTt=parseInt(arr_date_to[2])-543;
   //              dataValueTo=dataValueTo.replace(arr_date_to[2],yearTt);
   //          }
   //          $("#to").val(dataValueTo);
   //      }
   //  });

    // $("#from").click(function(){
    //     var dateValueFrom=$("#from").val();
    //     var dataValueTo=$("#to").val();
    //     if(dateValueFrom != undefined && dataValueTo != undefined){
    //         if(dateValueFrom!=""){
    //             var arr_date_form=dateValueFrom.split("/");
    //             var yearTf=parseInt(arr_date_form[2])-543;
    //             dateValueFrom=dateValueFrom.replace(arr_date_form[2],yearTf);
    //         }
    //         $("#from").val(dateValueFrom);

    //         if(dataValueTo!=""){
    //             var arr_date_form=dataValueTo.split("/");
    //             var yearTt=parseInt(arr_date_to[2])-543;
    //             dataValueTo=dataValueTo.replace(arr_date_to[2],yearTt);
    //         }
    //         $("#to").val(dataValueTo);
    //     }
    // });

    //  $("#from,#to").on("mouseenter mouseleave",function(e){
    //     var dateValue=$(this).val();
    //     if(dateValue!=""){
    //             var arr_date=dateValue.split("/"); // ถ้าใช้ตัวแบ่งรูปแบบอื่น ให้เปลี่ยนเป็นตามรูปแบบนั้น
    //             // ในที่นี้อยู่ในรูปแบบ 00-00-0000 เป็น d-m-Y  แบ่งด่วย - ดังนั้น ตัวแปรที่เป็นปี จะอยู่ใน array
    //             //  ตัวที่สอง arr_date[2] โดยเริ่มนับจาก 0 
    //             if(e.type=="mouseenter"){
    //                 var yearT=arr_date[2];
    //             }else if(e.type=="mouseleave"){
    //                 var yearT=parseInt(arr_date[2])+543;
    //             }   
    //             dateValue=dateValue.replace(arr_date[2],yearT);
    //             

    // $(this).val(dateValue);                                                 
    //     }       
    // });
     
});