<?php

namespace backend\controllers;

use Yii;
use backend\models\Role;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \MongoDate;
use MongoDB\BSON\ObjectID;
use backend\models\AuthItem;
use common\models\User;
use backend\models\AuthAssignment;
use backend\models\Department;
use yii\helpers\ArrayHelper;
use common\libs\DateTime;
use common\libs\Permission;
use yii\filters\AccessControl;
use common\libs\ActiveFlag;
use yii\data\Pagination;
use yii\data\Sort;
use common\models\Employee;

class AssignController extends Controller
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
    	Permission::havePermission(Permission::SEARCH_ASSIGN);
    	
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
	
    public function actionManagement()
    {
    	Permission::havePermission(Permission::MANAGEMENT_ASSIGN);
    	$request = Yii::$app->request;
    	$roleName = $request->post('id',null);
    	$name = $request->post('name',null);
    	$department = $request->post('department',null);
    	$length = (int)$request->post('per-page',null);
    	
    	if(empty($roleName)){
    		$roleName = $request->get('id',null);
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
    			'id' => $roleName,
    			'name' => $name,
    			'department' => $department
    	];
    	
    	$role = AuthItem::findOne(["name" => $roleName]);
    	$listUserInRole = AuthAssignment::findAll(["item_name" => $roleName]);
    	
    	$arrUserId = [];
    	foreach ($listUserInRole as $obj){
    		$arrUserId[] = new ObjectID($obj->user_id);
    	}
		
    	$query = User::find();
		$query->where(['in', '_id', $arrUserId]);
		
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
		
		$listCategory = $query->all();
		$pagination->params = [
				'page'=> $pagination->page,
				'sort'=>$sort,
				'per-page'=>$length,
				'id' => $roleName,
				'name' => $name,
				'department' => $department
		];
		
		$listUserInRole = $query->all();
    	
    	$departmentModel = new Department();
    	$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
    	
    	return $this->render('assignManagement', [
    			"role" => $role,
    			"listUserInRole" => $listUserInRole,
    			"listDepartment" => $listDepartment,
    			"roleName" => $roleName,
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
    	Permission::havePermission(Permission::ADD_MEMBER_ASSIGN);
    	$request = Yii::$app->request;
    	$roleName = $request->post('id',null);
    	$name = $request->post('name',null);
    	$department = $request->post('department',null);
    	$length = (int)$request->post('per-page',null);
    	
    	if(empty($roleName)){
    		$roleName = $request->get('id',null);
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
    			'id' => $roleName,
    			'name' => $name,
    			'department' => $department
    	];
    	
    	
    	$query = User::find();
    	$listUserInRole = AuthAssignment::findAll(["item_name" => $roleName]);
    	if($listUserInRole != null){
    		$arrUserInRole = [];
    		foreach ($listUserInRole as $obj){
    			$arrUserInRole[] = $obj->user_id;
    		}
    		
    		$query->andwhere(['not in','_id' , $arrUserInRole]);
    	}
    	
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
    	 
    	$listCategory = $query->all();
    	$pagination->params = [
    			'page'=> $pagination->page,
    			'sort'=>$sort,
    			'per-page'=>$length,
    			'id' => $roleName,
    			'name' => $name,
    			'department' => $department
    	];
    	
    	$listUserWithoutRole = $query->all();
    	
    	$departmentModel = new Department();
    	$listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
    	
    	return $this->render('assign', [
    		"roleName" => $roleName,
    		"listUserWithoutRole" => $listUserWithoutRole,
    		"listDepartment" => $listDepartment,
    		"name" => $name,
    		"department" => $department,
    		"pagination" => $pagination,
    		"dataTablesLength" => $dataTablesLength,
    		"length" => $length,
    		"sort" => $sort,
    		"dataTablesSort" => $dataTablesSort,
    		"page" => $page
    	]);
    }
    
    public function actionAddmember(){
    	Permission::havePermission(Permission::ADD_MEMBER_ASSIGN);
    	$request = Yii::$app->request;
    	$roleName = $request->post('role', null);
    	$data = $request->post('data', null);
    	
    	$retData['success'] = false;
    	$model = AuthItem::findOne(['name'=>$roleName]);
    	$isDeleted = $this->isDelete($model);
    	$retData['isDelete'] = $isDeleted;
    	
    	if($isDeleted){
    		$retData['isDelete'] = true;
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
    			$data = json_decode($data);
    			$nummberMember = sizeof($data);
    			 
    			for ($i = 0; $i < $nummberMember; $i++) {
    				$model = new AuthAssignment();
    				$model->user_id = $data[$i]->userId;
    				$model->item_name = $roleName;
    				$model->created_at = DateTime::SecNowDate();
    				$model->save();
    			}
    			$retData['success'] = true;
    		}
    	}
    	
    	echo json_encode($retData);
    }
    
    public function actionRemovemember(){
    	Permission::havePermission(Permission::REMOVE_MEMBER_ASSIGN);
    	$request = Yii::$app->request;
    	$roleName = $request->post('role', null);
    	$data = $request->post('data', null);
    	
    	$retData['success'] = false;
    	$model = AuthItem::findOne(['name'=>$roleName]);
    	$isDeleted = $this->isDelete($model);
    	$retData['isDelete'] = $isDeleted;
    	
    	if($isDeleted){
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
    			$data = json_decode($data);
    			$nummberMember = sizeof($data);
    			
    			$condition = [];
    			$condition['item_name'] =  $roleName;
    			 
    			for ($i = 0; $i < $nummberMember; $i++) {
    				$condition['user_id'] = $data[$i]->userId;
    				AuthAssignment::deleteAll($condition);
    			}
    			
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
    
}
