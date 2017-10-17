<?php

use yii\helpers\Html;
use yii\grid\GridView;
use richardfan\widget\JSRegister;
use backend\models\EventType;
use yii\web\View;
use yii\bootstrap\Modal;
use kartik\file\FileInput;
use yii\widgets\ActiveForm;
use backend\components\Success;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use backend\components\Wait;
use common\libs\ActiveFlag;
use common\libs\Status;

$holiday = "วันหยุด";

/*x @var $this yii\web\View */
/* @var $searchModel backend\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$baseUrl = \Yii::getAlias('@web');
$this->title = 'กำหนดประเภทกิจกรรม';
$this->params['breadcrumbs'][] = $this->title;
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/common/bootstrap-toggle.min.css");
$this->registerCssFile("@web/css/common/fullcalendar.min.css");
$this->registerCssFile("@web/css/common/jquery.datetimepicker.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/bootstrap-toggle.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/event/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
//$this->registerJsFile('@web/js/category/table-datatables-responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/moment.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/fullcalendar.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/locale/th.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.datetimepicker.full.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/date.format.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/event/checktime.js',['depends' => [\yii\web\JqueryAsset::className()]]);

$str2 = <<<EOT
$('#submit').click(function(){
		debugger;
		var formData = new FormData();
		var Type_name = $('input[id=Type_name]').val().trim();
		var Color = $('input[name="CheckType"]:checked').val();
		var calendar1 =  parseInt($('input[name="checkcalendar1"]:checked').val());
		var calendar2 =  parseInt($('input[name="checkcalendar2"]:checked').val());
		var calendar3 =  parseInt($('input[name="checkcalendar3"]:checked').val());
		if(calendar1 != 1){
    		calendar1 = 0;
    	}
    	if(calendar2 != 1){
    		calendar2 = 0;
    	}
    	if(calendar3 != 1){
    		calendar3 = 0;
    	}
    	
		debugger;
		if(Type_name.trim().length > 0){
				formData.append('Type_name', Type_name);
				formData.append('Color', Color);
				formData.append('Calendar1', calendar1);
				formData.append('Calendar2', calendar2);
				formData.append('Calendar3', calendar3);
				
				var request = new XMLHttpRequest();
				request.open("POST", "$baseUrl/eventtype/save", true);
				request.onreadystatechange = function () {
			        if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
						debugger;
			       	    var response = request.responseText;
			            if(typeof(response) == "string"){
			            	console.log(response);
			            	response = JSON.parse(request.responseText);
			            if(response.success){
								$('#modalCategory').modal('hide');
								$('#success').modal('show');
								location.reload();	        		
		        			}
		        			if(response.isDuplicate){
		        				$('#duplicateEventtype').show();
		        				$('#accessDeny').hide();
		        			}
		        			if(response.isDuplicateColor){
		        				$('#duplicateEventtypeColor').show();
		        				$('#accessDeny').hide();
		        			}
			       		}      	
				}
				};
		    
				request.send(formData);
// 				location.reload();
	}
});


$('.edit').click(function(){
	debugger;
	$("#checkcount_edit_pro").prop('checked', false);
	$("#checkcount_edit_dep").prop('checked', false);
	$("#checkcount_edit_indiv").prop('checked', false);
	
	var id = $(this).data('id');
	var name = $(this).data('name');
	
	var roleData = $.ajax({
		url: '$baseUrl/eventtype/getedit', 
		type: 'post',
		data: {
			'typeId' : id,
			'typeName' : name,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
    	complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			debugger;
			showModalEditRole(data);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			debugger;
			if(thrownError == 'Forbidden'){
				$('#modalIsAccessDeny').modal('show');
			}else{
				$('#modalContact').modal('show');
			}
	    }
	});
});

$('#save').click(function(){
		debugger;
		var TypeId = $('input[name=modalTypeId]').val();
		var Type_name = $('input[name=modalTypeName]').val();
		var Color = $('input[name="color"]:checked').val();
		var typeEdit = $('input[name=type_Edit]').val();
		var colorEdit = $('input[name=color_Edit]').val();
		
		var calendar1 =  parseInt($('input[name="checkcalendar1"]:checked').val());
		var calendar2 =  parseInt($('input[name="checkcalendar2"]:checked').val());
		var calendar3 =  parseInt($('input[name="checkcalendar3"]:checked').val());
		if(calendar1 != 1){
    		calendar1 = 0;
    	}
    	if(calendar2 != 1){
    		calendar2 = 0;
    	}
    	if(calendar3 != 1){
    		calendar3 = 0;
    	}

        var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('TypeId', TypeId);
        formData.append('Type_name', Type_name);
        formData.append('Color', Color);
        formData.append('typeEdit', typeEdit);
        formData.append('colorEdit', colorEdit);
        formData.append('Calendar1', calendar1);
		formData.append('Calendar2', calendar2);
		formData.append('Calendar3', calendar3);
    	
        if(Type_name != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/eventtype/edit");
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
							if(response.success){
								$('#modalRole').modal('hide');
								$('#success').modal('show');
								setTimeout(function(){ 
					            location.reload();
								}, 2000);	        		
	        				}
	        				if(response.isDuplicate){
		        				$('#duplicateEventtypeEdit').show();
		        				$('#accessDeny').hide();
		        			}
		        			if(response.isDuplicateColor){
		        				$('#duplicateEventtypeColorEdit').show();
		        				$('#accessDeny').hide();
		        			}
	        				
						}
	            }
	        };
	        request.send(formData);
	    }
});
	        		
$('.delete').click(function(){
	var id = $(this).data('id');
	var name = $(this).data('Type_name');
	$('.modal-title').html('ลบประเภทกิจกรรม');
	$('#submitDelete').attr('data-id', id);
	$('#submitDelete').attr('data-name', name);
	$('#modalDelete').modal('show');
});
	        		
$('#submitDelete').click(function(){
	debugger;
	$('body').addClass("loading");
	var TypeId = $(this).attr('data-id');
	
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('TypeId', TypeId);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/eventtype/delete");
	        request.onreadystatechange = function () {
	        	$('body').removeClass("loading");
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	             debugger;
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalDelete').modal('hide');
	        				$('#title-delete').html('เนื่องจากประเภทกิจกรรมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}
	        			else if(response.isTypeUsedByID){
	        				$('#modalDelete').modal('hide');
	        				$('#modalIsUsedInAuthassignment').modal('show');
						}
	        			else{
	        				if(response.success){
	        					$('#modalDelete').modal('hide');
								$('#success').modal('show');
								setTimeout(function(){ 
					            	location.reload();
								}, 2000);
		        			}
	        			}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#modalDelete').modal('hide');
	        		$('#modalIsAccessDeny').modal('show');
	            }
	        };
	    request.send(formData);
});
					
function showModalEditRole(data){
	
	debugger;
	var typeData = data.typeData;
	var typeName = data.typeName;   		
	
	$('#modalTypeId').val(typeData._id);
	       		
	if(typeData.Color == "#99A036A"){
		$("#optradio1").prop('checked', true);
	}else if(typeData.Color == "#3D9A8A"){
		$("#optradio2").prop('checked', true);
	}else if(typeData.Color == "#9A3D78"){
		$("#optradio3").prop('checked', true);
	}else if(typeData.Color == "#8F9A3D"){
		$("#optradio4").prop('checked', true);
	}else if(typeData.Color == "#D7D116"){
		$("#optradio5").prop('checked', true);
	}else if(typeData.Color == "#FF66CC"){
		$("#optradio6").prop('checked', true);
	}else if(typeData.Color == "#9999ff"){
		$("#optradio7").prop('checked', true);
	}
	
	$('#modalTypeName').val(typeData.Type_name); 
	$('#type_Edit').val(typeData.Type_name);  
	$('#color_Edit').val(typeData.Color); 
	      		
	$.each(typeName, function( index, value ) {
	    if(value.Calendar == 3){
			$("#checkcount_edit_pro").prop('checked', true);
		}else if(value.Calendar == 2){
			$("#checkcount_edit_dep").prop('checked', true);
		}else if(value.Calendar == 1){
			$("#checkcount_edit_indiv").prop('checked', true);
		}else if(value.Calendar == 4){
			$("#checkcount_edit_indiv").prop('checked', true);
	        $("#checkcount_edit_pro").prop('checked', true);
	        $("#checkcount_edit_dep").prop('checked', true);
		}
    });        		
	        		
	$('.modal-title').html('แก้ไขประเภทกิจกรรม');
	$('#modalRole').modal('show');
}
$('#submit').click(function(){
		var theForm = $('#validate');
		var typeName = $('#Type_name').val();
		var calendar1 = $('input[name=checkcalendar1]').is(':checked');
		var calendar2 = $('input[name=checkcalendar2]').is(':checked');
		var calendar3 = $('input[name=checkcalendar3]').is(':checked');
		var result1 = false;
		var result2 = false;
		if(calendar1 || calendar2 || calendar3){
			$('#check').hide();
			result1 = true;
		}else{
			$('#check').html("กรุณาเลือกปฏิทิน");
			$('#check').show();
		}
		
		if(typeName == ''){
			$('#checkName').html("กรุณากรอกชื่อประเภทกิจกรรม");
			$('#checkName').show();
		}else{
			$('#checkName').hide();
			result2 = true;
		}
		
		if(result1 && result2){
// 			location.reload();
		}
		
});

$('#save').click(function(){
		var theFormEdit = $('#validate');
		var typeNameEdit = $('#modalTypeName').val();
		var calendarEdit1 = $('input[name=checkcalendar1]').is(':checked');
		var calendarEdit2 = $('input[name=checkcalendar2]').is(':checked');
		var calendarEdit3 = $('input[name=checkcalendar3]').is(':checked');
		var resultEdit1 = false;
		var resultEdit2 = false;
		if(calendarEdit1 || calendarEdit2 || calendarEdit3){
			$('#checkEdit').hide();
			resultEdit1 = true;
		}else{
			$('#checkEdit').html("กรุณาเลือกปฏิทิน");
			$('#checkEdit').show();
		}
		
		if(typeNameEdit == ''){
			$('#checkNameEdit').html("กรุณากรอกชื่อประเภทกิจกรรม");
			$('#checkNameEdit').show();
		}else{
			$('#checkNameEdit').hide();
			resultEdit2 = true;
		}
		
		if(resultEdit1 && resultEdit2){
// 			location.reload();
		}
		
});
	        		

$(document).on('click', ".toggle", function() {
   	var toggle = $(this).children();
   	var Type_name = toggle.data('id');
   	var activeFlag = toggle.val();
   	
	$('#submitFlag').attr('data-id', Type_name);
   	$('#submitFlag').attr('data-flag', activeFlag);
	$('#modalActiveFlag').modal('show');
});

$('#submitFlag').click(function(){
	var Type_name = $(this).attr('data-id');
	var activeFlag = $(this).attr('data-flag');
	changeActiveFlag(Type_name, activeFlag);
});

function changeActiveFlag(Type_name, status){
        var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('Type_name',Type_name);
        formData.append('status', status);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/eventtype/changeactiveflag", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
// 	             debugger;
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalActiveFlag').modal('hide');
							$('#title-delete').html('เนื่องจากประเภทกิจกรรมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
		        			if(response.success){
								location.reload();	        		
		        			}
		        		}
	                }
	            }else if(request.status === 403){
					$('#modalActiveFlag').modal('hide');
	            	$('#modalIsAccessDeny').modal('show');
				}else{
					$('#modalContact').modal('show');
				}
	        };
	        request.send(formData);
};	        
EOT;

$this->registerJs($str2, View::POS_END);
// ?>

<?php JSRegister::begin(); ?>
<script>
$(function () {
	eventType: [
	   	  <?php foreach ($valueeventType as $field): {?>
	   		{
	   			Type_name: <?php echo "'".$field->Type_name."'";?>,
	   			Color: <?php echo "\"".$field->Color."\"";?>,
	   	    },
	   	  <?php  }?>
	   	  <?php endforeach; ?>
	         ]
	         
});
</script>
<?php JSRegister::end(); ?>

<div class="event-index">
   <div class="wrapper">
    <section class="content">
      <div class="row">
        <!-- /.col -->
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-body no-padding">
              <!-- THE CALENDAR -->
                          
<!--               <div id="calendar"></div> -->
<!--               	<div id="calendarModal" class="modal fade"> -->
<!-- 					<div class="modal-dialog"> -->
<!-- 					    <div class="modal-content "> -->
<!-- 					        <div class="modal-header"> -->
					            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"></span> <span class="sr-only">close</span></button>
<!-- 					            <h4 id="modalTitle" class="modal-title"></h4> -->
					        </div>
<!-- 					       <form action="" role="form" data-toggle="validator" id="validate" method="post"> -->
					        <!-- ********** BODY MODAL ********** -->
					        <div id="modalBody" class="modal-body">
								<section class="content-modal">
	        				<div id="duplicateEventtype" class="alert alert-warning alert-dismissible" style="display: none;">
		                                           	ฃื่อประเภทกิจกรรมซ้ำ เนื่องจากมีการใช้ชื่อประเภทกิจกรรมนี้แล้ว
		            		</div>
		            		<div id="duplicateEventtypeColor" class="alert alert-warning alert-dismissible" style="display: none;">
		                                           	สีประเภทกิจกรรมซ้ำ เนื่องจากมีการใช้สีประเภทกิจกรรมนี้แล้ว
		            		</div>
<!-- 							<input type="hidden" id="EventtypeId" name="EventtypeId">				        	 -->
					        	<div class="form-group">
								  <label for="usr">ชื่อประเภทกิจกรรม
								  	<span class="required"> * </span>
								  	<font color="red"><span id="checkName"> </span></font>
								  </label>
								  <div class='input-group' >
								  <div class='col-md-12' >
								  <input type="text" class="form-control" id="Type_name" name="eventType" placeholder="ชื่อประเภทกิจกรรม" data-error="กรุณากรอกข้อมูล">
								  </div>
								  </div>
								  <div class="help-block with-errors"></div>
								</div>
								
																
							<div class="panel" style="padding: 10px;">	
								<table class="" width="100%" id="table_policy">
							<thead>
  								<tr>
    								<th> ปฏิทิน <span class="required"> * </span> 
    									<font color="red"><span id="check"> </span></font></th>
    								<th> กำหนดสี </th>							
  								</tr>
  							</thead>
  									<tbody>
  										<tr>
   											<td><input id="checkcount3" type="checkbox" name="checkcalendar3" value="1"> ปฏิทินโครงการ </td>
    										<td><input type="radio" id="optradio" name="CheckType" value="#99A036A" data-error="กรุณาเลือก" checked="checked"> <span class="fc-event-dot" style="background-color:#99A036A"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio" name="CheckType" value="#3D9A8A" data-error="กรุณาเลือก"> <span class="fc-event-dot" style="background-color:#3D9A8A"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio" name="CheckType" value="#9A3D78" data-error="กรุณาเลือก"> <span class="fc-event-dot" style="background-color:#9A3D78"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio" name="CheckType" value="#8F9A3D" data-error="กรุณาเลือก"> <span class="fc-event-dot" style="background-color:#8F9A3D"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio" name="CheckType" value="#D7D116" data-error="กรุณาเลือก"> <span class="fc-event-dot" style="background-color:#D7D116"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio" name="CheckType" value="#FF66CC" data-error="กรุณาเลือก"> <span class="fc-event-dot" style="background-color:#FF66CC"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio" name="CheckType" value="#9999ff" data-error="กรุณาเลือก"> <span class="fc-event-dot" style="background-color:#9999ff"></span>
    										</td>
    					
  										</tr>
									</tbody>
							
									<tbody>
  										<tr>
   											<td><input id="checkcount2" type="checkbox" name="checkcalendar2" value="1"> ปฏิทินแผนก</td>
  										</tr>
									</tbody>
									<tbody>
  										<tr>
   											<td><input id="checkcount1" type="checkbox" name="checkcalendar1" value="1"> ปฏิทินส่วนบุคคล  </td>
  										</tr>
									</tbody>
								</table>
								</div>	
						<div class="role-index">
 							<div class="site-index">
  								<div class="">
									<div class="panel" style="padding: 10px;">	
										<div class="form-group">
											<label for="usr">ประเภทกิจกรรม
											</label>
										</div>
							<table class="table table-striped table-bordered table-hover dt-responsive" width="100%" id="table_policy">
								<thead>
										<tr>
											<th class="all">ชื่อประเภทกิจกรรม</th>
											<th class="all">ปฏิทินโครงการ</th>
											<th class="all">ปฏิทินแผนก</th>
											<th class="all">ปฏิทินส่วนบุคคล</th>
											<th class="all">แก้ไข</th>
											<th class="all">สถานะ</th>
										</tr>
								</thead>
									<tbody>
										<?php 
										    
											//var_dump($querytype_name);
											foreach ($type_Name as $item_type):
										?>
									<tr>
											<td>
												<span>
												<?php 
													foreach ($querytype_name as $item_color):
													if($item_type == $item_color->Type_name){
													 echo '<span class="fc-event-dot" style="background-color:'. $item_color->Color .'"></span> '.$item_type;
													 break;
													}
													endforeach;
												?>										
											    </span>
											</td>
											<?php 
												$data = null;
 												$calandar = array(3,2,1);
	 											foreach ($calandar as $i):
	 												$row = null;
	 												foreach ($querytype_name as $field): // object 1 ตัว
	 													$data = $field;
	 													if($field->Type_name == $item_type 
		 														&& $field->Type_name == $holiday){
		 													$row = $field;
		 													break;
		 												}
		 												if($field->Type_name == $item_type && $i == $field->Calendar){
		 													$row = $field;
		 													break;
		 												}
		 												else{
		 													$row = null;
		 												}
	 												endforeach;	 
		 											if($row != null){
		 														echo '<td>';
			 													echo '<div align="center">';
			 													?>
			 													<?php if($field->activeFlag == ActiveFlag::ACTIVE){ 
			 														echo '<button id="type_pro" type="button" class="delete btn btn-danger glyphicon glyphicon-trash btn-sm" title="ลบ" data-id="'. $field->_id .'" data-name="'. $field->Type_name .'"></button>';
			 													 } ?> 
			 													<?php 
			 													echo '</div>';
			 													echo '</td>';
		 											}
		 											else{
		 												echo '<td></td>';
		 											}
		 											endforeach;
		 											?>
		 											<?php
		 											foreach ($querytype_name as $field):
		 											//var_dump($item_button->Type_name);
		 											if($item_type == $field->Type_name ){
		 														echo '<td>';
				 												echo '<div align="center">';
				 												?>
				 												<?php if($field->status == Status::OPEN){
				 												echo '<button id="querytype_name" type="button" class="edit btn btn-warning glyphicon glyphicon-edit btn-sm" title="แก้ไข" data-id="'.$field->_id.'" data-name="'.$field->Type_name.'"></button>';
				 												}?> 
			 													<?php
				 												echo '</div>';
				 												echo '</td>';
	 															echo '<td>';
	 															echo '<div align="center">';
	 															?>
	 															<?php if($field->Type_name == $holiday){?>
	 																<span style="visibility:hidden"> <?=$field->status; ?></span>
	 															<?php	
	 																 }
	 															else{	?>
	 															<input type="checkbox" class="toggle-switch" <?php echo ($field->status == Status::OPEN)?"checked":""; ?> disabled="disabled" data-toggle="toggle" data-on="เปิดใช้งาน" data-off="ปิดใช้งาน" data-style="ios" data-size="mini" data-onstyle="success" value="<?=$field->status;?>" data-id="<?=$field->Type_name; ?>">
	 															<?php 
	 															}
	 															?>
	 															<span style="visibility:hidden"><?=$field->status; ?></span>
	 															<?php
	 															echo '</div>';
	 															echo '</td>';
		 												break;
		 											}	 																	
 												endforeach;
												?>
									</tr>
									
										<?php 
											endforeach;
										?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>	
					        <div class="modal-footer">
					        
					        	<button type="button" class="btn btn-success" id="submit">บันทึก</button>
					        	<button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button> 
				        </div>
<!-- 				    </div> -->
<!-- 				</div> -->
<!-- 				</div> -->
<!-- 				 </form> -->
            <!-- /.box-body -->
          </div>
          <!-- /. box -->
        </div>
        <!-- /.col -->
      </div>
      <div id="calendarModalEdit" class="modal fade">
					<div class="modal-dialog">
					    <div class="modal-content">
					        <div class="modal-header">
					            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">X</span> <span class="sr-only">close</span></button>
					            <h4 id="modalTitleEdit" class="modal-title"></h4>
					        </div>
					        <div id="modalBody" class="modal-body">
					        	
					        	<div class="form-group">
								  <label for="usr">หัวข้อกิจกรรม
								  	<span class="required"> * </span>
								  </label>
								  <input type="text" class="form-control" id="event_name_Edit" name="eventName" placeholder="หัวข้อกิจกรรม">
								</div>
								
				    </div>
				</div>
				</div>
      </div>
      
      
      
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  
</div>

<div class="modal fade" id="modalDelete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
						<div class="form-group">
					      	<label>คุณต้องการลบประเภทกิจกรรมนี้ใช่หรือไม่</label>
					    </div>
					    <div class="text-right">
						 	<button id="submitDelete" type="button" class="btn btn-success"  >ตกลง</button>
						 	<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</section>
	      	</div>
    	</div>
  	</div>
</div>

<div class="modal fade" id="modalRole" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <div>
        	<span class="modal-title"></span>
        </div>
      </div>
      <div class="modal-body">
        <section class="content-modal">
        	<div id="duplicateEventtypeEdit" class="alert alert-warning alert-dismissible" style="display: none;">
		                                ฃื่อประเภทกิจกรรมซ้ำ เนื่องจากมีการใช้ชื่อประเภทกิจกรรมนี้แล้ว
		    </div>
		    <div id="duplicateEventtypeColorEdit" class="alert alert-warning alert-dismissible" style="display: none;">
		                                     สีประเภทกิจกรรมซ้ำ เนื่องจากมีการใช้สีประเภทกิจกรรมนี้แล้ว
		   </div>
        	<form id="formRole" >
        			<input type="hidden" id="modalTypeId" name="modalTypeId">
						<div class="form-group">
					      <label>ชื่อประเภทกิจกรรม <span class="required">*</span></label>
					      <font color="red"><span id="checkNameEdit"> </span></font>
					      <input type="text" id="modalTypeName" name="modalTypeName" class="form-control" placeholder="=ชื่อประเภทกิจกรรม" >
					      <div class="panel" style="padding: 10px;">	
								<table class="" width="100%" id="table_policy">
							<thead>
  								<tr>
    								<th> ปฏิทิน <span class="required"> * </span> 
    									<font color="red"><span id="checkEdit"> </span></font></th>
    								<th> กำหนดสี </th>							
  								</tr>
  							</thead>
  									<tbody>
  										<tr>
   											<td><input id="checkcount_edit_pro" type="checkbox" name="checkcalendar3" value="1"> ปฏิทินโครงการ </td>
    										<td><input type="radio" id="optradio1" name="color" value="#99A036A" data-error="กรุณาเลือก"> <span class="color fc-event-dot" style="background-color:#99A036A"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio2" name="color" value="#3D9A8A" data-error="กรุณาเลือก"> <span class="color fc-event-dot" style="background-color:#3D9A8A"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio3" name="color" value="#9A3D78" data-error="กรุณาเลือก"> <span class="color fc-event-dot" style="background-color:#9A3D78"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio4" name="color" value="#8F9A3D" data-error="กรุณาเลือก"> <span class="color fc-event-dot" style="background-color:#8F9A3D"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio5" name="color" value="#D7D116" data-error="กรุณาเลือก"> <span class="color fc-event-dot" style="background-color:#D7D116"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio6" name="color" value="#FF66CC" data-error="กรุณาเลือก"> <span class="color fc-event-dot" style="background-color:#FF66CC	"></span>
    										&nbsp;&nbsp;&nbsp;<input type="radio" id="optradio7" name="color" value="#9999ff" data-error="กรุณาเลือก"> <span class="color fc-event-dot" style="background-color:#9999ff"></span>
    										</td>
    					
  										</tr>
									</tbody>
									<tbody>
  										<tr>
   											<td><input id="checkcount_edit_dep" type="checkbox" name="checkcalendar2" value="1"> ปฏิทินแผนก</td>
  										</tr>
									</tbody>
									<tbody>
  											<tr>
   											<td><input id="checkcount_edit_indiv" type="checkbox" name="checkcalendar1" value="1"> ปฏิทินส่วนบุคคล  </td>
  										</tr>
									</tbody>
									<tbody>
  										<tr>
   											<td><input type="hidden" class="form-control" id="type_Edit" name="type_Edit" ></td>
  										</tr>
  										<tr>
   											<td><input type="hidden" class="form-control" id="color_Edit" name="color_Edit" ></td>
  										</tr>
									</tbody>
								</table>
								</div>	
					      
					      
					    </div>
					    <div class="modal-footer">
						 	<button id="save" type="button" class="btn btn-success">ตกลง</button>
						 	<button id="cancel" class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
			</form>
        </section>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="modalActiveFlag" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
  	<div class="modal-dialog" role="document">
    	<div class="modal-content">
    		<div class="modal-header">
    			<button type="button" class="close" data-dismiss="modal" aria-label="Close" title="ปิด"><span>&times;</span></button>
		        <div>
		        	<span class="modal-title" style="font-size: 20px">เปลี่ยนสถานะประเภทกิจกรรม</span>
		        </div>
    		</div>
	     	<!-- ********** BODY MODAL ********** --> 
	      	<div class="modal-body">
	        	<section class="content-modal">
		        	<form action="javascript;" id="formRole" method="POST">
						<div class="form-group">
					      <label>คุณต้องการเปลี่ยนสถานะประเภทกิจกรรมนี้ใช่หรือไม่</label>
					    </div>
					    <div class="text-right">
						 	<input type="button" id="submitFlag" class="btn btn-success" value="ตกลง">
						 	<button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
					</form>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>


<div class="modal fade" id="modalIsUsedInAuthassignment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-target=".bs-example-modal-sm">
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
					      <label><b>ไม่สามารถลบประเภทกิจกรรมนี้ได้เนื่องจากถูกใช้งานอยู่</b></label>
					    </div>
	        	</section>
	      	</div>
    	</div>
  	</div>
</div>

<?php 
	// Display Deleted Modal
	echo Deleted::widget();
	// Display AccessDeny Modal
	echo AccessDeny::widget();
	// Display Waiting Modal
	echo Wait::widget();
	// Display Contact Admin
	echo Contact::widget();
	// Display Success
	echo Success::widget();
?>
