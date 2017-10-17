<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\web\View;
use yii\widgets\ActiveForm;
use backend\components\Modal;
use common\libs\Status;
use common\libs\DateTime;
use common\libs\CheckProgress;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use backend\components\Success;
use common\libs\CheckMember;
use common\models\User;
use kartik\typeahead\TypeaheadBasic;
use kartik\typeahead\Typeahead;
use kartik\file\FileInput;
use backend\models\Comment;

$path = Comment::getUploadUrl();
$baseUrl = \Yii::getAlias ( '@web' );
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$baseUrl = \Yii::getAlias ( '@web' );
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$project = $projectId;
$currentUser = Yii::$app->user->identity->_id;
$userId = Yii::$app->user->identity->_id;
$userName = User::getUserName ( ( string ) $userId );
$now = new \MongoDate ();
$approved = (int)Status::APPROVED_TASK;
$reject = (int)Status::REJECTED_TASK;
$date = DateTime::MongoDateToDateCreate ( $now->sec );
$pathAvartar = User::getPhotoUserViewer($userId);
$this->title = 'งานในโครงการ';
$this->params ['breadcrumbs'] [] = [
		'label' => 'โครงการ',
		'url' => [
				"/project"
		]
];
$this->params ['breadcrumbs'] [] = $this->title;
$str = <<<EOT
$("li.user-menu").click(function(){
	var strClass = $(this).attr('class');
	if(!strClass.includes("user-menu open")){
		$(this).removeClass('dropdown user user-menu open').addClass('dropdown user user-menu');
	}else{
		$(this).removeClass('dropdown user user-menu').addClass('dropdown user user-menu open');
	}
});
$("#uploadimage").click(function(){
	$('#modalUploadImage').modal('show');
});
$("#uploadfile").click(function(){
	$('#modalUploadFile').modal('show');
});
		$('#checkallday').change(function(){
		$("label.error").hide();
        if(this.checked){
			$('input[name=starttime]').val('');
			$('input[name=stoptime]').val('');
            
			$('#fromTime').prop('disabled', true);
            $('#toTime').prop('disabled', true);
	}
        else{
            $('#fromTime').prop('disabled', false);
            $('#toTime').prop('disabled', false);
	}
});		
$('#from,#to').change(function(){
		var enddate = $('input[name=stopdate]').val();
		var startdate = $('input[name=startdate]').val();
		var allday = $('input[name=checkallday]').prop('checked');
		if(enddate != ""){
			if(startdate != ""){
        		if(allday){
					$('#fromTime').prop('disabled', true);
		            $('#toTime').prop('disabled', true);
				}
        	else{
            		$('#fromTime').prop('disabled', false);
           			 $('#toTime').prop('disabled', false);
				}
			}	
		}
});
$("#approve").click(function(){
	var comment =  $('textarea[name=modalCommentApprove]').val();
	var id = $('#modalTaskId3').val();
	var status = $('#status3').val();
	saveStatus(id, status, comment);
});
$("#reject").click(function(){
	var comment =  $('textarea[name=modalCommentReject]').val();
	var id = $('#modalTaskId4').val();
	var status = $('#status4').val();
	saveStatus(id, status, comment);
});
$('#saveApprove').click(function(){
	var id = $('#modalTaskId2').val();
	var status = $('#status2').val();
	saveStatus(id,status,null);
});
$('.change-status').click(function(){
	var id = $(this).data('id');
	var status = $(this).data('status');
	saveStatus(id,status,null);
});
$('.change-waitapprove').click(function(){
	var id = $(this).data('id');
	var taskname = $(this).data('taskname');
	var status = $(this).data('status');
	$('.modal-title2').html('ขออนุมัติ');
	$('#modalTaskId2').val(id);
	$('#taskName2').html(taskname);
	$('#status2').val(status);
	$('#sendTask').modal('show');
});
$('.change-reject').click(function(){
	var id = $(this).data('id');
	var status = $(this).data('status');
	$('.modal-title4').html('ขออนุมัติ');
	$('#modalTaskId4').val(id);
	$('#status4').val(status);
	$('#emptyComment').hide();	
	$('#modalRejectTask').modal('show');	
});
$('.change-approve').click(function(){
	var id = $(this).data('id');
	var status = $(this).data('status');
	$('.modal-title3').html('ขออนุมัติ');
	$('#modalTaskId3').val(id);
	$('#status3').val(status);
	$('#modalApproveTask').modal('show');
});
$('#saveMember').click(function(){
	var listUser = getAllCheck();
	var id = $('input[name=taskId]').val();
	if(listUser != ""){
		var formData = new FormData();
	        formData.append('$csrfParam', '$csrf');
	        formData.append('data',JSON.stringify(listUser));
	  	    formData.append('taskId', id);
	  	    formData.append('projectId', "$projectId");
		        var request = new XMLHttpRequest();
		        request.open("POST", "$baseUrl/task/editmember", false);
		        request.onreadystatechange = function () {
		            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
		                var response = request.responseText;
		                if(typeof(response) == "string"){
		                    response = JSON.parse(request.responseText);
		        			console.log(response);
		        			if(response.isDelete){
								$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
		        				$('#modalIsDelete').modal('show');
		        				setTimeout(function(){
	            						$.redirect('$baseUrl/task/index', {'projectId':'$projectId','$csrfParam':'$csrf'});
										}, 2000);
		        			}else{
			        			if(response.success){
			        				$('#modalView').modal('hide');
			        				$('#success').modal('show');
									setTimeout(function(){
				            			location.reload();
									}, 2000);
			        			}else{
				            		if(response.isClose){
	        							$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});	
	        						}else{
	        							if(response.isProject){
	        								$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        							}else{
	        								if(response.isCancel){
	        									$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});	
	        								}else{
	        									if(response.isDone){
	        										$.redirect('$baseUrl/task', {'projectId':'$projectId','$csrfParam':'$csrf'});
	        									}
	        								}
	        							}
	        						}
				            	}
			        		}
		                }
		            }else if(request.status == 403){
		            	$('#modalIsAccessDeny').modal('show');
		            }else{
		            	$('#modalContact').modal('show');
		            }
		        };
		 	request.send(formData);
	}else{
		$('#modalNotCheck').modal('show');
	}
})
function saveStatus(id,status,comment){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('taskId', id);
        formData.append('status', status);
        formData.append('comment', comment);
        formData.append('projectId', '$project');
        if(status == '$reject' && comment == ""){       
       		 $('#emptyComment').show();	
        }else{
        	 var request = new XMLHttpRequest();
	         request.open("POST", "$baseUrl/task/changestatus", false);
	         request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
					var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
	        				if(response.notProgress){
	        					$('#sendTask').modal('hide');
								$('#title-delete').html('ไม่สามารถขออนุมัติปิดงานได้ เนื่องจากงานยังไม่สำเร็จ');
	        					$('#modalIsDelete').modal('show');
							}else{
	        					if(response.isWait){
	        					$('#modalApproveTask').modal('hide');
	        					$('#modalRejectTask').modal('hide');
	        					$('#title-delete').html('เนื่องจากงานนี้เปลื่ยนสถานะโดยผู้ใช้ท่านอื่นแล้ว');
	        					$('#modalIsDelete').modal('show');
		        				}else{
	        						if(response.isClose){
	        							$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        						}else{
	        							if(response.isProject){
	        							$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        							}else{
	        								if(response.isCancel){
	        									$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        								}else{
	        									if(response.isDone){
	        										$('#title-delete').html('เนื่องจากงานนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว');
	        										$('#modalIsDelete').modal('show');
	        									}else{
	        										if(response.success){
	        											location.reload();
	        										}
	        									}
	        								}
	        							}
	        						}
	        					}
	        				}
	        			}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	     
	            	$('#emptyComment').show();
	            }
	        };
	        request.send(formData);
	   	}
}
$('.task-detail').click(function(){
	$('.emoji-wysiwyg-editor').html("");       									
	$("#formcreateTask").validate().resetForm();
	var id = $(this).data('id');
	$('#taskNowId').val(id);
	callGetTask(id);
});
function callGetTask(id){
	var roleData = $.ajax({
		url: '$baseUrl/task/view',
		type: 'post',
		data: {
			'taskId' : id,
			'$csrfParam' : '$csrf',
			'projectId' : '$project'
		},
		beforeSend: function() { $('#wait').modal('show'); },
    	complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else{
				if(data.success){
					$('.fileinput-upload').attr('type','button');
					showModalViewTask(data);
				}else{
					if(data.isClose){
	        			$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
		  			}else{
		  			  	if(data.isCancel){
	        				$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
		     		    }else{
		     		    	if(data.isProject){
	        					$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
		       				 }
						}
					}
				}
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(thrownError == 'Forbidden'){
				$('#modalIsAccessDeny').modal('show');
			}else{
				$('#modalContact').modal('show');
			}
	    }
	});
}
function showModalViewTask(data){
	var taskData = data.taskData;
	var comment = taskData.comment;
	var commentBy = taskData.commentBy;
	var log = taskData.log2;
	var pathAvartar = taskData.pathAvartar;
	var memberIntask = taskData.arrMemberInTask
	if(taskData.checkCreate){	
		document.getElementById("manageTask").style.display="block";
		$('[href="#add"]').closest('li').show();	 
	}else{
		 document.getElementById("manageTask").style.display="none";
		 $('[href="#add"]').closest('li').hide();	 
	}
	if(taskData.status2 == "$approved"){
		  document.getElementById('DivComment').style.display="none";
	}else{
		 document.getElementById('DivComment').style.display="block";
	}
	$.each(memberIntask, function(index, id) {
			var checkId = "input[id=\'"+id+"\']";
			$(checkId).prop('checked', true);
	})
	$('#taskIdUploadImages').val(taskData._id);
	$('#taskIdUploadFiles').val(taskData._id);
	$('#assign').val(taskData._id);
	$('#delete').val(taskData._id);
	$('#edit').val(taskData._id);
	$('#commentID').val(taskData._id);
	$('#viewTaskName').html(taskData.taskName);
	$('#viewDescription').html(taskData.description);
	$('#viewTaskTag').html(taskData.tag);
	$('#newcomment').html('');
	$('#viewStartDate').html(taskData.startDate);
	$('#viewEndDate').html(taskData.endDate);
	$('#viewStatus').html(taskData.status);
	$('#viewCreateby').html(taskData.createBy);
	$('.modal-title').html('รายละเอียดงาน');
	$('#modalView').modal('show');
	playinterval();		
	newComment= '';
	lenderUser(taskData.users);
	lenderComment(comment,commentBy, pathAvartar);
	renderLog(log);
	$('#modalView').on('hidden.bs.modal', function () {
			if(taskData.changestatus){
				location.reload();
			}
	});
}
function showModalEditTask(data){
	$('#modalTaskId').val(data.taskId);
	if(data.Allday){
		 $('#checkallday').attr('checked', true);
		 $('#toTime').val('');
		 $('#fromTime').val('');
	}
	else{
		$('#toTime').val(data.timeend);
		 $('#fromTime').val(data.timestart);
	}
	$('#modalTaskName').val(data.taskName);
	$('#modalTaskTag').val(data.tag);
	$('#modalDescription').val(data.description);
	$('#from').val(data.startDate);
	$('#to').val(data.endDate);
	$('.modal-title').html('แก้ไขงาน');
	$('#modalTask').modal('show');
}
$('#createTask').click(function(){
	$('#modalTaskId').val(undefined);
	$('#modalTaskName').val(undefined);
	$('#modalDescription').val(undefined);
	$('#from').val(undefined);
	$('#to').val(undefined);
	$('#toTime').val(undefined);
	$('#fromTime').val(undefined);
	$("form[name='formcreateTask']").validate().resetForm();		
	$('#accessDeny').hide();
	$('#duplicateTask').hide();
	$('.modal-title').html('สร้างงาน');
	$('#modalTaskName').html();
	$('#description').html();
	$('#modalTask').modal('show');
});
$('#editTask').click(function(){
	var id = $('#edit').val();
	$("form[name='formcreateTask']").validate().resetForm();
	geteditTask(id);
});
$('#deleteTask').click(function(){
	var id = $('#delete').val();
	$('.modal-title').html('ลบงาน');
	$('#modalTaskIddelete').val(id);
	$('#modalDelete').modal('show');
});
$('#submitDelete').click(function(){
	var taskId = $('input[name=modalTaskIddelete]').val();
	deleteTask(taskId);
});
function deleteTask(id){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('taskId', id);
        formData.append('projectId', '$project');
         var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/task/delete", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
						var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
	        				if(response.inProgress){
							$('#modalDelete').modal('hide');
							$('#title-delete').html('เนื่องจากงานนี้มีการดำเนินการอยู่');
	        					$('#modalIsDelete').modal('show');
	        				}else{
	        					if(response.success){
		        					$('#modalDelete').modal('hide');
		        					$('#modalView').modal('hide');
		        					$('#success').modal('show');
		        					location.reload();
								}else{
									if(response.isClose){
	        							$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});	
	        						}else{
	        							if(response.isProject){
	        								$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});	
	        							}else{
	        								if(response.isCancel){
	        									$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        								}else{
	        									if(response.isDone){
		        									$('#modalTask').hide();
													$('#title-delete').html('เนื่องจากโครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว');
						        					$('#modalIsDelete').modal('show');
	        									}
	        								}
	        							}
	        						}
								}
		        			}
	        			}
	        		}
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	            }
	        };
	        request.send(formData);
}
function geteditTask(id){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('taskId', id);
         var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/task/getedittask", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	        		var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
	        				if(response.success){
	        					$('#modalView').modal('hide');
	        					showModalEditTask(response);
							}
		        		}
	        		}
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	            }
	        };
	request.send(formData);
}     		
$('#save').click(function(){
	var title = $('.modal-title').text().substring(0,8);
	var isValid = $("form[name='formcreateTask']").valid();
	if(isValid){
		var page = "";
			if(title.includes('สร้างงาน')){
				page = 'savetask';
			}else{
				page = 'edittask';
			}
		submit(page);
	}
});
function getAllCheck(){
	var member = [];
	var row = "";
        $("table[id=memberInTask] tr").each(function(index) {
            if (index !== 0) {
                row = $(this);
				var firstRow = row.find("td:first");
				var isCheck = firstRow.children().is(':checked');
				var id = firstRow.data('id');
				if(isCheck){
					var temp = {
					userId: id
				};
					member.push(temp);
				}
            }
    });
	return member;
}		
function submit(action){
		var id = $('input[name=modalTaskId]').val();
		var allday = $('input[name=checkallday]').prop('checked');
		var taskName = $('input[name=modalTaskName]').val();
		var tag = $('input[name=modalTaskTag]').val();
		var description = $('textarea[name=modalDescription]').val();
		var startDate = $('input[id=from]').val();
			startDate = startDate.split('/');
			startDate = startDate[2]+"-"+startDate[1]+"-"+startDate[0];
		var startTime = $('input[id=fromTime]').val();
		if(startTime != ""){
			startTime = startTime.split(':');
			startTime = (startTime[0])+":"+startTime[1];
		}else{
			startTime = "00:00";
		}
		var endDate = $('input[id=to]').val();
			endDate = endDate.split('/');
			endDate = endDate[2]+"-"+endDate[1]+"-"+endDate[0];
		var endTime = $('input[id=toTime]').val();
			
		if(endTime != ""){
			endTime = endTime.split(':');
			endTime = (endTime[0])+":"+endTime[1];
		}else{
			endTime = "23:59";
		}
		var start = new Date(startDate+" "+startTime);
			start = start.setHours(start.getHours()-6);
			start = new Date(start);
			
		var startDate = $.format.date(start, "yyyy-MM-dd HH:mm");
		
		var end =  new Date(endDate+" "+endTime);
			end = end.setHours(end.getHours()-6);	
			end = new Date(end);		
		var endDate = $.format.date(end, "yyyy-MM-dd HH:mm");
		var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('taskId', id);
        formData.append('projectId', '$project');
        formData.append('taskName', taskName);
        formData.append('tag', tag);
        formData.append('allday', allday);
        formData.append('description', description);
        formData.append('startdate', startDate);
        formData.append('enddate', endDate);;
        if(taskName != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/task/"+action, false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isProject){
		        			$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        			}else{
	        				if(response.isDuplicate){
		        				$('#duplicateTask').show();
		        				$('#accessDeny').hide();
	        				}else{
								if(response.success){
									$('#duplicateTask').hide();
		        					$('#accessDeny').hide();
	        						$('#modalTask').modal('hide');
	        						$('#success').modal('show');
									 setTimeout(function(){
            						location.reload();
									}, 2000);
		        				}else{
									if(response.isClose){
	        							$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        						}else{
									if(response.isCancel){
	        							$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
		        						}else{
		        							if(response.isDelete){
		        							$('#modalTask').hide();
												$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
						        				$('#modalIsDelete').modal('show');
	        								}else{
	        									if(response.isDone){
		        									$('#modalTask').hide();
													$('#title-delete').html('เนื่องจากโครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว');
						        					$('#modalIsDelete').modal('show');
	        									}
	        								}
		        						}
	        						}
	        					}
	        				}
						}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	     
	            	$('#accessDeny').show();
	            	$('#duplicateTask').hide();
	            }
	        };
	        request.send(formData);
	    }
};
function lenderUser(data){
	var i = 0;
    var lender = "";
    $.each(data, function(index, value) {
    if(i == 0){
        lender = lender.concat("<span>"+value['userid']+"</span>");
	    i=i+1;
	 }else{
	      if(i == 1){
            lender = lender.concat("<span>,  "+value['userid']+"</span>");
	   		 i=i+1;
	 		}
	      else{
	        if(i == 2){
 				lender = lender.concat("<span>,  "+value['userid']+"</span><br>");
	  			i=0;
			 }
	      }
	  }
    });
    $('#viewAssign').html(lender);
}    
	var	minDate = new Date('$minDate');
	var	maxDate = new Date('$maxDate');
	var strMinDate = '$minDate';
	var strMaxDate = '$maxDate';
    var optsDate = {
        format:'d/m/Y',
        formatDate:'d/m/Y',
        lang:'th',
        timepicker:false,
        closeOnDateSelect:true,
    }
    var optsTime = {
        format:'H:i',
        step:15,
        formatTime:'H:i',
        datepicker:false,
    }
    var setDateFunc = function(ct,obj){
        var minDateSet = $("#from").val();
        var maxDateSet = $("#to").val();
        if($(obj).attr("id")=="from"){
            this.setOptions({
                minDate:minDate,
                maxDate:maxDateSet?maxDateSet:maxDate
            })
        }
        if($(obj).attr("id")=="to"){
            this.setOptions({
                maxDate:maxDate,
                minDate:minDateSet?minDateSet:minDate
            })
        }
    }
    var	minTime = '$minTime';
	var	maxTime = '$maxTime';
    var setTimeFunc = function(ct,obj){
        var minDateSet = $("#from").val();
        var maxDateSet = $("#to").val();
        var minTimeSet = $("#fromTime").val();
        var maxTimeSet = $("#toTime").val();
        if(minDateSet!=maxDateSet){
            minTimeSet = false;
            maxTimeSet = false;
        }
		var toDate = minDateSet;
		var endDate = maxDateSet;
		var startDate = toDate;
			startDate = startDate.split('/');
			startDate = startDate[1]+"-"+startDate[0]+"-"+startDate[2];
		var endDate = endDate;
			endDate = endDate.split('/');
			endDate = endDate[1]+"-"+endDate[0]+"-"+endDate[2];
		if(startDate == strMinDate && endDate == strMaxDate){
			minTimeSet = minTime;
            maxTimeSet = maxTime;
		}
        if($(obj).attr("id")=="fromTime"){
            this.setOptions({
                defaultDate:minDateSet?minDateSet:false,
                minTime:(startDate == strMinDate)?minTime:false,
                maxTime:maxTimeSet?maxTimeSet:(startDate == strMaxDate)?maxTime:false
            })
        }
        if($(obj).attr("id")=="toTime"){
            this.setOptions({
                defaultDate:maxDateSet?maxDateSet:false,
                maxTime:(endDate == strMaxDate)?maxTime:false,
                minTime:minTimeSet?minTimeSet:(endDate == strMinDate)?minTime:false
            })
        }
    }   
    $("#from,#to").datetimepicker($.extend(optsDate,{
        onShow:setDateFunc,
        onSelectDate:setDateFunc,
    }));
    $("#fromTime,#toTime").datetimepicker($.extend(optsTime,{
        onShow:setTimeFunc,
        onSelectTime:setTimeFunc,
    }));
