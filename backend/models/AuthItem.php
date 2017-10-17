<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for collection "auth_item".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $roleName
 * @property mixed $description
 * @property mixed $activeFlag
 * @property mixed $createDate
 * @property mixed $createBy
 */
class AuthItem extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'auth_item'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'name',
            'description',
            'activeFlag',
        	'type',
            'createDate',
            'createBy',
        	'created_at', // ทดสอบ
        	'updated_at', // ทดสอบ
        	'rule_name',  // ทดสอบ	
        	'parents',
        	'module',
        	'permissionName',
        	'canBeDeleted'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description', 'activeFlag', 'type' ,'createDate', 'createBy','created_at', 'updated_at','rule_name','parents','module', 'permissionName','canBeDeleted'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'name' => 'Role Name',
            'description' => 'Description',
            'activeFlag' => 'Active Flag',
        	'type' => 'Type',
            'createDate' => 'Create Date',
            'createBy' => 'Create By',
        	'created_at' => 'test',
        	'updated_at' => 'test',
        	'rule_name' => 'test',
        	'parents' => 'parents',
        ];
    }
    
    public function findAllRoleByStatus($status){
    	$conditions = [];
    	$query = AuthItem::find();
    
    	if(!empty($status)){
    		$conditions['status'] = $status;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	$listRole = $query->all();
    	return $listRole;
    }
     
    public function findAuthItemById($id){
    	$roleData = AuthItem::findOne($id);
    	return $roleData;
    }
    
    public function findAuthItemByNameAndActiveFlag($name, $activeFlag){
    	$conditions = [];
    	$query = AuthItem::find();
    
    	if(!empty($activeFlag)){
    		$conditions['activeFlag'] = $activeFlag;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    
    	if(!empty($name)){
    		$query->andWhere(['like', "name", $name]);
    	}
    
    	$listRole = $query->andWhere(["type" => 1])->all();
    	return $listRole;
    }
    
    public function findAuthItemByType($roleName){
    	$query = AuthItem::find();
    	$listType = $query->where(["type" => 2,"parents"=> $roleName])->all();
    	return $listType;
    }
    
}
