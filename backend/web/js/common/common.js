
$(".modal").on('shown.bs.modal', function(event){
	$('body').attr('style',"overflow:hidden !important");
});
	        		
$(".modal").on('hide.bs.modal', function(event){
    $('body').removeAttr('style');
});


