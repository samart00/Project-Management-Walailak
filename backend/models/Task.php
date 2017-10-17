<?php

namespace backend\models;

use Yii;
use common\libs\Status;
use MongoDB\BSON\ObjectID;

/**
 * This is the model class for collection "Task".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $task_name
 * @property mixed $start_date
 * @property mixed $end_date
 * @property mixed $status
 * @property mixed $create_date
 * @property mixed $approve_date
 * @property mixed $create_by
 * @property mixed $member
 * @property mixed $description
 * @property mixed $askforapprove_date
 * @property mixed $Allday
 * @property mixed $progress
 * @property mixed $tag
 */
class Task extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return ['wu-dev', 'task'];
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return [
            '_id',
            'taskName',
            'startDate',
            'endDate',
            'status',
        	'activeFlag',
            'createDate',
            'createBy',
            'member',
            'description',
        	'projectId',
        	'assignee',
        	'progress',
        	'askforapproveDate',
        	'approveDate',
            'Allday',
        	'tag',
        	'sendemail',
        		
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        		[['taskName', 'startDate', 'endDate', 'status','activeFlag','createDate', 'createBy', 'member', 'description','projectId','assignee','progress','approveDate','askforapproveDate','Allday','tag','sendemail'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            '_id' => 'ID',
            'taskName' => 'Task Name',
            'startDate' => 'Start Date',
            'endDate' => 'End Date',
            'status' => 'Status',
        	'activeFlag' => 'activeFlag',
            'createDate' => 'createDate',
            'createBy' => 'Create By',
            'member' => 'Member',
            'description' => 'Description',
        	'projectId' => 'ProjectId',
        	
        	'assignee' => 'Assignee',
        	'progress' => 'Progress',
        	'approveDate' => 'ApproveDate',
        	'askforapproveDate' => 'AskForApproveDate',
        	'Allday' => 'All Day',
        	'tag' => 'Tag',
        	'sendemail' => 'Sendemail',
        ];
    }
    const TYPE_PROGRESS = 0;
    const TYPE_SUCCESS = 1;
    
    
    
    public static $arrType = array(
    		self::TYPE_PROGRESS => "กำลังดำเนินการ",
    		self::TYPE_SUCCESS => "เสร็จแล้ว",
    		
    		
    
    );
    
    public function findAllTaskByName($name){
    	$userId = Yii::$app->user->identity->_id;
    	$project = Project::findAll(['createBy'=>$userId]);
    	
    	$query = Task::find();
    	$query->andWhere(['<>', 'projectId', null]);
    	$query->andWhere(['status' => Status::WAIT_APPROVE_TASK]);
    	
    	$id = [];
    	foreach ($project as $obj){
    		$id[] = $obj->_id;
    	}
    	$query->andWhere(['in','projectId', $id]);
    	$query->addOrderBy(['taskName' => SORT_ASC]);
    	$value = $query->all();
    	return $value;
    }
    
    public function findTaskById($id){
    	$taskData = Task::findOne($id);
    	return $taskData;
    }
    
    
}
