<?php

use yii\helpers\Html;
use yii\grid\GridView;
use richardfan\widget\JSRegister;
use backend\models\Event;
//use backend\models\CsvForm;
use yii\web\View;
use yii\bootstrap\Modal;
use kartik\file\FileInput;
use yii\widgets\ActiveForm;
use backend\components\Wait;
use backend\components\AccessDeny;
use backend\components\Deleted;
use backend\components\Contact;
use backend\components\Success;

$currentId = Yii::$app->user->identity->_id;

$depCode = Yii::$app->user->identity->depCode;
$proId = "000000000000000000000001";
/*x @var $this yii\web\View */
/* @var $searchModel backend\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$this->title = 'ปฏิทินแผนก';
$this->params['breadcrumbs'][] = $this->title;

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
$this->registerJsFile('@web/js/category/table-datatables-responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/moment.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/fullcalendar.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/locale/th.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/event/jquery.datetimepicker.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/date.format.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/event/checktime.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/event/setting-date-time.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/event/form-validateEdit.js',['depends' => [\yii\web\JqueryAsset::className()]]);


$str2 = <<<EOT

$('#checkTime').change(function(){
debugger;
        if(this.checked){
		
			$('#timeStart').prop('disabled', true);
            $('#timeEnd').prop('disabled', true);
	}
        else{
            $('#timeStart').prop('disabled', false);
            $('#timeEnd').prop('disabled', false);
	}

    });
    
$('#checkTime_Edit').change(function(){
debugger;
        if(this.checked){
		
			$('#timeStart_Edit').prop('disabled', true);
            $('#timeEnd_Edit').prop('disabled', true);
	}
        else{
            $('#timeStart_Edit').prop('disabled', false);
            $('#timeEnd_Edit').prop('disabled', false);
	}

    });
$('#submit').click(function(){
		
		var formData = new FormData();
		var eventName = $('input[id=event_name]').val().trim();
		var startDate = $('input[id=dateStart]').val().trim();
		var startTime = $('input[id=timeStart]').val().trim();
		var endDate = $('input[id=dateEnd]').val().trim();
		var endTime = $('input[id=timeEnd]').val().trim();
		var title = $('input[id=event_name]').val();
		var Allday = $('input[id=checkTime]').is(":checked");
		var CreateBy = "$currentId";
		var depCode = "$depCode";
		var proID = "$proId";
		
		debugger;
		var	startDate1 = startDate.split('/');
		var	startDate_dmY = startDate1[2]+"-"+startDate1[1]+"-"+startDate1[0]+" "+startTime;

		var	endDate1 = endDate.split('/');
		var	endDate_dmY = endDate1[2]+"-"+endDate1[1]+"-"+endDate1[0]+" "+endTime;
		
		if(title.trim().length > 0){
			if(eventName != "" && startDate != "" && endDate != ""){
				formData.append('event_name', eventName);
				formData.append('start_date', startDate_dmY);
				formData.append('end_date', endDate_dmY);
				formData.append('description', $('textarea[id=description]').val());
				var TypeID = $(id=eventtype).val();
				formData.append('TypeID', TypeID);
				formData.append('Allday', Allday);
				formData.append('CreateBy', CreateBy);
				formData.append('depCode', depCode);
				formData.append('proID', proID);
				
				var request = new XMLHttpRequest();
				request.open("POST", "$baseUrl/event/save", true);
				request.onreadystatechange = function () {
			        if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
						debugger;
			       	    var response = request.responseText;
			            if(typeof(response) == "string"){
			            	response = JSON.parse(request.responseText);
			            }
			        }
		    	};
				request.send(formData);
				location.reload();
			}
	}
});

function submit(action){
		var eventId = $('input[id=modalEventId]').val();
		var eventNameEdit = $('input[id=event_name_Edit]').val();
		var startDateEdit = $('input[id=dateStart_Edit]').val();
		var startTimeEdit = $('input[id=timeStart_Edit]').val();
		var endDateEdit = $('input[id=dateEnd_Edit]').val();
		var endTimeEdit = $('input[id=timeEnd_Edit]').val();
		var title = $('input[id=event_name_Edit]').val();
		var Allday = $('input[id=checkTime_Edit]').is(":checked");
		var CreateBy = "$currentId";
		var depCode = "$depCode";
		
		debugger;
		var	startDateEdit1 = startDateEdit.split('/');
		var	startDateEdit_dmY = startDateEdit1[2]+"-"+startDateEdit1[1]+"-"+startDateEdit1[0]+" "+startTimeEdit;

		var	endDateEdit1 = endDateEdit.split('/');
		var	endDateEdit_dmY = endDateEdit1[2]+"-"+endDateEdit1[1]+"-"+endDateEdit1[0]+" "+endTimeEdit;	

        var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
		formData.append('eventId', eventId);
		formData.append('event_name', eventNameEdit);
		formData.append('start_date', startDateEdit_dmY);
		formData.append('end_date', endDateEdit_dmY);
		formData.append('description', $('textarea[id=description_Edit]').val());
		var TypeID = $('#eventtype_edit option:selected').val();
		formData.append('TypeID', TypeID);
		formData.append('Allday', Allday);
		formData.append('CreateBy', CreateBy);
		formData.append('depCode', depCode);
        if(eventNameEdit != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/event/"+action, false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
	//              debugger;
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#calendarModalEdit').modal('hide');
							$('#title-delete').html('เนื่องจากกิจกรรมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
							if(response.success){
								$('#calendarModalEdit').modal('hide');
								$('#success').modal('show');
								location.reload();	        		
		        			}
		        			if(response.isDuplicate){
		        				$('#duplicateCategory').show();
		        				$('#accessDeny').hide();
		        			}	        			
	        			}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){
	            	$('#accessDeny').show();
	            	$('#duplicateCategory').hide();
	            }else{
	            	$('#modalContact').modal('show');
	            }
	        };
	        request.send(formData);
	    }
};

$('#submitDelete').click(function(){
	$('body').addClass("loading");
	var eventId = $('input[id=modalEventId]').val();
	var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('eventId', eventId);
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/event/delete", false);
	        request.onreadystatechange = function () {
	        	$('body').removeClass("loading");
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {
// 	             debugger;
	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        			console.log(response);
	        			if(response.isDelete){
							$('#modalDelete').modal('hide');
	        				$('#title-delete').html('เนื่องจากกิจกรรมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
	        				$('#modalIsDelete').modal('show');
	        			}else{
	        				if(response.success){
								$('#modalDelete').modal('hide');
								$('#success').modal('show');
								location.reload();	        		
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

$('#btnEdit').click(function(){
	$("#formEditEvent").validate().resetForm();
	var id = $(this).data('id');
	var page = 'edit';	
	var action = 'geteditevent';	
	callGetEvent(id, page, action);
});

$('#btnDelete').click(function(){
	var id = $(this).data('id');
	$('.modal-title').html('ลบกิจกรรม');
	$('#submitDelete').attr('modalEventId', id);
	$('#modalDelete').modal('show');
});

function callGetEvent(id, page, action){
	var eventData = $.ajax({
		url: '$baseUrl/event/'+action,
		type: 'post',
		data: {
			'eventId' : id,
			'$csrfParam' : '$csrf'
		},
		beforeSend: function() { $('#wait').modal('show'); },
        complete: function() { $('#wait').modal('hide'); },
		dataType: "json",
		success: function (data) {
			console.log(data);
			if(data.isDelete){
				$('#title-delete').html('เนื่องจากกิจกรรมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว');
				$('#modalIsDelete').modal('show');
			}else{
				if(page == 'edit'){
					$('#accessDeny').hide();
					$('#duplicateCategory').hide();
//					showModalEditCategory(data);
				}
// 				if(page == 'view'){
// 					showModalViewCategory(data);
// 				}
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {
			if(thrownError == 'Forbidden'){
				$('#modalIsAccessDeny').modal('show');
			}else{
				//$('#modalContact').modal('show');//500 Internal Server
			}
	    }
	});
}

$('#save').click(function(){
	var title = $('.modal-title').text();
	var eventName = $('#event_name_Edit').val();
	$("#formEditEvent").validate();
	var isValid = $("#formEditEvent").valid();
	
	if(isValid){
		var page = "";
		page = 'edit';
		submit(page);			
	}
});
EOT;

$this->registerJs($str2, View::POS_END);

?>

<?php JSRegister::begin(); ?>
<script>
$(function () {
     var date = new Date();
     var d = date.getDate(),
         m = date.getMonth(),
         y = date.getFullYear();
     $('#calendar').fullCalendar({
       header: {
         left: 'prev,next, today, createEvent,listevent',
         center: 'title',
         right: 'listYear,month,agendaWeek,agendaDay'
       },
       buttonText: {
           today: 'วันนี้',
           year: 'ปี',
           month: 'เดือน',
           week: 'สัปดาห์',
           day: 'วัน'
         },
         customButtons: {
        	 createEvent: {
               text: 'สร้างกิจกรรม',
            	   click:  function(event, jsEvent, view) {
                       $('#modalTitle').html("สร้างกิจกรรม");
                       $('#modalTitleEdit').html("แก้ไขกิจกรรม");
                       $('#modalBody').html(event.description);
                       $('#eventUrl').attr('href',event.url);
                       $('#calendarModal').modal();
               }
             },
             listevent: {
                 text: 'รายการกิจกรรม',
              	   click:  function(event, jsEvent, view) {
              		 document.location = "<?php echo $baseUrl?>/event/listeventd";
                 }
               }
           },

      events: [
	  <?php foreach ($value as $field): {?>
		{
			<?php 
					$check = $field->Allday;		
					if($check == false){
					?>
							  title: <?php echo "'".$field->Event_name."'";?>,
							  Allday: <?php echo "\"".$field->Allday."\"";?>,
							  start: new Date(<?php echo "\"".date('Y/m/d H:i:s',  strtotime('+0 Hour',$field->Start_Date["sec"]))."\"";?>),
							  end: new Date(<?php echo "\"".date('Y/m/d H:i:s',  strtotime('+0 Hour',$field->End_Date["sec"]))."\"";?>),
							  discription: <?php echo "\"".$field->Discription."\"";?>,
							  backgroundColor: <?php echo "\"".$valuecolor[(string)$field->TypeID]."\"";?>,
							  borderColor:"#000000",
							  TypeID: <?php echo "\"".$field->TypeID."\"";?>,
							  eventId: <?php echo "'".$field->_id."'";?>,
							  TypeName: <?php echo "\"".$field->TypeName."\"";?>
					<?php }else{?>
							  title: <?php echo "'".$field->Event_name."'";?>,
							  Allday: <?php echo "\"".$field->Allday."\"";?>,
							  start: <?php echo "\"".date('Y-m-d',  strtotime('+0 Hour',$field->Start_Date["sec"]))."\"";?>,
							  end: <?php echo "\"".date('Y-m-d',  strtotime('+0 Hour',$field->End_Date["sec"]))."\"";?>,
							  discription: <?php echo "\"".$field->Discription."\"";?>,
							  backgroundColor: <?php echo "\"".$valuecolor[(string)$field->TypeID]."\"";?>,
							  borderColor:"#000000",
							  TypeID: <?php echo "\"".$field->TypeID."\"";?>,
							  eventId: <?php echo "'".$field->_id."'";?>,
							  TypeName: <?php echo "\"".$field->TypeName."\"";?>
					<?php }?>
	    },
	  <?php  }?>
	  <?php endforeach; ?>
			  
      ],
     

      editable: false,
      disableDragging: true,
      eventLimit: true,

      //Test
      eventClick: function(event, element) {
    	  console.log((event));
		  var date_s = event.start;
		  var date_e = event.end;
		  
		  if(event.Allday == "1"){
			  
			  $("#modalEventId").val(event.eventId);
			  $("#showEventName").text(event.title);
	    	  $("#showDateStartt").text((date_s._d).format('d\\/m\\/Y'));
	    	  $("#showDateEnd").text((date_e._d).format('d\\/m\\/Y'));
	    	  $("#showTimeStart").text("00:00");
	    	  $("#showTimeEnd").text("00:00");
	    	  $("#showDescription").text(event.discription);
	    	  $("#showType").text(event.TypeName);
	    	 
	    	  //$('#showType').text(Type);
	    	  $('#showEvent').modal('show');
	    	  if(event.TypeName == "วันหยุด"){
		    	$('#btnEdit').hide();
			  }else
	    	  $('#btnEdit').click(function(){//กดปุ่มแก้ไข
	    			  $("#modalEventId").val(event.eventId);
					  $("#event_name_Edit").val(event.title);
			    	  $("#checkTime_Edit").prop('checked', true);
			    	  $("#dateStart_Edit").val((date_s._d).format('d\\/m\\/Y'));
			    	  $("#dateEnd_Edit").val((date_e._d).format('d\\/m\\/Y'));
			    	  $("#timeStart_Edit").prop('disabled', true);
			  		  $("#timeEnd_Edit").prop('disabled', true);	  
			    	  $("#description_Edit").val(event.discription);
			    	  var check_type = event.TypeName;
		    		  if(check_type == "วันหยุด"){
			    		  debugger;
		    			 $('.dropdownType').hide();
		        	  }else{
		        		  $('.dropdownType').show();
    	  			  }
		    		  $('#calendarModalEdit').modal('show');
		    		  $('#showEvent').modal('hide');
	    	});
	    	  if(event.TypeName == "วันหยุด"){
			    $('#btnDelete').hide();
			  }else
	    	$('#btnDelete').click(function(){//กดปุ่มลบ
				$('#modalDelete').modal('show');
		    });
	    	$('#btnCancel').click(function(){
	    		$('#calendarModalEdit').modal('hide');
	    		$('#showEvent').modal('show');  
	    	});

		  }else {
			  $("#modalEventId").val(event.eventId);
			  $("#showEventName").text(event.title);
	    	  $("#showTimeStart").text((date_s._d).format('H\\:i'));
	    	  $("#showTimeEnd").text((date_e._d).format('H\\:i'));
	    	  $("#showDateStartt").text((date_s._d).format('d\\/m\\/Y'));
	    	  $("#showDateEnd").text((date_e._d).format('d\\/m\\/Y'));
	    	  $("#showDescription").text(event.discription);
	    	  $("#showType").text(event.TypeName);
	    	  
	    	  $('#showEvent').modal('show');
	    	  if(event.TypeName == "วันหยุด"){
			  	$('#btnEdit').hide();
			  }else
	    	  $('#btnEdit').click(function(){//กดปุ่มแก้ไข
	    		  $("#modalEventId").val(event.eventId);
				  $("#event_name_Edit").val(event.title);
		    	  $("#checkTime_Edit").prop('checked', false);
		    	  $("#dateStart_Edit").val((date_s._d).format('d\\/m\\/Y'));
		    	  $("#dateEnd_Edit").val((date_e._d).format('d\\/m\\/Y'));	
		    	  $("#timeStart_Edit").val((date_s._d).format('H\\:i'));
		  		  $("#timeEnd_Edit").val((date_e._d).format('H\\:i'));	  
		    	  $("#description_Edit").val(event.discription);
		    	  var check_type = event.TypeName;
	    		  if(check_type == "วันหยุด"){
		    		  debugger;
	    			 $('.dropdownType').hide();
	        	  }else{
	        		  $('.dropdownType').show();
	  			  }
	    		  $('#calendarModalEdit').modal('show');
	    		  $('#showEvent').modal('hide');
	    		  
	    	});
	    	if(event.TypeName == "วันหยุด"){
				$('#btnDelete').hide();
			}else
	    	$('#btnDelete').click(function(){
	    		 $('#modalDelete').modal('show');
	    	});
	    	$('#btnCancel').click(function(){
	    		 $('#calendarModalEdit').modal('hide');
	    		 $('#showEvent').modal('show');
	    		  
	    	});
		};
      },
    });
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
                
              	
                      
              <div id="calendar"></div>

              	   <div id="calendarModal" class="modal fade">
					<div class="modal-dialog">
					    <div class="modal-content ">
					        <div class="modal-header">
					            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span> <span class="sr-only">close</span></button>
					            <h4 id="modalTitle" class="modal-title"></h4>
					        </div>
					       <form action="" role="form" data-toggle="validator" id="validate"  method="post">
					        <div id="modalBody" class="modal-body">
														        	
					        	<div class="form-group">
								  <label for="usr">หัวข้อกิจกรรม
								  	<span class="required"> * </span>
								  </label>
								  
								  <input type="text" class="form-control" id="event_name" name="eventName" placeholder="หัวข้อกิจกรรม" data-error="กรุณากรอกข้อมูล">
								  <div class="help-block with-errors"></div>
								</div>
									<div class="form-group" style="display: inline-flex;">
										<input id="checkTime" type="checkbox" name="checkTime">&nbsp;<label>ตลอดวัน</label>
									</div>
								<div class="form-group">
									<label for="usr">เริ่มต้น
										<span class="required"> * </span>
									</label>
									<div class='input-group date' >
						                <div class='col-md-4' >
						                    <input id="dateStart" type='text' class="form-control date-picker" name="startDate" placeholder="วันที่เริ่มต้น" data-error="กรุณากรอกข้อมูล">
						                 </div>
						                <div class="col-md-4">
	                                        <input id="timeStart" type="text" class="form-control date-picker1" name="startTime" placeholder="เวลาที่เริ่มต้น" data-error="กรุณากรอกข้อมูล" date-required="true">
						                </div>
	                                 </div>
					                <div class="help-block with-errors"></div>
					            </div>
					            
					            <div class="form-group">
						            <label for="usr">สิ้นสุด
						            	<span class="required"> * </span>
						            </label>
						            <div class='input-group date' >
							            <div class='col-md-4' >
							                <input id="dateEnd" type='text' class="form-control date-picker" name="endDate" placeholder="วันที่สิ้นสุด" data-error="กรุณากรอกข้อมูล">
							            </div>
							            <div class="col-md-4">
		                                    <input id="timeEnd" type="text" class="form-control date-picker2" name="endTime" placeholder="เวลาที่สิ้นสุด" data-error="กรุณากรอกข้อมูล" date-required="true">
							            </div>
						            </div>
						            <div class="help-block with-errors"></div>
					            </div>
								<div class="form-group">
								  <label for="comment">รายละเอียด</label>
								  <textarea class="form-control" rows="5" name="comment" value="" placeholder="รายละเอียด" id="description" style="overflow:auto;resize:none"></textarea>
								</div>
								
								<div class="form-group">
                                    <label class="control-label col-md-3">ประเภทกิจกรรม</label>
                                        <div class="col-md-5">
                                            <?php 
                                               echo  Html::dropDownList( 'eventtype',
                                               'selected optionkk',  
                                               $valueeventtype,
                                               ['class' => 'form-control', 'id' => 'eventtype','options' =>[]]
                                               )
                                           ?>
                                        </div>
                                </div>	
					         </div>
					        
					        <div class="modal-footer">
					        	<button type="submit" class="btn btn-success" id="submit">บันทึก</button>
					        	<button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button> 
				        </div>
				    </div>
				</div>
				</div>
			</form>
			

				<div id="calendarModalEdit" class="modal fade">
					<div class="modal-dialog">
					    <div class="modal-content">
					        <div class="modal-header">
					            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">X</span> <span class="sr-only">close</span></button>
					            <h4 id="modalTitleEdit" class="modal-title"></h4>
					        </div>
					        <div id="modalBody" class="modal-body">
					       <div id="duplicateCategory" class="alert alert-warning alert-dismissible" style="display: none;">
			                                           ฃื่อกิจกรรมซ้ำ เนื่องจากมีการใช้ชื่อกิจกรรมนี้แล้ว
			            	</div>
			            	<div id="accessDeny" class="alert alert-danger alert-dismissible" style="display: none;">
								ขออภัย คุณไม่มีสิทธิ์สร้างกิจกรรม กรุณาติดผู้ดูแลระบบ
			            	</div>
			            	<div id="accessDeny" class="alert alert-danger alert-dismissible" style="display: none;">
								ขออภัย กิจกรรมนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว
			            	</div>
					        	<form id="formEditEvent">
					        	<div class="form-group">
					        	<input type="hidden" class="form-control " id="modalEventId" name="modalEventId" >
								  <label for="usr">หัวข้อกิจกรรม
								  	<span class="required"> * </span>
								  </label>
								  <input type="text" class="form-control" id="event_name_Edit" name="eventName" placeholder="หัวข้อกิจกรรม">
								</div>
								<div class="form-group" style="display: inline-flex;">
									<input id="checkTime_Edit" type="checkbox" name="checkTime">&nbsp;<label>ตลอดวัน</label>
								</div>
								<div class="form-group">
									<label for="usr">เริ่มต้น
										<span class="required"> * </span>
									</label>
									<div class='input-group date' >
						                <div class='col-md-4' >
						                    <input id="dateStart_Edit" type='text' class="form-control date-picker" name="startDate" placeholder="วันที่เริ่มต้น" data-error="กรุณากรอกข้อมูล">
						                 </div>
						                <div class="col-md-4">
	                                        <input id="timeStart_Edit" type="text" class="form-control date-picker1" name="startTime" placeholder="เวลาที่เริ่มต้น" data-error="กรุณากรอกข้อมูล" date-required="true">
						                </div>
	                                 </div>
					                <div class="help-block with-errors"></div>
					            </div>
					            
					            <div class="form-group">
						            <label for="usr">สิ้นสุด
						            	<span class="required"> * </span>
						            </label>
						            <div class='input-group date' >
							            <div class='col-md-4' >
							                <input id="dateEnd_Edit" type='text' class="form-control date-picker" name="endDate" placeholder="วันที่สิ้นสุด" data-error="กรุณากรอกข้อมูล">
							            </div>
							            <div class="col-md-4">
		                                    <input id="timeEnd_Edit" type="text" class="form-control date-picker2" name="endTime" placeholder="เวลาที่สิ้นสุด" data-error="กรุณากรอกข้อมูล" date-required="true">
							            </div>
						            </div>
						            <div class="help-block with-errors"></div>
					            </div>
								<div class="form-group">
								  <label for="comment">รายละเอียด</label>
								  <textarea class="form-control" rows="5" id="description_Edit" style="overflow:auto;resize:none"></textarea>
								</div>
								<div class="dropdownType form-group" id="dropdownType">
                                    <label class="control-label col-md-3">ประเภทกิจกรรม</label>
                                        <div class="col-md-5">
                                            <?php 
                                               echo  Html::dropDownList( 'eventtype',
                                               'selected optionkk',  
                                               $valueeventtype,
                                               ['class' => 'form-control', 'id' => 'eventtype_edit','options' =>[]]
                                               )
                                           ?>
                                        </div>
                                </div>
                                </form>				        	
					         </div>
					        <div class="modal-footer">
					            <button type="button" class="btn btn-success" id="save">บันทึก</button>
					        	<button type="button" class="btn btn-danger" data-dismiss="modal">ยกเลิก</button>
				            </div>
				    </div>
				</div>
				</div>
				
<!-- 			โชว์รายละเอียดกิจกรรม -->
				<div id="showEvent" class="modal fade" tabindex="-1" role="dialog">
              		<div class="modal-dialog" >
              			<div class="modal-content">
              				<div class="modal-header">
					            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span> <span class="sr-only">close</span></button>
					            <h4 id="modalTitle" class="modal-title">รายละเอียดกิจกรรม</h4>
					        </div>
					        <div id="modalBody" class="modal-body">
					        	<input type="hidden" class="form-control " id="modalEventId" name="modalEventId" >
					        	<div class="row">
                                	<label class="control-label col-md-3 text-right">หัวข้อกิจกรรม : </label>
                                	<div class="col-md-9">
                                    	<span id="showEventName" style="word-break: break-all;"></span>
                                	</div>
                           		 </div>
					        	<div class="row">
                                	<label class="control-label col-md-3 text-right">วันที่เริ่มต้น : </label>
                                	<div class="col-md-9">
                                    	<span id="showDateStartt"></span>
                                    	<span id="showTimeStart"></span>
                                	</div>
                           		 </div>
								<div class="row">
	                                <label class="control-label col-md-3 text-right">วันที่สิ้นสุด : </label>
	                                <div class="col-md-9">
	                                    <span id="showDateEnd"></span>
	                                    <span id="showTimeEnd"></span>
	                                </div>
	                            </div>
					            <div class="row">
					            	<label class="control-label col-md-3 text-right">รายละเอียด : </label>
					            	<div class="col-md-9">
	                                    <span id="showDescription" style="word-break: break-all;"></span>
	                                </div>
					            </div>
					             <div class="row">
					            	<label class="control-label col-md-3 text-right">ประเภทกิจกรรม : </label>
					            	<div class="col-md-9">
	                                    <span id="showType"></span>
	                                </div>
					            </div>
					        </div>
					        <div class="modal-footer">
					            <button type="button" id="btnEdit" class="btn btn-warning" data-toggle="modal" >แก้ไข</button>
          						<button type="button" id="btnDelete" class="btn btn-danger" data-dismiss="modal" >ลบ</button> 
					        </div>
              			</div>
              		</div>
              	</div>
            	
            <!--------Delete Category------->
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
									     <label>คุณต้องการลบกิจกรรมนี้ใช่หรือไม่</label>
									</div>
									<div class="text-right">
										 <button id="submitDelete" type="button" class="btn btn-success">ตกลง</button>
										 <button class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
									</div>
					        	</section>
					      	</div>
				    	</div>
				  	</div>
				</div>
				
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /. box -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
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
