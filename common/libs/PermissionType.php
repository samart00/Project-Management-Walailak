<?php
namespace common\libs;
class PermissionType{
	const CHILD_PERMISSION_TYPE = 0;
	const ROLE_TYPE = 1;
	const PERMISSION_TYPE = 2;
	const PROJECT_PERMISSION_TYPE = 3;
	
	public static $arrPermissionType = array(
			self::CHILD_PERMISSION_TYPE => "สิทธิ์ย่อย",
			self::ROLE_TYPE => "ประเภทพนักงาน",
			self::PERMISSION_TYPE => "ประเภทสิทธิ์",
			self::PROJECT_PERMISSION_TYPE => "สิทธิ์ภายในโครงการ",
	);
}
