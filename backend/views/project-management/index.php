<?php

use backend\models\Project;
use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;
use yii\base\Widget;
use common\libs\ActiveFlag;
use backend\components\Modal;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use backend\components\Success;
use common\libs\Permission;
use common\libs\Status;
use yii\widgets\LinkPager;
use backend\models\Department;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$user = Yii::$app->user;

$this->title = 'การจัดการโครงการ';
$this->params['breadcrumbs'][] = $this->title;

$str = <<<EOT
$("#pManage").on('click','.project-detail', function () { 
	var id = $(this).data('id');
	//alert(id);
	var page = 'detail';	
	var action = 'getproject';	
	callGetProject(id, page, action);
})

$("#pManage").on('click','.delete', function () { 
	var id = $(this).data('id');
	var projectName = $(this).data('projectName');
	$('.modal-title').html('ลบโครงการ');
	$('#submitDelete').attr('data-id', id);
	$('#modalDelete').modal('show');
});

function changeActiveFlag(projectId, activeFlag){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('projectId', projectId);
        formData.append('activeFlag', activeFlag);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/project-management/changeactiveflag", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
 	             debugger;
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			$('#modalConfirm').modal('hide');
	        			if(response.isDelete){
							$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
		        			if(response.success){
		        				$('#success').modal('show');
								setTimeout(function(){ 
			            			location.reload();
								}, 2000); 	        		
		        			}
		        		}
	                }
	            }else if(request.status === 403){
	            	$('#modalIsAccessDeny').modal('show');
				}else{
					$('#modalContact').modal('show');
				}
	        };
		request.send(formData);
};	

$(document).on('click', ".toggle", function() {
	var toggle = $(this).children();
	var type = toggle.data('type');
	  	
	if(type == 'activeFlag'){
		var projectId = toggle.data('id');
	   	var activeFlag = toggle.val();
		$('#modalConfirm').modal('show');
		$('#submitFlag').click(function(){
			changeActiveFlag(projectId, activeFlag);
	  	});
	}
	
	if(type == 'status'){
	   	var projectId = toggle.data('id');
	   	var status = toggle.val();
	 	
		$('#modalConfirm').modal('show');
		$('#submitFlag').click(function(){
		   changeStatus(projectId, status);
	  	});
	}
});
	
function changeStatus(projectId, status){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('projectId', projectId);
        formData.append('status', status);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/project-management/changestatus", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
 	             debugger;
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			$('#modalConfirm').modal('hide');
	        			if(response.isDelete){
							$('#title-delete').html('เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
		        			if(response.success){
								$('#success').modal('show');
								setTimeout(function(){ 
			            			location.reload();
								}, 2000); 	        		
		        			}
		        		}
	                }
	            }else if(request.status === 403){
	            	$('#modalIsAccessDeny').modal('show');
				}else{
					$('#modalContact').modal('show');
				}
	        };
		request.send(formData);
};	
		