function saveprogress(id,progress){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('taskId', id);
        formData.append('projectId', '$project');
        formData.append('progress', progress);
         var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/task/saveprogress", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
						var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{      			
	        				if(response.success){
	        					location.reload();
							}else{
	        					if(response.isClose){
	        						$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        					}else{
	        						if(response.isProject){
	        						$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        						}else{
	        							if(response.isCancel){
	        								$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        							}else{
	        								if(response.isDone){
	        									$('#title-delete').html('เนื่องจากงานนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว');
	        									$('#modalIsDelete').modal('show');
	        								}
	        							}
	        						}
	        					}
	        				}
		        		}
	        		}
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	            }
	        };
	        request.send(formData);
}
$(document).on('change', "#changeProgress", function() {
	    var id = $(this).data('id');
		var progress = $(this).val();
		saveprogress(id,progress);
});
$(".addComment").click(function(){
		var comment =  $('input[name=message]').val();
		var taskId = $('input[name=taskId]').val();
		saveComment(taskId, comment);
});
function saveComment(id, comment){
	if(comment.trim().length > 0){
		var formData = new FormData();
		formData.append('comment', comment);
		formData.append('$csrfParam', '$csrf');
		formData.append('projectId', '$project');
		formData.append('refId', id);
		var request = new XMLHttpRequest();
		var form = $(".comment-form");
		request.open("POST", "$baseUrl/comment/savecommenttask", false);
		request.onreadystatechange = function () {
	        if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	            var response = request.responseText;
	            if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
	        			$('#modalView').modal('hide');
							$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
		        			if(response.success){
								lenderNewComment(comment,response.createtime);
								$('.emoji-wysiwyg-editor').html("");
		        			}else{
		        				if(response.isCancel){
	        						$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
		        				}else{
		        					if(response.isProject){
	        							$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
		        					}else{
		        						if(response.isClose){
	        								$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
		        						}else{
		        							if(response.isDone){
	        									$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
		        							}
		        						}
		        					}
		        				}
		        			}
		        		}
	                }
	            }else if(request.status == 403){
	            	$('#modalDelete').modal('hide');
	            	$('#modalIsAccessDeny').modal('show');
	            }else{
	            	$('#modalContact').modal('show');
	            }
	    };
		request.send(formData);
	}}
