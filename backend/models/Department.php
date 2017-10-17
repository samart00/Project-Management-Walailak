<?php

namespace backend\models;

use Yii;
use common\models\User;

/**
 * This is the model class for collection "department".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $department_name
 */
class Department extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'department'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
        	'depCode',
            'depName'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['depCode','depName'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'departmentName' => 'Department Name',
        ];
    }
    
    public function getDepartmentName($userId){
    	$model = User::findOne($userId);
    	return $model->depName;
    }
    
    public function getDepartmentNameById($departmentId){
    	$model = Department::findOne($departmentId);
    	return $model->depName;
    }
    
    public function getDepartmentNameByDepCode($depCode){
    	$model = Department::findOne(['depCode'=>$depCode]);
    	return $model->depName;
    }
    
    public function getDepartmentNameByProjectId($projectId){
    	$model = Project::findOne($projectId);
    	$model = Department::findOne(['depCode'=>$model->departmentId]);
    	return $model->depName;
    }
}
