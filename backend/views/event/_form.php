<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Event */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="event-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'Event_name') ?>

    <?= $form->field($model, 'Discription') ?>

    <?= $form->field($model, 'Date') ?>

    <?= $form->field($model, 'Status') ?>

    <?= $form->field($model, 'Color') ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
