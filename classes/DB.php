<?php
class DB extends DBcore{

	public function addCollection($name){
		$values = array('collection_name'=>$name, 'user_id'=>$_SESSION['user_id']);
		$lastInsertId = $this->insert('collections', $values);
		return $lastInsertId;
	}

	public function addIssue($series_id, $collection_id, $issue, $chrono='', $grade=8, $notes=''){
		$values = array('series_id'=>$series_id, 'collection_id'=>$collection_id, 'issue'=>$issue, 
						'chrono_index'=>$chrono, 'grade'=>$grade, 'notes'=>$notes, 'user_id'=>$_SESSION['user_id']);
		$lastInsertId = $this->insert('comics', $values);
		return $lastInsertId;
	}

	public function addSeries($name, $volume, $year, $first_issue, $last_issue, $comicvine_series_id, $comicvine_series_full, $image_thumb, $image_full){
		$values = array('series_name'=>$name, 'year'=>$year, 'first_issue'=>$first_issue, 'last_issue'=>$last_issue,
						'comicvine_series_id'=>$comicvine_series_id, 'comicvine_series_full'=>$comicvine_series_full, 
						'image_thumb'=>$image_thumb, 'image_full'=>$image_full, 'user_id'=>$_SESSION['user_id']);
		if($volume){
			$values['volume'] = $volume;
		}
		$lastInsertId = $this->insert('series', $values);
		return $lastInsertId;
	}

	public function changeChronoIndex($issue_id, $new_chrono_index){
		$values = array('chrono_index'=>$new_chrono_index);
		$rowsAffected = $this->update('comics', $values, 'issue_id='.intval($issue_id));
		return $rowsAffected;
	}

	public function changeCollectionId($issue_id, $new_collection_id){
		$values = array('collection_id'=>$new_collection_id);
		$rowsAffected = $this->update('comics', $values, 'issue_id='.intval($issue_id));
		return $rowsAffected;
	}

	public function changeCollectionName($collection_id, $new_name){
		$values = array('collection_name'=>$new_name);
		$rowsAffected = $this->update('collections', $values, 'collection_id='.intval($collection_id));
		return $rowsAffected;
	}

	public function changeGradeId($issue_id, $new_grade_id){
		$values = array('grade'=>$new_grade_id);
		$rowsAffected = $this->update('comics', $values, 'issue_id='.intval($issue_id));
		return $rowsAffected;
	}

	public function  changeIssueNumber($issue_id, $new_issue_number){
		$values = array('issue'=>$new_issue_number);
		$rowsAffected = $this->update('comics', $values, 'issue_id='.intval($issue_id));
		return $rowsAffected;
	}

	public function changeSeriesId($issue_id, $new_series_id){
		$values = array('series_id'=>$new_series_id);
		$rowsAffected = $this->update('comics', $values, 'issue_id='.intval($issue_id));
		return $rowsAffected;
	}

	public function changeSeriesName($series_id, $new_name){
		$values = array('series_name'=>$new_name);
		$rowsAffected = $this->update('series', $values, 'series_id='.intval($series_id));
		return $rowsAffected;
	}

	public function changeSeriesVolume($series_id, $new_volume){
		$values = array('volume'=>$new_volume);
		$rowsAffected = $this->update('series', $values, 'series_id='.intval($series_id));
		return $rowsAffected;
	}

	public function deleteCollection($collection_id){
		$values = array('collection_id'=>$collection_id);
		$rowsAffected = $this->delete('collections', $values, 'collection_id=:collection_id');
		return $rowsAffected;
	}

	public function deleteIssue($issue_id){
		$values = array('issue_id'=>$issue_id);
		$rowsAffected = $this->delete('comics', $values, 'issue_id=:issue_id');
		return $rowsAffected;
	}

	public function deleteSeries($series_id){
		$values = array('series_id'=>$series_id);
		$rowsAffected = $this->delete('series', $values, 'series_id=:series_id');
		return $rowsAffected;
	}

	public function getAllCollectionIds(){
		$query = "SELECT c.collection_id FROM collections c {$this->whereUserid('c')}";
//		self::logQueryAndValues($query, array(), 'getAllCollectionIds');
		$rows = $this->select($query);
		return array_column($rows, 'collection_id');
	}

	public function getAllGrades(){
		$query = "SELECT position, abbr, grade_name, short_desc, long_desc 
					FROM grades 
					ORDER BY position ASC";
//		self::logQueryAndValues($query, array(), 'getAllGrades');
		$rows = $this->select($query);
		return $rows;
	}

