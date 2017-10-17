<?php
namespace common\libs;

use yii\web\HttpException;
use Yii;

class Permission{
	//category
	const SEARCH_CATEGORY = "pm.category.index";
    const CREATE_CATEGORY = "pm.category.create";
    const EDIT_CATEGORY = "pm.category.edit";
    const VIEW_CATEGORY = "pm.category.view";
    const CHANGE_STATUS_CATEGORY = "pm.category.changeactiveflag";
    const DELETE_CATEGORY = "pm.category.delete";
    
    //role
    const SEARCH_ROLE = "pm.role.index";
    const CREATE_ROLE = "pm.role.create";
    const EDIT_ROLE = "pm.role.edit";
    const VIEW_ROLE = "pm.role.view";
   	const CHANGE_STATUS_ROLE = "pm.role.changeactiveflag";
   	const DELETE_ROLE = "pm.role.delete";
   	const ADD_PERMISSION = "pm.role.addpermission";
   	const REMOVE_PERMISSION = "pm.role.removepermission";
   	const MANAGEMENT_ROLE = "pm.role.management";
   
    //team
    const SEARCH_TEAM = "pm.team.index";
    const CREATE_TEAM = "pm.team.create";
    const EDIT_TEAM = "pm.team.edit";
    const VIEW_TEAM = "pm.team.view";
    const DELETE_TEAM = "pm.team.delete";
    const MEMBER_TEAM = "pm.team.member";
    const ADD_MEMBER_TEAM = "pm.team.addmember";
    const REMOVE_MEMBER_TEAM = "pm.team.removemember";
    
    //policy/amount
    const SEARCH_AMOUNT = "pm.policy.index";
    const SET_ALL_AMOUNT = "pm.policy.setallamount";
    const SET_AMOUNT = "pm.policy.setamount";
    
    //assign
    const SEARCH_ASSIGN = "pm.assign.index";
    const MANAGEMENT_ASSIGN = "pm.assign.management";
    const ADD_MEMBER_ASSIGN = "pm.assign.addmember";
    const REMOVE_MEMBER_ASSIGN = "pm.assign.removemember";
    
    //eventtype
    const SEARCH_EVENTTYPE = "cm.eventtype.index";
    const SAVE_EVENTTYPE = "cm.eventtype.save";
    const VIEW_EVENTTYPE = "cm.eventtype.view";
    const UPDATE_EVENTTYPE= "cm.eventtype.update";
    const DELETE_EVENTTYPE= "cm.eventtype.delete";
    
    //Individual Calendar
    const SEARCH_INDIVIDUAL_CALENDAR = "cm.event.index";
    
    //Department Calendar
    const SEARCH_DEPARTMENT_CALENDAR = "cm.event.devision";
    
    //Project Calendar
    const SEARCH_PROJECT_CALENDAR = "cm.event.project";
    
    //upload_Holiday
    const SEARCH_EVENT_HOLIDAY = "cm.upload.index";
    
    //project
    const SEARCH_PROJECT = "pm.project.index";
    const CREATE_PROJECT = "pm.project.create";
    
    //project-management
    const SEARCH_PROJECT_MANAGEMENT = "pm.projectmanagement.index";
    const CHANGE_STATUS_PROJECT_MANAGEMENT = "pm.projectmanagement.changestatus";
    const DELETE_PROJECT_MANAGEMENT = "pm.projectmanagement.delete";
    const VIEW_PROJECT_MANAGEMENT = "pm.projectmanagement.view";
    
    //team-management
    const SEARCH_TEAM_MANAGEMENT = "pm.teammanagement.index";
    const CHANGE_STATUS_TEAM_MANAGEMENT = "pm.teammanagement.changestatus";
    const DELETE_TEAM_MANAGEMENT = "pm.teammanagement.delete";
    const VIEW_TEAM_MANAGEMENT = "pm.teammanagement.view";
    
    //task
    const SEARCH_TASK = "pm.task.index";
    const CREATE_TASK = "pm.task.create";
    
    //approve
    const SEARCH_APPROVE_TASK = "pm.approve.index";
    
