<?php
namespace common\libs;
class Status{
	
	const OPEN = 1;
    const CLOSE = 2;
    const CANCEL = 3;
    
    public static $arrStatus = array(
    	self::OPEN => "เปิด",
   		self::CLOSE => "ปิด",
   		self::CANCEL => "ยกเลิก",
    );
	
    const PREPARE_TASK = 1;
    const NEW_TASK = 2;
    const OPEN_TASK = 3;
    const DOING_TASK = 4;
    const WAIT_APPROVE_TASK = 5;
	const APPROVED_TASK = 6;
	const REJECTED_TASK = 7;
	const COMPLETED_TASK = 8;

	public static $arrTaskStatus = array(
		self::PREPARE_TASK => "Prepare",
		self::NEW_TASK => "New Task",
		self::OPEN_TASK => "Open Task",
		self::DOING_TASK => "Doing",
		self::WAIT_APPROVE_TASK => "Waiting for Approve",
		self::APPROVED_TASK => "Approved",
		self::REJECTED_TASK => "Rejected",
		self::COMPLETED_TASK => "Complete",
	);
	public static $arrTaskStatusSearchProject = array(
			self::PREPARE_TASK => "Prepare",
			self::NEW_TASK => "New Task",
			self::OPEN_TASK => "Open Task",
			self::DOING_TASK => "Doing",
			self::WAIT_APPROVE_TASK => "Waiting for Approve",
			self::APPROVED_TASK => "Approved",
			self::REJECTED_TASK => "Rejected",
			
	);
	
}
?>