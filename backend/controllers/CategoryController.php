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

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends Controller
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
    	Permission::havePermission(Permission::SEARCH_CATEGORY);
    	$request = Yii::$app->request;
    	$categoryName = $request->post('categoryName',null);
    	$activeFlag = $request->post('activeFlag',null);
    	$length = (int)$request->post('per-page',null);
    	if(empty($categoryName)){
    		$categoryName = $request->get('categoryName',null);
    	}
    	if(empty($activeFlag)){
    		$activeFlag = $request->get('activeFlag',null);
    	}
    	if(empty($length)){
    		$defaultLength = 10;
    		$length = $request->get('per-page',$defaultLength);
    	}
    	
    	$categoryName = trim($categoryName);
    	
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
    					'categoryName' => SORT_ASC
    			],
    			'attributes' => [
    					'categoryName' => [
    							'asc' => ['categoryName' => SORT_ASC],
    							'desc' => ['categoryName' => SORT_DESC],
    							'default' => SORT_DESC,
    							'label' => 'ชื่อประเภทโครงการ',
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
    			'categoryName' => $categoryName,
    			'activeFlag' => $activeFlag
    	];
    	
    	$conditions = [];
    	$query = Category::find();
    	
    	if(!empty($activeFlag)){
    		$conditions['activeFlag'] = (int)$activeFlag;
    	}
    	
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	
    	if(!empty($categoryName)){
    		$query->andWhere(['like', "categoryName", $categoryName]);
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
    			'categoryName' => $categoryName,
    			'activeFlag' => $activeFlag
    	];
    	
        return $this->render('index', [
            'listCategory' => $listCategory,
        	'categoryName' => $categoryName,
        	'activeFlag' => $activeFlag,
        	"pagination" => $pagination,
        	"dataTablesLength" => $dataTablesLength,
       		"length" => $length,
       		"sort" => $sort,
      		"dataTablesSort" => $dataTablesSort,
       		"page" => $page
        ]);
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $_id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function actionGeteditcategory()
    {
    	Permission::havePermission(Permission::EDIT_CATEGORY);
    	if (Yii::$app->request->isAjax) {
    		$data = Yii::$app->request->post();
    		$categoryData = explode(":", $data['categoryId']);
    		$categoryId = $categoryData[0];
    		
    		$model = Category::findOne($categoryId);
    		$isDelete = $this->isDelete($model);
    		
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		return [
    				'categoryData' => $model,
    				'code' => 100,
    				'isDelete' => $isDelete
    		];
    	}
    }
    
    public function actionView()
    {
    	Permission::havePermission(Permission::VIEW_CATEGORY);
    	if (Yii::$app->request->isAjax) {
    		$data = Yii::$app->request->post();
    		$categoryData = explode(":", $data['categoryId']);
    		$categoryId = $categoryData[0];
    		
    		$model = Category::findOne($categoryId);
    		$isDelete = $this->isDelete($model);
    		if(!$isDelete){
    			// set createDate, createBy, activeFlag
    			$model->activeFlag = ActiveFlag::$arrActiveFlag[(int)$model->activeFlag];
    			$model->createBy = User::getUserName((string)$model->createBy);
    			$model->createDate = DateTime::MongoDateToDateCreate($model->createDate["sec"]);
    		}
    		
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		return [
    				'categoryData' => $model,
    				'isDelete' => $isDelete,
    				'code' => 100,
    		];
    	}
    }
    
    public function isDuplicate($categoryName, $categoryId){
    	$isDuplicate = false;
    	
    	$condition = [];
    	$query = Category::find();
    
    	if(!empty($categoryName)){
    		$conditions['categoryName'] = $categoryName;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	
    	if($categoryId != null){
    		$query->andWhere(['<>', '_id', new ObjectID($categoryId)]);
    	}
    	$listProject = $query->all();
    
    	if($listProject != null){
    		$isDuplicate = true;
    	}
    	return $isDuplicate;
    }
    
    public function actionCreate(){
    	Permission::havePermission(Permission::CREATE_CATEGORY);
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    	 
    	$categoryId = $request->post('categoryId', null);
    	$categoryName = $request->post('categoryName', null);
    	$description = $request->post('description', null);
    	
    	$categoryName = trim($categoryName);
    	$description = trim($description);
    	 
    	// break if category name is duplicate
    	$retData['success'] = false;
    	$retData['isDuplicate'] = $this->isDuplicate($categoryName, $categoryId);
    	 
    	if(!$retData['isDuplicate']){
    		$model = new Category();
    		$model->categoryName = $categoryName;
    		$model->description = $description;
    		$model->activeFlag = ActiveFlag::ACTIVE;
    		$model->createDate = new MongoDate();
    		$model->createBy = $currentId;
    		 
    		if($model->save()){
    			$retData['success'] = true;
    		}
    	}
    	echo json_encode($retData);
    }
    
    public function actionEdit(){
    	Permission::havePermission(Permission::EDIT_CATEGORY);
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    	
    	$categoryId = $request->post('categoryId', null);
    	$categoryName = $request->post('categoryName', null);
    	$description = $request->post('description', null);
    	
    	$categoryName = trim($categoryName);
    	$description = trim($description);
    	
    	$model = Category::findOne($categoryId);
    	$retData['isDelete'] = $this->isDelete($model);
    	$retData['success'] = false;
    	if(!$retData['isDelete']){
    		// break if category name is duplicate
    		$retData['isDuplicate'] = $this->isDuplicate($categoryName, $categoryId);
    		 
    		if(!$retData['isDuplicate']){
    			if($categoryId != null){
    				$model->categoryName = $categoryName;
    				$model->description = $description;
    			}
    			 
    			if($model->save()){
    				$retData['success'] = true;
    			}
    		}    		
    	}
    	echo json_encode($retData);
    }
    
    public function actionDelete(){
    	Permission::havePermission(Permission::DELETE_CATEGORY);
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$categoryId = $request->post('categoryId', null);
    	
    	$model = Category::findOne($categoryId);
    	$isDelete = $this->isDelete($model);
    	$isActiveflag = $this->isActiveflag($model);
    	$retData['success'] = false;
    	$isUsedInProject = Project::findAllProjectByCategory(new ObjectID($categoryId));
    	
    	if($isDelete){
    		$retData['isDelete'] = true;
    	}else if ($isActiveflag){
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
    
    public function actionChangeactiveflag(){
    	Permission::havePermission(Permission::CHANGE_STATUS_CATEGORY);
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    
    	$categoryId = $request->post('categoryId', null);
    	$activeFlag = $request->post('activeFlag', null);
    	
    	$model = Category::findOne($categoryId);
    	$retData['isDelete'] = $this->isDelete($model);
    	$retData['success'] = false;
    	if(!$retData['isDelete']){
	    	$model->activeFlag = ((int)$activeFlag == ActiveFlag::ACTIVE)? ActiveFlag::INACTIVE : ActiveFlag::ACTIVE ;
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
    
    public function isUsedInProject($categoryId){
    	$model = Project::findAllProjectByCategory(new ObjectID($categoryId));
    	$isUsed = false;
    	if($model != null){
    		$isUsed = true;
    	}
    	return $isUsed;
    }
    
    public function actionDuplicate(){
    	if (Yii::$app->request->isAjax) {
    		$data = Yii::$app->request->post();
    		$categoryId = explode(":", $data['categoryId']);
    		$categoryId = $categoryId[0];
    		$categoryName = explode(":", $data['categoryName']);
    		$categoryName = $categoryName[0];
    
    		$isDuplicate = $this->isDuplicate($categoryName, $categoryId);
    
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		return [
    				'isDuplicate' => $isDuplicate,
    				'code' => 100
    		];
    	}
    }
}
