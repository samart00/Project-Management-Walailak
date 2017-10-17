<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Task */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="task-form">

    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'task_name') ?>

    <?= $form->field($model, 'start_date') ?>

    <?= $form->field($model, 'end_date') ?>

    <?= $form->field($model, 'status') ?>

    <?= $form->field($model, 'create_date') ?>

    <?= $form->field($model, 'create_by') ?>

    <?= $form->field($model, 'member') ?>

    <?= $form->field($model, 'description') ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
