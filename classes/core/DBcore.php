<?php
class DBcore{

	protected function delete($table, $values=array(), $where=false){
		$this->handleFakeTables($table);
		$return = 0;
		try{
			$query = 'DELETE FROM '.$table.($where?' WHERE '.$where:'');
			$this->logQueryAndValues($query, $values, 'DBcore->delete');
			$stmt = $this->connection->prepare($query);
			foreach($values as $column=>$value){
				$stmt->bindValue(':'.$column, $value);
			}
			$stmt->execute();
			$return = $stmt->rowCount();
		}catch (PDOException $e){
			logDebug('ERROR: selecting: '.var_export($e, true));
			logDebug('ERROR: params: '.var_export($values, true));
		}
//		logDebug('delete returning: '.$return);
		return $return;
	}

	protected function deleteQuery($query, $values=array()){
		$return = 0;
		try{
//			$this->logQueryAndValues($query, $values, 'DBcore->deleteQuery');
			$stmt = $this->connection->prepare($query);
			foreach($values as $placeholder=>$value){
				$stmt->bindValue(':'.$placeholder, $value);
			}
			$stmt->execute();
			$return = $stmt->rowCount();
		}catch (PDOException $e){
			logDebug('ERROR: deleting: '.var_export($e, true));
			logDebug('ERROR: params: '.var_export($values, true));
		}
//		logDebug('delete effected '.$return.' rows');
		return $return;
	}

	protected function insert($table, $values=array()){
		$this->handleFakeTables($table);
		$return = false;
		try{
			$query = 'INSERT INTO '.$table;
			$firstIteration = true;
			foreach($values as $column=>$value){
				$query .= ($firstIteration ? ' (' : ', ').$column;
				$firstIteration = false;
			}
			if(!$firstIteration){ $query .= ')'; }
			if(!empty($values)){ $query .= ' VALUES '; }
			$firstIteration = true;
			foreach($values as $column=>$value){
				$query .= ($firstIteration ? ' (' : ', ').':'.$column;
				$firstIteration = false;
			}
			if(!$firstIteration){ $query .= ')'; }
			$this->logQueryAndValues($query, $values, 'DBcore->insert');
			$stmt = $this->connection->prepare($query);
			foreach($values as $column=>$value){
				$stmt->bindValue(':'.$column, $value);
			}
			if(!$stmt->execute()){
				logDebug('ERROR: executing query: '.$this->connection->errorCode().': '.var_export($this->connection->errorInfo(), true));
			}else{
				$return = $this->connection->lastInsertId();
			}
		}catch (PDOException $e){
			logDebug('ERROR: inserting: '.var_export($e, true));
			logDebug('ERROR: params: '.var_export($values, true));
		}
		logDebug('insert returning: '.var_export($return, true));
		return $return;
	}

	protected function insertQuery($query, $values=array()){
		$return = false;
		try{
//			$this->logQueryAndValues($query, $values, 'DBcore->insertQuery');
			$stmt = $this->connection->prepare($query);
			foreach($values as $column=>$value){
				$stmt->bindValue(':'.$column, $value);
			}
			if(!$stmt->execute()){
				logDebug('ERROR: executing query: '.$this->connection->errorCode().': '.var_export($this->connection->errorInfo(), true));
			}else{
				$return = $this->connection->lastInsertId();
			}
		}catch (PDOException $e){
			logDebug('ERROR: inserting: '.var_export($e, true));
			logDebug('ERROR: params: '.var_export($values, true));
		}
//		logDebug('insertQuery returning: '.$return);
		return $return;
	}

	protected function select($query, $values=array(), $fetchModes=PDO::FETCH_ASSOC){
		$return = array();
		try{
//			$this->logQueryAndValues($query, $values, 'DBcore->select');
			$stmt = $this->connection->prepare($query);
			foreach($values as $column=>$value){
				$stmt->bindValue(':'.$column, $value);
			}
			$stmt->execute();
//			logDebug('fetchModes: '.var_export($fetchModes, true));
			$rows = $stmt->fetchAll($fetchModes);
			if(count($rows) > 0){
				$return = $rows;
			}
		}catch (PDOException $e){
			logDebug('ERROR: selecting: '.var_export($e, true));
			logDebug('ERROR: params: '.var_export($values, true));
		}
//		logDebug('select returning '.count($return).' rows');
		return $return;
	}

