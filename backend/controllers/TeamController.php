<?php

namespace backend\controllers;

use Yii;
use backend\models\Project;
use backend\models\Team;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use common\libs\ActiveFlag;
use common\models\User;
use \MongoDate;
use MongoDB\BSON\ObjectID;
use common\libs\DateTime;
use backend\models\Department;
use yii\helpers\ArrayHelper;
use common\libs\Permission;
use yii\filters\AccessControl;
use yii\data\Pagination;
use yii\data\Sort;
/**
 * TeamController implements the CRUD actions for Team model.
 */
class TeamController extends Controller
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
	 * Lists all Team models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		Permission::havePermission(Permission::SEARCH_TEAM);
		$request = Yii::$app->request;
		$name = $request->post('name',null);
		$nameUser = $request->post('nameUser',$request->get('nameUser',null));
		
		if(empty($name)){
			$name = $request->get('searchname',null);
		}
		
		$conditions = [];
		$query = Team::find();
		if(!empty($name)){
			$query->andWhere(['like', "teamName", $name]);
		}
		
		$name = trim($name);
		$nameUser = trim($nameUser);
		
		$condition = [];
		$username=null;
		if(!empty($nameUser)){
			$queryuser = User::find();
			$username = explode(" ", $nameUser);
			$size = sizeof($username);
			if($size > 1){
				$queryuser->andWhere(['like',  'nameTh', $username[0]]);
				$queryuser->andWhere(['like',  'sernameTh', $username[1]]);
			}else{
				$queryuser->andWhere(['like',  'nameTh', $username[0]]);
				$queryuser->orWhere(['like',  'sernameTh', $username[0]]);
			}
			$iduser=[];
			$userall = $queryuser->all();
			$j=0;
			foreach($userall as $object1){
				$iduser[$j] = $object1['_id'];
				$j++;
			}
			for($k=0 ; $k < $j; $k++){
				$query->orwhere(['member' => ['$elemMatch' => ['userId' => $iduser[$k],'activeFlag' => 1]]]);
			}
		}
		
		$query->andWhere(['activeFlag'=> ActiveFlag::ACTIVE]);
		$pagination = new Pagination([
				'defaultPageSize' => 16,
				'totalCount' => $query->count(),
		]);
		
		$query->offset($pagination->offset);
		$query->limit($pagination->limit);
		$value = $query->all();
		
		$user = User::find()->all();
		$arrUser = [];
		$i=1;
		if($user){
			foreach ($user as $object){
				$arrUser[$i] = $object->nameTh . ' ' . $object->sernameTh;
				$i++;
			}
		}
		
		$pagination->params = [
				'page'=> $pagination->page, 
				'name'=>$name,
				'nameUser'=>$nameUser,
		];
		
		$model = new Team();
		
		return $this->render('index', [
				'value' => $value,
				'name' => $name,
				"pagination"=>$pagination,
				"arrUser"=>$arrUser,
				"nameUser"=>$nameUser,
				"model"=>$model
			
		]);
	}

	/**
	 * Displays a single Team model.
	 * @param integer $_id
	 * @return mixed
	 */
	public function actionView()
	{
		Permission::havePermission(Permission::VIEW_TEAM);
		if (Yii::$app->request->isAjax) {
			
			$data = Yii::$app->request->post();
			$teamId = explode(":", $data['teamId']);
			$teamId = $teamId[0];
				 
			$isDelete = $this->isDelete($teamId);
			$retData['success'] = $isDelete;
			if(!$isDelete){
				$team = Team::findOne(["_id"=>new ObjectID($teamId)]);
				$retData['teamId'] = (string)$team->_id;
				$retData['teamName'] = $team->teamName;
				$retData['description'] = $team->description;
				
				if($team->createBy != null){
					$retData['createBy'] = User::getUserName((string)$team->createBy);
				}else{
					$retData['createBy'] = null;
				}
				$retData['createDate'] = DateTime::MongoDateToDateCreate($team->createDate["sec"]);
				
				$teamData = [];
				$users = [];
				
				$model = Team::findOne(["_id" => new ObjectID($teamId)]);
				$teamData = $model;
				
				$index = 0;
				if($teamData['member']){
					foreach ($teamData->member as $userInTeam) {
						if($userInTeam['activeFlag'] == ActiveFlag::ACTIVE){
							$users[$index]['userid'] = $userInTeam['userId'];
							$index++;
						}
					}
					$nummberUser = sizeof($users);;
					for($j = 0; $j<$nummberUser; $j++){
						$users[$j]['userid'] = User::getUserName((string)$users[$j]['userid']);
					}
				}
				
				$retData['users'] = $users;
			}
			 
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'isDelete' => $isDelete,
					'teamData' => $retData,
					'code' => 100,
			];
		}
	}

	/**
	 * Creates a new Team model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	 
	/**
	 * Deletes an existing Team model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $_id
	 * @return mixed
	 */
	public function actionDelete()
	{	
		Permission::havePermission(Permission::DELETE_TEAM);
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$teamId = $request->post('teamId', null);
		 
		$isDelete = $this->isDelete($teamId);
		$isUsedInProject = Project::findAllProjectByTeam(new ObjectID($teamId));
		$retData['success'] = false;

		 
		if($isDelete){
			$retData['isDelete'] = true;
		}else if($isUsedInProject){
			$retData['isUsedInProject'] = true;
		}else{
			$model = Team::findOne(["_id"=>new ObjectID($teamId)]);
			$model->activeFlag = ActiveFlag::INACTIVE;
			$retData['isDelete'] = false;
			if($model->save()){
				$retData['success'] = true;
			}
		}
		echo json_encode($retData);
	}
	
	public function actionGeteditteam()
	{
		Permission::havePermission(Permission::EDIT_TEAM);
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$teamId = explode(":", $data['teamId']);
			$teamId = $teamId[0];

			$isDelete = $this->isDelete($teamId);
			 
			if(!$isDelete){
				$team = Team::findTeamById($teamId);
				$retData['teamId'] = (string)$team->_id;
				$retData['teamName'] = $team->teamName;
				$retData['description'] = $team->description;
			}
	
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
	
			return [
					'isDelete' => $isDelete,
					'teamData' => $retData,
					'code' => 100,
			];
		}
	}
	
	public function actionCreate()
	{
		Permission::havePermission(Permission::CREATE_TEAM);
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
		 
		$teamId = $request->post('teamId', null);
		$teamName = $request->post('teamName', null);
		$description = $request->post('description', null);

		$teamName = trim($teamName);
		$description = trim($description);
		 
		$retData['success'] = false;
		$retData['isDuplicate'] = $this->isDuplicate($teamName,$teamId);
		 
		if(!$retData['isDuplicate']){
			$team = new Team();
			$team->teamName = $teamName;
			$team->createDate = new MongoDate();
			$team->description = $description;
			$team->createBy = $currentId;
			$team->activeFlag = ActiveFlag::ACTIVE;
			$arrMember = (!empty($team->member))?$team->member:[];
			array_push($arrMember, ["userId" => new ObjectID($currentId), "activeFlag"=>ActiveFlag::ACTIVE]);
			$team->member =  $arrMember;
			 
			if($team->save()){
				$retData['success'] = true;
			}
		}
		echo json_encode($retData);
		 

	}
	public function actionEdit()
	{
		Permission::havePermission(Permission::EDIT_TEAM);
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
		 
		$teamId = $request->post('teamId', null);
		$teamName = $request->post('teamName', null);
		$description = $request->post('description', null);
		
		$teamName = trim($teamName);
		$description = trim($description);
		
		$isDelete = $this->isDelete($teamId);
		$retData['success'] = false;
		if($isDelete){
			$retData['isDelete'] = true;
		}else{
			// break if category name is duplicate
			$retData['isDuplicate'] = $this->isDuplicate($teamName, $teamId);

			if(!$retData['isDuplicate']){
				$model = null;
				if($teamId != null){
					$model = Team::findOne($teamId);
					$model->teamName = $teamName;
					$model->description = $description;
				}

				if($model->save()){
					$retData['success'] = true;
				}
				$retData['isDelete'] = false;
			}
		}
		
		echo json_encode($retData);
	}
	
	public function actionMember()
	{
		Permission::havePermission(Permission::MEMBER_TEAM);
		$request = Yii::$app->request;
		$id = $request->post('teamId', null);
		$name = $request->post('name', null);
		$department = $request->post('department',null);
		$length = (int)$request->post('per-page',null);
		 
		if(empty($id)){
			$id = $request->get('teamId',null);
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
				'teamId' => $id,
				'name' => $name,
				'department' => $department
		];

		$team = Team::findOne(["_id" => new ObjectID($id)]);
		
		$arrUser = [];
		foreach ($team->member as $obj){
			if($obj['activeFlag'] == ActiveFlag::ACTIVE){
				$arrUser[] = $obj['userId'];
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
		
		$query->andWhere(['in','_id',$arrUser]);
		
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
		
		$listUser = $query->all();
		$pagination->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'teamId' => $id,
				'name' => $name,
				'department' => $department
		];

		$departmentModel = new Department();
		 
		$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
		return $this->render('member', [
				"listDepartment" => $listDepartment,
				'id'=>$id,
				'team' => $team,
				'listUser'=>$listUser,
				'department' => $department,
				'name' => $name,
				"length" => $length,
				"pagination" => $pagination,
				"dataTablesLength" => $dataTablesLength,
				"sort" => $sort,
				"dataTablesSort" => $dataTablesSort,
				"page" => $page
		]);
	}
	
	public function actionAdd(){
		Permission::havePermission(Permission::ADD_MEMBER_TEAM);
		$request = Yii::$app->request;
		$id = $request->post('teamId', null);
		$name = $request->post('name', null);
		$department = $request->post('department',null);
		$length = (int)$request->post('per-page',null);
		 
		if(empty($id)){
			$id = $request->get('teamId',null);
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
				'teamId' => $id,
				'name' => $name,
				'department' => $department
		];
		 
		//check if have role continuous else redirect
		$arrUser = [];
		$memberinteam = Team::findOne(["_id" => $id]);
		foreach ($memberinteam->member as $obj){
			if($obj['activeFlag'] == ActiveFlag::ACTIVE){
			$arrUser[] =  $obj['userId'];
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
		
		$query->andWhere(['not in','_id' , $arrUser]);
		
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
				'teamId' => $id,
				'name' => $name,
				'department' => $department
		];
		
		$departmentModel = new Department();
		 
		$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
		return $this->render('addmember', [
				"id"=>$id,
				"users" => $users,
				"listDepartment" => $listDepartment,
				'department' => $department,
				'name' => $name,
				"length" => $length,
				"pagination" => $pagination,
				"dataTablesLength" => $dataTablesLength,
				"sort" => $sort,
				"dataTablesSort" => $dataTablesSort,
				"page" => $page,
				"teamId" => $id
		]);
	}
	 
	public function actionAddmember()
	{
		Permission::havePermission(Permission::ADD_MEMBER_TEAM);
		$request = Yii::$app->request;
		$id = $request->post('id', null);
		$data = $request->post('data', null);


		$data = json_decode($data);
		$nummberMember = sizeof($data);
		
		$isDelete = $this->isDelete($id);
		$retData['success'] = false;
		$retData['isDelete'] = false;
		$retData['isClose'] = false;
		
		if($isDelete){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'ทีมนี้ถูกลบแล้วโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-danger']
			]);
			$retData['isDelete'] = true;
		}else{
			
			$memberinteam = Team::findOne(["_id" => $id]);
			if((int)$memberinteam->activeFlag == (int)ActiveFlag::INACTIVE){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'ทีมนี้ถูกปิดใช้งานแล้วโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isClose'] = true;
			}else{
				
				$arrmember=[];
				foreach ($memberinteam->member as $obj){
					$arrmember[(string)$obj['userId']]['userId'] =  new ObjectID($obj['userId']);
					$arrmember[(string)$obj['userId']]['activeFlag'] =	$obj['activeFlag'];
				}
				
				$model = null;
				$teamMember=[];
				foreach ($data as $obj2){
					$arrmember[(string)$obj2->userId]['userId'] = new ObjectID($obj2->userId);
					$arrmember[(string)$obj2->userId]['activeFlag'] = ActiveFlag::ACTIVE;
				}
				
				$i=0;
				foreach ($arrmember as $obj3){
					$teamMember[$i]['userId'] = new ObjectID($obj3['userId']);
					$teamMember[$i]['activeFlag'] = $obj3['activeFlag'];
					$i++;
				}
				$model = Team::findOne($id);
				$model->member = $teamMember;
				
				if($model->save()){
					$retData['success'] = true;
				}
			}
		}
	
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		echo json_encode($retData);
	}
	
	public function actionRemovemember(){
		Permission::havePermission(Permission::REMOVE_MEMBER_TEAM);
		$request = Yii::$app->request;
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		$id = $request->post('id', null);
		$data = $request->post('data', null);
		
		$data = json_decode($data);
		$memberinteam = Team::findOne(["_id" => $id]);
		$isDelete = $this->isDelete($id);
		$retData['success'] = false;
		$retData['isClose'] = false;
		$retData['isDelete'] = false;
		
		if($isDelete){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'ทีมนี้ถูกลบแล้วโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-danger']
			]);
			$retData['isDelete'] = true;
		}else{
			$memberinteam = Team::findOne(["_id" => $id]);
			$arrmember=[];
			
			if((int)$memberinteam->activeFlag == (int)ActiveFlag::INACTIVE){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'ทีมนี้ถูกปิดใช้งานแล้วโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				$retData['isClose'] = true;
			}else{
				foreach ($memberinteam->member as $obj){
					$arrmember[(string)$obj['userId']]['userId'] =  new ObjectID($obj['userId']);
					$arrmember[(string)$obj['userId']]['activeFlag'] =	$obj['activeFlag'];
				}
				$model = null;
				$teamMember=[];
					
				foreach ($data as $obj2){
					$arrmember[(string)$obj2->userId]['userId'] = new ObjectID($obj2->userId);
					$arrmember[(string)$obj2->userId]['activeFlag'] = ActiveFlag::INACTIVE;
				}
				
				$i=0;
				foreach ($arrmember as $obj3){
					$teamMember[$i]['userId'] = new ObjectID($obj3['userId']);
					$teamMember[$i]['activeFlag'] = $obj3['activeFlag'];
					$i++;	
				}
				$model = Team::findOne($id);
				$model->member = $teamMember;
				
				if($model->save()){
					$message = true;
					$retData['success'] = true;
					$retData['isClose'] = false;
					$retData['isDelete'] = false;
				}else{
					$message = false;
					$retData['success'] = false;
					$retData['isClose'] = false;
					$retData['isDelete'] = false;
				}
			}
		}
		echo json_encode($retData);
	}
	 
	public function isDuplicate($teamName, $teamId){
		$condition = [];
		$query = Team::find();
		 
		if(!empty($teamName)){
			$conditions['teamName'] = $teamName;
		}
		 
		if(!empty($conditions)){
			$query->where($conditions);
		}
		 
		if($teamId != null){
			$query->andwhere(['not in','_id' , $teamId]);
		}
		 
		$listTeam = $query->all();
		
		$result = false;
		if($listTeam != null){
			$result = true;
		}
		return $result;
	}
	
	public function isDelete($teamId){
		$model = Team::findOne($teamId);
		$result = false;
		if($model == null){
			$result = true;
		}
		return $result;
	}

	/**
	 * Finds the Team model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $_id
	 * @return Team the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = Team::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
	
	public function actionUpload()
	{
		$request = Yii::$app->request;
		$teamId = $request->post('uploadTeamId', null);
		
		$model = Team::findOne($teamId);
	
		if ($model->load(Yii::$app->request->post()) && $model->validate()) {
			@unlink(Team::getPhotoTeam($teamId));
			$model->images = $model->upload($model,'images');
			$model->save();
		}
		return $this->redirect(['/team']);
	}
	
	public function actionDuplicate(){
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$teamId = explode(":", $data['teamId']);
			$teamId = $teamId[0];
			$teamName = explode(":", $data['teamName']);
			$teamName = $teamName[0];
	
			$isDuplicate = $this->isDuplicate($teamName, $teamId);
	
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'isDuplicate' => $isDuplicate,
					'code' => 100
			];
		}
	}
}
