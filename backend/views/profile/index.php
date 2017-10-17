<?php

/* @var $this yii\web\View */
use yii\web\View;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Contact;
use backend\components\Success;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\file\FileInput;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$this->title = 'ข้อมูลผู้ใช้';
$user = Yii::$app->user->identity;
$username = $user->username;

$this->registerJsFile('@web/js/profile/jquery.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/profile/jquery.validate.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/profile/form-validate.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/profile/profile-index.js',['depends' => [\yii\web\JqueryAsset::className()]]);

$str = <<<EOT

function upload(){
	var avatar = $('input[name=avatar]').val();
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('avatar', avatar);
        
        if(avatar != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/profile/upload", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
							if(response.success){
								$('#success').modal('show');
 								location.reload();	        		
		        			}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	            }else{
	            	$('#modalContact').modal('show');
	            }
	        };
		request.send(formData);
	}
};

function submitpassword(){
	var currentPassword = $('input[name=currentPassword]').val();
	var newPassword = $('input[name=newPassword]').val();
	var confirmPassword = $('input[name=confirmPassword]').val();
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('currentPassword', currentPassword);
        formData.append('newPassword', newPassword);
        formData.append('confirmPassword', confirmPassword);
        if(currentPassword.length >= 8 && newPassword.length >= 8 && confirmPassword.length >= 8){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/profile/editpassword", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
		        		if(response.incorrect){
	        				$('#modalPasswordIncorrect').modal('show');
		        		}else{
							if(response.success){
								$('#success').modal('show');
 								location.reload();	        		
		        			}
	        		  }
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	            }else{
	            	$('#modalContact').modal('show');
	            }
	        };
	        request.send(formData);
	    }
};
	        		
function submitusername(){
	var username = $('#username').val();
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('username', username);
        if(username != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/profile/editusername", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			
							if(response.success){
								$('#success').modal('show');
 								location.reload();	        		
		        			}
	        				if(response.isDuplicate){
		        				$('#duplicateUsername').show();
		        				$('#accessDeny').hide();
		        			}else{
	        					$('#duplicateUsername').hide();	
	        				}
	        		  
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	        		$('#duplicateUsername').hide();
	            }else{
	            	$('#modalContact').modal('show');
	            }
	        };
	        request.send(formData);
	    }
};

