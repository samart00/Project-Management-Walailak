<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Reset password';
?>
<div class="site-reset-password">
	
	<div class="login-box">
		<div class="login-box-body">
			<p><h2>เปลี่ยนรหัสผ่าน</h2></p>
			<hr>
<!-- 	        <p>Please fill out your email. A link to reset password will be sent there.</p> -->
	        <p style="color: gray;">กรุณากรอกรหัสผ่านใหม่</p>

		    <div class="row">
		        <div class="col-md-12">
		            <?php $form = ActiveForm::begin(['id' => 'reset-password-form']); ?>

		                <?= $form->field($model, 'password')->passwordInput(['autofocus' => true, 'placeholder'=>'รหัสผ่าน'])->label('รหัสผ่าน') ?>
		
		                <div class="form-group text-right">
		                    <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
		                    <a href="<?=$baseUrl?>" type="button" class="btn btn-danger">ยกเลิก</a>
		                </div>
		
		            <?php ActiveForm::end(); ?>
		        </div>
		    </div>
	        
	    </div>
	</div
</div>
