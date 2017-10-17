<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\web\View;
use common\libs\ActiveFlag;
use common\libs\Permission;
use backend\models\Team;
use backend\components\Modal;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use common\libs\DateTime;
use backend\components\Select;
use common\models\User;
use backend\models\Department;
use yii\widgets\LinkPager;
use backend\components\Success;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$user = Yii::$app->user;
$this->title = 'การจัดการผู้ใช้งานระบบในทีม';
$this->params['breadcrumbs'][] = ['label' => 'ทีม', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$str2 = <<<EOT

$('#submit').click(function(){
	var listUser = getAllCheck();
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('data',JSON.stringify(listUser));
  	    formData.append('id', "$id");
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/team/removemember", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			if(response.isDelete){
							$.redirect('$baseUrl/team', {'$csrfParam':'$csrf'});
	        			}else{
	        				if(response.isClose){
								$.redirect('$baseUrl/team', {'$csrfParam':'$csrf'});
	        				}else{
		        				if(response.success){
				        			$('#modalDeleteMemberInTeam').modal('hide');
				        			$('#success').modal('show');
									setTimeout(function(){
							            location.reload();	
									}, 2000);
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
})

EOT;
$this->registerJs($str2, View::POS_END);

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/team/addMember.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/team/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.redirect.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/team/team-member.js',['depends' => [\yii\web\JqueryAsset::className()]]);

?>
<div class="box box-solid">
	<div class="box-header with-border">
		<table id="w0" class="table table-striped table-bordered detail-view">
			<tbody>
				<tr>
					<th style="width:25%">ชื่อทีม</th>
					<td><?=$team->teamName ?></td>
				</tr>
				<tr>
					<th style="width:25%">คำอธิบาย</th>
					<td><?=$team->description ?></td>
				</tr>
				<tr>
					<th style="width:25%">วันที่สร้าง</th>
					<td><?=DateTime::MongoDateToDate($team->createDate["sec"]) ?></td>
				</tr>
				<tr>
					<th style="width:25%">ผู้ที่สร้าง</th>
					<td><?=User::getUserName((string)$team->createBy) ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<h4>รายการผู้ใช้งานระบบในทีม</h4>

<div class="box box-solid">
	<div class="box-header with-border">
		<div class="row">
			<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/team/member']); ?>
			<input type="hidden" name="teamId" value="<?=$id?>">
			<input type="hidden" name="per-page" value="<?=$length?>">
			<div class="col-md-4">
				<div class="input-group">
			      	<?php echo Html::textInput('name', $name, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อสมาชิก']);?>
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
			<div class="col-md-4 text-right">
				<a href="<?php echo $baseUrl."/team/index" ?>" type="button" class="btn btn-default" aria-label="Left Align" title="ยอนกลับ">
				  <span class="" aria-hidden="true">ย้อนกลับ</span>
				</a>
				<button id="delete" type="button" class="btn btn-danger" aria-label="Left Align" title="ลบสมาชิก" disabled="disabled">
				  <span class="" aria-hidden="true">ลบผู้ใช้งาน</span>
				</button>
					<form action="<?=$baseUrl."/team/add"?>" method="post" style="display: inline;">
						<input id="teamId" type="hidden"  name="teamId" value="<?=(string)$id;?>">
						<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
	           	 		<a  href="javascript:;" onclick="this.parentNode.submit()" type="button" class="btn btn-success" aria-label="Left Align" title="เพิ่มสมาชิก" > 
	                	 <span class="" aria-hidden="true">เพิ่มผู้ใช้งาน</span>
	                	</a>
                </form>
				
			</div>
		</div>
	</div>
</div>
	<div class="panel" style="padding: 10px;">
	<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/team/member']); ?>
		<div class="row">
			<input type="hidden" name="teamId" value="<?=$id?>">
			<input type="hidden" name="name" value="<?=$name?>">
			<input type="hidden" name="department" value="<?=$department?>">
			<input type="hidden" name="page" value="<?=$page?>">
			<input type="hidden" name="sort" value="<?=$sort?>">
			<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
		</div>
	<?php ActiveForm::end(); ?>
	<br>
	<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="member" style="margin-bottom: 1px !important;border: 1px solid #f4f4f4 !important;">
		<thead>
			<tr>
				<th class="text-center"><input type="checkbox" name="checkAll"></th>
				<th class="text-center"><?php echo $dataTablesSort->link('name');?></th>
				<th class="text-center"><?php echo $dataTablesSort->link('department');?></th>	
			</tr>
		</thead>
		<tbody>
			<?php 
			foreach( $listUser as $obj){
			?>
				<tr>		
					<td class="text-center"><?php echo Html::checkbox('id', false, ['class'=>'checkbox-col', 'data-id'=>$obj->_id])?></td>
					<td><span><?php echo $obj->nameTh." ".$obj->sernameTh; ?></span></td>
					<td><span><?php echo $obj->depName ?></span></td>
				</tr> 
			<?php } ?>
			         
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
				รายการที่ <?php if($listUser != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
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


<!--------Delete Member in Role------->
<div class="modal fade" id="modalDeleteMemberInTeam" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span class="modal-title" style="font-size: 20px">ลบสมาชิกในทีม</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
		        	<form action="javascript;" id="formCategory" method="POST">
						<div class="form-group">
					      <label>คุณต้องการลบสมาชิกในทีมนี้ใช่หรือไม่</label>
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
	// Display Deleted Modal
	echo Deleted::widget();
	// Display AccessDeny Modal
	echo AccessDeny::widget();
	// Display Contact Admin
	echo Contact::widget();
	// Display NotCheck
	echo Select::widget();
	echo Success::widget();
?>
