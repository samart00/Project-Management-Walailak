var tableUser = $('#user').DataTable({
	// Internationalisation. For more info refer to http://datatables.net/manual/i18n
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "ไม่พบข้อมูล",
                "info": "แสดง _START_ ถึง _END_ ของ _TOTAL_ รายการ",
                "infoEmpty": "ไม่พบข้อมูล",
                "infoFiltered": "(filtered1 from _MAX_ total entries)",
                "lengthMenu": "_MENU_ รายการ",
                "zeroRecords": "ไม่พบข้อมูล",
                "oPaginate": {
                    "sFirst": "หน้าแรก", // This is the link to the first page
                    "sPrevious": "ก่อนหน้า", // This is the link to the previous page
                    "sNext": "ถัดไป", // This is the link to the next page
                    "sLast": "หน้าสุดท้าย" // This is the link to the last page
                }
            },

            responsive: true,

            "order": [
                [0, 'asc']
            ],
            
            "bLengthChange": false,
            
            "lengthMenu": [
                [5, 10, 15, 20, -1],
                [5, 10, 15, 20, "ทั้งหมด"] // change per page values here
            ],
            // set the initial value
            "pageLength": 10,

            "searching": true,

            "columnDefs": [ {
                "targets": [1],
                "orderable": false
            },
        
            {"className": "dt-center", "targets": []},
            {"className": "dt-right", "targets": [1]},
            {"className": "dt-head-center", "targets": [0]},
            ],
});

var tableTeam = $('#team').DataTable({
	// Internationalisation. For more info refer to http://datatables.net/manual/i18n
            "language": {
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                },
                "emptyTable": "ไม่พบข้อมูล",
                "info": "แสดง _START_ ถึง _END_ ของ _TOTAL_ รายการ",
                "infoEmpty": "ไม่พบข้อมูล",
                "infoFiltered": "(กรอง 1 จากทั้งหมด _MAX_ รายการ)",
                "lengthMenu": "_MENU_ รายการ",
                "zeroRecords": "ไม่พบข้อมูล",
                "oPaginate": {
                    "sFirst": "หน้าแรก", // This is the link to the first page
                    "sPrevious": "ก่อนหน้า", // This is the link to the previous page
                    "sNext": "ถัดไป", // This is the link to the next page
                    "sLast": "หน้าสุดท้าย" // This is the link to the last page
                }
            },

            responsive: true,

            "order": [
                [0, 'asc']
            ],
            
            "bLengthChange": false,
            
            "lengthMenu": [
                [5, 10, 15, 20, -1],
                [5, 10, 15, 20, "ทั้งหมด"] // change per page values here
            ],
            // set the initial value
            "pageLength": 10,

            "searching": true,

            "columnDefs": [ {
                "targets": [1],
                "orderable": false
            },
        
            {"className": "dt-center", "targets": []},
            {"className": "dt-right", "targets": [1]},
            {"className": "dt-head-center", "targets": [0]},
            ],
});

$(document).on('click', "a#back", function() {
    var isShowErrorName = $('#error-name').text();
	if(isShowErrorName != ""){
		$('#next').hide();
	}else{
		$('#next').show();
	}
});

function addUserMenber(newUser) {
    dataUser.push(newUser);
//  child.removeClass('fa fa-plus').addClass('fa fa-minus');
    console.log(dataUser);
    lenderMember();
//     hideColumnUser(newUser.name);
}

function removeUserMember(id){

    $.each(dataUser, function( index, value ) {
        if(id === value.userId){
            dataUser.splice(index,1);
            var name = value.name;
            showColumnUser(id, name);
            return false;
        }
    });
    console.log(dataUser);
    lenderMember();
}

function addTeamMenber(newTeam) {
    var isNewTeam = true;
    $.each(dataTeam, function( index, value ) {
        if(newTeam.teamId === value.teamId){
            isNewTeam = false;
            value.member = [];
            value.member = newTeam.member;
            return false;
        }
    });
    if(isNewTeam){
        dataTeam.push(newTeam);
    }
    console.log(dataTeam);
    lenderTeamMember();
//     hideColumnTeam(newTeam.name);
    
}