	public function getAllIssueIds(){
		$query = "SELECT c.issue_id FROM comics c {$this->whereUserid('c')}";
//		self::logQueryAndValues($query, array(), 'getAllIssueIds');
		$rows = $this->select($query);
		return array_column($rows, 'issue_id');
	}

	public function getAllIssueIdsForCollection($collection_id){
		$query = "SELECT c.issue_id
					FROM comics c 
					{$this->whereUserid('c')}
						AND c.collection_id=:collection_id";
		$values = array('collection_id'=>$collection_id);
//		self::logQueryAndValues($query, $values, 'getAllIssueIdsForCollection');
		$rows = $this->select($query, $values);
		return array_column($rows, 'issue_id');
	}

	public function getAllIssueIdsForSeries($series_id){
		$query = "SELECT c.issue_id
					FROM comics c 
					{$this->whereUserid('c')}
						AND c.series_id=:series_id";
		$values = array('series_id'=>$series_id);
//		self::logQueryAndValues($query, $values, 'getAllIssueIdsForSeries');
		$rows = $this->select($query, $values);
		return array_column($rows, 'issue_id');
	}

	public function getAllIssues(){
		$query = "SELECT c.issue_id, c.series_id, c.collection_id, c.issue, c.chrono_index, c.grade, c.note, c.cover_date, 
						c.comicvine_issue_id, c.comicvine_url, c.issue_title, c.creators, c.characters, c.image_full, c.image_thumb, 
						l.collection_name, s.series_name, s.volume, g.position, g.abbr, g.grade_name, g.short_desc, g.long_desc
					FROM comics c
					LEFT JOIN collections l ON c.collection_id=l.collection_id
					LEFT JOIN series s ON c.series_id=s.series_id
					LEFT JOIN grades g ON c.grade=g.position
					{$this->whereUserid('c')}
					ORDER BY s.series_name ASC, c.issue ASC, c.grade ASC";
//		self::logQueryAndValues($query, array(), 'getAllIssues');
		$rows = $this->select($query);
//		logDebug('result: '. var_export($rows, true));
		return $rows;
	}

	public function getAllSeries(){
		$query = "SELECT s.series_id, s.year, s.series_name, s.volume, s.comicvine_series_id, s.comicvine_series_full
					FROM series s
					{$this->whereUserid('s')}
					ORDER BY series_name ASC, volume ASC";
//		self::logQueryAndValues($query, array(), 'getAllSeries');
		$rows = $this->select($query);
//		logDebug('result: '. var_export($rows, true));
		return $rows;
	}

	public function getAllSeriesIds(){
		$query = "SELECT s.series_id FROM series s {$this->whereUserid('s')}";
		$rows = $this->select($query);
//		self::logQueryAndValues($query, array(), 'getAllSeriesIds');
		return array_column($rows, 'series_id');
	}

	public function getCollection($collection_id){
		$query = "SELECT c.collection_id, c.collection_name, COUNT(i.issue_id) AS issue_count 
					FROM collections c
					LEFT JOIN comics i USING (collection_id)
					{$this->whereUserid('c')}
						AND collection_id=".intval($collection_id);
		self::logQueryAndValues($query, array(), 'getCollection');
		$rows = $this->select($query);
		return (isset($rows[0]) ? $rows[0] : false);
	}

	public function getCollectionByName($name){
		$query = "SELECT c.collection_id, c.collection_name 
					FROM collections c
					{$this->whereUserid('c')}
						AND c.collection_name=:collection_name
					ORDER BY c.collection_name";
		$values = array('collection_name'=>$name);
		$rows = $this->select($query, $values);
		return (isset($rows[0]) ? $rows[0] : false);
	}

	public function getIssueDetails($issue_id){
		$query = "SELECT c.issue_id, c.series_id, c.collection_id, c.issue, c.chrono_index, 
						c.cover_date, c.grade, c.notes, c.comicvine_issue_id, c.comicvine_url, 
						c.issue_title, c.creators, c.characters, c.synopsis, c.image_full, c.image_thumb,
						s.year, s.series_name, s.volume, s.comicvine_series_id, s.comicvine_series_full, 
						l.collection_name
					FROM comics c
					LEFT JOIN series s USING (series_id)
					LEFT JOIN collections l ON c.collection_id=l.collection_id
					{$this->whereUserid('c')}
						AND issue_id=:issue_id";
		$values = array('issue_id'=>$issue_id);
//		$this->logQueryAndValues($query, $values, 'getIssueDetails');
		$rows = $this->select($query, $values);
		return (isset($rows[0]) ? $rows[0] : false);
	}	

