<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\web\View;
use common\libs\ActiveFlag;
use common\libs\Permission;
use backend\components\Modal;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use yii\helpers\ArrayHelper;
use yii\widgets\LinkPager;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'กำหนดบทบาท';
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/js/common/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

?>

<?php if(Yii::$app->session->hasFlash('alert')):?>
    <?= \yii\bootstrap\Alert::widget([
    'body'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
    'options'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
    ])?>
<?php endif; ?>

<div class="site-index">
  		<div class="box box-solid">
			<div class="box-header with-border">
				<?php $form = ActiveForm::begin(['action' => $baseUrl.'/assign']); ?>
				<div class="row">
					<div class="col-md-6">
						<div class="input-group">
					      <?php echo Html::textInput('name', $name, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อบทบาท']);?>
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
					      <?php echo Html::dropDownList('activeFlag', $activeFlag,[null=>'ทั้งหมด']+ActiveFlag::$arrActiveFlag , ['id'=> 'activeFlag', 'class'=> 'form-control','onchange'=>'this.form.submit()'])?>
					    </div>
					</div>
				</div>
				<input type="hidden" name="per-page" value="<?=$length?>">
				<?php ActiveForm::end(); ?>
			</div>
		</div>
		<div class="panel">
			<div class="box-header with-border">
				<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/assign/index']); ?>
					<div class="row">
						<input type="hidden" name="name" value="<?=$name?>">
						<input type="hidden" name="activeFlag" value="<?=$activeFlag?>">
						<input type="hidden" name="page" value="<?=$page?>">
						<input type="hidden" name="sort" value="<?=$sort?>">
						<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
					</div>
				<?php ActiveForm::end(); ?>
				<br>
				<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="sample_1" style="margin-bottom: 1px !important">
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
					foreach ($listRole as $field): 
					?>
					<tr>
						<td class="text-center"><?php echo $count++; ?></td>
						<td><span><?= $field->name; ?></span></td>
						<td class="text-center">
							<?php echo ActiveFlag::$arrActiveFlag[$field->activeFlag]; ?> 
						</td>
						<td class="text-center">
							<?php if($field->activeFlag == ActiveFlag::ACTIVE && $user->can(Permission::MANAGEMENT_ASSIGN)){ ?>
								<?php if($field->canBeDeleted != 1){ ?>
									<form action="<?=$baseUrl."/assign/management"?>" method="post">
										<input type="hidden" name="id" value="<?=$field->name ?>">
										<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
										<button type="submit" class="add btn btn-success glyphicon glyphicon-cog btn-sm" title="กำหนดบทบาทให้ผู้ใช้งานระบบ"></a>
									</form>
								<?php } ?>
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
						รายการที่ <?php if($listRole != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
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
	</div>
</div>