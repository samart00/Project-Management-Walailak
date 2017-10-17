<?php

namespace backend\models;

use Yii;
use \yii\web\UploadedFile;

/**
 * This is the model class for collection "team".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $team_name
 * @property mixed $description
 * @property mixed $status
 * @property mixed $create_date
 * @property mixed $create_by
 * @property mixed $member
 *  @property mixed $activeFlag
 */
class Team extends \yii\mongodb\ActiveRecord
{
	const UPLOAD_FOLDER ='uploads/teamImages/';
	
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'team'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'teamName',
            'description',
            'status',
            'createDate',
            'createBy',
            'member',
        	'activeFlag',
        	'images'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['teamName', 'description', 'status', 'createDate', 'createBy', 'member','activeFlag','images'], 'safe'],
        	[['images'], 'file',
        		'skipOnEmpty' => true,
        		'extensions' => 'png,jpg',
        		'maxSize' => 512000, 'tooBig' => 'ขนาดรูปภาพต้องไม่เกิน 500KB'
        	],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'teamName' => 'Team Name',
            'description' => 'Description',
            'status' => 'Status',
            'createDate' => 'Create Date',
            'createBy' => 'Create By',
            'member' => 'Member',
        	'activeFlag' => 'ActiveFlag',
        ];
    }
    public function findAllTeam($name,$status,$sort){
    	$conditions = [];
    	$query = Team::find();
    	if(!empty($status)){
    		$conditions['status'] = $status;
    	}
    	if(!empty($sort)){
    		$conditions['sort'] = $sort;
    	}
    	$conditions['activeFlag'] = 1;
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	if(!empty($name)){
    		$query->andWhere(['like', "teamName", $name]);
    	}
    	$query->addOrderBy(['teamName'=>SORT_ASC]);
    	$value = $query->all();
    	return $value;
    }
    
    public function findAllTeamByName($name, $activeFlag){
    	$query = Team::find();
    	if(!empty($name)){
    		$query->andWhere(['like', "teamName", $name]);
    	}
    	if(!empty($activeFlag)){
    		$query->andWhere(['activeFlag' => (int)$activeFlag]);
    	}
    	$query->addOrderBy(['teamName'=>SORT_ASC]);
    	$value = $query->all();
    	return $value;
    }
    
    public function findAllTeamByActiveFlag($activeFlag){

    	$conditions = [];
    	$query = Team::find();
    	if(!empty($activeFlag)){
    		$conditions['activeFlag'] = $activeFlag;
    	}
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	$query->addOrderBy(['teamName' => SORT_ASC]);
    	$listTeam = $query->all();
    	return $listTeam;
    }
    public function findTeamById($id){
    	$teamData = Team::findOne($id);
    	return $teamData;
    }
    
    public function getUploadPath(){
    	return Yii::getAlias('@webroot').'/'.Team::UPLOAD_FOLDER.'/';
    }
    
    public function getUploadUrl(){
    	return Yii::getAlias('@web').'/'.Team::UPLOAD_FOLDER.'/';
    }
    
    public function getPhotoViewer(){
    	return empty($this->images) ? Yii::getAlias('@web').'/images/common/members.png' : Team::getUploadUrl().$this->images;
    }
    
    public function getPhotoTeamViewer($teamId){
    	$model = Team::findOne($teamId);
    	return empty($model->images) ? Yii::getAlias('@web').'/images/common/members.png' : Team::getUploadUrl().$model->images;
    }
    
    public function getPhotoTeam($teamId){
    	$model = Team::findOne($teamId);
    	return empty($model->images) ? Yii::getAlias('@web').'/images/common/members.png' : Team::getUploadPath().$model->images;
    }
    
    public function upload($model,$attribute)
    {
    	$photo  = UploadedFile::getInstance($model, $attribute);
    	$path = $this->getUploadPath();
    	if ($this->validate() && $photo !== null) {
    
    		$fileName = md5($photo->baseName.time()) . '.' . $photo->extension;
    		//$fileName = $photo->baseName . '.' . $photo->extension;
    		if($photo->saveAs($path.$fileName)){
    			return $fileName;
    		}
    	}
    	return $model->isNewRecord ? false : $model->getOldAttribute($attribute);
    }
    
}
