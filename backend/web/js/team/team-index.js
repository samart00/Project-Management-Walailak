$('.team-detail').click(function(){
	$("#formTeam").validate().resetForm();
	var id = $(this).data('id');
	var page = 'view';
	var action = 'view';
	callGetTeam(id, page, action);
});

$('.delete').click(function(){
	var id = $(this).data('id');
	$('.modal-title').html('ลบทีม');
	$('#submitDelete').attr('data-id', id);
	$('#modalTeamId2').val(id);
	$('#modalDelete').modal('show');
});

$('#submitDelete').click(function(){
	var teamId = $('input[name=modalTeamId2]').val();
	changeActiveFlag(teamId);
});

$('.edit').click(function(){
	$('#duplicateTeam').hide();	
	$("#formTeam").validate().resetForm();
	var id = $(this).data('id');
	var page = 'edit';	
	var action = 'geteditteam';
	callGetEditTeam(id);
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
function showModalEditTeam(data){
	var teamData = data.teamData;
	$('#modalTeamId').val(teamData.teamId);
	$('#modalTeamName').val(teamData.teamName);
	$('#modalDescription').val(teamData.description);			
	$('.modal-title').html('แก้ไขข้อมูลทีม');
	$('#modalTeam').modal('show');
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
$('#save').click(function(){
	var title = $('.modal-title').text();
	var teamName = $('#modalteamName').val();
	var page = "";
	if(teamName != ""){
		if(title.includes('สร้างทีม')){
			page = 'create';
		}else{
			page = 'edit';
		}
		submit(page);
	}
});

$('#createTeam').click(function(){
	$('#modalTeamId').val(undefined);
	$('#modalTeamName').val(undefined);
	$('#modalDescription').val(undefined);	
	$('#accessDeny').hide();	
	$('#duplicateTeam').hide();
	$('.modal-title').html('สร้างทีม');
	$('#modalTeamName').html();
	$('#description').html();
	$('#modalTeam').modal('show');
});

$(".picture").click(function(){
	var teamId = $(this).data('id');
	$('#uploadTeamId').val(teamId);
	$('#uploadTeamPicture').trigger("reset");
	$('#uploadPicture').modal('show');
});