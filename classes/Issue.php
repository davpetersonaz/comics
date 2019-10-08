<?php

class Issue{

	private function accentMainCharacters($characters){
		foreach($characters as &$character){
			if(in_array($character, $this->mainCharacters)){
				$character = "<b>{$character}</b>";
			}
		}
		return $characters;
	}

	public function addSeriesToIssue($series_id){
		$rowsAffected = $this->db->updateIssue($this->issue_id, array('series_id'=>$series_id));
//		logDebug('updated issue, rowsAffected: '.$rowsAffected);
		return $rowsAffected;
	}

	public function changeChronoIndex($new_chrono_index){
		$this->chrono_index = $new_chrono_index;
		return $this->db->changeChronoIndex($this->issue_id, $new_chrono_index);
	}

	public function changeCollection($new_collection_id){
		$this->collection_id = $new_collection_id;
		return $this->db->changeCollectionId($this->issue_id, $new_collection_id);
	}

	public function changeGrade($new_grade_id){
		$this->grade = $new_grade_id;
		return $this->db->changeGradeId($this->issue_id, $new_grade_id);
	}

	public function changeIssueNumber($new_issue_number){
		$this->issue = $new_issue_number;
		$rowsAffected = $this->db->changeIssueNumber($this->issue_id, $new_issue_number);
		$this->updateIssueDetails();
		return $rowsAffected;
	}

	public function changeNotes($new_notes){
		$this->notes = $new_notes;
		return $this->db->changeGradeId($this->issue_id, $new_notes);
	}

	public function changeSeries($new_series_id){
		$this->series_id = $new_series_id;
		return $this->db->changeSeriesId($this->issue_id, $new_series_id);
	}

	//unsure what to default the grade-position to, just using "no grade" for now
	public static function createIssue(DB $db, $collection_id, $series_id, $issue, $chrono='', $gradepos=8, $notes=''){
		$lastInsertId = $db->addIssue($series_id, $collection_id, $issue, $chrono, $gradepos, $notes);
		return $lastInsertId;
	}

	public function delete(){
		$rowsAffected = $this->db->deleteIssue($this->issue_id);
		return $rowsAffected;
	}

	public static function getAllIssues(DB $db, Curl $curl){
		$issues = array();
		$dbissues = $db->getAllIssueIds();
		logDebug('dbissues: '.var_export($dbissues, true));
		foreach($dbissues as $dbissueid){
			$issues[] = new Issue($db, $curl, $dbissueid);
		}
		usort($issues, 'Func::compareByObjectName');
		return $issues;
	}

	public static function getAllIssuesInCollection(DB $db, Curl $curl, $collection_id){
		$issues = array();
		$dbissues = $db->getAllIssueIdsForCollection($collection_id);
		logDebug('dbissues: '.var_export($dbissues, true));
		foreach($dbissues as $dbissueid){
			$issues[] = new Issue($db, $curl, $dbissueid);
		}
		usort($issues, 'Func::compareByObjectName');
		return $issues;
	}

	public static function getAllIssuesInSeries(DB $db, Curl $curl, $series_id){
		$issues = array();
		$dbissues = $db->getAllIssueIdsForSeries($series_id);
		logDebug('dbissues: '.var_export($dbissues, true));
		foreach($dbissues as $dbissueid){
			$issues[] = new Issue($db, $curl, $dbissueid);
		}
		usort($issues, 'Func::compareByObjectName');
		return $issues;
	}

	public function getCharactersArray(){
		$characters = array();
		if(!empty(trim($this->characters))){
			$characters = explode('|', $this->characters);
		}
		$characters = $this->accentMainCharacters($characters);
		return $characters;
	}

	public function getCharactersDiedArray(){
		$characters = array();
		if(!empty(trim($this->character_died_in))){
			$characters = explode('|', $this->character_died_in);
		}
		return $characters;
	}

	public function getCreatorsArray(){
		$creatorsArray = array();
		if(!empty(trim($this->creators))){
			$creators = explode('|', $this->creators);
			foreach($creators as $creatorposition){
				$creatorPositionArray = explode(':', $creatorposition);
				$creatorsArray[$creatorPositionArray[0]] = (isset($creatorPositionArray[1]) ? $creatorPositionArray[1] : '');
			}
		}
		$creatorsArray = $this->sortCreatorsByJob($creatorsArray);
		return $creatorsArray;
	}

