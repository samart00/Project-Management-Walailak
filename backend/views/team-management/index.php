<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\libs\ActiveFlag;
use backend\models\Team;
use backend\components\Modal;
use backend\components\AccessDeny;
use yii\web\View;
use backend\components\Wait;
use backend\components\Deleted;
use backend\components\Contact;
use backend\components\Success;
use common\libs\Permission;
use yii\widgets\LinkPager;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$user = Yii::$app->user;

$this->title = 'การจัดการทีม';
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;
$str2 = <<<EOT

function changeActiveFlag(teamId, activeFlag){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('teamId', teamId);
        formData.append('activeFlag', activeFlag);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/team-management/changeactiveflag", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalActiveFlag').modal('hide');
							$('#title-delete').html('ไม่สามารถเปลี่ยนสถานะได้เนื่องจากทีมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
		        			if(response.success){
								$('#modalActiveFlag').modal('hide');
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
	var teamId = $(this).attr('data-id');
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('teamId', teamId);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/team-management/delete", false);
	        request.onreadystatechange = function () {
	        	$('body').removeClass("loading");
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			$('#modalDelete').modal('hide');
	        			if(response.isDelete){
	        				$('#title-delete').html('ไม่สามารถลบได้เนื่องจากทีมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else if(response.isUsedInProject){
	        				$('#title-delete').html('ไม่สามารถลบได้เนื่องจากทีมนี้ถูกใช้อยู่ในโครงการ');
	        				$('#modalIsDelete').modal('show');
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

function callGetTeam(id){
	var teamData = $.ajax({
		url: '$baseUrl/team/view',
		type: 'post',
		data: {
			'teamId' : id,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
    	complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากทีมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else{
				showModalViewTeam(data);
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

$('.view').click(function(){
	var teamId = $(this).data('id');
	var teamData = $.ajax({
		url: '$baseUrl/team/view',
		type: 'post',
		data: {
			'teamId' : teamId,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
    	complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากทีมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else{
				showModalViewTeam(data);
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
})

EOT;
$this->registerJs($str2, View::POS_END);

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/common/bootstrap-toggle.min.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/bootstrap-toggle.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/team-management/team-management-index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>


<div class="team-management-index">

	<div class="box box-solid">
  		<div class="box-header with-border">
  			<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/team-management']); ?>
  			<div class="row">
  				<div class="col-md-6">
  					<div class="input-group">
				      	<?php echo Html::textInput('name', $name, ['id'=> 'project_name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อทีม']);?>
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
				      	<?php echo Html::dropDownList('activeFlag', $activeFlag, [null => 'ทั้งหมด'] + ActiveFlag::$arrActiveFlag, ['id'=> 'activeFlag', 'class'=> 'form-control','onchange'=>'this.form.submit()'])?>
				    </div>
				</div>
				<input type="hidden" name="per-page" value="<?=$length?>">
  			<?php ActiveForm::end(); ?>
  			</div>
  		</div>
  	</div>

	<div class="panel" style="padding: 10px;">
		<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/team-management/index']); ?>
			<div class="row">
				<input type="hidden" name="name" value="<?=$name?>">
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
					<th class="text-center"><?php echo $dataTablesSort->link('name'); ?></th>
					<th class="text-center"><?php echo $dataTablesSort->link('activeFlag'); ?></th>
					<th class="text-center"></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$count = 1;
					foreach ($value as $field):
				?>
				<tr>
					<td class="text-center"><?php echo $count++; ?></td>

					<td><span><?= $field->teamName; ?></span></td>
					<td class="text-center">
						<?php  if($user->can(Permission::CHANGE_STATUS_TEAM_MANAGEMENT)){?> 
						<input type="checkbox" class="toggle-switch" <?php echo ($field->activeFlag == 1)?"checked":""; ?> disabled="disabled" data-toggle="toggle" data-on="เปิดใช้งาน" data-off="ปิดใช้งาน" data-style="ios" data-size="mini" data-onstyle="success" value="<?= $field->activeFlag; ?>" data-id="<?= $field->_id; ?>">
						<span style="visibility:hidden"><?=$field->activeFlag; ?></span>
						<?php }else{
							echo ActiveFlag::$arrActiveFlag[$field->activeFlag];
						} ?>
					</td>
					<td class="text-center">
						<?php  if($user->can(Permission::VIEW_TEAM_MANAGEMENT)){?> 
							<button type="button" class="view btn btn-info glyphicon glyphicon-eye-open btn-sm" title="รายละเอียด" data-id="<?= $field->_id; ?>"></button>
						<?php } ?>
						
						<?php  if($field->activeFlag == ActiveFlag::INACTIVE && $user->can(Permission::DELETE_TEAM_MANAGEMENT)){?> 
							<button type="button" class="delete btn btn-danger glyphicon glyphicon-trash btn-sm" title="ลบ" data-id="<?= $field->_id; ?>"></button>
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

	<!--------Modal Change ActiveFlag Team------->
	<div class="modal fade" id="modalActiveFlag" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
			        	<form action="javascript;" id="formCategory" method="POST">
							<div class="form-group">
						      <label>คุณต้องการเปลี่ยนสถานะทีมนี้ใช่หรือไม่</label>
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
	
	<!--------Modal Delete Category------->
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
						     <label>คุณต้องการลบทีมนี้ใช่หรือไม่</label>
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

</div>  <!-- end class team-management-index -->

<!------ Modal View Team ------>
	<div class="modal fade" id="modalView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
	        <div>
	        	<span id="viewTeamName" style="font-size: 17px"></span> 
	        </div>
	      </div>
	     <!-- ********** BODY MODAL ********** -->
	      <div class="modal-body">
	        <section class="content-modal">
		          <div class="nav-tabs-custom">
		            <ul class="nav nav-tabs">
		              <li class="active"><a href="#fa-icons" data-toggle="tab">รายละเอียด</a></li>
		              <li><a href="#member" data-toggle="tab">สมาชิก</a></li>
		            </ul>
		            <!-- ********** TAB DETAIL ********** -->
		            <div class="tab-content">
		              <!-- Font Awesome Icons -->
		              <div class="tab-pane active" id="fa-icons"><br>
                        <div class="row">
                            <label class="control-label col-md-3 text-right">คำอธิบาย : </label>
                            <div class="col-md-9">
								<span id="viewDescription"></span>							
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
		              </div>
		              <!-- ********** TAB MEMBER ********** -->
		              <div class="tab-pane" id="member"><br>
						  <textarea rows="10" style="width: 100%" id="viewAssign" readonly></textarea>
		              </div>
		            </div>
		          </div>
		    </section>
	      </div>
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
