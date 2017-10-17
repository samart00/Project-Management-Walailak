<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for collection "EventType".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $Type_name
 * @property mixed $Color
 * @property mixed $Calendar
 */
class EventType extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'eventType'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'Type_name',
            'Color',
        	'Calendar',
        	'activeFlag',
        	'status',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Type_name', 'Color','Calendar','activeFlag','status'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'Type_name' => 'Type name',
            'Color' => 'Color',
        	'Calendar' => 'Calendar',
        	'activeFlag' => 'activeFlag',
        	'status'=> 'status'
        ];
    }
    
    public function search($params)
    {
    	$query = EventType::find();
    
    	// add conditions that should always apply here
    
    	
    	$query = EventType::find();
    	
    	
    
    	// grid filtering conditions
    	$query->andFilterWhere(['like', '_id', $this->_id])
    	->andFilterWhere(['like', 'Type_name', $this->Type_name])
    	->andFilterWhere(['like', 'Color', $this->Color])
    	->andFilterWhere(['like', 'Calendar', $this->Calendar])
    	->andFilterWhere(['like', 'activeFlag', $this->activeFlag])
    	->andFilterWhere(['like', 'status', $this->status]);
    	$dataProvider = $query->all();
    	return $dataProvider;
    }
    
    public function findAllCategoryByActiveFlag($activeFlag){
    	$conditions = [];
    	$query = EventType::find();
    	 
    	if(!empty($activeFlag)){
    		$conditions['activeFlag'] = $activeFlag;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	$listEventType = $query->all();
    	return $listEventType;
    }
    
    public function findCategoryByNameAndActiveFlag($Type_name, $activeFlag){
    	$conditions = [];
    	$query = EventType::find();
    
    	if(!empty($activeFlag)){
    		$conditions['activeFlag'] = (int)$activeFlag;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    
    	if(!empty($Type_name)){
    		$query->andWhere(['like', "Type_name", $Type_name]);
    	}
    
    	$listEventType = $query->all();
    	return $listEventType;
    }
    
}
