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

});