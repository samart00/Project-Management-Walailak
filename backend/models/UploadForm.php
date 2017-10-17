<?php
namespace backend\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
	/**
	 * @var UploadedFile
	 */
	public $file;

	public function rules()
	{
		return [
				[['file'], 'file', 'skipOnEmpty' => false, 'skipOnEmpty' => true,'extensions' => 'csv'],
		];
	}

	public function uploadFile($model, $attribute)
	{
		$file = UploadedFile::getInstance($model, $attribute);
		
	}
}
?>