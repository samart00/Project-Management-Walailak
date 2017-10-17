<?php
namespace common\libs;
class ActiveFlag{
	
	const ACTIVE = 1;
    const INACTIVE = 2;
    
    public static $arrActiveFlag = array(
    		self::ACTIVE => "เปิดใช้งาน",
    		self::INACTIVE => "ปิดใช้งาน",
    );	
}
?>