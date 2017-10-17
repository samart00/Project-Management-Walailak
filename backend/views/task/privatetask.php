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
use common\libs\TypeTask;
use common\models\User;
use yii\widgets\LinkPager;
use kartik\file\FileInput;
use backend\models\Comment;
use kartik\typeahead\TypeaheadBasic;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$currentUser = Yii::$app->user->identity->_id;
$userId = Yii::$app->user->identity->_id;
$userName= User::getUserName((string)$userId);
$now = new \MongoDate();
$date = DateTime::MongoDateToDateCreate($now->sec);
$pathAvartar = User::getPhotoUserViewer($userId);
$path = Comment::getUploadUrl();

$this->title = 'งาน';
$this->params['breadcrumbs'][] = $this->title;

$str = <<<EOT
$("li.user-menu").click(function(){
	var strClass = $(this).attr('class');
	if(strClass.includes("user-menu open")){
		$(this).removeClass('dropdown user user-menu open').addClass('dropdown user user-menu');
	}else{
		$(this).removeClass('dropdown user user-menu').addClass('dropdown user user-menu open');
	}
});
$('#checkallday').change(function(){
		$("label.error").hide();
        if(this.checked){
			$('input[name=starttime]').val('');
			$('input[name=stoptime]').val('');
			$('#fromTime').prop('disabled', true);
            $('#toTime').prop('disabled', true);
		}else{
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

$('#createTask').click(function(){
	$('#modalTaskId').val(undefined);
	$('#modalTaskName').val(undefined);
	$('#modalDescription').val(undefined);
	$("#formTask").validate().resetForm();
	$('#accessDeny').hide();
	$('#duplicateTask').hide();
	$('.modal-title').html('สร้างงาน');
	$('#modalTaskName').html();
	$('#description').html();
	$('#modalTask').modal('show');
});
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
		
function submit(action){
		var id = $('input[name=modalTaskId]').val();
		var allday = $('input[name=checkallday]').prop('checked');
		var taskName = $('input[name=modalTaskName]').val();
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
        formData.append('taskName', taskName);
        formData.append('allday', allday);
        formData.append('description', description);
        formData.append('startdate', startDate);
        formData.append('enddate', endDate);
        if(taskName != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/task/"+action, false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDuplicate){
		        				$('#duplicateTask').show();
		        				$('#accessDeny').hide();
	        			}else{
							if(response.success){
	        					$('#duplicateTask').hide();
		        				$('#accessDeny').hide();
	        					$('#modalTask').hide();
	        					$('#success').modal('show');
								setTimeout(function(){
            						location.reload();
								}, 2000);
		        			}else{
		        				if(response.isDelete){
		        					$('#modalTask').hide();
									$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
						        	$('#modalIsDelete').modal('show');
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
$('.task-detail').click(function(){
	$("#formTask").validate().resetForm();
	var id = $(this).data('id');
	callGetTask(id);
});
function callGetTask(id){
	var roleData = $.ajax({
		url: '$baseUrl/task/view',
		type: 'post',
		data: {
			'taskId' : id,
			'$csrfParam' : '$csrf',
			'projectId' : null
		},
		beforeSend: function() { $('#wait').modal('show'); },
    	complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			if(data.isDelete){
				
				$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else{
				$('.fileinput-upload').attr('type','button');
				showModalViewTask(data);
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
	var pathAvartar = taskData.pathAvartar;
	if(taskData.checkCreate){
		if(taskData.checkproject){
		 document.getElementById("manageTask").style.display="block";
		 document.getElementById("taskProject").style.display="none";
		}else{
		  document.getElementById("manageTask").style.display="none";
		  document.getElementById("taskProject").style.display="block";
		  $('#task').val(taskData.projectId);
		}
	}else{
		document.getElementById("manageTask").style.display="none";
	}
	$('#assign').val(taskData._id);
	$('#delete').val(taskData._id);
	$('#edit').val(taskData._id);
	$('#commentID').val(taskData._id);
	$('#taskIdUploadImages').val(taskData._id);
	$('#taskIdUploadFiles').val(taskData._id);
	$('#viewTaskName').html(taskData.taskName);
	$('#viewDescription').html(taskData.description);
	$('#viewStatus').html(taskData.status);
	$('#viewCreateby').html(taskData.createBy);
	$('#viewStartDate').html(taskData.startDate);
	$('#viewEndDate').html(taskData.endDate);
	$('.modal-title').html('รายละเอียดงาน');
	$('#modalView').modal('show');
	lenderUser(taskData.users);
	lenderComment(comment, commentBy, pathAvartar);
	$('#taskNowId').val(taskData._id);
	playinterval();
	$('#modalView').on('hidden.bs.modal', function () {
			if(taskData.changestatus){
				location.reload();
			}
	});
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
				                     		lender += "<a href='"+'$baseUrl'+'/project/download/?file='+value.allfiles+"'><span style='vertical-align: middle'>"+value.filename+"</span></a> ";
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
				                     		lender += "<a href='"+'$baseUrl'+'/project/download/?file='+value.allfiles+"'><span style='vertical-align: middle'>"+value.filename+"</span></a> ";
				                     	}
				                  	lender += "</div></div>";
        }
    });
   	$('#newcomment').html("");
    $('#allcomment').html(lender);
}
$(document).on('change', "#changeProgress", function() {
	    var id = $(this).data('id');
			var progress = $(this).val();
			saveprogress(id,progress);
}); 						 	 		
function saveprogress(id,progress){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('taskId', id);
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
							}
		        		}
	        		}
				}else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	            }
	        };
	        request.send(formData);
}
$('.change-status').click(function(){
	var id = $(this).data('id');
	var status = $(this).data('status');
	saveStatus(id,status);
});
function saveStatus(id,status){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('taskId', id);
        formData.append('status', status);
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
								$('#title-delete').html('ไม่สามารถปิดงาน เนื่องจากงานยังไม่สำเร็จ');
	        					$('#modalIsDelete').modal('show');
							}else{
	        					if(response.success){
								location.reload();

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
         var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/task/delete", false);
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
	        				if(response.inProgress){
								$('#modalDelete').modal('hide');
								$('#title-delete').html('เนื่องจากงานนี้มีการดำเนินการอยู่');
	        					$('#modalIsDelete').modal('show');
	        				}else{
	        					if(response.success){
	        						$('#modalView').modal('hide');
	        						$('#modalDelete').modal('hide');
	        						$('#success').modal('show');
	        						location.reload();
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
$('#editTask').click(function(){
	var id = $('#edit').val();
	geteditTask(id);
});
function showModalEditTask(data){
	$('#modalTaskId').val(data.taskId);
	$('#modalTaskName').val(data.taskName);
	$('#modalDescription').val(data.description);
	$('#from').val(data.startDate);
	$('#fromTime').val(data.timestart);
	$('#to').val(data.endDate);
	$('#toTime').val(data.timeend);
	$('.modal-title').html('แก้ไขงาน');
	$('#modalTask').modal('show');
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
	        				$('#modalView').modal('hide');
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
							location.reload();
	        			}else{
		        			if(response.success){
		        				$('.emoji-wysiwyg-editor').html("");
								lenderNewComment(comment,response.createtime);
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
var newComment = "";
function lenderNewComment(comment){
        newComment = newComment.concat("<div class='direct-chat-msg right'>"
        							 +"<div class=\"direct-chat-info clearfix\">"
        							 +"<span class=\"direct-chat-name pull-right\">"+'$userName'+"</span>"
        							 +"<span class=\"direct-chat-timestamp pull-left\">"+'$date'+"</span>"
        							 +"</div>"
        						 	 +"<img class=\"direct-chat-img\" src=\"$pathAvartar\" alt=\"Message User Image\">"
				                     +"<div class=\"direct-chat-text\">"
				                   		+comment
				                  	 +"</div>"
				                +"</div>");
       
   	$('input[name=message]').val('');
    $('#newcomment').html(newComment);
    var size = $('#allcomment').children().length;
    var sizeNewComment = $('#newcomment').children().length;
    $('#inComment').scrollTop((size+sizeNewComment)*850);
}     						 	 		
$(function() {
	window.emojiPicker = new EmojiPicker({
		emojiable_selector: '[data-emojiable=true]',
		assetsPath: '$baseUrl'+'/lib/img/',
		popupButtonClasses: 'fa fa-smile-o'
	});
	window.emojiPicker.discover();
});      						 	 		
$(document).on('click', "#tabComment a", function() {
   	var size = $('#allcomment').children().length;
   	$('#inComment').scrollTop(size*850);
});

$("#uploadimage").click(function(){
	$('#modalUploadImage').modal('show');
});       						 	 		
$("#uploadfile").click(function(){
	$('#modalUploadFile').modal('show');
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
			'$csrfParam' : '$csrf'
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
				'projectId' : null,
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
$this->registerJs($str, View::POS_LOAD, 'form-js');

$this->registerCssFile ( "@web/css/task/task.css" );
$this->registerCssFile ( "@web/css/project/plugins-md.min.css" );
$this->registerCssFile ( "@web/css/project/jquery-ui.css" );
$this->registerCssFile ( "@web/css/project/jquery.datetimepicker.css" );
$this->registerCssFile ( "@web/css/common/styles.css" );
$this->registerCssFile ( "@web/css/task/emoji.css" );

$this->registerJsFile ('@web/js/common/jquery-3.1.1.min.js', ['depends' => [\yii\web\JqueryAsset::className ()	] ] );
$this->registerJsFile ('@web/js/common/jquery.validate.min.js', [ 	'depends' => [ 	\yii\web\JqueryAsset::className () 	]] );
$this->registerJsFile ('@web/js/project/moment.min.js', [ 	'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/project/select2.full.min.js', [ 'depends' => [ \yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/css/common/bootstrap-toastr/toastr.min.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/common/ui-toastr.min.js', [ 'depends' => [ \yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/project/jquery.validate.min.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/task/jquery.datetimepicker.js', [ 	'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/project/validate-date-time.js', [ 	'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/task/setting-date-time.js', [ 	'depends' => [ 	\yii\web\JqueryAsset::className ()	] ] );
$this->registerJsFile ('@web/js/project/jquery.inputmask.bundle.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/project/form-input-mask.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/common/clipboard.min.js', [ 'depends' => [ \yii\web\JqueryAsset::className ()	] ] );
$this->registerJsFile ('@web/js/common/jquery-dateFormat.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile ('@web/js/task/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile ('@web/js/common/jquery.emojiarea.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/common/emoji-picker.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/common/config.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/common/util.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/common/jquery.form.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
?>
<div class="task-index">
	<p align="right">			
       <button id="createTask" class="btn btn-success"><i class="fa fa-plus"></i> สร้างงาน</button>
    </p>			
    <div class="box box-solid">
		<div class="box-header with-border">
		<?php $form = ActiveForm::begin(); ?>
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
				      	<?php echo Html::textInput('username', $username, ['id'=> 'username', 'class'=> 'form-control', 'placeholder'=> 'ชื่อผู้รับผิดชอบ']);?>
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
				      	</span>
				    </div>
				</div>
				<div class="col-md-4">
  					<div class="input-group">
				      	<?php  echo TypeaheadBasic::widget([
							'name' => 'projectName',
							'data' => $arrProject,
							'value' => $projectName,
						    'options' => ['placeholder' => 'ชื่อโครงการ'],
						    'pluginOptions' => [
						    		'highlight'=>true
						    ]
						]);?>
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
				      	</span>
				    </div>
				</div>	
				</div>
				<br>
				<div class="row">
				<div class="col-md-4"></div>
				<div class="col-md-4">
					<div class="input-group">
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">ประเภท</button>
				      	</span>
				      	<?php echo Html::dropDownList('type', $type, [0=>'ทั้งหมด']+ TypeTask::$arrTypeTask, ['id'=> 'status', 'class'=> 'form-control','onchange'=>'this.form.submit()'])?>
				    </div>
				</div>
				<div class="col-md-4">
					<div class="input-group">
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">สถานะ</button>
				      	</span>
				      	<?php echo Html::dropDownList('status', $status, [0=>'ทั้งหมด']+ Status::$arrTaskStatus , ['id'=> 'status', 'class'=> 'form-control','onchange'=>'this.form.submit()'])?>
				    </div>
				</div>		
			</div>
		<?php ActiveForm::end(); ?>
		</div>
	</div>
   <div class="col-md-4">
		<div class="box box-default">
			<div class="box-header with-border text-center">
				<h3 class="box-title">TO DO</h3>
			</div>
			<!-- /.box-header -->
			<div class="box-body"
				style="width: auto; height: 650px; overflow-y: scroll;">
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
                  		<?php if ($field->projectId == null):?>
                  		
					  <button type="button" data-toggle="dropdown" class="btn btn-default dropdown-toggle" style="text-align: right;background-color: #fff;padding-top: 1px;font-size: 12px;  <?=(!$result)?"pointer-events: none;":"";?>">
					  <?php echo Status::$arrTaskStatus[$field->status];?>
					  </button>
								<ul class="dropdown-menu pull-right">
									<li><a href="javascript:;" class="change-status"
										data-status="<?php echo Status::DOING_TASK;?>"
										data-id="<?=$field->_id;?>">Doing</a></li>
								</ul>
								<?php else:?>
								 <span style="text-align: right; background-color: #fff; padding-top: 1px; font-size: 12px; pointer-events: none;">
					  			 	<?php echo Status::$arrTaskStatus[$field->status];?>
					  			 </span>
					 			<?php endif;?>
				  </div>
					<?php endif;?>
						<a href="javascript:;" class="task-detail" data-id="<?=$field->_id;?>" style="color: black;">
                             <div class="display"> 
                                    <div class="box-body-height">          
                                        <span><?php echo $field->taskName;?></span>     
                                    </div>
                                    <div>
								<small>
									<?php echo "วันที่สิ้นสุด"." : ".date('d/m/Y',  strtotime('+6 Hour',$field->endDate["sec"])); ?>
									<?php if($field->projectId):?>
									<br>
									<?php echo "ชื่อโครงการ"." : ".$arrProject[(string)$field->projectId]; ?>
									<?php else:?>
									<br><span >ชื่อโครงการ</span>
									<?php endif;?>
								</small>
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
				style="width: auto; height: 650px; overflow-y: scroll;">
				<?php if($doing == null):?>
				<p align="center"><span style="color: gray;">ไม่พบข้อมูล</span></p>
				<?php else:?>
				<ul class="products-list product-list-in-box">
                <?php foreach($doing as $field):?>
                <li class="item">
					<div class="dashboard-stat2 bordered blue">
					<?php if ($field->projectId == null):?>
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
										<a	href="javascript:;" class="change-status"
										data-status="<?php echo Status::COMPLETED_TASK;?>"
										data-id="<?=$field->_id;?>">Completed</a></li>
								</ul>
							</div>
					<?php else:?>
					 <div class="btn-group" style="float: right;">
						 <span style="text-align: right; background-color: #fff; padding-top: 1px; font-size: 12px; pointer-events: none;">
					  			<?php echo Status::$arrTaskStatus[$field->status];?>
						 </span>
					 </div>
					<?php endif;?>
					<?php else:?>
					<div class="btn-group" style="float: right;">
						 <span style="text-align: right; background-color: #fff; padding-top: 1px; font-size: 12px; pointer-events: none;">
					  			<?php echo Status::$arrTaskStatus[$field->status];?>
						 </span>
					 </div>
					 <?php endif;?>
                              <div class="display">
								<div class="number">
									<a href="javascript:;" class="task-detail" data-id="<?=$field->_id;?>" style="color: black;">
                             		<div class="display"> 
                                    <div class="box-body-height">     
                                    	<span><?php echo $field->taskName;?></span>   
                                    </div>
                                    <div>
									<small>
									<?php echo "วันที่สิ้นสุด"." : ".date('d/m/Y',  strtotime('+6 Hour',$field->endDate["sec"])); ?>
									<?php if($field->projectId):?>
									<br>
									<?php echo "ชื่อโครงการ"." : ".$arrProject[(string)$field->projectId]; ?>
									<?php else:?>
									<br><span >ชื่อโครงการ</span>
									<?php endif;?>
									</small>
									</div>
                                   </div>
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
										<?php if ($field->projectId == null):?>
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
										<?php else:
													echo $field->progress . "%";
										?>
										<?php endif;?>
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
			<div class="box-body" style="width: auto; height: 650px; overflow-y: scroll;">
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
                             		<div class="display"> 
                                    <div class="box-body-height">            
                                            <span><?php echo $field->taskName;?></span>     
                                    </div>
                                    <div>
									<small>
									<?php echo "วันที่สิ้นสุด"." : ".date('d/m/Y',  strtotime('+6 Hour',$field->endDate["sec"])); ?>
									<?php if($field->projectId):?>
									<br>
									<?php echo "ชื่อโครงการ"." : ".$arrProject[(string)$field->projectId]; ?>
									<?php else:?>
									<br><span >ชื่อโครงการ</span>
									<?php endif;?>
									</small>
									</div>
                           	 		</div>
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
<!-- /.modal -->
<input type="hidden" name="taskNowId" id="taskNowId" value="">
<!--------View Task------->
<div class="modal fade" id="modalView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			 <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span class="modal-title" style="font-size: 20px"></span><br>
		        	<span id="manageTask" style="display: none;">
		        	<form action="<?=$baseUrl."/task/assignprivatetask"?>" method="post" style="display: inline;">
						<input id="assign" type="hidden"  name="taskId" value="">
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">		
						<button type="submit" class="btn btn-default" title="จัดการผู้รับผิดชอบ">
						<i class="glyphicon glyphicon-user"></i>
						</button>
					</form>
					<form action="javascript:;" id="editTask" style="display: inline;">
						<input id="edit" type="hidden"  name="taskId" value="">
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
						<input id="projectId" type="hidden" name="projectId" value="<?=$projectId?>">
						<button id="editTask" class="btn btn-default" type="submit" title="แก้ไขงาน"><i class="fa fa-edit" ></i></button>
					</form>
					<form action="javascript:;" id="deleteTask" style="display: inline;">
						<input id="delete" type="hidden"  name="taskId" value="">
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
						<input id="projectId" type="hidden" name="projectId" value="<?=$projectId?>">
						<button id="deleteTask" class="btn btn-default" type="submit" title="ลบงาน"><i class="glyphicon glyphicon-trash"></i></button>
					</form>
					</span>
					<span id="taskProject" style="display: none;">
					<form action="<?=$baseUrl."/task/index"?>" method="post" style="display: inline;">
						<input id="task" type="hidden" name="projectId" value="">
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
						<button id="taskInProject" type="submit" class="btn btn-default" title="งานในโครงการ">
							<i class="glyphicon glyphicon-list"></i>
						</button>
					</form>
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
		              <li id="tabComment"><a href="#comment" data-toggle="tab">รายงานความก้าวหน้า</a></li>  
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
                            <label class="control-label col-md-3 text-right">คำอธิบาย : </label>
                            <div class="col-md-9">
								<span id="viewDescription"></span>
							</div>
                        </div>
                      
                        <div class="row">
                            <label class="control-label col-md-3 text-right">วันเริ่มต้น : </label>
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
                        <div class="text-right">
                        </div>
                        </div>
                        <div class="tab-pane" id="assignee"><br>
						 		 <textarea rows="10" style="width: 100%" id="viewAssign" readonly></textarea>
		            	</div>
                         <div class="tab-pane" id="comment">	
				          <!-- DIRECT CHAT SUCCESS -->
				          <div class="box box-success direct-chat direct-chat-success">
				            <!-- /.box-header -->
				            <div class="box-body">
				            <input id="commentID" type="hidden"  name="taskId" value="">
				              <!-- Conversations are loaded here -->
				              <div class="direct-chat-messages" id="inComment">
				                <!-- Message. Default to the left -->	
								<span id="allcomment"></span>
								<span id="newcomment"></span> 
				              </div>         
				            </div>
				            <!-- /.box-body -->
				            <div class="box-footer">
				              <form id="commentform">
				              	<div class="input-group">
									<input type="text" class="form-control" name="message"  placeholder="ข้อความ..." data-emojiable="true"> 
									<span class="input-group-btn">
										<button type="button" class="btn btn-default" title="อัพโหลดรูป" id="uploadimage"><span class="glyphicon glyphicon-picture" ></span></button>
										<button type="button" class="btn btn-default" title="อัพโหลดไฟล์" id="uploadfile"><span class="glyphicon glyphicon-paperclip" ></span></button>
										<button type="button" class="addComment btn btn-success btn-flat">ส่ง</button>
									</span>
								</div>
				              </form>
				            </div>
				            <!-- /.box-footer-->
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
<div class="row">
		<div class="col-md-12" style="margin-left: 10px;margin-top: 10px;">
			<span><b>หมายเหตุ แถบสีความก้าวหน้าของงาน</b></span><br>
			<i class="fa fa-circle text-aqua"></i> สีฟ้า     งานได้รับการอนุมัติหรือสำเร็จแล้ว<br>
			<i class="fa fa-circle text-green"></i> สีเขียว  %ความก้าวหน้าของงาน  ≥ 80% ของเวลางานที่ใช้ไป<br>
			<i class="fa fa-circle text-yellow"></i> สีเหลือง %ความก้าวหน้าของงาน  ≥ 50%ของเวลางานที่ใช้ไป แต่น้อยกว่า 80%<br>
		    <i class="fa fa-circle text-red"></i> สีแดง   %ความก้าวหน้าของงาน < 50%ของเวลางานที่ใช้ไป 
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
					<div id="accessDeny" class="alert alert-danger alert-dismissible"
						style="display: none;">ขออภัย คุณไม่มีสิทธิ์สร้างงาน</div>
					<div id="accessDeny" class="alert alert-danger alert-dismissible"
						style="display: none;">ขออภัย งานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว</div>
					<form action="javascript:void(0);" id="formTask" name="formcreateTask" method="POST">
						<input type="hidden" id="modalTaskId" name="modalTaskId">
						<div class="form-group">
							<label>ชื่องาน <span class="required">*</span></label> <input
								type="text" id="modalTaskName" name="modalTaskName"
								class="form-control" placeholder="ชื่องาน" maxlength="50">
						</div>
						<div class="form-group" style="display: inline-flex;">
							<input id="checkallday" type="checkbox" name="checkallday">&nbsp;<label>ตลอดวัน</label>
						</div>
						<div class="col-md-12">
							<div class="form-group">
								<div class="col-md-3 text-left">
									<label>วันที่เริ่มต้น <span class="required"> * </span></label>
								</div>
								<div class="col-md-3">
									<input type="text" class="form-control date-picker" name="startdate" placeholder="วันที่เริ่มต้น" id="from" readonly="readonly" style="cursor:default;"/>
								</div>
								<div class="col-md-3">
									<input type="text" id="fromTime"
										class="form-control date-picker" name="starttime"
										placeholder="เวลาเริ่มต้น" value="" disabled="disabled"/>
								</div>
							</div>
						</div>
						<br>
						<div class="col-md-12">
							<div class="form-group">
								<div class="col-md-3">
									<label>วันที่สิ้นสุด <span class="required"> * </span></label>
								</div>
								<div class="col-md-3">
									<input type="text" class="form-control date-picker"	name="stopdate" placeholder="วันที่สิ้นสุด" id="to" readonly="readonly" style="cursor:default;"/>
								</div>
								<div class="col-md-3">
									<input type="text" id="toTime" class="form-control date-picker"
										name="stoptime" placeholder="เวลาสิ้นสุด" value="" disabled="disabled" />
									<span id="requireDate" class="error-date"></span>
								</div>	
							</div>
						</div>
						<br>
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
						<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modalUploadImage" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-label="Close" title="ปิด">
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
				<?php echo $form->field($modelcomment, 'images[]')->widget(FileInput::classname(), [
					    'options' => ['multiple' => true, 'accept' => 'image/*'],
					    'pluginOptions' => ['previewFileType' => 'image','maxFileSize'=>512000],
						])->label('รูปโปรไฟล์ <small style="font-weight: normal !important;">รูปภาพประเภท jpg/png ขนาดไม่เกิน 500KB</small>');
					 ActiveForm::end();?>
			</div>
		</div>
	</div>
</div>
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
				$form = ActiveForm::begin(['id'=>'uploadFile','action'=>$baseUrl.'/task/uploadfiles','options' => ['enctype' => 'multipart/form-data']]);
			?>
						<input id="projectUploadFiles" type="hidden" name="projectUploadFiles" value="">
					<?php 	echo  $form->field($modelcomment, 'allfiles[]')->widget(FileInput::classname(), [
									    'options' => ['multiple' => true],
									    'pluginOptions' => ['previewFileType' => 'any']
									])->label('ไฟล์ <small style="font-weight: normal !important;">ขนาดไม่เกิน 3MB</small>');
			
					 ActiveForm::end();?>
			</div>
		</div>
	</div>
</div>
<?php 
	// Display Deleted Modal
	echo Deleted::widget();
	// Display AccessDeny Modal
	echo AccessDeny::widget();
	// Display Waiting Modal
	echo Wait::widget();
	// Display Contact Admin
	echo Contact::widget();
	echo Success::widget();
?>
                      