function lenderComment(data, userid, pathAvartar){
    var lender = "";
    $.each(data, function(index, value) {
    if(userid[index] == "$userId"){
        lender = lender.concat("<div class='direct-chat-msg right'>"
        							 +"<div class=\"direct-chat-info clearfix\">"
        							 +"<span class=\"direct-chat-name pull-right\">"+value.commentBy+"</span>"
        							 +"<span class=\"direct-chat-timestamp pull-left\">"+value.createTime+"</span>"
        							 +"</div>"
        						 	 +"<img class=\"direct-chat-img\" src=\""+pathAvartar[index]+"\" alt=\"Message User Image\">");
				                     	if(value.comment != null){
				                     		lender += "<div class=\"direct-chat-text\">";
				                     		lender += value.comment;
				                     	}else if(value.images != null){
				                     		lender += "<div class=\"direct-chat-text\" style=\"text-align: -webkit-right;background-color: white;border: white;\">";
				                     		lender += "<a href='"+'$baseUrl'+'/task/download/?file='+value.images+"'><span class='glyphicon glyphicon-download-alt' style='font-size: 30px;color: #d4d4d4;'></span></a> "
				                     		lender += '<img src=\"';
				                     		lender += '$path'+value.images;
				                     		lender += '\" style=\"width: 30%;\" class=\"right\"></img>'
				                     	}else{
				                     		lender += "<div class=\"direct-chat-text\" style=\"text-align: -webkit-right;;background-color: white;border: white;\">";
				                     		lender += "<span class=\"glyphicon glyphicon-file\" style=\"font-size: 20px;color: #d4d4d4;\"></span>";
				                     		lender += "<a href='"+'$baseUrl'+'/task/download/?file='+value.allfiles+"'><span style='vertical-align: middle'>"+value.filename+"</span></a> ";
				                     	}
				                  	lender += "</div></div>";
        }else{
        lender = lender.concat("<div class='direct-chat-msg'>"
        							 +"<div class=\"direct-chat-info clearfix\">"
        							 +"<span class=\"direct-chat-name pull-left\">"+value.commentBy+"</span>"
        							 +"<span class=\"direct-chat-timestamp pull-right\">"+value.createTime+"</span>"
        							 +"</div>"
        						 	 +"<img class=\"direct-chat-img\" src=\""+pathAvartar[index]+"\" alt=\"Message User Image\">");
				                     if(value.comment != null){
				                     		lender += "<div class=\"direct-chat-text\">";
				                     		lender += value.comment;
				                     	}else if(value.images != null){
				                     		lender += "<div class=\"direct-chat-text\" style=\"text-align: -webkit-left;background-color: white;border: white;\">";
				                     		lender += '<img src=\"';
				                     		lender += '$path'+value.images;
				                     		lender += '\" style=\"width: 30%;\" class=\"right\"></img>'
				                     		lender += "<a href='"+'$baseUrl'+'/task/download/?file='+value.images+"'><span class='glyphicon glyphicon-download-alt' style='font-size: 30px;color: #d4d4d4;'></span></a> "
				                     	}else{
				                     		lender += "<div class=\"direct-chat-text\" style=\"text-align: -webkit-left;;background-color: white;border: white;\">";
				                     		lender += "<span class=\"glyphicon glyphicon-file\" style=\"font-size: 20px;color: #d4d4d4;\"></span>";
				                     		lender += "<a href='"+'$baseUrl'+'/task/download/?file='+value.allfiles+"'><span style='vertical-align: middle'>"+value.filename+"</span></a> ";
				                     	}
				                  	lender += "</div></div>";
        }
    }); 
    $('#allcomment').html(lender);
	$('#newcomment').html('');
    newComment = "";
    var size = $('#allcomment').children().length;
    $('#inComment').scrollTop((size)*850);
}
var newComment = "";
function lenderNewComment(comment,createtime){
        newComment = newComment.concat("<div class='direct-chat-msg right'>"
        							 +"<div class=\"direct-chat-info clearfix\">"
        							 +"<span class=\"direct-chat-name pull-right\">"+'$userName'+"</span>"
        							 +"<span class=\"direct-chat-timestamp pull-left\">"+createtime+"</span>"
        							 +"</div>"
        						 	 +"<img class=\"direct-chat-img\" src=\"$pathAvartar\" alt=\"Message User Image\">"
				                     +"<div class=\"direct-chat-text\">"
				                   		+comment
				                  	 +"</div>"
				                +"</div>");
    
  	$('input[name=message]').val('');
    $('#newcomment').html(newComment);
   	$('#inComment').scrollTop( ($(document).height())+200 );
}
function lenderAddAssignee(userall,usertask){
        $.each(userall, function(index, value) {						 	 		
        lender = lender.concat("<td class=\"checkbox-col\" style=\"text-align: center;\" data-permission=\"value._id\" id=\"value._id\">"
        							 +"<input type=\"checkbox\"></td>"
        							 +"<td>"+'$userName'+"</td>");
        									 });
    	$('#addassignee').html(lender);
}
function lenderUser(data){
	var i = 1;
    var render = "";
    $.each(data, function(index, value) {
		render = render.concat("&nbsp;" + i + ". " + value['userid'] + "&#13;");
		i++;	
    });
    $('#viewAssign').html(render);
}        		
function renderLog(data){
	console.log(data);
	var render;
	if(data.length > 0 ){
		render = "<table width=\"100%\"><tr align=\"center\"><th>วันที่</th><th>ลักษณะการเข้าใช้งาน</th><th>ผู้ใช้งาน</th></tr>";
		$.each(data, function(index, value) {
		render = render.concat("<div>"
        		+"<tr><div>"
         		+"<td><small><span class=\"direct-chat-timestamp pull-left\"> "+value.editDate+" </span></small></td>"
        		+"<td><small><span class=\"direct-chat-timestamp pull-left\"> "+value.action+" </span></small></td>"
        		+"<td><small><span class=\"direct-chat-timestamp pull-left\"> "+value.userId+" </span></small></td>"
				+"<td><small><span class=\"direct-chat-timestamp pull-left\">"
        		+"<div class='dropdown'>"
				    +"<a class=\"glyphicon glyphicon-info-sign dropdown-toggle\" title="+value._id+" data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'></a>"
				  +"<ul class='dropdown-menu pull-right' aria-labelledby='dropdownMenu2' style='padding: 10px;width: 250px;'>"
				  +"<li><div class='input-group' style='display: flex;'>"
        		  +"<span class='input-group-addon'style='width: 80px;'>LOG ID</span>"
        		  +"<input id='text"+value._id+"' type='text' class='form-control  input-monospace input-sm js-zeroclipboard-target js-url-field' value="+value._id+" readonly>"
        		  +"<button data-clipboard-action='copy' data-clipboard-target='#text"+value._id+"' class='clip-board' data-copied-hint='Copied!' type='button'><i class=\"glyphicon glyphicon-copy\" style='color: black;' title='คัดลอก'></i></button>"
        		  +"</div>"
        		  +"</div></li>"
				  +"</ul>"
				+"</div>"
        		
        		+"</span></small></td>"
        		+"</div></tr>");
	});
	render += "</table>";
	}else{
		render = '';
	}
    $('#showlog').html(render);
}
var clipboard = new Clipboard('.clip-board');
clipboard.on('success', function(e) {
    console.info('Action:', e.action);
    console.info('Text:', e.text);
    console.info('Trigger:', e.trigger);
    e.clearSelection();
});
clipboard.on('error', function(e) {
    console.error('Action:', e.action);
    console.error('Trigger:', e.trigger);
});     						 	 		
$(function() {
        window.emojiPicker = new EmojiPicker({
          emojiable_selector: '[data-emojiable=true]',
          assetsPath: '../lib/img/',
          popupButtonClasses: 'fa fa-smile-o'
        });
        window.emojiPicker.discover();
      });