	//can be used for dropdown options, title tooltips
	public function getDisplayText(){
		return "{$this->name} vol.{$this->volume} #{$this->issue}";
	}

	public function getFirstAppearanceCharactersArray(){
		$characters = array();
		if(!empty(trim($this->first_appearance_characters))){
			$characters = explode('|', $this->first_appearance_characters);
		}
		return $characters;
	}

	public function getFirstAppearanceObjectsArray(){
		$objects = array();
		if(!empty(trim($this->first_appearance_objects))){
			$objects = explode('|', $this->first_appearance_objects);
		}
		return $objects;
	}

	public function getFirstAppearanceTeamsArray(){
		$teams = array();
		if(!empty(trim($this->first_appearance_teams))){
			$teams = explode('|', $this->first_appearance_teams);
		}
		return $teams;
	}

	public function getIssueDetails($issue_id){
		$issue = $this->db->getIssueDetails($issue_id);
		return $issue;
	}

	//TODO: this will go away
//	public function getIssuesThatMatch($comicvine){
//		$issues = $this->db->getIssuesThatMatch($this->name, $comicvine[4], $comicvine[6], $comicvine[7]);
//		return $issues;
//	}

	private function sortCreatorsByJob($creators=array()){
		$remainingCreators = $creators;
		$tasks = array('writer', 'penciler', 'artist', 'inker', 'colorist', 'letterer', 'editor');
		$sortedCreators = array();
		foreach($tasks as $task){
			foreach($remainingCreators as $creator=>$jobs){
				if(strpos($jobs, $task) !== false){
					$sortedCreators[$creator] = $jobs;
					unset($remainingCreators[$creator]);
				}
			}
		}
		if(!empty($remainingCreators)){
			foreach($remainingCreators as $creator=>$jobs){
				$remainingCreators[$creator] = $jobs;
			}			
		}
		return $sortedCreators;
	}

	public function update($issue_id, $values){
		$rowsAffected = $this->db->updateIssue($issue_id, $values);
		return $rowsAffected;
	}

