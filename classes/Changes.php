<?php
class Changes{
	
	public function addChange($item_type=2, $item_id=0, $changes=array()){
		$this->db->addChange($item_type, $item_id, $changes);
	}
	
	public function getChanges($item_type=false){
		return $this->db->getChanges($item_type);
	}
	
	public function __construct(DB $db){
		$this->db = $db;
	}

	protected $db = false;
}