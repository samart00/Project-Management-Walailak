<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\web\View;
use backend\components\Wait;
use yii\base\Widget;
use backend\components\Contact;
use backend\components\AccessDeny;
use backend\components\Success;
use common\libs\Permission;
use common\models\User;
use yii\widgets\LinkPager;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$user = Yii::$app->user;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'กำหนดจำนวนโครงการที่สร้างได้';
$this->params['breadcrumbs'][] = $this->title;

$str = <<<EOT

function submitAllAmount(amountOfProject){
	amountOfProject = parseInt(amountOfProject);
	var valueMaxCount = $maxCountProject;
	if(amountOfProject >= valueMaxCount){
			var allAmountData = $.ajax({
				url: '$baseUrl/policy/setallamount',
				type: 'post',
				data: {
					'allAmountOfProject' : amountOfProject,
					'$csrfParam' : '$csrf'
				},
				beforeSend: function() { $('#wait').modal('show'); },
		        complete: function() { $('#wait').modal('hide'); },
				dataType: "json",
				success: function (data) {
					console.log(data);
					$('#modalSetAll').modal('hide');
					if(data.isSuccess){
						$('.set-number').val(data.amountOfProject);
	       	 			$('.number-child').val(data.amountOfProject);
						$('#success').modal('show');
								setTimeout(function(){ 
			            			location.reload();
								}, 2000); 	
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
		}else{
			$('#modalSetAll').modal('hide');
			$('#isLessThan').modal('show');
		}
}
	
	
function submitOneAmount(userId, amountOfProject, id){
	var amountData = $.ajax({
		url: '$baseUrl/policy/setamount',
		type: 'post',
		data: {
			'amountOfProject' : amountOfProject,
			'userId' : userId,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
        complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			console.log(data);
			if(data.isSuccess){
					 $(id).val(data.amountOfProject);
					$('#success').modal('show');
					setTimeout(function(){ 
			            location.reload();
					}, 2000); 
				}else{
					
				$('#isLessThan').modal('show');
			 
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

function changeLimit(userId, isLimit, inputId){
   	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('userId', userId);
        formData.append('isLimit', isLimit);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/policy/changelimit", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			$('#modalChangeLimit').modal('hide');
		        			if(response.success){
								$('#success').modal('show');
									setTimeout(function(){ 
		            					location.reload();
									}, 2000); 	        		
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
	        		


EOT;

$this->registerJs($str, View::POS_END);

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/common/bootstrap-toggle.min.css");
$this->registerCssFile("@web/css/policy/pageAmountOfProject.css");
$this->registerCssFile("@web/css/common/bootstrap-toggle.min.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/bootstrap-toggle.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/policy/policy-index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

?>
<div class="setamount">
  
  <div class="box box-solid">
		<div class="box-header with-border">
			<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/policy']); ?>
			<input type="hidden" name="per-page" value="<?=$length?>">
			<div class="row">
				<div class="col-md-6">
					<div class="input-group">
				      	<?php echo Html::textInput('name', $name, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อผู้ใช้งานระบบ']);?>
				      	<span class="input-group-btn">
				        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
				      	</span>
				    </div>
				</div>
				
				<div class="col-md-6">
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
										(string)$department => ['selected' => true]
                              		],
									'prompt'=>'ทั้งหมด',
									'onchange'=>'this.form.submit()'
								]
							)
						?>
				    </div>
				</div>
				
			</div>
			<?php ActiveForm::end(); ?>
		</div>
	</div>
	<div class="panel" style="padding: 10px;">
	<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/policy/index']); ?>
		<div class="row">
			<input type="hidden" name="name" value="<?=$name?>">
			<input type="hidden" name="departmentId" value="<?=$department?>">
			<input type="hidden" name="page" value="<?=$page?>">
			<input type="hidden" name="sort" value="<?=$sort?>">
			<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
		</div>
	<?php ActiveForm::end(); ?>
	<br>
	<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="table_policy"  style="margin-bottom: 1px !important;border: 1px solid #f4f4f4 !important;">
		<thead>
			<tr>
				<th class="text-center"><?php echo $dataTablesSort->link('name');?></th>
				<th class="text-center"><?php echo $dataTablesSort->link('department');?></th>
				<th class="text-center"><?php echo $dataTablesSort->link('limit');?></th>
				<th class="text-center" width="20%">
				<div class="input-group">
						<?php if($user->can(Permission::SET_ALL_AMOUNT)){ ?>
				          <span class="input-group-btn">
				              <button type="button" class="btn btn-danger btn-minus"  data-type="minus">
				                <span class="glyphicon glyphicon-minus"></span>
				              </button>
				          </span>
				          
				          <input type="number" id="amountOfProject" class="form-control set-number text-center" 
				          		 value="<?= $defaultPolicy->defaultPolicy; ?>" name="all-number"
				          >
				          
				          <span class="input-group-btn">
				              <button type="button" class="btn btn-success btn-plus" data-type="plus">
				                  <span class="glyphicon glyphicon-plus"></span>
				              </button>
				          </span>
				          <?php }else{ ?>
				      </div>
				      <?php echo "จำนวนโครงการที่สร้างได้";
						} ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach ($model as $field): 
			?>
			<tr>
				<td height="34px"><span><?= $field->nameTh." ".$field->sernameTh; ?></span></td>
				<td><span><?= $field->depName ?></span></td>
				<td class="text-center">
					<input type="checkbox" class="toggle-switch" <?php echo ($field->limit == User::LIMIT)?"checked":""; ?> disabled="disabled" data-toggle="toggle" data-on="จำกัด" data-off="ไม่จำกัด" data-style="ios" data-size="mini" data-onstyle="warning" value="<?=$field->limit;?>" data-id="<?=$field->_id; ?>" data-name="set-number-<?= $field->username; ?>">
					<span style="visibility:hidden"><?=$field->limit; ?></span>
				</td>
				<td class="text-center">
				<?php if($field->limit == User::LIMIT){ ?>
				  <span class="text-center">
					<div class="input-group">
						<?php if($user->can(Permission::SET_AMOUNT)){ ?>
				          <span class="input-group-btn">
				              <button type="button" class="btn btn-danger btn-minusChild" data-type="minus" data-name="set-number-<?= $field->username; ?>" data-id="<?= $field->_id; ?>">
				                <span class="glyphicon glyphicon-minus"></span>
				              </button>
				          </span>
				          <input type="number" id="set-number-<?= $field->username; ?>" class="form-control number-child text-center" value="<?= $field->amountofproject ?>" name="oneNumber" data-id="<?= $field->_id; ?>">
				          <span class="input-group-btn">
				              <button type="button" class="btn btn-success btn-plusChild" data-type="plus" data-name="set-number-<?= $field->username; ?>" data-id="<?= $field->_id; ?>">
				                  <span class="glyphicon glyphicon-plus"></span>
				              </button>
				          </span>
				        <?php }else{ ?>
				      </div>
				      <?php echo $field->amountofproject;
						}  ?>
				   </span>
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
</div>
<!--------isLessThan------->
<div class="modal fade" id="isLessThan" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span class="modal-title" style="font-size: 20px">ผิดพลาด</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
					<div class="form-group">
					     <label>ไม่สามารถกำหนดจำนวนโครงการได้ต่ำกว่าจำนวนโครงการที่ผู้ใช้งานระบบสร้างไว้</label>
					</div>
					<div class="text-right">
						 <button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ตกลง</button>
					</div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Change Limit Amount------->
<div class="modal fade" id="modalChangeLimit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span class="modal-title" style="font-size: 20px">กำหนดจำนวนโครงการ</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
		        	<form action="javascript;" id="formCategory" method="POST">
						<div class="form-group">
					      <label>คุณต้องการเปลี่ยนแปลงการกำหนดจำนวนโครงการที่สร้างได้ใช่หรือไม่</label>
					    </div>
					    <div class="text-right">
						 	<input type="button" id="submitLimit" class="btn btn-success" value="ตกลง">
						 	<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>
<!--------Set All Amount------->
<div class="modal fade" id="modalSetAll" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span class="modal-title" style="font-size: 20px">เปลี่ยนแปลงจำนวนโครงการทั้งหมด</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
		        	<form action="javascript;" id="formCategory" method="POST">
						<div class="form-group">
					      <label>คุณต้องการเปลี่ยนแปลงจำนวนโครงการที่สร้างได้ทั้งหมดใช่หรือไม่</label>
					    </div>
					    <div class="text-right">
						 	<input type="button" id="submitsetall" class="btn btn-success" value="ตกลง">
						 	<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>
<?php 
	echo Wait::widget();
	// Display AccessDeny Modal
	echo AccessDeny::widget();
	// Display Contact Admin
	echo Contact::widget();
	// Display Success
	echo Success::widget();
?>
