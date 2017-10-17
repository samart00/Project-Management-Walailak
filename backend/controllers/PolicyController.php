<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\Policy;
use backend\models\Department;
use backend\models\Project;
use MongoDB\BSON\ObjectID;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use yii\data\Sort;
use yii\filters\AccessControl;

/**
 * PolicyController implements the CRUD actions for User model.
 */
class PolicyController extends Controller
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
    	$request = Yii::$app->request;
    	$department = $request->post('departmentId',null);
		$name = $request->post('name',null);
    	$length = (int)$request->post('per-page',null);
    	if(empty($name)){
    		$name = $request->get('name',null);
    	}
    	if(empty($department)){
    		$department = $request->get('departmentId',null);
    	}
    	if(empty($length)){
    		$defaultLength = 10;
    		$length = $request->get('per-page',$defaultLength);
    	}
    	
    	$name = trim($name);
    	
    	// find max of amountOfProject
        $defaultPolicy = Policy::findOne(["policyName" => "จำนวนโครงการ"]);
		$query = User::find();
		$query->where(['limit'=>User::LIMIT]);
		$query->orderBy(['amountofproject'=>SORT_DESC]);
		$model = $query->one();
		
		if($model != null){
			$maxCountProject = $model->amountofproject;
		}else{
			$maxCountProject = 0;
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
    					],
    					'limit' => [
    							'asc' => ['limit' => SORT_ASC],
    							'desc' => ['limit' => SORT_DESC],
    							'default' => SORT_DESC,
    							'label' => 'จำกัด/ไม่จำกัด',
    					]
    			],
    	]);
    	 
    	$dataTablesSort->params = [
    			'page'=> $page,
    			'sort'=>$sort,
    			'per-page'=>$length,
    			'name' => $name,
    			'department' => $department
    	];
		
		// find user data
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

        $departmentModel = new Department();
        $listDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
        
        return $this->render('index', [
            'model' => $model,
        	'defaultPolicy' =>	$defaultPolicy,
        	'maxCountProject' => $maxCountProject,
        	'department' =>	$department,
        	'name' => $name,
        	'listDepartment'=> $listDepartment,
        	'pagination' => $pagination,
        	'dataTablesLength' => $dataTablesLength,
        	'length' => $length,
        	'sort' => $sort,
        	'dataTablesSort' => $dataTablesSort,
        	'page' => $page
        ]);
    }
    
    
    public function actionSetallamount()
    {
    	if (Yii::$app->request->isAjax) {
    		$data = Yii::$app->request->post();
    		$amontOfProject = explode(":", $data['allAmountOfProject']);
    		$amontOfProject = trim($amontOfProject[0]);
	    	
    		$isSuccess = false;
	    	$defaultPolicy = Policy::findOne(['policyName' => 'จำนวนโครงการ']);
	    	if($defaultPolicy != null){
	    		$defaultPolicy->defaultPolicy = $amontOfProject;
	    		if($defaultPolicy->save()){
	    			$model = User::find(['limit'=>User::UNLIMIT])->all();
	    			if($model != null){
	    				foreach ($model as $value) {
	    					$value->amountofproject = (int)$amontOfProject ;
	    					$value->save();
	    				}
	    			}
	    			$isSuccess = true;
	    		}
	    	}
	    	
	    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
	    	return [
	    			'isSuccess' => $isSuccess,
	    			'amountOfProject' => $amontOfProject
	    	];
    	}
    }
    
    public function actionSetamount()
    {
    	if (Yii::$app->request->isAjax) {
    		$data = Yii::$app->request->post();
    		$amountOfProject = explode(":", $data['amountOfProject']);
    		$userId = explode(":", $data['userId']);
    		$amountOfProject = trim($amountOfProject[0]);
    		$userId = $userId[0];
	    	
	    	$isSuccess = false;
	    	$isLessThan = true;
	    	
	    	$countAmountProject = $this->findNumberOfProjectByUserId($userId);
	    	if($amountOfProject >= $countAmountProject){
	    		$model = User::findOne($userId);
	    		if($model != null){
	    			$model->amountofproject = (int)$amountOfProject;
	    			if($model->save()){
	    				$isSuccess = true;
	    			}
	    		}
	    	}else{
	    		$isLessThan = false;
	    	}
	    	
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		return [
    				'isSuccess' => $isSuccess,
    				'isLessTham' => $isLessThan,
    				'amountOfProject' => $amountOfProject
    		];
    	
    	}

    }
    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $_id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
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
    
    public function actionChangelimit(){
    
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    
    	$userId = $request->post('userId', null);
    	$isLimit = $request->post('isLimit', null);
    	$createProject = $this->findNumberOfProjectByUserId($userId);
    	
    	$retData['success'] = false;
    	$model = User::findOne($userId);
    	if($model != null){
    		$model->limit = ((int)$isLimit == User::LIMIT)? User::UNLIMIT : User::LIMIT ;
    		$model->amountofproject = (int)$createProject;
    	}
    	$retData['activeFlag'] = $model->limit;
    	if($model->save()){
    		$retData['success'] = true;
    	}
    	echo json_encode($retData);
    }
}
