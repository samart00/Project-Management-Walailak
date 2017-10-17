<?php
use yii\helpers\Html;
use yii\web\View;
use app\Entity;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $model backend\models\Project */
$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$project = $projectId;
$this->title = 'สร้างงาน';
$this->params['breadcrumbs'][] = ['label' => 'งาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$str = <<<EOT

$('#save').click(function(){
debugger;

		var taskName = $('input[name=taskname]').val();
		var description = $('textarea[name=description]').val();
		var startDate = $('input[id=from]').val();
			startDate = startDate.split('/');
			startDate = startDate[2]+"-"+startDate[1]+"-"+startDate[0];
		var startTime = $('input[id=fromTime]').val();
			startTime = startTime.split(':');
			startTime = (startTime[0]-6)+":"+startTime[1];
		var endDate = $('input[id=to]').val();
			endDate = endDate.split('/');
			endDate = endDate[2]+"-"+endDate[1]+"-"+endDate[0];
		var endTime = $('input[id=toTime]').val();
			endTime = endTime.split(':');
			endTime = (endTime[0]-6)+":"+endTime[1];
		var formData = new FormData();
        formData.append('$csrfParam', '$csrf');
        formData.append('projectId', '$project');
        formData.append('taskName', taskName);
        formData.append('description', description);
        formData.append('startdate', startDate+" "+startTime);
        formData.append('enddate', endDate+" "+endTime);
       debugger;
        if(taskName != ""){
	        var request = new XMLHttpRequest();
	        request.open("POST", "$baseUrl/task/savetask", false);
	        request.onreadystatechange = function () {
	            if(request.readyState === XMLHttpRequest.DONE && request.status === 200) {

	                var response = request.responseText;
	                if(typeof(response) == "string"){
	                    response = JSON.parse(request.responseText);
	        		toastr.options = {
						  "closeButton": true,
						  "debug": false,
						  "positionClass": "toast-top-full-width",
						  "onclick": null,
						  "showDuration": "1000",
						  "hideDuration": "1000",
						  "timeOut": "3000",
						  "extendedTimeOut": "1000",
						  "showEasing": "swing",
						  "hideEasing": "linear",
						  "showMethod": "fadeIn",
						  "hideMethod": "fadeOut"
					};
	        			console.log(response);

	        			if(response.isDuplicate){
	        			debugger;
		        				$('#duplicateTask').show();
		        				$('#accessDeny').hide();
		  
	        			}else{
	        			debugger;
							if(response.success){
	        					$('#modalTeam').hide();
	        					toastr['success'](response.message, "บันทึกสำเร็จ");
	        					setTimeout(function(){
								$.redirect('$baseUrl/task/index', {'projectId': '$project','$csrfParam':'$csrf'});
										}, 2000);
		        			}
		     
	        			}
	                }
	            }else if(request.readyState === XMLHttpRequest.DONE && request.status === 403){debugger;

	            	$('#accessDeny').show();
	            	$('#duplicateTeam').hide();
	            }
	        };
	        request.send(formData);
	    }
});

EOT;
$this->registerJs($str, View::POS_LOAD, 'form-js');
$this->registerCssFile("@web/css/project/components-md.min.css");
$this->registerCssFile("@web/css/project/plugins-md.min.css");
$this->registerCssFile("@web/css/project/jquery-ui.css");
$this->registerCssFile("@web/css/project/jquery.datetimepicker.css");
$this->registerCssFile("@web/css/common/bootstrap-toastr/toastr.min.css");


$this->registerJsFile('@web/js/project/jquery.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/moment.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/bootstrap.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/select2.full.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/jquery.validate.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/form-validate.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/jquery.datetimepicker.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/validate-date-time.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/setting-date-time.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/jquery.inputmask.bundle.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/project/form-input-mask.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/css/common/bootstrap-toastr/toastr.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/ui-toastr.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.redirect.js',['depends' => [\yii\web\JqueryAsset::className()]]);

?>
<div class="task-create">
<div class="col-md-12">
    <div class="portlet light bordered myBox">
    												<div class="row">
    													<div class="form-group">
                                                            <label class="control-label col-md-3">ชื่อโครงการ
                                                                <span class="required"> * </span>
                                                            </label>
                                                            <div class="col-md-5">
                                                                <input type="text" class="form-control" name="taskname" placeholder="ชื่อโครงการ" id="projectname" maxlength="50"/>
                                                                <span id="error-name" class="error-date"></span>
                                                            </div>
                                                            <br><br>
                                                        </div>
                                                        </div>
                                                        <div class="row">
                                                         <div class="form-group">
                                                            <label class="control-label col-md-3">วันที่เริ่มต้น
                                                                <span class="required"> * </span>
                                                            </label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control date-picker" name="startdate" placeholder="วันที่เริ่มต้น" id="from" />
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="text" id="fromTime" class="form-control date-picker" name="starttime" placeholder="เวลาเริ่มต้น" value="09:00"/>
                                                            </div>
                                                            <br><br>
                                                        </div>
                                                        </div>
                                                        <div class="row">
                                                        <div class="form-group">
                                                            <label class="control-label col-md-3">วันที่สิ้นสุด
                                                                <span class="required"> * </span>
                                                            </label>
                                                            <div class="col-md-3">
                                                                <input type="text" class="form-control date-picker" name="stopdate" placeholder="วันที่สิ้นสุด" id="to"/>
                                                            </div>
                                                            <div class="col-md-2">
                                                                <input type="text" id="toTime" class="form-control date-picker" name="stoptime" placeholder="เวลาสิ้นสุด" value="18:00"/>
                                                            	<span id="requireDate" class="error-date"></span>
                                                            </div>
                                                            <br><br>
                                                        </div>
                                                        
                                                           
                                                        </div>
                                                        <div class="row">
                                                        <div class="form-group">
                                                            <label class="control-label col-md-3">คำอธิบาย</label>
                                                            <div class="col-md-5">
                                                                <textarea class="form-control" name="description" rows="3" placeholder="คำอธิบาย" id="description"></textarea>
                                                            </div>
                                                        </div>
                                                        <br><br>
                                                        </div>
                                                        <div class="text-right">
						 	<input id="save" type="submit" class="btn btn-success" value="บันทึก">
						 	<button id="cancel" class="btn btn-danger" data-dismiss="modal" aria-label="Close">ยกเลิก</button>
						</div>
    </div>
</div>
    
</div>