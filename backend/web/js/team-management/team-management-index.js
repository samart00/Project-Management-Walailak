/* เมื่อกด toggle แล้วให้ show modal ขึ้นมา */
$(document).on('click', ".toggle", function() {
   	var toggle = $(this).children();
   	var teamId = toggle.data('id');
   	var activeFlag = toggle.val();

	$('.modal-title').html('เปลี่ยนสถานะทีม');
   	$('#submitFlag').attr('data-id', teamId);
   	$('#submitFlag').attr('data-flag', activeFlag);
	$('#modalActiveFlag').modal('show');
});

/* เมื่อกด ตกลง ใน modal แล้วจะมาทำที่นี่ */
$('#submitFlag').click(function(){
	var teamId = $(this).attr('data-id');
	var activeFlag = $(this).attr('data-flag');
	changeActiveFlag(teamId, activeFlag);
});

$('.delete').click(function(){
	var id = $(this).data('id');
	$('.modal-title').html('ลบทีม');
	$('#submitDelete').attr('data-id', id);
	$('#modalDelete').modal('show');
});

function showModalViewTeam(data){			
	var teamData = data.teamData;
	$('#viewTeamName').html(teamData.teamName);
	$('#viewDescription').html(teamData.description);
	$('#viewCreateBy').html(teamData.createBy);
	$('#viewCreateDate').html(teamData.createDate);
	renderUser(teamData.users);
	$('#modalView').modal('show');
}	    

function renderUser(data){
	var i = 1;
    var render = "";
    $.each(data, function(index, value) {
		render = render.concat("&nbsp;&nbsp;" + i + ". " + value['userid'] + "&#13;");
		i++;	
    });
    $('#viewAssign').html(render);
}