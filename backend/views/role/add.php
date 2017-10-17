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
use common\libs\DateTime;
use backend\components\Select;
use backend\components\Success;
use common\models\User;
use yii\widgets\LinkPager;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = ['label' => 'การจัดการบทบาท', 'url' => ['index']];
$this->title = 'การกำหนดสิทธิ์ในบทบาท';
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/assign/addMember.css");
$this->registerCssFile("@web/css/common/dataTables.checkboxes.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.redirect.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/role/role-add.js',['depends' => [\yii\web\JqueryAsset::className()]]);

$str2 = <<<EOT

$('#submit').click(function(){
	var listPermission = getAllCheck();
		var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('data',JSON.stringify(listPermission));
  	    formData.append('role', "$roleName");
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/role/removepermission", false);
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
	        					$('#modalRemovePermission').modal('hide');
								$('#success').modal('show');
								setTimeout(function(){ 
			            			location.reload();
								}, 2000);	        		
		        			}
 		        		}
	                }
	            }
				else if(request.status == 403){
	            	$('#modalDelete').modal('hide');
	            	$('#modalIsAccessDeny').modal('show');
	            }else{
	            	$('#modalContact').modal('show');
	            }
	        };
	        request.send(formData);
})

EOT;
$this->registerJs($str2, View::POS_END);
?>

<div class="box box-solid">
	<div class="box-header with-border">
		<table id="w0" class="table table-striped detail-view">
			<tbody>
				<tr>
					<th style="width:25%">ชื่อบทบาท</th>
					<td><?=$role->name ?></td>
				</tr>
				<tr>
					<th style="width:25%">คำอธิบาย</th>
					<td><?=$role->description ?></td>
				</tr>
				<tr>
					<th style="width:25%">วันที่สร้าง</th>
					<td><?=DateTime::MongoDateToDate($role->created_at) ?></td>
				</tr>
				<tr>
					<th style="width:25%">ผู้ที่สร้าง</th>
					<td><?=User::getUserName((string)$role->createBy) ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<h4>รายการสิทธิ์ในบทบาท</h4>

<div class="box box-solid">
	<div class="box-header with-border">
		<div class="row">
			<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/role/add']); ?>
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
				<a href="<?php echo $baseUrl."/role" ?>" type="button" class="btn btn-default" aria-label="Left Align" title="ย้อนกลับ">
				  <span class="" aria-hidden="true">ย้อนกลับ</span>
				</a>
				<?php if($user->can(Permission::REMOVE_PERMISSION)){ ?>
					<button id="delete" type="button" class="btn btn-danger" aria-label="Left Align" title="ลบสมาชิกในประเภทพนักงาน" disabled="disabled">
					  <span class="" aria-hidden="true">ยกเลิกสิทธิ์</span>
					</button>
				<?php } ?>
				<?php if($user->can(Permission::ADD_PERMISSION)){ ?>
					<form action="<?=$baseUrl."/role/permission"?>" method="post" style="display: inline;">
						<input type="hidden" name="id" value="<?=$roleName ?>">
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
						<button type="submit" class="btn btn-success" aria-label="Left Align" title="เพิ่มสมาชิกในประเภทพนักงาน">
						  <span class="" aria-hidden="true">เพิ่มสิทธิ์</span>
						</button>
					</form>
				<?php } ?>
			</div>
		</div>
	</div>
</div>

<div class="panel" style="padding: 10px;">
	<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/role/add']); ?>
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
	<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="childRole" style="margin-bottom: 1px !important;border: 1px solid #f4f4f4 !important;">
		<thead>
			<tr>
				<th class="text-center"><input type="checkbox" name="checkAll"></th>
				<th class="text-center"><?php echo $dataTablesSort->link('permissionName'); ?></th>
				<th class="text-center"><?php echo $dataTablesSort->link('module'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($listRoleType as $field): 
			?>
			<tr>
				<td class="text-center"><input type="checkbox" class="checkbox-col" data-id="<?=$field->_id?>"></td>
				<td><span><?=$field->permissionName?></span></td>
				<td><span><?=$field->module?></span></td>
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
				รายการที่ <?php if($listRoleType != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
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

<!--------Remove Permission ------->
<div class="modal fade" id="modalRemovePermission" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span class="modal-title" style="font-size: 20px">ยกเลิกสิทธิ์</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
		        	<form action="javascript;" id="formCategory" method="POST">
						<div class="form-group">
					      <label>คุณต้องการยกเลิกสิทธิ์ให้กับผู้ใช้คนนี้ใช่หรือไม่</label>
					    </div>
					    <div class="text-right">
						 	<input type="button" id="submit" class="btn btn-success" value="ตกลง">
						 	<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
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