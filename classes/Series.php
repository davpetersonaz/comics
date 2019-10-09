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
		$this->year = $comicvine_info[4];
		$this->first_issue = $comicvine_info[6];
		$this->last_issue = $comicvine_info[7];
		$this->comicvine_series_id = $comicvine_info[8];
		$this->comicvine_series_full = $comicvine_info[9];
		$this->series_id = $this->db->addSeries($this->series_name, $this->volume, $this->year, $this->first_issue, $this->last_issue, $this->comicvine_series_id, $this->comicvine_series_full);
		return $this->series_id;
	}	

	public function delete(){
		$rowsAffected = $this->db->deleteSeries($this->series_id);
		return $rowsAffected;
	}

	public static function getAllSeries(DB $db){
		$series = array();
		$dbseries = $db->getAllSeriesIds();
		foreach($dbseries as $dbseriesid){
			$series[] = new Series($db, $dbseriesid);
		}
		usort($series, 'Func::compareByObjectName');
		return $series;
	}

	//can be used for dropdown options, title tooltips
	public function getDisplayText(){
		return "{$this->series_name} vol.{$this->volume} ({$this->year})";
	}

	public static function getSeriesByName(DB $db, $name, $volume){
		return $db->getSeriesByName($name, $volume);
	}

	////////////////////////////////////////////////////////////////

	public function get($series_id){
		$series = $this->db->getSeries($series_id);
		if($series){
			$this->series_id = $series_id;
			if($series['series_name']){ $this->series_name = $series['series_name']; }
			if($series['volume']){ $this->volume = $series['volume']; }
			if($series['year']){ $this->year = $series['year']; }
			if($series['first_issue']){ $this->first_issue = $series['first_issue']; }
			if($series['last_issue']){ $this->last_issue = $series['last_issue']; }
			if($series['comicvine_series_id']){ $this->comicvine_series_id = $series['comicvine_series_id']; }
			if($series['comicvine_series_full']){ $this->comicvine_series_full = $series['comicvine_series_full']; }
			$this->issue_count = $series['issue_count'];
		}
	}

	public function isSeries(){
		return ($this->series_id ? true : false);
	}

	public function getId(){ return $this->series_id; }
	public function getName(){ return $this->series_name; }
	public function getVolume(){ return $this->volume; }
	public function getYear(){ return $this->year; }
	public function getFirstIssue(){ return $this->first_issue; }
	public function getLastIssue(){ return $this->last_issue; }
	public function getComicvineId(){ return $this->comicvine_series_id; }
	public function getComicvineIdFull(){ return $this->comicvine_series_full; }
	public function getIssueCount(){ return $this->issue_count; }

	public function __construct(DB $db, $series_id=false){
		$this->db = $db;
		if($series_id !== false){
			$this->get($series_id);
		}
	}

	protected $db = false;
	protected $series_id = false;//db series id
	protected $series_name = false;//my series name, not comicvine's name
	protected $volume = false;//my series volume number, not comicvine's volume
	protected $year = false;//copyright year
	protected $first_issue = false;//first issue of series
	protected $last_issue = false;//last issue of series
	protected $comicvine_series_id = false;//comicvine series id, ex) Avengers vol.1 is 2128
	protected $comicvine_series_full = false;//comicvine series full id, ex) Avengers vol.1 is 4000-2128
	protected $issue_count = 0;
}