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
use MongoDB\BSON\ObjectID;
use yii\mongodb\Query;
use yii\base\Widget;

class FileController extends Controller
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
		$request = \Yii::$app->request;
		$depId = Yii::$app->user->identity->depCode;
		
		$model = new UploadForm();
		$query = Event::find();
		$query->where(['Calendar' => ['4']]);
		//$query->orwhere(['depCode' => $depId]);
		$query = $query->all();
		$eventtype = EventType::find();
		$eventtype->where(['Calendar' => ['4']]);
		$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
		$eventall = EventType::find()->all();
		
		$color = [];
		foreach ($eventall as $events){
			$color[(string)$events->_id] = $events->Color;
		}
		$TypeID = "59226408a4b5530770005763";
		$Allday = "true";
		$CreateBy = "591527aa37d61b1ff0002208";
		$Calendar = "4";
		$eventName = Event::find()->select(['Event_name','Start_Date'])->from('Event')->where(['Calendar' => ['4']])->all();
		if(Yii::$app->request->post()){
			$model->file = $model->uploadFile($model,'file');
			try{
				$fh = fopen($_FILES['uploadfile']['tmp_name'], "r");
				while (($row = fgetcsv($fh, 0, ","))!= FALSE)
				{	
					$model = new Event();
					$model->Event_name = $row[0];
					$model->Start_Date = new MongoDate(strtotime($row[1]));
					$t = intval(strtotime($row[1]))+86400;
					$model->End_Date =  new MongoDate($t);
					$model->Discription =  $row[2];
					$model->TypeID =  new ObjectID($TypeID);
					$model->Allday =  ($Allday=="true")?true:false;
					$model->CreateBy =  new ObjectID($CreateBy);
					$model->Calendar =  $Calendar;
					$duplicateName = Event::find()
					->select(['Event_name','Start_Date'])
					->from('Event')
					->where(['Event_name' => [$model->Event_name],'Start_Date' => [$model->Start_Date]])
					->all();
					if ($duplicateName == null){
						$model->save();
					}
				}
				fclose($fh);
			
				Yii::$app->session->setFlash('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
				return $this->redirect('upload');
			}catch (Exception $e){
				Yii::$app->session->setFlash('danger', 'มีข้อผิดพลาด');
				return $this->redirect('upload');
			}
		}
		return $this->render('index', ['model' => $model,'valueeventtype' => $eventtype,'valuecolor'=> $color,'value'=> $query]);
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