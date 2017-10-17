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
use backend\components\Success;
use yii\base\Widget;
use yii\widgets\LinkPager;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'การจัดการข้อมูลพนักงาน';
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;
$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;

$str2 = <<<EOT
var table = "";
$(document).ready(function() {
				
	$("#employee tbody").delegate("tr", "click", function() {
		var checkBox = $("td:first", this).children();
		$(this).toggleClass('odd info');
		var className = $(this).attr('class');
		if(className == 'odd info'){
			checkBox.prop('checked', true);
		}else{
			checkBox.prop('checked', false);
		}
	});
				
	$('input[name=checkAll]').change(function(){
	
		if($(this).prop('checked')){
			$.each($('.checkbox-col'), function(index, obj){
				var id = "table[id=employee] tr:eq("+(index+1)+")"
				$(id).addClass('odd info');
				$(obj).prop('checked', true);
			});
		}else{
			$.each($('.checkbox-col'), function(index, obj){
				var id = "table[id=employee] tr:eq("+(index+1)+")"
				$(id).removeClass('odd info');
				$(obj).prop('checked', false);
			});
		}
	});
	
	function getAllCheck(){
		var memberData = [];
		var row = "";
		$("table[id=employee] tr").each(function(index) {
			if (index !== 0) {
				//          debugger;
				row = $(this);
				var firstRow = row.find("td:first");
				var isCheck = firstRow.children().is(':checked');
				var id = firstRow.children().data('id');
				if(isCheck){
					var temp = {
						employeeId: id
					};
					memberData.push(temp);
				}
			}
		});
		return memberData;
	}
				
	$('#test').click(function(){
		var listId = getAllCheck();
		var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
  	    formData.append('employee', JSON.stringify(listId));
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/merge/save", false);
	        request.onreadystatechange = function () {
	        
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
		        			if(response.success){
								$('#success').modal('show');
								setTimeout(function(){ 
			            			location.reload();
								}, 2000);   
		        			}
	                }
	            }
				else if(request.status == 403){
	            	$('#modalIsAccessDeny').modal('show');
	            }else{
	            	$('#modalContact').modal('show');
	            }
	        };
		request.send(formData);
	})
} );


EOT;
$this->registerJs($str2, View::POS_END);

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/common/dataTables.checkboxes.css");


$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/js/common/dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

?>
<p align="right">
	<button id="test" class="btn btn-success text-right"><i class="fa fa-plus"></i> เพิ่มพนักงานเข้าสู่ระบบ</button>
</p>
<div class="panel" style="padding: 10px;">
	<div class="merge-index">
		<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/merge/index']); ?>
			<div class="row">
				<input type="hidden" name="page" value="<?=$page?>">
				<input type="hidden" name="sort" value="<?=$sort?>">
				<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
			</div>
		<?php ActiveForm::end(); ?>
		<br>
		<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="employee" style="margin-bottom: 1px !important">
			 <thead>
                    <tr>
                    	<th class="text-center"><input type="checkbox" name="checkAll"></th>
                        <th class="text-center"><?php echo $dataTablesSort->link('name'); ?></th>
                        <th class="text-center"><?php echo $dataTablesSort->link('department'); ?></th>
                        <th class="text-center"><?php echo $dataTablesSort->link('company'); ?></th>
                    </tr>
                </thead>
                <?php foreach ($model as $obj){ ?>
                <tr>
                	<td class="text-center"><input type="checkbox" class="checkbox-col" data-id="<?=$obj->_id?>"></td>
                	<td><?=$obj->nameTh." ".$obj->sernameTh;?></td>
                	<td><?=$obj->depName?></td>
                	<td><?=$obj->companyName?></td>
                </tr>
                <?php } ?>
		</table>
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<hr style="border-top: 1px solid #000; !important; margin-top: 1px !important;">
				<?php $lastRecordNo = (($pagination->page+1) * $pagination->limit); 
				if ($lastRecordNo > $pagination->totalCount) $lastRecordNo = $pagination->totalCount?>
				<div class="col-md-4 dataTables_info" role="status" aria-live="polite" style="padding-left: 10px;">
					รายการที่ <?php if($model != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
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
	</div>
</div>

<?php 
	// Display AccessDeny Modal
	echo AccessDeny::widget();
	// Display Contact Admin
	echo Contact::widget();
	// Display Success
	echo Success::widget();
?>