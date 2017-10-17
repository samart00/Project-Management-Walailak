<?php
namespace backend\components;
use yii\base\Widget;
use yii\helpers\Url;

class Success extends Widget {
	public function run() {
		echo $this->render('success');
	}
}