<?php
class Collection{

	public function changeCollectionName($new_name){
		$this->name = $new_name;
		return $this->db->changeCollectionName($this->collection_id, $new_name);
	}

	public static function createCollection($db, $collection_name){
		return $db->addCollection($collection_name);
	}

	public function delete(){
		$rowsAffected = $this->db->deleteCollection($this->collection_id);
		return $rowsAffected;
	}

	public static function getCollectionByName($db, $collection_name){
		return $db->getCollectionByName($collection_name);
	}

	public static function getAllCollections($db){
		$collections = array();
		$dbcollections = $db->getAllCollectionIds();
		foreach($dbcollections as $dbcollectionid){
			$collections[] = new Collection($db, $dbcollectionid);
		}
		usort($collections, 'Func::compareByObjectName');
		return $collections;
	}

	////////////////////////////////////////////////////////////////

	public function get($collection_id){
		$collection = $this->db->getCollection($collection_id);
		if($collection){
			$this->collection_id = $collection['collection_id'];
			$this->name = $collection['collection_name'];
			$this->series_count = $collection['series_count'];
		}
	}

	public function isCollection(){
		return ($this->collection_id ? true : false);
	}

	public function getId(){ return $this->collection_id; }
	public function getName(){ return $this->name; }
	public function getSeriesCount(){ return $this->series_count; }

	public function __construct(DB $db, $collection_id=false){
		$this->db = $db;
		if($collection_id){
			$this->get($collection_id);
		}
	}

	protected $db = false;
	protected $collection_id = false;//db collection id
	protected $name = false;//my collection name
	protected $series_count = 0;
}