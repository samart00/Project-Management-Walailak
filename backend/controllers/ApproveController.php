<?php

namespace backend\controllers;

use Yii;
use backend\models\Task;
use backend\models\Comment;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use backend\models\Project;
use backend\models\Department;
use common\libs\DateTime;
use common\models\User;
use \MongoDate;
use yii\helpers\ArrayHelper;
use MongoDB\BSON\ObjectID;
use common\libs\Status;
use yii\data\Pagination;
use yii\data\Sort;
use yii\web\UploadedFile;
use common\libs\ActiveFlag;
use common\libs\RoleInProject;

/**
 * ApproveController implements the CRUD actions for Task model.
 */
class ApproveController extends Controller
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
	 * Lists all Task models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$request = Yii::$app->request;
		$department = $request->post('department',null);
		$taskName = $request->post('taskName',null);
		$length = (int)$request->post('per-page',null);
		$projectName = $request->post('projectName',$request->get('projectName',null));
		
		if(empty($department)){
			$department = $request->get('department',null);
		}
		
		if(empty($taskName)){
			$taskName = $request->get('taskName',null);
		}
		
		if(empty($length)){
			$defaultLength = 10;
			$length = $request->get('per-page',$defaultLength);
		}
		
		$projectName = trim($projectName);
		$taskName = trim($taskName);
		 
		$dataTablesLength = [
				10 => "10",
				20 => "20",
				30 => "30",
				50 => "50",
		];
		 
		$page = $request->get('page',1);
		$sort = $request->get('sort','categoryName');
		 
		$dataTablesSort = new Sort([
				'defaultOrder' => [
						'taskName' => SORT_ASC
				],
				'attributes' => [
						'taskName' => [
							'asc' => ['taskName' => SORT_ASC],
							'desc' => ['taskName' => SORT_DESC],
							'default' => SORT_DESC,
							'label' => 'ชื่องาน'
						]
				]
		]);
		
		$dataTablesSort->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'department' => $department,
				'taskName' => $taskName,
				'projectName' =>$projectName,
		];
		
		$userId = Yii::$app->user->identity->_id;
    	$query = Project::find();
    	$query->andwhere(['member' => ['$elemMatch' => ['userId' => $userId,'type' => RoleInProject::PROJECT_MANAGER]]]);
    	$allprojectname = $query->all();
    	$arrProject = [""];
    	
    	$indexProject = 0;
    	if($allprojectname){
    		foreach ($allprojectname as $object){
    			$arrProject[$indexProject] = $object->projectName;
    			$indexProject++;
    		}
    	}
    	
    	if(!empty($department)){
    		$query->andWhere(['departmentId'=>$department]);
    	}
    	if(!empty($projectName)){
    		
    		$queryproject = Project::find();
    		$query->andwhere(['member' => ['$elemMatch' => ['userId' => $userId,'type' => RoleInProject::PROJECT_MANAGER]]]);
    		$queryproject->andWhere(['like',  'projectName', $projectName]);
    		$projectall = $queryproject->all();
    		
    		$idproject=[];
    		foreach($projectall as $object1){
    			$idproject[] = $object1['_id'];
    		}
    		$query->andwhere(['_id' => $idproject]);
    	}
    	$query->andWhere(['activeFlag'=>ActiveFlag::ACTIVE]);
    	$query->andWhere(['<>', 'status', Status::CANCEL]);
    	$project = $query->all();
    	
    	$query = Task::find();
    	$query->andWhere(['<>', 'projectId', null]);
    	$query->andWhere(['status' => Status::WAIT_APPROVE_TASK]);
    	
    	$projectId = [];
    	foreach ($project as $obj){
    		$projectId[] = $obj->_id;
    	}
    	$query->andWhere(['in','projectId', $projectId]);
    	
    	if(!empty($taskName)){
    		$query->andWhere(['like','taskName',$taskName]);
    	}
    	
    	$pagination = new Pagination([
    			'totalCount' => $query->count(),
    			'defaultPageSize' => 10
    	]);
    	 
    	$pagination->setPageSize($length);
    	$query->offset($pagination->offset);
    	$query->orderBy($dataTablesSort->orders);
    	$query->limit($pagination->limit);
    	
    	$model = $query->all();
    	$pagination->params = [
    			'page'=> $pagination->page,
    			'sort'=>$sort,
    			'per-page'=>$length,
    			'department' => $department,
				'taskName' => $taskName,
    			'projectName' =>$projectName,
    	];
		
		$departmentModel = new Department();
		$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
		
		return $this->render('index', [
				'model' => $model,
				'listDepartment' => $listDepartment,
				'department' => $department,
				'taskName' => $taskName,
				"pagination" => $pagination,
				"dataTablesLength" => $dataTablesLength,
				"length" => $length,
				"sort" => $sort,
				"dataTablesSort" => $dataTablesSort,
				"page" => $page,
				"arrProject" => $arrProject,
				"projectName" => $projectName
		]);
	}
	
	/**
	 * Displays a single Task model.
	 * @param integer $_id
	 * @return mixed
	 */
	public function actionView()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$taskId = explode(":", $data['taskId']);
			$taskId = $taskId[0];
		
			$retData['success'] = false;
			$retData['isInactive'] = false;
			$retData['isCancel'] = false;
			$retData['isProject'] = false;
			$isDelete = $this->isDelete($taskId);
			$taskData = [];
			
			if(!$isDelete){
				$task = Task::findOne($taskId);
				$modelProject = Project::findOne($task->projectId);
				if($this->isInactive($modelProject)){
					$retData['isInactive'] = true;
				}else if($this->isCancel($modelProject)){
					$retData['isCancel'] = true;
				}else if($modelProject == null){
						$retData['isProject'] = true;
				
				}else{
					$retData['taskId'] = (string)$task->_id;
					$retData['taskName'] = $task->taskName;
					$retData['description'] = $task->description;
					
					$retData['project'] = Project::getProjectName($task->projectId);
					
					$retData['startDate'] = DateTime::MongoDateToDate($task->startDate["sec"]);
					$retData['endDate'] = DateTime::MongoDateToDate($task->endDate["sec"]);
					
					if($task->status == Status::WAIT_APPROVE_TASK){
						$retData['status'] = "ขออนุมัติปิดงาน";
					}
					
					
					$retData['createBy'] = User::getUserName((string)$task->createBy);
					
					
					$retData['createDate'] = DateTime::MongoDateToDateCreate($task->createDate["sec"]);
					//     	$retData['assignee'] = $task->assignee;
					$retData['askforapproveDate'] = DateTime::MongoDateToDate($task->askforapproveDate["sec"]);
					$users = [];
					
					$i=0;
					if($task['assignee']){
						foreach ($task->assignee as $obj2) {
							$users[$i]['userid'] = $obj2['userId'];
							$i=$i+1;
						}
						$nummberUser = sizeof($users);;
						for($j=0;$j<$nummberUser;$j++){
							$users[$j]['userid'] = User::getUserName((string)$users[$j]['userid']);
						}}
					$retData['success'] = true;
					
					$conditions = [];
					$query = Comment::find();
					$conditions['refId'] = new ObjectID($taskId);
					$query->where($conditions);
					$query->orderBy(['createTime'=>SORT_ASC]);
					$comment = $query->all();
					
					$userId = [];
					$index = 0;
					$arrComment =[];
					$pathAvartar = [];
					foreach ($comment as $obj){
						$userId[$index] = (string)$obj->commentBy;
						$pathAvartar[$index] = User::getPhotoUserViewer($userId[$index]);
						$arrComment[$index]['comment'] = $obj->comment;
						$arrComment[$index]['images'] = $obj->images;
						$arrComment[$index]['filename'] = $obj->filename;
						$arrComment[$index]['allfiles'] = $obj->allfiles;
						$arrComment[$index]['commentBy'] = User::getUserName((string)$obj->commentBy);
						$arrComment[$index]['createTime'] = DateTime::MongoDateToDate($obj->createTime["sec"]);
						$index++;
					}
					
					$retData['comment'] = $arrComment;
					$retData['users'] = $users;
					$retData['userId'] = $userId;
					$retData['pathAvartar'] = $pathAvartar;
				}
			}
			
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'isDelete' => $isDelete,
					'taskData' => $retData,
					'code' => 100,
			];
		}
	}
	
	
	public function actionSave()
	{	
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
		
		$comment = $request->post('comment', null);
		$refId = $request->post('refId', null);
		$status = (int)$request->post('status', null);
		
		$comment = trim($comment);
		
		$modelTask = Task::findOne($refId);
		$isDelete = $this->isDelete($refId);
		$retData['success'] = false;
		$retData['isDelete'] = $isDelete;
		$retData['isInactive'] = false;
		$retData['isCancel'] = false;
		$retData['isDeleteProject'] = true;
		if(!empty($status)){
			if(!$isDelete){
				$modelProject = Project::findOne($modelTask->projectId);
				if($modelProject != null){
					$retData['isDeleteProject'] = false;
					
					if($this->isInactive($modelProject)){
						$retData['isInactive'] = true;
					}else if($this->isCancel($modelProject)){
						$retData['isCancel'] = true;
					}else{
						if($status == Status::APPROVED_TASK){
								
							$modelTask->status = Status::APPROVED_TASK;
								
							$model = new Comment();
							$model->comment = $comment;
							$model->createTime = new MongoDate();
							$model->commentBy = new ObjectID($currentId);
							$model->refId = new ObjectID($refId);
								
							if($modelTask->save()){
								if(!empty($comment)){
									$model->save();
								}
								$retData['success'] = true;
							}
						}else if($status == Status::REJECTED_TASK){
							$retData['isEmpty'] = true;
							if(!empty($comment)){
								$modelTask->status = Status::REJECTED_TASK;
									
								$model = new Comment();
								$model->comment = $comment;
								$model->createTime = new MongoDate();
								$model->commentBy = new ObjectID($currentId);
								$model->refId = new ObjectID($refId);
									
								if($modelTask->save()){
									if($model->save()){
										$retData['success'] = true;
										$retData['isEmpty'] = false;
									}
								}
							}
						}
					}
				}
			}
		}
		echo json_encode($retData);		
	}
		
	public function isDelete($taskId){
		$isDelete = true;
		$model = Task::findOne($taskId);
	
		if($model != null){
			$isDelete = false;
		}
		
		return $isDelete;
	}
	
	public function actionUploadimages()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$currentId = Yii::$app->user->identity->_id;
		
		$projectId = $request->post('projectUploadImages', null);
	
		if (Yii::$app->request->isPost) {
			$model = new Comment();
			$model->images = UploadedFile::getInstances($model, 'images');
			$result = $model->upload($projectId);
			if ($result != false) {
				// file is uploaded successfully
				return json_encode($result);
			}else{
				return;
			}
		}
	}
	
	public function actionUploadfiles()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		// 		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
	
		$projectId = $request->post('projectUploadFiles', null);
	
		if (Yii::$app->request->isPost) {
			$model = new Comment();
			$model->allfiles = UploadedFile::getInstances($model, 'allfiles');
			$result = $model->uploadfiles($projectId);
			if ($result != false) {
				// file is uploaded successfully
				return json_encode($result) ;
			}else{
				return;
			}
		}
	}
	
	/**
	 * Finds the Task model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $_id
	 * @return Task the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = Task::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
	
	public function isCancel($model){
		$isCancel = false;
		if($model->status == Status::CANCEL){
			$isCancel = true;
		}
		return $isCancel;
	}
	
	public function isInactive($model){
		$isInactive = false;
		if($model->activeFlag == ActiveFlag::INACTIVE){
			$isInactive = true;
		}
		return $isInactive;
	}
	public function actionDownload()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
			
		$file = $request->get('file', null);
		$path = Comment::getUploadPath();
	
		Yii::$app->response->sendFile($path.$file);
	}
	
	public function actionGetcomment()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$taskId = explode(":", $data['taskId']);
			$taskId = $taskId[0];
	
			$model = Task::findOne($taskId);
			$currentId = Yii::$app->user->identity->_id;
	
			$conditions = [];
			$conditions['refId'] = new ObjectID($taskId);
			
			$query = Comment::find();
			$query->where($conditions);
			$query->orderBy(['createTime'=>SORT_ASC]);
			$comment = $query->all();
	
			$isDelete = $this->isDelete($taskId);
			$retData['success'] = false;
			$pathAvartar = null;
			$commentBy = null;
	
			if(!$isDelete){
				$nummberComment = sizeof($comment);
				$commentBy=[];
				$pathAvartar = [];
				for($i=0;$i<$nummberComment;$i++){
					$commentBy[$i] = (string)$comment[$i]->commentBy;
	
					$pathAvartar[$i] = User::getPhotoUserViewer($commentBy[$i]);
					$comment[$i]->commentBy = User::getUserName($comment[$i]->commentBy);
					$comment[$i]->createTime = DateTime::MongoDateToDateCreate($comment[$i]->createTime["sec"]);
				}
			}
	
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'comment' => $comment,
					'commentBy' => $commentBy,
					'pathAvartar' => $pathAvartar,
					'code' => 100,
					'isDelete' => $isDelete,
			];
		}
	}
}
