<?php
namespace common\libs;

use yii\web\HttpException;
use Yii;

class PermissionInProject{
	//project
    const EDIT_PROJECT = "pm.project.edit";
    const DELETE_PROJECT = "pm.project.delete";
    const CANCEL_PROJECT = "pm.project.cancel";
    const SETTING_PROJECT = "pm.project.setting";
    const CHANGE_STATUS_PROJECT = "pm.project.changestatus";
    const EDIT_PERMISSION_PROJECT = "pm.project.editpermission";
    
    //task
    const TASK_PROJECT = "pm.project.task";
    const CREATE_TASK_PROJECT = "pm.project.createtask";
    
    public static $arrPermissionProject = array(
    		self::EDIT_PROJECT => "แก้ไขโครงการ",
    		self::DELETE_PROJECT => "ลบโครงการ",
    		self::CANCEL_PROJECT => "ยกเลิกโครงการ",
    		self::SETTING_PROJECT => "ตั้งค่าโครงการ",
    		self::CHANGE_STATUS_PROJECT => "เปิด/ปิดโครงการ",
    
    		self::TASK_PROJECT => "เข้าสู่งานในโครงการ",
    		self::CREATE_TASK_PROJECT => "สร้างงานในโครงการ",
    );
    
    public static $arrPermission = array(
    	array(),
    	array(
    		self::EDIT_PROJECT,
	    	self::DELETE_PROJECT,
	    	self::CANCEL_PROJECT,
	    	self::SETTING_PROJECT,
	    	self::CHANGE_STATUS_PROJECT,
	    	self::EDIT_PERMISSION_PROJECT,
	    		
	    	self::TASK_PROJECT,
	    	self::CREATE_TASK_PROJECT
    	),
    	array(
    		self::TASK_PROJECT,
    		self::CREATE_TASK_PROJECT
    	),
    	array(
    		self::TASK_PROJECT
    	)	
    );
    
    public static $arrPermissionProjectManagement = array(
    	self::EDIT_PROJECT,
    	self::DELETE_PROJECT,
    	self::CANCEL_PROJECT,
    	self::SETTING_PROJECT,
    	self::CHANGE_STATUS_PROJECT,
    	self::EDIT_PERMISSION_PROJECT,
    		
    	self::TASK_PROJECT,
    	self::CREATE_TASK_PROJECT,
    );
    
    public static $arrPermissionDeveloper = array(
    		self::TASK_PROJECT,
    		self::CREATE_TASK_PROJECT,
    );
    
    public static $arrPermissionReporter = array(
    		self::TASK_PROJECT,
    );
   
    public static $arrModuleInProject = array(
    		self::EDIT_PROJECT => "โครงการ",
    		self::DELETE_PROJECT => "โครงการ",
    		self::CANCEL_PROJECT => "โครงการ",
    		self::SETTING_PROJECT => "โครงการ",
    		self::CHANGE_STATUS_PROJECT => "โครงการ",
    		self::EDIT_PERMISSION_PROJECT => "โครงการ",
    
    		self::TASK_PROJECT => "งาน",
    		self::CREATE_TASK_PROJECT => "งาน",
    );
    
    public function havePermissionInProject($permission, $member){
    	$userId = Yii::$app->user->identity->_id;
    	$size = sizeof($member);
    	$arrPermission = [];
    	for ($i=0;$i<$size;$i++){
    		if((string)$member[$i]['userId'] == (string)$userId){
    			$arrPermission = $member[$i]['permission'];
    			break;
    		}
    	}
    	$havePermission = in_array($permission,$arrPermission);
    	return $havePermission;
    }
    
    public function haveNotPermission(){
    	Throw new HttpException(403);
    }
    
}

?>