<?php

namespace backend\controllers;

use Yii;
use backend\models\Project;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\libs\ActiveFlag;
use common\libs\Status;
use yii\filters\AccessControl;
use backend\models\Department;
use yii\helpers\ArrayHelper;
use backend\models\Task;
use MongoDB\BSON\ObjectID;
use common\models\User;
use common\libs\DateTime;
use backend\models\Category;
use common\models\Employee;
use yii\data\Pagination;
use yii\data\Sort;
use backend\models\Log;
use \MongoDate;

class ProjectManagementController extends Controller
{
	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
				'access' => [
						'class' => AccessControl::className(),
						'rules' => [
								[
										'actions' => ['login', 'error'],
										'allow' => true,
								],
								[
										'allow' => true,
										'roles' => ['@'],
								],
						],
				]
		];
	}

	/**
	 * Lists all Project models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$request = Yii::$app->request;
		 
		$name = $request->post('name',null);
		$activeFlag = $request->post('activeFlag',null);
		$departmentId = $request->post('departmentId',null);
		$length = (int)$request->post('per-page',null);
		if(empty($name)){
			$name = $request->get('name',null);
		}

		if(empty($departmentId)){
			$departmentId = $request->get('departmentId',null);
		}
		
		if(empty($activeFlag)){
			$activeFlag = (int)$request->get('activeFlag',null);
		}
		 
		if(empty($length)){
			$defaultLength = 10;
			$length = $request->get('per-page',$defaultLength);
		}
		
		$name = trim($name);
		
		$dataTablesLength = [
				10 => "10",
				20 => "20",
				30 => "30",
				50 => "50",
		];
		 
		$page = $request->get('page',1);
		$sort = $request->get('sort','name');
		
		$dataTablesSort = new Sort([
				'defaultOrder' => [
						'projectName' => SORT_ASC
				],
				'attributes' => [
						'projectName' => [
								'asc' => ['projectName' => SORT_ASC],
								'desc' => ['projectName' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'ชื่อโครงการ',
						],
						'activeFlag' => [
								'asc' => ['status' => SORT_ASC,'activeFlag' => SORT_ASC],
								'desc' => ['status' => SORT_DESC,'activeFlag' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'สถานะ',
						]
				],
		]);
		
		$dataTablesSort->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'name' => $name,
				'departmentId' => $departmentId,
				'activeFlag' => $activeFlag
		];
		
		$query = Project::find();
		
		if(empty($activeFlag)){
			$query->orWhere(['status'=>Status::CANCEL]);
			$query->orWhere(['activeFlag'=>ActiveFlag::INACTIVE]);
		}else{
			if($activeFlag == 1){
				$query->andWhere(['activeFlag'=>ActiveFlag::INACTIVE]);
			}else{
				$query->andWhere(['status'=>Status::CANCEL]);
			}
		}
		
		if(!empty($departmentId)){
			$query->andWhere(["departmentId" => $departmentId]);
		}
		
		if(!empty($name)){
			$query->andWhere(['like', "projectName", $name]);
		}
		
		$pagination = new Pagination([
				'totalCount' => $query->count(),
				'defaultPageSize' => 10
		]);
		$pagination->setPageSize($length);
		$query->offset($pagination->offset);
		$query->orderBy($dataTablesSort->orders);
		$query->limit($pagination->limit);
		
		$listProject = $query->all();
		$pagination->params = [
			'page'=> $pagination->page,
			'sort'=>$sort,
			'per-page'=>$length,
			'name' => $name,
			'activeFlag' => $activeFlag,
			'departmentId' => $departmentId
		];

		$departmentModel = new Department();
		$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
		return $this->render('index', [
				'name' => $name,
				'listProject' => $listProject,
				'activeFlag' => $activeFlag,
				"listDepartment" => $listDepartment,
				"departmentId" => $departmentId,
				"pagination" => $pagination,
				"dataTablesLength" => $dataTablesLength,
				"length" => $length,
				"sort" => $sort,
				"dataTablesSort" => $dataTablesSort,
				"page" => $page
		]);
	}

	public function actionChangeactiveflag(){

		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;

		$projectId = $request->post('projectId', null);
		$activeFlag = $request->post('activeFlag', null);

		$model = Project::findOne($projectId);
		$isDelete = $this->isDelete($model);
		
		$retData['success'] = false;
		if($isDelete){
			$retData['isDelete'] = true;
		}else{
			$logs = new Log();
			$templog = [];
			$templog['_id'] = $model->_id;
			$templog['activeFlag'] = $model->activeFlag;
			$templog['category'] = $model->category;
			$templog['createBy'] = $model->createBy;
			$templog['createDate'] = $model->createDate;
			$templog['departmentId'] = $model->departmentId;
			$templog['description'] = $model->description;
			$templog['endDate'] = $model->endDate;
			$templog['isCreatedTeam'] = $model->isCreatedTeam;
			$templog['member'] = $model->member;
			$templog['projectName'] = $model->projectName;
			$templog['startDate'] = $model->startDate;
			$templog['status'] = $model->status;
			
			if($model != null){
				$model->activeFlag = ((int)$activeFlag == ActiveFlag::ACTIVE)? ActiveFlag::INACTIVE : ActiveFlag::ACTIVE ;
				$retData['activeFlag'] = $model->activeFlag;
			}
			
			if($model->save()){
				$this->ChangeActiveFlagTask($projectId, ActiveFlag::ACTIVE);
				$logs->oldData = $templog;
				$logs->newData = '';
				$logs->userId = $currentId;
				$logs->refId = new ObjectID($projectId);
				$logs->editDate = new MongoDate();
				$logs->action = 'เปิดใช้งานโครงการ';
				$logs->memberId = '';
				if ($logs->save()){
					$retData['success'] = true;
				}
			}
		}
		echo json_encode($retData);
	}

	public function actionChangestatus(){

		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;

		$projectId = $request->post('projectId', null);
		$status = $request->post('status', null);
		
		$model = Project::findOne($projectId);
		$isDelete = $this->isDelete($model);
		$retData['success'] = false;
		
		if($isDelete){
			$retData['isDelete'] = true;
		}else{
			$logs = new Log();
			$templog = [];
			$templog['_id'] = $model->_id;
			$templog['activeFlag'] = $model->activeFlag;
			$templog['category'] = $model->category;
			$templog['createBy'] = $model->createBy;
			$templog['createDate'] = $model->createDate;
			$templog['departmentId'] = $model->departmentId;
			$templog['description'] = $model->description;
			$templog['endDate'] = $model->endDate;
			$templog['isCreatedTeam'] = $model->isCreatedTeam;
			$templog['member'] = $model->member;
			$templog['projectName'] = $model->projectName;
			$templog['startDate'] = $model->startDate;
			$templog['status'] = $model->status;
			
			$model->status = ((int)$status == Status::OPEN)? Status::CANCEL : Status::OPEN ;
			if($model->save()){
				$this->ChangeActiveFlagTask($projectId, ActiveFlag::ACTIVE);
				$logs->oldData = $templog;
				$logs->newData = '';
				$logs->userId = $currentId;
				$logs->refId = new ObjectID($projectId);
				$logs->editDate = new MongoDate();
				$logs->action = 'เปิดโครงการ';
				$logs->memberId = '';
				if ($logs->save()){
					$retData['success'] = true;
				}
			}
		}
		 
		echo json_encode($retData);
	}

	public function actionDelete(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$projectId = $request->post('projectId', null);
		$currentId = Yii::$app->user->identity->_id;
		$model = Project::findOne($projectId);
		
		$isDelete = $this->isDelete($model);
		$retData['success'] = false;
			
		if($isDelete){
			$retData['isDelete'] = true;
		}else{
			$logs = new Log();
			$templog = [];
			$templog['_id'] = $model->_id;
			$templog['projectName'] = $model->projectName;
			$templog['startDate'] = $model->startDate;
			$templog['endDate'] = $model->endDate;
			$templog['description'] = $model->description;
			$templog['status'] = $model->status;
			$templog['category'] = $model->category;
			$templog['departmentId'] = $model->departmentId;
			$templog['activeFlag'] = $model->activeFlag;
			$templog['member'] = $model->member;
			$templog['createBy'] = $model->createBy;
			$templog['createDate'] = $model->createDate;
			$templog['isCreatedTeam'] = $model->isCreatedTeam;
			if($model->delete()){
				$this->deleteTaskInProject($projectId);
				$logs->oldData = $templog;
				$logs->newData = '';
				$logs->userId = $currentId;
				$logs->refId = new ObjectID($projectId);
				$logs->editDate = new MongoDate();
				$logs->action = 'ลบโครงการออกจากฐานข้อมูล';
				$logs->memberId = '';
				if ($logs->save()){
					$retData['success'] = true;
				}
			}
		}
		echo json_encode($retData);
	}
	
	public function actionGetproject()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$projectId = explode(":", $data['projectId']);
			$projectId = $projectId[0];
			
			$project = Project::findOne($projectId);
			
			$isDelete = $this->isDelete($project);
			if(!$isDelete){
				$project->createBy = User::getUserName((string)$project->createBy);
				$project->createDate = DateTime::MongoDateToDateCreate($project->createDate["sec"]);
				$project->startDate = DateTime::MongoDateToDate($project->startDate["sec"]);
				$project->endDate = DateTime::MongoDateToDate($project->endDate["sec"]);
				$project->departmentId = Department::getDepartmentNameByDepCode($project->departmentId);
				$project->category = Category::getCategoryName($project->category);
				$project->status =	"tets";
			}
			
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'project' => $project,
					'isDelete' => $isDelete
			];
		}
	}

	public function isDelete($model){
		$isDelete = false;
		if($model == null){
			$isDelete = true;
		}
		return $isDelete;
	}

	public function ChangeActiveFlagTask($projectId, $activeFlag){
		$listTask = Task::findAll(["projectId"=>new ObjectID($projectId)]);
		foreach ($listTask as $model){
			$modelTask = Task::findOne($model->_id);
			$modelTask->activeFlag = $activeFlag;
			$modelTask->save();
		}
	}

	public function deleteTaskInProject($projectId){
		if(!empty($projectId)){
			$model = new Task();
			$model->deleteAll(["projectId"=>new ObjectID($projectId)]);
		}
	}
}