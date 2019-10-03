<?php
class DB extends DBcore{
	
	public function addCollection($name){
		$values = array('collection_name'=>$name);
		$lastInsertId = $this->insert('collections', $values);
		return $lastInsertId;
	}

	public function addIssue($series_id, $collection_id, $issue, $chrono='', $grade=8){
		$values = array('series_id'=>$series_id, 'collection_id'=>$collection_id, 'issue'=>$issue, 'chrono_index'=>$chrono, 'grade'=>$grade);
		$lastInsertId = $this->insert('comics', $values);
		return $lastInsertId;
	}
	
	public function addSeries($name, $volume, $collection_id, $year, $comicvine_series_id, $comicvine_series_full){
		$values = array('title'=>$name, 'volume'=>$volume, 'collection_id'=>$collection_id, 'year'=>$year,
						'comicvine_series_id'=>$comicvine_series_id, 'comicvine_series_full'=>$comicvine_series_full);
		$lastInsertId = $this->insert('series', $values);
		return $lastInsertId;
	}
	
	public function changeCollectionName($collection_id, $new_name){
		$values = array('collection_name'=>$new_name);
		$rowsAffected = $this->update('collections', $values, 'collection_id='.intval($collection_id));
		return $rowsAffected;
	}
	
	public function changeSeriesName($series_id, $new_name){
		$values = array('title'=>$new_name);
		$rowsAffected = $this->update('series', $values, 'series_id='.intval($series_id));
		return $rowsAffected;
	}
	
	public function getAllGrades(){
		$query = "SELECT position, abbr, name, short_desc, long_desc 
					FROM grades 
					ORDER BY position ASC";
//		self::logQueryAndValues($query, array(), 'getAllGrades');
		$rows = $this->select($query);
		return $rows;
	}
	
	public function getAllIssues(){
		$query = "SELECT c.comic_id, c.series_id, c.collection_id, c.issue, c.chrono_index, c.grade, c.cover_date, 
						c.comicvine_url, c.issue_title, c.creators, c.characters, c.image_full, c.image_thumb, 
						l.collection_name, s.title, s.volume, g.position, g.abbr, g.name, g.short_desc, g.long_desc
					FROM comics c
					LEFT JOIN collections l ON c.collection_id=l.collection_id
					LEFT JOIN series s ON c.series_id=s.series_id
					LEFT JOIN grades g ON c.grade=g.position
					ORDER BY s.title ASC, c.issue ASC, c.grade ASC";
		self::logQueryAndValues($query, array(), 'getAllIssues');
		$rows = $this->select($query);
//		logDebug('result: '. var_export($rows, true));
		return $rows;
	}
	
	public function getAllSeries(){
		$query = "SELECT s.series_id, s.year, s.title, s.volume, s.comicvine_series_id, s.comicvine_series_full, c.collection_name
					FROM series s
					LEFT JOIN collections c USING (collection_id)
					ORDER BY title ASC, volume ASC";
		self::logQueryAndValues($query, array(), 'getAllSeries');
		$rows = $this->select($query);
//		logDebug('result: '. var_export($rows, true));
		return $rows;
	}
	
	public function getCollectionByName($name){
		$query = "SELECT collection_id, collection_name 
					FROM collections 
					WHERE collection_name=:collection_name
					ORDER BY collection_name";
		$values = array('collection_name'=>$name);
		$rows = $this->select($query, $values);
		return (isset($rows[0]) ? $rows[0] : false);
	}
	
	public function getCollections(){
		$query = "SELECT collection_id, collection_name 
					FROM collections 
					ORDER BY collection_name";
		$rows = $this->select($query);
		return $rows;
	}
	
	public function getIssueDetails($comic_id){
		$query = 'SELECT c.comic_id, c.series_id, c.collection_id, c.issue, 
						c.chrono_index, c.cover_date, c.grade, c.comicvine_issue_id, c.comicvine_url, 
						c.issue_title, c.creators, c.characters, c.synopsis, c.image_full, c.image_thumb,
						s.year, s.title, s.volume, s.comicvine_series_id, s.comicvine_series_full, 
						l.collection_name
					FROM comics c
					LEFT JOIN series s USING (series_id)
					LEFT JOIN collections l ON c.collection_id=l.collection_id
					WHERE comic_id=:comic_id';
		$values = array('comic_id'=>$comic_id);
		$this->logQueryAndValues($query, $values, 'getIssueDetails');
		$rows = $this->select($query, $values);
		return (isset($rows[0]) ? $rows[0] : false);
	}	
	
