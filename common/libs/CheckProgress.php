<?php
namespace common\libs;
use MongoDB\BSON\ObjectID;
use yii\base\ErrorException;
use yii\mongodb\Exception;
use common\libs\RoleInProject;
use backend\models\Project;
use Yii;
use \MongoDate;

class CheckProgress{

	public function  checkProgress($startdate, $enddate, $progress){
		$result = false;
		$date= 0;
		$now = new \MongoDate();
		$datestart = date_create(date('Y/m/d',  strtotime('+6 Hour',$startdate["sec"])));
		$dateend = date_create(date('Y/m/d',  strtotime('+6 Hour',$enddate["sec"])));
		$datenow = date_create(date('Y/m/d ',  strtotime('+6 Hour',$now->sec)));
		if($datenow >= $dateend){
			return "progress-bar progress-bar-red";
		}
		elseif($datestart <= $datenow){
			$totaldate = date_diff($datestart,$dateend);
			$datepast = date_diff($datestart,$datenow);
			$totaldate = (int)$totaldate->days;
			$datepast = (int)$datepast->days;;
			$date = ($datepast/$totaldate)*100;
			if($date < ( int ) $progress){
				return "progress-bar progress-bar-green";
			}elseif($date / 2 <= ( int ) $progress){
				return "progress-bar progress-bar-yellow";
			}else{
				return "progress-bar progress-bar-red";
			}
		}
		else{
			return "progress-bar progress-bar-green";
		}
	}
}
?>