EOT;
$this->registerJs($str, View::POS_END);
?>
<div class="profile-index">

    <div class="row">
   	 	<div class="col-md-3">
   	 		<div class="box box-solid text-center" style="padding-bottom: 10px;padding-top: 10px;">
   	 			<?= Html::img($model->getPhotoViewer(),['style'=>'width:60%;','class'=>'img-rounded']); ?>
   	 		<div><br> </div>	
   	 		<div class="text-center">
				 <span><button id="btnEditAvatar"  class="btn btn-info fa fa-edit">แก้ไขรูปโปรไฟล์</button></span>
			</div>
			</div>	
   	 		
   	 	</div>
   	 	<div class="col-md-9">
   	 		<div class="box box-solid">
   	 			<div class="row">
   	 			<br>
	   	 			<div class="col-md-4">
	   	 				<div class="col-md-12"><b>ชื่อผู้ใช้</b></div>
	   	 			</div>
	   	 			<div class="col-md-8">
	   	 				<div class="col-md-10"><?= $username ?></div>
	   	 				<div class="col-md-2"><a id="editUsername" class="fa fa-edit" href="javascript:;">แก้ไข</a></div>
	   	 			</div>
	   	 		</div><br>
	   	 		<div class="row">
	   	 			<div class="col-md-4">
	   	 				<div class="col-md-12"></div>
	   	 			</div>
	   	 			<div class="changeUsername col-md-8">
	   	 				<div class="col-md-12">
	   	 				    <form id="usernameForm" class="show_label"  method="POST" name="usernameForm">
								<table style="border:0px;" border="0">
									<tr>
										<td>
											ชื่อผู้ใช้ &nbsp;
										</td>
										<td>
											<input id="username" name="username" class="form-control" maxlength="20"/>
											<span id="error-name" class="error-date"></span>
										</td>
										<div id="duplicateUsername" class="alert alert-warning alert-dismissible" style="display: none;">
                                         	 ชื่อผู้ใช้งานซ้ำ
            							</div>
									</tr>
								</table>
								<hr>
								<div>
									<div>
										<label class="submit ButtonConfirm" id="">
											<input value="บันทึกการเปลี่ยนแปลง" type="button" id="submitChangeUsername"  class="btn btn-success">
										</label>
										<label class="cancel uiButton" for="">
											<input value="ยกเลิก" type="button" id="cancelEdit" class="btn btn-danger">
										</label>
									</div>
								</div>
							</form><br>
	   	 				</div>
	   	 			</div>
	   	 		</div>
	   	 		
   	 			<div class="row">
	   	 			<div class="col-md-4">
	   	 				<div class="col-md-12"><b>ชื่อ-นามสกุล</b></div>
	   	 			</div>
	   	 			<div class="col-md-8">
	   	 				<div class="col-md-12"><?= $model->nameTh." ".$model->sernameTh ?></div>
	   	 			</div>
	   	 		</div><br>
	   	 		
	   	 		<div class="row">
	   	 			<div class="col-md-4">
	   	 				<div class="col-md-12"><b>อีเมล์</b></div>
	   	 			</div>
	   	 			<div class="col-md-8">
	   	 				<div class="col-md-12"><?=$model->email ?></div>
	   	 			</div>
	   	 		</div><br>
	   	 		<div class="row">
	   	 			<div class="col-md-4">
	   	 				<div class="col-md-12"><strong>รหัสผ่าน</strong></div>
	   	 			</div>
	   	 			<div class="col-md-8">
	   	 				<div class="col-md-10"></div>
	   	 				<div class="col-md-2"><a id="btnEdit"  class="fa fa-edit" href="javascript:;">แก้ไข</a></div>
	   	 			</div>
	   	 		</div><br>
	   	 		<div class="row">
	   	 			<div class="col-md-4">
	   	 				<div class="col-md-12"></div>
	   	 			</div>
	   	 			<div class="changePassword col-md-8">
	   	 				<div class="col-md-12">
	   	 				    <form id="passwordForm" class="show_label"  method="POST" name="passwordForm">
								<input type="hidden" name="<?=$csrfParam?>" value="<?=$csrf;?>" />			
								<table style="border:0px;" border="0">
									<tr>
										<td>
											รหัสผ่านเดิม &nbsp;
										</td>
										<td>
											<input type="password" class="inputtext form-control" name="currentPassword" id="currentPassword"/>
											<span id="error-name" class="error-date"></span>
										</td>
									</tr>
									<tr>
									<th class="label noLabel"></th>
										<td class="data">
											<div>
											<span>&nbsp;</span>
											</div>
										</td>
									</tr>
									<tr>
										<td>
											รหัสผ่านใหม่&nbsp;
										</td>
										<td>
											<input type="password" class="inputtext form-control" name="newPassword" id="newPassword"/>
											<span id="error-name" class="error-date"></span>
										</td>
									</tr>
									<tr>
									<th class="label noLabel"></th>
										<td class="data">
											<div>
											<span>&nbsp;</span>
											</div>
										</td>
									</tr>
									<tr>
										<td>
											พิมพ์อีกครั้ง&nbsp;
										</td>
										<td>
											<input type="password" class="inputtext form-control" name="confirmPassword" id="confirmPassword">
											<span id="error-name" class="error-date"></span>
										</td>
									</tr>
								</table>
							<hr>
							<div>
								<div>
									<label class="submit ButtonConfirm" id="">
										<input value="บันทึกการเปลี่ยนแปลง" type="button" id="submitChangePassword"  class="btn btn-success">
									</label>
									<label class="cancel uiButton" for="">
										<input value="ยกเลิก" type="button" id="cancelSave" class="btn btn-danger">
									</label>
								</div>
							</div>
						</form><br>
	   	 				</div>
	   	 			</div>
	   	 		</div>
	   	 		
	   	 		<div class="row">
	   	 			<div class="col-md-4">
	   	 				<div class="col-md-12"><b>บริษัท</b></div>
	   	 			</div>
	   	 			<div class="col-md-8">
	   	 				<div class="col-md-12"><?=$model->companyName ?></div>
	   	 			</div>
	   	 		</div><br>
	   	 		<div class="row">
	   	 			<div class="col-md-4">
	   	 				<div class="col-md-12"><b>แผนก</b></div>
	   	 			</div>
	   	 			<div class="col-md-8">
	   	 				<div class="col-md-12"><?=$model->depName ?></div>
	   	 			</div>
	   	 		</div><br>
	   	 	</div><br>

   	 		</div>
   	 	</div>
    </div>
</div>

<!--------Alert Password Incorrect------->
<div class="modal fade" id="modalPasswordIncorrect" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
					      <label><b>รหัสผ่านไม่ถูกต้อง</b></label>
					    </div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<!--------Upload Avatar------->
<div class="modal fade" id="uploadAvatar" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span style="font-size: 20px"><i class="fa fa-edit"></i>แก้ไขรูปโปรไฟล์</font></span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** -->
	      	<div class="modal-body">
	        	<section class="content-modal">
				   <div class="box box-success box-solid">
					  <div class="box-body">
					
					    <?php $form = ActiveForm::begin(['action'=>$baseUrl.'/profile/upload','options' => ['enctype' => 'multipart/form-data']]); 
					
					    echo $form->field($model, 'avatar')->widget(FileInput::classname(), [
						    'options' => ['accept' => 'image/*']
						])->label('รูปโปรไฟล์ <small style="font-weight: normal !important;">รูปภาพประเภท jpg/png ขนาดไม่เกิน 500KB</small>');
					 
					    ActiveForm::end(); ?>
					    
					  </div>
					</div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>
<?php 
	// Display AccessDeny Modal
	echo AccessDeny::widget();
	// Display Waiting Modal
	echo Wait::widget();
	// Display Contact Admin
	echo Contact::widget();
	// Display Success
	echo Success::widget();
?>