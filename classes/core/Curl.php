<?php
class Curl{

	/* TODO: right up a blog on how to connect to the stupid comicvine api over php/curl, and make sure to include info on using USERAGENT */

	public static function getComivineIssueUrl($comicvine_id){
		//https://comicvine.gamespot.com/api/issue/4000-6686/?api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json
		$url = self::$baseUrl."issue/{$comicvine_id}/?api_key=".self::$apikey."&format=json";
		logDebug('getComivineIssueUrl: '.$url);
		return $url;
	}

	public static function getComivineSeriesUrl($comicvine_id){
		//https://comicvine.gamespot.com/api/volume/4050-2290/?api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json
		$url = self::$baseUrl."volume/{$comicvine_id}/?api_key=".self::$apikey."&format=json";
		logDebug('getComivineSeriesUrl: '.$url);
		return $url;
	}

	public function getIssueByComicvineId($comicvine_issue_id){
		//https://comicvine.gamespot.com/api/issue/4000-6686/?api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json
		return $this->getResults('issue', $comicvine_issue_id);
	}

	public function getIssuesBySeriesAndIssue($comicvine_series_id, $issue_number){
		//https://comicvine.gamespot.com/api/issues/?filter=volume:2128,issue_number:1&api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json
		//massage the issue number for a couple corner cases
		if($issue_number === '1/2'){ $issue_number = "½"; }
		if($issue_number === 'infinity'){ $issue_number = "∞"; }
		return $this->getResults('issues', array(), 'filter=volume:'.intval($comicvine_series_id).urlencode(',issue_number:'.$issue_number));
	}

	public function getSeriesByName($name){
		$filters['name'] = urlencode(preg_replace("/[".CHARS_TO_REMOVE_FOR_SEARCH."]/", '', $name));
		logDebug('call curl: '.var_export($filters, true));
		$decodedResponse = $this->getResults('volumes', $filters);
		return $decodedResponse;
	}

	public function getSeriesByNameAndIssue($name, $issue){
		$filters['name'] = urlencode(preg_replace("/[".CHARS_TO_REMOVE_FOR_SEARCH."]/", '', $name));
		$filters['issue'] = urlencode(preg_replace("/[".CHARS_TO_REMOVE_FOR_SEARCH."]/", '', $issue));
//		$filters['start_year'] = urlencode(preg_replace("/[".CHARS_TO_REMOVE_FOR_SEARCH."]/", '', $year));
		logDebug('call curl: '.var_export($filters, true));
		$decodedResponse = $this->getResults('volumes', $filters);
		return $decodedResponse;
	}

	public function getResults($resouce, $param, $suffix=false){
		$page_results = $total_results = $offset = $sanity_check = 0;
		$finalResults = array();
		do{
			logDebug('loop: '.$sanity_check);
			$response = $this->post($resouce, $param, $suffix.($suffix?'&':'').'offset='.$offset);
			$decodedResponse = json_decode($response, true);
			$tempResults = $decodedResponse['results'];
//			logDebug('decoded'. var_export($decodedResponse, true));
			logDebug('size of tempResults: '.count($tempResults));
			$results_offset = $decodedResponse['offset'];
			$page_results = $decodedResponse['number_of_page_results'];
			$total_results = $decodedResponse['number_of_total_results'];
			logDebug("results_offset[{$results_offset}], page_results[{$page_results}], total_results[{$total_results}]");
			$finalResults = array_merge($finalResults, $tempResults);
			$offset = ($results_offset + $page_results);
			$sanity_check++;//just in case testing goes awry
			logDebug('size of results so far: '.count($finalResults));
		}while($offset < $total_results && $sanity_check < 13);
		return $finalResults;
	}

	public function post($resouce, $param, $suffix=false){
		//prepare
		$idparam = '';
		if(is_array($param)){ 
			$filters = $param; 
		}else{ 
			$idparam = '/'.$param;
		}
		$apiUrl = self::$baseUrl."{$resouce}{$idparam}/?api_key=".self::$apikey."&format=json".($suffix ? '&'.$suffix : '');
		if(!empty($filters)){
			$apiUrl .=	"&filter=";
			$i = 0;
			foreach($filters as $key=>$value){
				if($i++>0){ $apiUrl .= ","; }
				$apiUrl .= "{$key}:{$value}";
			}
		}
		logDebug('apiurl: '.$apiUrl);

		//initiate
		$this->ch = curl_init();
		$timeout = 5;
		curl_setopt($this->ch, CURLOPT_URL, $apiUrl);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($this->ch, CURLOPT_USERAGENT, 'davpeterson');

		//get content
		$response = curl_exec($this->ch);
		if(empty($response)){
			// some kind of an error happened
			logDebug('ERROR> curl error: '.var_export(curl_error($this->ch), true));
			curl_close($this->ch);
			return '';
		}
		$info = curl_getinfo($this->ch);
//		$time = $info['total_time'] * 1000;
//		logDebug("time took (ms): ".$time);

		//close and release
		curl_close($this->ch);
		if($info['http_code'] != 200 && $info['http_code'] != 201 ){
			logDebug("ERROR> received error: ".var_export($info['http_code'], true));
			logDebug("ERROR> received error, info: ".var_export($info, true));
			logDebug("ERROR> raw response: ".var_export($response, true));
			return '';
		}
		//convert json to php-array
//		$jsonResponse = json_decode($response);
//		logDebug('jsonResponse: '.$jsonResponse);
//		logDebug('returning: '.var_export($response, true));
		return $response;
	}

	protected $ch = false;
	protected static $baseUrl = 'https://comicvine.gamespot.com/api/';
	protected static $apikey = '5881a5da17876142d003f9bcf843d4db4ce9fce2';
}