$(document).on('click', "#tabComment a", function() {
   	$('#inComment').scrollTop( ($(document).height())+200 );
});      					
$('#uploadFile').submit(function(e){
	$(this).ajaxSubmit({
	    async: false,
	    dataType: 'json',
		success: function (data) {
			renderNewFile(data);
		}
	});
	return false; 
});
$('#uploadImage').submit(function(e){
	$(this).ajaxSubmit({
	    async: false,
	    dataType: 'json',
		success: function (data) {
			renderNewImages(data);
		}
	});
	return false; 
});
function renderNewImages(data){
	$('#uploadImage').trigger("reset");
	$('#modalUploadImage').modal('hide');
	$.each(data, function(index, value) {
		 newComment = newComment.concat("<div class='direct-chat-msg right'>"
        		+"<div class=\"direct-chat-info clearfix\">"
        		+"<span class=\"direct-chat-name pull-right\">"+'$userName'+"</span>"
        		+"<span class=\"direct-chat-timestamp pull-left\">"+'$date'+"</span>"
        		+"</div>"
        		+"<img class=\"direct-chat-img\" src=\""+'$pathAvartar'+"\" alt=\"Message User Image\">");
					newComment += "<div class=\"direct-chat-text\" style=\"text-align: -webkit-right;;background-color: white;border: white;\">";
					newComment += "<a href='"+'$baseUrl'+'/task/download/?file='+value+"'><span class='glyphicon glyphicon-download-alt' style='font-size: 30px;color: #d4d4d4;'></span></a> ";
					newComment += '<img src=\"';
					newComment += '$path'+value;
					newComment += '\" style=\"width: 30%;\" class=\"right\"></img>'
					newComment += "</div></div>";	                     		
	});
    $('#newcomment').html(newComment);
    var size = $('#allcomment').children().length;
    var sizeNewComment = $('#newcomment').children().length;
    $('#inComment').scrollTop((size+sizeNewComment)*850);
};	        		
function renderNewFile(data){
	$('#uploadFile').trigger("reset");
	$('#modalUploadFile').modal('hide');
	$.each(data, function(index, value) {
		 newComment = newComment.concat("<div class='direct-chat-msg right'>"
        		+"<div class=\"direct-chat-info clearfix\">"
        		+"<span class=\"direct-chat-name pull-right\">"+'$userName'+"</span>"
        		+"<span class=\"direct-chat-timestamp pull-left\">"+'$date'+"</span>"
        		+"</div>"
        		+"<img class=\"direct-chat-img\" src=\""+'$pathAvartar'+"\" alt=\"Message User Image\">");
					newComment += "<div class=\"direct-chat-text\" style=\"text-align: -webkit-right;;background-color: white;border: white;\">";
					newComment += "<span class=\"glyphicon glyphicon-file\" style=\"font-size: 20px;color: #d4d4d4;\"></span>";
					newComment += "<a href='"+'$baseUrl'+'/project/download/?file='+value+"'><span style='vertical-align: middle'>"+value+"</span></a> ";
					newComment += "</div></div>";
	});
    $('#newcomment').html(newComment);
    var size = $('#allcomment').children().length;
    var sizeNewComment = $('#newcomment').children().length;
    $('#inComment').scrollTop((size+sizeNewComment)*220);      		
}
function playinterval(){
  interval = setInterval(function(){callComment();},5000); 
  return false;
}
function stopinterval(){
  clearInterval(interval); 
  return false;
}
var xhr;
function callComment(){
	var taskId = $('#taskNowId').val();
	xhr = $.ajax({
		url: '$baseUrl/task/getcomment',
		type: 'post',
		data: {
			'taskId' : taskId,
			'$csrfParam' : '$csrf',
			'projectId' : '$project'
		},
		beforeSend: function() { stopinterval() },
        complete: function() { playinterval() },
		dataType: "json",
		success: function (data) {
			console.log(data);
			var comment = data.comment;
			var commentBy = data.commentBy;
			var pathAvartar = data.pathAvartar;
			
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        	$('#modalIsDelete').modal('show');
				$('#modalView').modal('hide');
				xhr.abort();
				stopinterval();
			}else{
				lenderComment(comment, commentBy, pathAvartar);
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			stopinterval();
	    }
	});
}
$('#modalView').on('hidden.bs.modal', function (e) {
  	xhr.abort();
	stopinterval();
})
$('#modalTaskName').change(function(){
	var taskId = $('input[name=modalTaskId]').val();
	var taskName = $('input[name=modalTaskName]').val();
	if(taskName != ""){
		$.ajax({
			url: '$baseUrl/task/duplicate', 
			type: 'post',
			data: {
				'taskId' : taskId,
				'taskName' : taskName,
				'projectId' : '$project',
				'$csrfParam' : '$csrf'
			},
			dataType: "json",
			success: function (data) {
				if(data.isDuplicate){
					$('#duplicateTask').show();
				}else{
					$('#duplicateTask').hide();	
				}
			}
		});
	}
});

