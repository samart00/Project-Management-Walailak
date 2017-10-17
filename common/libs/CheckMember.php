<?php
namespace common\libs;
use MongoDB\BSON\ObjectID;
use yii\base\ErrorException;
use yii\mongodb\Exception;
use common\libs\RoleInProject;
use backend\models\Project;
use Yii;


class CheckMember{
    
    public function  checkMemberInTask($arrMemberInTask, $userId){
    	$result = false;
    	
    		foreach ($arrMemberInTask as $member){
//     			var_dump($member);
    			if((string)$member['userId'] == (string)$userId){
    				$result = true;
    				break;
    			}
    		}

    	
    	return $result;
    }
    public function  checkProjectManager($projectId){
    	$result = false;
    	$currentUser = Yii::$app->user->identity->_id;
    	$query = Project::find();
    	$query->where(["_id" => $projectId]);
    	$query->andwhere(['member' => ['$elemMatch' => ['userId' => $currentUser,'type' => RoleInProject::PROJECT_MANAGER]]]);
    	$query = $query->all();
    	 if($query == null){
    	 	$result =  false;
    	 }else{
    	 	$result =  true;
    	 }
    	 return $result;
    }
    	
    
}
?>