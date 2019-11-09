<?php
class Series{

	public function changeSeriesName($new_name){
		$this->series_name = $new_name;
		return $this->db->changeSeriesName($this->series_id, $new_name);
	}

	public function changeSeriesVolume($new_volume){
		$this->volume = $new_volume;
		return $this->db->changeSeriesVolume($this->series_id, $new_volume);
	}

	public function countIssues(){
		return $this->db->countIssuesInSeries($this->series_id);
	}

	public function createSeries($name, $volume, $comicvine_info){
		logDebug('comicvine: '.var_export($comicvine_info, true));
		$this->series_name = $name;
		$this->volume = intval($volume);
		$this->publisher = $comicvine_info[3];
		$this->year = $comicvine_info[4];
		$this->series_issue_count = $comicvine_info[5];
		$this->first_issue = $comicvine_info[6];
		$this->last_issue = $comicvine_info[7];
		$this->comicvine_series_id = $comicvine_info[8];
		$this->comicvine_series_full = $comicvine_info[9];
		$this->image_thumb = $comicvine_info[10];
		$this->image_full = $comicvine_info[11];
		$this->series_id =	$this->db->addSeries($this->series_name, $this->volume, $this->year, 
							$this->publisher, $this->first_issue, $this->last_issue, $this->comicvine_series_id, 
							$this->comicvine_series_full, $this->image_thumb, $this->image_full);
		return $this->series_id;
	}	

	public function delete(){
		$rowsAffected = $this->db->deleteSeries($this->series_id);
		return $rowsAffected;
	}

	public function doesSeriesExist($comicvine_series_full){
		$rows = $this->db->getSeriesByComicvineFull($comicvine_series_full);
		return ($rows !== false ? true : false);
	}

	public static function getAllSeries(DB $db, Curl $curl){
		$series = array();
		$dbseries = $db->getAllSeriesIds();
		foreach($dbseries as $dbseriesid){
			$series[] = new Series($db, $curl, $dbseriesid);
		}
		return $series;
	}
	
	public function getComicvineApiUrl(){
		return $this->curl::getComivineSeriesUrl($this->getComicvineIdFull());
	}

	public function getComicvineUrl(){
		return 'https://comicvine.gamespot.com/volume/'.$this->getComicvineIdFull();
	}

	//can be used for dropdown options, title tooltips
	public function getDisplayText(){
		return "{$this->series_name}".($this->volume > 1 ? " vol.{$this->volume}" : "")." ({$this->year})";
	}
	public static function getDisplayTextStatic($name, $volume, $year){
		return "{$name}".($volume > 1 ? " vol.{$volume}" : "")." ({$year})";
	}

	public static function getIssueCountStatic(DB $db, $series_id){
		return $db->getIssueCountForSeries($series_id);
	}
	
	public static function getSeriesByName(DB $db, $name, $volume){
		return $db->getSeriesByName($name, $volume);
	}

	public static function getSeriesIdName(DB $db){
		return $db->getSeriesIdName();
	}
	
	public function updateSeriesValues($values){
		return $this->db->updateSeries($this->series_id, $values);
	}

	////////////////////////////////////////////////////////////////

	public function get($series_id){
		$series = $this->db->getSeries($series_id);
		if($series){
			$this->series_id = $series_id;
			if($series['series_name']){ $this->series_name = $series['series_name']; }
			if($series['volume']){ $this->volume = $series['volume']; }
			if($series['year']){ $this->year = $series['year']; }
			if($series['publisher']){ $this->publisher = $series['publisher']; }
			if($series['first_issue']){ $this->first_issue = $series['first_issue']; }
			if($series['last_issue']){ $this->last_issue = $series['last_issue']; }
			if($series['series_issue_count']){ $this->series_issue_count = $series['series_issue_count']; }
			if($series['comicvine_series_id']){ $this->comicvine_series_id = $series['comicvine_series_id']; }
			if($series['comicvine_series_full']){ $this->comicvine_series_full = $series['comicvine_series_full']; }
			if($series['image_thumb']){ $this->image_thumb = $series['image_thumb']; }
			if($series['image_full']){ $this->image_full = $series['image_full']; }
			$this->issue_count = $series['issue_count'];
		}
	}

	public function isSeries(){
		return ($this->series_id ? true : false);
	}
	
	public function toArray(){
		$return = array();
		$return['series_id'] = $this->getId();
		$return['year'] = $this->getYear();
		$return['series_name'] = urlencode($this->getName());
		$return['volume'] = $this->getVolume();
		$return['publisher'] = urlencode($this->getPublisher());
		$return['first_issue'] = $this->getFirstIssue();
		$return['last_issue'] = $this->getLastIssue();
		$return['series_issue_count'] = $this->getSeriesIssueCount();
		$return['comicvine_series_full'] = $this->getComicvineIdFull();
		$return['user_id'] = $this->getUserId();
		return $return;
	}

	public function getId(){ return $this->series_id; }
	public function getName(){ return $this->series_name; }
	public function getVolume(){ return $this->volume; }
	public function getYear(){ return $this->year; }
	public function getPublisher(){ return $this->publisher; }
	public function getFirstIssue(){ return $this->first_issue; }
	public function getLastIssue(){ return $this->last_issue; }
	public function getSeriesIssueCount(){ return $this->series_issue_count; }
	public function getComicvineId(){ return $this->comicvine_series_id; }
	public function getComicvineIdFull(){ return $this->comicvine_series_full; }
	public function getImageThumb(){ return $this->image_thumb; }
	public function getImageFull(){ return $this->image_full; }
	public function getIssueCount(){ return intval($this->issue_count); }
	public function getUserId(){ return $this->user_id; }

	public function __construct(DB $db, Curl $curl, $series_id=false){
		$this->db = $db;
		$this->curl = $curl;
		if($series_id !== false){
			$this->get($series_id);
		}
	}

	protected $db = false;
	protected $curl = false;
	protected $series_id = false;//db series id
	protected $series_name = false;//my series name, not comicvine's name
	protected $volume = false;//my series volume number, not comicvine's volume
	protected $year = false;//copyright year
	protected $publisher = false;//series publisher
	protected $first_issue = false;//first issue of series
	protected $last_issue = false;//last issue of series
	protected $series_issue_count = false;//number of issues in this series (not my physical count of issues)
	protected $comicvine_series_id = false;//comicvine series id, ex) Avengers vol.1 is 2128
	protected $comicvine_series_full = false;//comicvine series full id, ex) Avengers vol.1 is 4000-2128
	protected $image_thumb = false;//thumbnail for first issue
	protected $image_full = false;//full image for first issue
	protected $issue_count = 0;//physical count of my issues in this series
	protected $user_id = false;
}