EOT;
$this->registerJs ( $str, View::POS_LOAD, 'form-js' );

$this->registerCssFile ( "@web/css/common/bootstrap-toastr/toastr.min.css" );
$this->registerCssFile ( "@web/css/task/task.css" );
$this->registerCssFile ( "@web/css/project/plugins-md.min.css" );
$this->registerCssFile ( "@web/css/project/jquery-ui.css" );
$this->registerCssFile ( "@web/css/project/jquery.datetimepicker.css" );
$this->registerCssFile ( "@web/css/common/styles.css" );
$this->registerCssFile ( "@web/css/task/emoji.css" );

$this->registerJsFile ( '@web/js/common/jquery-3.1.1.min.js', ['depends' => [\yii\web\JqueryAsset::className ()	] ] );
$this->registerJsFile ( '@web/js/common/jquery.validate.min.js', [ 	'depends' => [ 	\yii\web\JqueryAsset::className () 	]] );
$this->registerJsFile ( '@web/js/project/moment.min.js', [ 	'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/project/select2.full.min.js', [ 'depends' => [ \yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/css/common/bootstrap-toastr/toastr.min.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/common/ui-toastr.min.js', [ 'depends' => [ \yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/task/jquery.datetimepicker.js', [ 	'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/project/jquery.inputmask.bundle.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/project/form-input-mask.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/common/clipboard.min.js', [ 'depends' => [ \yii\web\JqueryAsset::className ()	] ] );
$this->registerJsFile ( '@web/js/task/form-validate.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/task/config.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/task/util.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/task/jquery.emojiarea.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ( '@web/js/task/emoji-picker.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/common/jquery-dateFormat.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile ('@web/js/common/jquery.redirect.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile ('@web/js/common/jquery.form.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
?>
<div class="task-index">
	<div class="box box-solid">
		<div class="box-header with-border">
			<table id="w0" class="table table-striped table-bordered detail-view">
				<tbody>	
					<tr>
						<th style="width: 25%">ชื่อโครงการ</th>
						<td><?=$projects->projectName ?></td>
					</tr>
					<tr>
						<th style="width: 25%">คำอธิบาย</th>
						<td><?=$projects->description ?></td>
					</tr>
					<tr>
						<th style="width: 25%">วันที่เริ่มต้น</th>
						<td><?=DateTime::MongoDateToDateReturnDate($projects->startDate["sec"]) ?><span>
						</span><?=DateTime::MongoDateToDateReturnTime($projects->startDate['sec']) ?></td>
					</tr>
					<tr>
						<th style="width: 25%">วันที่สิ้นสุด</th>
						<td><?=DateTime::MongoDateToDateReturnDate($projects->endDate["sec"]) ?><span>
						</span><?=DateTime::MongoDateToDateReturnTime($projects->endDate['sec']) ?></td>
					</tr>
					<tr>
						<th style="width: 25%">ผู้ที่สร้าง</th>
						<td><?=User::getUserName((string)$projects->createBy) ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>  
    <?php if($isCreate): ?> 
    <?php if($statusproject != (int)Status::CLOSE):?>
	<p align="right">
		<button id="createTask" class="btn btn-success">
			<i class="fa fa-plus"></i> สร้างงาน
		</button>
	</p>
	<?php endif;endif;?>		
    <div class="box box-solid">
		<div class="box-header with-border">
			<?php $form = ActiveForm::begin(); ?>
			<input id="projectId" type="hidden" name="projectId" value="<?=$projectId?>">
			<div class="row">
				<div class="col-md-4">
					<div class="input-group">
				      	<?php echo Html::textInput('name', $name, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่องานหรือคำค้น']);?>
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
				      	</span>
				    </div>
				</div>
				<div class="col-md-4">
					<div class="input-group">
				      <?php  echo TypeaheadBasic::widget([
								'name' => 'username',
								'value' => $username,
							    'data' =>  $arrUser,
							    'options' => ['placeholder' => 'ผู้ใช้งานระบบในโครงการ'],
							    'pluginOptions' => ['highlight'=>true],								
							]);?>
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
				      	</span>
				    </div>
				</div>
				<div class="col-md-4">
					<div class="input-group">
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">สถานะ</button>
				      	</span>
				      	<?php echo Html::dropDownList('status', $status, [0 =>'ทั้งหมด']+ Status::$arrTaskStatusSearchProject , ['id'=> 'status', 'class'=> 'form-control','onchange'=>'this.form.submit()'])?>
				    </div>
				</div>
			</div>
		<?php ActiveForm::end(); ?>
		</div>
	</div>
	<input type="hidden" name="taskNowId" id="taskNowId" value="">
	<div class="col-md-4">
		<div class="box box-default">
			<div class="box-header with-border text-center">
				<h3 class="box-title">TO DO</h3>
			</div>
			<!-- /.box-header -->
			<div class="box-body"
				style="width: auto; height: 550px; overflow-y: scroll;">
				<?php if($todo == null):?>
				<p align="center"><span style="color: gray;">ไม่พบข้อมูล</span></p>
				<?php else:?>
				<ul class="products-list product-list-in-box">
               <?php foreach($todo as $field):?>
                <li class="item">
					<div class="dashboard-stat2 bordered blue">
                  	<?php $result = CheckMember::CheckMemberInTask ( $field->assignee, $currentUser );?>
					<?php if ($field->status == Status::PREPARE_TASK || $field->status == Status::NEW_TASK):?>
					<div class="btn-group" style="float: right;">
						 <span style="text-align: right; background-color: #fff; padding-top: 1px; font-size: 12px; color: red; pointer-events: none;">
					  		<?php echo Status::$arrTaskStatus[$field->status];?>
					  	 </span>
					</div>
					<?php else:?>
                  <div class="btn-group" style="float: right;">
					  <button type="button" data-toggle="dropdown" class="btn btn-default dropdown-toggle" style="text-align: right;background-color: #fff;padding-top: 1px;font-size: 12px;  <?=(!$result)?"pointer-events: none;":"";?>">
					  <?php echo Status::$arrTaskStatus[$field->status];?>
					  </button>
								<ul class="dropdown-menu pull-right">
									<li><a href="javascript:;" class="change-status"
										data-status="<?php echo Status::DOING_TASK;?>"
										data-id="<?=$field->_id;?>">Doing</a></li>
								</ul>
				  </div>
					<?php endif;?>
						<a href="javascript:;" class="task-detail"
								data-id="<?=$field->_id;?>" style="color: black;">
								<div class="display">
									<div class="number">
										<span><?php echo $field->taskName;?></span>
									</div>
								</div>
						<div class="progress-info">
									<div class="progress">
                                       		 <span style="width: <?php echo $field->progress;?>%;" class="<?=CheckProgress::checkProgress($field->startDate, $field->endDate, $field->progress)?>">
                                             <span class="sr-only"><?php echo $field->progress;?> progress</span>
										</span>
									</div>
									<div class="status">
										<div class="status-title">progress</div>
										<div class="status-number"> <?php echo $field->progress;?>% </div>
									</div>
								</div>
						</div> 
					</a>
				</li>
                 <?php endforeach;?>
                <!-- /.item -->
				</ul>
				<?php  endif;?>
			</div>
			<!-- /.box-body -->
		</div>
	</div>
	<div class="col-md-4">
		<div class="box box-default">
			<div class="box-header with-border text-center">
				<h3 class="box-title">DOING</h3>
			</div>
			<!-- /.box-header -->
			<div class="box-body"
				style="width: auto; height: 550px; overflow-y: scroll;">
				<?php if($doing == null):?>
				<p align="center"><span style="color: gray;">ไม่พบข้อมูล</span></p>
				<?php else:?>
				<ul class="products-list product-list-in-box">
                <?php foreach($doing as $field):?>
                <li class="item">
					<div class="dashboard-stat2 bordered blue">
                  	<?php	$status = ( int ) Status::DOING_TASK;
						if ($field->status == $status) :?>
                	 <div class="btn-group" style="float: right;">
                 	 	 <?php	$result = CheckMember::CheckMemberInTask ( $field->assignee, $currentUser );?>
					 	 <button type="button" data-toggle="dropdown" class="btn btn-default dropdown-toggle" style="text-align: right;background-color: #fff;padding-top: 1px;font-size: 12px; <?=(!$result)?"pointer-events: none;":"";?>">
					 	 <?php echo Status::$arrTaskStatus[$field->status];?>
					  	 </button>
								<ul class="dropdown-menu pull-right">
									<li><a href="javascript:;" class="change-status"
										data-status="<?php echo Status::OPEN_TASK;?>"
										data-id="<?=$field->_id;?>">Open Task</a> </li>
									<li>
										<a	href="javascript:;" class="change-waitapprove"
										data-status="<?php echo Status::WAIT_APPROVE_TASK;?>"
										data-id="<?=$field->_id;?>"
										data-taskname="<?=$field->taskName;?>">Ask for Approve</a></li>
								</ul>
							</div>
					<?php else: 
								$result=false;
								$projectmanager = CheckMember::CheckProjectManager($project);
								if($projectmanager):?>
						<div class="btn-group" style="float: right;">
						<button type="button" data-toggle="dropdown" class="btn btn-default dropdown-toggle" style="text-align: right;background-color: #fff;padding-top: 1px;font-size: 12px; <?=(!$projectmanager)?"pointer-events: none;":"";?>">
					 		 <?php echo Status::$arrTaskStatus[$field->status];?>
					 	 </button>
								<ul class="dropdown-menu pull-right">
									<li><a href="javascript:;" class="change-approve"
										data-status="<?php echo Status::APPROVED_TASK;?>"
										data-id="<?=$field->_id;?>">Approve</a> </li>
									<li>
										<a	href="javascript:;" class="change-reject"
										data-status="<?php echo Status::REJECTED_TASK;?>"
										data-id="<?=$field->_id;?>"
										data-taskname="<?=$field->taskName;?>">Reject</a></li>
								</ul>
							</div>
							<?php else:?>
					 <div class="btn-group" style="float: right;">
						 <span style="text-align: right; background-color: #fff; padding-top: 1px; font-size: 12px; pointer-events: none;">
					  			<?php echo Status::$arrTaskStatus[$field->status];?>
						 </span>
					 </div>
					<?php endif; endif;?>
                              <div class="display">
								<div class="number">
									<a href="javascript:;" class="task-detail"
										data-id="<?=$field->_id;?>" style="color: black;"> <span><?php echo $field->taskName;?></span>
									</a>
								</div>
							  </div>
							<div class="progress-info">
								<div class="progress">
                                       <span style="width: <?php echo $field->progress;?>%;" class="<?=CheckProgress::checkProgress($field->startDate, $field->endDate, $field->progress)?>">
                                            <span class="sr-only"><?php echo $field->progress;?> progress</span>
											</span>			
							</div>
								<div class="status">
									<div class="status-title">progress</div>
										<div class="status-number">
                                        	<?php if($result): ?>
                                        	<?php							
												echo Html::dropDownList ( 'status', $field->progress, [ 
																			0 => '0%',
																			10 => "10%",
																			20 => "20%",
																			30 => "30%",
																			40 => "40%",
																			50 => "50%",
																			60 => "60%",
																			70 => "70%",
																			80 => "80%",
																			90 => "90%",
																			100 => "100%" 
																	], [ 
																			'id' => 'changeProgress',
																			'data-id' => $field->_id ,
																			'style'=>"color: black;"
																	] )?>
                                        
                                      	  <?php		
											 else:
													echo $field->progress . "%";
											 endif;
											?>
                             	  </div>
                               </div>
							</div>
						</div>
					</li>
                 <?php endforeach;?>
                <!-- /.item -->
				</ul>
				<?php endif;?>
			</div>
			<!-- /.box-body -->
		</div>
	</div>
	<div class="col-md-4">
		<div class="box box-default">
			<div class="box-header with-border text-center">
				<h3 class="box-title">DONE</h3>
			</div>
			<!-- /.box-header -->
			<div class="box-body" style="width: auto; height: 550px; overflow-y: scroll;">
				<?php if($done == null):?>		
				<p align="center"><span style="color: gray;">ไม่พบข้อมูล</span></p>
				<?php else:?>
				<ul class="products-list product-list-in-box">
                <?php foreach($done as $field):?>
                <li class="item">
						<div class="dashboard-stat2 bordered blue">
							<div class="btn-group" style="float: right;">
								 <span style="text-align: right; background-color: #fff; padding-top: 1px; font-size: 12px; pointer-events: none;">
					  			 	<?php echo Status::$arrTaskStatus[$field->status];?>
					  			 </span>
							</div>
							<div class="display">
								<div class="number">
									<a href="javascript:;" class="task-detail" data-id="<?=$field->_id;?>" style="color: black;">
										<span><?php echo $field->taskName;?></span>
									</a>
								</div>
							</div>
							<div class="progress-info">
								<div class="progress">
									<span style="width: <?php echo $field->progress;?>%;" class="progress-bar progress-bar-aqua">
										<span class="sr-only"><?php echo $field->progress;?> progress</span>
									</span>
								</div>
								<div class="status">
									<div class="status-title">progress</div>
									<div class="status-number"> <?php echo $field->progress;?>% </div>
								</div>
							</div>
						</div>
					</li>
                 <?php endforeach;?>
                <!-- /.item -->
				</ul>
				<?php endif;?>
			</div>
			<!-- /.box-body -->
		</div>
	</div>	
</div>
		<div class="row">
			<div class="col-md-12" style="margin-left: 10px;margin-top: 10px;">
					<span><b>หมายเหตุ แถบสีความก้าวหน้าของงานในโครงการ</b></span><br>
					<i class="fa fa-circle text-aqua"></i> สีฟ้า     งานได้รับการอนุมัติ<br>
					<i class="fa fa-circle text-green"></i> สีเขียว  %ความก้าวหน้าของงาน  ≥ 80% ของเวลางานที่ใช้ไป<br>
					<i class="fa fa-circle text-yellow"></i> สีเหลือง %ความก้าวหน้าของงาน  ≥ 50%ของเวลางานที่ใช้ไป แต่น้อยกว่า 80%<br>
		            <i class="fa fa-circle text-red"></i> สีแดง   %ความก้าวหน้าของงาน < 50%ของเวลางานที่ใช้ไป 
        	</div>
        </div>
<!-- /.modal -->
<div class="modal fade" id="sendTask" tabindex="-1" role="dialog"
	aria-hidden="myModalLabel" data-target=".bs-example-modal-sm">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close" title="ปิด">
					<span>&times;</span>
				</button>
				<div>
					<span class="modal-title2" style="font-size: 20px"></span>
				</div>
			</div>
			<div class="modal-body">
				<input type="hidden" id="modalTaskId2" name="modalTaskId2">
				 <input	type="hidden" id="status2" name="status2">
				<div class="row">
					<label class="col-md-3 control-label" style="align: right;">ชื่องาน</label>
					<div class="col-md-9">
						<span id="taskName2"></span>
					</div>

				</div>
			</div>
			<div class="modal-footer">
				<div class="text-right">
					<input id="saveApprove" type="submit" class="btn btn-success"
						value="ขออนุมัติ">
					<button id="cancel" class="btn btn-danger" data-dismiss="modal"
						aria-label="Close">ยกเลิก</button>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- /.modal -->
<!--------View Task------->
<div class="modal fade" id="modalView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close" title="ปิด">
					<span>&times;</span>
				</button>
				<div>
					<span class="modal-title" style="font-size: 20px"></span><br>
					<span id="manageTask" style="display: none;">
					<?php if($statusproject != (int)Status::CLOSE):?>
					<form action="<?=$baseUrl."/task/assign"?>" method="post" style="display: inline;">
						<input id="assign" type="hidden" name="taskId" value="">
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>"> 
						<input id="projectId" type="hidden" name="projectId" value="<?=$projectId?>">
						<button type="submit" class="btn btn-default" title="จัดการผู้รับผิดชอบ">
							<i class="glyphicon glyphicon-user"></i>
						</button>
					</form>
					<form action="javascript:;" id="editTask" style="display: inline;">
						<input id="edit" type="hidden" name="taskId" value=""> 
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>"> 
						<input id="projectId" type="hidden" name="projectId" value="<?=$projectId?>">
						<button id="editTask" class="btn btn-default" type="submit"
							title="แก้ไขงาน">
							<i class="fa fa-edit"></i>
						</button>
					</form>
					<form action="javascript:;" id="deleteTask"	style="display: inline;">
						<input id="delete" type="hidden" name="taskId" value=""> 
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
						<input id="projectId" type="hidden" name="projectId" value="<?=$projectId?>">
						<button id="deleteTask" class="btn btn-default" type="submit" title="ลบงาน">
							<i class="glyphicon glyphicon-trash"></i>
						</button>
					</form>
					<?php endif; ?>
					</span>
				</div>	
			</div>
			<!-- ********** BODY MODAL ********** -->
			<div class="modal-body">
				<section class="content-modal">
					<div class="nav-tabs-custom">
						<ul class="nav nav-tabs">
							<li class="active"><a href="#fa-icons" data-toggle="tab">รายละเอียด</a></li>
							<li><a href="#assignee" data-toggle="tab">ผู้รับผิดชอบ</a></li>
							<li><a href="#add" data-toggle="tab">เพิ่มผู้รับผิดชอบ</a></li>
							<li id="tabComment"><a href="#comment" data-toggle="tab">รายงานความก้าวหน้า</a></li>
							<li><a href="#log" data-toggle="tab">Log</a></li>		
						</ul>
						<div class="tab-content">
							<!-- Font Awesome Icons -->
							<div class="tab-pane active" id="fa-icons">
								<div class="row">
									<label class="control-label col-md-3 text-right">ชื่องาน : </label>
									<div class="col-md-9">
										<span id="viewTaskName"></span>
									</div>
								</div>
								<div class="row">
									<label class="control-label col-md-3 text-right">คำค้น : </label>
									<div class="col-md-9">
										<span id="viewTaskTag"></span>
									</div>
								</div>
								<div class="row">
									<label class="control-label col-md-3 text-right">คำอธิบาย : </label>
									<div class="col-md-9">
										<textarea  style="width: 100%" id="viewDescription"
											readonly></textarea>
									</div>
								</div>
								<div class="row">
									<label class="control-label col-md-3 text-right">วันเริ่มต้น :
									</label>
									<div class="col-md-9">
										<span id="viewStartDate"></span>
									</div>
								</div>
								<div class="row">
									<label class="control-label col-md-3 text-right">วันสิ้นสุด : </label>
									<div class="col-md-9">
										<span id="viewEndDate"></span>
									</div>
								</div>		
								<div class="row">
									<label class="control-label col-md-3 text-right">ผู้สร้าง :
									</label>
									<div class="col-md-9">
										<span id="viewCreateby"></span>
									</div>
								</div>
								<div class="row">
									<label class="control-label col-md-3 text-right">สถานะ :
									</label>
									<div class="col-md-9">
										<span id="viewStatus"></span>
									</div>
								</div>
								<div class="text-right"></div>
							</div>
							<div class="tab-pane" id="assignee"><br>
						 		 <textarea rows="10" style="width: 100%" id="viewAssign" readonly></textarea>
		            	  	</div>
		            	  	<div class="tab-pane" id="add"><br>
		            	  			<div style="width: auto;height: 300px;overflow-y: scroll;">
										<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="memberInTask">
										<thead>
										<tr>
											<th></th>
											<th></th>
										</tr>
									</thead>
											<?php foreach($arrMemberInProject as $memberproject):?>
											<tr>	
												<td class="checkbox-col" data-id="<?=$memberproject?>" style="text-align: center;">
												<input type="checkbox" id="<?=$memberproject?>"></td>
												<td><?php echo User::getUserName((string)$memberproject)?></td>												
											</tr>
											<?php endforeach;?>
										</table>
									</div>
									<div class="modal-footer">
										<div class="text-right">
											<input id="saveMember" type="submit" class="btn btn-success" value="บันทึก">
										</div>
									</div>
		            		</div>
							<div class="tab-pane" id="comment">
								<!-- DIRECT CHAT SUCCESS -->
								<div class="box box-success direct-chat direct-chat-success">
									<!-- /.box-header -->
									<div class="box-body">
										<input id="commentID" type="hidden" name="taskId" value="">
										<!-- Conversations are loaded here -->
										<div class="direct-chat-messages" id="inComment">
											<!-- Message. Default to the left -->
											<span id="allcomment"></span> <span id="newcomment"></span>
										</div>
									</div>
									<!-- /.box-body -->		
									<div class="box-footer" id="DivComment">
										<form>
										<?php if($statusproject != (int)Status::CLOSE):?>
										  <div class="input-group">
										  		<input type="text" class="form-control" name="message"  placeholder="ข้อความ..." data-emojiable="true"> 
													<span class="input-group-btn">
													 <button type="button" class="btn btn-default" title="อัพโหลดรูป" id="uploadimage"><span class="glyphicon glyphicon-picture" ></span></button>
										        	 <button type="button" class="btn btn-default" title="อัพโหลดไฟล์" id="uploadfile"><span class="glyphicon glyphicon-paperclip" ></span></button>
										           	 <button type="button" class="addComment btn btn-success btn-flat">ส่ง</button>
												    </span>
										  </div>
											<?php endif;?>
										</form>
									</div>
									<!-- /.box-footer-->
								</div>
								<!--/.direct-chat -->					
							</div>
							<div class="tab-pane" id="log">
								<!-- DIRECT CHAT SUCCESS -->
								<div class="box box-success direct-chat direct-chat-success">
									<!-- /.box-header -->
									<div class="box-body">
										<!-- Conversations are loaded here -->
										<div class="direct-chat-messages">
											<!-- Message. Default to the left -->
											<div class="direct-chat-msg">
												<!-- /.direct-chat-info -->
												<div class="col-md-12">
													<span id="showlog"></span>
												</div>
												<!-- /.direct-chat-text -->
											</div>
											<!-- /.direct-chat-msg -->
										</div>
										<!--/.direct-chat-messages-->
									</div>
									<!-- /.box-body -->
								</div>
								<!--/.direct-chat -->
							</div>
						</div>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>
<!--------Create Task------->
<div class="modal fade" id="modalTask" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<div class="text-right">
					<button type="button" class="close" data-dismiss="modal"
						aria-label="Close" title="ปิด">
						<span>&times;</span>
					</button>
				</div>
				<div>
					<span class="modal-title" style="font-size: 20px"></span>
				</div>
			</div>
			<!-- ********** BODY MODAL ********** -->
			<div class="modal-body">
				<section class="content-modal">
					<div id="duplicateTask"
						class="alert alert-warning alert-dismissible"
						style="display: none;">ฃื่องานซ้ำ</div>
					<div id="emptyTask"
						class="alert alert-warning alert-dismissible"
						style="display: none;">กรุณากรอกชื่องาน</div>	
					<div id="accessDeny" class="alert alert-danger alert-dismissible"
						style="display: none;">ขออภัย คุณไม่มีสิทธิ์สร้างงาน</div>
					<div id="accessDeny" class="alert alert-danger alert-dismissible"
						style="display: none;">ขออภัย งานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว</div>
					<form action="javascript:void(0);" id="formcreateTask" method="POST" name="formcreateTask">
						<input type="hidden" id="modalTaskId" name="modalTaskId">
						<div class="form-group">
							<label>ชื่องาน <span class="required">*</span></label> <input
								type="text" id="modalTaskName" name="modalTaskName"
								class="form-control" placeholder="ชื่องาน" maxlength="50">
						</div>
						<div class="form-group">
							<label>คำค้น </label> <input
								type="text" id="modalTaskTag" name="modalTaskTag"
								class="form-control" placeholder="คำค้น" maxlength="20">
						</div>	
						<div class="col-md-12">
							<div class="form-group" style="display: inline-flex;">
								<input id="checkallday" type="checkbox" name="checkallday">&nbsp;<label>ตลอดวัน</label>
							</div>
							<div class="form-group">
								<div class="col-md-3 text-left">
									<label>วันที่เริ่มต้น <span class="required"> * </span>
									</label>
								</div>
								<div class="col-md-3">
									<input type="text" class="form-control date-picker"
										name="startdate" placeholder="วันที่เริ่มต้น" id="from" readonly="readonly" style="cursor:default;"/>
								</div>
								<div class="col-md-3">
									<input type="text" id="fromTime"
										class="form-control date-picker" name="starttime"
										placeholder="เวลาเริ่มต้น" value="" disabled/>
								</div>
								<br>
							</div>
						</div>	
						<div class="col-md-12">
							<div class="form-group">
								<div class="col-md-3">
									<label>วันที่สิ้นสุด <span class="required"> * </span>
									</label>
								</div>
								<div class="col-md-3">
									<input type="text" class="form-control date-picker"
										name="stopdate" placeholder="วันที่สิ้นสุด" id="to" readonly="readonly" style="cursor:default;"/>
								</div>
								<div class="col-md-3">
									<input type="text" id="toTime" class="form-control date-picker"
										name="stoptime" placeholder="เวลาสิ้นสุด" value="" disabled/> <span
										id="requireDate" class="error-date"></span>
								</div>
								<br>
							</div>
						</div>		
						<div class="form-group">
							<label>คำอธิบาย</label>
							<textarea id="modalDescription" name="modalDescription"
								class="form-control" rows="3" placeholder="คำอธิบาย"
								maxlength="1000"></textarea>
						</div>
						<div class="text-right">
							<input id="save" type="submit" class="btn btn-success"
								value="บันทึก">
							<button id="cancel" class="btn btn-danger" data-dismiss="modal"
								aria-label="Close">ยกเลิก</button>
						</div>
					</form>
				</section>
			</div>
		</div>
	</div>
</div>
<!--------Delete Task------->
<div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close" title="ปิด">
					<span>&times;</span>
				</button>
				<div>
					<span class="modal-title" style="font-size: 20px"></span>
				</div>
			</div>
			<!-- ********** BODY MODAL ********** -->
			<div class="modal-body">
				<input type="hidden" id="modalTaskIddelete" name="modalTaskIddelete">
				<section class="content-modal">
					<div class="form-group">
						<label>คุณต้องการลบงานนี้ใช่หรือไม่</label>
					</div>
					<div class="text-right">
						<button id="submitDelete" type="button" class="btn btn-success">ตกลง</button>
						<button class="btn btn-danger" data-dismiss="modal"
							aria-label="Close">ยกเลิก</button>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>
<!--------UploadImage------->
<div class="modal fade" id="modalUploadImage" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด">
					<span>&times;</span>
				</button>
				<div>
					<span class="modal-title" style="font-size: 20px">อัพโหลดรูป</span>
				</div>
			</div>
			<!-- ********** BODY MODAL ********** -->
			<div class="modal-body">
						<?php 	 
						$form = ActiveForm::begin(['id'=>'uploadImage','action'=>$baseUrl.'/task/uploadimages','options' => ['enctype' => 'multipart/form-data']]);
						?>
						<input id="taskIdUploadImages" type="hidden" name="taskIdUploadImages" value="">
						<input id="projectUploadImages" type="hidden" name="projectUploadImages" value="<?=$projectId?>">
							
							
					<?php echo $form->field($modelcomment, 'images[]')->widget(FileInput::classname(), [
					    'options' => ['multiple' => true, 'accept' => 'image/*'],
					    'pluginOptions' => ['previewFileType' => 'image','maxFileSize'=>512000],
						
					])->label('รูปโปรไฟล์ <small style="font-weight: normal !important;">รูปภาพประเภท jpg/png ขนาดไม่เกิน 500KB</small>');
					 ActiveForm::end();?>
			</div>
		</div>
	</div>
</div>
<!--------UploadFile------->
<div class="modal fade" id="modalUploadFile" tabindex="-1" role="dialog"
	aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close" title="ปิด">
					<span>&times;</span>
				</button>
				<div>
					<span class="modal-title" style="font-size: 20px">อัพโหลดไฟล์</span>
				</div>
			</div>
			<!-- ********** BODY MODAL ********** -->
			<div class="modal-body">
						<?php 	 
						$form = ActiveForm::begin(['id'=>'uploadFile', 'action'=>$baseUrl.'/task/uploadfiles','options' => ['enctype' => 'multipart/form-data']]);
						?><input id="taskIdUploadFiles" type="hidden" name="taskIdUploadFiles" value="">
						<input id="projectUploadFiles" type="hidden" name="projectUploadFiles" value="<?=$projectId?>">
							
							
						<?php 	echo  $form->field($modelcomment, 'allfiles[]')->widget(FileInput::classname(), [
									    'options' => ['multiple' => true],
									    'pluginOptions' => ['previewFileType' => 'any']
									])->label('ไฟล์ <small style="font-weight: normal !important;">ขนาดไม่เกิน 3MB</small>');
			
						 ActiveForm::end();?>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modalApproveTask" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		         <div>
			        	<span class="modal-title3" style="font-size: 20px"></span>
			     </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	      		 <input type="hidden" id="modalTaskId3" name="modalTaskId3">
				 <input	type="hidden" id="status3" name="status3">
	        	 <section class="content-modal">
					    <input type="hidden" id="viewTaskId" name="viewTaskId">
                        <div class="form-group">
					      <label>คำอธิบาย</label>	      
					      <textarea id="modalCommentApprove" name="modalCommentApprove" class="form-control" rows="3" placeholder="คำอธิบาย" maxlength="100"></textarea>
					    </div>
                        <div class="text-right">
						 	<input id="approve" type="submit" class="btn btn-success" value="อนุมัติ">
						 	<button id="cancel" class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>
<!--------RejectTask------->
<div class="modal fade" id="modalRejectTask" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		         <div>
			        	<span class="modal-title4" style="font-size: 20px"></span>
			     </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
	        	<input type="hidden" id="modalTaskId4" name="modalTaskId4">
				 <input	type="hidden" id="status4" name="status4">
		        	<div id="emptyComment" class="alert alert-warning alert-dismissible" style="display: none;">
		                                          โปรดอธิบายเหตุผลที่ไม่อนุมัติปิดงาน
		            </div>
                        <div class="form-group">
					      <label>คำอธิบาย</label>
					      <span class="required" aria-required="true"> * </span>
					      <textarea id="modalCommentReject" name="modalCommentReject" class="form-control" rows="3" placeholder="คำอธิบาย" maxlength="100"></textarea>
					    </div>
                        <div class="text-right">
						 	<input id="reject" type="submit" class="btn btn-success" value="ไม่อนุมัติ">
						 	<button id="cancel" class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>
<?php
// Display Deleted Modal
echo Deleted::widget ();
// Display AccessDeny Modal
echo AccessDeny::widget ();
// Display Waiting Modal
echo Wait::widget ();
// Display Contact Admin
echo Contact::widget ();
echo Success::widget ();
?>