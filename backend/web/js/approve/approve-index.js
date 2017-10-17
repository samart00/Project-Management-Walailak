$("#approveTable").on('click','.view', function () { 
	var id = $(this).data('id');
	var page = 'view';
	var action = 'view';
	$('#taskNowId').val(id);
	callGetTask(id, page, action);
});

$("#approveTable").on('click','.approve', function () { 
	var id = $(this).data('id');
	var page = 'approve';
	var action = 'approve';
	$('#viewTaskId').val(id);
	
	$('.modal-title').html('อนุมัติปิดงาน');
	$('#modalApproveTask').modal('show');
});

$("#approveTable").on('click','.reject', function () { 
	var id = $(this).data('id');
	var page = 'reject';
	var action = 'reject';
	$('#viewTaskId').val(id);
	
	$('#emptyComment').hide();
	
	$('.modal-title').html('ไม่อนุมัติปิดงาน');
	$('#modalRejectTask').modal('show');
});

$("#approve").click(function(){
	var comment =  $('textarea[name=modalCommentApprove]').text();
	var taskId  =  $('input[name=viewTaskId]').val();
	var status = 6;
	saveComment(taskId, comment, status);
});

$("#reject").click(function(){
	var comment =  $('textarea[name=modalCommentReject]').val();
	var taskId  =  $('input[name=viewTaskId]').val();
	var status = 7;
	saveComment(taskId, comment, status);
});

function showModalViewTask(data){
	var taskData = data.taskData;
	var comment = taskData.comment;
	var userId = taskData.userId;
	var pathAvartar = taskData.pathAvartar;
	$('#viewTaskId').html(taskData.taskId);
	$('#viewTaskName').html(taskData.taskName);
 	$('#viewDescription').html(taskData.description);
	        		
	$('#viewProject').html(taskData.project);
	$('#viewStartDate').html(taskData.startDate);
	$('#viewEndDate').html(taskData.endDate);
	lenderUser(taskData.users);        		
	$('#viewStatus').html(taskData.status);
	$('#viewCreateDate').html(taskData.createDate);
	$('#viewCreateBy').html(taskData.createBy);
	$('#viewAskForApproveDate').html(taskData.askforapproveDate);
	        		
	$('.modal-title').html('รายละเอียดงาน');
	$('#modalViewTask').modal('show');
	lenderComment(comment,userId, pathAvartar);
}
	        		
$('#approveTable').DataTable( {
    responsive: true,
	searching: false,
	paging: false,
    lengthChange: false,
	ordering: false,
	info: false,
	language: {"emptyTable": "ไม่พบข้อมูล"},
} );