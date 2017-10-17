<?php 
use yii\web\View;

$this->registerCssFile("@web/css/common/styles.css");

$this->registerJsFile('@web/js/common/jquery-3.1.1.min.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.mockjax.js',['depends' => [\yii\web\JqueryAsset::className()]]);
$this->registerJsFile('@web/js/common/jquery.autocomplete.js',['depends' => [\yii\web\JqueryAsset::className()]]);

$str = <<<EOT
	var data = $data;
	$('#autocomplete').autocomplete({
		lookup: data,
		onSelect: function (suggestion) {
			$('#data').val(suggestion.data);
		}
	});
	
EOT;
$this->registerJs($str, View::POS_LOAD, 'form-js');

?>

<div>
	<input type="hidden" name="<?=$inputNameValue?>" id="data" value="<?=$value?>">
	<input type="text" name="<?=$inputNameShowValue?>" id="autocomplete" class="form-control" value="<?=$showValue?>" placeholder="<?=$placeholder?>"/>
</div>