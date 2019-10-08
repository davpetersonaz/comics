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

}