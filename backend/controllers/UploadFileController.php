<?php

namespace backend\controllers;


use Yii;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\libs\ActiveFlag;

use \MongoDate;
use MongoDB\BSON\ObjectID;
use common\libs\DateTime;
use backend\models\Department;
use yii\helpers\ArrayHelper;
use common\libs\Permission;
use yii\filters\AccessControl;
use yii\data\Pagination;
use yii\data\Sort;
use backend\models\UploadFile;
use yii\web\UploadedFile;
/**
 * TeamController implements the CRUD actions for Team model.
 */
class UploadFileController extends Controller
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
	
	public function actionIndex($id)
	{
		
			$model= UploadFile::findOne(["_id" => $id]);
			header('Content-type: '.$model->contentType);
			echo $model->file->getBytes();
		
		return $this->render('index', [
				
		]);
	}
	public function actionCreate()
	{
		$model = new UploadFile;
		if ($model->load($_POST)) {
			$file = UploadedFile::getInstance($model,'file');
			$model->filename=$file->name;
			$model->contentType=$file->type;
			$model->file=$file;
			if($model->save()){
				return $this->redirect(['index', 'id' => (string)$model->_id]);
			}
		}else
			return $this->render('create', [
					'model' => $model,
			]);
	}
	public function actionGet($id)
	{
		$model=$this->findModel($id);
		header('Content-type: '.$model->contentType);
		echo $model->file->getBytes();
	}
}
