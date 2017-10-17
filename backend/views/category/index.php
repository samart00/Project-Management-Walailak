<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\web\View;
use common\libs\ActiveFlag;
use common\libs\Permission;
use backend\components\Modal;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use backend\components\Success;
use yii\widgets\LinkPager;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ประเภทโครงการ';
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;
$str2 = <<<EOT

function callGetCategory(id, page, action){
	var categoryData = $.ajax({
		url: '$baseUrl/category/'+action,
		type: 'post',
		data: {
			'categoryId' : id,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
        complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			console.log(data);
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากประเภทโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else{
				if(page == 'edit'){
					$('#accessDeny').hide();
					$('#duplicateCategory').hide();
					showModalEditCategory(data);
				}
				if(page == 'view'){
					showModalViewCategory(data);
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

function submit(action){
	var categoryId = $('input[name=modalCategoryId]').val();
	var categoryName = $('input[name=modalCategoryName]').val();
	var description = $('textarea[name=modalDescription]').val();
   	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('categoryId', categoryId);
        formData.append('categoryName', categoryName);
        formData.append('description', description);
        if(categoryName != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/category/"+action, false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalCategory').modal('hide');
							$('#title-delete').html('เนื่องจากประเภทโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
							if(response.success){
								$('#modalCategory').modal('hide');
								$('#success').modal('show');
								location.reload();	        		
		        			}
		        			if(response.isDuplicate){
		        				$('#duplicateCategory').show();
		        				$('#accessDeny').hide();
		        			}	        			
	        			}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	            	$('#duplicateCategory').hide();
	            }else{
	            	$('#modalContact').modal('show');
	            }
	        };
	        request.send(formData);
	    }
};

function changeActiveFlag(categoryId, activeFlag){
        var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('categoryId', categoryId);
        formData.append('activeFlag', activeFlag);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/category/changeactiveflag", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			$('#modalActiveFlag').modal('hide');
	        			if(response.isDelete){
							$('#title-delete').html('เนื่องจากประเภทโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
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
	            }else if(request.status == 403){
	            	$('#modalActiveFlag').modal('hide');
	            	$('#modalIsAccessDeny').modal('show');
	            }else{
	            	$('#modalContact').modal('show');
	            }
	        };
	        request.send(formData);
};
	        		
$('#submitDelete').click(function(){
	$('body').addClass("loading");
	var categoryId = $(this).attr('data-id');
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('categoryId', categoryId);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/category/delete", false);
	        request.onreadystatechange = function () {
	        	$('body').removeClass("loading");
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			$('#modalDelete').modal('hide');
	        			if(response.isDelete){
	        				$('#title-delete').html('เนื่องจากประเภทโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else if(response.isUsedInProject){
	        				$('#modalIsUsedInProject').modal('show');
	        			}else if(response.isActiveflag){
							$('#modalDelete').modal('hide');
	        				$('#modalIsNotInActive').modal('show');
						}
	        			else{
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

$('#modalCategoryName').change(function(){
	var categoryId = $('input[name=modalCategoryId]').val();
	var categoryName = $('input[name=modalCategoryName]').val();
	
	if(categoryName != ""){
		$.ajax({
			url: '$baseUrl/category/duplicate', 
			type: 'post',
			data: {
				'categoryId' : categoryId,
				'categoryName' : categoryName,
				'$csrfParam' : '$csrf'
			},
			dataType: "json",
			success: function (data) {
				if(data.isDuplicate){
					$('#duplicateCategory').show();
				}else{
					$('#duplicateCategory').hide();	
				}
			}
		});
	}
});

EOT;
$this->registerJs($str2, View::POS_END);

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/common/bootstrap-toggle.min.css");
$this->registerCssFile("@web/css/common/styles.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/bootstrap-toggle.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/category/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/category/category-index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>
<div class="category-index">
  <p class="text-right">
  	  <?php if($user->can(Permission::CREATE_CATEGORY)){ ?>
  	  <button id="createCategory" class="btn btn-success"><i class="fa fa-plus"></i> สร้างประเภทโครงการ</button>
  	  <?php } ?>
  </p>
  <div class="box box-solid">
		<div class="box-header with-border">
			<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/category/index']); ?>
			<div class="row">
				<div class="col-md-6">
					<div class="input-group">
				      	<?php echo Html::textInput('categoryName', $categoryName, ['id'=> 'categoryName', 'class'=> 'form-control', 'placeholder'=> 'ชื่อประเภทโครงการ']);?>
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
				      	</span>
				    </div>
				</div>
					
				<div class="col-md-6">
					<div class="input-group">
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">สถานะ</button>
				      	</span>
				      	<?php echo Html::dropDownList('activeFlag', $activeFlag,[null=>'ทั้งหมด']+ActiveFlag::$arrActiveFlag , ['id'=> 'activeFlag', 'class'=> 'form-control','onchange'=>'this.form.submit()'])?>
				    </div>
				</div>
			</div>
			<input type="hidden" name="per-page" value="<?=$length?>">
			<?php ActiveForm::end(); ?>
		</div>
	</div>
	<div class="panel" style="padding: 10px;">
	<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/category/index']); ?>
		<div class="row">
			<input type="hidden" name="categoryName" value="<?=$categoryName?>">
			<input type="hidden" name="activeFlag" value="<?=$activeFlag?>">
			<input type="hidden" name="page" value="<?=$page?>">
			<input type="hidden" name="sort" value="<?=$sort?>">
			<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
		</div>
	<?php ActiveForm::end(); ?>
	<br>
	<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="sample_1" style="margin-bottom: 1px !important;border: 1px solid #f4f4f4 !important;">
		<thead>
			<tr>
				<th class="text-center">ลำดับ</th>
				<th class="text-center"><?php echo $dataTablesSort->link('categoryName'); ?></th>
				<th class="text-center"><?php echo $dataTablesSort->link('activeFlag'); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php 
			$count = 1;
			foreach ($listCategory as $field): 
			?>
			<tr>
				<td class="text-center"><?php echo $count++; ?></td>
				<td><span><?= $field->categoryName; ?></span></td>
				<td class="text-center">
					<?php if($user->can(Permission::CHANGE_STATUS_CATEGORY)){ ?>
						<input type="checkbox" class="toggle-switch" <?php echo ($field->activeFlag == ActiveFlag::ACTIVE)?"checked":""; ?> disabled="disabled" data-toggle="toggle" data-on="เปิดใช้งาน" data-off="ปิดใช้งาน" data-style="ios" data-size="mini" data-onstyle="success" value="<?=$field->activeFlag;?>" data-id="<?=$field->_id; ?>">
					<?php }else{
							echo ActiveFlag::$arrActiveFlag[$field->activeFlag];
						} 
					?>
					<span style="visibility:hidden"><?=$field->activeFlag; ?></span>
				</td>
				<td class="text-center">
					<?php if($user->can(Permission::VIEW_CATEGORY)){ ?>
						<button type="button" class="view btn btn-info glyphicon glyphicon-eye-open btn-sm" title="รายละเอียด" data-id="<?=$field->_id; ?>"></button>
					<?php } ?>
					<?php if($user->can(Permission::EDIT_CATEGORY)){ ?>
						<button type="button" class="edit btn btn-warning glyphicon glyphicon-edit btn-sm" title="แก้ไข" data-id="<?=$field->_id; ?>"></button>
					<?php } ?>
					<?php if($field->activeFlag == ActiveFlag::INACTIVE && $user->can(Permission::DELETE_CATEGORY)){ ?>
						<button type="button" class="delete btn btn-danger glyphicon glyphicon-trash btn-sm" title="ลบ" data-id="<?=$field->_id; ?>"></button>
					<?php } ?>
				</td>
			</tr> 
			<?php endforeach; ?>                   
		</tbody>
	</table>
	<?php
		$total = $pagination->totalCount;
		if($total == 0){
			echo "<div class='text-center' style='padding: 5px; background-color: #f4f4f4;'>"."ไม่พบข้อมูล"."</div>";
		}else{
	?>
	<div class="row">
		<div class="col-md-12 col-sm-12">
			<hr style="border-top: 1px solid #000; !important; margin-top: 1px !important;">
			<?php $lastRecordNo = (($pagination->page+1) * $pagination->limit); 
			if ($lastRecordNo > $pagination->totalCount) $lastRecordNo = $pagination->totalCount?>
			<div class="col-md-4 dataTables_info" role="status" aria-live="polite" style="padding-left: 10px;">
				รายการที่ <?php if($listCategory != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
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

<!--------Category------->
<div class="modal fade" id="modalCategory" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
	        		<div id="duplicateCategory" class="alert alert-warning alert-dismissible" style="display: none;">
		                                           ฃื่อประเภทโครงการซ้ำ เนื่องจากมีการใช้ชื่อประเภทโครงการนี้แล้ว
		            </div>
		            <div id="accessDeny" class="alert alert-danger alert-dismissible" style="display: none;">
						คุณไม่มีสิทธิ์สร้างประเภทโครงการ กรุณาติดผู้ดูแลระบบ
		            </div>
		            <div id="accessDeny" class="alert alert-danger alert-dismissible" style="display: none;">
						ประเภทโครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว
		            </div>
		            <form id="formCategory">
		        		<input type="hidden" id="modalCategoryId" name="modalCategoryId">
						<div class="form-group">
					      <label>ชื่อประเภทโครงการ <span class="required">*</span></label>
					      <input type="text" id="modalCategoryName" name="modalCategoryName" class="form-control" placeholder="ชื่อประเภทโครงการ" maxlength="50">
					    </div>
					    <div class="form-group">
					      <label>คำอธิบาย</label>
					      <textarea id="modalDescription" name="modalDescription" class="form-control" rows="3" placeholder="คำอธิบาย" maxlength="1000"></textarea>
					    </div>
					    <div class="text-right">
						 	<input id="save" type="button" class="btn btn-success" value="บันทึก">
						 	<button id="cancel" class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Delete Category------->
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
					     <label>คุณต้องการลบประเภทโครงการนี้ใช่หรือไม่</label>
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

<!--------Change ActiveFlag Category------->
<div class="modal fade" id="modalActiveFlag" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span class="modal-title" style="font-size: 20px">เปลี่ยนสถานะประเภทโครงการ</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
		        	<form action="javascript;" id="formCategory" method="POST">
						<div class="form-group">
					      <label>คุณต้องการเปลี่ยนสถานะประเภทโครงการนี้ใช่หรือไม่</label>
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

<!--------View Category------->
<div class="modal fade" id="modalView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
		        	<form action="#" id="formCategory" method="POST">
					    <div class="row">
                            <label class="control-label col-md-3 text-right">ชื่อประเภทโครงการ : </label>
                            <div class="col-md-9">
								<span id="viewCategoryName"></span>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-3 text-right">คำอธิบาย : </label>
                            <div class="col-md-9">
                            	<textarea rows="3" style="width: 100%" id="viewDescription" readonly></textarea>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-3 text-right">ผู้สร้าง : </label>
                            <div class="col-md-9">
								<span id="viewCreateBy"></span>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-3 text-right">วันที่สร้าง : </label>
                            <div class="col-md-9">
								<span id="viewCreateDate"></span>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-3 text-right">สถานะ : </label>
                            <div class="col-md-9">
								<span id="viewStatus"></span>
							</div>
                        </div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Alert IsUsedInProject Category------->
<div class="modal fade" id="modalIsUsedInProject" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
					      <label><b>ไม่สามารถลบประเภทโครงการนี้ได้เนื่องจากถูกใช้งานในโครงการ</b></label>
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
