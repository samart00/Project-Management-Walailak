<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for collection "auth_assignment".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $user_id
 * @property mixed $item_name
 * @property mixed $created_at
 */
class AuthAssignment extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'auth_assignment'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'user_id',
            'item_name',
            'created_at',
        	'canBeDeleted'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'item_name', 'created_at','canBeDeleted'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'user_id' => 'User ID',
            'item_name' => 'Item Name',
            'created_at' => 'Created At',
        ];
    }
    
    // Is used in Authitem
    public function findAllAuthAssignmentByRole($name){
    	$conditions = [];
    	$query = AuthAssignment::find();
    
    	if(!empty($name)){
    		$conditions['item_name'] = $name;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	$listAuthassignment = $query->all();
    	return $listAuthassignment;
    }
    
    public function haveMemberInRole($roleName){
    	$haveMember = false;
    	$model = AuthAssignment::findOne(["item_name"=>$roleName]);
    	if($model != null){
    		$haveMember = true;
    	}
    	return $haveMember;
    }
}
