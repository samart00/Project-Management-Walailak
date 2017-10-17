<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use backend\models\Task;
use backend\components\Modal;
use yii\web\View;
use common\libs\DateTime;
use common\models\User;
use backend\models\Department;
use backend\models\Project;
use yii\widgets\LinkPager;
use kartik\typeahead\TypeaheadBasic;
use backend\components\Deleted;
use backend\components\AccessDeny;
use backend\components\Wait;
use backend\components\Contact;
use backend\components\Success;
use kartik\file\FileInput;
use backend\models\Comment;

$path = Comment::getUploadUrl();
$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$userId = Yii::$app->user->identity->_id;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'อนุมัติปิดงาน';
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;
$userName= User::getUserName((string)$userId);
$now = new \MongoDate();
$date = DateTime::MongoDateToDate($now->sec);

$str2 = <<<EOT

function saveComment(id, comment, status){

		var formData = new FormData();
		formData.append('comment', comment);
		formData.append('$csrfParam', '$csrf');
		formData.append('refId', id);
		formData.append('status', status);
		
	
		var request = new XMLHttpRequest();
		var form = $(".comment-form");
		request.open("POST", "$baseUrl/approve/save", false);
		request.onreadystatechange = function () {
	        if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	    
	            var response = request.responseText;
	            if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			$('#modalApproveTask').modal('hide');
	        			if(response.isDelete){
							$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
							$('#modalRejectTask').modal('hide');
	        				$('#modalIsDelete').modal('show'); 
	        			}else if(response.isInactive){
	        				$('#modalRejectTask').modal('hide');
	        				$('#modalIsInactive').modal('show');
	        			}else if(response.isCancel){
	        				$('#modalRejectTask').modal('hide');
	        				$('#modalIsCancel').modal('show');
	        			}else if(response.isDeleteProject){
	        				$('#modalRejectTask').modal('hide');
	        				$('#modalIsProjectDelete').modal('show');
	        			}else{
		        			if(response.success){
		        				$('#modalRejectTask').modal('hide');
								$('#success').modal('show');
								setTimeout(function(){
				            		location.reload();	
								}, 2000);	
		        			}
							if(response.isEmpty){
		        				$('#emptyComment').show();
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
}

function callGetTask(id, page, action){
	     var roleData = $.ajax({
		url: "$baseUrl/approve/" + action,
		type: 'post',
		data: {
			'taskId' : id,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
    	complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else if(data.taskData.isInactive){
	        	$('#modalIsInactive').modal('show');
	        }else if(data.taskData.isCancel){
	        	$('#modalIsCancel').modal('show');
	         }else if(data.taskData.isProject){
	        	$('#modalIsDelete').modal('show');
	        }else{
				if(page == 'view'){
					showModalViewTask(data);
					playinterval();
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
$(document).on('click', "#tabComment a", function() {
   	$('#inComment').scrollTop( ($(document).height())+200 );
});	  

function playinterval(){
  interval = setInterval(function(){callComment();},5000); 
  return false;
}

function stopinterval(){
  clearInterval(interval); 
  return false;
}

function callComment(){
	var taskId = $('#taskNowId').val();
	$.ajax({
		url: '$baseUrl/approve/getcomment',
		type: 'post',
		data: {
			'taskId' : taskId,
			'$csrfParam' : '$csrf',
		},
		beforeSend: function() { stopinterval() },
        complete: function() { playinterval() },
		dataType: "json",
		success: function (data) {
			console.log(data);
			var comment = data.comment;
			var commentBy = data.commentBy;
			var pathAvartar = data.pathAvartar;
			
			lenderComment(comment, commentBy, pathAvartar);
			
		},
		error: function (xhr, ajaxOptions, thrownError) {
			stopinterval();
	    }
	});
}

$('#modalViewTask').on('hidden.bs.modal', function (e) {
  	stopinterval();
})
EOT;
$this->registerJs($str2, View::POS_END);

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/common/styles.css");
$this->registerCssFile("@web/css/approve/approve.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/bootstrap-toggle.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/approve/approve-index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<div class="approve-index">

  	<div class="site-index">
	<div class="box box-solid">
  		<div class="box-header with-border">
  			<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/approve']); ?>
  			<input type="hidden" name="per-page" value="<?=$length?>">
  			<div class="row">
  				<div class="col-md-4">
  					<div class="input-group">
				      	<?php echo Html::textInput('taskName', $taskName, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่องาน']); ?>
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
  				<div class="col-md-4">
  					<div class="input-group">
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">แผนก</button>
				      	</span>
				      	<?php 
							echo  Html::dropDownList( 'department',
								'selected option',  
								$listDepartment,
								[
									'class' => 'form-control', 'id' => 'department',
									'options' => [
											$department => ['selected' => true]
									],
									'prompt'=>'ทั้งหมด',
									'onchange'=>'this.form.submit()'
								]
							)
						?>
				    </div>
				</div>
  				
  			<?php ActiveForm::end(); ?>
  		</div>
  	</div>
  	<input type="hidden" name="taskNowId" id="taskNowId" value="">
	</div>
  	<div class="panel" style="padding: 10px;">
  		
		<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/approve']); ?>
			<div class="row">
				<input type="hidden" name="department" value="<?=$department?>">
				<input type="hidden" name="taskName" value="<?=$taskName?>">
				<input type="hidden" name="projectName" value="<?=$projectName?>">
				<input type="hidden" name="page" value="<?=$page?>">
				<input type="hidden" name="sort" value="<?=$sort?>">
				<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
			</div>
		<?php ActiveForm::end(); ?>
		<br>
		<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="approveTable" style="margin-bottom: 1px !important;border: 1px solid #f4f4f4 !important;">
			<thead>
				<tr>
					<th class="text-center"><?php echo $dataTablesSort->link('taskName'); ?></th>
					<th class="text-center">วันที่สิ้นสุด</th>
					<th class="text-center">ชื่อโครงการ</th>
					<th class="text-center">แผนก</th>
					<th class="text-center"></th>
				</tr>
			</thead>
			<tbody>
				<?php
					foreach ($model as $field):
				?>
				<tr>
					<td><span><?= $field->taskName; ?></span></td>
					<td><span><?= DateTime::MongoDateToDate($field->endDate['sec']) ?></span></td>
					<td><span><?= Project::getProjectName($field->projectId); ?></span></td>
					<td><span><?= Department::getDepartmentNameByProjectId($field->projectId); ?></span></td>
					<td class="text-center">
						<button type="button" class="view btn btn-info glyphicon glyphicon-eye-open btn-sm" title="รายละเอียด" data-id="<?= $field->_id; ?>"></button>
						<button type="button" class="approve btn btn-success glyphicon glyphicon-ok btn-sm" title="อนุมัติ" data-id="<?= $field->_id; ?>"></button>
						<button type="button" class="reject btn btn-warning glyphicon glyphicon-remove btn-sm" title="ไม่อนุมัติ" data-id="<?= $field->_id; ?>"></button>
					</td>
				</tr>
				<?php endforeach;?>

			</tbody>
		</table>
		<?php
		$total = $pagination->totalCount;
			if($total > 0){
		?>
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<hr style="border-top: 1px solid #000; !important; margin-top: 1px !important;">
				<?php $lastRecordNo = (($pagination->page+1) * $pagination->limit); 
				if ($lastRecordNo > $pagination->totalCount) $lastRecordNo = $pagination->totalCount?>
				<div class="col-md-4 dataTables_info" role="status" aria-live="polite" style="padding-left: 10px;">
					รายการที่ <?php if($model != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
				</div>
				<div class="col-md-8 dataTables_paginate paging_bootstrap_full_number text-right">
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
		<?php } ?>
	</div> 
	<!--------Modal View Task------->
	<div class="modal fade" id="modalViewTask" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
	  	<div class="modal-dialog" role="document">
	    	<div class="modal-content">
	    		<div class="modal-header">
	    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
			        <div>
			        	<span class="modal-title" style="font-size: 20px"></span>
			        </div>
	    		</div>
		     	<!-- ********** BODY MODAL ********** -->
		      	<div class="modal-body">
	        	<section class="content-modal">
		          <div class="nav-tabs-custom">
		            <ul class="nav nav-tabs">
		              <li class="active"><a href="#fa-icons" data-toggle="tab">รายละเอียด</a></li>
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
                            <label class="control-label col-md-3 text-right">ผู้รับผิดชอบ : </label>
                            <div class="col-md-9">
								<span id="viewAssign"></span>
							</div>
                        </div>
                        <div class="row">
									<label class="control-label col-md-3 text-right">ผู้สร้าง :
									</label>
									<div class="col-md-9">
										<span id="viewCreateBy"></span>
									</div>
								</div>
								<div class="row">
									<label class="control-label col-md-3 text-right">สถานะ :
									</label>
									<div class="col-md-9">
										<span id="viewStatus"></span>
									</div>
								</div>
								<div class="row">
	                            <label class="control-label col-md-3 text-right">วันที่ขออนุมัติ : </label>
	                            <div class="col-md-9">
									<span id="viewAskForApproveDate"></span>
								</div>
	                        </div>
                        <div class="text-right">
						
					
                        </div>
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
				              </div>         
				          
				            </div>
				            <!-- /.box-body -->
				            
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
  </div><!-- end class approve-index -->

<div class="modal fade" id="modalApproveTask" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		         <div>
			        	<span class="modal-title" style="font-size: 20px"></span>
			        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
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

<div class="modal fade" id="modalRejectTask" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		         <div>
			        	<span class="modal-title" style="font-size: 20px"></span>
			        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
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

<!--------Alert Project Close------->
<div class="modal fade" id="modalIsInactive" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
					      <span style="font: bold !important;">โครงการถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว</span>
					    </div>
	        	</section>
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
					      <span style="font: bold !important;">โครงการถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว</span>
					    </div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>
<!--------Alert Project Delete------->
<div class="modal fade" id="modalIsProjectDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
					      <span style="font: bold !important;">โครงการถูกลบโดยผู้ใช้ท่านอื่นแล้ว</span>
					    </div>
	        	</section>
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
