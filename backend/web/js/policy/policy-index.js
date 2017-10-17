//$('.set-number').change(function(){
//	var valueParent = $(this).val();
//    $('.number-child').val(valueParent);
//})

$('.btn-plus').click(function(){
	var valueParent = $('.set-number').val();
    var intParent = parseInt(valueParent)+1;
   $('#submitsetall').attr('intParent', intParent);
   $('#modalSetAll').modal('show');
})

$('.btn-minus').click(function(){
	var valueParent = $('.set-number').val();
    var intParent = parseInt(valueParent)-1;
    $('#submitsetall').attr('intParent', intParent);
   $('#modalSetAll').modal('show');
})

$('#amountOfProject').change(function(){
	var valueParent = $(this).val();
	 $('#submitsetall').attr('intParent', valueParent);
   $('#modalSetAll').modal('show');
});	

$('#submitsetall').click(function(){
	var intParent = $(this).attr("intParent");
	submitAllAmount(intParent);
});

$("#table_policy").on('click','.btn-plusChild', function () {
	var id = "#"+$(this).data('name');
	var value = $(id).val();
    var amountOfProject = parseInt(value)+1;
    
    var userId = $(this).data('id');
    submitOneAmount(userId, amountOfProject, id);
})

$("#table_policy").on('click','.btn-minusChild', function () {
	var id = "#"+$(this).data('name');
	var value = $(id).val();
    var amountOfProject = parseInt(value)-1;
    
    var userId = $(this).data('id');
    submitOneAmount(userId, amountOfProject, id);
})	

$('.number-child').change(function(){
	var id = "#"+$(this).data('name');
    var amountOfProject = $(this).val();
    $(id).val(amountOfProject);
    var userId = $(this).data('id');
    submitOneAmount(userId, amountOfProject, id);
});

$(document).on('click', ".toggle", function() {
   	var toggle = $(this).children();
   	var userId = toggle.data('id');
	var inputId = toggle.data('name');
   	var isLimit = toggle.val();

   	$('#submitLimit').attr('data-id', userId);
   	$('#submitLimit').attr('data-limit', isLimit);
	$('#submitLimit').attr('data-input-id', inputId);
	$('#modalChangeLimit').modal('show');
});
		
$('#submitLimit').click(function(){
	var userId = $(this).attr('data-id');
	var isLimit = $(this).attr('data-limit');
	var inputId = $(this).attr('data-input-id');
	changeLimit(userId, isLimit, inputId);
});

$('#table_policy').DataTable( {
    responsive: true,
	searching: false,
	paging: false,
    lengthChange: false,
	ordering: false,
	info: false,
	language: {"emptyTable": "ไม่พบข้อมูล"},
} );