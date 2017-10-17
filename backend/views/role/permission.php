<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\web\View;
use backend\components\AccessDeny;
use backend\components\Contact;
use backend\components\Select;
use backend\components\Deleted;
use backend\components\Success;
use common\libs\Permission;
use yii\widgets\LinkPager;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['label' => 'การจัดการบทบาท', 'url' => ['index']];
$this->title = 'เพิ่มสิทธิ์';
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/assign/addMember.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.redirect.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/role/role-permission.js',['depends' => [\yii\web\JqueryAsset::className()]]);

$str2 = <<<EOT

$('#addPermission').click(function(){
	var listPermission = getAllCheck();
	if(listPermission != ""){
		var formData = new FormData();
	        formData.append('$csrfParam', '$csrf');
	        formData.append('data',JSON.stringify(listPermission));
	  	    formData.append('role', "$roleName");
		        var request = new XMLHttpRequest();
		        request.open("POST", "$baseUrl/role/addpermission", false);
		        request.onreadystatechange = function () {
		            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
		                var response = request.responseText;
		                if(typeof(response) == "string"){
		                    response = JSON.parse(request.responseText);
		        			console.log(response);
		        			if(response.isDelete || response.isClose){
								location.href="$baseUrl/role";
		        			}else{
			        			if(response.success){
			        				$('#success').modal('show');
									setTimeout(function(){ 
				            			$.redirect('$baseUrl/role/add', {'id': '$roleName','$csrfParam':'$csrf'});
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
	}else{
		$('#modalNotCheck').modal('show');									
	}
})

EOT;
$this->registerJs($str2, View::POS_END);
?>

<div class="box box-solid">
	<div class="box-header with-border">
		<div class="row">
			<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/role/permission']); ?>
			<input type="hidden" name="id" value="<?=$roleName?>">
			<input type="hidden" name="per-page" value="<?=$length?>">
			<div class="col-md-4">
				<div class="input-group">
					<?php echo Html::textInput('permissionName', $permissionName, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อสิทธิ์']);?>
				    <span class="input-group-btn">
						<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
					</span>
				</div>
			</div>
			<div class="col-md-4">
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">โมดูล</button>
					</span>
					<?php 
						echo  Html::dropDownList( 'module',
							'selected option',  
							Permission::$arrModule,
							[
								'class' => 'form-control', 'id' => 'module',
								'options' => [
									$module => ['selected' => true]
                              	],
								'prompt'=>'ทั้งหมด',
								'onchange'=>'this.form.submit()'
							]
						)
					?>
				</div>
			</div>
			<?php ActiveForm::end(); ?>
			<div class="col-md-4 text-right">
				<form action="<?=$baseUrl."/role/add"?>" method="post" style="display: inline;">
						<input type="hidden" name="id" value="<?=$roleName ?>">
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
						<button type="submit" class="btn btn-default" aria-label="Left Align" title="ย้อนกลับ">
						  <span class="" aria-hidden="true">ย้อนกลับ</span>
						</button>
					</form>
				<?php if ($user->can(Permission::ADD_PERMISSION)){?>
				<button id="addPermission" type="button" class="btn btn-success" aria-label="Left Align" title="เพิ่มสิทธิ์">
				  <span class="" aria-hidden="true">เพิ่มสิทธิ์</span>
				</button>
				<?php }?>
				
			</div>
		</div>
	</div>
</div>

<div class="panel" style="padding: 10px;">
	<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/role/permission']); ?>
		<div class="row">
			<input type="hidden" name="id" value="<?=$roleName?>">
			<input type="hidden" name="permissionName" value="<?=$permissionName?>">
			<input type="hidden" name="module" value="<?=$module?>">
			<input type="hidden" name="page" value="<?=$page?>">
			<input type="hidden" name="sort" value="<?=$sort?>">
			<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
		</div>
	<?php ActiveForm::end(); ?>
	<br>
	<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="addChildRole" style="margin-bottom: 1px !important;border: 1px solid #f4f4f4 !important;">
		<thead>
			<tr>
				<th class="text-center"><input type="checkbox" name="checkAll"></th>
				<th class="text-center"><?php echo $dataTablesSort->link('permissionName'); ?></th>
				<th class="text-center"><?php echo $dataTablesSort->link('module'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
				foreach ($listPermissionWithoutRole as $field): 
				?>
				<tr>
					<td class="text-center"><input type="checkbox" class="checkbox-col" data-id="<?=$field->_id?>"></td>
					<td><span><?php echo $field->permissionName ?></span></td>
					<td><span><?php echo $field->module ?></span></td>
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
				รายการที่ <?php if($listPermissionWithoutRole != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
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

<?php 
	// Display AccessDeny Modal
	echo AccessDeny::widget();
	// Display Contact Admin
	echo Contact::widget();
	// Display NotCheck
	echo Select::widget();
	// Display Success
	echo Success::widget();
?>