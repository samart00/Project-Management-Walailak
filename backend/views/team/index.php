<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\web\View;
use common\libs\ActiveFlag;
use common\libs\Permission;
use backend\models\Team;
use backend\components\Modal;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use backend\components\Success;
use yii\base\Widget;
use yii\widgets\LinkPager;
use kartik\typeahead\TypeaheadBasic;
use kartik\typeahead\Typeahead;
use yii\helpers\ArrayHelper;
use kartik\file\FileInput;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'ข้อมูลทีม';
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;
$str2 = <<<EOT

function changeActiveFlag(teamId){
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('teamId', teamId);	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/team/delete", false);
	        request.onreadystatechange = function () {
	        	$('#modalDelete').modal('hide');
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalDelete').modal('hide');
							$('#title-delete').html('เนื่องจากทีมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else if(response.isUsedInProject){
	        				$('#modalDelete').modal('hide');
	        				$('#modalIsUsedInProject').modal('show');
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
	            	$('#modalIsAccessDeny').modal('show');
	            }else{
	            	$('#modalContact').modal('show');
	            }
	        };
	        request.send(formData);	
};

function callGetEditTeam(id){
	var teamData = $.ajax({
		url: '$baseUrl/team/geteditteam',
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
				showModalEditTeam(data);
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

function submit(action){
	var teamId = $('input[name=modalTeamId]').val();
	var teamName = $('input[name=modalTeamName]').val();
	var description = $('textarea[name=modalDescription]').val();
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('teamId', teamId);
        formData.append('teamName', teamName);
        formData.append('description', description);
        if(teamName != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/team/"+action, false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalTeam').modal('hide');
							$('.modal-title').html('<font color="red"><i class="icon fa fa-ban"></i>  ผิดพลาด</font>');
	        				$('#modalIsDelete').modal('show');
	        			}else{
							if(response.success){
	        					$('#modalTeam').hide();
	        					$('#success').modal('show');
								 setTimeout(function(){ 
            			location.reload();
					}, 2000);      		
		        			}
		        			if(response.isDuplicate){
		        				$('#duplicateTeam').show();
		        				$('#accessDeny').hide();
		        			}	        			
	        			}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	        		
	            	$('#accessDeny').show();
	            	$('#duplicateTeam').hide();
	            }
	        };  
	        request.send(formData);
	    }
};
	        		
$('#modalTeamName').change(function(){
	var teamId = $('input[name=modalTeamId]').val();
	var teamName = $('input[name=modalTeamName]').val();
	
	if(teamName != ""){
		$.ajax({
			url: '$baseUrl/team/duplicate', 
			type: 'post',
			data: {
				'teamId' : teamId,
				'teamName' : teamName,
				'$csrfParam' : '$csrf'
			},
			dataType: "json",
			success: function (data) {
				if(data.isDuplicate){
					$('#duplicateTeam').show();
				}else{
					$('#duplicateTeam').hide();	
				}
			}
		});
	}
});
	        		
EOT;
$this->registerJs($str2, View::POS_END);
$this->registerCssFile("@web/css/common/styles.css");
$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/team/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/team/team-index.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
?>

<?php if(Yii::$app->session->hasFlash('alert')):?>
    <?= \yii\bootstrap\Alert::widget([
    'body'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
    'options'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
    ])?>
<?php endif; ?>

