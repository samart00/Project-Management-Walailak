<?php

namespace backend\controllers;

use Yii;
use backend\models\Task;
use backend\models\Project;
use backend\models\Comment;
use backend\models\Log;
use common\models\User;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\libs\Status;
use common\libs\ActiveFlag;
use \MongoDate;
use MongoDB\BSON\ObjectID;
use common\libs\DateTime;
use yii\helpers\ArrayHelper;
use common\libs\Permission;
use yii\base\Object;
use backend\models\Department;
use common\libs\PermissionInProject;
use yii\data\Pagination;
use yii\data\Sort;
use yii\web\UploadedFile;
use common\libs\CheckMember;
use common\libs\TypeTask;
use yii\filters\AccessControl;
/**
 * TaskController implements the CRUD actions for Task model.
 */
class TaskController extends Controller
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
		$projectId = $request->post('projectId',$request->get('projectId',null));
		$name = $request->post('name', null);
		$status = $request->post('status', null);
		$username = $request->post('username', null);
		$userId = Yii::$app->user->identity->_id;
		$userlist = User::find();
		$name = trim($name);
		$username = trim($username);
		$baseUrl = \Yii::getAlias ( '@web' );
		$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
		if($projectId == null){
			return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
		}else{
			if($projects == null){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
			}else{
				if($projects->status == Status::CANCEL){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
				}else if($projects->activeFlag == ActiveFlag::INACTIVE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
				}
			}
		}
		$permission = PermissionInProject::TASK_PROJECT;
		$havePermission = PermissionInProject::havePermissionInProject($permission, $projects->member);
		if(!$havePermission){
			PermissionInProject::haveNotPermission();
		}
		$permission = PermissionInProject::CREATE_TASK_PROJECT;
		$isCreate = PermissionInProject::havePermissionInProject($permission, $projects->member);	
		$query = Task::find();
		$query2 = Task::find();
		$query3 = Task::find();
			$query->where(["projectId" => new ObjectID($projectId)]);
			$query2->where(["projectId" => new ObjectID($projectId)]);
			$query3->where(["projectId" => new ObjectID($projectId)]);	
		if(!empty($name)){
			$query->andWhere(['like', "taskName",$name]);
			$query2->andWhere(['like', "taskName", $name]);
			$query3->andWhere(['like', "taskName", $name]);
			$query->orWhere(['like', "tag", $name]);
			$query2->orWhere(['like', "tag", $name]);
			$query3->orWhere(['like', "tag", $name]);
			$query->andWhere(["projectId" => new ObjectID($projectId)]);
			$query2->andWhere(["projectId" => new ObjectID($projectId)]);
			$query3->andWhere(["projectId" => new ObjectID($projectId)]);
		}
		if(!empty($username)){
			$employeeName = explode(" ", $name);
			$size = sizeof($employeeName);
			$userlist = User::find();
			if($size > 1){
				$userlist->andwhere(['like',  'nameTh', $employeeName[0]]);
				$userlist->andwhere(['like',  'sernameTh', $employeeName[1]]);
			}else{
				$userlist->andwhere(['like',  'nameTh', $employeeName[0]]);
				$userlist->andwhere(['like',  'sernameTh', $employeeName[0]]);
			}
			$arrusersid=[];
			$userlist= $userlist->all();
			foreach ($userlist as $obj){
				$arrusersid[]=  $obj->_id;
			}
			$query->andwhere(["assignee.userId" => $arrusersid]);
			$query2->andwhere(["assignee.userId" => $arrusersid]);
			$query3->andwhere(["assignee.userId" => $arrusersid]);
		}
		if(!empty($status)){
			if($status== (int)Status::PREPARE_TASK){
				$query->andwhere(["status" => (int)Status::PREPARE_TASK]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::NEW_TASK){
				$query->andwhere(["status" => (int)Status::NEW_TASK]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::OPEN_TASK){
				$query->andwhere(["status" => (int)Status::OPEN_TASK]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::DOING_TASK){
				$query->andwhere(["status" => 0]);
				$query2->andwhere(["status" => (int)Status::DOING_TASK]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::WAIT_APPROVE_TASK){
				$query->andwhere(["status" => 0]);
				$query2->andwhere(["status" => (int)Status::WAIT_APPROVE_TASK]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::APPROVED_TASK){
				$query->andwhere(["status" => 0]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => (int)Status::APPROVED_TASK]);
			}
			elseif($status== (int)Status::REJECTED_TASK){
				$query->andwhere(["status" => (int)Status::REJECTED_TASK]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => 0]);
			}else{
				$query->andwhere(["status" => 0]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => 0]);
			}
		}else{
			$query->andwhere(["status" => [(int)Status::PREPARE_TASK,(int)Status::NEW_TASK,(int)Status::OPEN_TASK,(int)Status::REJECTED_TASK]]);
			$query2->andwhere(["status" => [(int)Status::DOING_TASK,(int)Status::WAIT_APPROVE_TASK]]);
			$query3->andwhere(["status" => (int)Status::APPROVED_TASK]);
		}	 
		$query->orderBy(['status'=>SORT_ASC,'endDate'=>SORT_ASC]);
		$query2->orderBy(['status'=>SORT_ASC,'endDate'=>SORT_ASC]);
		$query3->addOrderBy(['approveDate'=>SORT_ASC]);
		$todo = $query->all();;
		$doing = $query2->all();
		$done = $query3->all();	 
		$user = Project::findOne(["_id" => $projectId]);
		$arrUser = [];
		    	foreach ($user->member as $obj) {
		    		$arrUser[] =  User::getUserName((string)$obj['userId']);	
		    		}
		$startDate = $projects->startDate['sec'];
		$endDate = $projects->endDate['sec'];
		$statusproject = $projects->status;
		$minDate = DateTime::MongoDateToDateNotTime($startDate);
		$minTime = DateTime::MongoDateToDateReturnTime($startDate);
		$maxDate = DateTime::MongoDateToDateNotTime($endDate);
		$maxTime = DateTime::MongoDateToDateReturnTime($endDate);
		$memberIdProject = Project::findOne(["_id" => new ObjectID($projectId)]);
		$arrMemberInProject= [];
		foreach ($memberIdProject->member as $obj){			
			if($obj['activeFlag'] == ActiveFlag::ACTIVE){
				$arrMemberInProject[] =  (string)$obj['userId'];
			}
		}
		$modelcomment = new Comment();	
		return $this->render('index', [
				"name" => $name,
				"todo" => $todo,
				"doing" => $doing,
				"done" => $done,
				"projectId" => $projectId,
				"arrUser" => $arrUser,
				"projects" =>$projects,
				"minDate" => $minDate,
				"minTime" => $minTime,
				"maxDate" => $maxDate,
				"maxTime" => $maxTime,
				'status' => $status,
				'username' =>$username,
				'isCreate' => $isCreate,
				'modelcomment' => $modelcomment,
				'statusproject' => $statusproject,
				'arrMemberInProject' => $arrMemberInProject			 
		]);
	}
	public function actionView()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$taskId = explode(":", $data['taskId']);
			$taskId = $taskId[0];
			$projectId = explode(":", $data['projectId']);
			$projectId = $projectId[0];
			$currentId = Yii::$app->user->identity->_id;
			$changestatus= null;
			$status= Status::NEW_TASK;
			$retData=[];
			$retData['changestatus'] = false;
			$isDelete = false;
			$success = false;
			$isProject = false;
			$isCancel = false;
			$isClose = false;
			$projectmanager = false;
			$baseUrl = \Yii::getAlias ( '@web' );
			if($projectId == null){
			$checkprojectId = Task::findOne(["_id"=>new ObjectID($taskId)]);
				if($checkprojectId['projectId'] != null){
					$projectId = $checkprojectId->projectId;			
				}
			}
			if($projectId != null){
				$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
				$projectmanager = CheckMember::CheckProjectManager($projectId);
				if($projects == null){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$isProject = true;
				}else{
					if($projects['status'] == Status::CANCEL){
						Yii::$app->getSession()->setFlash('alert',[
								'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
								'options'=>['class'=>'alert-danger']
						]);
						$isCancel = true;
					}else if($projects->activeFlag == ActiveFlag::INACTIVE){
						Yii::$app->getSession()->setFlash('alert',[
								'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
								'options'=>['class'=>'alert-danger']
						]);
						$isClose = true;
					}
					
				}
			}
			if($isClose == true || $isCancel == true || $isProject == true){
				$success = false;
			}else{	
				$isDelete = Task::findOne(["_id"=>new ObjectID($taskId)]);
				$taskData = [];
					if($isDelete == null){
						$isDelete = true;
					}
					else{
						$success = true;
						$isDelete= false;
						$user = User::find()->all();
						$retData['isDuplicate'] = false;
						$model = Task::findOne(["_id"=>new ObjectID($taskId)]);
						$taskData2 = $model;
						if($taskData2['assignee']){
							if((int)$taskData2['status'] == (int)$status){
								$changestatus = $this->changOpentask($taskId,$currentId);
								$retData['changestatus'] = true;
							}
						}
						$model2 = Task::findOne(["_id"=>new ObjectID($taskId)]);
						$taskData = $model2;
						$retData['_id'] = (string)$taskData->_id;
						$retData['taskName'] = $taskData->taskName;
						$retData['tag'] = $taskData->tag;
						$retData['description'] = $taskData->description;	
						if((string)$taskData->createBy==(string)$currentId || $projectmanager == true){
							$retData['checkCreate'] = true;
						}
						else{
							$retData['checkCreate'] = false;
						}
						if($taskData['projectId'] == null){
							$retData['checkproject'] = true;
						}
						else{
							$retData['checkproject'] = false;
							$retData['projectId'] = (string)$taskData->projectId;
						}
						$taskData->createBy = User::getUserName((string)$taskData->createBy);
						$taskData->startDate = DateTime::MongoDateToDate($taskData->startDate["sec"]);
						$taskData->endDate = DateTime::MongoDateToDate($taskData->endDate["sec"]);
						$retData['createBy'] = (string)$taskData->createBy;
						$retData['startDate'] = $taskData->startDate;
						$retData['endDate'] = $taskData->endDate;
						$retData['status'] = Status::$arrTaskStatus[(int)$taskData->status];
						$retData['status2'] = (int)$taskData->status;
						$users = [];	 
						$i=0;
						if($taskData['assignee']){
							foreach ($taskData->assignee as $obj2) {
								$users[$i]['userid'] = $obj2['userId'];
								$i=$i+1;
							}
							$nummberUser = sizeof($users);;
							for($j=0;$j<$nummberUser;$j++){
								$users[$j]['userid'] = User::getUserName((string)$users[$j]['userid']);
							}
						}
						$conditions=[];
						$model3=null;
						$query = Comment::find();
						$conditions['refId'] = new ObjectID($taskId);
						$query->where($conditions);
						$query->orderBy(['createTime'=>SORT_ASC]);
						$model3 = $query->all();
						$comment = null;
						$comment = $model3;
						$nummberComment = sizeof($comment);
						$commentBy=[];
						$i=0;
						$comment2 =[];
						$pathAvartar = [];
						foreach ($comment as $obj){
							$commentBy[$i] = (string)$obj->commentBy;
							$pathAvartar[$i] = User::getPhotoUserViewer($commentBy[$i]);
							$comment2[$i]['comment'] = $obj->comment;
							$comment2[$i]['images'] = $obj->images;
							$comment2[$i]['filename'] = $obj->filename;
							$comment2[$i]['allfiles'] = $obj->allfiles;
							$comment2[$i]['commentBy'] = User::getUserName((string)$obj->commentBy);
							$comment2[$i]['createTime'] = DateTime::MongoDateToDateCreate($obj->createTime["sec"]);
							$i++;
						}
						$member = Log::find('memberId');
						$member->where(["refId" => new ObjectID($taskId)]);
						$memberId = $member->all();	 
						$dataLog = Log::find();
						$dataLog->where(["refId" => new ObjectID($taskId)]);
						$dataLog->orderBy(['editDate' => SORT_DESC]);
						$log = $dataLog->all();
						$log2 = null;
						$log2 = $log;
						$userId = [];
						$i = 0;
						$nummberLog = sizeof($log);
						$log3 = [];
						foreach ($log2 as $obj){
							$userId[$i] = (string)$obj->userId;
							$log3[$i]['userId'] = User::getUserName((string)$obj->userId);
							$log3[$i]['editDate'] = DateTime::MongoDateToDateCreate($log[$i]->editDate["sec"]);
							$log3[$i]['action'] = $obj->action;
							$log3[$i]['_id'] = (string)$obj->_id;
							$i++;
						}
						$retData['comment'] = $comment2;
						$retData['userId'] = $userId;
						$retData['commentBy'] = $commentBy;
						$retData['log2'] = $log3;
						$retData['users'] = $users;
						$retData['changestatus'] = $changestatus;
						$retData['pathAvartar'] = $pathAvartar;	
						$memberintask = Task::findOne(["_id" => $taskId]);
						$arrMemberInTask=[];
						if($memberintask['assignee']){
							foreach ($memberintask->assignee as $obj){
								$arrMemberInTask[] =  (string)$obj['userId'];
							}
						}
						$retData['arrMemberInTask'] = $arrMemberInTask;		
				}
			}
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
				'isDelete' => $isDelete,
				'success' => $success,
				'isProject' => $isProject,
				'isCancel' => $isCancel,
				'isClose' => $isClose,
				'taskData' => $retData,
				'code' => 100,
			];
		}
	}
	public function actionGetedittask()
	{
		Permission::havePermission(Permission::EDIT_TEAM);
		$request = \Yii::$app->request;
		$data = Yii::$app->request->post();
		$taskId = $request->post('taskId', null);
		$isDelete = $this->isDelete($taskId);
		$retData['success'] = false;
		if($isDelete){
			$retData['isDelete'] = true;
		}else{
			$task = Task::findOne(["_id"=>new ObjectID($taskId)]);
			$retData['taskId'] = (string)$task->_id;
			$retData['taskName'] = $task->taskName;
			$retData['tag'] = $task->tag;
			$retData['description'] = $task->description;
			$retData['startDate'] =	DateTime::MongoDateToDateReturnDate($task->startDate["sec"]);
			$retData['endDate'] = DateTime::MongoDateToDateReturnDate($task->endDate["sec"]);
			$retData['timestart'] = DateTime::MongoDateToDateReturnTime($task->startDate["sec"]);
			$retData['timeend'] = DateTime::MongoDateToDateReturnTime($task->endDate["sec"]);
			$retData['Allday'] = $task->Allday;
			$retData['success'] = true;
			$retData['isDelete'] = false;
		}
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;	 
		echo json_encode($retData);
	}
	public function actionGetusertask()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$memberId = explode(":", $data['memberId']);
			$memberId = $memberId[0];
			$projectId = explode(":", $data['projectId']);
			$projectId = $projectId[0];		 
			$retData=[];
			$isDelete = false;
			$taskData = [];
			if($isDelete){
				$isDelete = true;
			}else{
				$query = Task::find();
				$query2 = Task::find();
				$query3 = Task::find();
				$query->where(['assignee.userId'=>new ObjectID($memberId)]);
				$query2->where(['assignee.userId'=>new ObjectID($memberId)]);
				$query3->where(['assignee.userId'=>new ObjectID($memberId)]); 
				$alltask = $query->andwhere(['not','projectId', null]);
				$alltaskinproject = $query2->andwhere(['projectId'=> new ObjectID($projectId)]);
				$query3->andwhere(['not','projectId', null]);
				$alltaskotherproject = $query3->andwhere(['not','projectId', new ObjectID($projectId)]);		 
				$isDelete= false;
				$retData['isDuplicate'] = false;
				$retData['userName'] = User::getUserName((string)$memberId);
				$retData['alltask'] = $alltask->count();
				$retData['alltaskinproject'] = $alltaskinproject->count();
				$retData['alltaskotherproject'] = $alltaskotherproject->count();	 

			}
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'isDelete' => $isDelete,
					'taskData' => $retData,
					'code' => 100,
			];
		}
	}
	public function actionGetusertaskprivate()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$memberId = explode(":", $data['memberId']);
			$memberId = $memberId[0];
			$retData=[];
			$isDelete = false;
			$taskData = [];
			if($isDelete){
				$isDelete = true;
			}
			else{
				$query = Task::find();
				$query2 = Task::find();
				$query3 = Task::find();
				$query->where(['assignee.userId'=>new ObjectID($memberId)]);
				$query2->where(['assignee.userId'=>new ObjectID($memberId)]);
				$query3->where(['assignee.userId'=>new ObjectID($memberId)]);
				$query->andwhere(['not','status', Status::COMPLETED_TASK]);
				$query2->andwhere(['not','status', Status::COMPLETED_TASK]);
				$query3->andwhere(['not','status', Status::COMPLETED_TASK]);
				$alltask = $query;
				$alltaskproject = $query2->andwhere(['not','projectId', null]);
				$alltaskprivate = $query3->andwhere(['projectId'=> null]);
				$isDelete= false;
				$retData['isDuplicate'] = false;
				$retData['userName'] = User::getUserName((string)$memberId);
				$retData['alltask'] = $alltask->count();
				$retData['alltaskproject'] = $alltaskproject->count();
				$retData['alltaskprivate'] = $alltaskprivate->count();
			}
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'isDelete' => $isDelete,
					'taskData' => $retData,
					'code' => 100,
			];
		}
	}
	public function actionSavetask()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;	 
		$projectId = $request->post('projectId', null);
		$taskName = trim($request->post('taskName', null));
		$tag = trim($request->post('tag', null));
		$description = trim($request->post('description', null));
		$startdate = $request->post('startdate', null);
		$enddate = $request->post('enddate', null);
		$allday = $request->post('allday', null);
		$userId = Yii::$app->user->identity->_id;
		$retData['success'] = false;
		$retData['isProject'] = false;
		$retData['isCancel'] = false;
		$retData['isClose'] = false;
		$retData['isDuplicate'] = false;
		$retData['isDone'] = false;
		$baseUrl = \Yii::getAlias ( '@web' );
		if($projectId != null){
		$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
		$isDuplicate = $this->isDuplicate($taskName,$projectId,null);
			if($projects == null){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isProject'] = true;
			}else{
				if($projects['status'] == Status::CANCEL){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isCancel'] = true;
				}else if($projects->activeFlag == ActiveFlag::INACTIVE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isClose'] = true;
				}
				else if($projects->activeFlag == Status::CLOSE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isDone'] = true;
				}
			}
		}else{
			$isDuplicate = $this->isDuplicatePrivate($taskName,$userId,null);
		}
			if($retData['isClose'] == true || $retData['isCancel'] == true || $retData['isProject'] == true || $retData['isDone'] == true){
				$retData['success'] = false;
			}
			else{
			if($isDuplicate){
				$retData['isDuplicate'] = true;
			}else{			
				$retData['isDuplicate'] = false;
				$task = null;		
				if ($task == null){
					$task = new Task();
					$task->taskName = $taskName;
					$task->createDate = new MongoDate();
					$task->description = $description;
					$task->createBy = $currentId;
					if(!empty($projectId)){
						$permission = PermissionInProject::CREATE_TASK_PROJECT;
						$havePermission = PermissionInProject::havePermissionInProject($permission, $projects->member);
						if(!$havePermission){
							PermissionInProject::haveNotPermission();
						}
						$task->projectId = new ObjectID($projectId);
						$task->assignee = [];
						$task->status = Status::PREPARE_TASK;
					}else{	
						Permission::havePermission(Permission::CREATE_TASK);
						$taskMember=[];
						$taskMember[0]['userId'] = $currentId;
						$task->assignee = $taskMember;
						$task->status = Status::NEW_TASK;
					}
					$task->startDate = new MongoDate(strtotime($startdate));
					$task->endDate = new MongoDate(strtotime($enddate));
					$task->progress = 0;
					$task->Allday= $allday;
					$task->tag= $tag;
				}
				if($task->save()){
					$retData['message'] = "บันทึกสำเร็จ";
					$retData['success'] = true;
				}else{
					$retData['message'] = "บันทึกผิดพลาด";
					$retData['success'] = false;
				}
			}
		}
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		echo json_encode($retData);
	}
	public function actionEdittask()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
		$projectId = $request->post('projectId', null);
		$taskName = trim($request->post('taskName', null));
		$description = trim($request->post('description', null));
		$startdate = $request->post('startdate', null);
		$enddate = $request->post('enddate', null);
		$taskId = $request->post('taskId', null);
		$tag= $request->post('tag', null);
		$userId = Yii::$app->user->identity->_id;
		$retData['success'] = false;
		$retData['isProject'] = false;
		$retData['isCancel'] = false;
		$retData['isClose'] = false;
		$retData['isDuplicate'] = false;
		$retData['isDelete'] = false;
		$retData['isDone'] = false;
		$baseUrl = \Yii::getAlias ( '@web' );
		if($projectId != null){
			$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
			$isDuplicate = $this->isDuplicate($taskName,$projectId,null);
			if($projects == null){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isProject'] = true;
			}else{
				if($projects['status'] == Status::CANCEL){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isCancel'] = true;
				}else if($projects->activeFlag == ActiveFlag::INACTIVE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isClose'] = true;
				}else if($projects->activeFlag == Status::CLOSE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isDone'] = true;
				}
			}
		}else{
			$isDuplicate = $this->isDuplicatePrivate($taskName,$userId,null);
		}
		if($retData['isClose'] == true || $retData['isCancel'] == true || $retData['isProject'] == true || $retData['isDone'] == true){
			$retData['success'] = false;
		}
		else{
			$retData['success'] = false;
			$isDelete = Task::findOne(["_id"=>new ObjectID($taskId)]);
			if($isDelete == null){
				$retData['$isDelete'] = true;
			}else{
				$isDuplicate = $this->isDuplicate($taskName,$projectId,$taskId);
				if($isDuplicate){
					$retData['isDuplicate'] = true;	
				}else{
					$logs = new Log();
					$templog = [];
					$oldData = Task::findOne($taskId);
					$templog['_id'] = $oldData->_id;
					$templog['assignee'] = $oldData->assignee;
					$templog['createBy'] = $oldData->createBy;
					$templog['createDate'] = $oldData->createDate;
					$templog['description'] = $oldData->description;
					$templog['endDate'] = $oldData->endDate;
					$templog['progress'] = $oldData->progress;
					$templog['projectId'] = $oldData->projectId;
					$templog['startDate'] = $oldData->startDate;
					$templog['status'] = $oldData->status;
					$templog['taskName'] = $oldData->taskName;
					$retData['isDuplicate'] = false;
					$task = null;			
					if ($task == null){
						$task = Task::findOne($taskId);
						$task->taskName = $taskName;
						$task->tag = $tag;
						$task->description = $description;
						$newData = [];
						$newData['_id'] = $task->_id;
						$newData['assignee'] = $task->assignee;
						$newData['createBy'] = $task->createBy;
						$newData['createDate'] = $task->createDate;
						$newData['description'] = $task->description;
						$newData['endDate'] = $task->endDate;
						$newData['progress'] = $task->progress;
						$newData['projectId'] = $task->projectId;
						$newData['startDate'] = $task->startDate;
						$newData['status'] = $task->status;
						$newData['taskName'] = $task->taskName;
						$task->startDate = new MongoDate(strtotime($startdate));
						$task->endDate = new MongoDate(strtotime($enddate));
					}
					if($task->save()){
						$logs->oldData = $templog;
						$logs->newData = $newData;
						$logs->userId = $currentId;
						$logs->refId = new ObjectID($taskId);
						$logs->editDate = new MongoDate();
						$logs->action = 'แก้ไขงาน';
						$logs->memberId = '';
						if ($logs->save()){
							$message = "บันทึกสำเร็จ";
							$retData['success'] = true;
						}
					}else{
						$retData['message'] = "บันทึกผิดพลาด";
						$retData['success'] = false;
					}
				}
			}
		}
		echo json_encode($retData);
	}
	public function actionDelete()
	{
		Permission::havePermission(Permission::DELETE_TEAM);
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$taskId = $request->post('taskId', null);
		$projectId = $request->post('projectId', null);
		$currentId = Yii::$app->user->identity->_id;
		$isDelete= Task::findOne(["_id"=>new ObjectID($taskId)]);
		$retData['success'] = false;		
		$retData['isProject'] = false;
		$retData['isCancel'] = false;
		$retData['isClose'] = false;
		$retData['isDelete'] = false;
		$retData['isDone'] = false;
		$baseUrl = \Yii::getAlias ( '@web' );
		if($projectId != null){
			$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
			if($projects == null){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isProject'] = true;
			}else{
				if($projects['status'] == Status::CANCEL){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isCancel'] = true;
				}else if($projects['activeFlag'] == ActiveFlag::INACTIVE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isClose'] = true;
				}else if($projects->activeFlag == Status::CLOSE){
						Yii::$app->getSession()->setFlash('alert',[
								'body'=>'โครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว',
								'options'=>['class'=>'alert-danger']
						]);
						$retData['isDone'] = true;
					}
			}
		}
		if($retData['isClose'] == true || $retData['isCancel'] == true || $retData['isProject'] == true || $retData['isDone'] == true){
			$retData['success'] = false;
		}else{
			if($isDelete == null){
				$retData['isDelete'] = true;
			}else{
				$logs = new Log();
				$templog = [];
				$oldData = Task::findOne($taskId);
				$templog['_id'] = $oldData->_id;
				$templog['assignee'] = $oldData->assignee;
				$templog['createBy'] = $oldData->createBy;
				$templog['createDate'] = $oldData->createDate;
				$templog['description'] = $oldData->description;
				$templog['endDate'] = $oldData->endDate;
				$templog['progress'] = $oldData->progress;
				$templog['projectId'] = $oldData->projectId;
				$templog['startDate'] = $oldData->startDate;
				$templog['status'] = $oldData->status;
				$templog['taskName'] = $oldData->taskName;
				$model = Task::findOne(["_id"=>new ObjectID($taskId)]);
				if($model->progress == 0 || (int)$model->status == (int)Status::COMPLETED_TASK){
					if($model->delete()){
						$logs->oldData = $templog;
						$logs->newData = '';
						$logs->userId = $currentId;
						$logs->refId = new ObjectID($taskId);
						$logs->editDate = new MongoDate();
						$logs->action = 'ลบงาน';
						$logs->memberId = '';
						if ($logs->save()){
							$message = true;
							$retData['success'] = true;
						}
					}else{
						$message = false;
						$retData['success'] = false;
					}
				}else{
	
					$retData['success'] = false;
					$retData['inProgress'] = true;
				}
			}
		}
		echo json_encode($retData);
	} 
	public function actionChangestatus()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
		$taskId = $request->post('taskId', null);
		$status = $request->post('status', null);
		$projectId = $request->post('projectId', null);
		$comment = $request->post('comment', null);
		$isDelete = null;
		$isDelete = Task::findOne(["_id"=>new ObjectID($taskId)]);
		$retData['success'] = false;	
		$retData['isProject'] = false;
		$retData['isCancel'] = false;
		$retData['isClose'] = false;
		$retData['isDelete'] = false;
		$retData['isDone'] = false;
		$retData['isWait']= false;
		$baseUrl = \Yii::getAlias ( '@web' );	
		if($projectId != null){
			$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
			if($projects == null){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isProject'] = true;
			}else{
				if($projects['status'] == Status::CANCEL){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isCancel'] = true;
				}else if($projects['activeFlag'] == ActiveFlag::INACTIVE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isClose'] = true;
				}else if($projects->activeFlag == Status::CLOSE){
						Yii::$app->getSession()->setFlash('alert',[
								'body'=>'โครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว',
								'options'=>['class'=>'alert-danger']
						]);
						$retData['isDone'] = true;
				}
			}
		}
		if($retData['isClose'] == true || $retData['isCancel'] == true || $retData['isProject'] == true || $retData['isDone'] == true){
			$retData['success'] = false;
		}else{
			if ($isDelete == null){
				$retData['isDelete'] = true;
			}else{
				$task = Task::findOne(["_id"=>new ObjectID($taskId)]);
				if($status == (int)Status::OPEN_TASK){
					$task->status = Status::OPEN_TASK;
				}
				if($status == (int)Status::DOING_TASK){
					$task->status = Status::DOING_TASK;
	
				}
				if($status == (int)Status::WAIT_APPROVE_TASK){
					if($task['progress'] == 100){
						$task->status = Status::WAIT_APPROVE_TASK;
						$task->askforapproveDate= new MongoDate();
					}
					else{
						$retData['success'] = false;
						$retData['notProgress'] = true;
					}
				}
				if($status == (int)Status::APPROVED_TASK){
					if($task['status'] == Status::WAIT_APPROVE_TASK){
						$task->status = Status::APPROVED_TASK;
						if(!empty($comment)){
							$model = new Comment();
							$model->comment = $comment;
							$model->createTime = new MongoDate();
							$model->commentBy = new ObjectID($currentId);
							$model->refId = new ObjectID($taskId);
							$model->save();
						}
					}else{
						$retData['isWait']= true;
					}
				}
				if($status == (int)Status::REJECTED_TASK){
					if($task['status'] == Status::WAIT_APPROVE_TASK){
					$task->status = Status::REJECTED_TASK;
					$model = new Comment();
					$model->comment = $comment;
					$model->createTime = new MongoDate();
					$model->commentBy = new ObjectID($currentId);
					$model->refId = new ObjectID($taskId);
					$model->save();
					}else{
						$retData['isWait']= true;
					}
				}
				if($status == (int)Status::COMPLETED_TASK){
					if($task['progress'] == 100){
						$task->status = Status::COMPLETED_TASK;
					}
					else{
						$retData['success'] = false;
						$retData['notProgress'] = true;
							
					}
				}
				if($retData['isWait']== false){
					if($task->save()){
						$retData['message'] = "บันทึกสำเร็จ";
						$retData['success'] = true;
					}else{
						$retData['message'] = "บันทึกผิดพลาด";
						$retData['success'] = false;
					}
				}else{
					$retData['success'] = false;
				}
			}
		}
	
		echo json_encode($retData);
	}
	public function actionSaveprogress()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;	 
		$taskId = $request->post('taskId', null);
		$progress = $request->post('progress', null);
		$projectId = $request->post('projectId', null);			
		$isDelete = null;
		$isDelete = Task::findOne(["_id"=>new ObjectID($taskId)]);
		$retData['success'] = false;	
		$retData['isProject'] = false;
		$retData['isCancel'] = false;
		$retData['isClose'] = false;
		$retData['isDelete'] = false;
		$retData['isDone'] = false;
		$baseUrl = \Yii::getAlias ( '@web' );	
		if($projectId != null){
			$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
			if($projects == null){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isProject'] = true;
			}else{
				if($projects['status'] == Status::CANCEL){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isCancel'] = true;
				}else if($projects['activeFlag'] == ActiveFlag::INACTIVE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isClose'] = true;
				}else if($projects->activeFlag == Status::CLOSE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isDone'] = true;
				}
			}
		}
		if($retData['isClose'] == true || $retData['isCancel'] == true || $retData['isProject'] == true || $retData['isDone'] == true){
			$retData['success'] = false;
		}else{
				if ($isDelete == null){
					$retData['isDelete'] = true;
				}else{
					$task = Task::findOne(["_id"=>new ObjectID($taskId)]);
					if((int)$progress == 100 && $task->projectId == null){
						$task->progress = (int)$progress;
						$task->status = Status::COMPLETED_TASK;
					}else{
						$task->progress = (int)$progress;
						if($task['status'] == Status::WAIT_APPROVE_TASK){
							$task->status = Status::DOING_TASK;
						}
					}
					if($task->save()){
						$retData['message'] = "บันทึกสำเร็จ";
						$retData['success'] = true;
					}else{
						$retData['message'] = "บันทึกผิดพลาด";
						$retData['success'] = false;
					}
				}
		}
		echo json_encode($retData);
	}
	public function actionAssign()
	{
		$request = Yii::$app->request;
		$taskId = $request->post('taskId', null);
		$projectId =$request->post('projectId', null);
		$name = $request->post('name',null);
		$department = $request->post('department',null);
		$length = (int)$request->post('per-page',null);		 
		if(empty($taskId)){
			$taskId = $request->get('taskId',null);
		}
		if(empty($projectId)){
			$projectId = $request->get('projectId',null);
		}
		if(empty($name)){
			$name = $request->get('name',null);
		}
		if(empty($department)){
			$department = $request->get('department',null);
		}

		if(empty($length)){
			$defaultLength = 10;
			$length = $request->get('per-page',$defaultLength);
		}
		$name = trim($name);
		$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
		if($projectId == null){
			return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
		}else{
			if($projects == null){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
			}else{
				if($projects->status == Status::CANCEL){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
				}else if($projects->activeFlag == ActiveFlag::INACTIVE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
				}else if($projects->activeFlag == Status::CLOSE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/task/index', ['projectId'=>'$projectId','$csrfParam'=>'$csrf']);
				}
			}
		}
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
						'name' => SORT_ASC
				],
				'attributes' => [
						'name' => [
								'asc' => ['nameTh' => SORT_ASC, 'sernameTh' => SORT_ASC],
								'desc' => ['nameTh' => SORT_DESC, 'sernameTh' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'ชื่อผู้ใช้งานระบบ',
						],
						'department' => [
								'asc' => ['depName' => SORT_ASC],
								'desc' => ['depName' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'แผนก',
						]
				],
		]);	 
		$dataTablesSort->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'taskId' => $taskId,
				'projectId' => $projectId,
				'name' => $name,
				'department' => $department
		];	 
		$task = Task::findOne(["_id"=>new ObjectID($taskId)]);
		$arrUser = [];
		foreach ($task->assignee as $obj){
			$arrUser[] = $obj['userId'];
		} 
		$query = User::find();
		$condition = [];
		if(!empty($name)){
			$employeeName = explode(" ", $name);
			$size = sizeof($employeeName);
			if($size > 1){
				$query->andWhere(['like',  'nameTh', $employeeName[0]]);
				$query->andWhere(['like',  'sernameTh', $employeeName[1]]);
			}else{
				$query->andWhere(['like',  'nameTh', $employeeName[0]]);
				$query->orWhere(['like',  'sernameTh', $employeeName[0]]);
			}
		}
		$query->andwhere(['in','_id',$arrUser]);
		 
		if(!empty($department)){
			$condition['depCode'] = $department;
		}	 
		if(!empty($condition)){
			$query->andWhere($condition);
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
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'taskId' => $taskId,
				'projectId' => $projectId,
				'name' => $name,
				'department' => $department
		];	 
		$departmentModel = new Department();
		$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
		return $this->render('assign', [
				"listDepartment" => $listDepartment,
				"taskId"=>$taskId,
				"projectId"=>$projectId,
				"task"=>$task,
				"model"=>$model,
				"name" => $name,
				"department" => $department,
				"length" => $length,
				"pagination" => $pagination,
				"dataTablesLength" => $dataTablesLength,
				"sort" => $sort,
				"dataTablesSort" => $dataTablesSort,
				"page" => $page
		]);
	}
	public function actionAdd(){ 
		$request = Yii::$app->request;
		$taskId = $request->post('taskId', null);
		$projectId = $request->post('projectId', null);
		$name = $request->post('name',null);
		$department = $request->post('department',null);
		$length = (int)$request->post('per-page',null);
		if(empty($taskId)){
			$taskId = $request->get('taskId',null);
		}	 
		if(empty($projectId)){
			$projectId = $request->get('projectId',null);
		}
		if(empty($name)){
			$name = $request->get('name',null);
		}
		if(empty($department)){
			$department = $request->get('department',null);
		}
		if(empty($length)){
			$defaultLength = 10;
			$length = $request->get('per-page',$defaultLength);
		}
		$name = trim($name);
		$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
		if($projectId == null){
			return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
		}else{
			if($projects == null){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
			}else{
				if($projects->status == Status::CANCEL){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
				}else if($projects->activeFlag == ActiveFlag::INACTIVE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
				}else if($projects->activeFlag == Status::CLOSE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/task/index', ['projectId'=>'$projectId','$csrfParam'=>'$csrf']);
				}
			}
		}
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
						'name' => SORT_ASC
				],
				'attributes' => [
						'name' => [
								'asc' => ['nameTh' => SORT_ASC, 'sernameTh' => SORT_ASC],
								'desc' => ['nameTh' => SORT_DESC, 'sernameTh' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'ชื่อผู้ใช้งานระบบ',
						],
						'department' => [
								'asc' => ['depName' => SORT_ASC],
								'desc' => ['depName' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'แผนก',
						]
				],
		]);
		$dataTablesSort->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'taskId' => $taskId,
				'projectId' => $projectId,
				'name' => $name,
				'department' => $department
		];
		$memberintask = Task::findOne(["_id" => $taskId]);
		if($memberintask['assignee']){
			foreach ($memberintask->assignee as $obj){
				$arrMemberInTask[] =  $obj['userId'];
			}}
			$query = Project::findOne(["_id" => $projectId]);
			foreach ($query->member as $obj){
				if($obj['activeFlag'] == ActiveFlag::ACTIVE){
					$arrMemberInProject[] =  $obj['userId'];
				}
			}
			$query = User::find();
			$condition = [];
			if(!empty($name)){
				$employeeName = explode(" ", $name);
				$size = sizeof($employeeName);
				if($size > 1){
					$query->andWhere(['like',  'nameTh', $employeeName[0]]);
					$query->andWhere(['like',  'sernameTh', $employeeName[1]]);
				}else{
					$query->andWhere(['like',  'nameTh', $employeeName[0]]);
					$query->orWhere(['like',  'sernameTh', $employeeName[0]]);
				}
			}
			$query->andwhere(['_id' =>  $arrMemberInProject]);
			if($memberintask['assignee']){
				$query->andwhere(['not in','_id' , $arrMemberInTask]);
			}
			if(!empty($department)){
				$condition['depCode'] = $department;
			}
			if(!empty($condition)){
				$query->andWhere($condition);
			}
			$pagination = new Pagination([
					'totalCount' => $query->count(),
					'defaultPageSize' => 10
			]);
			$pagination->setPageSize($length);
			$query->offset($pagination->offset);
			$query->orderBy($dataTablesSort->orders);
			$query->limit($pagination->limit);
			$users = $query->all();
			$pagination->params = [
					'page'=> $page,
					'sort'=>$sort,
					'per-page'=>$length,
					'taskId' => $taskId,
					'projectId' => $projectId,
					'name' => $name,
					'department' => $department
			];
			$departmentModel = new Department();
			$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
			return $this->render('addmember', [
					'taskId'=>$taskId,
					'projectId'=>$projectId,
					"users" => $users,
					"listDepartment" => $listDepartment,
					"name" => $name,
					"department" => $department,
					"length" => $length,
					"pagination" => $pagination,
					"dataTablesLength" => $dataTablesLength,
					"sort" => $sort,
					"dataTablesSort" => $dataTablesSort,
					"page" => $page
			]);
	}
	public function actionAddmember()
	{
		$request = Yii::$app->request;
		$taskId = $request->post('taskId', null);
		$projectId =$request->post('projectId', null);
		$data = $request->post('data', null);
		$data = json_decode($data);
		$nummberMember = sizeof($data);
		$retData['success'] = false;
		$retData['isProject'] = false;
		$retData['isCancel'] = false;
		$retData['isClose'] = false;
		$retData['isDelete'] = false;
		$retData['isDone'] = false;
		$baseUrl = \Yii::getAlias ( '@web' );
		$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
		if($projects == null){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-danger']
			]);
			$retData['isProject'] = true;
		}else{
			if($projects['status'] == Status::CANCEL){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isCancel'] = true;
			}else if($projects['activeFlag'] == ActiveFlag::INACTIVE){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isClose'] = true;
			}else if($projects->activeFlag == Status::CLOSE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					$retData['isDone'] = true;
				}
		}
		if($retData['isClose'] == true || $retData['isCancel'] == true || $retData['isProject'] == true || $retData['isDone'] == true){
			$retData['success'] = false;
		}else{
			$retData['success'] = false;
			$memberintask = Task::findOne(["_id" => $taskId]);
			$arrmember=[];
			if($memberintask['assignee']){
				foreach ($memberintask->assignee as $obj){
					$arrmember[(string)$obj['userId']]['userId'] =  new ObjectID($obj['userId']);
				}
			}
				if($memberintask == null){
					$retData['isDelete'] = true;
				}else{
					$model = null;
					$taskMember=[];
					foreach ($data as $obj2){
						$arrmember[(string)$obj2->userId]['userId'] = new ObjectID($obj2->userId);
					}
					$i=0;
					foreach ($arrmember as $obj3){
						$taskMember[$i]['userId'] = new ObjectID($obj3['userId']);
						$i++;
					}
					$model = Task::findOne(["_id" => $taskId]);
					$model2 = Task::findOne(["_id" => $taskId]);
					$model->assignee = $taskMember;
					$status2 =	$model2->status;
					$status = Status::PREPARE_TASK;
					if((int)$status2 == (int)$status){
						$model->status = Status::NEW_TASK;	 
					}
				}
				if($model->save()){
					$message = true;
					$retData['success'] = true;
					$retData['isDelete'] = false;
				}else{
					$message = false;
					$retData['success'] = false;
					$retData['isDelete'] = true;
				}
			}
			echo json_encode($retData);
	}
	public function actionRemovemember()
	{
		$request = Yii::$app->request;
		$taskId = $request->post('taskId', null);
		$data = $request->post('data', null);
		$projectId = $request->post('projectId', null);
		$currentId = Yii::$app->user->identity->_id;
		$data = json_decode($data);
		$retData['success'] = false;
		$retData['isProject'] = false;
		$retData['isCancel'] = false;
		$retData['isClose'] = false;
		$retData['isDelete'] = false;
		$retData['isDone'] = false;
		$baseUrl = \Yii::getAlias ( '@web' );
		$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
		if($projects == null){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-danger']
			]);
			$retData['isProject'] = true;
		}else{
			if($projects['status'] == Status::CANCEL){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isCancel'] = true;
			}else if($projects['activeFlag'] == ActiveFlag::INACTIVE){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isClose'] = true;
			}else if($projects['status'] == Status::CLOSE){
					
					$retData['isDone'] = true;
				}
		}
		if($retData['isClose'] == true || $retData['isCancel'] == true || $retData['isProject'] == true || $retData['isDone'] == true){
			$retData['success'] = false;
		}else{
			$isDelete= Task::findOne(["_id"=>new ObjectID($taskId)]);
			if($isDelete == null){
				$retData['isDelete'] = true;
			}else{
				$memberintask = Task::findOne(["_id" =>  $taskId]);
				$logs = new Log();
				$templog = [];
				$oldData = Task::findOne($taskId);
				$templog['_id'] = $oldData->_id;
				$templog['assignee'] = $oldData->assignee;
				$templog['createBy'] = $oldData->createBy;
				$templog['createDate'] = $oldData->createDate;
				$templog['description'] = $oldData->description;
				$templog['endDate'] = $oldData->endDate;
				$templog['progress'] = $oldData->progress;
				$templog['projectId'] = $oldData->projectId;
				$templog['startDate'] = $oldData->startDate;
				$templog['status'] = $oldData->status;
				$templog['taskName'] = $oldData->taskName;
				$id=[];
				if($memberintask['assignee']){
					foreach ($memberintask->assignee as $obj){
						$id[] =  new ObjectID($obj['userId']);
					}
				}
				$conditions = [];
				$id2=[];
				foreach ($data as $obj2){
					$id2[] = new ObjectID($obj2->userId);
				}
				$query = User::find();
				if(!empty($id)){
					$conditions['_id'] = $id;
				}
				$query->where($conditions);
				$query->andwhere(['not in','_id' , $id2]);
				$arrmember= $query->all();
				$model = null;
				$taskMember=[];
				$i=0;
				foreach ($arrmember as $obj3){
					$taskMember[$i]['userId'] = $obj3->_id;
					$i++;
				}
				$model = Task::findOne($taskId);
				$model->assignee = $taskMember;
				if($model->save()){
					$logs->oldData = $templog;
					$logs->newData = '';
					$logs->userId = $currentId;
					$logs->refId = new ObjectID($taskId);
					$logs->editDate = new MongoDate();
					$logs->action = 'ลบสมาชิก';
					$logs->memberId = $id2;
					if ($logs->save()){
						$message = true;
						$retData['success'] = true;
						$retData['isDelete'] = false;
					}
				}else{
					$message = false;
					$retData['success'] = false;
					$retData['isDelete'] = false;
				}
			}
		
		}
		echo json_encode($retData);
	}
	public function actionEditmember()
	{
		$request = Yii::$app->request;
		$taskId = $request->post('taskId', null);
		$projectId =$request->post('projectId', null);
		$data = $request->post('data', null);
		$currentId = Yii::$app->user->identity->_id;
		$data = json_decode($data);
		$nummberMember = sizeof($data);
		$retData['success'] = false;
		$retData['isProject'] = false;
		$retData['isCancel'] = false;
		$retData['isClose'] = false;
		$retData['isDelete'] = false;
		$retData['isDone'] = false;
		$baseUrl = \Yii::getAlias ( '@web' );
		$projects = Project::findOne(["_id"=>new ObjectID($projectId)]);
		if($projects == null){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'โครงการนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-danger']
			]);
			$retData['isProject'] = true;
		}else{
			if($projects['status'] == Status::CANCEL){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกยกเลิกโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isCancel'] = true;
			}else if($projects['activeFlag'] == ActiveFlag::INACTIVE){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isClose'] = true;
			}else if($projects->activeFlag == Status::CLOSE){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกปิดโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isDone'] = true;
			}
		}
		if($retData['isClose'] == true || $retData['isCancel'] == true || $retData['isProject'] == true || $retData['isDone'] == true){
			$retData['success'] = false;
		}else{
			$retData['success'] = false;
			$memberintask = Task::findOne(["_id" => $taskId]);
				if($memberintask == null){
					$retData['isDelete'] = true;
				}else{
					$logs = new Log();
					$templog = [];
					$oldData = Task::findOne($taskId);
					$templog['_id'] = $oldData->_id;
					$templog['assignee'] = $oldData->assignee;
					$templog['createBy'] = $oldData->createBy;
					$templog['createDate'] = $oldData->createDate;
					$templog['description'] = $oldData->description;
					$templog['endDate'] = $oldData->endDate;
					$templog['progress'] = $oldData->progress;
					$templog['projectId'] = $oldData->projectId;
					$templog['startDate'] = $oldData->startDate;
					$templog['status'] = $oldData->status;
					$templog['taskName'] = $oldData->taskName;
					$model = null;
					$arrmember=[];
					$i=0;
					foreach ($data as $obj2){
						$arrmember[$i]['userId'] = new ObjectID($obj2->userId);
						$i++;
					}
					$model = Task::findOne(["_id" => $taskId]);
					$model->assignee = $arrmember;	
					$status2 =	$model->status;
					$status = Status::PREPARE_TASK;
					if((int)$status2 == (int)$status){
						$model->status = Status::NEW_TASK;
					}
				}
				if($model->save()){
					$logs->oldData = $templog;
					$logs->newData = '';
					$logs->userId = $currentId;
					$logs->refId = new ObjectID($taskId);
					$logs->editDate = new MongoDate();
					$logs->action = 'แก้ไขสมาชิก';
					if ($logs->save()){
						$message = true;
						$retData['success'] = true;
						$retData['isDelete'] = false;
					}
				}else{
					$message = false;
					$retData['success'] = false;
					$retData['isDelete'] = true;
				}
		}
		echo json_encode($retData);
	}

	public function actionPrivatetask()
	{
		$request = Yii::$app->request;
		$projectId = $request->post('projectId',$request->get('projectId',null));
		$name = $request->post('name',$request->get('name', null));
		$status = $request->post('status',$request->get('status', null));
		$username = $request->post('username',$request->get('username', null));
		$type = $request->post('type',$request->get('type', null));
		$projectName = $request->post('projectName',$request->get('projectName', null));
		$name = trim($name);
		$projectName = trim($projectName);
		$username = trim($username); 
		$userId = Yii::$app->user->identity->_id;
		Permission::havePermission(Permission::SEARCH_TASK);
		$userlist = User::find();
		$conditions = [];
		$query = Task::find();
		$query2 = Task::find();
		$query3 = Task::find();
		$query->where(['$or' => [['assignee.userId' => $userId ], ['createBy' => $userId]]]);
		$query2->where(['$or' => [['assignee.userId' => $userId ], ['createBy' => $userId]]]);
		$query3->where(['$or' => [['assignee.userId' => $userId ], ['createBy' => $userId]]]);	
		if(!empty($projectName)){
			$queryproject = Project::find();
			$queryproject->andWhere(['like',  'projectName', $projectName]);
			$queryproject->andwhere(['status' => Status::OPEN ]);
			$queryproject->andwhere(['activeFlag' => ActiveFlag::ACTIVE ]);
			$projectall = $queryproject->all();
			$idproject=[];
			foreach($projectall as $object1){
				$idproject[] = $object1['_id'];
			}
			$query->andwhere(['projectId' => $idproject]);
			$query2->andwhere(['projectId' => $idproject]);
			$query3->andwhere(['projectId' => $idproject]);		
		}
		if(!empty($name)){
			$query->andWhere(['like', "taskName",$name]);		
			$query->orWhere(['like', "tag", $name]);
			$query->andwhere(['$or' => [['assignee.userId' => $userId ], ['createBy' => $userId]]]);
			$query2->andWhere(['like', "taskName",$name]);
			$query2->orWhere(['like', "tag", $name]);
			$query2->andwhere(['$or' => [['assignee.userId' => $userId ], ['createBy' => $userId]]]);
			$query3->andWhere(['like', "taskName",$name]);
			$query3->orWhere(['like', "tag", $name]);
			$query3->andwhere(['$or' => [['assignee.userId' => $userId ], ['createBy' => $userId]]]);
		}
		if(!empty($status)){
			if($status== (int)Status::PREPARE_TASK){
				$query->andwhere(["status" => (int)Status::PREPARE_TASK]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::NEW_TASK){
				$query->andwhere(["status" => (int)Status::NEW_TASK]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::OPEN_TASK){
				$query->andwhere(["status" => (int)Status::OPEN_TASK]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::DOING_TASK){
				$query->andwhere(["status" => 0]);
				$query2->andwhere(["status" => (int)Status::DOING_TASK]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::WAIT_APPROVE_TASK){
				$query->andwhere(["status" => 0]);
				$query2->andwhere(["status" => (int)Status::WAIT_APPROVE_TASK]);
				$query3->andwhere(["status" => 0]);
			}
			elseif($status== (int)Status::APPROVED_TASK){
				$query->andwhere(["status" => 0]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => (int)Status::APPROVED_TASK]);
			}
			elseif($status== (int)Status::REJECTED_TASK){
				$query->andwhere(["status" => (int)Status::REJECTED_TASK]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => 0]);
			}elseif($status== (int)Status::COMPLETED_TASK){
				$query->andwhere(["status" => 0]);
				$query2->andwhere(["status" => 0]);
				$query3->andwhere(["status" => (int)Status::COMPLETED_TASK]);
			}else{
				$query->andwhere(["status" => [(int)Status::PREPARE_TASK,(int)Status::NEW_TASK,(int)Status::OPEN_TASK,(int)Status::REJECTED_TASK]]);
				$query2->andwhere(["status" => [(int)Status::DOING_TASK,(int)Status::WAIT_APPROVE_TASK]]);
				$query3->andwhere(["status" => [(int)Status::APPROVED_TASK,(int)Status::COMPLETED_TASK]]);
			}
		}else{
			$query->andwhere(["status" => [(int)Status::PREPARE_TASK,(int)Status::NEW_TASK,(int)Status::OPEN_TASK,(int)Status::REJECTED_TASK]]);
			$query2->andwhere(["status" => [(int)Status::DOING_TASK,(int)Status::WAIT_APPROVE_TASK]]);
			$query3->andwhere(["status" => [(int)Status::APPROVED_TASK,(int)Status::COMPLETED_TASK]]);
		}
		if(!empty($type)){
			if($type == TypeTask::PRIVATETASK){
				$query->andwhere(['projectId' => null]);
				$query2->andwhere(['projectId' => null]);
				$query3->andwhere(['projectId' => null]);
			}
			if($type == TypeTask::PROJECTTASK){
				$query->andwhere(['not','projectId', null]);
				$query2->andwhere(['not','projectId', null]);
				$query3->andwhere(['not','projectId', null]);
			}	
		}
		if(!empty($username)){
			$users= User::find();
			$users->where(['like','nameTh',$username]);
			$users->andwhere(['like','sernameTh',$username]);
			$users = $users->all();
			$arruserId = [];
			foreach($users as $objusers){
				$arruserId[]= $objusers->_id;
			}
			$query->andwhere(['assignee.userId' => $arruserId]);
			$query2->andwhere(['assignee.userId' => $arruserId]);
			$query3->andwhere(['assignee.userId' => $arruserId]);
		}
		$project = Project::find();
		$project->where(['member.userId' => $userId ]);
		$project->andwhere(['status' => Status::CLOSE ]);
		$project->orwhere(['status' => Status::CANCEL ]);
		$project->orwhere(['activeFlag' => ActiveFlag::INACTIVE ]);
		$project->andwhere(['member.userId' => $userId ]);
		$project = $project->all();
		$notproject = [];
		if($project){		
			foreach ($project as $obj){
				$query->andwhere(['not','projectId' , $obj->_id]);
				$query2->andwhere(['not','projectId' , $obj->_id]);
				$query3->andwhere(['not','projectId' , $obj->_id]);
			}	
		}
		$query->OrderBy(['status'=>SORT_ASC,'endDate'=>SORT_ASC]);
		$query2->OrderBy(['status'=>SORT_ASC,'endDate'=>SORT_ASC]);
		$query3->OrderBy(['status'=>SORT_ASC,'endDate'=>SORT_ASC]);
		$todo = $query->all();
		$doing = $query2->all();
		$done = $query3->all();
		$userName = [];
		$project2 = Project::find();
		$project2->where(['member.userId' => $userId ]);
		$project2->andwhere(['status' => Status::OPEN ]);
		$project2->andwhere(['activeFlag' => ActiveFlag::ACTIVE ]);
		$project2 = $project2->all();
		$arrProject = [""];
		if($project2){
			foreach ($project2 as $obj){
				$arrProject[(string)$obj->_id] = $obj->projectName;
			}
		}
		$modelcomment = new Comment();
		return $this->render('privatetask', [
				'name' => $name,
				"todo" => $todo,
				"doing" => $doing,
				"done" => $done,
				'projectId' => $projectId,
				'status' => $status,
				'type' => $type,
				'username' => $username,
				'arrProject' => $arrProject,
				'modelcomment' => $modelcomment,
				'projectName' => $projectName
		]);
	}
	public function actionAssignprivatetask()
	{
		$request = Yii::$app->request;
		$taskId = $request->post('taskId',$request->get('taskId',null));
		$name = $request->post('name',$request->get('name',null));
		$department = $request->post('department',$request->get('department',null));
		$length = (int)$request->post('per-page',null);
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
		$sort = $request->get('sort','categoryName');
		$dataTablesSort = new Sort([
				'defaultOrder' => [
						'name' => SORT_ASC
				],
				'attributes' => [
						'name' => [
								'asc' => ['nameTh' => SORT_ASC, 'sernameTh' => SORT_ASC],
								'desc' => ['nameTh' => SORT_DESC, 'sernameTh' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'ชื่อผู้ใช้งานระบบ',
						],
						'department' => [
								'asc' => ['depName' => SORT_ASC],
								'desc' => ['depName' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'แผนก',
						]
				],
		]);	 
		$dataTablesSort->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'taskId' => $taskId,
				'name' => $name,
				'department' => $department
		];
		$task = Task::findOne(["_id"=>new ObjectID($taskId)]);
		$arrUser = [];
		foreach( $task->assignee as $member){
			$arrUser[] = $member['userId'];
		} 
		$query = User::find();
		$condition = [];
		if(!empty($name)){
			$employeeName = explode(" ", $name);
			$size = sizeof($employeeName);
			if($size > 1){
				$query->andWhere(['like',  'nameTh', $employeeName[0]]);
				$query->andWhere(['like',  'sernameTh', $employeeName[1]]);
			}else{
				$query->andWhere(['like',  'nameTh', $employeeName[0]]);
				$query->orWhere(['like',  'sernameTh', $employeeName[0]]);
			}
		}
		$query->andwhere(['in','_id',$arrUser]);
		if(!empty($department)){
			$condition['depCode'] = $department;
		}
		if(!empty($condition)){
			$query->andWhere($condition);
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
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'taskId' => $taskId,
				'name' => $name,
				'department' => $department
		];
		$departmentModel = new Department();
		$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
		return $this->render('assignprivatetask', [
				"listDepartment" => $listDepartment,
				"taskId" => $taskId,
				"task" => $task,
				"model" => $model,
				"name" => $name,
				"department" => $department,
				"length" => $length,
				"pagination" => $pagination,
				"dataTablesLength" => $dataTablesLength,
				"sort" => $sort,
				"dataTablesSort" => $dataTablesSort,
				"page" => $page
				
		]);
	}
	public function actionAddprivatetask()
	{
		$request = Yii::$app->request;
		$taskId = $request->post('taskId', $taskId = $request->get('taskId',null));
		$name = $request->post('name',$name = $request->get('name',null));
		$department = $request->post('department',$department = $request->get('department',null));
		$length = (int)$request->post('per-page',null);
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
		$sort = $request->get('sort','categoryName');
		$dataTablesSort = new Sort([
				'defaultOrder' => [
						'name' => SORT_ASC
				],
				'attributes' => [
						'name' => [
								'asc' => ['nameTh' => SORT_ASC, 'sernameTh' => SORT_ASC],
								'desc' => ['nameTh' => SORT_DESC, 'sernameTh' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'ชื่อผู้ใช้งานระบบ',
						],
						'department' => [
								'asc' => ['depName' => SORT_ASC],
								'desc' => ['depName' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'แผนก',
						]
				],
		]);
		$dataTablesSort->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'taskId' => $taskId,
				'name' => $name,
				'department' => $department
		];
		$member=[];
		$memberintask = Task::findOne(["_id" => $taskId]);
		if($memberintask['assignee']){
			foreach ($memberintask->assignee as $obj){
				$member[] =  $obj['userId'];
			}
		}		 
		$query = User::find();
		$condition = [];
		if(!empty($name)){
			$employeeName = explode(" ", $name);
			$size = sizeof($employeeName);
			if($size > 1){
				$query->andWhere(['like',  'nameTh', $employeeName[0]]);
				$query->andWhere(['like',  'sernameTh', $employeeName[1]]);
			}else{
				$query->andWhere(['like',  'nameTh', $employeeName[0]]);
				$query->orWhere(['like',  'sernameTh', $employeeName[0]]);
			}
		}
		$query->andwhere(['not in','_id' , $member]);
		if(!empty($department)){
			$condition['depCode'] = $department;
		}
		if(!empty($condition)){
			$query->andWhere($condition);
		}
		$pagination = new Pagination([
				'totalCount' => $query->count(),
				'defaultPageSize' => 10
		]);
		$pagination->setPageSize($length);
		$query->offset($pagination->offset);
		$query->orderBy($dataTablesSort->orders);
		$query->limit($pagination->limit);
		$users = $query->all();
		$pagination->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'taskId' => $taskId,
				'name' => $name,
				'department' => $department
		];
		$departmentModel = new Department();
		$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
		return $this->render('addmemberprivatetask', [
				'taskId'=>$taskId,
				"users" => $users,
				"listDepartment" => $listDepartment,
				"name" => $name,
				"department" => $department,
				"length" => $length,
				"pagination" => $pagination,
				"dataTablesLength" => $dataTablesLength,
				"sort" => $sort,
				"dataTablesSort" => $dataTablesSort,
				"page" => $page
		]);
	}
	public function actionAddmemberprivatetask()
	{
		$request = Yii::$app->request;
		$taskId = $request->post('taskId', null); 
		$data = $request->post('data', null);
		$data = json_decode($data);
		$nummberMember = sizeof($data);
		$isDelete = Task::findOne(["_id" => $taskId]);;
		$retData['success'] = false;
			if($isDelete == null){
				$retData['isDelete'] = true;
			}else{
				$memberintask = Task::findOne(["_id" => $taskId]);
				$arrmember=[];
				if($memberintask['assignee']){
					foreach ($memberintask->assignee as $obj){
						$arrmember[(string)$obj['userId']]['userId'] =  new ObjectID($obj['userId']);
					}
				}
				$model = null;
				$taskMember=[];
				foreach ($data as $obj2){
					$arrmember[(string)$obj2->userId]['userId'] = new ObjectID($obj2->userId);
				}
				$i=0;
				foreach ($arrmember as $obj3){
					$taskMember[$i]['userId'] = new ObjectID($obj3['userId']);
					$i++;
				}
				$model = Task::findOne(["_id" => $taskId]);
				$model2 = Task::findOne(["_id" => $taskId]);
				$model->assignee = $taskMember;
				$status = Status::PREPARE_TASK;
				if($model->status == (int)$status){
					$model->status = Status::NEW_TASK;
				}
			}
			if($model->save()){
				$message = true;
				$retData['success'] = true;
				$retData['isDelete'] = false;
			}else{
				$message = false;
				$retData['success'] = false;
				$retData['isDelete'] = true;
			}
			echo json_encode($retData);
	}
	public function actionRemovememberprivatetask()
	{
		$request = Yii::$app->request;
		$taskId = $request->post('taskId', null);
		$data = $request->post('data', null);
		$currentId = Yii::$app->user->identity->_id;
		$data = json_decode($data);
		$isDelete= Task::findOne(["_id"=>new ObjectID($taskId)]);
		$retData['success'] = false;
		$retData['isDelete'] = false;
			if($isDelete == null){
				$retData['isDelete'] = true;
			}else{
				$id=[];
				$memberintask = Task::findOne(["_id"=>new ObjectID($taskId)]);
				if($memberintask['assignee']){
					foreach ($memberintask->assignee as $obj){
						$id[] =  new ObjectID($obj['userId']);
					}
				}
				$conditions = [];
				$id2=[];
				foreach ($data as $obj2){
					$id2[] = new ObjectID($obj2->userId);
				}
				$query = User::find();
				if(!empty($id)){
					$conditions['_id'] = $id;
				}
				$query->where($conditions);
				$query->andwhere(['not in','_id' , $id2]);
				$arrmember= $query->all();
				$model = null;
				$taskMember=[];
				$i=0;
				foreach ($arrmember as $obj3){
					$taskMember[$i]['userId'] = $obj3->_id;
					$i++;
				}
				$model = Task::findOne($taskId);
				$model->assignee = $taskMember;	
			if($model->save()){
					$message = true;
					$retData['success'] = true;
					$retData['isDelete'] = false;
			}else{
				$message = false;
				$retData['success'] = false;
				$retData['isDelete'] = false;
			}
			}
		echo json_encode($retData);
	}
	public function isDuplicate($taskName,$projectId,$taskId){
		$conditions = [];
		$query = Task::find();
		if(!empty($taskName)){
			$conditions['taskName'] = $taskName;
		}
		if(!empty($projectId)){
			$conditions['projectId'] = new ObjectID($projectId);
		}
		if(!empty($conditions)){
			$query->where($conditions);
		}
		if($taskId != null){
		
			$query->andwhere(['not in','_id',new ObjectID($taskId)]);
		}
		$listTask = $query->all();
		if($listTask != null){
			return true;
		}else{
			return false;
		}
	}
	public function isDuplicatePrivate($taskName,$createby,$taskId){
		$conditions = [];
		$query = Task::find();
		if(!empty($taskName)){
			$conditions['taskName'] = $taskName;
		}
		if(!empty($createby)){
			$conditions['createBy'] = $createby;
		}
		if(!empty($conditions)){
			$query->where($conditions);
		}
		if($taskId != null){	
			$query->andwhere(['not in','_id',new ObjectID($taskId)]);
		}
		$listTask = $query->all();
		if($listTask != null){
			return true;
		}else{
			return false;
		}
	}

	public function isDelete($taskId){
		$conditions = [];
		$query = Task::find();
		if(!empty($taskId)){
			$conditions['_id'] = new ObjectID($taskId);
		}
		if(!empty($conditions)){
			$query->where($conditions);
		}
		$listTask = $query->all();
		if($listTask != null){
			return false;
		}else{
			return true;
		}
	}
	public function isProject($projectId){
		$conditions = [];
		$query = Project::find();
		if(!empty($projectId)){
			$conditions['_id'] = new ObjectID($projectId);
		}
		if(!empty($conditions)){
			$query->where($conditions);
		}
		$listTask = $query->all();
	
		if($listTask != null){
			return false;
		}else{
			return true;
		}
	}

	public function changOpentask($taskId,$userId){
		$conditions = [];
		$query = Task::find();
		$listTask = null;
		$model =null;
		if(!empty($taskId)){
			$conditions['_id'] = new ObjectID($taskId);
		}
		if(!empty($userId)){	 
			$conditions['assignee.userId'] = new ObjectID($userId);
		}
		if(!empty($conditions)){
			$query->where($conditions); 
		}		 
		$listTask = $query->all();	 
		if($listTask != null){

			$model = Task::findOne($taskId);
			$model->status = Status::OPEN_TASK;
			if($model->save()){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	public function actionUploadimages()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$currentId = Yii::$app->user->identity->_id;
		$taskId = $request->post('taskIdUploadImages', null);
		
		if (Yii::$app->request->isPost) {
			$model = new Comment();
			$model->images = UploadedFile::getInstances($model, 'images');
			$result = $model->upload($taskId);
			if ($result != false) {
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
		$currentId = Yii::$app->user->identity->_id;
		$taskId = $request->post('taskIdUploadFiles', null);
		if (Yii::$app->request->isPost) {
			$model = new Comment();
			$model->allfiles = UploadedFile::getInstances($model, 'allfiles');
			$result = $model->uploadfiles($taskId);
			if ($result != false) {
				return json_encode($result) ;
			}else{
				return;
			}
		}
	}
	protected function findModel($id)
	{
		if (($model = Task::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
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
			$query = Comment::find();
			$conditions['refId'] = new ObjectID($taskId);
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
	
	public function actionDuplicate(){
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$taskId = explode(":", $data['taskId']);
			$taskId = $taskId[0];
			$projectId = explode(":", $data['projectId']);
			$projectId = $projectId[0];
			$taskName = explode(":", $data['taskName']);
			$taskName = $taskName[0];
			
			if($projectId == ""){
				$projectId = null;
			}
			
			if($taskId == ""){
				$taskId = null;
			}
	
			$isDuplicate = $this->isDuplicate($taskName, $projectId, $taskId);
	
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'taskId' => $taskId,
					'projectId' => $projectId,
					'taskName' => $taskName, 
					'isDuplicate' => $isDuplicate,
					'code' => 100
			];
		}
	}
}