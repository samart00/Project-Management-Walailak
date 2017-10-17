<?php
namespace backend\components;
use yii\base\Widget;
use yii\helpers\Url;

class AutoComplete extends Widget {
	
	public $data;
	public $placeholder;
	public $value;
	public $showValue;
	public $inputNameValue;
	public $inputNameShowValue;
		
	public function run() {
		if($this->showValue == ""){
			$this->value = "";
		}
		echo $this->render('autocomplete', [
			'data' => json_encode($this->data),
			'placeholder' => $this->placeholder,
			'showValue' => $this->showValue,
			'value' => $this->value,
			'inputNameValue' => $this->inputNameValue,
			'inputNameShowValue' => $this->inputNameShowValue		
		]);
	}
}