<?php

namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use backend\models\Category;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \MongoDate;
use MongoDB\BSON\ObjectID;
use common\models\User;
use common\libs\Status;
use common\libs\Permission;
use backend\models\Project;
use common\libs\DateTime;
use common\libs\ActiveFlag;
use common\models\Employee;
use yii\data\Pagination;
use yii\data\Sort;
use backend\models\Department;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class MergeController extends Controller
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
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
    	$request = Yii::$app->request;
    	$query = Employee::find();
    	
    	$defaultLength = 10;
    	
    	$dataTablesLength = [
    		10 => "10",
    		20 => "20",
    		30 => "30",
    		50 => "50",
    		100 => "100"
    	];
    	
    	$dataTablesSort = new Sort([
    			'defaultOrder' => [
    				'name' => SORT_ASC
    			],
    			'attributes' => [
    					'name' => [
    						'asc' => ['nameTh' => SORT_ASC, 'sernameTh' => SORT_ASC],
    						'desc' => ['nameTh' => SORT_DESC, 'sernameTh' => SORT_DESC],
    						'default' => SORT_DESC,
    						'label' => 'ชื่อพนักงาน',
    					],
    					'department' => [
    						'asc' => ['depName' => SORT_ASC],
    						'desc' => ['depName' => SORT_DESC],
    						'label' => 'แผนก'
    					],
    					'company' => [
    						'asc' => ['companyName' => SORT_ASC],
    						'desc' => ['companyName' => SORT_DESC],
    						'label' => 'บริษัท'
    					]
    			],
    	]);
    	
    	$length = $request->get('per-page',$defaultLength);
    	$page = $request->get('page',1);
    	$sort = $request->get('sort','name');
    	
    	$pagination = new Pagination([
    		'totalCount' => $query->count(),
    		'defaultPageSize' => 10
    	]);
    	$pagination->setPageSize($length);
    	$query->offset($pagination->offset);
    	$query->orderBy($dataTablesSort->orders);
    	$query->limit($pagination->limit);
    	
    	$model = $query->all();
    	$pagination->params = ['page'=> $pagination->page,'sort'=>$sort, 'per-page'=>$length];
    	
    	foreach ($model as $obj){
    		$dataTemp = [];
    		$dataTemp['id'] = (string)$obj->_id;
    		$dataTemp['name'] = $obj->nameTh." ".$obj->sernameTh;
    		$dataTemp['firstname'] = $obj->nameTh;
    		$dataTemp['lastname'] = $obj->sernameTh;
    		$dataTemp['nameEn'] = $obj->nameEn;
    		$dataTemp['department'] = $obj->depName;
    		$dataTemp['companyName'] = $obj->companyName;
    		$data[] = $dataTemp;
    	}
    	
    	
        return $this->render('index', [
          	'model' => $model,
        	"pagination" => $pagination,
        	"dataTablesLength" => $dataTablesLength,
        	"length" => $length,
        	"sort" => $sort,
        	"dataTablesSort" => $dataTablesSort,
        	"page" => $page,
        ]);
    }
    
    public function actionSave(){
    	$request = Yii::$app->request;
    	$listEmployee = $request->post('employee', null);
    	$listEmployee = json_decode($listEmployee);
    	$password = "password";
    	$passwordHash = Yii::$app->security->generatePasswordHash($password);
    	$user = [];
    	foreach ($listEmployee as $o){
    		$obj = Employee::findOne($o->employeeId);
    		$user = new User();
    		$user->username = $obj->empCode;
    		$user->password_hash = $passwordHash;
    		$user->auth_key = Yii::$app->security->generateRandomString();
    		$user->amountofproject = 0;
    		$user->limit = User::UNLIMIT;
    		$user->avatar = "";
    		
    		$user->empCode = $obj->empCode;
    		$user->createTime = $obj->createTime;
    		$user->createBy = $obj->createBy;
    		$user->nameTh = $obj->nameTh;
    		$user->sernameTh = $obj->sernameTh;
    		$user->nameEn = $obj->nameEn;
    		$user->sernamemEn = $obj->sernamemEn;
    		$user->sex = $obj->sex;
    		$user->email = $obj->email;
    		$user->positionId = $obj->positionId;
    		$user->positionName = $obj->positionName;
    		$user->divCode = $obj->divCode;
    		$user->divName = $obj->divName;
    		$user->depCode = $obj->depCode;
    		$user->depName = $obj->depName;
    		$user->sectionName = $obj->sectionName;
    		$user->companyCode = $obj->companyCode;
    		$user->companyName = $obj->companyName;
    		$user->officePhone = $obj->officePhone;
    		$user->birthday = $obj->birthday;
    		$user->beginDate = $obj->beginDate;
    		$user->resignDate = $obj->resignDate;
    		$user->lastUpdateTime = $obj->lastUpdateTime;
    		$user->lastUpdateBy = $obj->lastUpdateBy;
    		$user->save();
    	}
    	
    	$retData['success'] = json_encode($user);
    	 
    	echo json_encode($retData);
    }
	
    public function actionMergedepartment(){
    	$model = Employee::find()->all();
    	$department = [];
    	foreach ($model as $obj){
    		$department[] = $obj->depName;
    	}
    	$department = array_unique($department);
    	foreach ($department as $departmentName){
    		$obj = Employee::findOne(['depName' => $departmentName]);
    		if($departmentName != ""){
	    		$model = new Department();
	    		$model->depCode = $obj->depCode;
	    		$model->depName = $departmentName;
	    		$model->save();
    		}
    	}
    	return Yii::$app->getResponse()->redirect('index');
    }
    
    public function actionMergeemployee(){
    	$model = Employee::find()->all();
    	$password = "password";
    	$passwordHash = Yii::$app->security->generatePasswordHash($password);
    	foreach ($model as $obj){
    		$user = new User();
    		$user->username = $obj->empCode;
    		$user->password_hash = $passwordHash;
    		$user->auth_key = Yii::$app->security->generateRandomString();
    		$user->amountofproject = 0;
    		$user->limit = User::UNLIMIT;
    		$user->avatar = "";
    		
    		$user->empCode = $obj->empCode;
    		$user->createTime = $obj->createTime;
    		$user->createBy = $obj->createBy;
    		$user->nameTh = $obj->nameTh;
    		$user->sernameTh = $obj->sernameTh;
    		$user->nameEn = $obj->nameEn;
    		$user->sernamemEn = $obj->sernamemEn;
    		$user->sex = $obj->sex;
    		$user->email = $obj->email;
    		$user->positionId = $obj->positionId;
    		$user->positionName = $obj->positionName;
    		$user->divCode = $obj->divCode;
    		$user->divName = $obj->divName;
    		$user->depCode = $obj->depCode;
    		$user->depName = $obj->depName;
    		$user->sectionName = $obj->sectionName;
    		$user->companyCode = $obj->companyCode;
    		$user->companyName = $obj->companyName;
    		$user->officePhone = $obj->officePhone;
    		$user->birthday = $obj->birthday;
    		$user->beginDate = $obj->beginDate;
    		$user->resignDate = $obj->resignDate;
    		$user->lastUpdateTime = $obj->lastUpdateTime;
    		$user->lastUpdateBy = $obj->lastUpdateBy;
    	}
    }
}
