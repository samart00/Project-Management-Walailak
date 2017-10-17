<?php

namespace backend\models;

use Yii;
use \yii\web\UploadedFile;
use \MongoDate;
use MongoDB\BSON\ObjectID;

/**
 * This is the model class for collection "comment".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $comment
 * @property mixed $createTime
 * @property mixed $commentBy
 * @property mixed $type
 * @property mixed $refId
 * @property mixed $images
 * @property mixed $allfiles
 * @property mixed $filename
 */
class Comment extends \yii\mongodb\ActiveRecord
{
	const UPLOAD_FOLDER ='comment';
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'comment'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'comment',
            'createTime',
            'commentBy',
            'type',
            'refId',
        	'images',
        	'allfiles',
        	'filename',
        		
        		
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
            	['images'], 'file',
        		'skipOnEmpty' => true,
        		'extensions' => 'png,jpg',
            	'maxFiles' => 5,
            	'maxSize' => 512000,
            	'tooBig' => 'ขนาดรูปภาพต้องไม่เกิน 500KB',
        	],
        	[
        		['allfiles'], 'file',
        		'skipOnEmpty' => true,
        		'extensions' => 'pdf,rar,zip,png,jpg,doc,docx,xls,xlsx,ppt,pptx,ACCDB,vpp,sln,txt',
        		'maxFiles' => 3,
        		'maxSize' => 30720000,
        		'tooBig' => 'ขนาดไฟล์ต้องไม่เกิน 3mb',
        		
        	]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'comment' => 'Comment',
            'createTime' => 'Create Time',
            'commentBy' => 'Comment By',
            'type' => 'Type',
            'refId' => 'Ref ID',
        	'images' => 'Images',
        	'allfiles' => 'allfiles',
        	'filename' => 'filename',
        	
        		
        ];
    }
    public function getUploadPath(){
    	return Yii::getAlias('@webroot').'/'.Comment::UPLOAD_FOLDER.'/';
    }
    
    public function getUploadUrl(){
    	return Yii::getAlias('@web').'/'.Comment::UPLOAD_FOLDER.'/';
    }
    
    public function getPhotoViewer(){
    	return empty($this->images) ? Yii::getAlias('@web').'/images/none.png' : Comment::getUploadUrl().$this->images;
    }
    
    public function getPhotoUserViewer($userId){
    	$model = User::findOne($userId);
    	return empty($model->images) ? Yii::getAlias('@web').'/images/none.png' : Comment::getUploadUrl().$model->images;
    }
    
    public function getPhotoUser($userId){
    	$model = User::findOne($userId);
    	return empty($model->images) ? Yii::getAlias('@web').'/images/none.png' : Comment::getUploadPath().$model->images;
    }
    
//     public function upload($model,$attribute)
//     {
//     	$photo  = UploadedFile::getInstance($model, $attribute);
//     	$path = $this->getUploadPath();
//     	if ($this->validate() && $photo !== null) {
    
//     		$fileName = md5($photo->baseName.time()) . '.' . $photo->extension;
//     		//$fileName = $photo->baseName . '.' . $photo->extension;
//     		if($photo->saveAs($path.$fileName)){
//     			return $fileName;
//     		}
//     	}
//     	return $model->isNewRecord ;
//     }

    public function upload($Id)
    {
    	$currentId = Yii::$app->user->identity->_id;
    	$arrImage = [];
    	if ($this->validate()) {
    		foreach ($this->images as $file) {
    			$path = $this->getUploadPath();
    			$fileName = md5($file->baseName.time()) . '.' . $file->extension;
    			$arrImage[] = $fileName;
    			$file->saveAs($path.$fileName);
    			
    			$commentModel = new Comment();
    			$commentModel->images = $fileName;
    			$commentModel->commentBy = $currentId;
    			$commentModel->refId = new ObjectID($Id);
    			$commentModel->createTime = new MongoDate();
    			$commentModel->save();
    		}
    		return $arrImage;
    	} else {
    		return false;
    	}
    }
    public function uploadfiles($Id)
    {
    	$currentId = Yii::$app->user->identity->_id;
    	$arrFile = [];
    	if ($this->validate()) {
    		foreach ($this->allfiles as $file) {
    			$path = $this->getUploadPath();
    			$fileName = md5($file->baseName.time()) . '.' . $file->extension;
    			$arrFile[] = $file->baseName . '.' .$file->extension;
    			$file->saveAs($path.$fileName);
    			 
    			$commentModel = new Comment();
    			$commentModel->allfiles = $fileName;
    			$commentModel->filename = $file->baseName . '.' .$file->extension;
    			$commentModel->commentBy = $currentId;
    			$commentModel->refId = new ObjectID($Id);
    			$commentModel->createTime = new MongoDate();
    			$commentModel->save();
    		}
    		return $arrFile;
    	} else {
    		return false;
    	}
    }
}
