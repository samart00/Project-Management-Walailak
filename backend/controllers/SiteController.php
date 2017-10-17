<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use common\models\User;
use backend\models\Event;
use backend\models\Task;
use backend\models\EventType;
use backend\models\EventSearch;
use yii\helpers\ArrayHelper;
use backend\models\Project;

/**
 * Site controller
 */
class SiteController extends Controller
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
                        'actions' => ['login', 'error', 'request-password-reset', 'test', 'reset-password'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new Event();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        
        $userId = Yii::$app->user->identity->_id;
        $request = \Yii::$app->request;
        $lProject = $request->post('lProject',null);
        $depId = Yii::$app->user->identity->depCode;
        
        $query = Event::find();
        $query->where(['Calendar' => '4']);
        $query->orwhere(['CreateBy' => $userId]);
        $query->orwhere(['depCode' => $depId]);
        $query->andwhere(['>=','Start_Date',new \MongoDate()]);
        
        $query = $query->all();
        
        $querytask = Task::find();
        $querytask->andwhere(['>=','startDate', new \MongoDate()]);
        
        $querytask = $querytask->all();
        
        $eventtype = EventType::find();
        $eventtype->where(['Calendar' => ['1']]);
        $eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
        
        
        $eventall = EventType::find()->all();
        $color = []; 
        foreach ($eventall as $events){
        	$color[(string)$events->_id] = $events->Color;
        }
        
        if($lProject != NULL){
        	$querytask->andWhere(['projectId' => new ObjectID($lProject)]);
        	$querytask->andWhere(['status' => [2,3,4,5,6,7]]);
        }
         
        if($lProject != NULL){
        	$nameP = Project::find();
        	$nameP->where(['_id' => new ObjectID($lProject)]);
        	$nameP = $nameP->all();
        }else {
        	$nameP = null;
        }
        
        $listProject = Project::find();
        $listProject->where(['status' => [1]]);
        $listProject->andwhere(['member.userId' => $userId]);
        $listProject = $listProject->all();
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        	'value'=> $query,
        	'valueeventtype' => $eventtype,
        	'valuetask'=> $querytask,
        	'valuecolor'=> $color,
        	'valuenameP'=> $nameP,
        	'valuelistProject'=> $listProject,
        ]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    
    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
    	$this->layout = "@backend/themes/adminlte/layouts/reset-password";
    	$model = new PasswordResetRequestForm();
    	if ($model->load(Yii::$app->request->post()) && $model->validate()) {
    		$user = User::findOne(['username'=>'admin']);
    		 
    		if (!$user) {
    			return false;
    		}
    		 
    		if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
    			$user->generatePasswordResetToken();
    			if (!$user->save()) {
    				return false;
    			}
    		}
    		 
    		$model = Yii::$app
	    		->mailer
	    		->compose(
	    				['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
	    				['user' => $user]
	    		)
	    		->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
	    		->setTo($user->email)
	    		->setSubject('Password reset for ' . Yii::$app->name);
    		
    		if ($model->send()) {
    			Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
    
    			return $this->goHome();
    		} else {
    			Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
    		}
    	}
    
    	return $this->render('requestPasswordResetToken', [
    			'model' => $model,
    	]);
    }
    
    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
    	$this->layout = "@backend/themes/adminlte/layouts/reset-password";
    	try {
    		$model = new ResetPasswordForm($token);
    	} catch (InvalidParamException $e) {
    		throw new BadRequestHttpException($e->getMessage());
    	}
    
    	if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
    		Yii::$app->session->setFlash('success', 'คุณได้เปลี่ยนรหัสผ่านใหม่สำเร็จ');
    
    		return $this->goHome();
    	}
    
    	return $this->render('resetPassword', [
    			'model' => $model,
    	]);
    }
}
