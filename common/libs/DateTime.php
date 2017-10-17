<?php
namespace common\libs;
use \MongoDate;

class DateTime{
	
	public function MongoDateToDate($date){
		return date('d/m/Y H:i:s',  strtotime('+6 Hour', $date));
	}
	
	public function MongoDateToDateCreate($date){
		return date('d/m/Y H:i:s',  strtotime('+5 Hour', $date));
	}
	
	public function MongoDateToDateReturnDate($date){
		return date('d/m/Y',  strtotime('+6 Hour', $date));
	}
	
	public function MongoDateToDateReturnTime($date){
		return date('H:i',  strtotime('+6 Hour', $date));
	}
	
	public function SecNowDate(){
		return (new MongoDate())->sec;
	}
	
	public function MongoDateToDateNotTime($date){
		return date('m-d-Y',  strtotime('+6 Hour', $date));
	}
}
?>