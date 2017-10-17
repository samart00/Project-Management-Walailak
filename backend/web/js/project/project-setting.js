$('.delete').click(function(){
// 	debugger;
	var userId = $(this).data('user');
	var projectId = $(this).data('project');
	$('.modal-title').html('ลบสมาชิก');
	$('#submitDelete').attr('data-user', userId);
	$('#submitDelete').attr('data-project', projectId);
	$('#modalProjectId').val(projectId);
	$('#modalUserId').val(userId);
	$('#modalDelete').modal('show');
});

$('#submitDelete').click(function(){
// debugger;
	var projectId = $('input[name=modalProjectId]').val();
	var userId = $('input[name=modalUserId]').val();
	changeActiveFlag(projectId,userId);
});

$('.type').change(function(){
    var projectId = $(this).data('project-id');
	var userId = $(this).data('user-id');
	var type = $(this).val();
	changeEmployeetype(projectId,userId,type);
});

$('input[name=checkAll]').change(function(){

	if($(this).prop('checked')){

		$.each($('.checkbox-col input'), function(index, obj){
			$(obj).prop('checked', true);
		});
	}else{
		$.each($('.checkbox-col input'), function(index, obj){
			$(obj).prop('checked', false);
		});
	}
});

function getAllCheck(){
	var permission = [];
	var row = "";
        $("table[id=permissionInProject] tr").each(function(index) {
            if (index !== 0) {
//          debugger;
                row = $(this);
				var firstRow = row.find("td:first");
				var isCheck = firstRow.children().is(':checked');
				var id = firstRow.data('permission');
				if(isCheck){
					var temp = id;
					permission.push(temp);
				}
            }
    });
	return permission;
}