function removeTeam(id){
//  child.removeClass('fa fa-minus').addClass('fa fa-plus');
    $.each(dataTeam, function( index, value ) {
        if(id === value.teamId){
            dataTeam.splice(index,1);
            return false;
        }
    });
    console.log(dataTeam);
    lenderTeamMember();
}

function removeTeamMember(id, parentId){
    $.each(dataTeam, function( index, value ) {
    	var teamName = "";
        if(parentId == value.teamId){
            $.each(value.member, function(indexMember, valueMember) {
                if(id === valueMember.userId){
                    value.member.splice(indexMember,1);
                    teamName = value.name;
                    showColumnTeam(value.teamId, teamName);
                    return false;
                }
            });
            return false;
        }
    });
}

$(document).on('click', "a.right-team", function() {
    var id = $(this).attr('arr-id');
    var name = $(this).attr('arr-name');
    
    $("#acceptTeam").attr('arr-id', id);
    $("#acceptTeam").attr('arr-team-name', name);
    $("#questionTeam").html('ต้องการลบทีม \"'+name+'"\ ออกจากโครงการ');
    $('#modalTeam').modal('show'); 
});

$('#acceptTeam').click(function(){
	var id = $(this).attr('arr-id');
    var name = $(this).attr('arr-team-name');
    showColumnTeam(id, name);
    removeTeam(id);
});


$(document).on('click', "a.right-member-team", function() {
	var id = $(this).attr('arr-id');
	var parentId = $(this).attr('arr-team-id');
	var teamName = $(this).attr('arr-team-name');
	var userName = $(this).attr('arr-user-name');

    $("#accept").attr('arr-id', id);
    $("#accept").attr('arr-team-id', parentId);
    $("#question").html('คุณการต้องลบ \"'+userName+'"\ ออกจากทีม \"'+teamName+'\" หรือลบผู้ใฃ้งานออกจากโครงการ');
    $("#choice1").html('ลบออกจากทีม \"'+teamName+'\"');
    $("#choice2").html("ลบออกจากโครงการ");
	$('#myModal').modal('show');
});


$('#accept').click(function(){
	var id = $(this).attr('arr-id');
    var parentId = $(this).attr('arr-team-id');
    var isOne = $('#deleteTeam').is(':checked');
    if(parentId == ""){
    	if(isOne){
    		removeUserMember(id);
    	}else{
    		removeUserProject(id);
    	}
    }else{
    	if(isOne){
		    removeTeamMember(id, parentId); 
		}else{
			removeUserProject(id);
		}
    }
    lenderTeamMember();
    lenderMember();
}); 

function showColumnTeam(id, teamName){
	var row = "";
	var haveTeam = false;
    $("table[id=team] tr").each(function(index) {
		if (index !== 0) {
			row = $(this);
            var id = row.find("td:first").text();
            if (id == teamName) {
				haveTeam = true;
                return false;
            }
       	}
   	});
   	
   	if(!haveTeam){
   		tableTeam.row.add( [
            teamName,
            "<p align='center'>"
	            +"<a href='javascript:;' class='btn btn-circle btn-icon-only green team'"
					+"data-arr-id='"+id+"'"
					+"data-arr-name='"+teamName+"'>"
					+"<i class='fa fa-plus' style='font-size:20px;vertical-align: middle;'></i>"
				+"</a></p>"
        ] ).draw( false );
   	}
};

function showColumnUser(id, userName){
//     $.each(dataUser, function(index, value) {
		tableUser.row.add( [
            userName,
            "<p align='center'>"
	            +"<a href='javascript:;' class='btn btn-circle btn-icon-only green user'"
					+"data-arr-id='"+id+"'"
					+"data-arr-name='"+userName+"'>"
					+"<i class='fa fa-plus' style='font-size:20px;vertical-align: middle;'></i>"
				+"</a></p>"
        ] ).draw( false );
        
        var row = "";
        $("table[id=user] tr").each(function(index) {
            if (index !== 0) {
//          debugger;
                row = $(this);
                var id = row.find("td:first").text();
                if (id == userName) {
                    row.removeClass('hiden');
                    row.show();
                    return false;
                }
            }
        });
//     });
};

