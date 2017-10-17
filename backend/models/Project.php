<?php

namespace backend\models;

use Yii;
use GuzzleHttp\Psr7\PumpStream;
use common\libs\Status;
use common\libs\ActiveFlag;
use MongoDB\BSON\ObjectID;

/**
 * This is the model class for collection "project".
 *
 * @property \MongoDB\BSON\ObjectID|string $_id
 * @property mixed $projectName
 * @property mixed $startDate
 * @property mixed $endDate
 * @property mixed $description
 * @property mixed $category
 * @property mixed $status
 * @property mixed $createDate
 * @property mixed $createBy
 * @property mixed department
 * @property mixed $member
 */
class Project extends \yii\mongodb\ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function collectionName()
	{
		return ['wu-dev', 'project'];
	}

	/**
	 * @inheritdoc
	 */
	public function attributes()
	{
		return [
				'_id',
				'projectName',
				'abbrProjectName',
				'startDate',
				'endDate',
				'description',
				'category',
				'status',
				'createDate',
				'createBy',
				'departmentId',
				'member',
				'activeFlag',
				'isCreatedTeam'
		];
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				[['projectName', 'abbrProject', 'startDate', 'endDate', 'description', 'category', 'status', 'createDate', 'createBy', 'departmentId', 'member', 'activeFlag', 'isCreatedTeam'], 'safe']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
				'_id' => 'ID',
				'projectName' => 'Project Name',
				'startDate' => 'Start Date',
				'endDate' => 'End Date',
				'description' => 'Description',
				'category' => 'Project Type',
				'status' => 'Status',
				'createDate' => 'Create Date',
				'createBy' => 'Create By',
				'member' => 'Member',
		];
	}

	public function findAllProject($name,$status,$sort,$userId){
		$conditions = [];
		$query = Project::find();

		if(!empty($status)){
			$conditions['status'] = $status;
		}
		if(!empty($userId)){
			$conditions['member.userId'] = $userId;
		}
		if(!empty($conditions)){
			$query->where($conditions);
		}
		if(!empty($name)){
			$query->andWhere(['like', "projectName", $name]);
		}
		 
		$query->orderBy(['status'=>SORT_ASC]);
		 
		if(!empty($sort)){
			if($sort == 1){
				$query->addOrderBy(['projectName'=>SORT_ASC]);
			}elseif ($sort == 2){
				$query->addOrderBy(['status'=>SORT_ASC]);
			}elseif ($sort == 3){
				$query->addOrderBy(['startDate'=>SORT_ASC]);
			}else{
				$query->addOrderBy(['endDate'=>SORT_DESC]);
			}
		}
		 
		$value = $query->all();
		return $value;
	}

	const STATUS_OPEN = 1;
	const STATUS_CLOSE = 2;

	const SORT_PROJECT_NAME = 1;
	const SORT_STATUS = 2;
	const SORT_START_DATE = 3;
	const SORT_END_DATE = 4;


	public static $arrSendStatus = array(
			self::STATUS_OPEN => "เปิด",
			self::STATUS_CLOSE => "ปิด",
	);

	public static $arrSort = array(
			self::SORT_PROJECT_NAME => "ชื่อโครงการ",
			self::SORT_STATUS => "สถานะ",
			self::SORT_START_DATE => "วันที่เริ่ม",
			self::SORT_END_DATE => "วันที่สิ้นสุด"
	);

	public function findAllProjectByProjectNameAndDepartmentId($projectName, $departmentId){
		$conditions = [];
		$query = Project::find();
		 
		if(!empty($projectName)){
			$conditions['projectName'] = $projectName;
		}
		if(!empty($departmentId)){
			$conditions['departmentId'] = $departmentId;
		}

		if(!empty($conditions)){
			$query->where($conditions);
		}
		$listProject = $query->all();
		return $listProject;
	}
	
	public function findAllProjectByProjectNameAndDepartmentIdWithoutProjectId($projectId, $projectName, $departmentId){
		$conditions = [];
		$query = Project::find();
			
		if(!empty($projectName)){
			$conditions['projectName'] = $projectName;
		}
		if(!empty($departmentId)){
			$conditions['departmentId'] = $departmentId;
		}
	
		if(!empty($conditions)){
			$query->where($conditions);
		}
		
		if($projectId != null){
			$query->andWhere(['<>', '_id', new ObjectID($projectId)]);
		}
		$listProject = $query->all();
		return $listProject;
	}
	
	// Is used in Category
	public function findAllProjectByCategory($categoryId){
		$conditions = [];
		$query = Project::find();
		
		if(!empty($categoryId)){
			$conditions['category'] = $categoryId;
		}
		
		if(!empty($conditions)){
			$query->where($conditions);
		}
		$listProject = $query->all();
		return $listProject;
	}
	public function findAllProjectByTeam($teamid){
		$conditions = [];
		$query = Project::find();
	
		if(!empty($teamid)){
			$conditions['member.team.teamId'] = $teamid;
		}
	
		if(!empty($conditions)){
			$query->where($conditions);
		}
		$listProject = $query->all();
		return $listProject;
	}
	public function findAllProjectByProjectName($projectName,$statusOrFlag,$departmentId){
		$query = Project::find();
		
		if(empty($statusOrFlag)){
			$query->orWhere(['status'=>Status::CANCEL]);
			$query->orWhere(['activeFlag'=>ActiveFlag::INACTIVE]);
		}else{
			if($statusOrFlag == 1){
				$query->andWhere(['activeFlag'=>ActiveFlag::INACTIVE]);
			}else{
				$query->andWhere(['status'=>Status::CANCEL]);
			}
		}
		
		if(!empty($departmentId)){
			$query->andWhere(["departmentId" => new ObjectID($departmentId)]);
		}
	
		if(!empty($projectName)){
			$query->andWhere(['like', "projectName", $projectName]);
		}
		
	
		$listProject = $query->all();
		return $listProject;
	}
	
	public function findNumberOfProjectByUserId($userId){
		$query = Project::find();
		$conditions = [];
		 
		if(!empty($userId)){
			$conditions['createBy'] = new ObjectID($userId);
		}
		 
		if(!empty($conditions)){
			$query->where($conditions);
		}
		 
		$minProject = $query->count();
		 
		return (int)$minProject;
	}
	
	public function getProjectName($projectId){
		$model = Project::findOne($projectId);
		return $model->projectName;
	}
}
