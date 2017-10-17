<?php
namespace backend\components;
use yii\base\Widget;
use yii\helpers\Url;

class AccessDeny extends Widget {
	public function run() {
		echo $this->render('accessDeny');
	}
}