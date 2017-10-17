<?php

namespace backend\controllers;

use Yii;
use backend\models\Event;
use backend\models\Task;
use backend\models\EventType;
use backend\models\EventSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\backend\models;
use \MongoDate;
use yii\helpers\ArrayHelper;
use MongoDB\BSON\ObjectID;
use common\models\User;
use common\libs\Status;
use common\libs\Permission;
use backend\models\Project;
use common\libs\DateTime;
use common\libs\ActiveFlag;
/**
 * EventController implements the CRUD actions for Event model.
 */
class EventController extends Controller
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
        $searchModel = new Event();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        $request = \Yii::$app->request;
        $taskandevent = $request->post('taskandevent',null);
        
        if($taskandevent == null){
        	$userId = Yii::$app->user->identity->_id;
        	$depId = Yii::$app->user->identity->depCode;
        	
        	$query = Event::find();
        	$query->where(['Calendar' => ['1','4']]);
        	$query->andwhere(['CreateBy' => $userId]);
        	 
        	$query = $query->all();
        	
        	
        	$querytask = Task::find();
        	$querytask->where(['assignee.userId' => $userId]);
        	$querytask = $querytask->all();
        	
        	$eventtype = EventType::find();
        	$eventtype->where(['Calendar' => '1','activeFlag'=> ActiveFlag::ACTIVE,'status'=>Status::OPEN]);
        	$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
        	
        	
        	$eventall = EventType::find()->all();
        	$color = [];
        	foreach ($eventall as $events){
        		$color[(string)$events->_id] = $events->Color;
        	}
        	 
        	return $this->render('index', [
        			'searchModel' => $searchModel,
        			'dataProvider' => $dataProvider,
        			'value'=> $query,
        			'valuetask'=> $querytask,
        			'valueeventtype' => $eventtype,
        			'valuecolor'=> $color,
        			'taskandevent'=> $taskandevent,
        	]);
        }if($taskandevent == "1"){
        	$userId = Yii::$app->user->identity->_id;
        	$depId = Yii::$app->user->identity->depCode;
        	
        	$query = Event::find();
        	$query->where(['Calendar' => ['1','4']]);
        	$query->andwhere(['CreateBy' => $userId]);
        	 
        	$query = $query->all();
        	
        	
        	$querytask = Task::find();
        	$querytask->where(['assignee.userId' => $userId]);
        	$querytask = $querytask->all();
        	
        	$eventtype = EventType::find();
        	$eventtype->where(['Calendar' => '1','activeFlag'=> ActiveFlag::ACTIVE,'status'=>Status::OPEN]);
        	$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
        	
        	
        	$eventall = EventType::find()->all();
        	$color = [];
        	foreach ($eventall as $events){
        		$color[(string)$events->_id] = $events->Color;
        	}
        	 
        	return $this->render('index', [
        			'searchModel' => $searchModel,
        			'dataProvider' => $dataProvider,
        			'value'=> $query,
        			'valuetask'=> $querytask,
        			'valueeventtype' => $eventtype,
        			'valuecolor'=> $color,
        			'taskandevent'=> $taskandevent,
        	]);
        }else if($taskandevent == "2"){
        	$userId = Yii::$app->user->identity->_id;
        	$depId = Yii::$app->user->identity->depCode;
        	 
        	 
        	$querytask = Task::find();
        	$querytask->where(['assignee.userId' => $userId]);
        	$querytask = $querytask->all();
        	 
        	$eventtype = EventType::find();
        	$eventtype->where(['Calendar' => '1','activeFlag'=> ActiveFlag::ACTIVE,'status'=>Status::OPEN]);
        	$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
        	 
        	 
        	$eventall = EventType::find()->all();
        	$color = [];
        	foreach ($eventall as $events){
        		$color[(string)$events->_id] = $events->Color;
        	}
        	
        	return $this->render('index', [
        			'searchModel' => $searchModel,
        			'dataProvider' => $dataProvider,
        			'value'=> null,
        			'valuetask'=> $querytask,
        			'valueeventtype' => $eventtype,
        			'valuecolor'=> $color,
        			'taskandevent'=> $taskandevent,
        	]);
        }else if($taskandevent == "3"){
        	$userId = Yii::$app->user->identity->_id;
        	$depId = Yii::$app->user->identity->depCode;
        	 
        	$query = Event::find();
        	$query->where(['Calendar' => ['1','4']]);
        	$query->andwhere(['CreateBy' => $userId]);
        	
        	$query = $query->all();
        	
        	$eventtype = EventType::find();
        	$eventtype->where(['Calendar' => '1','activeFlag'=> ActiveFlag::ACTIVE,'status'=>Status::OPEN]);
        	$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
        	 
        	 
        	$eventall = EventType::find()->all();
        	$color = [];
        	foreach ($eventall as $events){
        		$color[(string)$events->_id] = $events->Color;
        	}
        	
        	return $this->render('index', [
        			'searchModel' => $searchModel,
        			'dataProvider' => $dataProvider,
        			'value'=> $query,
        			'valuetask'=> null,
        			'valueeventtype' => $eventtype,
        			'valuecolor'=> $color,
        			'taskandevent'=> $taskandevent,
        	]);
        }
        
        
        
        
        
    }
    
    public function actionProject()
    {
    	$now = new \MongoDate ();
    	$isnow = $now->sec;
    	$userId = Yii::$app->user->identity->_id;
    	$request = \Yii::$app->request;
    	$lProject = $request->post('lProject',null);
    	$sendemail = User::findOne($userId);
    	$searchModel = new Event();
    	$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    	
    	$request = \Yii::$app->request;
    	$taskandevent = $request->post('taskandevent',null);
    	
    	if($taskandevent == null){
    		$listProject = Project::find();
    		$listProject->where(['status' => [1]]);
    		$listProject->andwhere(['member.userId' => $userId]);
    		$listProject->orWhere(['createBy' => $userId]);
    		$listProject = $listProject->all();
    		$lpro = [];
    		foreach ($listProject as $field){
    			$field->projectName = "$field->projectName ($field->abbrProjectName)";
    			$lpro[] = $field->_id;
    		}
    		$privatetask = NULL;
    		$querytask = Task::find();
    		$querytask->where(['projectId' => $lpro]);
    		$querytask->andWhere(['assignee.userId' => $userId]);
    		$querytask->orWhere(['createBy' => $userId]);
    		$querytask->andWhere(['status' => [2,3,4,5,6,7,8]]);
    		$query = Event::find();
    		 
    		if($lProject != NULL){
    			$query->where(['projectID' => new ObjectID($lProject)]);
    			$query->orWhere(['Calendar' => ['4']]);
    			$querytask->andWhere(['projectId' => new ObjectID($lProject)]);
    			$querytask = $querytask->all();
    		}else {
    			$query->where(['Calendar' => ['4']]);
    			$query->orWhere(['projectID' => $lpro]);
    			$privatetask = Task::find();
    			$privatetask->where(['assignee.userId' => $userId]);
    			$privatetask->andWhere(['projectId' => NULL]);
    			$privatetask = $privatetask->all();
    			$querytask = $querytask->all();
    		
    			foreach ($privatetask as $field){
    				$createBy = User::findOne(['_id' => $field->createBy]);
    				$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    				$field->taskName = "(งานส่วนตัว)  $field->taskName";
    				if($field->status == 2){
    					$field->status= "New Task";
    				}else if($field->status == 3){
    					$field->status= "Open Task";
    				}else if($field->status == 4){
    					$field->status= "Doing";
    				}else if($field->status == 5){
    					$field->status= "Waiting For Approve";
    				}else if($field->status == 6){
    					$field->status= "Approved";
    				}else if($field->status == 7){
    					$field->status= "Rejected";
    				}else if($field->status == 8){
    					$field->status= "Completed";
    				}
    			}
    			$i=0;
    			foreach ($listProject as $field1){
    				foreach ($querytask as $field){
    		
    					if($field1->_id == $field->projectId){
    						$field->taskName = "($field1->abbrProjectName)  $field->taskName";
    					}else if($field->projectId == NULL){
    						$i++;
    						if($i==1){
    							$field->taskName = "(ไม่สังกัด)  $field->taskName";
    						}
    					}
    				}
    			}
    		}
    		foreach ($querytask as $field){
    			$task = Task::findOne($field->_id);
    			$addArr = [];
    			if(intval($field->endDate["sec"])-intval($field->startDate["sec"]) > 604799){
    				if(intval($field->endDate["sec"]) - intval($isnow) < 604800){
    					if(intval($isnow) < (intval($field->endDate["sec"]) - 86400)){
    						$arr=[];
    						$i=0;
    						foreach ($field->assignee as $field1){
    							$arr[$i] = (string)$field1['userId'];
    							$i++;
    						}
    						$j=0;
    						$eMail=null;
    						foreach ($arr as $field2){
    							$eMail = User::findOne(['_id' => new ObjectID($field2)]);
    							if($eMail->sendemail == 1 ){
    								if(!$field->sendemail){
    									Yii::$app->mailer
    									->compose()
    									->setFrom('badlist99@gmail.com')
//     										->setTo('deathearth999@gmail.com')
    									->setTo($eMail->email)
    									->setSubject('งาน '.$field->taskName.' : กำหนดส่งเหลือเวลาไม่ถึง 7 วันแล้ว!!!')
    									->setTextBody('งาน  :  '.$field->taskName.'ซึ่งมี่กำหนดการณ์จากวันที่ '.
    											date('d/m/Y H:i',  strtotime('+6 Hour',$field->startDate["sec"])).
    											' ถึงวันที่ '.date('d/m/Y H:i',  strtotime('+6 Hour',$field->endDate["sec"])).
    											'  ขณะนี้เหลือเวลาไม่ถึง 7 วันแล้ว')
    											->send();
    											$addArr[$j] = new ObjectID($field2);
    								}else{
    									foreach ($field->sendemail as $fields){
    										if($eMail->_id == $fields){
    											$addArr[$j] = new ObjectID($field2);
    										}else {
    											Yii::$app->mailer
    											->compose()
    											->setFrom('badlist99@gmail.com')
//     											->setTo('deathearth999@gmail.com')
    											->setTo($eMail->email)
    											->setSubject('งาน '.$field->taskName.' : กำหนดส่งเหลือเวลาไม่ถึง 7 วันแล้ว!!!')
    											->setTextBody('งาน  :  '.$field->taskName.'ซึ่งมี่กำหนดการณ์จากวันที่ '.
    													date('d/m/Y H:i',  strtotime('+6 Hour',$field->startDate["sec"])).
    													' ถึงวันที่ '.date('d/m/Y H:i',  strtotime('+6 Hour',$field->endDate["sec"])).
    													'  ขณะนี้เหลือเวลาไม่ถึง 7 วันแล้ว')
    													->send();
    													$addArr[$j] = new ObjectID($field2);
    										}
    									}
    									
    								}
    							}
    							$j++;
    						}
    					}
    				}
    			}	    		
    			$createBy = User::findOne(['_id' => $field->createBy]);
    			$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    			if($field->status == 2){
    				$field->status= "New Task";
    			}else if($field->status == 3){
    				$field->status= "Open Task";
    			}else if($field->status == 4){
    				$field->status= "Doing";
    			}else if($field->status == 5){
    				$field->status= "Waiting For Approve";
    			}else if($field->status == 6){
    				$field->status= "Approved";
    			}else if($field->status == 7){
    				$field->status= "Rejected";
    			}else if($field->status == 8){
    				$field->status= "Completed";
    			}
    			$task->sendemail = $addArr;
    			$task->save();
    		}
    		 
    		if($lProject != NULL){
    			$nameP = Project::find();
    			$nameP->where(['_id' => new ObjectID($lProject)]);
    			$nameP = $nameP->all();
    		}else {
    			$nameP = null;
    		}
    		 
    		$eventtype = EventType::find();
    		$eventtype->where(['Calendar' => '3','activeFlag'=>ActiveFlag::ACTIVE,'status' => Status::OPEN]);
    		$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
    		 
    		$eventall = EventType::find()->all();
    		$color = [];
    		foreach ($eventall as $events){
    			$color[(string)$events->_id] = $events->Color;
    		}
    		$query = $query->all();
    		foreach ($query as $field){
    			$field->EventSname = $field->Event_name;
    		}
    		 
    		foreach ($listProject as $field1){
    			foreach ($query as $field){
    				if($field->Calendar == "3"){
    					if($field1->_id == $field->projectID){
    						if($lProject == NULL){
    							$field->Event_name = "($field1->abbrProjectName)  $field->Event_name";
    						}
    					}
    				}
    			}
    		}
    		 
    		return $this->render('indexproject', [
    				'searchModel' => $searchModel,
    				'dataProvider' => $dataProvider,
    				'valuetask'=> $querytask,
    				'valuelistProject'=> $listProject,
    				'valuenameP'=> $nameP,
    				'value'=> $query,
    				'valueeventtype' => $eventtype,
    				'valuecolor'=> $color,
    				'valuechecks'=> $sendemail,
    				'valueprivatetask'=> $privatetask,
    				'taskandevent'=> $taskandevent,
    		]);
    	}if($taskandevent == "1"){
    		$listProject = Project::find();
    		$listProject->where(['status' => [1]]);
    		$listProject->andwhere(['member.userId' => $userId]);
    		$listProject = $listProject->all();
    		$lpro = [];
    		foreach ($listProject as $field){
    			$field->projectName = "$field->projectName ($field->abbrProjectName)";
    			$lpro[] = $field->_id;
    		}
    		$privatetask = NULL;
    		$querytask = Task::find();
    		$querytask->where(['projectId' => $lpro]);
    		$querytask->andWhere(['assignee.userId' => $userId]);
    		$querytask->andWhere(['status' => [2,3,4,5,6,7,8]]);
    		$query = Event::find();
    		 
    		if($lProject != NULL){
    			$query->where(['projectID' => new ObjectID($lProject)]);
    			$query->orWhere(['Calendar' => ['4']]);
    			$querytask->andWhere(['projectId' => new ObjectID($lProject)]);
    			$querytask = $querytask->all();
    		}else {
    			$query->where(['Calendar' => ['4']]);
    			$query->orWhere(['projectID' => $lpro]);
    			$privatetask = Task::find();
    			$privatetask->where(['assignee.userId' => $userId]);
    			$privatetask->andWhere(['projectId' => NULL]);
    			$privatetask = $privatetask->all();
    			$querytask = $querytask->all();
    		
    			foreach ($privatetask as $field){
    				$createBy = User::findOne(['_id' => $field->createBy]);
    				$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    				$field->taskName = "(งานส่วนตัว)  $field->taskName";
    				if($field->status == 2){
    					$field->status= "New Task";
    				}else if($field->status == 3){
    					$field->status= "Open Task";
    				}else if($field->status == 4){
    					$field->status= "Doing";
    				}else if($field->status == 5){
    					$field->status= "Waiting For Approve";
    				}else if($field->status == 6){
    					$field->status= "Approved";
    				}else if($field->status == 7){
    					$field->status= "Rejected";
    				}else if($field->status == 8){
    					$field->status= "Completed";
    				}
    			}
    			$i=0;
    			foreach ($listProject as $field1){
    				foreach ($querytask as $field){
    		
    					if($field1->_id == $field->projectId){
    						$field->taskName = "($field1->abbrProjectName)  $field->taskName";
    					}else if($field->projectId == NULL){
    						$i++;
    						if($i==1){
    							$field->taskName = "(ไม่สังกัด)  $field->taskName";
    						}
    					}
    				}
    			}
    		}
    		foreach ($querytask as $field){
    			$createBy = User::findOne(['_id' => $field->createBy]);
    			$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    			if($field->status == 2){
    				$field->status= "New Task";
    			}else if($field->status == 3){
    				$field->status= "Open Task";
    			}else if($field->status == 4){
    				$field->status= "Doing";
    			}else if($field->status == 5){
    				$field->status= "Waiting For Approve";
    			}else if($field->status == 6){
    				$field->status= "Approved";
    			}else if($field->status == 7){
    				$field->status= "Rejected";
    			}else if($field->status == 8){
    				$field->status= "Completed";
    			}
    		}
    		 
    		if($lProject != NULL){
    			$nameP = Project::find();
    			$nameP->where(['_id' => new ObjectID($lProject)]);
    			$nameP = $nameP->all();
    		}else {
    			$nameP = null;
    		}
    		 
    		$eventtype = EventType::find();
    		$eventtype->where(['Calendar' => '3','activeFlag'=>ActiveFlag::ACTIVE,'status' => Status::OPEN]);
    		$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
    		 
    		$eventall = EventType::find()->all();
    		$color = [];
    		foreach ($eventall as $events){
    			$color[(string)$events->_id] = $events->Color;
    		}
    		$query = $query->all();
    		foreach ($query as $field){
    			$field->EventSname = $field->Event_name;
    		}
    		 
    		foreach ($listProject as $field1){
    			foreach ($query as $field){
    				if($field->Calendar == "3"){
    					if($field1->_id == $field->projectID){
    						if($lProject == NULL){
    							$field->Event_name = "($field1->abbrProjectName)  $field->Event_name";
    						}
    					}
    				}
    			}
    		}
    		 
    		return $this->render('indexproject', [
    				'searchModel' => $searchModel,
    				'dataProvider' => $dataProvider,
    				'valuetask'=> $querytask,
    				'valuelistProject'=> $listProject,
    				'valuenameP'=> $nameP,
    				'value'=> $query,
    				'valueeventtype' => $eventtype,
    				'valuecolor'=> $color,
    				'valueprivatetask'=> $privatetask,
    				'taskandevent'=> $taskandevent,
    		]);
    	}else if($taskandevent == "2"){
    		$listProject = Project::find();
    		$listProject->where(['status' => [1]]);
    		$listProject->andwhere(['member.userId' => $userId]);
    		$listProject = $listProject->all();
    		$lpro = [];
    		foreach ($listProject as $field){
    			$field->projectName = "$field->projectName ($field->abbrProjectName)";
    			$lpro[] = $field->_id;
    		}
    		$privatetask = NULL;
    		$querytask = Task::find();
    		$querytask->where(['projectId' => $lpro]);
    		$querytask->andWhere(['assignee.userId' => $userId]);
    		$querytask->andWhere(['status' => [2,3,4,5,6,7,8]]);
    		$query = Event::find();
    		 
    		if($lProject != NULL){
    			$query->where(['projectID' => new ObjectID($lProject)]);
    			$query->orWhere(['Calendar' => ['4']]);
    			$querytask->andWhere(['projectId' => new ObjectID($lProject)]);
    			$querytask = $querytask->all();
    		}else {
    			$query->where(['Calendar' => ['4']]);
    			$query->orWhere(['projectID' => $lpro]);
    			$privatetask = Task::find();
    			$privatetask->where(['assignee.userId' => $userId]);
    			$privatetask->andWhere(['projectId' => NULL]);
    			$privatetask = $privatetask->all();
    			$querytask = $querytask->all();
    		
    			foreach ($privatetask as $field){
    				$createBy = User::findOne(['_id' => $field->createBy]);
    				$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    				$field->taskName = "(งานส่วนตัว)  $field->taskName";
    				if($field->status == 2){
    					$field->status= "New Task";
    				}else if($field->status == 3){
    					$field->status= "Open Task";
    				}else if($field->status == 4){
    					$field->status= "Doing";
    				}else if($field->status == 5){
    					$field->status= "Waiting For Approve";
    				}else if($field->status == 6){
    					$field->status= "Approved";
    				}else if($field->status == 7){
    					$field->status= "Rejected";
    				}else if($field->status == 8){
    					$field->status= "Completed";
    				}
    			}
    			$i=0;
    			foreach ($listProject as $field1){
    				foreach ($querytask as $field){
    		
    					if($field1->_id == $field->projectId){
    						$field->taskName = "($field1->abbrProjectName)  $field->taskName";
    					}else if($field->projectId == NULL){
    						$i++;
    						if($i==1){
    							$field->taskName = "(ไม่สังกัด)  $field->taskName";
    						}
    					}
    				}
    			}
    		}
    		foreach ($querytask as $field){
    			$createBy = User::findOne(['_id' => $field->createBy]);
    			$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    			if($field->status == 2){
    				$field->status= "New Task";
    			}else if($field->status == 3){
    				$field->status= "Open Task";
    			}else if($field->status == 4){
    				$field->status= "Doing";
    			}else if($field->status == 5){
    				$field->status= "Waiting For Approve";
    			}else if($field->status == 6){
    				$field->status= "Approved";
    			}else if($field->status == 7){
    				$field->status= "Rejected";
    			}else if($field->status == 8){
    				$field->status= "Completed";
    			}
    		}
    		 
    		if($lProject != NULL){
    			$nameP = Project::find();
    			$nameP->where(['_id' => new ObjectID($lProject)]);
    			$nameP = $nameP->all();
    		}else {
    			$nameP = null;
    		}
    		 
    		$eventtype = EventType::find();
    		$eventtype->where(['Calendar' => '3','activeFlag'=>ActiveFlag::ACTIVE,'status' => Status::OPEN]);
    		$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
    		 
    		$eventall = EventType::find()->all();
    		$color = [];
    		foreach ($eventall as $events){
    			$color[(string)$events->_id] = $events->Color;
    		}
    		$query = $query->all();
    		foreach ($query as $field){
    			$field->EventSname = $field->Event_name;
    		}
    		 
    		foreach ($listProject as $field1){
    			foreach ($query as $field){
    				if($field->Calendar == "3"){
    					if($field1->_id == $field->projectID){
    						if($lProject == NULL){
    							$field->Event_name = "($field1->abbrProjectName)  $field->Event_name";
    						}
    					}
    				}
    			}
    		}
    		 
    		return $this->render('indexproject', [
    				'searchModel' => $searchModel,
    				'dataProvider' => $dataProvider,
    				'valuetask'=> $querytask,
    				'valuelistProject'=> $listProject,
    				'valuenameP'=> $nameP,
    				'value'=> null,
    				'valueeventtype' => $eventtype,
    				'valuecolor'=> $color,
    				'valueprivatetask'=> $privatetask,
    				'taskandevent'=> $taskandevent,
    		]);
    	}else if($taskandevent == "3"){
    		$listProject = Project::find();
    		$listProject->where(['status' => [1]]);
    		$listProject->andwhere(['member.userId' => $userId]);
    		$listProject = $listProject->all();
    		$lpro = [];
    		foreach ($listProject as $field){
    			$field->projectName = "$field->projectName ($field->abbrProjectName)";
    			$lpro[] = $field->_id;
    		}
    		$privatetask = NULL;
    		$querytask = Task::find();
    		$querytask->where(['projectId' => $lpro]);
    		$querytask->andWhere(['assignee.userId' => $userId]);
    		$querytask->andWhere(['status' => [2,3,4,5,6,7,8]]);
    		$query = Event::find();
    		 
    		if($lProject != NULL){
    			$query->where(['projectID' => new ObjectID($lProject)]);
    			$query->orWhere(['Calendar' => ['4']]);
    			$querytask->andWhere(['projectId' => new ObjectID($lProject)]);
    			$querytask = $querytask->all();
    		}else {
    			$query->where(['Calendar' => ['4']]);
    			$query->orWhere(['projectID' => $lpro]);
    			$privatetask = Task::find();
    			$privatetask->where(['assignee.userId' => $userId]);
    			$privatetask->andWhere(['projectId' => NULL]);
    			$privatetask = $privatetask->all();
    			$querytask = $querytask->all();
    		
    			foreach ($privatetask as $field){
    				$createBy = User::findOne(['_id' => $field->createBy]);
    				$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    				$field->taskName = "(งานส่วนตัว)  $field->taskName";
    				if($field->status == 2){
    					$field->status= "New Task";
    				}else if($field->status == 3){
    					$field->status= "Open Task";
    				}else if($field->status == 4){
    					$field->status= "Doing";
    				}else if($field->status == 5){
    					$field->status= "Waiting For Approve";
    				}else if($field->status == 6){
    					$field->status= "Approved";
    				}else if($field->status == 7){
    					$field->status= "Rejected";
    				}else if($field->status == 8){
    					$field->status= "Completed";
    				}
    			}
    			$i=0;
    			foreach ($listProject as $field1){
    				foreach ($querytask as $field){
    		
    					if($field1->_id == $field->projectId){
    						$field->taskName = "($field1->abbrProjectName)  $field->taskName";
    					}else if($field->projectId == NULL){
    						$i++;
    						if($i==1){
    							$field->taskName = "(ไม่สังกัด)  $field->taskName";
    						}
    					}
    				}
    			}
    		}
    		foreach ($querytask as $field){
    			$createBy = User::findOne(['_id' => $field->createBy]);
    			$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    			if($field->status == 2){
    				$field->status= "New Task";
    			}else if($field->status == 3){
    				$field->status= "Open Task";
    			}else if($field->status == 4){
    				$field->status= "Doing";
    			}else if($field->status == 5){
    				$field->status= "Waiting For Approve";
    			}else if($field->status == 6){
    				$field->status= "Approved";
    			}else if($field->status == 7){
    				$field->status= "Rejected";
    			}else if($field->status == 8){
    				$field->status= "Completed";
    			}
    		}
    		 
    		if($lProject != NULL){
    			$nameP = Project::find();
    			$nameP->where(['_id' => new ObjectID($lProject)]);
    			$nameP = $nameP->all();
    		}else {
    			$nameP = null;
    		}
    		 
    		$eventtype = EventType::find();
    		$eventtype->where(['Calendar' => '3','activeFlag'=>ActiveFlag::ACTIVE,'status' => Status::OPEN]);
    		$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
    		 
    		$eventall = EventType::find()->all();
    		$color = [];
    		foreach ($eventall as $events){
    			$color[(string)$events->_id] = $events->Color;
    		}
    		$query = $query->all();
    		foreach ($query as $field){
    			$field->EventSname = $field->Event_name;
    		}
    		 
    		foreach ($listProject as $field1){
    			foreach ($query as $field){
    				if($field->Calendar == "3"){
    					if($field1->_id == $field->projectID){
    						if($lProject == NULL){
    							$field->Event_name = "($field1->abbrProjectName)  $field->Event_name";
    						}
    					}
    				}
    			}
    		}
    		 
    		return $this->render('indexproject', [
    				'searchModel' => $searchModel,
    				'dataProvider' => $dataProvider,
    				'valuetask'=> null,
    				'valuelistProject'=> $listProject,
    				'valuenameP'=> $nameP,
    				'value'=> $query,
    				'valueeventtype' => $eventtype,
    				'valuecolor'=> $color,
    				'valueprivatetask'=> null,
    				'taskandevent'=> $taskandevent,
    		]);
    	}else if($taskandevent == "4"){
    		$listProject = Project::find();
    		$listProject->where(['status' => [1]]);
    		$listProject->andwhere(['member.userId' => $userId]);
    		$listProject = $listProject->all();
    		$lpro = [];
    		foreach ($listProject as $field){
    			$field->projectName = "$field->projectName ($field->abbrProjectName)";
    			$lpro[] = $field->_id;
    		}
    		$privatetask = NULL;
    		$querytask = Task::find();
    		$querytask->where(['projectId' => $lpro]);
    		$querytask->andWhere(['assignee.userId' => $userId]);
    		$querytask->andWhere(['status' => [2,3,4,5,6,7,8]]);
    		$query = Event::find();
    		 
    		if($lProject != NULL){
    			$query->where(['projectID' => new ObjectID($lProject)]);
    			$query->orWhere(['Calendar' => ['4']]);
    			$querytask->andWhere(['projectId' => new ObjectID($lProject)]);
    			$querytask = $querytask->all();
    		}else {
    			$query->where(['Calendar' => ['4']]);
    			$query->orWhere(['projectID' => $lpro]);
    			$privatetask = Task::find();
    			$privatetask->where(['assignee.userId' => $userId]);
    			$privatetask->andWhere(['projectId' => NULL]);
    			$privatetask = $privatetask->all();
    			$querytask = $querytask->all();
    		
    			foreach ($privatetask as $field){
    				$createBy = User::findOne(['_id' => $field->createBy]);
    				$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    				$field->taskName = "(งานส่วนตัว)  $field->taskName";
    				if($field->status == 2){
    					$field->status= "New Task";
    				}else if($field->status == 3){
    					$field->status= "Open Task";
    				}else if($field->status == 4){
    					$field->status= "Doing";
    				}else if($field->status == 5){
    					$field->status= "Waiting For Approve";
    				}else if($field->status == 6){
    					$field->status= "Approved";
    				}else if($field->status == 7){
    					$field->status= "Rejected";
    				}else if($field->status == 8){
    					$field->status= "Completed";
    				}
    			}
    			$i=0;
    			foreach ($listProject as $field1){
    				foreach ($querytask as $field){
    		
    					if($field1->_id == $field->projectId){
    						$field->taskName = "($field1->abbrProjectName)  $field->taskName";
    					}else if($field->projectId == NULL){
    						$i++;
    						if($i==1){
    							$field->taskName = "(ไม่สังกัด)  $field->taskName";
    						}
    					}
    				}
    			}
    		}
    		foreach ($querytask as $field){
    			$createBy = User::findOne(['_id' => $field->createBy]);
    			$field->createBy = "$createBy->nameTh $createBy->sernameTh";
    			if($field->status == 2){
    				$field->status= "New Task";
    			}else if($field->status == 3){
    				$field->status= "Open Task";
    			}else if($field->status == 4){
    				$field->status= "Doing";
    			}else if($field->status == 5){
    				$field->status= "Waiting For Approve";
    			}else if($field->status == 6){
    				$field->status= "Approved";
    			}else if($field->status == 7){
    				$field->status= "Rejected";
    			}else if($field->status == 8){
    				$field->status= "Completed";
    			}
    		}
    		 
    		if($lProject != NULL){
    			$nameP = Project::find();
    			$nameP->where(['_id' => new ObjectID($lProject)]);
    			$nameP = $nameP->all();
    		}else {
    			$nameP = null;
    		}
    		 
    		$eventtype = EventType::find();
    		$eventtype->where(['Calendar' => '3','activeFlag'=>ActiveFlag::ACTIVE,'status' => Status::OPEN]);
    		$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
    		 
    		$eventall = EventType::find()->all();
    		$color = [];
    		foreach ($eventall as $events){
    			$color[(string)$events->_id] = $events->Color;
    		}
    		$query = $query->all();
    		foreach ($query as $field){
    			$field->EventSname = $field->Event_name;
    		}
    		 
    		foreach ($listProject as $field1){
    			foreach ($query as $field){
    				if($field->Calendar == "3"){
    					if($field1->_id == $field->projectID){
    						if($lProject == NULL){
    							$field->Event_name = "($field1->abbrProjectName)  $field->Event_name";
    						}
    					}
    				}
    			}
    		}
    		 
    		return $this->render('indexproject', [
    				'searchModel' => $searchModel,
    				'dataProvider' => $dataProvider,
    				'valuetask'=> null,
    				'valuelistProject'=> $listProject,
    				'valuenameP'=> $nameP,
    				'value'=> null,
    				'valueeventtype' => $eventtype,
    				'valuecolor'=> $color,
    				'valueprivatetask'=> $privatetask,
    				'taskandevent'=> $taskandevent,
    		]);
    	}
    }
    
    public function actionDevision()
    {
    	$searchModel = new Event();
    	$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    	
    	$eventtype = EventType::find();
    	$eventtype->where(['Calendar' => '2','activeFlag'=>ActiveFlag::ACTIVE,'status' => Status::OPEN]);
    	$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
    	
    	$eventall = EventType::find()->all();
    	$color = [];
    	foreach ($eventall as $events){
    		$color[(string)$events->_id] = $events->Color;
    	}
    	
    	$userDepartmentId = Yii::$app->user->identity->depCode;
    	
    	$query = Event::find();
    	$query->where(['Calendar' => ['4']]);
    	$query->orwhere(['depCode' => $userDepartmentId]);
    	$query = $query->all();
    	return $this->render('indexdevision', [
    			'searchModel' => $searchModel,
    			'dataProvider' => $dataProvider,
    			'value'=> $query,
    			'valueeventtype' => $eventtype,
    			'valuecolor'=> $color,
    	]);
    }
   
    public function actionGeteditevent()
    {
    	$data = Yii::$app->request->post();
    	if (Yii::$app->request->isAjax && isset($post['eventId'])) {
    		//$eventId= $post['eventId'];
    		$eventId = explode(":", $data['eventId']);
    		$eventId = $eventId[0];
    		
    		$model = Event::findOne($eventId);
    		$isDelete = $this->isDelete($model, $eventId);
    		$retData['success'] = false;
    		$Event= null;
    		
    		if(!$isDelete){
    			$Event = $model;
    		}
    		
    		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    		return [
    				'eventData' => $Event,
    				'code' => 100,
    				'isDelete' => $isDelete
    		];
    	}
    }
    public function isDuplicate($event_name, $eventId){
    	$condition = [];
    	$query = Event::find();
    	
    	if(!empty($event_name)){
    		$conditions['Event_name'] = $event_name;
    	}
    	
    	if(!empty($conditions)){
    		$query->where($conditions);
    	}
    	
    	if($eventId!= null){
    		$query->andWhere(['<>', '_id', new ObjectID($eventId)]);
    	}
    	
    	$listProject = $query->all();
    	
    	if($listProject != null){
    		return true;
    	}else{
    		return false;
    	}
    }
    public function isDelete($model, $eventId){
    	if($model == null){
    		return true;
    	}else{
    		return false;
    	}
    }
    public function actionEdit(){
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$currentId = Yii::$app->user->identity->_id;
    	
    	$eventId= $request->post('eventId',null);
    	$event_name = $request->post('event_name', null);
    	$start_date = $request->post('start_date', null);
    	$end_date = $request->post('end_date', null);
    	$description = $request->post('description', null);
    	$TypeID = $request->post('TypeID', null);
    	$Allday = $request->post('Allday', null);
    	$CreateBy = $request->post('CreateBy', null);
    	$depCode = $request->post('depCode', null);
    	
    	$model = Event::findOne($eventId);
    	$calendar = EventType::findOne(["_id"=>$TypeID]);
    	$isDelete = $this->isDelete($model, $eventId);
    	$retData['success'] = false;
    	if ($isDelete){
    		$retData['isDelete'] = true;
    	}else{
    		$retData['isDuplicate'] = $this->isDuplicate($event_name, $eventId);
    		if (!$retData['isDuplicate']){
    			if($eventId != null){
    						$model->Event_name = $event_name;
    						$model->Start_Date =  new MongoDate(strtotime($start_date));
    						$model->End_Date =  new MongoDate(strtotime($end_date));
    						$model->Discription =  $description;
    						$model->TypeID =  new ObjectID($TypeID);
    						$model->Allday =  ($Allday=="true")?true:false;
    						$model->CreateBy =  new ObjectID($CreateBy);
    						$model->depCode =  $depCode;
    						$model->TypeName =  $calendar->Type_name;
    			}
    			if($model->save()){
    				$message = true;
    				$retData['success'] = true;
    			}else{
    				$message = false;
    				$retData['success'] = false;
    			}
    		}
    	}
    	echo json_encode($retData);
    }

    public function actionSave(){
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	
    	$eventId= $request->post('eventId',null);
    	$event_name = $request->post('event_name', null);
    	$start_date = $request->post('start_date', null);
    	$end_date = $request->post('end_date', null);
    	$description = $request->post('description', null);
    	$TypeID = $request->post('TypeID', null);
    	$proID = $request->post('proID', null);
    	$Allday = $request->post('Allday', null);
    	$CreateBy = $request->post('CreateBy', null);
    	$depCode = $request->post('depCode', null);
    	
    	$calendar = EventType::findOne(["_id"=>$TypeID]);
    	$retData['success'] = false;
    	$retData['isDuplicate'] = $this->isDuplicate($event_name, $eventId);
    	if (!$retData['isDuplicate']){
    		$model = null;
    		if ($model == null){
    			$model = new Event();
    			$model->Event_name = $event_name;
    			$model->Start_Date =  new MongoDate(strtotime($start_date));;
    			$model->End_Date =  new MongoDate(strtotime($end_date));
    			$model->Discription =  $description;
    			$model->TypeID =  new ObjectID($TypeID);
    			if($proID){
    				$model->projectID =  new ObjectID($proID);
    				$projectName = Project::findOne(['_id' => new ObjectID($proID)]);
    				if($projectName == null){
    					$model->projectName =  "ส่วนตัว";
    				}else
    				$model->projectName =  $projectName->projectName;
    				$model->EventSname =  $event_name;
    			}  			
    			$model->Allday =  ($Allday=="true")?true:false;
    			$model->CreateBy =  new ObjectID($CreateBy);
    			$model->Calendar =  $calendar->Calendar;
    			$model->depCode =  $depCode;
    			$model->TypeName =  $calendar->Type_name;
    		}
    		if($model->save()){
    			$retData['success'] = true;if(!$proID){
    			Yii::$app->session->setFlash('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');}
    			//return $this->redirect(['index']);
    		}else{
    			$retData['success'] = false;if(!$proID){
    			Yii::$app->session->setFlash('danger', 'มีข้อผิดพลาด');}
    			//return $this->redirect(['index']);
    		}
    	}
    	echo json_encode($retData);
    }
    public function actionSendemail(){
    	$userId = Yii::$app->user->identity->_id;
    	$request = Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$check = $request->post('check',null);
    	
    	$sendemail = User::findOne($userId);
    	
    	if($check == null){
    		$sendemail->sendemail = 1;
    	}else if ($check == "1"){
    		$sendemail->sendemail = 0;
    	}else if ($check == "0"){
    		$sendemail->sendemail = 1;
    	}
    	if($sendemail->save()){
    		$message = true;
    		$retData['success'] = true;
    	}else{
    		$message = false;
    		$retData['success'] = false;
    	}
    	echo json_encode($retData);
    }
    public function actionHoliday(){
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	
    	$eventId= $request->post('eventId',null);
    	$event_name = $request->post('event_name', null);
    	$start_date = $request->post('start_date', null);
    	$end_date = $request->post('end_date', null);
    	$description = $request->post('description', null);
    	$TypeID = $request->post('TypeID', null);
    	$proID = $request->post('proID', null);
    	$Allday = $request->post('Allday', null);
    	$CreateBy = $request->post('CreateBy', null);
    	$depCode = $request->post('depCode', null);
    	
    	$calendar = EventType::findOne(["_id"=>$TypeID]);
    	$retData['success'] = false;
    	$retData['isDuplicate'] = $this->isDuplicate($event_name, $eventId);
    	if (!$retData['isDuplicate']){
    		$model = null;
    		if ($model == null){
    			$model = new Event();
    			$model->Event_name = $event_name;
    			$model->Start_Date =  new MongoDate(strtotime($start_date));;
    			$model->End_Date =  new MongoDate(strtotime($end_date));
    			$model->Discription =  $description;
    			$model->TypeID =  new ObjectID($TypeID);
    			if($proID){
    				$model->projectID=  new ObjectID($proID);
    			}
    			$model->Allday =  ($Allday=="true")?true:false;
    			$model->CreateBy =  new ObjectID($CreateBy);
    			$model->Calendar =  $calendar->Calendar;
    			$model->depCode =  $depCode;
    			$model->TypeName =  $calendar->Type_name;
    		}
    		if($model->save()){
    			$message = true;
    			$retData['success'] = true;
    		}else{
    			$message = false;
    			$retData['success'] = false;
    		}
    	}
    	echo json_encode($retData);
    }
    
    public function actionDelete(){
    	//Permission::havePermission(Permission::DELETE_CATEGORY);
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	$eventId = $request->post('eventId', null);
    	
    	$model = Event::findOne($eventId);
    	$isDelete = $this->isDelete($model, $eventId);
    	$retData['success'] = false;
    	//$isUsedInProject = Project::findAllProjectByCategory(new ObjectID($eventId));
    	
    	if($isDelete){
    		$retData['isDelete'] = true;
    	}else{
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
    public function actionListevent()
    {
    	$searchModel = new Event();
    	$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    	$query = Event::find();
    	$query->where(['Calendar' => ['1']]);
    	$query->orderBy(['Start_Date[sec]'=>SORT_ASC]);
    	$query = $query->all();
    	
    	$eventtype = EventType::find();
    	$eventtype->where(['Calendar' => ['1']]);
    	$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
    	
    	
    	$eventall = EventType::find()->all();
    	$color = [];
    	foreach ($eventall as $events){
    		$color[(string)$events->_id] = $events->Color;
    	}
    	$active = 0;
    	
    	return $this->render('listevent', [
    			'searchModel' => $searchModel,
    			'dataProvider' => $dataProvider,
    			'value'=> $query,
    			'valueeventtype' => $eventtype,
    			'valuecolor'=> $color,
    			'valueactive'=> $active,
    	]);

    }
    
    public function actionListeventd()
    {
    	$searchModel = new Event();
    	$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    	$query = Event::find();
    	$query->where(['Calendar' => ['2']]);
    	$query->orderBy(['Start_Date[sec]'=>SORT_ASC]);
    	$query = $query->all();
    	
    	$eventtype = EventType::find();
    	$eventtype->where(['Calendar' => ['2']]);
    	$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
    	
    	
    	$eventall = EventType::find()->all();
    	$color = [];
    	foreach ($eventall as $events){
    		$color[(string)$events->_id] = $events->Color;
    	}
    	$active = 2;
    	
    	return $this->render('listevent', [
    			'searchModel' => $searchModel,
    			'dataProvider' => $dataProvider,
    			'value'=> $query,
    			'valueeventtype' => $eventtype,
    			'valuecolor'=> $color,
    			'valueactive'=> $active,
    	]);
    	
    }
    public function actionListeventp()
    {
    	$userId = Yii::$app->user->identity->_id;
    	$request = \Yii::$app->request;
    	$lProject = $request->post('lProject',null);
    	$searchModel = new Event();
    	$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
    	
    	$listProject = Project::find();
    	$listProject->where(['status' => [1]]);
    	$listProject->andwhere(['member.userId' => $userId]);
    	$listProject = $listProject->all();
    	$lpro = [];
    	foreach ($listProject as $field){
    		$field->projectName = "$field->projectName ($field->abbrProjectName)";
    		$lpro[] = $field->_id;
    	}
    	$query = Event::find();
    	if($lProject != NULL){
    		$query->where(['projectID' => new ObjectID($lProject)]);
    		$nameP = Project::find();
    		$nameP->where(['_id' => new ObjectID($lProject)]);
    		$nameP = $nameP->all();
    	}else {
    		$query->where(['projectID' => $lpro]);
    		$nameP = null;
    	}
    	   	 	
    	$query->orderBy(['Start_Date[sec]'=>SORT_ASC]);
    	$query = $query->all();
    	
    	$eventtype = EventType::find();
    	$eventtype->where(['Calendar' => ['3']]);
    	$eventtype = ArrayHelper::map($eventtype->all(),function ($eventtypeModel){return  (string)$eventtypeModel->_id;},'Type_name');
    	
    	
    	$eventall = EventType::find()->all();
    	$color = [];
    	foreach ($eventall as $events){
    		$color[(string)$events->_id] = $events->Color;
    	}
    	$active = 1;
    	return $this->render('listevent', [
    			'searchModel' => $searchModel,
    			'dataProvider' => $dataProvider,
    			'value'=> $query,
    			'valueeventtype' => $eventtype,
    			'valuenameP'=> $nameP,
    			'valuelist' => $listProject,
    			'valuecolor'=> $color,
    			'valueactive'=> $active,
    	]);
    	
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
//     public function actionDelete($id)
//     {
//         $this->findModel($id)->delete();

//         return $this->redirect(['index']);
//     }

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
