<?php
namespace common\libs;
class RoleInProject{
	
	const PROJECT_MANAGER = 1;
    const TEAM_DEVELOPER = 2;
    const REPORTER = 3;
    
    public static $arrRole = array(
    		self::PROJECT_MANAGER => "ผู้จัดการโครงการ",
    		self::TEAM_DEVELOPER => "ทีมพัฒนา",
    		self::REPORTER => "ผู้เยี่ยมชมโครงการ"
    );	
}
?>