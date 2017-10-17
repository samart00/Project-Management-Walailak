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

$this->title = 'เพิ่มผู้รับผิดชอบงานในโครงการ';
$this->params ['breadcrumbs'] [] = [
		'label' => 'โครงการ',
		'url' => [
				"/project"
		]
];
$this->params ['breadcrumbs'] [] = [
		'label' => 'งานในโครงการ',
		'url' => [
				"/task",
				'projectId' => $projectId
		]
];
$this->params['breadcrumbs'][] = $this->title;
$user = Yii::$app->user;

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/task/assign.css");
// $this->registerCssFile("@web/css/common/dataTables.checkboxes.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/task/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/js/common/dataTables.checkboxes.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/js/task/table-datatables-responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.redirect.js',['depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/js/common/ui-toastr.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
// $this->registerJsFile('@web/css/common/bootstrap-toastr/toastr.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$str2 = <<<EOT

$('.view').click(function(){
	var id = $(this).data('id');
	var test = '$projectId';
	var page = 'view';
	var action = 'getusertask';
	callTaskMember(id, page, action);
});
function callTaskMember(id, page, action){
	var categoryData = $.ajax({
		url: '$baseUrl/task/'+action,
		type: 'post',
		data: {
			'projectId' : '$projectId',
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
	$('#viewAlltaskthisProject').html(userData.alltaskinproject);
	$('#viewAlltaskotherProject').html(userData.alltaskotherproject);			
	$('.modal-title').html('จำนวนงานที่รับผิดชอบ');
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
	  	    formData.append('projectId', "$projectId");
		        var request = new XMLHttpRequest();
		        request.open("POST", "$baseUrl/task/addmember", false);
		        request.onreadystatechange = function () {
		            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
		                var response = request.responseText;
		                if(typeof(response) == "string"){
		                    response = JSON.parse(request.responseText);
		        			console.log(response);
		        			if(response.isDelete){
								$('#title-delete').html('เนื่องจากงานนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        					$('#modalIsDelete').modal('show');
	        					setTimeout(function(){
            						$.redirect('$baseUrl/task/index', {'projectId':'$projectId','$csrfParam':'$csrf'});
								}, 2000);
		        			}else{
			        			if(response.success){
			        				$('#success').modal('show');
									setTimeout(function(){
				            			$.redirect('$baseUrl/task/assign', {'taskId': '$taskId','projectId':'$projectId','$csrfParam':'$csrf'});
									}, 2000);
			        			}else{
				            		if(response.isClose){
	        							$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});	
	        						}else{
	        							if(response.isProject){
	        								$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});		        						
	        							}else{
	        								if(response.isCancel){
	        									$.redirect('$baseUrl/project', {'$csrfParam':'$csrf'});
	        								}else{
	        									if(response.isDone){
	        										$.redirect('$baseUrl/task', {'projectId':'$projectId','$csrfParam':'$csrf'});
	        									}
	        								}
	        							}
	        						}
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
		<?php $form = ActiveForm::begin(['action'=>$baseUrl.'/task/add']); ?>
		<input type="hidden" name="projectId" value="<?=$projectId?>">
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
				<form id="buttonBack" action="<?=$baseUrl."/task/assign"?>" method="post" style="display: inline;">
					<input id="taskId" type="hidden"  name="taskId" value="<?=$taskId?>">
					<input type="hidden" name="<?=$csrfParam ?>" value="<?=$csrf?>">
					<input id="projectId" type="hidden" name="projectId" value="<?=$projectId?>">
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
	<?php $form = ActiveForm::begin(['method'=>'get', 'action' => $baseUrl.'/task/add']); ?>
		<div class="row">
			<input type="hidden" name="projectId" value="<?=$projectId?>">
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
				<th class="text-center"></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($users as $field){ ?>
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
			<?php } ?>    
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
                            <label class="control-label col-md-3 text-left">ชื่อ-นามสกุล : </label>
                            <div class="col-md-6">
								<span id="viewuserName"></span>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-3 text-left">ทั้งหมด : </label>
                            <div class="col-md-6">
								<span id="viewAllTask"></span>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-3 text-left">ในโครงการนี้ : </label>
                            <div class="col-md-6">
								<span id="viewAlltaskthisProject"></span>
							</div>
                        </div>
                        <div class="row">
                            <label class="control-label col-md-3 text-left">ในโครงการอื่น : </label>
                            <div class="col-md-6">
								<span id="viewAlltaskotherProject"></span>
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