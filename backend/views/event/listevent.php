<?php

use richardfan\widget\JSRegister;
use backend\models\Event;
use yii\web\View;
use yii\widgets\ActiveForm;
use backend\components\Success;

$currentId = Yii::$app->user->identity->_id;

/*x @var $this yii\web\View */
/* @var $searchModel backend\models\EventSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$baseUrl = \Yii::getAlias('@web');
$csrf = Yii::$app->request->csrfToken;
$csrfParam = Yii::$app->request->csrfParam;
$this->title = 'รายการกิจกรรม';
if($valueactive == 1){
	$this->params ['breadcrumbs'] [] = [
			'label' => 'ปฏิทินโครงการ',
			'url' => [
					"event/project"
			]
	];
}else if($valueactive == 0){
	$this->params ['breadcrumbs'] [] = [
			'label' => 'ปฏิทินส่วนบุคคล',
			'url' => [
					"index"
			]
	];
}else if($valueactive == 2){
	$this->params ['breadcrumbs'] [] = [
			'label' => 'ปฏิทินแผนก',
			'url' => [
					"event/devision"
			]
	];
}

$this->params ['breadcrumbs'] [] = $this->title;

$this->registerCssFile("@web/css/common/jquery.dataTables.css");
$this->registerCssFile("@web/css/common/dataTables.responsive.css");
$this->registerCssFile("@web/css/common/bootstrap-toggle.min.css");
$this->registerCssFile("@web/css/common/fullcalendar.min.css");
$this->registerCssFile("@web/css/common/jquery.datetimepicker.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.validate.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/bootstrap-toggle.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/category/form-validate.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.dataTables.min.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/dataTables.responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/category/table-datatables-responsive.js', ['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/moment.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/fullcalendar.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/locale/th.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.datetimepicker.full.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/date.format.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/event/checktime.js',['depends' => [\yii\web\JqueryAsset::className()]]);

$str2 = <<<EOT

EOT;

$this->registerJs($str2, View::POS_END);

?>

<?php JSRegister::begin(); ?>
<script>

$("#dateStarts").click(function(){
    $('#input').val('');
});
$("#input").click(function(){
    $('#dateStarts').val('');
    $('#dateEnds').val('');
});
$(function () {
	jQuery.datetimepicker.setLocale('th');
	
	jQuery('#dateStarts').datetimepicker({
		format:'d/m/Y',
		formatDate:'d/m/Y',
		onShow:function( ct ){
			   this.setOptions({
			    maxDate:jQuery('#dateEnds').val()?jQuery('#dateEnds').val():false
			   })
			  },
			  onSelectDate:function(ct,$i){
				  myFunction2();
				},
				onClose:function(ct,$i){
					  myFunction2();
					},
		timepicker:false,
	});
	jQuery('#dateEnds').datetimepicker({
		format:'d/m/Y',
		formatDate:'d/m/Y',
		onShow:function( ct ){
			   this.setOptions({
			    minDate:jQuery('#dateStarts').val()?jQuery('#dateStarts').val():false
			   })
			  },
			  onSelectDate:function(ct,$i){
				  myFunction2();
				},
				onClose:function(ct,$i){
					  myFunction2();
				},	  
		 timepicker:false,
	});

});
$("#col").css('word-wrap','break-word');
$("#col2").css('word-wrap','break-word');
</script>
<?php JSRegister::end(); ?>

<div class="category-index">
<!--   <p class="text-right"> -->
<!--   	  <button id="createEvent" class="btn btn-success">สร้างกิจกรรม</button> -->
<!--   </p> -->
  <div class="box box-solid">
		<div class="box-header with-border">
			<?php $form = ActiveForm::begin(['method'=>'post','action'=>$baseUrl.'/event/listeventp']); ?>
			<?php if($valueactive == 1){ ?> 
			<div class="row">
				<div class="col-md-6">
					<?php if($valuenameP == NULL){?> <p align="center" class="form-control">ขณะนี้แสดงกิจกรรมของทุกโครงการ</p> <?php }else{ ?>
						<p align="center" class="form-control">ขณะนี้แสดงงานของโครงการ<?php echo"  <b>"; foreach ($valuenameP as $field){ echo $field->projectName; echo" ("; echo $field->abbrProjectName; } echo")</b>";?> </p>
					<?php } ?>
				</div>
				<div class="col-md-6">
					<table width="100%" style="border: 1px solid #e0dede; background-color: #f4f4f4;">
						<tr>
							<td width="130px" style="text-align: center;"><span>โครงการ</span></td>
							<td>
								<select name="lProject" class="form-control" onchange="this.form.submit()">
									<?php if($valuenameP == NULL){?><option disabled selected hidden="" value="">--------------- เลือกโครงการที่รับผิดชอบ ---------------</option> <?php }else{ ?>
									<option disabled selected hidden="" value=""><?php echo"  <b>"; foreach ($valuenameP as $field){ echo $field->projectName; echo" ("; echo $field->abbrProjectName; } echo")</b>";?></option>
									<option value="" style="font-weight: bold;">เลือกทั้งหมด</option>  
									<?php }	?>
									<?php 
									foreach ($valuelist as $field){?>                                          	
		                                 <option value="<?php echo $field->_id ; ?>"><?php echo $field->projectName ; ?></option>                              	
		                            <?php } ?>
								</select>
							</td>
						</tr>
					</table>
				</div>				
			</div>
		</div>
		<?php } ?>
	
		<div class="box-header with-border">
			<div class="row">
				<div class="col-md-12">
					<table width="100%" style="border: 1px solid #e0dede; background-color: #f4f4f4;">	
						<tr>							
							<td width="130px" style="text-align: center;"><span>ชื่อกิจกรรม</span></td>
							<td width="30%"><input name="input" id="input" type="text" oninput="myFunction()" class="form-control" placeholder="ค้นหาชื่อกิจกรรม"></td>
							
							<td width="130px" style="text-align: center;"><span>จากวันที่</span></td>
							<td><input id="dateStarts" type="text" class="form-control date-picker" placeholder="จากวันที่"></td>

							<td width="130px" style="text-align: center;"><span>ถึงวันที่</span></td>
							<td><input name="dateEnds" id="dateEnds" type="text" class="form-control date-picker" placeholder="ถึงวันที่"></td>							
						</tr>
					</table>
				</div>				
			</div>
			<?php ActiveForm::end(); ?>		
		</div>
	</div>
	<div class="panel" style="padding: 10px;">

	<script type="text/javascript">
	function myFunction() {
		  var input, filter, table, tr, td, i;
		  input = document.getElementById("input");
		  filter = input.value.toUpperCase();
		  table = document.getElementById("sample_1");
		  tr = table.getElementsByTagName("tr");
		  for (i = 0; i < tr.length; i++) {
		    td = tr[i].getElementsByTagName("td")[0];
		    if (td) {
		      if (td.innerHTML.toUpperCase().indexOf(filter) > -1) {
		        tr[i].style.display = "";
		      } else {
		        tr[i].style.display = "none";
		      }
		    }       
		  }
		}

	function myFunction2() {
		  var input2 = document.getElementById("dateStarts").value;
		  var input1 = document.getElementById("dateEnds").value;
		  if(input1 == ""){
			  var td,aa;
			  var myDate=input2.split("/");
			  var newDate=myDate[1]+"/"+myDate[0]+"/"+myDate[2];
			  var dateEnds = new Date(newDate).getTime();
			  var b = (dateEnds/1000);
			  var filter = parseInt(b);
			  var table = document.getElementById("sample_1");
			  var tr = table.getElementsByTagName("tr");
			  for (var i = 0; i < tr.length; i++) {
			    td = table.rows[i].cells[4].innerHTML;	
			    aa = parseInt(td);
			    if (td) {
			    	if (input2 == "") {
				        tr[i].style.display = "";
				      }else{
				      if (aa >= filter) {
				        tr[i].style.display = "";		        
				      } else {
				        tr[i].style.display = "none";
				      }
			      }
			    }       
			  }
			}else{
				var myDate1=input1.split("/");
				  var newDate1=myDate1[1]+"/"+myDate1[0]+"/"+myDate1[2];
				  var dateEnds1 = new Date(newDate1).getTime();
				  var b1 = (dateEnds1/1000);
				  var filter1 = parseInt(b1);
				  var td,aa,td1,aa1;
				  var myDate=input2.split("/");
				  var newDate=myDate[1]+"/"+myDate[0]+"/"+myDate[2];
				  var dateEnds = new Date(newDate).getTime();
				  var b = (dateEnds/1000);
				  var filter = parseInt(b);
				  var table = document.getElementById("sample_1");
				  var tr = table.getElementsByTagName("tr");
				  for (var i = 0; i < tr.length; i++) {
				    td = table.rows[i].cells[4].innerHTML;	
				    aa = parseInt(td);
				    td1 = table.rows[i].cells[5].innerHTML;	
				    aa1 = parseInt(td1);
				    if (td) {
					      if (aa <= filter) {
					    	  tr[i].style.display = "none";		        
					      } else if(aa1 >= filter1){
					        tr[i].style.display = "none";
					      } else{
					    	  tr[i].style.display = "";
						  }
				      }			           
				  }
			}
		}
	
	</script>
	<table class="table table-striped table-bordered table-hover dt-responsive" id="sample_1">
		<thead>
			<tr>
				<th width="30%" class="all">ชื่อกิจกรรม</th>				
				<th width="10%" class="all">วันที่-เริ่ม</th>
				<th width="10%" class="all">วันที่-สิ้นสุด</th>
				<th width="50%" class="all">รายละเอียด</th>				
				<th hidden=""></th>
				<th hidden=""></th>
			</tr>
		</thead>
		
		<?php if($valueactive == 1){ ?> 
		<?php foreach ($valuelist as $field1){ $i=1;?>					
			<?php foreach ($value as $field){?>
			<?php if($field1->_id == $field->projectID){?>
			<?php if($i==1 && $valuenameP == NULL){?>
			<tbody>
			<tr>
				<td colspan="4"><b>- <?php echo $field1->projectName;?></b></td>
				<td hidden=""></td>
				<td hidden=""></td>
				<td hidden=""></td>
				<td hidden=""></td>
				<td hidden=""></td>
				
			</tr>
			<?php } $i++;?>				
			<tr>
				<td>&nbsp;&nbsp; <span class="fc-event-dot" style="background-color:<?= $valuecolor[(string)$field->TypeID]?>;"></span>&nbsp;&nbsp;<span id="col2"><?= $field->Event_name; ?></span></td>				
				<td><span><?= date('d/m/Y',  strtotime('+0 Hour',$field->Start_Date["sec"])); ?></span></td>
				<td><span><?= date('d/m/Y',  strtotime('+0 Hour',$field->End_Date["sec"])); ?></span></td>
				<td><span id="col"><?= $field->Discription; ?></span></td>
				<td hidden=""><?= $field->Start_Date["sec"]; ?></td>
				<td hidden=""><?= $field->End_Date["sec"]; ?></td>
			</tr>
			<?php } ?>
			 
			<?php } ?>
			</tbody>
		<?php } ?>
		<?php }else{ ?>
		<tbody>
			<?php foreach ($value as $field){?>
			<tr>
				<td>&nbsp;&nbsp; <span class="fc-event-dot" style="background-color:<?= $valuecolor[(string)$field->TypeID]?>;"></span>&nbsp;&nbsp;<span id="col2"><?= $field->Event_name; ?></span></td>				
				<td><span><?= date('d/m/Y',  strtotime('+0 Hour',$field->Start_Date["sec"])); ?></span></td>
				<td><span><?= date('d/m/Y',  strtotime('+0 Hour',$field->End_Date["sec"])); ?></span></td>
				<td><span id="col"><?= $field->Discription; ?></span></td>
				<td hidden=""><?= $field->Start_Date["sec"]; ?></td>
				<td hidden=""><?= $field->End_Date["sec"]; ?></td>
			</tr>
			<?php } ?>
			                   
		</tbody>
		<?php } ?>
	</table>
	</div>
</div>