	public function updateIssueDetails(){
		logDebug('get basic details');
		$response = $this->curl->getIssuesBySeriesAndIssue($this->comicvine_series_id, $this->issue);
		if(count($response) === 1){//just one result, should happen most of the time
			$comicvine_info = $response[0];
			logDebug('found it: '.var_export($comicvine_info, true));
			$this->comicvine_issue_id = '';
			if(preg_match('/^https:\/\/comicvine.gamespot.com\/.+\/(\d+-\d+)\/$/', $comicvine_info['site_detail_url'], $matches) === 1 && isset($matches[1])){
				$this->comicvine_issue_id = $matches[1];
			}

			//the basic details
			$values = array();
			$values['comicvine_issue_id'] = $this->comicvine_issue_id;
			$values['comicvine_url'] = $this->comicvine_url = $comicvine_info['site_detail_url'];
//			$version = $comicvine_info['version'];//TODO: what the eff is this? is this the VOLUME? Avengers #1 is '1.0', but i think its just the API version number.
			$values['cover_date'] = $this->cover_date = $comicvine_info['cover_date'];
			$values['issue_title'] = $this->issue_title = $comicvine_info['name'];
			$values['synopsis'] = $this->synopsis = $comicvine_info['description'];
			$values['image_full'] = $this->image_full = $comicvine_info['image']['super_url'];
			$values['image_thumb'] = $this->image_thumb = $comicvine_info['image']['thumb_url'];

			//get further details
			if($this->comicvine_issue_id){
				logDebug('get further details');
				$comicvine_info = $this->curl->getIssueByComicvineId($this->comicvine_issue_id);
				if($comicvine_info){
					//characters
					if($comicvine_info['character_credits']){
						$characters = array_column($comicvine_info['character_credits'], 'name');
						$values['characters'] = $this->characters = implode('|', $characters);
					}
					logDebug('this->characters: '.var_export($this->characters, true));
					//creators
					$creators = array();
					if($comicvine_info['person_credits']){
						foreach($comicvine_info['person_credits'] as $creator){
							$creator_entry = "{$creator['name']}:{$creator['role']}";
							$creators[] = $creator_entry;
						}
					}
					$values['creators'] = $this->creators = implode('|', $creators);
					//first appearances of characters
					if($comicvine_info['first_appearance_characters']){
						$first_appearance_characters = array_column($comicvine_info['first_appearance_characters'], 'name');
						$values['first_appearance_characters'] = $this->first_appearance_characters = implode('|', $first_appearance_characters);
					}
					//first appearances of objects
					if($comicvine_info['first_appearance_objects']){
						$first_appearance_objects = array_column($comicvine_info['first_appearance_objects'], 'name');
						$values['first_appearance_objects'] = $this->first_appearance_objects = implode('|', $first_appearance_objects);
					}
					//first appearances of teams
					if($comicvine_info['first_appearance_teams']){
						$first_appearance_teams = array_column($comicvine_info['first_appearance_teams'], 'name');
						$values['first_appearance_teams'] = $this->first_appearance_teams = implode('|', $first_appearance_teams);
					}
					//character died
					if($comicvine_info['character_died_in']){
						$character_died_in = array_column($comicvine_info['character_died_in'], 'name');
						$values['character_died_in'] = $this->character_died_in = implode('|', $character_died_in);
					}
				}else{
					logDebug("COULD NOT FIND [$this->comicvine_issue_id{}]");
				}
			}

			//save comicvine issue
			$this->update($this->issue_id, $values);
		}elseif(count($response) > 1){
			//TODO: not sure if this all is necessary, probably just output error message instead???
			$errormsg = "more than 1 result for series [{$this->comicvine_series_full}] issue [{$this->issue_number}]";
			echo "<p>{$errormsg}</p>";
			echo "<pre>". var_export($response, true)."</pre>";
			logDebug($errormsg);
			exit;
			//or, i could just accept the first result as the most obvious,
			//or, i could throw up a list so i could choose the proper issue...
	//		$returnArray = array();
	//		foreach($decodedResponse['results'] as $result){
	//			$row = array();
	//			//display: cover_date, id (xxxx), icon: https://comicvine.gamespot.com/api/image/square_avatar/2464633-avengers001.jpg, 
	//			$row[] = "<div class='picture'>".
	//								"<a class='small' href='#nogo' title='small image'>".
	//									"<img src='{$result['image']['thumb_url']}' class='img-responsive'>".
	//									"<img class='large' src='{$result['image']['super_url']}'>".
	//								"</a>".
	//							"</div>";
	//			$row[] = $result['cover_date'];
	//			$row[] = $result['id'];
	//			$returnArray[] = $row;
	//		}Grade
	//		echo json_encode($returnArray, JSON_UNESCAPED_SLASHES);
	//		exit;
		}else{
			//cannot find on comicvine??
			logDebug("no results found on comicvine");
			logDebug("for series: ".var_export($this->comicvine_series_full, true));
			logDebug('with response: '.var_export($response, true));
			$errormsg = "no results found on comicvine for series [{$this->comicvine_series_full}]: ".var_export($response, true);
			echo "<p>{$errormsg}</p>";
			logDebug($errormsg);
			exit;
		}
	}

	////////////////////////////////////////////////////////////////

//	function __toString() {
//		return "Issue: {$name} vol.{$series_volume} #{$issue} {$cover_date}";
//	}

	public function get($issue_id){
		$details = $this->db->getIssueDetails($issue_id);
		if($details){
			foreach($details as $k=>$v){
				if(!is_null($v)){
					$this->$k = $v;
				}
			}
		}
	}

	public function isIssue(){
		return ($this->issue_id ? true : false);
	}

