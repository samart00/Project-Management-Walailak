$('#createCategory').click(function(){
	$('#modalCategoryId').val(undefined);
	$('#modalCategoryName').val(undefined);
	$('#modalDescription').val(undefined);
	
	$("#formCategory").validate().resetForm();
	$('#accessDeny').hide();	
	$('#duplicateCategory').hide();
	$('.modal-title').html('สร้างประเภทโครงการ');
	$('#modalCategoryName').html();
	$('#description').html();
	$('#modalCategory').modal('show');
});

$('.edit').click(function(){
	$("#formCategory").validate().resetForm();
	var id = $(this).data('id');
	var page = 'edit';	
	var action = 'geteditcategory';	
	callGetCategory(id, page, action);
});

$('.view').click(function(){
	var id = $(this).data('id');
	var page = 'view';
	var action = 'view';
	callGetCategory(id, page, action);
});
		
$('.delete').click(function(){
	var id = $(this).data('id');
	$('.modal-title').html('ลบประเภทโครงการ');
	$('#submitDelete').attr('data-id', id);
	$('#modalDelete').modal('show');
});

$('#save').click(function(){
	var title = $('.modal-title').text();
	var categoryName = $('#modalCategoryName').val();
	var page = "";
	if(categoryName != ""){
		if(title.includes('สร้าง')){
			page = 'create';
		}else{
			page = 'edit';
		}
		submit(page);
	}
});
						
function showModalEditCategory(data){
	var categoryData = data.categoryData;
					
	$('#modalCategoryId').val(categoryData._id);
	$('#modalCategoryName').val(categoryData.categoryName);
	$('#modalDescription').val(categoryData.description);
					
	$('.modal-title').html('แก้ประเภทโครงการ');
	$('#modalCategory').modal('show');
}

function showModalViewCategory(data){
	var categoryData = data.categoryData;
					
	$('#viewCategoryName').html(categoryData.categoryName);
	$('#viewDescription').html(categoryData.description);
	$('#viewCreateDate').html(categoryData.createDate);
	$('#viewCreateBy').html(categoryData.createBy);
	$('#viewStatus').html(categoryData.activeFlag);
					
	$('.modal-title').html('รายละเอียดประเภทโครงการ');
	$('#modalView').modal('show');
}

$(document).on('click', ".toggle", function() {
   	var toggle = $(this).children();
   	var categoryId = toggle.data('id');
   	var activeFlag = toggle.val();

   	$('#submitFlag').attr('data-id', categoryId);
   	$('#submitFlag').attr('data-flag', activeFlag);
	$('#modalActiveFlag').modal('show');
});

$('#submitFlag').click(function(){
	var categoryId = $(this).attr('data-id');
	var activeFlag = $(this).attr('data-flag');
	changeActiveFlag(categoryId, activeFlag);
});