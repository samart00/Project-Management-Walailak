<?php
namespace backend\controllers;

use Yii;
use common\models\User;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use MongoDB\BSON\ObjectID;
use yii\filters\VerbFilter; 
use yii\web\UploadedFile;
use yii\base\ErrorException;
use yii\filters\AccessControl;

class ProfileController extends Controller
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
	
	public function actionIndex()
	{
		$userId = Yii::$app->user->identity->_id;
		$model = User::findOne($userId);
		
		return $this->render('index', [
				'model' => $model,
				'userId' => $userId
				
		]);
	}
	
	public function actionEditpassword(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
		 
		$currentPassword= $request->post('currentPassword', null);
		$newPassword = $request->post('newPassword', null);
		
		$model = User::findOne((string)$currentId);
		$password = $model->password_hash;
		$retData['success'] = false;
		$retData['incorrect'] = false;
		
				$currentPassword_hash = Yii::$app->security->validatePassword($currentPassword, $password);
				if($currentPassword_hash){
					$model->password_hash = Yii::$app->security->generatePasswordHash($newPassword);
					if($model->save()){
						$retData['success'] = true;
					}
				}else{
					$retData['incorrect'] = true;
				}
		
		echo json_encode($retData);
	}
	
	public function actionEditusername(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
			
		$username = $request->post('username', null);
	
		$model = User::findOne((string)$currentId);
		$retData['success'] = false;
		
		$retData['isDuplicate'] = $this->isDuplicate($username, $currentId);
		
		if(!$retData['isDuplicate']){
			$model->username = $username;
			if($model->save()){
				$retData['success'] = true;
			}
		}
		echo json_encode($retData);
	}
	
	public function isDuplicate($username, $userId){
		$isDuplicate = false;
		$condition = [];
		$query = User::find();
	
		if(!empty($username)){
			$conditions['username'] = $username;
		}
	
		if(!empty($conditions)){
			$query->where($conditions);
		}
		 
		if($userId != null){
			$query->andWhere(['<>', '_id', new ObjectID($userId)]);
		}
	
		$userProject = $query->all();
	
		if($userProject != null){
			$isDuplicate = true;
		}
		return $isDuplicate;
	}
	
	
	public function actionUpload()
	{
		$currentId = Yii::$app->user->identity->_id;
		$photo = Yii::$app->user->identity->avatar;
		$model = User::findOne($currentId);
	
		if ($model->load(Yii::$app->request->post()) && $model->validate()) {
			@unlink(User::getPhotoUser($currentId));
			$model->avatar = $model->upload($model,'avatar');
			$model->save();
		}  
		return $this->redirect(['/profile']);
	}
}
?>