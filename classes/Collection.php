<?php
class Collection{
	
	public static function changeCollectionName($db, $collection_id, $new_name){
		return $db->changeCollectionName($collection_id, $new_name);
	}
	
	public static function createCollection($db, $collection_name){
		return $db->addCollection($collection_name);
	}
	
	public static function getCollectionByName($db, $collection_name){
		return $db->getCollectionByName($collection_name);
	}
	
	public static function getCollections($db){
		return $db->getCollections();
	}

	public function __construct($db){
		$this->db = $db;
	}
	
	protected $db = false;
	protected $collection_id = false;//db collection id
	protected $name = false;//my collection name
}