    public static $arrPermission = array(
    		self::SEARCH_CATEGORY => "ค้นหาประเภทโครงการ",
    		self::CREATE_CATEGORY => "สร้างประเภทโครงการ",
    		self::EDIT_CATEGORY => "แก้ไขประเภทโครงการ",
    		self::VIEW_CATEGORY => "รายละเอียดประเภทโครงการ",
    		self::CHANGE_STATUS_CATEGORY => "เปิด/ปิดใช้งานประเภทโครงการ",
    		self::DELETE_CATEGORY => "ลบประเภทโครงการ",
    		
    		self::SEARCH_ROLE => "ค้นหาประเภทพนักงาน",
    		self::CREATE_ROLE => "สร้างประเภทพนักงาน",
    		self::EDIT_ROLE => "แก้ไขประเภทพนักงาน",
    		self::VIEW_ROLE => "รายละเอียดประเภทพนักงาน",
    		self::DELETE_ROLE => "ลบประเภทพนักงาน",
    		self::CHANGE_STATUS_ROLE => "เปิด/ปิดใช้งานประเภทพนักงาน",
    		self::MANAGEMENT_ROLE => "หน้าจัดการบทบาท",
    		
    		self::ADD_PERMISSION => "เพิ่มบทบาท",
    		self::REMOVE_PERMISSION => "ยกเลิกสิทธิ์",
    		
    		self::SEARCH_TEAM => "ค้นหาทีม",
    		self::CREATE_TEAM => "สร้างทีม",
    		self::EDIT_TEAM => "แก้ไขทีม",
    		self::VIEW_TEAM => "ดูรายละเอียดทีม",
    		self::DELETE_TEAM => "ลบทีม",
    		self::MEMBER_TEAM => "ดูสมาชิก",
    		self::ADD_MEMBER_TEAM => "เพิ่มสมาชิก",
    		self::REMOVE_MEMBER_TEAM => "ลบสมาชิก",
    		
    		self::SEARCH_AMOUNT => "กำหนดจำนวนการสร้างโครงการ",
    		self::SET_ALL_AMOUNT => "กำหนดจำนวนการสร้างโครงการทั้งหมด",
    		self::SET_AMOUNT => "กำหนดจำนวนการสร้างโครงการรายบุคคล",
    		
    		self::SEARCH_ASSIGN => "กำหนดบทบาท",
    		self::MANAGEMENT_ASSIGN => "เพิ่ม/ลบสมาชิกในบทบาท",
    		self::ADD_MEMBER_ASSIGN => "เพิ่มสมาชิกในบทบาท",
    		self::REMOVE_MEMBER_ASSIGN => "ลบสมาชิกในบทบาท",
    				
    		self::SEARCH_PROJECT => "ค้นหาโครงการ",
    		self::CREATE_PROJECT => "สร้างโครงการ",
    		
    		self::SEARCH_PROJECT_MANAGEMENT => "ค้นหาการจัดการโครงการ",
    		self::CHANGE_STATUS_PROJECT_MANAGEMENT => "เปลี่ยนสถานะการจัดการโครงการ",
    		self::DELETE_PROJECT_MANAGEMENT => "ลบการจัดการโครงการ",
    		self::VIEW_PROJECT_MANAGEMENT => "ดูรายละเอียดการจัดการโครงการ",
    		
    		self::SEARCH_TEAM_MANAGEMENT => "ค้นหาการจัดการทีม",
    		self::CHANGE_STATUS_TEAM_MANAGEMENT => "เปลี่ยนสถานะการจัดการทีม",
    		self::DELETE_TEAM_MANAGEMENT => "ลบการจัดการทีม",
    		self::VIEW_TEAM_MANAGEMENT => "ดูรายละเอียดทีม",
    		
    		self::SEARCH_TASK => "ค้นหางาน",
    		self::CREATE_TASK => "สร้างงาน",
    		
    		self::SEARCH_APPROVE_TASK => "ค้นหางานที่จะอนุมัติ",
    		
    		self::SEARCH_EVENTTYPE => "ค้นหาประเภทกิจกรรม",
    		self::VIEW_EVENTTYPE => "ดูรายละเอียดประเภทกิจกรรม",
    		self::SAVE_EVENTTYPE => "บันทึกประเภทกิจกรรม",
    		self::UPDATE_EVENTTYPE=> "แก้ไขประเภทกิจกรรม",
    		self::DELETE_EVENTTYPE=> "ลบประเภทกิจกรรม",
    		
    		self::SEARCH_INDIVIDUAL_CALENDAR => "ค้นหาปฏิทินส่วนบุคคล",
    		self::SEARCH_DEPARTMENT_CALENDAR => "ค้นหาปฏิทินแผนก",
    		self::SEARCH_PROJECT_CALENDAR => "ค้นหาปฏิทินโครงการ",
    		self::SEARCH_EVENT_HOLIDAY => "ค้นหาปฏิทินวันหยุด",
    		
    );	
    
    public function havePermission($permission){
    	if (!Yii::$app->user->can($permission)) Throw new HttpException(403);
    }
    
    public static $arrModule = array(
    	"การจัดการบทบาท" => "การจัดการบทบาท",
    	"การกำหนดบทบาท" => "การกำหนดบทบาท",
    	"กำหนดจำนวนโครงการที่สร้างได้" => "กำหนดจำนวนโครงการที่สร้างได้",
    	"กำหนดวันหยุด" => "กำหนดวันหยุด",
    	"กำหนดประเภทกิจกรรม" => "กำหนดประเภทกิจกรรม",
    	"การจัดการประเภทโครงการ" => "การจัดการประเภทโครงการ",
    	"การจัดการทีม" => "การจัดการทีม",
    	"การจัดการโครงการ" => "การจัดการโครงการ",
    	"โครงการ" => "โครงการ",
    	"งาน" => "งาน",
    	"ทีม" => "ทีม",
    	"ปฏิทินแผนก" => "ปฏิทินแผนก",
    	"ปฏิทินโครงการ" => "ปฏิทินโครงการ",
    	"ปฏิทินส่วนบุคคล" => "ปฏิทินส่วนบุคคล",
    	"อนุมัติปิดงาน" => "อนุมัติปิดงาน"
    );
    
    public function arrAllPermission(){
    	$arrPermission = [];
    	foreach (Permission::$arrPermission as $key => $value){
    		$data = [];
    		$data['value'] = $value;
    		$data['data'] = $key;
    		$arrPermission[] = $data;
    	}
    	return $arrPermission;
    }
}

?>