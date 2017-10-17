<?php
namespace backend\controllers;

use Yii;
use backend\models\Event;
use backend\models\EventType;
use backend\models\EventSearch;
use yii\web\Controller;
use backend\models\UploadForm;
use yii\web\UploadedFile;
use yii\bootstrap\BootstrapWidgetTrait;
use yii\bootstrap\Alert;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use MongoDate;
use DateTime;
use DateTimeZone;
use yii\helpers\ArrayHelper;

class UploadController extends Controller
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
	 * Lists all Event models.
	 * @return mixed
	 */
	
	public function actionIndex()
	{
		$model = new UploadForm();
		$query = Event::find();
		$query->where(['Calendar' => ['4']]);
		$query = $query->all();
		$eventtype = EventType::find();
		$eventtype->where(['Calendar' => ['4']]);
		$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
		$eventall = EventType::find()->all();
		
		$color = [];
		foreach ($eventall as $events){
			$color[(string)$events->_id] = $events->Color;
		}
		
		return $this->render('UploadHoliday', ['model' => $model,'valueeventtype' => $eventtype,'valuecolor'=> $color,'value'=> $query]);
	}

	public function actionSave(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		
		$event_name = $request->post('event_name', null);
		$start_date = $request->post('start_date', null);
		$end_date = $request->post('end_date', null);
		$description = $request->post('description', null);
		$TypeID = $request->post('TypeID', null);
		$Allday = $request->post('Allday', null);
		$CreateBy = $request->post('CreateBy', null);
		$model = null;
		
		$calendar = EventType::findOne($TypeID);
		
		if ($model == null){
			$model = new Event();
			$model->Event_name = $event_name;
			$model->Start_Date =  new MongoDate(strtotime($start_date));;
			$model->End_Date =  new MongoDate(strtotime($end_date));
			$model->Discription =  $description;
			$model->TypeID =  new ObjectID($TypeID);
			$model->Allday =  ($Allday=="true")?true:false;
			$model->CreateBy =  new ObjectID($CreateBy);
			$model->Calendar =  $calendar->Calendar;
		}
		
		
		if($model->save()){
			
			$retData['success'] = true;
			
		}else{
			$retData = ['success' => false];
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
	public function actionCreate()
	{
		$model = new Event();
	
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
	
	/**
	 * Deletes an existing Event model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $_id
	 * @return mixed
	 */
	public function actionDelete($id)
	{
		$this->findModel($id)->delete();
	
		return $this->redirect(['index']);
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
		if (($model = Event::findOne($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
	
	public function beforeAction($action) {
		$this->enableCsrfValidation = false;
		return parent::beforeAction($action);
	}
}
?>