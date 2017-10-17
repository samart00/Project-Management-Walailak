<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\web\View;
use backend\components\AccessDeny;
use backend\components\Contact;
use backend\components\Select;
use backend\components\Deleted;
use backend\components\Success;
use common\models\User;
use yii\widgets\LinkPager;

$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;

$this->title = 'เพิ่มผู้รับผิดชอบงานส่วนตัว';
$this->params ['breadcrumbs'] [] = [
		'label' => 'งาน',
		'url' => [
				"privatetask"
		]
];
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/task/assign.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/task/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.redirect.js',['depends' => [\yii\web\JqueryAsset::className()]]);

$str2 = <<<EOT
$('.view').click(function(){
	var id = $(this).data('id');
	var page = 'view';
	var action = 'getusertaskprivate';
	callTaskMember(id, page, action);
});
function callTaskMember(id, page, action){
	var categoryData = $.ajax({
		url: '$baseUrl/task/'+action,
		type: 'post',
		data: {
			'memberId' : id,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
        complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			console.log(data);
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากบุคคลนี้ถูกลบจากโครงการแล้ว');
				$('#modalIsDelete').modal('show');
			}else{	
				if(page == 'view'){
					showModalViewUser(data);
				}
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
function showModalViewUser(data){
	var userData = data.taskData;			
	$('#viewuserName').html(userData.userName);
	$('#viewAllTask').html(userData.alltask);
	$('#viewAlltaskProject').html(userData.alltaskproject);
	$('#viewAlltaskprivate').html(userData.alltaskprivate);		
	$('.modal-title').html('รายละเอียดผู้ใช้');
	$('#modalView').modal('show');
}
function getAllCheck(){
	var memberData = [];
	var row = "";
	$("table[id=member] tr").each(function(index) {
		if (index !== 0) {
			row = $(this);
			var firstRow = row.find("td:first");
			var isCheck = firstRow.children().is(':checked');
			var id = firstRow.children().data('id');
			if(isCheck){
				var temp = {
					userId: id
				};
				memberData.push(temp);
			}
		}
	});
	return memberData;
}
$("#member tbody").delegate("tr", "click", function(evn) {
	var str = (evn.target).toString().toLowerCase();
	var isButton = str.includes("button");
	if(!isButton){
		var checkBox = $("td:first", this).children();
		$(this).toggleClass('odd info');
		var className = $(this).attr('class');
		if(className == 'odd info'){
			checkBox.prop('checked', true);
		}else{
			checkBox.prop('checked', false);
		}
	}
});	
$('input[name=checkAll]').change(function(){
	if($(this).prop('checked')){
		$.each($('.checkbox-col'), function(index, obj){
			var id = "table[id=member] tr:eq("+(index+1)+")"
			$(id).addClass('odd info');
			$(obj).prop('checked', true);
		});
	}else{
		$.each($('.checkbox-col'), function(index, obj){
			var id = "table[id=member] tr:eq("+(index+1)+")"
			$(id).removeClass('odd info');
			$(obj).prop('checked', false);
		});
	}
});
$('#addMember').click(function(){
	var listUser = getAllCheck();
	if(listUser != ""){
		var formData = new FormData();
	        formData.append('$csrfParam', '$csrf');
	        formData.append('data',JSON.stringify(listUser));
	  	    formData.append('taskId', "$taskId");
		        var request = new XMLHttpRequest();
		        request.open("POST", "$baseUrl/task/addmemberprivatetask", false);
		        request.onreadystatechange = function () {
		            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
		                var response = request.responseText;
		                if(typeof(response) == "string"){
		                    response = JSON.parse(request.responseText);
		        			console.log(response);
		        			if(response.isDelete){
								$.redirect('$baseUrl/task/assignprivatetask', {'taskId': '$taskId','$csrfParam':'$csrf'});
		        			}else{
			        			if(response.success){
			        				$('#success').modal('show');
									setTimeout(function(){
				            			$.redirect('$baseUrl/task/assignprivatetask', {'taskId': '$taskId','$csrfParam':'$csrf'});
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
	}else{
		$('#modalNotCheck').modal('show');
	}
})

EOT;
$this->registerJs($str2, View::POS_END);
?>
<div class="box box-solid">
	<div class="box-header with-border">
		<div class="row">
			<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/task/addprivatetask']); ?>
			<input type="hidden" name="taskId" value="<?=$taskId?>">
			<input type="hidden" name="per-page" value="<?=$length?>">
			<div class="col-md-4">
				<div class="input-group">
			      	<?php echo Html::textInput('name', $name, ['id'=> 'name', 'class'=> 'form-control', 'placeholder'=> 'ชื่อผู้ใช้งานระบบ']);?>
			      	<span class="input-group-btn">
			        	<button class="btn btn-secondary" type="button" onclick="this.form.submit()" style="background-color: white;border-bottom-color: #d2d6de;border-top-color: #d2d6de;border-right-color: #d2d6de;"><i class="fa fa-search"></i></button>
			      	</span>
			    </div>
			</div>
			<div class="col-md-4">
				<div class="input-group">
			      	<span class="input-group-btn">
			        	<button class="btn btn-secondary" type="button" style="background: white;border-bottom-color: #d2d6de;border-left-color: #d2d6de;border-top-color: #d2d6de;pointer-events: none;">แผนก</button>
			      	</span>
			      	<?php 
						echo  Html::dropDownList( 'department',
							'selected option',  
							$listDepartment,
							[
								'class' => 'form-control', 'id' => 'department',
								'options' => [
									$department => ['selected' => true]
                              	],
								'prompt'=>'ทั้งหมด',
								'onchange'=>'this.form.submit()'
							]
						)
					?>
			    </div>
			</div>
			<?php ActiveForm::end(); ?>
			<div class="col-md-4 text-right">		
				<form id="buttonBack" action="<?=$baseUrl."/task/assignprivatetask"?>" method="post" style="display: inline;">
					<input id="taskId" type="hidden"  name="taskId" value="<?=$taskId?>">
					<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
					
					<button type="submit" class="btn btn-default" aria-label="Left Align" title="ย้อนกลับ">
						<span class="" aria-hidden="true">ย้อนกลับ</span>
					</button>
				</form>
				<button id="addMember" type="button" class="btn btn-success" aria-label="Left Align" title="เพิ่มผู้รับผิดชอบ">
				  <span class="" aria-hidden="true">เพิ่มผู้รับผิดชอบ</span>
				</button>
			</div>
		</div>
	</div>
</div>
<div class="panel" style="padding: 10px;">
	<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/task/addprivatetask']); ?>
		<div class="row">
			<input type="hidden" name="taskId" value="<?=$taskId?>">
			<input type="hidden" name="name" value="<?=$name?>">
			<input type="hidden" name="department" value="<?=$department?>">
			<input type="hidden" name="page" value="<?=$page?>">
			<input type="hidden" name="sort" value="<?=$sort?>">
			<div class="col-md-6">แสดง <?php echo Html::dropDownList('per-page', $length, $dataTablesLength , ['onchange'=>'this.form.submit()'])?> รายการ</div>
		</div>
	<?php ActiveForm::end(); ?>
	<br>
	<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="member" style="margin-bottom: 1px !important;border: 1px solid #f4f4f4 !important;">
		<thead>
			<tr>
				<th class="text-center"><input type="checkbox" name="checkAll"></th>
				<th class="text-center"><?php echo $dataTablesSort->link('name'); ?></th>
				<th class="text-center"><?php echo $dataTablesSort->link('department'); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $field):?>
				<tr>
					<td class="text-center"><input type="checkbox" class="checkbox-col" data-id="<?=$field->_id?>"></td>
					<td><span><?php echo $field->nameTh." ".$field->sernameTh ?></span></td>
					<td><span><?php echo $field->depName ?></span></td>
					<td>
						<p align="center">
							<button  type="button" class="view btn btn-info glyphicon glyphicon-eye-open btn-sm" title="รายละเอียด" data-id="<?=$field->_id; ?>" ></button>
						</p>
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
				รายการที่ <?php if($users != null):?> <?= $pagination->offset + 1?><?php else:?><?= $pagination->offset?><?php endif;?> ถึง   <?= $lastRecordNo ?> จาก  <?= $pagination->totalCount?> รายการ
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
<!--------View User------->
<div class="modal fade" id="modalView" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
		        	<form action="#" id="formCategory" method="POST">
					    <div class="row">
                            <label class="control-label col-md-6 text-left">ชื่อ-นามสกุล : </label>
                            <div class="col-md-6">
								<span id="viewuserName"></span>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-6 text-left">จำนวนงานทั้งหมดที่รับผิดชอบ : </label>
                            <div class="col-md-6">
								<span id="viewAllTask"></span>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-6 text-left">จำนวนงานในโครงการทั้งหมดที่รับผิดชอบ : </label>
                            <div class="col-md-6">
								<span id="viewAlltaskProject"></span>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-6 text-left">จำนวนงานส่วนตัวทั้งหมดที่รับผิดชอบ : </label>
                            <div class="col-md-6">
								<span id="viewAlltaskprivate"></span>
							</div>
                        </div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>
<?php 
	// Display AccessDeny Modal
	echo AccessDeny::widget();
	// Display Contact Admin
	echo Contact::widget();
	// Display NotCheck
	echo Select::widget();
	echo Success::widget();
?>