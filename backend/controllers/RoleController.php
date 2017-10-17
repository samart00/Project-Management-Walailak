<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use backend\models\Role;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \MongoDate;
use MongoDB\BSON\ObjectID;
use backend\models\AuthItem;
use common\libs\ActiveFlag;
use common\models\User;
use common\libs\Permission;
use common\libs\PermissionType;
use common\libs\Status;
use common\libs\DateTime;
use backend\models\AuthAssignment;
use backend\models\Department;
use yii\helpers\ArrayHelper;
use backend\models\Team;
use yii\data\Pagination;
use yii\data\Sort;

/**
 * RoleController implements the CRUD actions for Role model.
 */
class RoleController extends Controller
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
     * Lists all Role models.
     * @return mixed
     */
    public function actionIndex()
    {
    	Permission::havePermission(Permission::SEARCH_ROLE);
	    $request = Yii::$app->request;
	    $name = $request->post('name',null);
	    $activeFlag = (int)$request->post('activeFlag',null);
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
	    		100 => "100"
	    ];
	    
	    $page = $request->get('page',1);
	    $sort = $request->get('sort','name');
	     
	    $dataTablesSort = new Sort([
	    		'defaultOrder' => [
	    				'name' => SORT_ASC
	    		],
	    		'attributes' => [
	    				'name' => [
	    						'asc' => ['name' => SORT_ASC],
	    						'desc' => ['name' => SORT_DESC],
	    						'default' => SORT_DESC,
	    						'label' => 'ชื่อบทบาท',
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
	    
	    $conditions = [];
	    $query = AuthItem::find();
	    
	    if(!empty($activeFlag)){
	    	$conditions['activeFlag'] = $activeFlag;
	    }
	    
	    if(!empty($conditions)){
	    	$query->where($conditions);
	    }
	    
	    if(!empty($name)){
	    	$query->andWhere(['like', "name", $name]);
	    }
	    
	    $query->andWhere(["type" => 1]);
	    
	    $pagination = new Pagination([
	    		'totalCount' => $query->count(),
	    		'defaultPageSize' => 10
	    ]);
	    $pagination->setPageSize($length);
	    $query->offset($pagination->offset);
	    $query->orderBy($dataTablesSort->orders);
	    $query->limit($pagination->limit);
	     
	    $listRole = $query->all();
	    $pagination->params = [
	    		'page'=> $pagination->page,
	    		'sort'=>$sort, 
	    		'per-page'=>$length,
	    		'name' => $name,
	    		'activeFlag' => $activeFlag
	    ];
	    
        return $this->render('index', [
         		'listRole' => $listRole,
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

    /**
     * Displays a single Role model.
     * @param integer $_id
     * @return mixed
     */
    public function actionView()
    {
    	Permission::havePermission(Permission::VIEW_ROLE);
    	if (Yii::$app->request->isAjax) {
    		$data = Yii::$app->request->post();
    		$roleId = explode(":", $data['roleId']);
    		$roleId = $roleId[0];
    		
    		$arrRole = [];
    		$roleData = AuthItem::findAuthItemById($roleId);
    		$isDelete = $this->isDelete($roleData);
    		if($isDelete){
    			$isDelete = true;
    		}else{
    			$isDelete = false;
    			// set createDate, createBy, activeFlag
    			$arrRole = [];
    			$arrRole['_id'] = (string)$roleData->_id;
    			$arrRole['name'] = $roleData->name;
    			$arrRole['description'] = $roleData->description;
    			$arrRole['activeFlag'] = ActiveFlag::$arrActiveFlag[(int)$roleData->activeFlag];
    			$arrRole['createBy'] = User::getUserName((string)$roleData->createBy);
    			$arrRole['createDate'] = DateTime::MongoDateToDateCreate($roleData->createDate["sec"]);
    		}
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		return [
    				'isDelete' => $isDelete,
    				'roleData' => $arrRole,
    				'code' => 100,
    		];
    	}
    }

    /**
     * Creates a new Role model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
    	Permission::havePermission(Permission::CREATE_ROLE);
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    	
    	$roleId = $request->post('roleId', null);
    	$name = $request->post('name', null);
    	$description = $request->post('description', null);
    	
    	$name = trim($name);
    	$description = trim($description);
    	
    	$retData['success'] = false;
    	$retData['isDuplicate'] = $this->isDuplicate($name, $roleId);
    	
    	if(!$retData['isDuplicate']){
    		$model = null;
    		
    		if ($model == null){
    			$model = new AuthItem();
    			$model->name = $name;
    			$model->description = $description;
    			$model->activeFlag = ActiveFlag::ACTIVE;
    			$model->type = PermissionType::ROLE_TYPE;
    			$model->createDate = new MongoDate();
    			$model->createBy = $currentId;
    			$model->created_at = DateTime::SecNowDate();
    			$model->updated_at = "";
    			$model->rule_name = null;
    		}
    	
    		if($model->save()){
    			$retData['success'] = true;
    		}
    	}
    	echo json_encode($retData);
    }

    public function actionEdit()
    {
    	Permission::havePermission(Permission::EDIT_ROLE);
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    	
    	$roleId = $request->post('roleId', null);
    	$roleName = $request->post('name', null);
    	$description = $request->post('description', null);
    	
    	$roleName = trim($roleName);
    	$description = trim($description);
    	
    	$model = AuthItem::findOne($roleId);
    	$isDelete = $this->isDelete($model);
    	$retData['success'] = false;
    	if($isDelete){
    		$retData['isDelete'] = true;
    	}else{
    		$retData['isDuplicate'] = $this->isDuplicate($roleName, $roleId);
    		 
    		if(!$retData['isDuplicate']){
    			if($roleId != null){
    				$model->name = $roleName;
    				$model->description = $description;
    			}
    			if($model->save()){
    				$retData['success'] = true;
    			}
    		}
    	}
    	
    	echo json_encode($retData);
    }

    /**
     * Deletes an existing Role model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $_id
     * @return mixed
     */
    public function actionDelete()
    {
    	Permission::havePermission(Permission::DELETE_ROLE);
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$roleId = $request->post('roleId', null);
    	$name = $request->post('name', null); 
    	
    	$model = AuthItem::findOne($roleId);
    	$isActiveflag = $this->isActiveflag($model);
    	$isDelete = $this->isDelete($model);
    	$retData['success'] = false;
    	
    	if($isDelete){
    		$retData['isDelete'] = true;
    	}else if ($isActiveflag){
    		$retData['isActiveflag'] = true;
    	}else{
    		if($model->delete()){
				$this->deleteRoleInPermission($name);
				$this->deleteUserInRole($name);
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
     * Finds the Role model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $_id
     * @return Role the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Role::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionGeteditrole()
    {
    	if (Yii::$app->request->isAjax) {
    		$data = Yii::$app->request->post();
    		$roleId = explode(":", $data['roleId']);
    		$roleId = $roleId[0];
    		
    		$role = AuthItem::findAuthItemById($roleId);
    		$isDelete = $this->isDelete($role);
    		
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		return [
    				'roleData' => $role,
    				'code' => 100,
    				'isDelete' => $isDelete,
    		];
    	}
    }
	
	public function isDuplicate($name, $roleId)
	{
		$condition = [];
		$query = AuthItem::find();
	
		if(!empty($name)){
			$conditions['name'] = $name;
		}
	
		if(!empty($conditions)){
			$query->where($conditions);
		}
		 
		if($roleId != null){
			$query->andWhere(['<>', '_id', new ObjectID($roleId)]);
		}
	
		$listRole = $query->all();
	
		if($listRole != null){
			return true;
		}else{
			return false;
		}
	}
	
	public function actionChangeactiveflag(){
	
		Permission::havePermission(Permission::CHANGE_STATUS_ROLE);
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
	
		$roleId = $request->post('roleId', null);
		$activeFlag = $request->post('activeFlag', null);
		 
		$model = AuthItem::findOne($roleId);
		$isDelete = $this->isDelete($model);
		$retData['success'] = false;
		
		if($isDelete){
			$retData['isDelete'] = true;
		}else{
			$model->activeFlag = ((int)$activeFlag == ActiveFlag::ACTIVE)? ActiveFlag::INACTIVE : ActiveFlag::ACTIVE ;
			if($model->save()){
				$message = true;
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
	
	public function isUsedInAuthassignment($name){
		$model = AuthAssignment::findAllAuthAssignmentByRole($name);
		if($model == null){
			return false;
		}else{
			return true;
		}
	}
	
	public function actionAdd(){
		Permission::havePermission(Permission::MANAGEMENT_ROLE);
		$request = Yii::$app->request;
		$roleName = $request->post('id',null);
		$module = $request->post('module',null);
		$permissionName = $request->post('permissionName',null);
		$length = (int)$request->post('per-page',null);
		
		if(empty($roleName)){
			$roleName = $request->get('id',null);
		}
		
		if(empty($module)){
			$name = $request->get('module',null);
		}
		
		if(empty($permissionName)){
			$department = $request->get('permissionName',null);
		}
		
		if(empty($length)){
			$defaultLength = 10;
			$length = $request->get('per-page',$defaultLength);
		}
		
		$permissionName = trim($permissionName);
		
		// redirect to role index if variable roleName equal null
		if($roleName == null || $roleName == User::SUPER_ADMIN){
			return Yii::$app->getResponse()->redirect('index');
		}	
		
		$query = AuthItem::findOne(['name'=>$roleName]);
		//check if have role continuous else redirect
		if($query == null){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'บทบาทนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-danger']
			]);
			return Yii::$app->getResponse()->redirect('index');
		}
		 
		//check if active continuous else redirect
		if($query->activeFlag == ActiveFlag::INACTIVE){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'บทบาทนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-warning']
			]);
			return Yii::$app->getResponse()->redirect('index');
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
						'permissionName' => SORT_ASC
				],
				'attributes' => [
						'permissionName' => [
								'asc' => ['permissionName' => SORT_ASC],
								'desc' => ['permissionName' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'ชื่อสิทธิ์',
						],
						'module' => [
								'asc' => ['module' => SORT_ASC],
								'desc' => ['module' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'โมดูล',
						]
				],
		]);
		 
		$dataTablesSort->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'id' => $roleName,
				'permissionName' => $permissionName,
				'module' => $module
		];
		
		$query = AuthItem::find();
		$query->where(["type" => 2,"parents"=> $roleName]);
		
		if(!empty($module)){
			$query->andWhere(['module'=>$module]);
		}
		
		if(!empty($permissionName)){
			$query->andWhere(['like', "permissionName", $permissionName]);
		}
		
		$pagination = new Pagination([
				'totalCount' => $query->count(),
				'defaultPageSize' => 10
		]);
		
		$pagination->setPageSize($length);
		$query->offset($pagination->offset);
		$query->orderBy($dataTablesSort->orders);
		$query->limit($pagination->limit);
		
		$listRoleType = $query->all();
		$pagination->params = [
				'page'=> $pagination->page,
				'sort'=>$sort,
				'per-page'=>$length,
				'id' => $roleName,
				'permissionName' => $permissionName,
				'module' => $module
		];
		
		$role = AuthItem::findOne(["name" => $roleName]);
		
		return $this->render('add', [
				"role" => $role,
				"roleName" => $roleName,
				"listRoleType" => $listRoleType,
				"permissionName" => $permissionName,
				"module" => $module,
				"pagination" => $pagination,
				"dataTablesLength" => $dataTablesLength,
				"length" => $length,
				"sort" => $sort,
				"dataTablesSort" => $dataTablesSort,
				"page" => $page
		]);
	}
	
	public function actionPermission(){
		$request = Yii::$app->request;
		$roleName = $request->post('id',null);
		$module = $request->post('module',null);
		$permissionName = $request->post('permissionName',null);
		$length = (int)$request->post('per-page',null);
		
		if(empty($roleName)){
			$roleName = $request->get('id',null);
		}
		
		if(empty($module)){
			$name = $request->get('module',null);
		}
		
		if(empty($permissionName)){
			$department = $request->get('permissionName',null);
		}
		
		if(empty($length)){
			$defaultLength = 10;
			$length = $request->get('per-page',$defaultLength);
		}
		
		$permissionName = trim($permissionName);
		
		// redirect to role index if variable roleName equal null
		if($roleName == null || $roleName == User::SUPER_ADMIN){
			return Yii::$app->getResponse()->redirect('index');
		}
		
		$query = AuthItem::findOne(['name'=>$roleName]);
		//check if have role continuous else redirect
		if($query == null){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'บทบาทนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-danger']
			]);
			return Yii::$app->getResponse()->redirect('index');
		}
			
		//check if active continuous else redirect
		if($query->activeFlag == ActiveFlag::INACTIVE){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'บทบาทนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-warning']
			]);
			return Yii::$app->getResponse()->redirect('index');
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
						'permissionName' => SORT_ASC
				],
				'attributes' => [
						'permissionName' => [
								'asc' => ['permissionName' => SORT_ASC],
								'desc' => ['permissionName' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'ชื่อสิทธิ์',
						],
						'module' => [
								'asc' => ['module' => SORT_ASC],
								'desc' => ['module' => SORT_DESC],
								'default' => SORT_DESC,
								'label' => 'โมดูล',
						]
				],
		]);
		 
		$dataTablesSort->params = [
				'page'=> $page,
				'sort'=>$sort,
				'per-page'=>$length,
				'id' => $roleName,
				'permissionName' => $permissionName,
				'module' => $module
		];
		
		$listRole = AuthItem::findAll(["parents" => $roleName]);
		$query = AuthItem::find();
		$query->where(["type" => 2]);
		if ($listRole != null){
			foreach ($listRole as $obj){
				$query->andWhere(['not in','_id', new ObjectID($obj->_id)]);
			}
		}
		
		if(!empty($module)){
			$query->andWhere(['module'=>$module]);
		}
		
		if(!empty($permissionName)){
			$query->andWhere(['like', "permissionName", $permissionName]);
		}
		
		$pagination = new Pagination([
				'totalCount' => $query->count(),
				'defaultPageSize' => 10
		]);
		
		$pagination->setPageSize($length);
		$query->offset($pagination->offset);
		$query->orderBy($dataTablesSort->orders);
		$query->limit($pagination->limit);
		
		$listPermissionWithoutRole = $query->all();
		$pagination->params = [
				'page'=> $pagination->page,
				'sort'=>$sort,
				'per-page'=>$length,
				'id' => $roleName,
				'permissionName' => $permissionName,
				'module' => $module
		];
		
		$role = AuthItem::find()->where(["type" => 1])->all();
		return $this->render('permission', [
				"roleName" => $roleName,
				"role" => $role,
				"listPermissionWithoutRole" => $listPermissionWithoutRole,
				"permissionName" => $permissionName,
				"module" => $module,
				"pagination" => $pagination,
				"dataTablesLength" => $dataTablesLength,
				"length" => $length,
				"sort" => $sort,
				"dataTablesSort" => $dataTablesSort,
				"page" => $page
		]);
	}
	
	public function actionRemovepermission(){
		Permission::havePermission(Permission::REMOVE_PERMISSION);
		$request = Yii::$app->request;
		$roleName = $request->post('role', null);
		$data = $request->post('data', null);
		$data = json_decode($data);
		
		$model = AuthItem::findOne(['name'=>$roleName]);
		$isDelete = $this->isDelete($model);
		$retData['isDelete'] = $isDelete;
		$retData['success'] = false;
		
		if($isDelete){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'บทบาทนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-danger']
			]);
		}else{
			if($model->activeFlag == ActiveFlag::INACTIVE){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'บทบาทนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-warning']
				]);
				$retData['isClose'] = true;
			}else{
				$numberPermission = sizeof($data);
				$arr = array();
				for ($i = 0; $i < $numberPermission; $i++) {
					$model = AuthItem::findOne($data[$i]->permissionId);
					$numberParent = sizeof($model->parents);
					$array = $model->parents;
					$index = array_search($roleName,$array);
					array_splice($array,$index);
					$model->parents = $array;
					$model->save();
				}
				$retData['success'] = true;
			}
		}
		
		echo json_encode($retData);
	}
	
	public function actionAddpermission(){
		Permission::havePermission(Permission::ADD_PERMISSION);
		$request = Yii::$app->request;
		$roleName = $request->post('role', null);
		$data = $request->post('data', null);
		$data = json_decode($data);
		
		$model = AuthItem::findOne(['name'=>$roleName]);
		$isDelete = $this->isDelete($model);
		$retData['isDelete'] = $isDelete;
		$retData['success'] = false;
		
		if($isDelete){
			Yii::$app->getSession()->setFlash('alert',[
					'body'=>'บทบาทนี้ถูกลบโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-danger']
			]);
		}else{
			if($model->activeFlag == ActiveFlag::INACTIVE){
				Yii::$app->getSession()->setFlash('alert',[
					'body'=>'บทบาทนี้ถูกปิดใช้งานโดยผู้ใช้ท่านอื่นแล้ว',
					'options'=>['class'=>'alert-warning']
				]);
				$retData['isClose'] = true;
			}else{
				$numberPermission = sizeof($data);
				for ($i = 0; $i < $numberPermission; $i++) {
					$model = AuthItem::findOne($data[$i]->itemId);
					$listPermission = $model->parents;
					array_push($listPermission, $roleName);
					$model->parents = $listPermission;
					$model->save();
				}
				$retData['success'] = true;
			}
		}
		echo json_encode($retData);
	}
	
	public function deleteRoleInPermission($roleName){
		$query = AuthItem::find(['type'=>2]);
	    $query->andWhere(['parents'=>$roleName]);
	    $model = $query->all();
	     
	    foreach ($model as $obj){
	    	$permissionData = AuthItem::findOne(['name'=>$obj->name]);
	    	$array = $permissionData->parents;
	    	$index = array_search($roleName,$array);
	    	array_splice($array,$index);
	    	$permissionData->parents = $array;
	    	$permissionData->save();
	    }
	}
	
	public function deleteUserInRole($roleName){
		$model = new AuthAssignment();
		$model->deleteAll(['item_name'=>$roleName]);
	}
	
	public function actionDuplicate(){
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$roleId = explode(":", $data['roleId']);
			$roleId = $roleId[0];
			$roleName = explode(":", $data['roleName']);
			$roleName = $roleName[0];
		
			$isDuplicate = $this->isDuplicate($roleName, $roleId);
		
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'isDuplicate' => $isDuplicate,
					'code' => 100
			];
		}
	}
}
