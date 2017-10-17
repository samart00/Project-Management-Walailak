<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for collection "role".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $roleName
 * @property mixed $description
 * @property mixed $activeFlag
 * @property mixed $createDate
 * @property mixed $createBy
 */
class Role extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'role'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'roleName',
            'description',
            'activeFlag', //เปลี่ยนจาก status เป็น  activeFlag
            'createDate',
            'createBy',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['roleName', 'description', 'activeFlag', 'createDate', 'createBy'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'roleName' => 'Role Name',
            'description' => 'Description',
            'activeFlag' => 'ActiveFlag',
            'createDate' => 'Create Date',
            'createBy' => 'Create By',
        ];
    }
    
    public function findAllRoleByStatus($status){
    	$conditions = [];
    	$query = Role::find();
    	 
    	if(!empty($status)){
    		$conditions['status'] = $status;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	$listRole = $query->all();
    	return $listRole;
    }
     
    public function findRoleById($id){
    	$roleData = Role::findOne($id);
    	return $roleData;
    }
}