	//TODO; this will disappear, all issues will have a series_id so there will be nothing to look for or find
//	public function getIssuesThatMatch($name, $year, $first_issue, $last_issue){
//		$cover_date = "{$year}-00-00";
//		$query = "SELECT c.comic_id, c.series_id, c.collection_id, s.title, c.issue, c.cover_date, c.grade, s.volume
//					FROM comics c
//					LEFT JOIN series s USING (series_id)
//					WHERE c.series_id = 0
//						AND s.title = :name
//						AND c.cover_date >= {$cover_date}
//						AND ((c.issue >= :first_issue AND c.issue <= :last_issue) OR c.issue='88888')";
//		$values = array('name'=>$name, 'first_issue'=>$first_issue, 'last_issue'=>$last_issue);
//		$this->logQueryAndValues($query, $values, 'getIssuesThatMatch');
//		$rows = $this->select($query, $values);
//		return $rows;
//	}
	
	public function getSeries($series_id){
		$query = "SELECT series_id, collection_id, year, title, volume, comicvine_series_id, comicvine_series_full
					FROM series
					WHERE series_id=:series_id";
		$values = array('series_id'=>$series_id);
		$this->logQueryAndValues($query, $values, 'getSeries');
		$rows = $this->select($query, $values);
		return (isset($rows[0]) ? $rows[0] : false);
	}
	
	public function getSeriesByName($seriesName, $volume){
		$query = "SELECT series_id, collection_id, year, title, volume, comicvine_series_id, comicvine_series_full
					FROM series
					WHERE title=:title AND volume=:volume";
		$values = array('title'=>$seriesName, 'volume'=>$volume);
		$this->logQueryAndValues($query, $values, 'getSeriesByName');
		$rows = $this->select($query, $values);
		return ($rows[0] ? $rows[0] : false);
	}
	
	public function saveComicvineSeriesInfo($collection_id, $year, $title, $volume, $comicvine_series_id, $comicvine_series_full){
		$values = array('collection_id'=>$collection_id, 'year'=>$year, 'title'=>$title, 'volume'=>$volume, 
						'comicvine_series_id'=>$comicvine_series_id, 'comicvine_series_full'=>$comicvine_series_full);
		logDebug('saveComicvineSeriesInfo: '.var_export($values, true));
		if($this->verifyColumns('series', $values)){
			$lastInsertId = $this->insert('series', $values);
			return $lastInsertId;
		}
		return 0;
	}
	
	public function selectCountFromTable($indexColumn, $table){
		if(' ' === substr($table, strlen($table)-2, 1)){ $table = substr($table, 0, strlen($table)-2); }
		$query = "SELECT COUNT({$indexColumn}) AS rowcount FROM {$table}";
		$values = array();
//		$this->logQueryAndValues($query, $values, 'selectCountFromTable');
		$resultTotal = $this->select($query, $values);
		return $resultTotal;
	}
	
	public function selectFoundRows(){
		$query = 'SELECT FOUND_ROWS()';
		$rows = $this->select($query, array(), PDO::FETCH_BOTH);
		return ($rows ? $rows : array());
	}
	
	public function updateIssue($issueid, $values){
		if($this->verifyColumns('comics', $values)){
			$lastInsertId = $this->update('comics', $values, 'comic_id='.intval($issueid));
			return $lastInsertId;
		}
		return 0;
	}
	
	public function verifyUser($username, $password){
		$hashed = md5($password);
		$query = "SELECT uid 
					FROM users 
					WHERE uid=:username 
						AND pwd=:password";
		$values = array('username'=>$username, 'password'=>$hashed);
		$this->logQueryAndValues($query, $values, 'verifyUser');
		$rows = $this->select($query, $values);
		return (isset($rows[0]) ? true : false);
	}
	
	public function genericSelect($query, $values=array(), $pdoFetch=PDO::FETCH_ASSOC){
//		$this->logQueryAndValues($query, $values, 'genericSelect');
		return $this->select($query, $values, $pdoFetch);
	}
	
	public function __construct(){
		parent::__construct(self::HOST, self::USER, self::PASS, self::DB_TABLES);
	}

	const DB_TABLES = array('collections', 'comics', 'series');
	const HOST = 'mysql:host=localhost;dbname=davpeter_comic';
	const USER = 'davpeter_comic';
	const PASS = 'c0micsRahhSUM';
}