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
use common\libs\RoleInProject;
use backend\models\Project;
use backend\components\Success;
use common\libs\DateTime;
use common\libs\PermissionInProject;
use common\models\User;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$user = Yii::$app->user;

$projectname = $value->projectName;
$description = $value->description;
$startDate = $value->startDate;
$endDate = $value->endDate;
$createBy = $value->createBy;

$this->params['breadcrumbs'][] = ['label' => 'โครงการ', 'url' => ['index']];
$this->title = 'ตั้งค่าโครงการ ';
$this->params['breadcrumbs'][] = $this->title;
$str2 = <<<EOT

function changeActiveFlag(projectId,userId){
// debugger;
        var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('projectId', projectId);
        formData.append('userId', userId);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/project/deleteemployeeinproject", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
// 	             debugger;
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalDelete').modal('hide');
							$('#title-delete').html('เนื่องจากสมาชิกนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else if(response.isCancel){
							$('#modalIsCancel').modal('show');
						}else if(response.isClose){
// 							$('#modalDelete').modal('hide');
							$('#modalIsClose').modal('show');
						}else{
		        			if(response.progress){
								$('#modalDelete').modal('hide');
 								$('#title-delete').html('ไม่สามารถลบสมาชิกท่านนี้ได้เนื่องจากยังมีงานที่ไม่เสร็จสิ้น');
 	        					$('#modalIsDelete').modal('show');        		
		        			}else{
		        				$('#modalDelete').modal('hide');
		        				$('#success').modal('show');
		        				setTimeout(function(){ 
					            	location.reload();
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
// 	        debugger;
	        request.send(formData);
};
	        		
function changeEmployeetype(projectId,userId,type){
// debugger;
        var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('projectId', projectId);
        formData.append('userId', userId);
        formData.append('type', type);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/project/changeemployeetype", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
// 	             debugger;
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalDelete').modal('hide');
							$('#title-delete').html('เนื่องจากสมาชิกนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else if(response.isCancel){
							$('#modalIsCancel').modal('show');
						}else if(response.isClose){
							$('#modalIsClose').modal('show');
						}else{
	        				if(response.progress){
								$('#modalDelete').modal('hide');
 								$('#title-delete').html('ไม่สามารถเปลี่ยนประเภทพนักงานให้กับสมาชิกท่านนี้ได้เนื่องจากยังมีงานที่ไม่เสร็จสิ้น');
 	        					$('#modalIsDelete').modal('show');     		
		        			}else{
		      					$('#success').modal('show');
	        					setTimeout(function(){ 
					            	location.reload();
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
// 	        debugger;
	        request.send(formData);
};

$('.permission').click(function(){
	var userName = $(this).data('user-name');
	var userId = $(this).data('user-id');
	var projectId = $(this).data('project-id');
	
	$('#modalUserName').html("กำหนดสิทธิ์ในโครงการให้ \""+userName+"\"");
	$('#modalPermissionInProject').modal('show');
	
	var permission = $arrPermissionOfMember;
	var permissionOfUser = permission[userId];
	var size = permissionOfUser.length;
	
	$('input:checkbox').prop('checked', false);
			
	$.each(permissionOfUser, function(index, id) {
			var checkId = "input[id=\'"+id+"\']";
			$(checkId).prop('checked', true);
	})
			
	if(size == 8){
		$('input[name=checkAll]').prop('checked', true);
	}else{
		$('input[name=checkAll]').prop('checked', false);	
	}
			
	$('#acceptPermission').attr('data-project-Id',projectId);
	$('#acceptPermission').attr('data-user-id',userId);
})


$('#acceptPermission').click(function(){
	var projectId = $(this).data('project-id');
	var userId = $(this).data('user-id');
	
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('projectId', projectId);
        formData.append('userId', userId);
        formData.append('permission', JSON.stringify(getAllCheck()));
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/project/changepermission", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalDelete').modal('hide');
							$('#title-delete').html('เนื่องจากสมาชิกนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else if(response.isCancel){
							$('#modalIsCancel').modal('show');
						}else if(response.isClose){
							$('#modalIsClose').modal('show');
						}else{
	        				if(response.progress){
								$('#modalDelete').modal('hide');
 								$('#title-delete').html('ไม่สามารถเปลี่ยนประเภทพนักงานให้กับสมาชิกท่านนี้ได้เนื่องจากยังมีงานที่ไม่เสร็จสิ้น');
 	        					$('#modalIsDelete').modal('show');     		
		        			}else{
		      					$('#success').modal('show');
	        					setTimeout(function(){ 
					            	location.reload();
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
});


EOT;

$this->registerJs($str2, View::POS_END);
$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/common/bootstrap-toggle.min.css");
$this->registerCssFile("@web/css/project/assignemployeetype.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/bootstrap-toggle.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/role/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/table-datatables-responsive-assignemployeetype.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/project-setting.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

?>
<br>
<div class="box box-solid">
	<div class="box-header with-border">
	
		<table id="w0" class="table table-striped detail-view">
			<tbody>
				<tr>
					<th style="width:25%">ชื่อโครงการ</th>
					<td><?=$projectname ?></td>
				</tr>
				<tr>
					<th style="width:25%">คำอธิบาย</th>
					<td><?=$description ?></td>
				</tr>
				<tr>
					<th style="width:25%">ผู้ที่สร้าง</th>
					<td><?=User::getUserName($createBy) ?></td>
				</tr>
				<tr>
					<th style="width:25%">วันที่เริ่มต้น</th>
					<td><?=DateTime::MongoDateToDate($startDate['sec'])?> </td>
				</tr>
				<tr>
					<th style="width:25%">วันที่สิ้นสุด</th>
					<td><?=DateTime::MongoDateToDate($endDate['sec'])?> </td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<div class="box box-solid">
	<div class="box-header with-border">
		<div class="row">
		<div class="col-md-12" >
        	<ul class="nav nav-tabs">
        		<li class="active">
                	<a href="#tab_0" data-toggle="tab" aria-expanded="true">กำหนดบทบาทผู้ใช้งานในโครงการ</a>
                </li>
                <li>
                  	<a href="#tab_1" data-toggle="tab" > กำหนดสิทธิ์ผู้ใช้งานในโครงการ </a>
                </li>
            </ul>
            <div class="tab-content">
            	<div id="tab_0" class="tab-pane active">
            	 <br>
						<?php $form = ActiveForm::begin(); ?>
								<div class="row">
									<div class="col-md-6">
										<div class="input-group">
									      	<?php echo Html::textInput('name', $name, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อ-นามสกุล']);?>
									      	<span class="input-group-btn">
									        	<button class="btn btn-secondary" type="button" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
									      	</span>
									    </div>
									</div>
								</div>
								</br>
								<div class="panel">
									<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="assignemployeetype">
									<thead>
										<tr>
											<th class="all">ชื่อ-นามสกุล</th>
											<th class="all">ทีม</th>
											<th class="all">บทบาท</th>
											<th class="all"></th>
										</tr>
									</thead>
									<tbody>
 									<?php 
 									foreach ($value->member as $arrmember):
									
									if($arrmember['activeFlag'] == ActiveFlag::ACTIVE){
									?>
									<tr> 
										<td><?php echo User::getUserName($arrmember['userId']);?></td> 
										<?php if (! empty ($arrmember['team'])):	?>
										<td>
											<?php $count = 0;?>
											<?php foreach ($arrmember['team'] as $teamName):?>
											<?php if ($count != 2): ?>
											<?php echo $arrTeam[(string)$teamName['teamId']];?>
											<?php $count++;?>
											<?php else: break;?>
											<?php endif;?>
											<?php endforeach;?>
											
										</td>
										<?php else :?>
										<td></td>
										<?php endif;?>
 										
 										<?php if ($value->createBy == $arrmember['userId']): ?>
 										<td><span style="display: none;"><?=0; ?></span><?php echo Html::dropDownList('type', $arrmember['type'], RoleInProject::$arrRole, [ 'class'=> 'type form-control', 'placeholder'=> 'ตำแหน่ง', 'disabled'=>'disabled']);?></td>
										<td></td>
 										<?php else :?>
 										<td><span style="display: none;"><?=$arrmember['type']; ?></span><?php echo Html::dropDownList('type', $arrmember['type'], RoleInProject::$arrRole, [ 'class'=> 'type form-control', 'placeholder'=> 'ตำแหน่ง', 'data-user-id'=>$arrmember['userId'], 'data-project-id'=>$value->_id]);?></td>
 										<td>
 											<button type="button" class="delete btn btn-danger glyphicon glyphicon-trash btn-sm" title="ลบ" data-user="<?=$arrmember['userId']; ?>" data-team="<?= $name; ?>" data-project="<?= $value->_id ?>"></button>
 										</td>
 										<?php endif;?>
 									</tr> 
									<?php 
									}
									endforeach;?>                 
									</tbody>
									</table>
								</div>  
						<?php ActiveForm::end(); ?>		                                                      
 				</div>
            	<div id="tab_1" class="tab-pane">
            	</br>
            		<div class="row">
						<div class="col-md-6">
							<div class="input-group">
						      	<?php echo Html::textInput('name', $name, ['id'=> 'userName', 'class'=> 'form-control', 'placeholder'=> 'ชื่อ-นามสกุล']);?>
						      	<span class="input-group-btn">
						        	<button class="btn btn-secondary" type="button" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
						      	</span>
						    </div>
						</div>
					</div>
               		<br>
						<?php $form = ActiveForm::begin(); ?>
								<div class="panel">
									<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="permissionInProject">
									<thead>
										<tr>
											<th class="all">ชื่อ-นามสกุล</th>
											<th class="all">ประเภทพนักงาน</th>
											<th class="all"></th>
										</tr>
									</thead>
									<tbody>
 									<?php 
 									foreach ($value->member as $arrmember):
									if($arrmember['activeFlag'] == ActiveFlag::ACTIVE && (string)$value->createBy != (string)$arrmember['userId'] && (int)$arrmember['type'] != RoleInProject::REPORTER){
									?>
									<tr> 
										<td style="height: 34px;"><?php echo User::getUserName($arrmember['userId']);?></td> 
										<td><span style="display: none;"><?=$arrmember['type']; ?></span><?php echo RoleInProject::$arrRole[$arrmember['type']];?></td>
 										<td>
											<button type="button" class="permission btn btn-success glyphicon glyphicon-cog btn-sm" title="การจัดการสิทธิ์ให้ผู้ใช้งานในโครงการ" data-project-id="<?=$value->_id?>" data-user-id="<?=$arrmember['userId']?>" data-user-name="<?=User::getUserName($arrmember['userId']);?>"></a>
 										</td>
 									</tr> 
									<?php 
									}
									endforeach;?>                 
									</tbody>
									</table>
								</div>  
						<?php ActiveForm::end(); ?>                                                             
               	</div>
          	</div>
  		</div>
	</div>
	</div>
</div>
<!--------Delete------->
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
	      	<input type="hidden" id="modalProjectId" name="modalProjectId">
	      	<input type="hidden" id="modalUserId" name="modalUserId">
	        	<section class="content-modal">
	        		<div class="form-group">
					     <label>คุณต้องการลบสมาชิกคนนี้ใช่หรือไม่</label>
					</div>
					<div class="text-right">
						 <button id="submitDelete" type="button" class="btn btn-primary">ตกลง</button>
						 <button class="btn btn-default" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
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
					      <span style="font: bold !important;">โครงการถูกยกเลิก</span>
					    </div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Alert Project Close------->
<div class="modal fade" id="modalIsClose" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
					      <span style="font: bold !important;">โครงการถูกปิด</span>
					    </div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Permission In Project------->
<div class="modal fade" id="modalPermissionInProject" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span id="modalUserName" style="font-size: 20px"></span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
					<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="permissionInProject">
						<tr>
							<th style="text-align: center;"><input type="checkbox" name="checkAll"></th>
							<th style="text-align: center;">ชื่อสิทธิ์ในโครงการ</th>
							<th style="text-align: center;">โมดูล</th>
						</tr>
						<?php foreach (PermissionInProject::$arrPermissionProject as $permission => $value){ ?>
						<tr>
							<td class="checkbox-col" data-permission="<?=$permission?>" style="text-align: center;"><input type="checkbox" id="<?=$permission?>"></td>
							<td><?=$value ?></td>
							<td><?=PermissionInProject::$arrModuleInProject[$permission];?></td>
						</tr>
						<?php } ?>
					</table>
					<div class="text-right">
					 	<button id="acceptPermission" class="btn btn-success" data-dismiss="modal" aria-label="Close">บันทึก</button>
					 	<button id="cancel" class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
					</div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<?php 
// Display Deleted Modal
echo Deleted::widget();
// Display Success
echo Success::widget();
?>