	public function getId(){ return $this->issue_id; }
	public function getName(){ return $this->name; }
	public function getSeriesId(){ return $this->series_id; }
	public function getCollectionId(){ return $this->collection_id; }
	public function getIssue(){ return $this->issue; }
	public function getChronoIndex(){ return $this->chrono_index; }
	public function getCollectionName(){ return $this->collection_name; }
	public function getComicvineIssueId(){ return $this->comicvine_issue_id; }
	public function getComicvineSeriesId(){ return $this->comicvine_series_id; }
	public function getComicvineSeriesFull(){ return $this->comicvine_series_full; }
	public function getComicvineUrl(){ return $this->comicvine_url; }
	public function getCoverDate(){ return $this->cover_date; }
	public function getGrade(){ return $this->grade; }
	public function getNotes(){ return $this->notes; }
	public function getImageFull(){ return $this->image_full; }
	public function getImageThumb(){ return $this->image_thumb; }
	public function getIssueTitle(){ return $this->issue_title; }
	public function getSeriesTitle(){ return $this->series_title; }
	public function getVolume(){ return $this->volume; }
	public function getYear(){ return $this->year; }
	public function getSynopsis(){ return $this->synopsis; }
	public function getCharacters(){ return $this->characters; }
	public function getCreators(){ return $this->creators; }
	public function getCharactersDiedIn(){ return $this->character_died_in; }
	public function getFirstAppearanceCharacters(){ return $this->first_appearance_characters; }
	public function getFirstAppearanceObjects(){ return $this->first_appearance_objects; }
	public function getFirstAppearanceTeams(){ return $this->first_appearance_teams; }

	public function __construct(DB $db, Curl $curl, $issue_id=false){
		$this->db = $db;
		$this->curl = $curl;
		if($issue_id){
			$this->get($issue_id);
		}
	}

	protected $db = false;
	protected $curl = false;
	public $issue_id = false;//db issue id
	public $series_id = false;//db series id
	public $collection_id = false;//db colelction id
	public $name = false;//my series name, not comicvine's
	public $issue = false;//issue number
	public $chrono_index = false;//my issue index of the collection
	public $collection_name = false;//from collections table, the collection's name in my physical collection's ordering
	public $comicvine_issue_id = false;//comicvine issue id (full?)
	public $comicvine_series_id = false;//from series table, short version of comicvine series id
	public $comicvine_series_full = false;//from series table, long version of comicvine series id
	public $comicvine_url = false;//full url to this issue's page on comicvine
	public $cover_date = false;//issue cover date
	public $grade = 8;//grading for the physical copy of the issue //TODO: maybe split this out into separate table and keep issue as a "generic" version of the specific physical copy?
	public $notes = false;
	public $image_full = false;//large cover image
	public $image_thumb = false;//cover image thumbnail
	public $issue_title = false;//inside title for this issue
	public $series_title = false;//from series table, my series title, not comicvine's
	public $volume = false;//from series table, my series volume number, not comicvine's -- TODO: maybe should be $series_volume??
	public $year = false;//from series table, the copyright year of the first issue
	public $synopsis = false;//description of the issue's plot
	//arrays are stored as they are in the database: as '|'-separated strings
	public $characters = '';//characters in the issue
	public $creators = '';//creator strings in "name:position" format
	public $character_died_in = '';
	public $first_appearance_characters = '';
	public $first_appearance_objects = '';
	public $first_appearance_teams = '';

	public $mainCharacters = array(
		'Adam Warlock',
		'Batman',
		'Beast',
		'Black Bolt',
		'Black Panther',
		'Black Widow',
		'Bucky Barnes',
		'Cannonball',
		'Captain America',
		'Carol Danvers',
		'Clea',
		'Doctor Strange',
		'Dream of the Endless (Morpheus)',
		'Falcon',
		'Hal Jordan',
		'Hank Pym',
		'Hawkeye',
		'Human Torch',
		'Hulk',
		'Invisible Woman',
		'Iron Fist',
		'Iron Man',
		'John Constantine',
		'Luke Cage',
		'Mockingbird',
		'Mr. Fantastic',
		'Namor',
		'Quicksilver',
		'Scarlet Witch',
		'Shang-Chi',
		'Spider-Man',
		'Spider-Woman',
		'Sunspot',
		'Thing',
		'Thor',
		'Vision',
		'War Machine',
		'Wasp',
		'Wolverine',
		'Wonder Man'
	);
}