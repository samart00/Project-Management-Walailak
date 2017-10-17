<?php
namespace common\libs;
class TypeTask{

	const PRIVATETASK = 1;
	const PROJECTTASK = 2;
	

	public static $arrTypeTask = array(
			self::PRIVATETASK => "งานส่วนตัว",
			self::PROJECTTASK => "งานในโครงการ",
			
	);

	

}
?>