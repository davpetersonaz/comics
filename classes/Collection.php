<?php
class Collection{

	public function changeCollectionName($new_name){
		$this->name = $new_name;
		return $this->db->changeCollectionName($this->collection_id, $new_name);
	}

	public function changeDescription($new_description){
		$this->description = $new_description;
		return $this->db->changeDescription($this->collection_id, $new_description);
	}

	public static function createCollection(DB $db, $collection_name, $description){
		return $db->addCollection($collection_name, $description);
	}

	public function delete(){
		$rowsAffected = $this->db->deleteCollection($this->collection_id);
		return $rowsAffected;
	}

	public static function getCollectionByName(DB $db, $collection_name){
		return $db->getCollectionByName($collection_name);
	}

	public static function getAllCollections(DB $db){
		$collections = array();
		$dbcollections = $db->getAllCollectionIds();
		foreach($dbcollections as $dbcollectionid){
			$collections[] = new Collection($db, $dbcollectionid);
		}
		usort($collections, 'Func::compareByObjectName');
		return $collections;
	}

	public static function getCollectionsIdName(DB $db){
		return $db->getCollectionsIdName();
	}

	////////////////////////////////////////////////////////////////

	public function get($collection_id){
		$collection = $this->db->getCollection($collection_id);
		if($collection){
			$this->collection_id = $collection['collection_id'];
			$this->name = $collection['collection_name'];
			$this->description = $collection['description'];
			$this->issue_count = $collection['issue_count'];
		}
	}

	public function isCollection(){
		return ($this->collection_id ? true : false);
	}

	public function getId(){ return $this->collection_id; }
	public function getName(){ return $this->name; }
	public function getDescription(){ return $this->description; }
	public function getIssueCount(){ return $this->issue_count; }

	public function __construct(DB $db, $collection_id=false){
		$this->db = $db;
		if($collection_id){
			$this->get($collection_id);
		}
//		logDebug('collection: '.var_export($this, true));
	}

	protected $db = false;
	protected $collection_id = false;//db collection id
	protected $name = false;//my collection name
	protected $description = false;
	protected $issue_count = 0;//number of issues in collection
}