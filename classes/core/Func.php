<?php
class Func{

	public static function compareByObjectName($a, $b){
		return strcmp($a->getName(), $b->getName());
	}

	public static function logQueryAndValues($query, $values=array(), $callingFunction=false){
//		logDebug('logQueryAndValues: '.var_export($values, true));
		if($values){
			foreach($values as $key=>$value){
				if(substr($key, 0, 1) !== ':'){ $key = ':'.$key; }
				$query = str_replace($key, "'".$value."'", $query);
			}
		}
		logDebug(($callingFunction ? $callingFunction.': ' : '').$query);
	}
	
	public static function makeDisplayDate($inputdatestr){
		$inputdate = new DateTime($inputdatestr);
		if(in_array($inputdate->format('j'), array('1', '28', '29', '30', '31'))){
			return $inputdate->format('M Y');
		}elseif($inputdate->format('d') > 14){
			return 'late '.$inputdate->format('M Y');
		}else{
			return $inputdate->format('M j, Y');
		}
	}

	public static function trimFloat($floatval){
		while(strpos($floatval, '.') && substr($floatval, -1) === '0'){
			$floatval = rtrim($floatval, '0');
		}
		$floatval = rtrim($floatval, '.');
		return $floatval;
	}

}