<div class="team-index">
 	<?php if($user->can(Permission::CREATE_TEAM)){ ?>
	    <p align="right">
	       <button id="createTeam" class="btn btn-success">
	    		 <i class="fa fa-plus"></i> สร้างทีม
	       </button>
	    </p>
	<?php } ?>
	
   	<div class="site-index">
		<div class="box box-solid">
			<div class="box-header with-border">
				<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/team']); ?>
				<div class="row">
					<div class="col-md-4">
						<div class="input-group">
					      	<?php echo Html::textInput('name', $name, ['id'=> 'project_name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อทีม']);?>
					      	<span class="input-group-btn">
					        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
					      	</span>
					    </div>
					</div>
					<div class="col-md-4">
						<div class="input-group">
					      	<?php  echo TypeaheadBasic::widget([
										'name' => 'nameUser',
										'value' => $nameUser,
									    'data' =>  $arrUser,
									    'options' => ['placeholder' => 'ชื่อผู้ใช้งานระบบ'],
									    'pluginOptions' => ['highlight'=>true],								
									]);?>
					      	<span class="input-group-btn">
					        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
					      	</span>
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
		<div class="col-lg-3 col-sm-6 col-xs-12">
         	<div class="info-box">
	            <span class="info-box-icon bg-green" style="background-color: white !important; width: 85px;">
	            	<img src="<?=Team::getPhotoTeamViewer($field->_id)?>"></img>
	            </span>	
            	<div class="info-box-content">
					<?php if($user->can(Permission::VIEW_TEAM)): ?>
		            	<a href="javascript:;" class="team-detail" data-id="<?=$field->_id;?>" style="color: black;">
		            <?php else: ?><a>
		            <?php endif; ?>
		              	<span><?= $field->teamName; ?></span>
		              	<?php if(! empty ( $field->member )):
		              		$i=0;
							foreach( $field->member as $arrmember) :
							if($arrmember['activeFlag'] == ActiveFlag::ACTIVE):
								$i++;	
							?>					 
							<?php endif; endforeach; ?>  
								<span class="info-box-text" style="font-size: x-small">จำนวนสมาชิก: <?= $i; ?></span></a>
							<?php else: ?>
						    	<span class="info-box-text" style="font-size: x-small">จำนวนสมาชิก: 0</span></a>
							<?php endif;?>
					  		<br>
		              		
		              		<div class="box-tools pull-right" style="font-size: 15px;">
		              		<?php if((string)$field->createBy == (string)$user->identity->id){?>
		              
				            <a  href="javascript:;" class="picture"  data-id="<?=$field->_id;?>" title="เปลี่ยนรูปทีม">
				               	<i class="glyphicon glyphicon-picture"></i>
							</a>
		              
		              		<?php if($user->can(Permission::MEMBER_TEAM)){ ?>
			              	<form action="<?=$baseUrl."/team/member"?>" method="post" style="display: inline;">
									<input id="teamId" type="hidden"  name="teamId" value="<?=(string)$field->_id;?>">
									<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
				           	 		<a  href="javascript:;" onclick="this.parentNode.submit()" title="จัดการสมาชิก" > 
				                	<i class="glyphicon glyphicon-user"></i>
				                	</a>
			                </form>
		                	<?php } ?>
		                 
		                 	<?php if($user->can(Permission::EDIT_TEAM)){ ?>
		                  		<a  href="javascript:;" class="edit"  data-id="<?=$field->_id;?>" title="แก้ไขข้อมูลทีม">
		                			<i class="glyphicon glyphicon-edit"></i>
		                		</a>
		                	<?php } ?>
		                	
		                 	<?php if($user->can(Permission::DELETE_TEAM)){ ?>
		                		<a  href="javascript:;" class="delete"  data-id="<?=$field->_id;?>" title="ลบทีม">
		                			<i class="glyphicon glyphicon-trash"></i>
		                		</a>
		                	<?php } ?>
		                <?php }?>
		              </div>
            	</div>
          	</div>
		</div>
        
	<?php if($count == 4 ){ $count = 0;?>
	</div>
		<?php } ?>
	<?php endforeach; 
	
	else:?>
	<p align="center" style="font-size:160%;">ไม่พบรายการทีม</p>
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
</div>	

<!-- Modal create team -->
<div class="modal fade" id="modalTeam" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
	        		<div id="duplicateTeam" class="alert alert-warning alert-dismissible" style="display: none;">
		                                           ฃื่อทีมซ้ำ
		            </div>
		            <div id="accessDeny" class="alert alert-danger alert-dismissible" style="display: none;">
						ขออภัย คุณไม่มีสิทธิ์สร้างทีม
		            </div>
		            <div id="accessDeny" class="alert alert-danger alert-dismissible" style="display: none;">
						ขออภัย ทีมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว
		            </div>
		        	<form action="javascript:void(0);" id="formTeam" method="POST">
		        		<input type="hidden" id="modalTeamId" name="modalTeamId">
						<div class="form-group">
					      <label>ชื่อทีม <span class="required">*</span></label>
					      <input type="text" id="modalTeamName" name="modalTeamName" class="form-control" placeholder="ชื่อทีม" maxlength="50">
					    </div>
					    <div class="form-group">
					      <label>คำอธิบาย</label>
					      <textarea id="modalDescription" name="modalDescription" class="form-control" rows="3" placeholder="คำอธิบาย" maxlength="1000"></textarea>
					    </div>
					    <div class="text-right">
						 	<input id="save" type="submit" class="btn btn-success" value="บันทึก">
						 	<button id="cancel" class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!------ Modal Delete Team ------>
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
	      	<input type="hidden" id="modalTeamId2" name="modalTeamId2">
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

<!--------Alert IsUsedInProject Team------->
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
					      <label><b>ขออภัย ไม่สามารถลบทีมนี้ได้เนื่องจากถูกใช้ในโครงการ</b></label>
					    </div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Upload Picture------->
<div class="modal fade" id="uploadPicture" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span style="font-size: 20px"><i class="fa fa-edit"></i>แก้ไขรูปทีม</font></span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
				   <div class="box box-success box-solid">
					  <div class="box-body">
					
					    <?php $form = ActiveForm::begin(['action'=>$baseUrl.'/team/upload', 'id'=>'uploadTeamPicture']); ?>
						
					    <input id="uploadTeamId" type="hidden" name="uploadTeamId" value="">
					    
						<?php
					    echo $form->field($model, 'images')->widget(FileInput::classname(), [
						    'options' => ['accept' => 'image/*']
						])->label('รูปทีม <small style="font-weight: normal !important;">รูปภาพประเภท jpg/png ขนาดไม่เกิน 500KB</small>');
					 
					    ActiveForm::end(); ?>
					    
					  </div>
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
	echo Success::widget();
?>