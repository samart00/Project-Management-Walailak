<?php
//หากมี fileInput form จะสร้าง enctype="multipart/form-data ให้อัตโนมัตินะ (v2.0.8)
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]) ?>

<?=$form->field($model, 'title')?>

<?=$form->field($model, 'content')->textarea()?>

<?=$form->field($model, 'image')->fileInput()?>

<?=$form->field($model, 'files[]')->fileInput(['multiple' => true])//ต้องมี [] ด้วยนะเพราะหลายไฟล์เป็น array และมี multiple ด้วย?>

<?=Html::submitButton('บันทึกข้อมูล', ['class' => 'btn btn-success'])?>

<?php ActiveForm::end()?>