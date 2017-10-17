$('#createRole').click(function(){
	$('#modalRoleId').val(undefined);
	$('#modalRoleName').val(undefined);
	$('#modalDescription').val(undefined);	
	
	$('#message').show();
	$("#formRole").validate().resetForm();
	$('#accessDeny').hide();
	$('#duplicateRole').hide();
	$('.modal-title').html('สร้างบทบาท');
	$('#modalRoleName').html();
	$('#description').html();
	$('#modalRoleName').removeAttr('disabled');
	$('#modalRole').modal('show');
});

$('.edit').click(function(){
	$("#formRole").validate().resetForm();
	var id = $(this).data('id');
	var page = 'edit';		
	var action = 'geteditrole';
	$('#message').hide();
	$('#modalRoleName').attr('disabled','disabled');
	callGetRole(id, page, action);
});

$('.view').click(function(){
	var id = $(this).data('id');
	var page = 'view';
	var action = 'view';
	callGetRole(id, page, action);
});
		
$('.delete').click(function(){
	var id = $(this).data('id');
	var name = $(this).data('name');
	var haveMember = $(this).data('have-member');
	
	if(haveMember == 1){
		$('#submitDeleteRole').attr('data-id', id);
		$('#submitDeleteRole').attr('data-name', name);
		$('#modalIsUsedInAuthassignment').modal("show");
	}else{
		$('.modal-title').html('ลบบทบาท');
		$('#submitDelete').attr('data-id', id);
		$('#submitDelete').attr('data-name', name);
		$('#modalDelete').modal('show');
	}
});

$('#save').click(function(){
	var title = $('.modal-title').text();
	var roleName = $('#modalRoleName').val();
	var page = "";
	if(roleName != ""){
		if(title.includes('สร้าง')){
			page = 'create';
		}else{
			page = 'edit';
		}
		submit(page);
	}
});
function showModalEditRole(data){
	var roleData = data.roleData;
					
	$('#modalRoleId').val(roleData._id);
	$('#modalRoleName').val(roleData.name); 
	$('#modalDescription').val(roleData.description);
					
	$('.modal-title').html('แก้บทบาท');
	$('#modalRole').modal('show');
}

function showModalViewRole(data){
	var roleData = data.roleData;
					
	$('#viewRoleName').html(roleData.name);  
	$('#viewDescription').html(roleData.description);
	$('#viewCreateDate').html(roleData.createDate);
	$('#viewactiveFlag').html(roleData.activeFlag);
	$('#viewCreateBy').html(roleData.createBy);
					
	$('.modal-title').html('รายละเอียดบทบาท');
	$('#modalView').modal('show');
}

$(document).on('click', ".toggle", function() {
   	var toggle = $(this).children();
   	var roleId = toggle.data('id');
   	var activeFlag = toggle.val();
   	
	$('#submitFlag').attr('data-id', roleId);
   	$('#submitFlag').attr('data-flag', activeFlag);
	$('#modalActiveFlag').modal('show');
});

$('#submitFlag').click(function(){
	var roleId = $(this).attr('data-id');
	var activeFlag = $(this).attr('data-flag');
	changeActiveFlag(roleId, activeFlag);
});