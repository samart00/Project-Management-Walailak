<?php


use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\web\View;
use common\libs\Permission;
use common\libs\ActiveFlag;
use backend\components\Modal;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use backend\components\Success;
use yii\base\Widget;
use yii\widgets\LinkPager;
use yii\helpers\ArrayHelper;
use backend\models\AuthAssignment;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$user = Yii::$app->user;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'การจัดการบทบาท';
$this->params['breadcrumbs'][] = $this->title;
$str2 = <<<EOT

function callGetRole(id, page, action){
	var roleData = $.ajax({
		url: '$baseUrl/role/'+action, 
		type: 'post',
		data: {
			'roleId' : id,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
    	complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากบทบาทนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else{
				if(page == 'edit'){
					$('#accessDeny').hide();
					$('#duplicateRole').hide();
					showModalEditRole(data);
				}
				if(page == 'view'){
					showModalViewRole(data);
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
		var roleId = $('input[name=modalRoleId]').val();
		var roleName = $('input[name=modalRoleName]').val();
		var description = $('textarea[name=modalDescription]').val();
        var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('roleId', roleId);
        formData.append('name', roleName);
        formData.append('description', description);
        if(roleName != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/role/"+action, false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
	        				$('#modalRole').modal('hide');
							$('#title-delete').html('เนื่องจากบทบาทนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
						}else{
							if(response.success){
								$('#modalRole').modal('hide');
								$('#success').modal('show');
								setTimeout(function(){ 
					            	location.reload();
								}, 2000);	        		
	        				}
	        				if(response.isDuplicate){
	        					$('#duplicateRole').show();
	        					$('#accessDeny').hide();
	        				}
						}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	            	$('#duplicateRole').hide();
	            }else{
					$('#modalContact').modal('show');
				}
	        };
	        request.send(formData);
	    }
};

function changeActiveFlag(roleId, activeFlag){
   	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('roleId', roleId);
        formData.append('activeFlag', activeFlag);
	        
        var request = new XMLHttpRequest();
        request.open("POST", "$baseUrl/role/changeactiveflag", false);
        request.onreadystatechange = function () {
            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
                var response = request.responseText;
                if(typeof(response) == "string"){
                    response = JSON.parse(request.responseText);
        			console.log(response);
        			$('#modalActiveFlag').modal('hide');
        			if(response.isDelete){
						$('#title-delete').html('เนื่องจากบทบาทนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
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
				$('#modalActiveFlag').modal('hide');
            	$('#modalIsAccessDeny').modal('show');
			}else{
				$('#modalContact').modal('show');
			}
        };
        request.send(formData);
};	

$('#submitDelete, #submitDeleteRole').click(function(){

	var roleId = $(this).attr('data-id');
	var name = $(this).attr('data-name');
	
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('roleId', roleId);
        formData.append('name', name);
        
		var request = new XMLHttpRequest();
		request.open("POST", "$baseUrl/role/delete", false);
		request.onreadystatechange = function () {
		if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
			var response = request.responseText;
			if(typeof(response) == "string"){
				response = JSON.parse(request.responseText);
				$('#modalDelete').modal('hide');
				$('#modalIsUsedInAuthassignment').modal('hide');
				$('#submitDeleteRole').attr('data-id', roleId);
				$('#submitDeleteRole').attr('data-name', name);
				if(response.isDelete){
	        		$('#title-delete').html('เนื่องจากบทบาทนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        		$('#modalIsDelete').modal('show');
	        	}else if(response.isActiveflag){
					$('#modalIsNotInActive').modal('show');
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
				
$('#modalRoleName').change(function(){
	var roleId = $('input[name=modalRoleId]').val();
	var roleName = $('input[name=modalRoleName]').val();
	
	if(roleName != ""){
		var roleData = $.ajax({
			url: '$baseUrl/role/duplicate', 
			type: 'post',
			data: {
				'roleId' : roleId,
				'roleName' : roleName,
				'$csrfParam' : '$csrf'
			},
			dataType: "json",
			success: function (data) {
				if(data.isDuplicate){
					$('#duplicateRole').show();
				}else{
					$('#duplicateRole').hide();	
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
$this->registerJsFile('@web/js/role/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/role/role-index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<?php if(Yii::$app->session->hasFlash('alert')):?>
    <?= \yii\bootstrap\Alert::widget([
    'body'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
    'options'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
    ])?>
<?php endif; ?>

<div class="role-index">
		<p align="right">
			<?php  if($user->can(Permission::CREATE_ROLE)){?> 
  	  		<button id="createRole" class="btn btn-success"><i class="fa fa-plus"></i> สร้างบทบาท</button>
			<?php   } ?>
		</p>
 	<div class="site-index">
  		<div class="box box-solid">
			<div class="box-header with-border">
				<?php $form = ActiveForm::begin(['action' => $baseUrl.'/role']); ?>
				<div class="row">
					<div class="col-md-6">
			            <div class="input-group">
					      <?php echo Html::textInput('name', $name, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อบทบาท']);?>
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
		<div class="panel">
			<div class="box-header with-border">
				<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/role/index']); ?>
					<div class="row">
						<input type="hidden" name="name" value="<?=$name?>">
						<input type="hidden" name="activeFlag" value="<?=$activeFlag?>">
						<input type="hidden" name="page" value="<?=$page?>">
						<input type="hidden" name="sort" value="<?=$sort?>">
						<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
					</div>
				<?php ActiveForm::end(); ?>
				<br>
				<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="sample_1" style="margin-bottom: 1px !important">
					<thead>
						<tr>
							<th class="text-center">ลำดับ</th>
							<th class="text-center"><?php echo $dataTablesSort->link('name'); ?></th>
							<th class="text-center"><?php echo $dataTablesSort->link('activeFlag'); ?></th>
							<th class="text-center"></th>
						</tr>
					</thead>
					<tbody>
					<?php 
					$count = 1;
					foreach ($listRole as $field): 
					?>
						<tr>
							<td class="text-center"><?php echo $count++; ?></td>
							<td><span><?= $field->name; ?></span></td>
							<td class="text-center">
								<?php if(!$user->can(Permission::CHANGE_STATUS_ROLE) || $field->canBeDeleted == 1){
									echo ActiveFlag::$arrActiveFlag[$field->activeFlag];
								 }else{ ?>
									<input type="checkbox" class="toggle-switch" <?php echo ($field->activeFlag == 1)?"checked":""; ?> disabled="disabled" data-toggle="toggle" data-on="เปิดใช้งาน" data-off="ปิดใช้งาน" data-style="ios" data-size="mini" data-onstyle="success" value="<?=$field->activeFlag;?>" data-id="<?=$field->_id; ?>">
								<?php } ?>
								<span style="visibility:hidden"><?=$field->activeFlag; ?></span>
							</td>
							<td class="text-center">
								<?php if ($user->can(Permission::VIEW_ROLE)){?>
									<button type="button" class="view btn btn-info glyphicon glyphicon-eye-open btn-sm" title="รายละเอียด" data-id="<?=$field->_id; ?>"></button>
								<?php }?>
								<?php if($user->can(Permission::EDIT_ROLE)){?>	
									<button type="button" class="edit btn btn-warning glyphicon glyphicon-edit btn-sm" title="แก้ไข" data-id="<?=$field->_id; ?>"></button>
								<?php }?>
								<?php if($field->activeFlag == ActiveFlag::INACTIVE && $user->can(Permission::DELETE_ROLE)){?>	
									<button type="button" class="delete btn btn-danger glyphicon glyphicon-trash btn-sm" title="ลบ" data-id="<?=$field->_id; ?>" data-name="<?=$field->name;?>" data-have-member="<?=AuthAssignment::haveMemberInRole($field->name)?>"></button>
								<?php }?>	
								<?php if($field->activeFlag == ActiveFlag::ACTIVE && $user->can(Permission::MANAGEMENT_ROLE)){ ?>
									<?php if($field->canBeDeleted != 1){ ?>
										<form action="<?=$baseUrl."/role/add"?>" method="post" style="display: inline;">
											<input type="hidden" name="id" value="<?=$field->name ?>">
											<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
											<button type="submit" class="add btn btn-success glyphicon glyphicon-cog btn-sm" title="การกำหนดสิทธิ์ในบทบาท"></a>
										</form>
									<?php } ?>
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
						รายการที่ <?php if($listRole != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
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
	</div>

	<!--------Role------->
	<div class="modal fade" id="modalRole" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <div>
	        	<span class="modal-title" style="font-size: 20px"></span>
	        </div>
	      </div>
	      <div class="modal-body">
	        <section class="content-modal">
	            <div id="duplicateRole" class="alert alert-warning alert-dismissible" style="display: none;">
	                                                   ฃื่อบทบาทซ้ำ เนื่องจากมีการใช้ชื่อบทบาทนี้แล้ว
	           </div>
	            <div id="accessDeny" class="alert alert-danger alert-dismissible" style="display: none;">
							คุณไม่มีสิทธิ์สร้างบทบาท กรุณาติดต่อผู้ดูแลระบบ
			    </div>
			    <div id="accessDeny" class="alert alert-danger alert-dismissible" style="display: none;">
							บทบาทนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว
			    </div>
	        	<form id="formRole" >
        			<input type="hidden" id="modalRoleId" name="modalRoleId">
					<div class="form-group">
				      	<label>ชื่อบทบาท <span class="required">*</span> <span id="message" style="font-style: italic;font-weight: 100;font-size: small;">ชื่อบทบาทจะนำไปใช้ในการกำหนดสิทธิ์การใช้งานในบทบาท กรุณาตรวจชื่อบทบาทให้ถูกต้อง เนื่องจากจะไม่สามารถแก้ไขได้ในภายหลัง</span></span></label>
				      	<input type="text" id="modalRoleName" name="modalRoleName" class="form-control" placeholder="ชื่อบทบาท" maxlength="50">
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
</div>

<!--------Delete Role ------->
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
					      	<label>คุณต้องการลบบทบาทนี้ใช่หรือไม่</label>
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


<!-- View Role -->
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
		        	<form action="#" id="formRole" method="POST">
						<div class="row">
                            <label class="control-label col-md-3 text-right">ชื่อบทบาท : </label>
                            <div class="col-md-9">
								<span id="viewRoleName"></span>
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
								<span id="viewactiveFlag"></span>
							</div>
                        </div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!-- Change ActiveFlag Role -->

<div class="modal fade" id="modalActiveFlag" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span class="modal-title" style="font-size: 20px">เปลี่ยนสถานะบทบาท</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** --> 
	      	<div class="modal-body">
	        	<section class="content-modal">
		        	<form action="javascript;" id="formRole" method="POST">
						<div class="form-group">
					      <label>คุณต้องการเปลี่ยนสถานะบทบาทนี้ใช่หรือไม่</label>
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

<div class="modal fade" id="modalIsUsedInAuthassignment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span style="font-size: 20px"><font color="orange"><i class="glyphicon glyphicon-info-sign"></i>  ลบบทบาท</font></span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
						<div class="form-group">
					      <label><b>บทบาทนี้มีผู้ใช้งานระบบในบทบาทแล้ว คุณต้องการลบบทบาทใช่หรือไม่</b></label>
					    </div>
					    <div class="text-right">
						 	<button id="submitDeleteRole" type="button" class="btn btn-success"  >ตกลง</button>
						 	<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
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
