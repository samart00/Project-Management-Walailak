$(function(){
	var	minDate = new Date();
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
        var minDateSet = $("#dateStart").val();
        var maxDateSet = $("#dateEnd").val();
         
        if($(obj).attr("id")=="dateStart"){
            this.setOptions({
                minDate:minDate,
                maxDate:maxDateSet?maxDateSet:false
            })                   
        }
        if($(obj).attr("id")=="dateEnd"){
            this.setOptions({
                maxDate:false,
                minDate:minDateSet?minDateSet:minDate
            })                   
        }
    }
    
    var setDateEditFunc = function(ct,obj){
        var minDateSet = $("#dateStart_Edit").val();
        var maxDateSet = $("#dateEnd_Edit").val();
         
        if($(obj).attr("id")=="dateStart_Edit"){
            this.setOptions({
                minDate:minDate,
                maxDate:maxDateSet?maxDateSet:false
            })                   
        }
        if($(obj).attr("id")=="dateEnd_Edit"){
            this.setOptions({
                maxDate:false,
                minDate:minDateSet?minDateSet:minDate
            })                   
        }
    }
	
	
    var setTimeFunc = function(ct,obj){
        var minDateSet = $("#dateStart").val();
        var maxDateSet = $("#dateEnd").val();        
        var minTimeSet = $("#timeStart").val();
        var maxTimeSet = $("#timeEnd").val();
         
        if(minDateSet!=maxDateSet){
            minTimeSet = false;
            maxTimeSet = false;
        }
         
        if($(obj).attr("id")=="timeStart"){
            this.setOptions({
                defaultDate:minDateSet?minDateSet:false,
                minTime:false,
                maxTime:maxTimeSet?maxTimeSet:false        
            })                   
        }
        if($(obj).attr("id")=="timeEnd"){
            this.setOptions({
                defaultDate:maxDateSet?maxDateSet:false,
                maxTime:false,
                minTime:minTimeSet?minTimeSet:false      
            })                   
        }
    }    
    
    var setTimeEditFunc = function(ct,obj){
        var minDateSet = $("#dateStart_Edit").val();
        var maxDateSet = $("#dateEnd_Edit").val();        
        var minTimeSet = $("#timeStart_Edit").val();
        var maxTimeSet = $("#timeEnd_Edit").val();
         
        if(minDateSet!=maxDateSet){
            minTimeSet = false;
            maxTimeSet = false;
        }
         
        if($(obj).attr("id")=="timeStart_Edit"){
            this.setOptions({
                defaultDate:minDateSet?minDateSet:false,
                minTime:false,
                maxTime:maxTimeSet?maxTimeSet:false        
            })                   
        }
        if($(obj).attr("id")=="timeEnd_Edit"){
            this.setOptions({
                defaultDate:maxDateSet?maxDateSet:false,
                maxTime:false,
                minTime:minTimeSet?minTimeSet:false      
            })                   
        }
    } 
     
    $("#dateStart,#dateEnd").datetimepicker($.extend(optsDate,{  
        onShow:setDateFunc,
        onSelectDate:setDateFunc,
    }));
     
    $("#timeStart,#timeEnd").datetimepicker($.extend(optsTime,{  
        onShow:setTimeFunc,
        onSelectTime:setTimeFunc,
    }));
    
    $("#dateStart_Edit,#dateEnd_Edit").datetimepicker($.extend(optsDate,{  
        onShow:setDateEditFunc,
        onSelectDate:setDateEditFunc,
    }));
     
    $("#timeStart_Edit,#timeEnd_Edit").datetimepicker($.extend(optsTime,{  
        onShow:setTimeEditFunc,
        onSelectTime:setTimeEditFunc,
    }));

});