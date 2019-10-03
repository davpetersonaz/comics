<?php
class Series{
	
	public static function changeSeriesName($db, $series_id, $new_name){
		return $db->changeSeriesName($series_id, $new_name);
	}
	
	public function createSeries($title, $volume, $collection_id, $comicvine_info){
		logDebug('comicvine: '.var_export($comicvine_info, true));
		$this->title = $title;
		$this->volume = $volume;
		$this->collection_id = $collection_id;
		$this->year = $comicvine_info[4];
		$this->comicvine_series_id = $comicvine_info[8];
		$this->comicvine_series_full = $comicvine_info[9];
		$this->series_id = $this->db->addSeries($this->title, $this->volume, $this->collection_id, $this->year, $this->comicvine_series_id, $this->comicvine_series_full);
		return $this->series_id;
	}	
		
	public function get($series_id){
		$series = $this->db->getSeries($series_id);
		if($series){
			$this->series_id = $series_id;
			if($series['title']){ $this->title = $series['title']; }
			if($series['volume']){ $this->volume = $series['volume']; }
			if($series['collection_id']){ $this->collection_id = $series['collection_id']; }
			if($series['year']){ $this->year = $series['year']; }
			if($series['comicvine_series_id']){ $this->comicvine_series_id = $series['comicvine_series_id']; }
			if($series['comicvine_series_full']){ $this->comicvine_series_full = $series['comicvine_series_full']; }
		}
	}
	
	public static function getAllSeries($db){
		return $db->getAllSeries();
	}
	
	public static function getSeriesByName($db, $title, $volume){
		return $db->getSeriesByName($title, $volume);
	}

	public function __construct($db, $series_id=false){
		$this->db = $db;
		if($series_id !== false){
			$this->get($series_id);
		}
	}
	
	protected $db = false;
	protected $series_id = false;//db series id
	protected $title = false;//my series title, not comicvine's title
	protected $volume = false;//my series volume number, not comicvine's volume
	protected $collection_id = false;//ordering in my physical collection (the id in the collections table)
	protected $year = false;//copyright year
	protected $comicvine_series_id = false;//comicvine series id, ex) Avengers vol.1 is 2128
	protected $comicvine_series_full = false;//comicvine series full id, ex) Avengers vol.1 is 4000-2128
}