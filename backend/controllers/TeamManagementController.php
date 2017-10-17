<?php

namespace backend\controllers;

use Yii;
use backend\models\TeamManagement;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use backend\models\Team;
use MongoDB\BSON\ObjectID;
use common\libs\ActiveFlag;
use backend\models\Project;
use common\libs\Permission;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * TeamManagementController implements the CRUD actions for TeamManagement model.
 */
class TeamManagementController extends Controller
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
     * Lists all TeamManagement models.
     * @return mixed
     */
    public function actionIndex()
    {
    	Permission::havePermission(Permission::SEARCH_TEAM_MANAGEMENT);
    	
    	$request = Yii::$app->request;
    	$name = $request->post('name');
    	$activeFlag = $request->post('activeFlag', null);
    	$length = (int)$request->post('per-page',null);
    	if(empty($name)){
    		$name = $request->get('name',null);
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
    					'name' => SORT_ASC
    			],
    			'attributes' => [
    					'name' => [
    							'asc' => ['teamName' => SORT_ASC],
    							'desc' => ['teamName' => SORT_DESC],
    							'default' => SORT_DESC,
    							'label' => 'ชื่อทีม',
    					],
    					'activeFlag' => [
    							'asc' => ['activeFlag' => SORT_ASC],
    							'desc' => ['activeFlag' => SORT_DESC],
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
    			'activeFlag' => $activeFlag
    	];
    	
    	$query = Team::find();
    	if(!empty($name)){
    		$query->andWhere(['like', "teamName", $name]);
    	}
    	if(!empty($activeFlag)){
    		$query->andWhere(['activeFlag' => (int)$activeFlag]);
    	}
    	
    	$pagination = new Pagination([
    			'totalCount' => $query->count(),
    			'defaultPageSize' => 10
    	]);
    	$pagination->setPageSize($length);
    	$query->offset($pagination->offset);
    	$query->orderBy($dataTablesSort->orders);
    	$query->limit($pagination->limit);
    	
    	$value = $query->all();
    	$pagination->params = [
    			'page'=> $pagination->page,
    			'sort'=>$sort,
    			'per-page'=>$length,
    			'name' => $name,
    			'activeFlag' => $activeFlag
    	];
    	return $this->render('index', [
    			'value' => $value, 
    			'name' => $name,
    			'activeFlag' => $activeFlag,
    			"pagination" => $pagination,
    			"dataTablesLength" => $dataTablesLength,
    			"length" => $length,
    			"sort" => $sort,
    			"dataTablesSort" => $dataTablesSort,
    			"page" => $page,
    	]);
    }

    public function actionDelete(){
    	
    	Permission::havePermission(Permission::DELETE_TEAM_MANAGEMENT);
    	
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$teamId = $request->post('teamId', null);
    	
    	$model = Team::findOne($teamId);
    	$isActiveflag = $this->isActiveflag($model);
    	$isDelete = $this->isDelete($model);
    	$retData['success'] = false;
    	
    	$isUsedInProject = Project::findAllProjectByTeam(new ObjectID($teamId));
    	
    	if($isDelete){
    		$retData['isDelete'] = true;
    	}else if($isActiveflag){
    		$retData['isActiveflag'] = true;
    	}else if($isUsedInProject){
    		$retData['isUsedInProject'] = true;
    	}else{
    		if($model->delete()){
    			$retData['success'] = true;
    		}
    	}
    	echo json_encode($retData);
    }
    
	public function isActiveflag($model)
    {
    	$isActive = false;
    	if($model != null && $model->activeFlag == ActiveFlag::ACTIVE){
    		$isActive = true;
    	}
    	return $isActive;
    }

    /**
     * Finds the TeamManagement model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $_id
     * @return TeamManagement the loaded model
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
    
    public function actionChangeactiveflag(){
    	
    	Permission::havePermission(Permission::CHANGE_STATUS_TEAM_MANAGEMENT);
    	
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	
    	$teamId = $request->post('teamId', null);
    	$activeFlag = $request->post('activeFlag', null);
    	
    	$model = Team::findOne($teamId);
    	$isDelete = $this->isDelete($model);
    	$retData['success'] = false;
    	if($isDelete){
    		$retData['isDelete'] = true;
    	}else{
    		$model->activeFlag = ((int)$activeFlag == ActiveFlag::ACTIVE)? ActiveFlag::INACTIVE : ActiveFlag::ACTIVE ;
    		$retData['activeFlag'] = $model->activeFlag;
    		if($model->save()){
    			$retData['success'] = true;
    		}
    	}
    	echo json_encode($retData);
    }
    
	public function isDelete($model){
    	$isDelete = false;
    	if($model == null){
    		$isDelete = true;
    	}
    	return $isDelete;
    }
    
    public function isUsedInProject($teamId){
    	$model = Project::findAllProjectByTeam(new ObjectID($teamId));
    	return $model != null;
    }
    
    public function actionView()
    {
    	Permission::havePermission(Permission::VIEW_TEAM_MANAGEMENT);
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
    
}
