<?php

namespace backend\controllers;

use Yii;
use backend\models\EventType;
use backend\models\EventTypeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\backend\models;
use \MongoDate;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use MongoDB\BSON\ObjectID;
use backend\models\Event;
use common\models\User;
use common\libs\Status;
use common\libs\ActiveFlag;
use yii\mongodb\ActiveRecord;
use yii\base\Object;
use backend\models\AuthItem;


/**
 * EventController implements the CRUD actions for Event model.
 */
class EventtypeController extends Controller
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
     * Lists all Event models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new EventType();
        $collection = Yii::$app->mongodb->getCollection('eventType');       
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $queryeventType = EventType::find()->all();
        	
        	$querytype_name = EventType::find();
        	$querytype_name->where(['Calendar' => ['1','2','3','4']]);
        	$querytype_name = $querytype_name->all();       	
        	
         	$type_Name = $collection->distinct('Type_name');

        
        return $this->render('indexeventtype', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        	'valueeventType'=> $queryeventType,
        	
        	'type_Name'=> $type_Name,
        	'querytype_name'=> $querytype_name,

        ]);
    }
    
    public function distinctEventType()
    {
    	return $this->render('view', [
    			'model' => $this->findModel($id),
    	]);
    }

    public function actionSave(){
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    	$typeId = $request->post('TypeId', null);
    	$Type_name = $request->post('Type_name', null);
    	$Color = $request->post('Color', null);
    	$activeFlag = 1;
    	$status = 1;
    	$Calendar1 = $request->post('Calendar1', null);
    	$Calendar2 = $request->post('Calendar2', null);
    	$Calendar3 = $request->post('Calendar3', null);
    	// break if category name is duplicate
    	$retData['success'] = false;
    	$retData['isDuplicate'] = $this->isDuplicate($Type_name, $typeId);
    	$retData['isDuplicateColor'] = $this->isDuplicateColor($Color);
    	$model = new EventType();
    	if((!$retData['isDuplicate'])&&(!$retData['isDuplicateColor'])){
    		$model = null; 
    			if ($model == null){
//     				$model = new EventType();
    		if($Calendar1 == 1){
    			$CalendarIndi = 1;
    				$model = new EventType();
    				$model->Type_name = $Type_name;
    				$model->Color =  $Color;
    				$model->Calendar =  (string)$CalendarIndi;
    				$model->activeFlag = $activeFlag;
    				$model->status = $status;
    				$model->save();
    		}
    			if($Calendar2 == 1){
    				$CalendarDep = 2;
    				$model = new EventType();
    				$model->Type_name = $Type_name;
    				$model->Color =  $Color;
    				$model->Calendar =  (string)$CalendarDep;
    				$model->activeFlag = $activeFlag;
    				$model->status = $status;
    				$model->save();
    		}
    		
    			if($Calendar3 == 1){
    				$CalendarPro = 3;
    				$model = new EventType();
    				$model->Type_name = $Type_name;
    				$model->Color =  $Color;
    				$model->Calendar =  (string)$CalendarPro;
    				$model->activeFlag = $activeFlag;
    				$model->status = $status;
    				$model->save();
    				
    		}
    	 
    			$retData['success'] = true;
    
    	}
    }
    	echo json_encode($retData);
    
    }
    
    
    /**
     * Displays a single Event model.
     * @param integer $_id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Event model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    
    public function isDuplicate($Type_name, $typeId){
    	$condition = [];
    	$query = EventType::find();
    
    	if(!empty($Type_name)){
    		$conditions['Type_name'] = $Type_name;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    
    	if($typeId != null){
    		$query->andWhere(['<>', '_id', new ObjectID($typeId)]);
    	}
    
    	$listEventtype = $query->all();
    
    	if($listEventtype != null){
    		return true;
    	}else{
    		return false;
    	}
    }
    
    public function isDuplicateColor($Color){
    	$condition = [];
    	$query = EventType::find();
    
    	if(!empty($Color)){
    		$conditions['Color'] = $Color;
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	
    	$listEventtypeColor = $query->all();
    
    	if($listEventtypeColor != null){
    		return true;
    	}else{
    		return false;
    	}
    }
    
    public function isActiveflag($model)
    {
    	return $model->status == Status::OPEN;
    }
    public function actionChangeactiveflag(){
    
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    
    	$Type_name = $request->post('Type_name', null);
    	
    	$status = $request->post('status', null);
    
    	$query = EventType::find();
    	$model = $query->where(['Type_name'=>$Type_name])->all();
    	
    	if($model != null){
    		foreach ($model as $obj){
    		$obj->status = ((int)$status == Status::OPEN)? Status::CLOSE : Status::OPEN ;
    		$obj->save();
    		}
    	}
    	$retData['test'] = $model;
    	$retData['success'] = true;

    	echo json_encode($retData);
    }
    
    
    public function actionCreate()
    {
        $model = new EventType();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => (string)$model->_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Event model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $_id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => (string)$model->_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    public function findAllITypeUsedByID($TypeId){
    	$conditions = [];
    	$query = Event::find();
    
    	if(!empty($TypeId)){
    		$conditions['TypeID'] = new ObjectID($TypeId);
    	}
    
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	$listTypeUsedByID = $query->all();
    	return $listTypeUsedByID;
    }

    /**
     * Deletes an existing Event model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $_id
     * @return mixed
     */
    public function actionDelete()
    {
        $request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$TypeId= $request->post('TypeId', null);
    	$model = EventType::findOne($TypeId);
    	$isDelete = $this->isDelete($model);
    	$retData['success'] = false;
    	$isTypeUsedByID = $this->findAllITypeUsedByID($TypeId);
    	$retData['model'] = $isTypeUsedByID;
    	$retData['isDelete'] = $isDelete;
    	if($isDelete){
    		$retData['isDelete'] = $isDelete;
    	}
    	else if ($isTypeUsedByID){
    		$model->activeFlag =  ActiveFlag::INACTIVE;
    		$model->save();
    		//$retData['isTypeUsedByID'] = true;
    		$retData['success'] = true;
    	}
    	else{
    		if($model->delete()){
    			$message = true;
    			$retData['success'] = true;
    		}else{
    			$message = false;
    			$retData['success'] = false;
    		}
    	}
    	echo json_encode($retData);
    }
    
    public function isDelete($model){
    	if($model == null){
    		return true;
    	}else{
    		return false;
    	}
    }
    
    public function actionEdit()
    {
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    	
    	$typeId = $request->post('TypeId', null);
    	$Type_name = $request->post('Type_name', null);
    	$Type_nameEdit = $request->post('typeEdit', null);
    	$Type_colorEdit = $request->post('colorEdit', null);
    	$Color = $request->post('Color', null);
    	$Calendar1 = $request->post('Calendar1', null);
    	$Calendar2 = $request->post('Calendar2', null);
    	$Calendar3 = $request->post('Calendar3', null);
    	
    	$query = EventType::find(); 	
    	$query->where(['Type_name'=>$Type_name]);
    	$model = $query->all();
    	$retData['success'] = false;
    	$retData['model'] = $model;
    	$retData['isDuplicate'] = false;
    	$retData['isDuplicateColor'] = false;
    	
    	if($Type_nameEdit != $Type_name){
    		$retData['isDuplicate'] = $this->isDuplicate($Type_name, $typeId);
    	}
    	
    	if($Type_colorEdit!= $Color){
    		$retData['isDuplicateColor'] = $this->isDuplicateColor($Color);
    	}
    	
    	if((!$retData['isDuplicate'])&&(!$retData['isDuplicateColor'])){
    		$CalendarIndi = 1;
    		$query = EventType::findOne(['Type_name'=>$Type_nameEdit, 'Calendar'=>(string)$CalendarIndi]);
    		if($Calendar1 == 1){
    			if($query != null){
    				$query->activeFlag =  ActiveFlag::ACTIVE;
    				$query->Type_name = $Type_name;
    				$query->Color = $Color;
    				$query->save();
    			}else{
    				$model = new EventType();
    				$model->Type_name = $Type_name;
    				$model->Color =  $Color;
    				$model->Calendar =  (string)$CalendarIndi;
    				$model->activeFlag = ActiveFlag::ACTIVE;
    				$model->status = Status::OPEN;
    				$model->save();
    			}
    		}else{
    			if($query != null){
    				$query->activeFlag =  ActiveFlag::INACTIVE;
    				$query->Type_name = $Type_name;
    				$query->Color = $Color;
    				$query->save();
    			}
    		}
    		 
    		$CalendarDep = 2;
    		$query = EventType::findOne(['Type_name'=>$Type_nameEdit, 'Calendar'=>(string)$CalendarDep]);
    		if($Calendar2 == 1){
    			if($query != null){
    				$query->activeFlag =  ActiveFlag::ACTIVE;
    				$query->Type_name = $Type_name;
    				$query->Color = $Color;
    				$query->save();
    			}else{
    				$model = new EventType();
    				$model->Type_name = $Type_name;
    				$model->Color =  $Color;
    				$model->Calendar =  (string)$CalendarDep;
    				$model->activeFlag = ActiveFlag::ACTIVE;
    				$model->status = Status::OPEN;
    				$model->save();
    			}
    		}else{
    			if($query != null){
    				$query->activeFlag =  ActiveFlag::INACTIVE;
    				$query->Type_name = $Type_name;
    				$query->Color = $Color;
    				$query->save();
    			}
    		}
    		 
    		$CalendarPro = 3;
    		$query = EventType::findOne(['Type_name'=>$Type_nameEdit, 'Calendar'=>(string)$CalendarPro]);
    		if($Calendar3 == 1){
    			if($query != null){
    				$query->activeFlag =  ActiveFlag::ACTIVE;
    				$query->Type_name = $Type_name;
    				$query->Color = $Color;
    				$query->save();
    			}else{
    				$model = new EventType();
    				$model->Type_name = $Type_name;
    				$model->Color =  $Color;
    				$model->Calendar =  (string)$CalendarPro;
    				$model->activeFlag = ActiveFlag::ACTIVE;
    				$model->status = Status::OPEN;
    				$model->save();
    			}
    		}
    		else{
    			if($query != null){
    				$query->activeFlag =  ActiveFlag::INACTIVE;
    				$query->Type_name = $Type_name;
    				$query->Color = $Color;
    				$query->save();
    			}
    		}
    		 
    		if($query->save()){
    			$message = true;
    			$retData['success'] = true;
    		}else{
    			$message = false;
    			$retData['success'] = false;
    		}
    	}

    
    	echo json_encode($retData);
    }
    

    /**
     * Finds the Event model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $_id
     * @return Event the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = EventType::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    public function beforeAction($action) {
    	$this->enableCsrfValidation = false;
    	return parent::beforeAction($action);
    }
    
    
    public function actionGetedit()
    {
    	if (Yii::$app->request->isAjax) {
    		$data = Yii::$app->request->post();
    		$typeId = explode(":", $data['typeId']);
    		$typeId = $typeId[0];
    		
    		$typeName = explode(":", $data['typeName']);
    		$typeName = $typeName[0];
    		
    		$Name = EventType::find();
    		$Name->where(['Type_name' => $typeName, 'activeFlag'=>ActiveFlag::ACTIVE]);
    		$Name = $Name->all();
    		
    		$type = EventType::findOne($typeId);
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		return [
    				'typeData' => $type,
    				'typeName' => $Name,
    				'code' => 100,
    		];
    	}
    }
}
