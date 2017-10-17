<?php

use backend\models\Project;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\base\Widget;
use yii\widgets\LinkPager;
use backend\components\Modal;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use backend\controllers\CommentController;
use common\libs\RoleInProject;
use common\libs\Permission;
use backend\components\Success;
use common\libs\ActiveFlag;
use common\libs\DateTime;
use yii\helpers\ArrayHelper;
use common\libs\PermissionInProject;
use common\models\User;
use kartik\file\FileInput;
use backend\models\Comment;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$userId = Yii::$app->user->identity->_id;
$path = Comment::getUploadUrl();
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'โครงการ';
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;
$userName= User::getUserName($userId);
$now = new \MongoDate();
$date = DateTime::MongoDateToDateCreate($now->sec);
$pathAvartar = User::getPhotoUserViewer($userId);

$this->registerCssFile("@web/css/common/bootstrap-toggle.min.css");
$this->registerCssFile("@web/css/common/styles.css");
$this->registerCssFile ( "@web/css/task/emoji.css" );

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/bootstrap-toggle.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/clipboard.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile ('@web/js/task/jquery.emojiarea.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/task/emoji-picker.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/task/config.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/task/util.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );
$this->registerJsFile ('@web/js/common/jquery.form.js', [ 'depends' => [ 	\yii\web\JqueryAsset::className () 	] ] );

include 'extend.php';

$str = <<<EOT
var interval = null;

$("#uploadimage").click(function(){
	$('#modalUploadImage').modal('show');
});
		
$("#uploadfile").click(function(){
	$('#modalUploadFile').modal('show');
});
		
$(".addComment").click(function(){
		debugger;
	
		var comment =  $('input[name=message]').val();
		var projectId = $('input[name=modalProjectId]').val();
		saveComment(projectId, comment);
	});

function saveComment(id, comment){
		
	if(comment.trim().length > 0){
debugger;
		var formData = new FormData();
		formData.append('comment', comment);
		formData.append('$csrfParam', '$csrf');
		formData.append('refId', id);
		
	
		var request = new XMLHttpRequest();
		var form = $(".comment-form");
		request.open("POST", "$baseUrl/comment/savecommentproject", false);
		request.onreadystatechange = function () {
	        if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	    
	            var response = request.responseText;
	            if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');	  
	        			}else{if(response.isCancel){
							$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');	  
	        			}else{
							
		        			if(response.success){
		        			debugger;
								$('.emoji-wysiwyg-editor').html("");
								lenderNewComment(comment);	 
								
								$("#commentform").get(0).reset();
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
	
	$('.project-detail').click(function(){
	debugger;
		$('.emoji-wysiwyg-editor').html("");
		var id = $(this).data('id');
		var page = 'detail';	
		var action = 'getproject';	
		callGetProject(id, page, action);
	})

function callGetProject(id, page, action){
debugger;
	var project = $.ajax({
		url: '$baseUrl/project/'+action,
		type: 'post',
		data: {
			'projectId' : id,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
        complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			console.log(data);
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else if(data.isCancel){
				$('#modalIsCancel').modal('show');
			}else{
				if(page == 'detail'){
					$('#accessDeny').hide();
					$('.fileinput-upload').attr('type','button');
					showModalDetailProject(data);
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

function showModalDetailProject(data){
	var project = data.project;
	var comment = data.comment;
	var userId = data.userId;
	var commentBy = data.commentBy;
	var pathAvartar = data.pathAvartar;
	var isAllTaskApproved = data.isAllTaskApproved;
	var haveTask = data.haveTask;
	var log = data.log;
	var permissionInProject = data.permissionInProject;
	var arrPermissionInProject = data.arrPermissionInProject;
		$('.modal-title').text(project.projectName);
		$('#modalProjectId').val(project._id);
		$('#projectUploadImages').val(project._id);
		$('#projectUploadFiles').val(project._id);
		$('#task').val(project._id);
		$('#edit').val(project._id);
		$('#cancel').attr('data-project-id',project._id);
		$('#cancel').attr('data-progress', isAllTaskApproved);
		$('#delete').attr('data-project-id',project._id);
		$('#approve').attr('data-project-id',project._id);
		$('#assignemployeetype').val(project._id);
		$('#approve').val(project.status);
		
		var havePermissionChangeStatus = $.inArray(arrPermissionInProject[5], permissionInProject);
		$('#modal-status').hide();
		if(havePermissionChangeStatus >= 0){
			if(project.status == "เปิด"){
				$('#approve').removeAttr('disabled');
				$('#approve').bootstrapToggle('on');
				$('#approve').attr('disabled','disabled');
			}else{
				$('#approve').removeAttr('disabled');
				$('#approve').bootstrapToggle('off');
				$('#approve').attr('disabled','disabled');
			}
		}else{
			$('#approve').bootstrapToggle('destroy');
			$('#approve').hide();
			$('#modal-status').html(project.status);
			$('#modal-status').show();
		}
		$('#newcomment').html('');
		$('#modal-abbr-project').text(project.abbrProjectName);
		$('#modal-description').text(project.description);
		$('#modal-start-date').text(project.startDate);
		$('#modal-end-date').text(project.endDate);
		$('#modal-project-type').text(project.category);
		$('#modal-status').text(project.status);
		$('#modal-create-date').text(project.createDate);
		$('#modal-create-by').text(project.createBy);
		$('#modal-department-name').text(project.departmentId);	
		lenderComment(comment, commentBy, pathAvartar);
		renderLog(log);
		
		if(project.category == ""){
			hideButtonFromCategory(permissionInProject, arrPermissionInProject);
		}else{
			hideButtonFromStatus(project.status, permissionInProject, arrPermissionInProject);
		}
		
		if(haveTask){
			$('#delete').attr('title', 'ปิดใช้งานโครงการ หากต้องการเปิดใช้งานอีกครั้งโปรดติดต่อผู้ดูแลระบบ');
		}else{
			$('#delete').attr('title', 'ลบโครงการ');
		}
		
		renderUser(data.users);
		playinterval();
		$('#myModal').modal('show');
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

function hideButtonFromCategory(permissionInProject, arrPermissionInProject){
	var havePermissionEdit = $.inArray(arrPermissionInProject[2], permissionInProject);
	$('#buttonEdit').hide();
	if(havePermissionEdit >= 0){
		$('#buttonEdit').show();
	}
	$('#taskInProject').hide();
	$('#buttonSetting').hide();
	$('#cancel').hide();
	$('#delete').hide();
}


function hideButtonFromStatus(status, permissionInProject, arrPermissionInProject){
	var havePermissionTaskInProject = $.inArray(arrPermissionInProject[0], permissionInProject);	
	var havePermissionSetting = $.inArray(arrPermissionInProject[1], permissionInProject);
	var havePermissionEdit = $.inArray(arrPermissionInProject[2], permissionInProject);
	var havePermissionCancel = $.inArray(arrPermissionInProject[3], permissionInProject);
	var havePermissionDelete = $.inArray(arrPermissionInProject[4], permissionInProject);
	
	$('#taskInProject').hide();
	$('#buttonSetting').hide();
	$('#buttonEdit').hide();
	$('#cancel').hide();
	$('#delete').hide();
		
	if(status == "เปิด"){
		if(havePermissionTaskInProject >= 0){
			$('#taskInProject').show();
		}
		if(havePermissionSetting >= 0){
			$('#buttonSetting').show();
		}
		if(havePermissionEdit >= 0){
			$('#buttonEdit').show();
		}
		if(havePermissionCancel >= 0){
			$('#cancel').show();
		}
		if(havePermissionDelete >= 0){
			$('#delete').show();
		}	
	}else{
		if(havePermissionTaskInProject >= 0){
			$('#taskInProject').show();
		}	
	}
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
   	
    $('#allcomment').html(lender);
    $('#newcomment').html('');
    newComment = "";
    var size = $('#allcomment').children().length;
    $('#inComment').scrollTop((size)*850);
}

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

$('#cancel').click(function(){
	var projectId = $(this).data('project-id');
	var isAllAprove = $(this).attr('data-progress');
	$('#submitCancel').attr('data-id', projectId);
	if(isAllAprove == "true"){
		$('#isAllApprove').html('');
		$('#isAllApprove').hide();
	}else{
		$('#isAllApprove').html('โครงการนี้ยังมีงานที่ยังไม่เสร็จสิ้น');
		$('#isAllApprove').show();
	}
	$('#modalCancel').modal('show');
});
		
$('#submitCancel').click(function(){
	var projectId = $(this).attr('data-id');
	var formData = new FormData();
	formData.append('$csrfParam', '$csrf');
	formData.append('projectId', projectId);
	
	var request = new XMLHttpRequest();
		var form = $(".comment-form");
		request.open("POST", "$baseUrl/project/cancel", false);
		request.onreadystatechange = function () {
	        if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	    
	            var response = request.responseText;
	            if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
						$('#myModal').modal('hide');
	        			$('#modalCancel').modal('hide');
	        			if(response.isDelete){
	        				$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else if(response.isCancel){
	        			
							$('#modalIsCancel').modal('show');
						}else if(response.isClose){
							$('#modalIsClose').modal('show');
						}else{
	        				if(response.success){
		        				$('#success').modal('show');
								setTimeout(function(){ 
					            	location.reload();
								}, 2000);
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
});
				
$('#delete').click(function(){
	var projectId = $(this).data('project-id');
	$('#submitDelete').attr('data-id', projectId);
	
	$('#modalDelete').modal('show');
});

$('#submitDelete').click(function(){
	var projectId = $(this).attr('data-id');
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('projectId', projectId);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/project/delete", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			$('#myModal').modal('hide');
	        			$('#modalDelete').modal('hide');
	        			if(response.isDelete){
	        				$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else if(response.isCancel){
							$('#modalIsCancel').modal('show');
						}else if(response.isClose){
							$('#modalIsClose').modal('show');
						}else{
		        			$('#success').modal('show');
							setTimeout(function(){ 
					            	location.reload();
							}, 2000);
			        	}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#modalDelete').modal('hide');
	        		$('#modalIsAccessDeny').modal('show');
	            }
	        };
	    request.send(formData);
});
				
$(document).on('click', ".toggle", function() {
	var toggle = $(this).children();
   	var projectId = toggle.data('project-id');
   	var status = toggle.val();
   	if(status == 'เปิด'){
   		status = 1;
   		$('#titleChangeStatus').html('ปิดโครงการ');
   		$('#quetionChangeStatus').html('คุณต้องการปิดโครงการใช่หรือไม่');
   	}else{
   		status = 2;
   		$('#titleChangeStatus').html('เปิดโครงการ');
   		$('#quetionChangeStatus').html('คุณต้องการเปิดโครงการใช่หรือไม่');
   	}
	$('#submitApprove').attr('data-id', projectId);
	$('#submitApprove').attr('data-status', status);
	
	$('#modalApprove').modal('show');
});

$('#submitApprove').click(function(){
	var projectId = $(this).attr('data-id');
	var status = $(this).attr('data-status');
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('projectId', projectId);
        formData.append('status', status);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/project/changestatus", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
						$('#myModal').modal('hide');
	        			$('#modalApprove').modal('hide');
	        			if(response.isDelete){
	        				$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else if(response.isCancel){
							$('#modalIsCancel').modal('show');
						}else{
		        			if(response.success){
		        				$('#success').modal('show');
								setTimeout(function(){ 
					            	location.reload();
								}, 2000);
			        		}
		        		
		        			if(!response.isAllTaskApprove){
		        				$('#notApprove').modal('show');
		        				setTimeout(function(){ 
					            	location.reload();
								}, 2000);
		        			}
	        			}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#modalDelete').modal('hide');
	        		$('#modalIsAccessDeny').modal('show');
	            }
	        };
	    request.send(formData);
});
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
        // Initializes and creates emoji set from sprite sheet
        window.emojiPicker = new EmojiPicker({
          emojiable_selector: '[data-emojiable=true]',
          assetsPath: 'lib/img/',
          popupButtonClasses: 'fa fa-smile-o'
        });
        // Finds all elements with `emojiable_selector` and converts them to rich emoji input fields
        // You may want to delay this step if you have dynamically created input fields that appear later in the loading process
        // It can be called as many times as necessary; previously converted input fields will not be converted again
        window.emojiPicker.discover();
      });

$(document).on('click', "#tabComment a", function() {
	var size = $('#allcomment').children().length;
   	$('#inComment').scrollTop(size*850);
});

$('.formTask').click(function(){
	var form = $(this).data('id');
	var formId = "#"+form;
	$(formId).submit();
})

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
	var projectId = $('input[name=modalProjectId]').val();
	xhr = $.ajax({
		url: '$baseUrl/project/getcomment',
		type: 'post',
		data: {
			'projectId' : projectId,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { stopinterval() },
        complete: function() { playinterval() },
		dataType: "json",
		success: function (data) {
			console.log(data);
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        	$('#modalIsDelete').modal('show');
				$('#myModal').modal("hide");
				xhr.abort();
				stopinterval();
			}else if(data.isCancel){
				$('#modalIsCancel').modal('show');
				$('#myModal').modal("hide");
				xhr.abort();
				stopinterval();
			}else{
				var comment = data.comment;
				var commentBy = data.commentBy;
				var pathAvartar = data.pathAvartar;
				
				lenderComment(comment, commentBy, pathAvartar);	
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			stopinterval();
	    }
	});
}

$('#myModal').on('hidden.bs.modal', function (e) {
  	xhr.abort();
	stopinterval();
});

EOT;

$this->registerJs($str, View::POS_LOAD, 'form-js');

?>

<?php if(Yii::$app->session->hasFlash('alert')):?>
    <?= \yii\bootstrap\Alert::widget([
    'body'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
    'options'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
    ])?>
<?php endif; ?>

<div class="project-index">
	<span id="alert"></span>
    <p align="right">
    <?php if($amount == true && $user->can(Permission::CREATE_PROJECT)){ ?>
        <?= Html::a('<i class="fa fa-plus"></i> สร้างโครงการ', ['create'], ['class' => 'btn btn-success','style'=>'text-align: right;']) ?>
    <?php } ?>
    </p>
   	<div class="site-index">
	<div class="box box-solid">
		<div class="box-header with-border">
			<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/project']); ?>
				<div class="row">
					<div class="col-md-3">
						<div class="input-group">
					      	<?php echo Html::textInput('name', $name, ['id'=> 'projectName', 'class'=> 'form-control', 'placeholder'=> 'ชื่อโครงการ']);?>
					      	<span class="input-group-btn">
					        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
					      	</span>
					    </div>
					</div>
					
					<div class="col-md-3">
						<div class="input-group">
					      	<span class="input-group-btn">
					        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">สถานะ</button>
					      	</span>
					      	<?php echo Html::dropDownList('status', $status, [0=>'ทั้งหมด']+ Project::$arrSendStatus , ['id'=> 'status', 'class'=> 'form-control','onchange'=>'this.form.submit()'])?>
					    </div>
					</div>
	
					<div class="col-md-3">
						<div class="input-group">
					      	<span class="input-group-btn">
					        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">จัดเรียง</button>
					      	</span>
					      	<?php echo Html::dropDownList('sort', $sort,  Project::$arrSort , ['id'=> 'sort', 'class'=> 'form-control' ,'onchange'=>'this.form.submit()'])?>
					    </div>
					</div>
					<div class="col-md-3">
						<div class="input-group">
					      	<span class="input-group-btn">
					        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">ตำแหน่ง</button>
					      	</span>
					      	<?php echo Html::dropDownList('type', $type,[0=>'ทั้งหมด']+ RoleInProject::$arrRole, ['id'=> 'type', 'class'=> 'form-control', 'placeholder'=> 'ตำแหน่ง', 'onchange'=>'this.form.submit()']);?>
					    </div>
					</div>
				</div>
			<?php ActiveForm::end(); ?>
		</div>
	</div>
	<?php $count = 0; 
	if($value != null):?>
	<?php foreach ($value as $field):?>
	<?php $count++; ?>
	<?php if($count == 1){?>
		<div class="row">
		<?php } ?>
			<div class="col-lg-4">
				<div class="box box-solid">
					<form id="form-<?=$field->_id?>" action="<?=$baseUrl."/task" ?>" method="post">
						<input type="hidden" name="<?=$csrfParam?>" value="<?=$csrf?>">
						<input type="hidden" name="projectId" value="<?=$field->_id ?>">
						<a href="javascript:{}" class="formTask" data-id="form-<?=$field->_id?>" style="color: black;">
							<div class="box-body-height">
								<table style="width:95%; margin: 10px" class="col-md-12">
									<tr>
										<td width="85%">
											
												<?php 
												$date1 =0;
												$date2 =0;
												if($arrdate1[(string)$field->_id] != 0 ):
												$date1=($arrdate2[(string)$field->_id]/$arrdate1[(string)$field->_id])*100;
												
												endif;
												if ($arrtask1[(string)$field->_id] != 0):
												$date2 =($arrtask2[(string)$field->_id]/$arrtask1[(string)$field->_id])*100;
												endif;
												if($field->status == 3 || $field->status == 4):
												?><font color="gray" style="font-weight: bold">
												<?php elseif ($field->status == 2):?>
												<font color="gray" style="font-weight: bold">
												<?php 
												else:
												?>
											<?php if($arrdate1[(string)$field->_id] == 0):
											 ?><font style="font-weight: bold">
											 <?php elseif($arrtask1[(string)$field->_id] == 0):
												 ?><font style="font-weight: bold">
											<?php elseif($date1 <= $date2):
												 ?><font style="font-weight: bold">
											<?php 
												elseif ($date1/2 <= $date2):
												?><font color="orange" style="font-weight: bold">
											<?php 
											else:
											?>
										<font color="red" style="font-weight: bold">
										<?php endif;
										endif;?>
								
										<?php echo $field->projectName; ?></font>
										</td>
										<td align="right" style="vertical-align: top;">
											<span ><?php echo $lebel[$field->status]; ?></span>
										</td>
									</tr>
								</table>
							</div>
						</a>
					</form>  
					<div class="box-header with-border box-height">
						
						<div class="text-left">
							<div>
								<small>
									<?php echo "ตำแหน่ง"." : ".RoleInProject::$arrRole[(int)$arrtype[(string)$field->_id]];?>
								</small>
							</div>
							<div>
								<small>
									<?php echo "วันที่สิ้นสุด"." : ".date('d/m/Y H:i:s',  strtotime('+6 Hour',$field->endDate["sec"])); ?>
								</small>
							</div>
							<div class="progress-group">
										<small>
                   							 <span class="progress-text">progress</span>
                   							 
                   						 	 <span class="progress-number"><?php echo (int)$date2;?>%</span>
                   						</small>
                    				<div class="progress sm">
                   				   		<div class="progress-bar progress-bar-aqua" style="width: <?php echo (int)$date2;?>%"></div>
                   					</div>
                 			</div>
						</div>
						
								
					</div>
					<div class="box-body-height" style="height: 25px;">
						<div class="">
							<div class="text-right">
								<a href="javascript:;" class="project-detail" title="ดูรายละเอียดโครงการ" data-id="<?=$field->_id;?>">
									<span>รายละเอียด</span>
									<i class="fa fa-angle-right"></i>&nbsp;
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php if($count == 3 ){ $count = 0;?>
		</div>
		<?php } ?>
	<?php endforeach; 
	else:?>
		<p align="center" style="font-size:160%;">ไม่พบรายการโครงการ</p>
		<?php endif;?>
			<div class="row">
				<div class="col-md-12 col-sm-12">
					<?php $lastRecordNo = (($pagination->page+1) * $pagination->limit); 
					if ($lastRecordNo > $pagination->totalCount) $lastRecordNo = $pagination->totalCount?>
					<div class="dataTables_info" role="status" aria-live="polite" style="padding-left: 10px;">
						รายการที่ <?php if($value != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
					</div>
					<div class="dataTables_paginate paging_bootstrap_full_number text-center">
						<?= LinkPager::widget([
								'pagination' => $pagination,
								'lastPageLabel'=>'หน้าสุดท้าย',
								'firstPageLabel'=>'หน้าแรก',
								'prevPageLabel' => 'ก่อนหน้า',
								'nextPageLabel' => 'ถัดไป'
						]);?>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12" style="margin-left: 10px;margin-top: 10px;">
					<span><b>หมายเหตุ สีชื่อโครงการ</b></span><br>
					<i class="fa fa-circle text-gray"></i> สีเทา  โครงการที่ปิดแล้ว<br>
					<i class="fa fa-circle text-black"></i> สีดำ  %ของงานที่อนุมัติแล้วในโครงการ  ≥ %ของเวลาที่ใช้ไปในโครงการ<br>
					<i class="fa fa-circle text-yellow"></i> สีเหลือง %ของงานที่อนุมัติแล้วในโครงการ  ≥ 50%ของเวลาที่ใช้ไปในโครงการ<br>
		            <i class="fa fa-circle text-red"></i> สีแดง   %ของงานที่อนุมัติแล้วในโครงการ  < 50%ของเวลาที่ใช้ไปในโครงการ
				</div>
          	</div>
	</div>
		  
	<!-- Modal -->
	<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
	        <div>
	        	<span class="modal-title" id="myModalLabel" style="font-size: 17px"></span></br>
	        	<form action="<?=$baseUrl."/task/index"?>" method="post" style="display: inline;">
					<input id="task" type="hidden" name="projectId" value="">
					<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
					<button id="taskInProject" type="submit" class="btn btn-default" title="งานในโครงการ">
					<i class="glyphicon glyphicon-list"></i>
				</button>
				</form>
				<form id="buttonEdit" action="<?=$baseUrl."/project/edit"?>" method="post" style="display: inline;">
					<input id="edit" type="hidden" name="projectId" value="">
					<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
					<button type="submit" class="btn btn-default" title="แก้ไขโครงการ">
						<i class="fa fa-edit"></i>
					</button>
				</form>
				<button id="delete" type="button" class="btn btn-default" title="ลบโครงการ">
					<i class="glyphicon glyphicon-trash"></i>
				</button>
				<button id="cancel" type="button" class="btn btn-default" title="ยกเลิกโครงการ">
					<i class="fa fa-ban"></i>
				</button>
		
				<form id="buttonSetting" action="<?=$baseUrl."/project/setting"?>" method="post" style="display: inline;">
						<input id="assignemployeetype" type="hidden" name="projectId" value="">
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
						<button type="submit" class="btn btn-default" title="ตั้งค่าโครงการ">
							<i class="fa fa-cogs"></i>
						</button>
				</form>				
				
	        </div>
	      </div>
	     <!-- ********** BODY MODAL ********** -->
	      <div class="modal-body">
	        <section class="content-modal">
		          <div class="nav-tabs-custom">
		            <ul class="nav nav-tabs">
		              <li class="active"><a href="#fa-icons" data-toggle="tab">รายละเอียด</a></li>
		              <li><a href="#member" data-toggle="tab">สมาชิก</a></li>
		              <li id="tabComment"><a href="#comment" data-toggle="tab">Comment</a></li>
		              <li><a href="#log" data-toggle="tab">Log</a></li>
		            </ul>
		            <!-- ********** TAB DETAIL ********** -->
		            <div class="tab-content">
		              <!-- Font Awesome Icons -->
		              <div class="tab-pane active" id="fa-icons">
		              <input type="hidden" id="modalProjectId" name="modalProjectId"><br>
		              		<div class="row">
                                <label class="control-label col-md-3 text-right">ชื่อย่อโครงการ : </label>
                                <div class="col-md-9">
                                    <span id="modal-abbr-project"></span>
                                </div>
                            </div>
                            
		              		<div class="row">
                                <label class="control-label col-md-3 text-right">รายละเอียด : </label>
                                <div class="col-md-9">
                                	<textarea rows="3" style="width: 100%" id="modal-description" readonly></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-3 text-right">วันที่เริ่มต้น : </label>
                                <div class="col-md-9">
                                    <span id="modal-start-date"></span>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-3 text-right">วันที่สิ้นสุด : </label>
                                <div class="col-md-9">
                                    <span id="modal-end-date"></span>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-3 text-right">ประเภทโครงการ : </label>
                                <div class="col-md-9">
                                    <span id="modal-project-type"></span>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-3 text-right">แผนก : </label>
                                <div class="col-md-9">
                                    <span id="modal-department-name"></span>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-3 text-right">ผู้สร้าง : </label>
                                <div class="col-md-9">
                                    <span id="modal-create-by"></span>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-3 text-right">วันที่สร้าง : </label>
                                <div class="col-md-9">
                                    <span id="modal-create-date"></span>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-3 text-right">สถานะ : </label>
                                <div class="col-md-9">
                                    <span id="modal-status"></span>
                                    <input id="approve" type="checkbox" class="toggle-switch" data-toggle="toggle" data-on="เปิด" data-off="ปิด" data-style="ios" data-size="mini" data-onstyle="success">
                                </div>
                            </div>
		              </div>
		              <!-- /#fa-icons -->
		              
		              
		              <!-- ********** TAB MEMBER ********** -->
		              <!-- glyphicons-->
		              <div class="tab-pane" id="member"><br>
						  <textarea rows="10" style="width: 100%" id="viewAssign" readonly></textarea>
		              </div>
		              <!-- /#ion-icons -->
		              
					  <!-- ********** TAB LOG ********** -->
		              <!-- glyphicons-->
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
		              <!-- /#ion-icons -->
		              
		              <!-- ********** TAB COMMENT ********** -->
		              <!-- glyphicons-->
		              <div class="tab-pane" id="comment">
		
				          <!-- DIRECT CHAT SUCCESS -->
				          <div class="box box-success direct-chat direct-chat-success">
				            <!-- /.box-header -->
				            <div class="box-body">
				            <input type="hidden" id="modalProjectId" name="modalProjectId">
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
		              <!-- /#ion-icons -->
		
		            </div>
		            <!-- /.tab-content -->
		          </div>
		        <!-- /.col -->
		      <!-- /.row -->
		    </section>
	      </div>
	    </div>
	  </div>
	</div>
</div>

<!--------Change ActiveFlag Project------->
<div class="modal fade" id="modalCancel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span style="font-size: 20px">ยกเลิกโครงการ</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
		        	<form action="javascript;" id="formCategory" method="POST">
						<div class="form-group">
					       <label>คุณต้องการยกเลิกโครงการนี้ใช่หรือไม่ </label> <label id="isAllApprove"> โครงการนี้ยังมีงานที่ยังไม่เสร็จสิ้น</label>
					    </div>
					    <div class="text-right">
						 	<input type="button" id="submitCancel" class="btn btn-success" value="ตกลง">
						 	<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Delete Project------->
<div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span style="font-size: 20px">ลบโครงการ</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
					<div class="form-group">
					     <label>คุณต้องการลบโครงการนี้ใช่หรือไม่</label>
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

<!--------Approve Project------->
<div class="modal fade" id="modalApprove" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span style="font-size: 20px" id="titleChangeStatus"></span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
					<div class="form-group">
					     <label id="quetionChangeStatus"></label>
					</div>
					<div class="text-right">
						 <button id="submitApprove" type="button" class="btn btn-success">ตกลง</button>
						 <button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
					</div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<div class="modal fade" id="notApprove" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm" style="pointer-events: none;">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
		        <div>
		        	<span style="font-size: 20px"><i class="icon fa fa-close" style="color:red;"></i> ไม่สามารถปิดโครงการได้</span>
		        </div>
    		</div>
    	</div>
  	</div>
</div>

<!--------Alert Project Cancel------->
<div class="modal fade" id="modalIsCancel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span style="font-size: 20px"><font color="red"><i class="icon fa fa-ban"></i>  ผิดพลาด</font></span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
						<div class="form-group">
					      <span style="font: bold !important;">โครงการถูกยกเลิก</span>
					    </div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Alert Project Close------->
<div class="modal fade" id="modalIsClose" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span style="font-size: 20px"><font color="red"><i class="icon fa fa-ban"></i>  ผิดพลาด</font></span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
						<div class="form-group">
					      <span style="font: bold !important;">โครงการถูกปิด</span>
					    </div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>
<div class="modal fade" id="modalUploadImage" tabindex="-1" role="dialog"
	aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
						$form = ActiveForm::begin(['id'=>'uploadImage','action'=>$baseUrl.'/project/uploadimages','options' => ['enctype' => 'multipart/form-data']]);
						?>
						<input id="projectUploadImages" type="hidden" name="projectUploadImages" value="">
							
							
				<?php 	echo $form->field($modelcomment, 'images[]')->widget(FileInput::classname(), [
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
				$form = ActiveForm::begin(['id'=>'uploadFile','action'=>$baseUrl.'/project/uploadfiles','options' => ['enctype' => 'multipart/form-data']]);
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
	// Display Success
	echo Success::widget();
?>