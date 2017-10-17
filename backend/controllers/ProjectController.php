<?php

namespace backend\controllers;

use Yii;
use backend\models\Project;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\Category;
use backend\models\Department;
use backend\models\Team;
use backend\models\Task;
use MongoDB\BSON\ObjectID;
use common\models\User;
use \MongoDate;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\data\Pagination;
use backend\models\Comment;
use common\libs\ActiveFlag;
use backend\models\AuthItem;
use common\libs\RoleInProject;
use yii\filters\AccessControl;
use common\libs\Status;
use common\libs\DateTime;
use backend\models\backend\models;
use backend\models\Policy;
use yii\base\ErrorException;
use common\libs\Permission;
use common\libs\PermissionInProject;
use backend\models\Log;
use yii\web\UploadedFile;




/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends Controller
{
	const SORT_PROJECT_NAME = 1;
	const SORT_STATUS = 2;
	const SORT_START_DATE = 3;
	const SORT_END_DATE = 4;
	
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
     * Lists all Project models.
     * @return mixed
     */
    public function actionIndex()
    {
    	Permission::havePermission(Permission::SEARCH_PROJECT);
    	$alert = Yii::$app->session->getFlash('alert');
    	$request = Yii::$app->request;
    	$name = $request->post('name');
    	$status = $request->post('status',null);
    	$sort = $request->post('sort',self::SORT_END_DATE);
    	$type = $request->post('type',null);
    	
    	if(empty($name)){
    		$name = $request->get('name');
    	}
    	if(empty($status)){
    		$status = $request->get('status',null);
    	}
    	if(empty($sort)){
    		$sort = $request->get('sort',self::SORT_END_DATE);
    	}
    	if(empty($type)){
    		$type = $request->get('type',null);
    	}
    	
    	$name = trim($name);
    	
		$userId = Yii::$app->user->identity->_id;
		$limit = Yii::$app->user->identity->limit;
		$amountofProject = Yii::$app->user->identity->amountofproject;
		$arrUser = [];
		$amount = true;
		if ($limit == 1){
				$countAmountProject = Project::findNumberOfProjectByUserId($userId);
				$CountProject = (int)$countAmountProject;
				if ($amountofProject > $CountProject){
					$amount = true;
				}else {
					$amount = false;
				}
		}
		$conditions = [];
		$query = Project::find();
		$query->orWhere(['status'=>Status::OPEN]);
		$query->orWhere(['status'=>Status::CLOSE]);
		if(!empty($status)){
			$conditions['status'] = (int)$status;
		}
		if(!empty($conditions)){
			$query->where($conditions);
		}
		if(!empty($name)){
			$query->andWhere(['like', "projectName", $name]);
		}
		if(!empty($userId)){
			if(!empty($type)){
				$query->andwhere(['member' => ['$elemMatch' => ['userId' => $userId,'type' => (int)$type]]]);
			}
			else{
				$query->andwhere(array('member.userId' => $userId));
			}
			$query->andwhere(["activeFlag" => ActiveFlag::ACTIVE]);
		}
		$pagination = new Pagination([
				'defaultPageSize' => 15,
				'totalCount' => $query->count(),
		]);
		$query->offset($pagination->offset);
		$query->limit($pagination->limit);
		$query->orderBy(['status'=>SORT_ASC]);
		 
		if(!empty($sort)){
			if($sort == 1){
				$query->addOrderBy(['project_name'=>SORT_ASC]);
			}elseif ($sort == 2){
				$query->addOrderBy(['status'=>SORT_ASC]);
			}elseif ($sort == 3){
				$query->addOrderBy(['startDate'=>SORT_ASC]);
			}else{
				$query->addOrderBy(['endDate'=>SORT_ASC]);
			}
		}

	   	$value = $query->all();
    	    	
    	$pagination->params = [
    			'page'=>$pagination->page,
    			'name'=>$name,
    			'status'=>$status,
    			'sort'=>$sort,
    			'type'=>$type
    	];

    	$category = Category::find()->all();
    	$arrCategory = [];
    	if($category){
    		foreach ($category as $obj){
    			$arrCategory[(string)$obj->_id] = $obj->categoryName;
    		}
    	}
    	$projectdate = Project::find()->all();
    	$projecttype = Project::find(['member.userId' => $userId])->all();
    	$now = new \MongoDate();
    	$date1 = null;
    	$date2 = null;
    	$date3 = null;
    	$date4 = null;
    	$date5 = null;
    	$arrdate1 = [];
    	$arrdate2 = [];
    	$arrtask1 = [];
    	$arrtask2 = [];
    	$arrtype = [];
    	$arrdepart = [];
    	if($projectdate){
    		foreach ($projectdate as $obj){
    			$date1 = date_create(date('Y/m/d',  strtotime('+6 Hour',$obj->startDate["sec"])));
    			$date2 = date_create(date('Y/m/d',  strtotime('+6 Hour',$obj->endDate["sec"])));
    			$date3 = date_create(date('Y/m/d ',  strtotime('+6 Hour',$now->sec)));
    			if($date1 <= $date3){
    			$date4 = date_diff($date1,$date2);
    			$date5 = date_diff($date1,$date3);
    			$arrdate1[(string)$obj->_id] = (int)$date4->days;
    			$arrdate2[(string)$obj->_id] = (int)$date5->days;
    			$arrtask1[(string)$obj->_id] = Task::find()->where(['projectId'=>$obj->_id])->count();
    			$arrtask2[(string)$obj->_id] = Task::find()->where(['projectId'=>$obj->_id, 'status'=> (int)Status::APPROVED_TASK])->count();
    			}
    			else{
    				$arrdate1[(string)$obj->_id] = 0;
    				$arrdate2[(string)$obj->_id] = 0;
    			}
    			$arrtask1[(string)$obj->_id] = Task::find()->where(['projectId'=>$obj->_id])->count();
    			$arrtask2[(string)$obj->_id] = Task::find()->where(['projectId'=>$obj->_id, 'status'=> (int)Status::APPROVED_TASK])->count();
    			
    		}
    	}
    	
    	if($projecttype){
    		foreach ($projecttype as $obj2){
    			foreach ($obj2->member as $obj3){
    				if($obj3['userId'] == $userId){
    					$arrtype[(string)$obj2->_id] = (int)$obj3['type'];
    			    		}
    				}
    		}
    	}
    	
    	
    	if($alert != null){
    		$alert = ($alert)?true:false;
    	}else{
    		$alert = "undefined";
    	}
//     	$projectcomment = Project::find()->all();
//     	$arrComment = [];
//     	if (!empty($projectcomment)){
//     		foreach ($projectcomment as $comment){
//     			$arrComment[(string)$comment->_id] = Comment::find(['refId' => $comment->_id])->all();
    			
//     		}
//     	}
    	$modelcomment = new Comment();
        return $this->render('index', [
 			'value' => $value,'name' => $name,
        	"pagination"=>$pagination,
        	'status' => $status, 'sort' => $sort,
        	'arrCategory' => $arrCategory,
        	'alert' => $alert,
        	'type' =>	$type,'arrdate1' => $arrdate1,
        		'arrdate2' => $arrdate2,
        		'arrtask1' => $arrtask1,
        		'arrtask2' => $arrtask2,
        		'arrtype' => $arrtype,
        		'amount' => $amount,
        		'modelcomment' => $modelcomment
        		
        ]);
    }
    
    public function actionSave(){
    
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    
    	$name = trim($request->post('name', null));
    	$abbrProject = trim($request->post('abbrProject', null));
    	$description = trim($request->post('description', null));
    	$startdate = $request->post('startdate', null);
    	$enddate = $request->post('enddate', null);
    	$member = $request->post('member', null);
    	$category = $request->post('category', null);
    	$department = $request->post('department', null);
    	$isCreateTeam = $request->post('isCreateTeam', null);
    	$teamName = trim($request->post('teamName', null));
    	$model = null;

    	$currentId = Yii::$app->user->identity->_id;
    	$member = json_decode($member);
    	$nummberMember = sizeof($member);
    	 
    	//checkDuplicateProjectName
    	$isDuplicateProject = false;
    	$project = Project::findAllProjectByProjectNameAndDepartmentId($name,$department);
    	if($project){
    		$isDuplicateProject = true;
    	}
    	//checkDupplicateTeamName
    	$isDuplicateTeam = false;
    	if($isCreateTeam){
    		$team = Team::findOne(['teamName' => $teamName]);
    		if($team){
    			$isDuplicateTeam = true;
    		}
    	}
    	
    	$isDuplicateAbbrProjectName = $this->isDuplicateAbbrProjectName($abbrProject, null);
    	$retData['isDuplicateAbbrProject'] = $isDuplicateAbbrProjectName;
    	
    	if($isDuplicateProject == false && $isDuplicateTeam == false && $isDuplicateAbbrProjectName == false){
    		if($isCreateTeam){
    			$teamModel = new Team();
    			$teamModel->teamName = $teamName;
    			$teamModel->description = $teamName;
    			$teamModel->activeFlag = ActiveFlag::ACTIVE;
    			$teamModel->createDate = new MongoDate();
    			$teamModel->createBy = $currentId;
    	   
    			$teamMember = [];
    			for ($i = 0; $i < $nummberMember; $i++) {
    				$teamMember[$i]['userId'] = new ObjectID($member[$i]->userId);
    				$teamMember[$i]['activeFlag'] = ActiveFlag::ACTIVE;
    			}
    			$teamModel->member = $teamMember;
    	   
    			$teamModel->save();
    	   
    			//save new project with new team in project
    			$newTeamQuery = Team::findOne(['teamName' => $teamName]);
    			$newTeamId = $newTeamQuery->_id;
    	   
    			$projectMember = [];
    			for ($i = 0; $i < $nummberMember; $i++) {
    				$projectMember[$i]['userId'] = new ObjectID($member[$i]->userId);
    				$projectMember[$i]['team'][0]['teamId'] = new ObjectID($newTeamId);
    				if($currentId == $projectMember[$i]['userId']){
    					$projectMember[$i]['type'] = RoleInProject::PROJECT_MANAGER;;
    					$projectMember[$i]['permission'] = PermissionInProject::$arrPermissionProjectManagement;
    				}else{
    					$projectMember[$i]['type'] = RoleInProject::TEAM_DEVELOPER;
    					$projectMember[$i]['permission'] = PermissionInProject::$arrPermissionDeveloper;
    				}
                    $projectMember[$i]['activeFlag'] = ActiveFlag::ACTIVE;
    			}
    			$member = $projectMember;
    		}else{
    			// add Member
    			for ($i = 0; $i < $nummberMember; $i++) {
    				$userId = $member[$i]->userId;
    				$member[$i]->userId = new ObjectID($userId);
    				 
    				$team = $member[$i]->team;
    				$nummberTeam = sizeof($team);
    				for ($j = 0; $j < $nummberTeam; $j++) {
    					$member[$i]->team[$j]->teamId = new ObjectID($team[$j]->teamId);
    				}
    				if($currentId == $member[$i]->userId){
    					$member[$i]->type = RoleInProject::PROJECT_MANAGER;
    					$member[$i]->permission = PermissionInProject::$arrPermissionProjectManagement;
    				}else{
    					$member[$i]->type = RoleInProject::TEAM_DEVELOPER;
    					$member[$i]->permission = PermissionInProject::$arrPermissionDeveloper;
    				}
                    $member[$i]->activeFlag = ActiveFlag::ACTIVE;
    			}
    		}
    
    
    		if ($model == null){
    			$model = new Project();
    			$model->projectName = $name;
    			$model->abbrProjectName = $abbrProject;
    			$model->startDate = new MongoDate(strtotime($startdate));
    			$model->endDate = new MongoDate(strtotime($enddate));
    			$model->description =  $description;
    			$model->status = Status::OPEN;
    			$model->category = $this->haveCategory($category);
    			$model->departmentId = $department;
    			$model->activeFlag = ActiveFlag::ACTIVE;
    			$model->member = $member;
    			$model->createBy = new ObjectID($currentId);
    			$model->createDate = new MongoDate();
    			$model->isCreatedTeam = ($isCreateTeam)?true:false;
    		}
    
    		if($model->save()){
    			$message = true;
    			$retData['success'] = true;
    		}else{
    			$message = false;
    			$retData['success'] = false;
    		}
    	}else{
    		$retData['success'] = false;
    	}
    	 
    	$retData['isDuplicateProject'] = $isDuplicateProject;
    	$retData['isDuplicateTeam'] = $isDuplicateTeam;
    	//     	Yii::$app->session->setFlash('alert', $message);
    	echo json_encode($retData);
    
    }
    
    public function haveCategory($categoryId){
    	$model = Category::findOne($categoryId);
    	if($model == null){
    		return null;
    	}else{
    		return new ObjectID($categoryId);
    	}
    }
    
    /**
     * Displays a single Project model.
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
     * Creates a new Project model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
    	$userId = Yii::$app->user->identity->_id;
    	$limit = Yii::$app->user->identity->limit;
    	$amountofProject = Yii::$app->user->identity->amountofproject;
    	// drop down Category
    	$categoryModel = new Category();
    	$listCategory = Category::findAllCategoryByActiveFlag(ActiveFlag::ACTIVE);
    	$arrCategory = ArrayHelper::map($listCategory,function ($categoryModel){return  (string)$categoryModel->_id;},'categoryName');
		
    	$project = Project::findAllProjectByProjectNameAndDepartmentId("", Yii::$app->user->identity->depCode);
    	$arrProject = [];
    	if($project){
    		foreach ($project as $obj){
    			$arrProject[] = $obj->projectName;
    		}
    	}
    	
    	$departmentModel = new Department();
    	$arrDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
		
		$user = new User();
		$listUser  = User::findAllUserByStatus(10);
		
		$team = new Team();
		$listTeam  = Team::findAllTeamByActiveFlag(ActiveFlag::ACTIVE);
		
		// Json MemberOfTeam
    	$arrTeamMember = [];
    	
    	foreach ($listTeam as $obj){
    		$index = 0;
    		if($obj->activeFlag == ActiveFlag::ACTIVE){
	    		$member = $obj->member;
	    		$size = sizeof($member);
	    		$arrMember = [];
	    		for($i = 0; $i < $size;$i++)
	    		{
	    			if($member[$i]["activeFlag"] == ActiveFlag::ACTIVE){
	    				$arrMember[$index]['userId'] = (string)$member[$i]["userId"];
	    				$arrMember[$index]['name'] = User::getUserName($member[$i]["userId"]);
	    				$index++;
	    			}
	    		}
	    		$arrTeamMember[(string)$obj->_id] = $arrMember;
    		}
    	}
    	
    	if($limit == User::LIMIT){
	    	if(!$this->isCanCreated($userId,$amountofProject)){
	    		Yii::$app->getSession()->setFlash('alert',[
	    		'body'=>'ไม่สามารถสร้างได้ เนื่องจากท่านได้มีการสร้างโครงการครบจำนวนที่กำหนดแล้ว',
	    		'options'=>['class'=>'alert-danger']
	    		]);
	    		return Yii::$app->getResponse()->redirect('index');
	    	}
    	}

		return $this->render('create', [
	     	'arrCategory' => $arrCategory,
			'arrDepartment' => $arrDepartment,
			'listUser' => $listUser,
			'listTeam' => $listTeam,
			'arrTeamMember' => json_encode($arrTeamMember),
			'arrProject' => json_encode($arrProject),
		]);
    }

    /**
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $_id
     * @return mixed
     */
  public function actionEdit()
    {
    	$request = Yii::$app->request;
    	$projectId = $request->post('projectId',null);
    	$projectData = Project::findOne($projectId);
    	$baseUrl = \Yii::getAlias ( '@web' );
    	
    	if($projectId == null){
    		return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
    	}else{
    		if($projectData == null){
    			Yii::$app->getSession()->setFlash('alert',[
    					'body'=>'โครงการนี้ถูกลบแล้วโดยผู้ใช้ท่านอื่นแล้ว',
    					'options'=>['class'=>'alert-danger']
    			]);
    			return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
    		}else{
    			if($projectData->status == Status::CANCEL){
    				Yii::$app->getSession()->setFlash('alert',[
    						'body'=>'โครงการนี้ถูกยกเลิกแล้วโดยผู้ใช้ท่านอื่นแล้ว',
    						'options'=>['class'=>'alert-danger']
    				]);
    				return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
    			}else if($projectData->status == Status::CLOSE){
    				Yii::$app->getSession()->setFlash('alert',[
    						'body'=>'โครงการนี้ถูกปิดแล้วโดยผู้ใช้ท่านอื่นแล้ว',
    						'options'=>['class'=>'alert-warning']
    				]);
    				return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
    			}else if($projectData->activeFlag == ActiveFlag::INACTIVE){
    				Yii::$app->getSession()->setFlash('alert',[
    						'body'=>'โครงการนี้ถูกปิดใช้งานแล้วโดยผู้ใช้ท่านอื่นแล้ว',
    						'options'=>['class'=>'alert-danger']
    				]);
    				return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
    			}
    		}
    	}
    	
    	$permission = PermissionInProject::EDIT_PROJECT;
    	$havePermission = PermissionInProject::havePermissionInProject($permission, $projectData->member);
    	if(!$havePermission){
    		PermissionInProject::haveNotPermission();
    	}
    	
    	// drop down Category
    	$categoryModel = new Category();
    	$listCategory = Category::findAllCategoryByActiveFlag(ActiveFlag::ACTIVE);
    	$arrCategory = ArrayHelper::map($listCategory,function ($categoryModel){return  (string)$categoryModel->_id;},'categoryName');
    	
    	$project = Project::findAllProjectByProjectNameAndDepartmentId("", $projectData->departmentId);
    	$arrProject = [];
    	if($project){
    		foreach ($project as $obj){
    			$arrProject[] = $obj->projectName;
    		}
    	}
    	 
    	$departmentModel = new Department();
    	$arrDepartment = ArrayHelper::map(Department::find()->all(),function ($departmentModel){return  (string)$departmentModel->depCode;},'depName');
    	
    	$arrTeam = [];
    	$listTeam = Team::find()->all();
    	foreach ($listTeam as $obj){
    		$arrTeam[(string)$obj->_id] = $obj->teamName;
    	}
    	
    	$team = new Team();
    	$listTeam  = Team::findAllTeamByActiveFlag(ActiveFlag::ACTIVE);
    	
    	$teamMember = $this->getTeamMemberProject($projectData);
    	$member = $this->getMemberProject($projectData);
    	$arrTeamMember = $this->getArrayTeamMember($listTeam, $teamMember);
    	$arrUserInProject = $this->getArrayMemberInProject($member);
    	$arrTeamInProject = $this->getTeamInProject($teamMember, $arrTeam);
    	$listUser = $this->getUserNotInProject($member);
    	
    	$listTeam = $this->getTeamNotInAllUseInProject($teamMember, $listTeam);
    	
		return $this->render(
			'update', [
			'projectId' => $projectId,
			'projectData'=>$projectData,
			'arrCategory' => $arrCategory,
			'arrDepartment' => $arrDepartment,
			'listUser' => $listUser,
			'listTeam' => $listTeam,
			'arrTeamMember' => json_encode($arrTeamMember),
			'arrProject' => json_encode($arrProject),
			'member' => $member,
			'teamMember' => $teamMember,
			'arrTeam' => $arrTeam,
			'arrUserInProject' => json_encode($arrUserInProject),
			'arrTeamInProject' => json_encode($arrTeamInProject)
		]
		);
    }
    
    public function actionSaveedit(){
    	$request = \Yii::$app->request;
    	$response = Yii::$app->response;
    	$response->format = \yii\web\Response::FORMAT_JSON;
    	
    	$projectId = $request->post('projectId', null);
    	$name = trim($request->post('name', null));
    	$abbrProject = trim($request->post('abbrProject', null));
    	$description = trim($request->post('description', null));
    	$startdate = $request->post('startdate', null);
    	$enddate = $request->post('enddate', null);
    	$member = $request->post('member', null);
    	$categoty = $request->post('category', null);
    	$department = $request->post('department', null);
    	$isCreateTeam = $request->post('isCreateTeam', null);
    	$teamName = trim($request->post('teamName', null));
    	$model = null;
    	
    	$currentId = Yii::$app->user->identity->_id;
    	$member = json_decode($member);
    	$nummberMember = sizeof($member);
    	
    	//checkDuplicateProjectName
    	$isDuplicateProject = false;
    	$project = Project::findAllProjectByProjectNameAndDepartmentIdWithoutProjectId($projectId,$name,$department);
    	$retData['$project'] = $projectId;
    	if($project){
    		$isDuplicateProject = true;
    	}
    	//checkDupplicateTeamName
    	$isDuplicateTeam = false;
    	$templog = [];
    	$newData = [];
    	$model = Project::findOne($projectId);
    	$oldData = Project::findOne($projectId);
    	$logs = new Log();
    	$templog['_id'] = $oldData->_id;
    	$templog['activeFlag'] = $oldData->activeFlag;
    	$templog['category'] = $oldData->category;
    	$templog['createBy'] = $oldData->createBy;
    	$templog['createDate'] = $oldData->createDate;
    	$templog['departmentId'] = $oldData->departmentId;
    	$templog['description'] = $oldData->description;
    	$templog['endDate'] = $oldData->endDate;
    	$templog['isCreatedTeam'] = $oldData->isCreatedTeam;
    	$templog['member'] = $oldData->member;
    	$templog['projectName'] = $oldData->projectName;
    	$templog['abbrProjectName'] = $oldData->abbrProjectName;
    	$templog['startDate'] = $oldData->startDate;
    	$templog['status'] = $oldData->status;
    	//old data in member of project
    	$arrMemberInProject = $model->member;
    	$sizeArrMemberInProject = sizeof($arrMemberInProject);
    	$arrMember = [];
    	for ($i=0;$i<$sizeArrMemberInProject; $i++){
    		$userId = (string)$arrMemberInProject[$i]['userId'];
    		$activeFlag = $arrMemberInProject[$i]['activeFlag'];
    		$type = $arrMemberInProject[$i]['type'];
    		$permission = $arrMemberInProject[$i]['permission'];
    		$arrMember[(string)$userId]['userId'] = $userId;
    		$arrMember[(string)$userId]['activeFlag'] = $activeFlag;
    		$arrMember[(string)$userId]['type'] = $type;
    		$arrMember[(string)$userId]['permission'] = $permission;
    	}
    	
    	if($model->isCreatedTeam == true){
    		$isCreateTeam = false;
    	}else{
	    	if($isCreateTeam){
	    		$team = Team::findOne(['teamName' => $teamName]);
	    		if($team){
	    			$isDuplicateTeam = true;
	    		}
	    	}
    	}
    	
    	$isDuplicateAbbrProjectName = $this->isDuplicateAbbrProjectName($abbrProject, $projectId);
    	$retData['isDuplicateAbbrProject'] = $isDuplicateAbbrProjectName;
    	
    	if($isDuplicateProject == false && $isDuplicateTeam == false && $isDuplicateAbbrProjectName == false){
    		if($isCreateTeam){
    			$teamModel = new Team();
    			$teamModel->teamName = $teamName;
    			$teamModel->description = $teamName;
    			$teamModel->activeFlag = ActiveFlag::ACTIVE;
    			$teamModel->createDate = new MongoDate();
    			$teamModel->createBy = $currentId;
    	
    			$teamMember = [];
    			for ($i = 0; $i < $nummberMember; $i++) {
    				$teamMember[$i]['userId'] = new ObjectID($member[$i]->userId);
    				$teamMember[$i]['activeFlag'] = ActiveFlag::ACTIVE;
    			}
    			$teamModel->member = $teamMember;
    	
    			$teamModel->save();
    	
    			//save new project with new team in project
    			$newTeamQuery = Team::findOne(['teamName' => $teamName]);
    			$newTeamId = $newTeamQuery->_id;
    	
    			$projectMember = [];
    			for ($i = 0; $i < $nummberMember; $i++) {
    				$userId = $member[$i]->userId;
    				$projectMember[$i]['userId'] = new ObjectID($userId);
    				$projectMember[$i]['team'][0]['teamId'] = new ObjectID($newTeamId);
    				
    				try {
    				   	$projectMember[$i]['type'] = $arrMember[(string)$userId]['type'];
    				   	$projectMember[$i]['permission'] = $arrMember[(string)$userId]['permission'];
    				} catch (ErrorException $e) {
    				   	$projectMember[$i]['type'] = RoleInProject::TEAM_DEVELOPER;
    				   	$projectMember[$i]['permission'] = PermissionInProject::$arrPermissionDeveloper;
    				}
    				
    				$projectMember[$i]['activeFlag'] = ActiveFlag::ACTIVE;
    			}
    			$member = $projectMember;
    		}else{
    			// add Member
    			$projectMember = [];
    			for ($i = 0; $i < $nummberMember; $i++) {
    				$userId = $member[$i]->userId;
    				$member[$i]->userId = new ObjectID($userId);
    					
    				$team = $member[$i]->team;
    				$nummberTeam = sizeof($team);
    				for ($j = 0; $j < $nummberTeam; $j++) {
    					$member[$i]->team[$j]->teamId = new ObjectID($team[$j]->teamId);
    				}
    				
    				try {
    					$member[$i]->type = $arrMember[(string)$userId]['type'];
    					$member[$i]->permission = $arrMember[(string)$userId]['permission'];
    				} catch (ErrorException $e) {
    					$member[$i]->type = RoleInProject::TEAM_DEVELOPER;
    					$member[$i]->permission = PermissionInProject::$arrPermissionDeveloper;
    				}
    				
    				$member[$i]->activeFlag = ActiveFlag::ACTIVE;
    			}
    			$retData['data'] = $member;
    		}
    	
    		if ($model != null){
    			$model->projectName = $name;
    			$model->abbrProjectName = $abbrProject;
    			$model->startDate = new MongoDate(strtotime($startdate));
    			$model->endDate = new MongoDate(strtotime($enddate));
    			$model->description =  $description;
    			$model->status = Status::OPEN;
    			$model->category = new ObjectID($categoty);
    			$model->departmentId = $department;
    			$model->activeFlag = ActiveFlag::ACTIVE;
    			$model->member = $member;
    			if($model->isCreatedTeam != true){
    				$model->isCreatedTeam = ($isCreateTeam)?true:false;
    			}
    			$newData['_id'] = $model->_id;
    			$newData['projectName'] = $model->projectName;
    			$newData['startDate'] = $model->startDate;
    			$newData['endDate'] = $model->endDate;
    			$newData['description'] = $model->description;
    			$newData['status'] = $model->status;
    			$newData['abbrProjectName'] = $model->abbrProjectName;
    			$newData['category'] = $model->category;
    			$newData['departmentId'] = $model->departmentId;
    			$newData['activeFlag'] = $model->activeFlag;
    			$newData['member'] = $model->member;
    			$newData['isCreatedTeam'] = $model->isCreatedTeam;
    			$newData['createBy'] = $model->createBy;
    			$newData['createDate'] = $model->createDate;
    		}
    	
    		if($model->save()){
    			$logs->oldData = $templog;
    			$logs->newData = $newData;
    			$logs->userId = $currentId;
    			$logs->refId = new ObjectID($projectId);
    			$logs->editDate = new MongoDate();
    			$logs->action = 'แก้ไขโครงการ';
    			$logs->memberId = '';
    			if ($logs->save()){
    				$message = true;
    				$retData['success'] = true;
    			}
    			
    		}else{
    			$message = false;
    			$retData['success'] = false;
    		}
    	}else{
    		$retData['success'] = false;
    	}
    	
    	$retData['isDuplicateProject'] = $isDuplicateProject;
    	$retData['isDuplicateTeam'] = $isDuplicateTeam;
    	//     	Yii::$app->session->setFlash('alert', $message);
    	echo json_encode($retData);
    }
    
    public function getTeamNotInAllUseInProject($teamMember, $listTeam){
    	$team = [];
    	$size = sizeof($teamMember);
    	foreach ($listTeam as $obj){
    		$isTrue = false;
    		for($i=0; $i<$size; $i++){
    			$sizeMemberInTeam = sizeof($teamMember[$i]['user']);
	    		if((string)$obj->_id == (string)$teamMember[$i]['teamId']){
	    			$isTrue = $this->isEqualNumberMember($obj->member, $sizeMemberInTeam);
	    			break;
	    		}
    		}
    		if(!$isTrue){
    			$team[] = $obj;
    		}
    	}
    	
    	return $team;
    }
    
    public function isEqualNumberMember($member, $numberMember){
    	$count = 0;
    	$size = sizeof($member);
    	for ($i=0; $i<$size; $i++){
    		if($member[$i]['activeFlag'] == ActiveFlag::ACTIVE){
    			$count++;
    		}
    	}
    	return $count == $numberMember;
    }
    
    public function getTeamInProject($teamMember, $arrTeam){
    	$size = sizeof($teamMember);
    	$team = [];
    	for($i=0; $i<$size; $i++){
    		$team[$i]['teamId'] = $teamMember[$i]['teamId'];
    		$team[$i]['name'] = $arrTeam[(string)$teamMember[$i]['teamId']];
    		$team[$i]['active'] = false;
    		$member = $teamMember[$i]['user'];
    		$sizeMember = sizeof($member);
    		$arrTeamMember = [];
    		for($j=0; $j<$sizeMember; $j++){
    			$arrTeamMember[$j]['userId'] = $member[$j];
    			$arrTeamMember[$j]['name'] = User::getUserName($member[$j]);
    			$arrTeamMember[$j]['active'] = false;
    		}
    		$team[$i]['member'] = $arrTeamMember;
    	}
    	
    	return $team;
    }
    
    public function getArrayTeamMember($listTeam, $teamInProject){
    	$arrTeamMember = [];
    	foreach ($listTeam as $obj){
    			$member = $obj->member;
    			$size = sizeof($member);
    			$arrMember = [];
    			$arrMember = $this->setUseInProject($obj->_id, $member, $size, $teamInProject);
    			$arrTeamMember[(string)$obj->_id] = $arrMember;
    	}
    	
    	return $arrTeamMember;
    }
    
    public function setUseInProject($teamId, $memberInTeam, $size, $teamInProject){
    	$index = 0;
    	$member = [];
    	$sizeOfTeamInProject = sizeof($teamInProject);
    	if($sizeOfTeamInProject > 0){
    		foreach ($teamInProject as $team){
    			$index = 0;
    			for($i = 0; $i < $size;$i++){
    				if($memberInTeam[$i]["activeFlag"] == ActiveFlag::ACTIVE){
    					$member[$index]['userId'] = (string)$memberInTeam[$i]["userId"];
    					$member[$index]['name'] = User::getUserName($memberInTeam[$i]["userId"]);
    					if((string)$teamId == (string)$team['teamId']){
    						foreach ($team['user'] as $teamUser){
    							if($memberInTeam[$i]['userId'] == $teamUser){
    								$member[$index]['active'] = false;
    							}
    						}
    					}
    					$index++;
    				}
    			}
    		}
    	}else{
    		for($i = 0; $i < $size;$i++){
    			if($memberInTeam[$i]["activeFlag"] == ActiveFlag::ACTIVE){
    				$member[$index]['userId'] = (string)$memberInTeam[$i]["userId"];
    				$member[$index]['name'] = User::getUserName($memberInTeam[$i]["userId"]);
    				$index++;
    			}
    		}
    	}
    	return $member;
    }
    
    public function getArrayMemberInProject($member){
    	$size = sizeof($member);
    	$arrMemberInProject = [];
    	for($i=0; $i<$size; $i++){
    		$user = [];
    		$user['name'] = User::getUserName($member[$i]);
    		$user['userId'] = (string)$member[$i];
    		$user['active'] = false;
    		$arrMemberInProject[$i] = $user;
    	}
    	return $arrMemberInProject;
    }
    
    public function getTeamMemberProject($projectData){
    	$member = $projectData->member;
    	$size = sizeof($member);
    	
    	$uniqueTeam = $this->getUniqueTeam($member, $size);
    	
    	$sizeOfUniqueTeam = sizeof($uniqueTeam);
    	
    	$memberOfTeam = $this->getMemberOfTeam($uniqueTeam, $sizeOfUniqueTeam, $member, $size);
    	
    	return $memberOfTeam;
    }
    
    public function getMemberOfTeam($uniqueTeam, $sizeOfTeam, $member, $size){
   		$arrMemberOfTeam = [];
    	for($i=0; $i<$sizeOfTeam; $i++){
    		$arrMemberOfTeam[$i]['teamId'] = $uniqueTeam[$i];
    		$arrUser = [];
    		$index = 0;
    		for($j=0; $j<$size; $j++){
    			if($this->haveTeam($uniqueTeam[$i], $member[$j]['team'])){
    				if($member[$j]['activeFlag'] == ActiveFlag::ACTIVE){
    					$arrUser[$index] = (string)$member[$j]['userId'];
    					$index++;
    				}
    			}
    		}
    		$arrMemberOfTeam[$i]['user'] = array_unique($arrUser);
    	}
    	return $arrMemberOfTeam;
    }
    
    public function haveTeam($teamId, $arrTeam){
    	$result = false;
    	$size = sizeof($arrTeam);
    	for($i=0; $i<$size; $i++){
    		if( $teamId == (string)$arrTeam[$i]['teamId']){
    			$result = true;
    			break;
    		}
    	}
    	return $result;
    }
    
    public function getUniqueTeam($member, $size){
    	$index = 0;
    	$arrTeam = [];
    	for ($i=0; $i<$size; $i++){
    		if($member[$i]['activeFlag'] == ActiveFlag::ACTIVE){
    			if($member[$i]['team'] != null){
    				$memberOfTeam = $member[$i]['team'];
    				$sizeOfTeam = sizeof($memberOfTeam);
    				for ($j=0; $j<$sizeOfTeam; $j++){
    					$arrTeam[$index] = (string)$memberOfTeam[$j]['teamId'];
    					$index++;
    				}
    			}
    		}
    	}
    	
    	$index = 0;
    	$arrTeam = array_unique($arrTeam);
    	$arrUniqueTeam = [];
    	foreach ($arrTeam as $teamId){
    		$arrUniqueTeam[] = $teamId;
    	}
    	return array_unique($arrUniqueTeam);
    }
    
    public function getMemberProject($projectData){
    	 $member = $projectData->member;
    	 $size = sizeof($member);
    	 $arrMember = [];
    	 $index = 0;
    	 
    	 for ($i=0; $i<$size; $i++){
    	 	if($member[$i]['team'] == null){
    	 		if($member[$i]['activeFlag'] == ActiveFlag::ACTIVE){
    	 			$arrMember[$index] = $member[$i]['userId'];
    	 			$index++;
    	 		}
    	 	}
    	 }
    	 return $arrMember;
    }
    
    public function getUserNotInProject($memberInProject){
    	$size = sizeof($memberInProject);
    	$query = User::find();
    	for ($i=0; $i<$size; $i++){
    		$query->andWhere(["<>","_id",new ObjectID($memberInProject[$i])]);
    	}
    	$listUser = $query->all();
    	return $listUser;
    }

    /**
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $_id
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Project::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
	public function actionDuplicate()
	{
		if (Yii::$app->request->isAjax) {
		    $data = Yii::$app->request->post();
		    $projectname = explode(":", $data['searchname']);
		    $departmentId = explode(":", $data['departmentId']);
		    $projectId = explode(":", $data['projectId']);
		    
		    $search = Project::findAllProjectByProjectNameAndDepartmentIdWithoutProjectId($projectId[0],trim($projectname[0]), $departmentId[0]);
		    if($search){
		    	$isDuplicate = true;
		    }else{
		    	$isDuplicate = false;
		    }
		    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		    return [
		        'isDuplicate' => $isDuplicate,
		    ];
		}
	}
	
	public function isDuplicateAbbrProjectName($abbrProjectName, $projectId){

		$query = Project::find();
		$query->where(['abbrProjectName'=>$abbrProjectName]);
		$query->andWhere(['status'=>Status::OPEN, 'activeFlag'=>ActiveFlag::ACTIVE]);
			
		if($projectId != null){
			$query->andWhere(['<>', '_id', new ObjectID($projectId)]);
		}
			
		$search = $query->all();
		$isDuplicate = false;
		if($search != null){
			$isDuplicate = true;
		}
		
		return $isDuplicate;
	}
	
	public function actionDuplicateabbrproject()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$arrProjectName = explode(":", $data['searchname']);
			$projectId = explode(":", $data['projectId']);
			
			$arrProjectName = trim($arrProjectName[0]);
			$projectId = $projectId[0];
			
			$search = $this->isDuplicateAbbrProjectName($arrProjectName, $projectId);
			
			if($search){
				$isDuplicate = true;
			}else{
				$isDuplicate = false;
			}
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'isDuplicate' => $isDuplicate,
					'test' => $arrProjectName
			];
		}
	}
	
	public function actionGetprojectname()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$departmentId = explode(":", $data['departmentId']);
			$departmentId = $departmentId[0];
			
			$query = Project::find();
			$query->where(['departmentId'=>$departmentId]);
			$project = $query->all();
							
			$arrProject = [];
			if($project){
				foreach ($project as $obj){
					$arrProject[] = $obj->projectName;
				}
			}
				
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'arrProject' => $arrProject,
			];
		}
	}
	
	public function actionGetproject()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$projectId = explode(":", $data['projectId']);
			$projectId = $projectId[0];
		
			$model = Project::findOne($projectId);
			$currentId = Yii::$app->user->identity->_id;
			
			$conditions = [];
			$query = Comment::find();
			$conditions['refId'] = new ObjectID($projectId);
			$query->where($conditions);
			$query->orderBy(['createTime'=>SORT_ASC]);
			$model2 = $query->all();
			$isDelete = $this->isDelete($model);
			$retData['success'] = false;
			$project = null;
			$comment = null;
			$pathAvartar = null;
			$userId = null;
			$isAllTaskApproved = null;
			$haveTask = null;
			$users = null;
			$commentBy = null;
			$arrPermissionInProject = [];
			$permissionInProject = [];
			$isCancel = true;
			$log = [];
			
			if(!$isDelete){
				if($model->status != Status::CANCEL){
					$isCancel = false;
					//get permission in project
					$arrPermissionInProject[] = PermissionInProject::TASK_PROJECT;
					$arrPermissionInProject[] = PermissionInProject::SETTING_PROJECT;
					$arrPermissionInProject[] = PermissionInProject::EDIT_PROJECT;
					$arrPermissionInProject[] = PermissionInProject::CANCEL_PROJECT;
					$arrPermissionInProject[] = PermissionInProject::DELETE_PROJECT;
					$arrPermissionInProject[] = PermissionInProject::CHANGE_STATUS_PROJECT;
						
					$member = $model->member;
					$sizeOfMember = sizeof($member);
					for ($i=0;$i<$sizeOfMember;$i++){
						if((string)$member[$i]['userId'] == (string)$currentId){
							$permissionInProject = $member[$i]['permission'];
						}
					}
						
					$category = Category::find()->all();
					$arrCategory = [];
					if($category){
						foreach ($category as $obj){
							$arrCategory[(string)$obj->_id] = $obj->categoryName;
						}
					}
						
					$project = $model;
					$comment = $model2;
					$nummberComment = sizeof($comment);
					$project->activeFlag = ($project->activeFlag == 1)?"เปิดการใช้งาน":"ปิดการใช้งาน";
					$project->createBy = User::getUserName($project->createBy);
					$project->createDate = DateTime::MongoDateToDateCreate($project->createDate["sec"]);
					$project->startDate = DateTime::MongoDateToDate($project->startDate["sec"]);
					$project->endDate = DateTime::MongoDateToDate($project->endDate["sec"]);
					$project->departmentId = Department::getDepartmentNameByDepCode($project->departmentId);
					$project->category = ($project->category == null)?"":$arrCategory[(string)$project->category];
					$project->status =	Status::$arrStatus[$project->status];
					$commentBy=[];
					$pathAvartar = [];
					for($i=0;$i<$nummberComment;$i++){
						$commentBy[$i] = (string)$comment[$i]->commentBy;
						
						$pathAvartar[$i] = User::getPhotoUserViewer($commentBy[$i]);
						$comment[$i]->commentBy = User::getUserName($comment[$i]->commentBy);
						$comment[$i]->createTime = DateTime::MongoDateToDateCreate($comment[$i]->createTime["sec"]);
					}
					
					
					$model3 = Project::findOne(["_id" => new ObjectID($projectId)]);
					$projectData = $model3;
					
					$i=0;
					if($projectData['member']){
						foreach ($projectData->member as $userInTeam) {
							if($userInTeam['activeFlag'] == ActiveFlag::ACTIVE){
								$users[$i]['userid'] = $userInTeam['userId'];
								$i = $i + 1;
							}
						}
						$nummberUser = sizeof($users);;
						for($j = 0; $j<$nummberUser; $j++){
							$users[$j]['userid'] = User::getUserName($users[$j]['userid']);
						}
					}
					
					$isAllTaskApproved = $this->isAllTaskApproved($projectId);
					$haveTask = $this->haveTask($projectId);
					
					$dataLog = Log::find();
					$dataLog->where(["refId" => new ObjectID($projectId)]);
					$dataLog->orderBy(['editDate' => SORT_DESC]);
					$log = $dataLog->all();
					$nummberLog = sizeof($log);
					for($i=0;$i<$nummberLog;$i++){
						$userId[$i] = (string)$log[$i]->userId;
						$log[$i]->userId = User::getUserName($log[$i]->userId);
						$log[$i]->editDate = DateTime::MongoDateToDateCreate($log[$i]->editDate["sec"]);
					}
				}
			}
		
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'project' => $project,
					'comment' => $comment,
					'userId' => $userId,
					'commentBy' => $commentBy,
					'pathAvartar' => $pathAvartar,
					'code' => 100,
					'isDelete' => $isDelete,
					'isAllTaskApproved' => $isAllTaskApproved,
					'haveTask' => $haveTask,
					'users' => $users,
					'log' => $log,
					'arrPermissionInProject' => $arrPermissionInProject,
					'permissionInProject' => $permissionInProject,
					'isCancel' => $isCancel
			];
		}
	}
	
	
	public function actionDelete(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$projectId = $request->post('projectId', null);
		$currentId = Yii::$app->user->identity->_id;
		$logs = new Log();
		$model = Project::findOne($projectId);
		$templog = [];
		
		$isDelete = $this->isDelete($model);
		$retData['isDelete'] = $isDelete;
		$retData['success'] = false;
		if(!$isDelete){
			$templog['_id'] = $model->_id;
			$templog['projectName'] = $model->projectName;
			$templog['startDate'] = $model->startDate;
			$templog['endDate'] = $model->endDate;
			$templog['description'] = $model->description;
			$templog['status'] = $model->status;
			$templog['category'] = $model->category;
			$templog['departmentId'] = $model->departmentId;
			$templog['activeFlag'] = $model->activeFlag;
			$templog['member'] = $model->member;
			$templog['createBy'] = $model->createBy;
			$templog['createDate'] = $model->createDate;
			$templog['isCreatedTeam'] = $model->isCreatedTeam;
			//check permission in project
			$permission = PermissionInProject::DELETE_PROJECT;
			$havePermission = PermissionInProject::havePermissionInProject($permission, $model->member);
			if(!$havePermission){
				PermissionInProject::haveNotPermission();
			}
			
			$haveTask = $this->haveTask($projectId);
			$isCancel = $this->isCancel($model);
			$retData['isCancel'] = $isCancel;
			$isClose = $this->isClose($model);
			$retData['isClose'] = $isClose;
			if(!$isCancel && !$isClose && $model != null){
				if($haveTask){
					$model->activeFlag = ActiveFlag::INACTIVE;
					if($model->save()){
						$this->ChangeActiveFlagTask($projectId, ActiveFlag::INACTIVE);
						$logs->oldData = '';
						$logs->newData = '';
						$logs->userId = $currentId;
						$logs->refId = new ObjectID($projectId);
						$logs->editDate = new MongoDate();
						$logs->action = 'ปิดใช้งานโครงการ';
						$logs->memberId = '';
						if ($logs->save()){
							$retData['success'] = true;
						}
					}
				}else{
					if($model->delete()){
						$this->deleteTaskInProject($projectId);
						$logs->oldData = $templog;
						$logs->newData = '';
						$logs->userId = $currentId;
						$logs->refId = new ObjectID($projectId);
						$logs->editDate = new MongoDate();
						$logs->action = 'ลบโครงการออกจากฐานข้อมูล';
						$logs->memberId = '';
						if ($logs->save()){
							$retData['success'] = true;
						}
					}
				}
			}
		}
		
		echo json_encode($retData);
	}
	
	public function haveTask($projectId){
		$model = Task::findOne(["projectId" => new ObjectID($projectId)]);
		if($model != null){
			return true;
		}else{
			return false;
		}
	}
	
	public function actionCancel(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$projectId = $request->post('projectId', null);
		$currentId = Yii::$app->user->identity->_id;
		$model = Project::findOne($projectId);
		
		$logs = new Log();
		$templog = [];
		$isDeleted = $this->isDelete($model);
		$retData['success'] = false;
		$retData['isDelete'] = $isDeleted;
		
		if(!$isDeleted){
			//check permission in project
			$permission = PermissionInProject::CANCEL_PROJECT;
			$havePermission = PermissionInProject::havePermissionInProject($permission, $model->member);
			if(!$havePermission){
				PermissionInProject::haveNotPermission();
			}
			
			$oldData = Project::findOne($projectId);
			$templog['_id'] = $oldData->_id;
			$templog['activeFlag'] = $oldData->activeFlag;
			$templog['category'] = $oldData->category;
			$templog['createBy'] = $oldData->createBy;
			$templog['createDate'] = $oldData->createDate;
			$templog['departmentId'] = $oldData->departmentId;
			$templog['description'] = $oldData->description;
			$templog['endDate'] = $oldData->endDate;
			$templog['isCreatedTeam'] = $oldData->isCreatedTeam;
			$templog['member'] = $oldData->member;
			$templog['projectName'] = $oldData->projectName;
			$templog['startDate'] = $oldData->startDate;
			$templog['status'] = $oldData->status;
			
			$isCancel = $this->isCancel($model);
			$retData['isCancel'] = $isCancel;
			$isClose = $this->isClose($model);
			$retData['isClose'] = $isClose;
			if(!$isCancel && !$isClose && $model != null){
				$model->status = Status::CANCEL;
				if($model->save()){
					$this->ChangeActiveFlagTask($projectId, ActiveFlag::INACTIVE);
					$logs->oldData = $templog;
					$logs->newData = '';
					$logs->userId = $currentId;
					$logs->refId = new ObjectID($projectId);
					$logs->editDate = new MongoDate();
					$logs->action = 'ยกเลิกโครงการ';
					$logs->memberId = '';
					if ($logs->save()){
						$retData['success'] = true;
					}
				}
			}
		}
		
		echo json_encode($retData);
	}
	
	public function ChangeActiveFlagTask($projectId, $activeFlag){
		$listTask = Task::findAll(["projectId"=>new ObjectID($projectId)]);
		foreach ($listTask as $model){
			$modelTask = Task::findOne($model->_id);
			$modelTask->activeFlag = $activeFlag;
			$modelTask->save();
		}
	}
	
	public function actionSetting(){
		$request = Yii::$app->request;
		$projectId = $request->post('projectId',null);
		$name = trim($request->post('name', null));
		$userId = Yii::$app->user->identity->_id;
		$type = $request->post('type',null);
		$baseUrl = \Yii::getAlias ( '@web' );
		
		$model = Project::findOne($projectId);
		
		if($projectId == null){
			return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
		}else{
			if($model == null){
				Yii::$app->getSession()->setFlash('alert',[
						'body'=>'โครงการนี้ถูกลบแล้วโดยผู้ใช้ท่านอื่นแล้ว',
						'options'=>['class'=>'alert-danger']
				]);
				return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
			}else{
				if($model->status == Status::CANCEL){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกยกเลิกแล้วโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
				}else if($model->status == Status::CLOSE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดแล้วโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-warning']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
				}else if($model->activeFlag == ActiveFlag::INACTIVE){
					Yii::$app->getSession()->setFlash('alert',[
							'body'=>'โครงการนี้ถูกปิดใช้งานแล้วโดยผู้ใช้ท่านอื่นแล้ว',
							'options'=>['class'=>'alert-danger']
					]);
					return Yii::$app->getResponse()->redirect($baseUrl.'/project/index');
				}
			}
		}
		
		//check permission in project
		$permission = PermissionInProject::SETTING_PROJECT;
		$havePermission = PermissionInProject::havePermissionInProject($permission, $model->member);
		if(!$havePermission){
			PermissionInProject::haveNotPermission();
		}
		
		$team = Team::find()->all();
		$arrTeam = [];
		foreach ($team as $obj1) {
			$arrTeam[(string)$obj1->_id] = $obj1->teamName;
		}
		$value = Project::findOne($projectId);
		
		//permission of member in project
		$arrMemberInProject = $value->member;
		$sizeArrMemberInProject = sizeof($arrMemberInProject);
		$arrPermissionOfMember = [];
		for ($i=0;$i<$sizeArrMemberInProject; $i++){
			$memberId = (string)$arrMemberInProject[$i]['userId'];
			$permission = $arrMemberInProject[$i]['permission'];
			$arrPermissionOfMember[(string)$memberId] = $permission;
		}
		
		$roleType = AuthItem::find();
		$employeetype = $roleType->where(["type" => 2])->all();
		$arrRole = [];
		if ($employeetype){
			foreach ($employeetype as $obj2){
				$arrRole[(string)$obj2->_id] = $obj2->type;
			}
		}
		return $this->render('setting', [
				"name" => $name,
				"value" => $value,
				"type" => $type,
				"arrTeam" => $arrTeam,
				"arrRole" => $arrRole,
				"arrPermissionOfMember" => json_encode($arrPermissionOfMember)
		]);
	}
	
	public function actionDeleteemployeeinproject(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
		$projectId = $request->post('projectId', null);
		$userId = $request->post('userId', null);
		$model = Project::findOne($projectId);
		$isDelete = $this->isDelete($model);
		$retData['isDelete'] = $isDelete;
		$retData['success'] = false;
		$logs = new Log();
		if (!$isDelete){
			$isCancel = $this->isCancel($model);
			$retData['isCancel'] = $isCancel;
			if (!$isCancel){
				$isClose = $this->isClose($model);
				$retData['isClose'] = $isClose;
				if (!$isClose){
					$model = Project::findOne(["_id"=>new ObjectID($projectId), "member.userId"=>new ObjectID($userId)]);
					$member = [];
					$arrMember = $model->member;
					$size = sizeof($model->member);
					$progressTask = $this->haveProgresstask($userId,$projectId);
					$retData['progress'] = $progressTask;
					if ($progressTask){
						$retData['progress'] = true;
					}else {
						for($i=0;$i<$size;$i++){
							$member[$i]['userId'] = $arrMember[$i]['userId'];
							$member[$i]['team'] = $arrMember[$i]['team'];
							$member[$i]['type'] = $arrMember[$i]['type'];
							if((string)$userId == (string)$arrMember[$i]['userId']){
								$member[$i]['activeFlag'] = ActiveFlag::INACTIVE;
							}else{
								$member[$i]['activeFlag'] = $arrMember[$i]['activeFlag'];
							}
							$member[$i]['permission'] = $arrMember[$i]['permission'];
						}
						$model->member = $member;
						if($model->save()){
							//$message = true;
							$logs->oldData = '';
							$logs->newData = '';
							$logs->userId = $currentId;
							$logs->refId = new ObjectID($projectId);
							$logs->editDate = new MongoDate();
							$logs->action = 'ลบสมาชิกในโครงการ';
							$logs->memberId = new ObjectID($userId);
							if ($logs->save()){
								$retData['success'] = true;
							}
							
						}else{
							//$message = false;
							$retData['success'] = false;
						}
					}
					
				}
			}
		}
		
		echo json_encode($retData);
	}
		
		
	public function actionChangestatus(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
		$projectId = $request->post('projectId', null);
		$status = $request->post('status', null);
		$logs = new Log();
		$templog = [];
		$model = Project::findOne($projectId);
		
		$isDelete = $this->isDelete($model);
		$retData['isDelete'] = $isDelete;
		$retData['success'] = false;
		if(!$isDelete){
			$templog['_id'] = $model->_id;
			$templog['activeFlag'] = $model->activeFlag;
			$templog['category'] = $model->category;
			$templog['createBy'] = $model->createBy;
			$templog['createDate'] = $model->createDate;
			$templog['departmentId'] = $model->departmentId;
			$templog['description'] = $model->description;
			$templog['endDate'] = $model->endDate;
			$templog['isCreatedTeam'] = $model->isCreatedTeam;
			$templog['member'] = $model->member;
			$templog['projectName'] = $model->projectName;
			$templog['startDate'] = $model->startDate;
			$templog['status'] = $model->status;
			//check permission in project
			$permission = PermissionInProject::CHANGE_STATUS_PROJECT;
			$havePermission = PermissionInProject::havePermissionInProject($permission, $model->member);
			if(!$havePermission){
				PermissionInProject::haveNotPermission();
			}
			
			$isCancel = $this->isCancel($model);
			$retData['isCancel'] = $isCancel;
			if(!$isCancel){
				$isAllTaskApproved = $this->isAllTaskApproved($projectId);
				$retData['isAllTaskApprove'] = false;
				if($isAllTaskApproved && $this->amountOfTaskInProject($projectId) > 0){
					$retData['isAllTaskApprove'] = true;
					$model->status = ((int)$status==Status::OPEN)?Status::CLOSE:Status::OPEN;
					if($model->save()){
					if($model->status == Status::OPEN){
							$logs->oldData = $templog;
							$logs->newData = '';
							$logs->userId = $currentId;
							$logs->refId = new ObjectID($projectId);
							$logs->editDate = new MongoDate();
							$logs->action = 'เปิดโครงการ';
							$logs->memberId = '';
							if ($logs->save()){
								$retData['success'] = true;
							}
						}else {
							$logs->oldData = $templog;
							$logs->newData = '';
							$logs->userId = $currentId;
							$logs->refId = new ObjectID($projectId);
							$logs->editDate = new MongoDate();
							$logs->action = 'ปิดโครงการ';
							$logs->memberId = '';
							if ($logs->save()){
								$retData['success'] = true;
							}
						}
					}
				}
			}
		}
		echo json_encode($retData);
	}
	
	public function amountOfTaskInProject($projectId){
		$query = Task::find();
		$query->where(["projectId" => new ObjectID($projectId)]);
		$amountOfTask = $query->count();
		
		return $amountOfTask;
	}
	
	public function isAllTaskApproved($projectId){
		$query = Task::find();
		$query->where(["projectId" => new ObjectID($projectId)]);
		$query->andWhere(["<>","status",Status::APPROVED_TASK]);
		$listTask = $query->all();
		if($listTask == null){
			return true;
		}else{
			return false;
		}
	}
	public function haveProgresstask($userId,$projectId){
		$query = Task::find();
		$query->where(["projectId" => new ObjectID($projectId),"assignee.userId" => new ObjectID($userId)]);
		$query->andWhere(["<>","status",5]);
		$progressTask = $query->all();
		if($progressTask == null){
			return false;
		}else{
			return true;

		}
	}
	
	public function isDelete($model){
		if($model == null){
			return true;
		}else{
			if($model->activeFlag == ActiveFlag::INACTIVE){
				return true;
			}else{
				return false;
			}
		}
	}
	
	public function isCancel($model){
		if($model->status == Status::CANCEL){
			return true;
		}else{
			return false;
		}
	}

	
	public function isClose($model){
		if($model->status == Status::CLOSE){
			return true;
		}else{
			return false;
		}
	}
	public function actionChangeemployeetype(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
		$projectId = $request->post('projectId', null);
		$userId = $request->post('userId', null);
		$type = $request->post('type', null);
		$logs = new Log();
		$model = Project::findOne($projectId);
		$isDelete = $this->isDelete($model);
		$retData['isDelete'] = $isDelete;
		$retData['success'] = false;

		if (!$isDelete){
			
			//old data in member of project
			$arrMemberInProject = $model->member;
			$sizeArrMemberInProject = sizeof($arrMemberInProject);
			$arrUserMember = [];
			for ($i=0;$i<$sizeArrMemberInProject; $i++){
				$userMemberId = $arrMemberInProject[$i]['userId'];
				$activeFlag = $arrMemberInProject[$i]['activeFlag'];
				$typeMemberInProject = $arrMemberInProject[$i]['type'];
				$permission = $arrMemberInProject[$i]['permission'];
				$arrUserMember[(string)$userMemberId]['userId'] = $userMemberId;
				$arrUserMember[(string)$userMemberId]['activeFlag'] = $activeFlag;
				$arrUserMember[(string)$userMemberId]['type'] = $typeMemberInProject;
				$arrUserMember[(string)$userMemberId]['permission'] = $permission;
			}
			
			$isCancel = $this->isCancel($model);
			$retData['isCancel'] = $isCancel;
			if (!$isCancel){
				$isClose = $this->isClose($model);
				$retData['isClose'] = $isClose;
				if (!$isClose){
// 					$model = Project::findOne(["_id"=>new ObjectID($projectId), "member.userId"=>new ObjectID($userId)]);
					$member = [];
					$arrMember = $model->member;
					$size = sizeof($arrMember);
					$progressTask = $this->haveProgresstask($userId,$projectId);
					$retData['progress'] = $progressTask;
					if ($progressTask){
						$retData['progress'] = true;
					}else {
						for($i=0;$i<$size;$i++){
							$member[$i]['userId'] = $arrMember[$i]['userId'];
							$member[$i]['team'] = $arrMember[$i]['team'];
							if((string)$userId == (string)$arrMember[$i]['userId']){
								$member[$i]['type'] = (int)$type;
								$member[$i]['permission'] = PermissionInProject::$arrPermission[(int)$type];
							}else{
								$member[$i]['type'] = (int)$arrMember[$i]['type'];
								$member[$i]['permission'] = $arrUserMember[(string)$arrMember[$i]['userId']]['permission'];
							}
							$member[$i]['activeFlag'] = $arrMember[$i]['activeFlag'];
						}
						$model->member = $member;
						if($model->save()){
							$logs->oldData = '';
							$logs->newData = '';
							$logs->userId = $currentId;
							$logs->refId = new ObjectID($projectId);
							$logs->editDate = new MongoDate();
							$logs->action = 'เปลี่ยนประเภทพนักงาน';
							$logs->memberId = new ObjectID($userId);
							if ($logs->save()){
								$message = true;
								$retData['success'] = true;
							}
							
						}else{
							$message = false;
							$retData['success'] = false;
						}
					}
					
				}
			}
		}
		
		echo json_encode($retData);
	}
	
	public function deleteTaskInProject($projectId){
		if(!empty($projectId)){
			$model = new Task();
			$model->deleteAll(["projectId"=>new ObjectID($projectId)]);
		}
	}

	public function isCanCreated($userId,$amountofProject){
		$countAmountProject = Project::findNumberOfProjectByUserId($userId);
		$CountProject = (int)$countAmountProject;
		if ($amountofProject > $CountProject){
			return  true;
		}else {
			return  false;
		}
	}
	
	public function actionChangepermission(){
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$response->format = \yii\web\Response::FORMAT_JSON;
		$projectId = $request->post('projectId', null);
		$userId = $request->post('userId', null);
		$permission = $request->post('permission', null);
		
		$retData['success'] = true;
		$retData['permission'] = $permission;
		$model = Project::findOne($projectId);
		$isDelete = $this->isDelete($model);
		$retData['isDelete'] = $isDelete;
		$retData['success'] = false;
		$retData['data'] = $permission;
		$retData['isClose'] = false;
		if(!$isDelete){
			$isCancel = $this->isCancel($model);
			$retData['isCancel'] = $isCancel;
			if(!$isCancel){
				if($model->status == Status::CLOSE){
					$retData['isClose'] = true;
				}else{
					$arrMember = $model->member;
					$size = sizeof($arrMember);
					
					for($i=0;$i<$size;$i++){
						$member[$i]['userId'] = $arrMember[$i]['userId'];
						$member[$i]['team'] = $arrMember[$i]['team'];
						$member[$i]['type'] = (int)$arrMember[$i]['type'];
						if((string)$member[$i]['userId'] == (string)$userId){
							$member[$i]['permission'] = json_decode($permission);
						}else{
							$member[$i]['permission'] = $arrMember[$i]['permission'];
						}
						$member[$i]['activeFlag'] = $arrMember[$i]['activeFlag'];
					}
					$model->member = $member;
					if($model->save()){
						$retData['success'] = true;
					}
				}
			}
		}
		echo json_encode($retData);

	}
	
	public function actionUploadimages()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		$currentId = Yii::$app->user->identity->_id;
		
		$projectId = $request->post('projectUploadImages', null);
	
		if (Yii::$app->request->isPost) {
			$model = new Comment();
			$model->images = UploadedFile::getInstances($model, 'images');
			$result = $model->upload($projectId);
			if ($result != false) {
				// file is uploaded successfully
				return json_encode($result);
			}else{
				return;
			}
		}
	}
	
	public function actionUploadfiles()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
		// 		$response->format = \yii\web\Response::FORMAT_JSON;
		$currentId = Yii::$app->user->identity->_id;
	
		$projectId = $request->post('projectUploadFiles', null);
	
		if (Yii::$app->request->isPost) {
			$model = new Comment();
			$model->allfiles = UploadedFile::getInstances($model, 'allfiles');
			$result = $model->uploadfiles($projectId);
			if ($result != false) {
				// file is uploaded successfully
				return json_encode($result) ;
			}else{
				return;
			}
		}
	}
	
	public function actionDownload()
	{
		$request = \Yii::$app->request;
		$response = Yii::$app->response;
			
		$file = $request->get('file', null);
		$path = Comment::getUploadPath();
	
		Yii::$app->response->sendFile($path.$file);
	}
	
	public function actionGetcomment()
	{
		if (Yii::$app->request->isAjax) {
			$data = Yii::$app->request->post();
			$projectId = explode(":", $data['projectId']);
			$projectId = $projectId[0];
	
			$model = Project::findOne($projectId);
			$currentId = Yii::$app->user->identity->_id;
				
			$conditions = [];
			$query = Comment::find();
			$conditions['refId'] = new ObjectID($projectId);
			$query->where($conditions);
			$query->orderBy(['createTime'=>SORT_ASC]);
			$comment = $query->all();
			
			$isDelete = $this->isDelete($model);
			$retData['success'] = false;
			$pathAvartar = null;
			$commentBy = null;
			$isCancel = true;
				
			if(!$isDelete){
				if($model->status != Status::CANCEL){
					$isCancel = false;
	
					$nummberComment = sizeof($comment);
					$commentBy=[];
					$pathAvartar = [];
					for($i=0;$i<$nummberComment;$i++){
						$commentBy[$i] = (string)$comment[$i]->commentBy;
	
						$pathAvartar[$i] = User::getPhotoUserViewer($commentBy[$i]);
						$comment[$i]->commentBy = User::getUserName($comment[$i]->commentBy);
						$comment[$i]->createTime = DateTime::MongoDateToDateCreate($comment[$i]->createTime["sec"]);
					}
				}
			}
	
			\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
			return [
					'comment' => $comment,
					'commentBy' => $commentBy,
					'pathAvartar' => $pathAvartar,
					'code' => 100,
					'isDelete' => $isDelete,
					'isCancel' => $isCancel
			];
		}
	}
}

