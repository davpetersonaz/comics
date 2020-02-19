<?php
class Curl{

	/* TODO: right up a blog on how to connect to the stupid comicvine api over php/curl, and make sure to include info on using USERAGENT */
	
	public function getAllIssuesInSeries($comicvine_series_id){
		//https://comicvine.gamespot.com/api/issues/?filter=volume:2128&sort=issue_number:asc&api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json
		$results = $this->getResults('issues', array(), 'filter=volume:'.intval($comicvine_series_id).'&sort=issue_number:asc');
		logDebug('getAllIssuesInSeries: '.var_export($results, true));
		$final_results = array();
		foreach($results as $result){
			$final_results[] = $result['issue_number'];
		}
		logDebug('final getAllIssuesInSeries: '.var_export($final_results, true));
		return $final_results;
	}

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
		//massage the issue number for a couple corner cases (like "½" and "∞")
		$issue_number = Func::fancifyIssueNumber($issue_number);
		return $this->getResults('issues', array(), 'filter=volume:'.intval($comicvine_series_id).',issue_number:'.urlencode($issue_number));
	}

	public function getSeriesByName($name){
		$filters['name'] = urlencode(preg_replace("/[".CHARS_TO_REMOVE_FOR_SEARCH."]/", '', $name));
		logDebug('ADD THIS URL AS A COMMENT ... call curl: '.var_export($filters, true));
		$decodedResponse = $this->getResults('volumes', $filters);
		return $decodedResponse;
	}
	
	public function getSeriesByComicvineId($comicvine_issue_id){
		//https://comicvine.gamespot.com/api/volume/4050-2133/?api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json&offset=0
		return $this->getResults('volume', $comicvine_issue_id);
	}

	public function getSeriesByNameAndIssue($name, $issue){
		$filters['name'] = urlencode(preg_replace("/[".CHARS_TO_REMOVE_FOR_SEARCH."]/", '', $name));
		$filters['issue'] = urlencode(preg_replace("/[".CHARS_TO_REMOVE_FOR_SEARCH."]/", '', $issue));
//		$filters['start_year'] = urlencode(preg_replace("/[".CHARS_TO_REMOVE_FOR_SEARCH."]/", '', $year));
		logDebug('ADD THIS URL AS A COMMENT ... call curl: '.var_export($filters, true));
		$decodedResponse = $this->getResults('volumes', $filters);
		return $decodedResponse;
	}

	////////////////////////////////////////////////////////////////////////

	public function getResults($resource, $param, $suffix=false){
		logDebug('getResults resource: '.$resource);
		if($param){ logDebug('param: '.var_export($param, true)); }
		$page_results = $total_results = $offset = $sanity_check = 0;
		$finalResults = array();
		do{
			if($sanity_check){ logDebug('loop: '.$sanity_check); }
			$response = $this->post($resource, $param, $suffix.($suffix?'&':'').'offset='.$offset);
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

	public function post($resource, $param, $suffix=false){
		logDebug('Curl post resource ['.$resource.'], suffix ['.$suffix.']');
		logDebug('param: '.var_export($param, true));
		//prepare
		$idparam = '';
		if(is_array($param)){ 
			$filters = $param; 
		}else{ 
			$idparam = '/'.$param;
		}
		$apiUrl = self::$baseUrl."{$resource}{$idparam}/?api_key=".self::$apikey."&format=json".($suffix ? '&'.$suffix : '');
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

/*

basic syntax:
http://www.comicvine.com/api/<resource>/?filter=field1:value1,field2:value2&sort=cover_date:asc&api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json

series details by comicvine series id:
https://comicvine.gamespot.com/api/volume/4050-2133/?api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json&offset=0

all volumes filtered by title sorted by cover date: 
https://comicvine.gamespot.com/api/volumes/?filter=name:avengers&sort=cover_date:asc&api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json

to find list of series by name:
https://comicvine.gamespot.com/api/volumes/?filter=name:avengers&api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json

details on a single issue (by comicvine issue id)
https://comicvine.gamespot.com/api/issue/4000-6686/?api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json

get a specific issue using comicvine-series and issue-number !!
https://comicvine.gamespot.com/api/issues/?filter=volume:2128,issue_number:1&api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json

get all issues in a series-id (all issues in avengers vol.1) sorted by issue number:
https://comicvine.gamespot.com/api/issues/?filter=volume:2128&sort=issue_number:asc&api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json

all issues filtered by title sorted by cover date:
https://comicvine.gamespot.com/api/issues/?filter=name:avengers&sort=cover_date:asc&api_key=5881a5da17876142d003f9bcf843d4db4ce9fce2&format=json

sorting:
&sort=cover_date:asc
&issue_number:asc

biblio:
https://comicvine.gamespot.com/api/documentation
https://comicvine.gamespot.com/forums/api-developers-2334/simple-example-s-for-using-the-apis-1885345/
https://josephephillips.com/blog/how-to-use-comic-vine-api-part1

*/