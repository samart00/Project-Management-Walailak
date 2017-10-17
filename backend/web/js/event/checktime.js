$(document).ready(function(){
    $("#checkTime").click(function(){
    	if($('#checkTime').is(':checked')){
    		$(".date-picker1").attr('disabled', true);
    		$(".date-picker2").attr('disabled', true);
    	}
    	else{
    		$(".date-picker1").removeAttr('disabled');
            $(".date-picker2").removeAttr('disabled');	
    	}
    });
});


$(document).ready(function(){
    $('#Type_name').click(function(){
    	$('#duplicateEventtype').hide();
    	$('#duplicateEventtypeColor').hide();
    });
});

$(document).ready(function(){
    $('.panel').click(function(){
    	$('#duplicateEventtype').hide();
    	$('#duplicateEventtypeColor').hide();
    });
});

$(document).ready(function(){
    $('#modalTypeName').click(function(){
    	$('#duplicateEventtypeEdit').hide();
    	$('#duplicateEventtypeColorEdit').hide();
    });
});

$(document).ready(function(){
    $('.panel').click(function(){
    	$('#duplicateEventtypeEdit').hide();
    	$('#duplicateEventtypeColorEdit').hide();
    });
});