	public function getSeries($series_id){
		$query = "SELECT s.series_id, s.year, s.series_name, s.volume, s.first_issue, s.last_issue,
						s.comicvine_series_id, s.comicvine_series_full, s.image_thumb, s.image_full,
						COUNT(i.issue_id) AS issue_count
					FROM series s
					LEFT JOIN comics i USING (series_id)
					{$this->whereUserid('s')}
						AND s.series_id=:series_id";
		$values = array('series_id'=>$series_id);
		$this->logQueryAndValues($query, $values, 'getSeries');
		$rows = $this->select($query, $values);
		return (isset($rows[0]) ? $rows[0] : false);
	}

	public function getSeriesByName($seriesName, $volume){
		$query = "SELECT s.series_id, s.year, s.series_name, s.volume, s.comicvine_series_id, s.comicvine_series_full
					FROM series s
					{$this->whereUserid('s')}
						AND s.series_name=:name AND s.volume=:volume";
		$values = array('name'=>$seriesName, 'volume'=>$volume);
//		$this->logQueryAndValues($query, $values, 'getSeriesByName');
		$rows = $this->select($query, $values);
		return (isset($rows[0]) ? $rows[0] : false);
	}
	
	public function getUserByNumber($user_id){
		$query = "SELECT uid FROM users WHERE user_id=:user_id";
		$values = array('user_id'=>$user_id);
		$rows = $this->select($query, $values);
		return (isset($rows[0]['uid']) ? true : false);
	}
	
	public function getUserHeader($user_id){
		$query = "SELECT header FROM users WHERE user_id=:user_id";
		$values = array('user_id'=>$user_id);
		$this->logQueryAndValues($query, $values, 'getUserHeader');
		$rows = $this->select($query, $values);
		return (isset($rows[0]['header']) ? $rows[0]['header'] : false);
	}

	public function saveComicvineSeriesInfo($year, $name, $volume, $comicvine_series_id, $comicvine_series_full){
		$values = array('year'=>$year, 'series_name'=>$name, 'volume'=>$volume, 'comicvine_series_id'=>$comicvine_series_id, 
						'comicvine_series_full'=>$comicvine_series_full, 'user_id'=>$_SESSION['user_id']);
//		logDebug('saveComicvineSeriesInfo: '.var_export($values, true));
		if($this->verifyColumns('series', $values)){
			$lastInsertId = $this->insert('series', $values);
			return $lastInsertId;
		}
		return 0;
	}

	public function selectCountFromTable($indexColumn, $table){
		if(' ' === substr($table, strlen($table)-2, 1)){ $table = substr($table, 0, strlen($table)-2); }//remove table abbr
		$query = "SELECT COUNT({$indexColumn}) AS rowcount FROM {$table} {$this->whereUserid()}";
		$values = array();
		$this->logQueryAndValues($query, $values, 'selectCountFromTable');
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
			$lastInsertId = $this->update('comics', $values, 'issue_id='.intval($issueid));
			return $lastInsertId;
		}
		return 0;
	}

	public function verifyUser($username, $password){
		$hashed = md5($password);
		$query = "SELECT user_id, uid 
					FROM users 
					WHERE uid=:username 
						AND pwd=:password";
		$values = array('username'=>$username, 'password'=>$hashed);
//		$this->logQueryAndValues($query, $values, 'verifyUser');
		$rows = $this->select($query, $values);
		return (isset($rows[0]['user_id']) ? $rows[0]['user_id'] : false);
	}

	//////////////////////////////////////////////////////////////////////////////////

	public function genericSelect($query, $values=array(), $pdoFetch=PDO::FETCH_ASSOC){
//		$this->logQueryAndValues($query, $values, 'genericSelect');
		return $this->select($query, $values, $pdoFetch);
	}
	
	private function whereUserid($prefix=''){
		if($prefix){ $prefix = $prefix.'.'; }
		return " WHERE {$prefix}user_id=".$_SESSION['siteUser'];
	}

	public function __construct(){
		parent::__construct(self::HOST, self::USER, self::PASS, self::DB_TABLES);
	}

	const DB_TABLES = array('collections', 'comics', 'series', 'grades', 'users');
	const HOST = 'mysql:host=localhost;dbname=davpeter_comic';
	const USER = 'davpeter_comic';
	const PASS = 'c0micsRahhSUM';
}