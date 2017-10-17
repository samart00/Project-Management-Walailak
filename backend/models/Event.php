<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for collection "Event".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $Event_name
 * @property mixed $Discription
 * @property mixed $Start_Date
 * @property mixed $End_Date
 * @property mixed $TypeID
 * @property mixed $Allday
 * @property mixed $CreateBy
 * @property mixed $Calendar
 * @property mixed $TypeName
 */
class Event extends \yii\mongodb\ActiveRecord
{
	const INDIVIDUAL = 1;
	const DEPARTMENT = 2;
	const PROJECT = 3;
	const ALL = 4;
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'Event'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'Event_name',       	
            'Discription',
            'Start_Date',
            'End_Date',
            'TypeID',
        	'projectID',
        	'projectName',
        	'EventSname',
        	'Allday',
        	'CreateBy',
        	'Calendar',
        	'depCode',
        	'TypeName',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        		[['Event_name', 'Discription', 'Start_Date', 'End_Date', 'TypeID','projectID','projectName', 'EventSname', 'Allday','CreateBy','Calendar','depCode','TypeName'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'Event_name' => 'Event Name',       	
            'Discription' => 'Discription',
            'Start_Date' => 'Start  Date',
            'End_Date' => 'End  Date',
            'TypeID' => 'TypeID',
        	'projectID' => 'projectID',
        	'projectName' => 'projectName',
        	'EventSname' => 'EventSname',
        	'Allday' => 'Allday',
        	'CreateBy' => 'CreateBy',
        	'Calendar' => 'Calendar',
        	'depCode' => 'depCode',
        	'TypeName' => 'TypeName',
        ];
    }
    
    public function search($params)
    {
    	$query = Event::find();
    
    	// add conditions that should always apply here
    
    	
    	$query = Event::find();
    	
    	
    
    	// grid filtering conditions
    	$query->andFilterWhere(['like', '_id', $this->_id])
    	->andFilterWhere(['like', 'Event_name', $this->Event_name])   	
    	->andFilterWhere(['like', 'Discription', $this->Discription])
    	->andFilterWhere(['like', 'Start_Date', $this->Start_Date])
    	->andFilterWhere(['like', 'End_Date', $this->End_Date])
    	->andFilterWhere(['like', 'TypeID', $this->TypeID])
    	->andFilterWhere(['like', 'projectID', $this->projectID])
    	->andFilterWhere(['like', 'projectName', $this->projectName])
    	->andFilterWhere(['like', 'EventSname', $this->EventSname])
    	->andFilterWhere(['like', 'Allday', $this->Allday])
    	->andFilterWhere(['like', 'CreateBy', $this->CreateBy])
    	->andFilterWhere(['like', 'depCode', $this->depCode])
    	->andFilterWhere(['like', 'TypeName', $this->TypeName]);
    	
    	$dataProvider = $query->all();
    	return $dataProvider;
    }
}