	//updates by tablename, columnvalues, and where-clause
	protected function update($table, $values=array(), $where=''){
		$return = 0;
		$query = "UPDATE {$table} SET ";
		foreach(array_keys($values) as $column){
			$query .= "{$column}=:{$column}, "; 
		}
		$query = substr($query, 0, -2);//remove the last ", "
		$query .= " WHERE {$where}";
		$this->logQueryAndValues($query, $values, 'DBcore->update');
		try{
			$stmt = $this->connection->prepare($query);
			foreach($values as $placeholder=>$value){
				$stmt->bindValue(':'.$placeholder, $value);
			}
			$stmt->execute();
			$return = $stmt->rowCount();
		}catch (PDOException $e){
			logDebug('ERROR: updating: '.var_export($e, true));
			logDebug('ERROR: params: '.var_export($values, true));
		}
//		logDebug('update effected '.$return.' rows');
		return $return;
	}

	protected function updateQuery($query, $values=array()){
		$return = 0;
		try{
//			$this->logQueryAndValues($query, $values, 'DBcore->update');
			$stmt = $this->connection->prepare($query);
			foreach($values as $placeholder=>$value){
				$stmt->bindValue(':'.$placeholder, $value);
			}
			$stmt->execute();
			$return = $stmt->rowCount();
		}catch (PDOException $e){
			logDebug('ERROR: updating: '.var_export($e, true));
			logDebug('ERROR: params: '.var_export($values, true));
		}
//		logDebug('update effected '.$return.' rows');
		return $return;
	}

	public function dbSafe($data){
		// 1. remove white spaces (trim)
		// 2. remove any escape slashes put infront of quotes by magic quotes (stripslashes)
		// 3. encode all special characters i.e. double quotes, single quotes, ampersands, symbols etc (htmlentities) - no need to decode when pulling data out of database. browser will automatically use doctype to decode
		$safe_data = htmlentities(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
		return $safe_data;
	}
	
	//this returns the actual column name, it does not lowercase them or anything.
	public function getColumns($table){
		$return = array();
		$rows = $this->select("SHOW TABLES LIKE '{$table}'");
		if(!empty($rows)){
			$rows = $this->select("SHOW COLUMNS FROM {$table}", array(), PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
//			logDebug('getColumns: '.var_export($rows, true));
			$return = array_keys($rows);
		}
		return $return;
	}
	
	public function verifyColumns($table, $values){
		$result = true;
		$dbcolumns = $this->getColumns($table);
		foreach(array_keys($values) as $column){
			if(!in_array($column, $dbcolumns)){
				$result = false;
				error_log("column [{$column}] doesnt exist in table [{$table}]");
			}
		}
		if(!$result){
			logDebug("at least one column doesnt exist in {$table}: ".implode(', ', array_keys($values)));
		}
		return $result;
	}
	
	public function getLastSequence($table, $show_id=false){
		$where = ($show_id ? ' WHERE show_id=:show_id' : '');
		$params = ($show_id ? array('show_id'=>$show_id) : array());
		if($show_id){
			$params = array('show_id'=>$show_id);
		}
		$sequence = 0;
		$rows = $this->select('SELECT MAX(sequence) AS sequence FROM '.$table.$where, $params);
		if($rows[0]['sequence']){
			$sequence = $rows[0]['sequence'];
		}
//		logDebug('getLastSequence returning: '.$sequence);
		return $sequence;
	}

	public function replicateFetchColumnGroup($rows){
		$returnArray = array();
		foreach($rows as $row){
			$returnArray[$row[0]] = array(0=>$row[1]);
		}
		return $returnArray;
	}

	public function indexArrayByColumn($array, $column){
		$return = array();
		if(count($array) > 0){
			foreach($array as $row){
				$return[$row[$column]] = $row;
			}
		}
		uksort($return, 'strnatcasecmp');
		return $return;
	}

	public function logQueryAndValues($query, $values=array(), $callingFunction=false){
//		logDebug('logQueryAndValues: '.var_export($values, true));
		if($values){
			foreach($values as $key=>$value){
				if(substr($key, 0, 1) !== ':'){ $key = ':'.$key; }
				$query = str_replace($key, "'".$value."'", $query);
			}
		}
		logDebug(($callingFunction ? $callingFunction.': ' : '').$query);
	}

	private function handleFakeTables($table){
		if(!in_array($table, $this->db_tables)){
			$msg = 'ERROR: attempted access of fake table ['.$table.'], exiting';
			logDebug($msg);
			exit;
		}
	}

	function __construct($host, $user, $pass, $db_tables){
		$this->connection = new PDO($host, $user, $pass);
		$this->db_tables = $db_tables;
	}

	private $db = NULL;
	private $connection = NULL;//connection
	private $db_tables = NULL;
}