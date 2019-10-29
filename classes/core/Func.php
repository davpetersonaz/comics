<?php
class Func{

	public static function compareByObjectName($a, $b){
		return strcmp($a->getName(), $b->getName());
	}

	public static function escapeForHtml($input){
		$input = str_replace("'", "\'", $input);
//		$input = str_replace("'", '&#39;', $input);
//		$input = str_replace('"', '&quot;', $input);
//		$input = str_replace("&", '&amp;', $input);
//		$input = str_replace("<", '&lt;', $input);
		return $input;
	}

	/**
	 * transforms an issue number into its 'fancy' form (∞, ½), for comicvine/curl or displaying in html
	 * @param type an issue number
	 * @return string fancy issue number
	 */
	public static function fancifyIssueNumber($issue_number){
		if(intval($issue_number) === 88888 || $issue_number === 'infinity' || $issue_number === "∞"){
			return "∞";
		}elseif(floatval($issue_number) === 0.5 || $issue_number === '1/2' || $issue_number === "½"){
			return "½";
		}else{
			return Func::trimFloat($issue_number);
		}
	}

	/**
	 * transforms an issue number into a db-friendly version (88888 / 0.5).
	 * @param type issue number
	 * @return string db-friendly issue number
	 */
	public static function dbFriendlyIssueNumber($issue_number){
		if($issue_number === "∞" || $issue_number === 'infinity' || $issue_number === 88888){
			return 88888;
		}elseif($issue_number === "½" || $issue_number === "1/2" || $issue_number === 0.5){
			return 0.5;
		}else{
			return Func::trimFloat($issue_number);
		}
	}

	/**
	 * transforms an issue number into its normal/working form (infinity / 1/2)
	 * @param type an issue number
	 * @return string working-form of issue number
	 */
	public static function normalizeIssueNumber($issue_number){
		if(intval($issue_number) === 88888 || $issue_number === "∞" || $issue_number === 'infinity'){
			return 'infinity';
		}elseif(floatval($issue_number) === 0.5 || $issue_number === "½" || $issue_number === '1/2'){
			return '1/2';
		}else{
			return Func::trimFloat($issue_number);
		}
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
		if(in_array($inputdate->format('j'), array('1', '2', '28', '29', '30', '31'))){
			return $inputdate->format('M Y');
		}elseif($inputdate->format('d') > 12){
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