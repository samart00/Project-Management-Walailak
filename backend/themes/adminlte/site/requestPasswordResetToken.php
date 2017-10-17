<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\PasswordResetRequestForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
$baseUrl = \Yii::getAlias('@web');
$this->title = 'ขอเปลี่ยนรหัสผ่าน';
?>
<div class="site-request-password-reset">
	<div class="login-box">
		<div class="login-box-body">
			<p><h2>ลืมรหัสผ่าน</h2></p>
			<hr>
<!-- 	        <p>Please fill out your email. A link to reset password will be sent there.</p> -->
	        <p style="color: gray;">กรุณากรอกอีเมล์ ระบบจะทำการส่งลิงค์ไปยังอีเมล์เพื่อทำการกำหนดรหัสผ่านใหม่</p>

		    <div class="row">
		        <div class="col-md-12">
		            <?php $form = ActiveForm::begin(['id' => 'request-password-reset-form']); ?>
		
		                <?= $form->field($model, 'email')->textInput(['class'=>'form-control', 'autofocus' => true, 'message' => 'Current password cannot be blank.', 'placeholder'=>'อีเมล์'])->label('อีเมล์') ?>
				
		                <div class="form-group text-right">
		                    <?= Html::submitButton('ส่ง', ['class' => 'btn btn-success']) ?>
		                    <a href="<?=$baseUrl?>" type="button" class="btn btn-danger">ยกเลิก</a>
		                </div>
		
		            <?php ActiveForm::end(); ?>
		        </div>
		    </div>
	        
	    </div>
	</div
</div>