$('#teamName').keyup(function(){
    tableTeam.column(0).search( this.value ).draw();
});

$('#nameUser').keyup(function(){
    tableUser.column(0).search( this.value ).draw();
});

$("#abbrProject").change(function(){
	callAbbrProjectIsDuplicate();
});

function isShowMessage(){
	var isShowMessageProjectName = $('#error-name').is(":hidden"); 
	var isShowMessageAbbrProject = $('#error-abbr-project').is(":hidden"); 
	var messageProjectName = $("#error-name").text();
	var messageAbbrProject = $("#error-abbr-project").text();
	var isShowErrorProjectName = !isShowMessageProjectName && messageProjectName != '';
	var isShowErrorAbbrProject = !isShowMessageAbbrProject && messageAbbrProject != '';
	if(isShowErrorProjectName || isShowErrorAbbrProject){
		$("#next").hide();
	}else{
		$("#next").show();
	}
}

$("#projectname").keyup(function(){
    var value=($(this).val()).trim();
    if(value == ""){
        $("#error-name").hide();
    }
});

function getMember(){

    var dataMemberOfProject = [];
    var user = {};
    var team = {};
    // add user and map user in team
    $.each(dataUser, function( index, value ) {
        user = {
            userId : value.userId,
            team : [],
        };
        $.each(dataTeam, function( indexTeam, valueTeam ) {
            var teamId = valueTeam.teamId;
            $.each(valueTeam.member, function( indexMember, valueMember ) {
                if(valueMember.userId == value.userId){
                	team = {
                		teamId : valueTeam.teamId
                	}
                    user.team.push(team);
                    valueTeam.member.splice(indexMember,1);
                    return false;
                }
            }); 
        });
        debugger;
        dataMemberOfProject.push(user);
    });
    
    var tempDataMember = jQuery.extend({}, dataTeam);
    var uniqueTeamMember = jQuery.unique(uniqueMember(tempDataMember));
    // add member in team map team
	$.each(uniqueTeamMember, function( index, userId ) {
		team = {
	      	userId : userId,
	       	team : []
	    }
		$.each(tempDataMember, function( index, value ) {
			$.each(value.member, function( indexMember, valueMember ) {
			 	if(userId == valueMember.userId){
			 		var teamId = {
			 			teamId : value.teamId
			 		}
			 		team.team.push(teamId);
			 		return false;
			 	}
			});
		});
		dataMemberOfProject.push(team);
	});
    
    console.log(dataMemberOfProject);
    return dataMemberOfProject;
};

function uniqueMember(tempDataMember){
	var userList = [];
	$.each(tempDataMember, function( index, value ) {
	  	$.each(value.member, function( indexMember, valueMember ) {
	     	userList.push(valueMember.userId);
	  	}); 	
	});
	return userList;
}

$("#from,#to").prop("readonly", true);

$( "#projectname" ).autoComplete({
    minChars: 1,
    cache: false,
    source: function(term, suggest){
        term = term.toLowerCase();
        var choices = getDataAutocomplete();
        var suggestions = [];
        for (i=0;i<choices.length;i++)
            if (~choices[i].toLowerCase().indexOf(term)) suggestions.push(choices[i]);
        suggest(suggestions);
    }
});

$('#teamname').change(function(){
	$('#duplicateTeamname').hide();
});

$("#from,#to,#fromTime,#toTime").change(function(){
	var from = $("#from").val();
        fromtime = $("#fromTime").val();
        to = $("#to").val();
        totime = $("#toTime").val();
                    			
	if(from != "" && fromtime != "" && to != "" && totime != ""){
		var fromdate = (from+" "+fromtime).trim();
		    todate = (to+" "+totime).trim();
		
		if(fromdate == todate){
			$('#acceptDate').modal('show');
        }
	}
});	

var buttonId = "";

$('#acceptDateTime').click(function(){
	buttonId = "acceptDateTime";
})   		
        		
$('#acceptDate').on('hidden.bs.modal', function () {
    if(buttonId == ""){
        $('#to').val("");	
    }
	buttonId = "";
});