$('#submitDelete').click(function(){
	var projectId = $(this).attr('data-id');
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('projectId', projectId);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/project-management/delete", false);
	        request.onreadystatechange = function () {
	        	$('body').removeClass("loading");
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			$('#modalDelete').modal('hide');
	        			if(response.isDelete){
							$('#title-delete').html('ไม่สามารถลบได้เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
	        				if(response.success){
								$('#success').modal('show');
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

function callGetProject(id, page, action){

	var project = $.ajax({
		url: '$baseUrl/project-management/' + action,
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
				$('#title-delete').html('ไม่สามารถดูรายละเอียดได้เนื่องจากโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else{
				if(page == 'detail'){
					$('#accessDeny').hide();				
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


EOT;

$this->registerJs($str, View::POS_LOAD, 'form-js');
$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/common/bootstrap-toggle.min.css");
$this->registerCssFile("@web/css/common/styles.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/bootstrap-toggle.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project-management/project-management-index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

?>

<div class="projectmanagement-index">
  <div class="box box-solid">
		<div class="box-header with-border">
			<?php $form = ActiveForm::begin(['action' => $baseUrl.'/project-management/index']); ?>
			<div class="row">
				<div class="col-md-4">
					<div class="input-group">
				      	<?php echo Html::textInput('name', $name, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อโครงการ']);?>
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
                           	echo  Html::dropDownList( 'departmentId',
								'selected option',  
								$listDepartment,
								[
									'class' => 'form-control', 'id' => 'department',
									'options' => [
										$departmentId => ['selected' => true]
                              		],
									'prompt'=>'ทั้งหมด',
									'onchange'=>'this.form.submit()'
								]
							)
						?>
				    </div>
				</div>
				<div class="col-md-4">
					<div class="input-group">
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">สถานะ</button>
				      	</span>
				      	<?php echo Html::dropDownList('activeFlag', $activeFlag,[null=>'ทั้งหมด',1 => "ปิดใช้งาน",2 => "ยกเลิก"] , ['id'=> 'activeFlag', 'class'=> 'form-control', 'placeholder'=> 'สถานะ','onchange'=>'this.form.submit()'])?>
				    </div>
				</div>
			</div>
			<input type="hidden" name="per-page" value="<?=$length?>">
			<?php ActiveForm::end(); ?>
		</div>
	</div>
	<div class="panel" style="padding: 10px;">
	<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/project-management/index']); ?>
		<div class="row">
			<input type="hidden" name="name" value="<?=$name?>">
			<input type="hidden" name="activeFlag" value="<?=$activeFlag?>">
			<input type="hidden" name="departmentId" value="<?=$departmentId?>">
			<input type="hidden" name="page" value="<?=$page?>">
			<input type="hidden" name="sort" value="<?=$sort?>">
			<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
		</div>
	<?php ActiveForm::end(); ?>
	<br>
	<table class="table table-striped table-bordered table-hover table-responsive" width="100%" id="pManage" style="margin-bottom: 1px !important;border: 1px solid #f4f4f4 !important;">
		<thead>
			<tr>
				<th class="text-center">ลำดับ</th>
				<th class="text-center"><?php echo $dataTablesSort->link('projectName'); ?></th>
				<th class="text-center">แผนก</th>
				<th class="text-center"><?php echo $dataTablesSort->link('activeFlag'); ?></th>
				<th class="text-center"></th>
			</tr>
		</thead>
		<tbody>
			<?php 
			$count = 1;
			foreach ($listProject as $value):
				?>
			<tr>
				<td class="text-center"><?php echo $count++; ?></td>
				<td><span><?= $value->projectName; ?></span></td>
				<td><span><?= Department::getDepartmentNameByDepCode($value->departmentId) ?></span></td>
				<td class="text-center">
				<?php if($value->activeFlag == 2 && $user->can(Permission::CHANGE_STATUS_PROJECT_MANAGEMENT)){?>
					<input type="checkbox" class="toggle-switch"  disabled="disabled" data-toggle="toggle" data-on="เปิดใช้งาน" data-off="ปิดใช้งาน" data-style="ios" data-size="mini" data-onstyle="success" value="<?=$value->activeFlag;?>" data-id="<?=$value->_id; ?>" data-type="activeFlag">
					<span style="visibility:hidden"><?=$value->activeFlag; ?></span>
				<?php }else if($value->activeFlag == 2 && !$user->can(Permission::CHANGE_STATUS_PROJECT_MANAGEMENT)){
					echo ActiveFlag::$arrActiveFlag[$value->activeFlag];
				}?>
				<?php if($value->status == 3 && $user->can(Permission::CHANGE_STATUS_PROJECT_MANAGEMENT)){?>
					<input type="checkbox" class="toggle-switch"  disabled="disabled" data-toggle="toggle" data-on="เปิดใช้งาน" data-off="ยกเลิก" data-style="ios" data-size="mini" data-onstyle="success" value="<?=$value->status;?>" data-id="<?=$value->_id; ?>" data-type="status">
					<span style="visibility:hidden"><?=$value->status; ?></span>
				<?php }else if($value->status == 3 && !$user->can(Permission::CHANGE_STATUS_PROJECT_MANAGEMENT)){
					echo Status::$arrStatus[$value->status];
				}?>
				</td>
				<td class="text-center">
					<?php  if($user->can(Permission::VIEW_PROJECT_MANAGEMENT)){?>
					<button type="button" class="project-detail btn btn-info glyphicon glyphicon-eye-open btn-sm" title="ดูรายละเอียด" data-id="<?=$value->_id; ?>" data-name="<?=$value->projectName;?>"></button>
					<?php } ?>
					
					<?php  if($user->can(Permission::DELETE_PROJECT_MANAGEMENT)){?> 
					<button type="button" class="delete btn btn-danger glyphicon glyphicon-trash btn-sm" title="ลบ" data-id="<?=$value->_id; ?>" data-name="<?=$value->projectName;?>"></button>
					<?php } ?>
				</td>
			</tr> 
			  <?php endforeach; ?>                
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
				รายการที่ <?php if($value != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
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
</div>

<!-- Change ActiveFlag & Status Project -->
<div class="modal fade" id="modalConfirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span style="font-size: 20px">เปลี่ยนสถานะโครงการ</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** --> 
	      	<div class="modal-body">
	        	<section class="content-modal">
		        	<form action="javascript;" id="formRole" method="POST">
						<div class="form-group">
					      <label>คุณต้องการเปลี่ยนสถานะโครงการนี้ใช่หรือไม่</label>
					    </div>
					    <div class="text-right">
						 	<input type="button" id="submitFlag" class="btn btn-success" value="ตกลง">
						 	<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Delete Project ------->
<div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
						<div class="form-group">
					      	<label>คุณต้องการลบโครงการนี้ใช่หรือไม่</label>
					    </div>
					    <div class="text-right">
						 	<button id="submitDelete" type="button" class="btn btn-success"  >ตกลง</button>
						 	<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</section>
	      	</div>
    	</div>
  	</div>
</div>

<!-- Modal Detail Project -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
	        <div>
	        	<span class="modal-title" id="myModalLabel" style="font-size: 17px"></span></br>
	        </div>
	      </div>
	     <!-- ********** BODY MODAL ********** -->
	      <div class="modal-body">
	        <section class="content-modal">
	              <!-- Font Awesome Icons -->
		              <div class="tab-pane active" id="fa-icons">
		              <input type="hidden" id="modalProjectId" name="modalProjectId">
		              		<div class="row">
                                <label class="control-label col-md-3 text-right">รายละเอียด : </label>
                                <div class="col-md-9">
                                    <textarea rows="3" style="width: 100%" id="viewDescription" readonly></textarea>
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
		              </div>
				<!-- /#fa-icons -->								    	     
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