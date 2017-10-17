<?php
namespace backend\components;
use yii\base\Widget;
use yii\helpers\Url;

class Contact extends Widget {
	public function run() {
		echo $this->render('contact');
	}
}