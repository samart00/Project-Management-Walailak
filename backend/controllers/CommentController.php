<?php

namespace backend\controllers;

use Yii;
use backend\models\Project;
use backend\models\Comment;
use backend\models\Task;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \MongoDate;
use MongoDB\BSON\ObjectID;
use common\models\User;
use common\libs\DateTime;
use common\libs\Status;
use common\libs\ActiveFlag;
/**
 * CommentController implements the CRUD actions for Comment model.
 */
class CommentController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Comment models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Comment::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Comment model.
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
     * Creates a new Comment model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionSavecommenttask(){
    	 
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    	$projectId = $request->post('projectId', null);
    	$comment = $request->post('comment', null);
    	$refId = $request->post('refId', null);
    	$isDelete= Task::findOne(["_id"=>new ObjectID($refId)]);
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
    					'body'=>'โครงการนี้ถูกลบแล้วโดยผู้ใช้ท่านอื่นแล้ว',
    					'options'=>['class'=>'alert-danger']
    			]);
    			$retData['isProject'] = true;
    		}else{
    			if($projects['status'] == Status::CANCEL){
    				Yii::$app->getSession()->setFlash('alert',[
    						'body'=>'โครงการนี้ถูกยกเลิกแล้วโดยผู้ใช้ท่านอื่นแล้ว',
    						'options'=>['class'=>'alert-danger']
    				]);
    				$retData['isCancel'] = true;
    			}else if($projects['activeFlag'] == ActiveFlag::INACTIVE){
    				Yii::$app->getSession()->setFlash('alert',[
    						'body'=>'โครงการนี้ถูกปิดใช้งานแล้วโดยผู้ใช้ท่านอื่นแล้ว',
    						'options'=>['class'=>'alert-danger']
    				]);
    				$retData['isClose'] = true;
    			}else if($projects['status'] == Status::CLOSE){
    				Yii::$app->getSession()->setFlash('alert',[
    						'body'=>'โครงการนี้ถูกปิดแล้วโดยผู้ใช้ท่านอื่นแล้ว',
    						'options'=>['class'=>'alert-danger']
    				]);
    				$retData['isDone'] = true;
    			}
    		}
    	}
    		
    	
    	
    	if($retData['isClose'] == true || $retData['isCancel'] == true || $retData['isProject'] == true || $retData['isDone'] == true ){
    		$retData['success'] = false;
    	}else{
    	if($isDelete == null){
    		$retData['isDelete'] = true;
    	}else{
//     		$retData['isDelete'] = false;
    		$model = new Comment();
    		$model->comment = $comment;
    		$model->createTime = new MongoDate();
    		$model->commentBy = new ObjectID($currentId);
    		$model->refId = new ObjectID($refId);
    		$createTime = new MongoDate();
    	
    	if($model->save()){
    		$message = true;
    		$retData['success'] = true;
    		$retData['isDelete'] = false;
    	}else{
    		$message = false;
    		$retData['success'] = false;
    		$retData['isDelete'] = true;
    	}
    	$retData['createtime'] =  DateTime::MongoDateToDateCreate($createTime->sec);
    	}
    	}
    	echo json_encode($retData);
    	 
    }
    public function actionSavecommentproject(){
    
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    	
    	$comment = $request->post('comment', null);
    	$refId = $request->post('refId', null);
    	
    	$retData['success'] = false;
    	
    	$retData['isCancel'] = false;
    	$retData['isDelete'] = false;
    	$baseUrl = \Yii::getAlias ( '@web' );
    	
    	$isDelete= Project::findOne(["_id"=>new ObjectID($refId)]);
    			
    			if($isDelete == null){
    				$retData['isDelete'] = true;
    			}else{
    				if($isDelete['status'] == Status::CANCEL){
    					Yii::$app->getSession()->setFlash('alert',[
    							'body'=>'โครงการนี้ถูกยกเลิกแล้วโดยผู้ใช้ท่านอื่นแล้ว',
    							'options'=>['class'=>'alert-danger']
    					]);
    					$retData['isCancel'] = true;
    				}
    			}
    
    	 
    	 
    	
    		if($retData['isDelete'] == true || $retData['isCancel'] == true){
    			$retData['success'] = false;
    		}else{
    			//     		$retData['isDelete'] = false;
    			$model = new Comment();
    			$model->comment = $comment;
    			$model->createTime = new MongoDate();
    			$model->commentBy = new ObjectID($currentId);
    			$model->refId = new ObjectID($refId);
    			$createTime = new MongoDate();
    			 
    			if($model->save()){
    				$message = true;
    				$retData['success'] = true;
    				$retData['isDelete'] = false;
    			}else{
    				$message = false;
    				$retData['success'] = false;
    				$retData['isDelete'] = true;
    			}
    			$retData['createtime'] =  DateTime::MongoDateToDateCreate($createTime->sec);
    		}
    	
    	echo json_encode($retData);
    
    }

    /**
     * Updates an existing Comment model.
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

    /**
     * Deletes an existing Comment model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $_id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    public function isDelete($Id){
    	$model = Project::findOne($Id);
    	if($model == null){
    		return true;
    	}
    	else{
    		return false;
    	}
    }

    /**
     * Finds the Comment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $_id
     * @return Comment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Comment::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
