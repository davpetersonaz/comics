<?php
class Grading{

	//https://comicspriceguide.com/comic-book-grading

	public function getAllGrades(){
		return $this->grades;
	}

	public function getGrade($position){
		return $this->grades[$position];
	}

	public function getLongDesc($abbr){
		$return = array();
		if($this->grades){
			$this->grades = $this->indexArrayByColumn($this->grades, 'abbr');
			$return = $this->grades[$abbr]['long_desc'];
		}
		return $return;
	}
	
	public function getShortDesc($abbr){
		$return = array();
		if($this->grades){
			$this->grades = $this->indexArrayByColumn($this->grades, 'abbr');
			$return = $this->grades[$abbr]['short_desc'];
		}
		return $return;
	}

	private function getAll(){
		$this->grades = $this->db->getAllGrades();
	}

	public function __construct(DB $db){
		$this->db = $db;
		$this->getAll();
	}

	private $db = false;
	private $grades = array();
}