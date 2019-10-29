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
		return $this->db->changeNotes($this->issue_id, $new_notes);
	}

	public function changeSeries($new_series_id){
		$this->series_id = $new_series_id;
		$rowsAffected = $this->db->changeSeriesId($this->issue_id, $new_series_id);
		//reset series-related member vars before calling updateIssueDetails
		$series = new Series($this->db, $this->curl, $new_series_id);
		$this->comicvine_series_id = $series->getComicvineId();
		$this->comicvine_series_full = $series->getComicvineIdFull();
		$this->series_name = $series->getName();
		$this->updateIssueDetails();
		return $rowsAffected;
	}

	public static function createIssue(DB $db, $collection_id, $series_id, $issue, $chrono='', $gradepos=8, $notes=''){
		$lastInsertId = $db->addIssue($series_id, $collection_id, $issue, $chrono, $gradepos, $notes);
		return $lastInsertId;
	}

	public function delete(){
		$rowsAffected = $this->db->deleteIssue($this->issue_id);
		return $rowsAffected;
	}

	public static function deleteIssue($db, $issue_id){
		$rowsAffected = $db->deleteIssue($issue_id);
		return $rowsAffected;
	}

	public static function getAllIssues(DB $db, Curl $curl){
		$issues = array();
		$dbissues = $db->getAllIssueIds();
//		logDebug('dbissues: '.var_export($dbissues, true));
		foreach($dbissues as $dbissueid){
			$issues[] = new Issue($db, $curl, $dbissueid);
		}
		usort($issues, 'Func::compareByObjectName');
		return $issues;
	}

	public static function getAllIssuesInCollection(DB $db, Curl $curl, $collection_id){
		$issues = array();
		$dbissues = $db->getAllIssueIdsForCollection($collection_id);
//		logDebug('dbissues: '.var_export($dbissues, true));
		foreach($dbissues as $dbissueid){
			$issues[] = new Issue($db, $curl, $dbissueid);
		}
		usort($issues, 'Func::compareByObjectName');
		return $issues;
	}

	public static function getAllIssuesInSeries(DB $db, Curl $curl, $series_id){
		$issues = array();
		$dbissues = $db->getAllIssueIdsForSeries($series_id);
//		logDebug('dbissues: '.var_export($dbissues, true));
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
		return "{$this->series_name} ".($this->volume > 1 ? "vol.".$this->volume : '')." ({$this->year}) #{$this->issue}";
	}

	public static function getDisplayTextStatic($name, $volume, $year, $issue){
		return "{$name} ".($volume > 1 ? "vol.".$volume : '')." ({$year}) #{$issue}";
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
		if(count($response) > 0){//just use the first one, some have multiple elements (like 4050-20272)
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
			logDebug('get further details');
			$comicvine_info = $this->curl->getIssueByComicvineId($this->comicvine_issue_id);
			if($comicvine_info){
				//characters
				if($comicvine_info['character_credits']){
					$characters = array_column($comicvine_info['character_credits'], 'name');
					$values['characters'] = $this->characters = implode('|', $characters);
				}
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
				
				//save comicvine issue
				$this->update($this->issue_id, $values);
				$this->get($this->issue_id);
			}else{
				//cannot find issue details on comicvine?? (shouldn't really happen)
				logDebug("issue not found on comicvine");
				logDebug("for series: ".var_export($this->comicvine_series_full, true));
				logDebug('with response: '.var_export($response, true));
				$errormsg = "issue not found on comicvine for series [{$this->comicvine_series_full}], issue [{$this->issue}]: ".var_export($response, true);
				logDebug($errormsg);
				$this->issue_id = false;
			}
		}else{
			//cannot find the issue on comicvine?? (occurs when the issue isn't found in the comicvine series)
			logDebug("no results found on comicvine");
			logDebug("for series: ".var_export($this->comicvine_series_full, true));
			logDebug('with response: '.var_export($response, true));
			$errormsg = "no results found on comicvine for series [{$this->comicvine_series_full}], issue [{$this->issue}]: ".var_export($response, true);
			logDebug($errormsg);
			$this->issue_id = false;
		}
		return $this->issue_id;
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
					if($k === 'issue'){
						$this->$k = Func::normalizeIssueNumber($v);
					}else{
						$this->$k = $v;
					}
				}
			}
		}
	}

	public function isIssue(){
		return ($this->issue_id ? true : false);
	}

	public function getId(){ return $this->issue_id; }
	public function getSeriesId(){ return $this->series_id; }
	public function getCollectionId(){ return $this->collection_id; }
	public function getIssue(){ return $this->issue; }
	public function getChronoIndex(){ return Func::trimFloat($this->chrono_index); }
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
	public function getSeriesName(){ return $this->series_name; }
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
	public $issue = false;//issue number
	public $chrono_index = false;//my issue index of the collection
	public $collection_name = false;//from collections table, the collection's name in my physical collection's ordering
	public $comicvine_issue_id = false;//comicvine issue id (full?)
	public $comicvine_series_id = false;//from series table, short version of comicvine series id
	public $comicvine_series_full = false;//from series table, long version of comicvine series id
	public $comicvine_url = false;//full url to this issue's page on comicvine
	public $cover_date = false;//issue cover date
	public $grade = 8;//grading for the physical copy of the issue //TODO: maybe split this out into separate table and keep issue as a "generic" version of the specific physical copy? what?
	public $notes = false;
	public $image_full = false;//large cover image
	public $image_thumb = false;//cover image thumbnail
	public $issue_title = false;//inside title for this issue
	public $series_name = false;//from series table, my series title, not comicvine's
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
		'Adam Strange',
		'Adam Warlock',
		'Angel',
		'Animal Man',
		'Ant-Man (Lang)',
		'Apocalypse',
		'Aquaman',
		'Aspen Matthews',
		'Aurora',
		'Banshee',
		'Barry Allen',
		'Batman',
		'Batwoman',
		'Beast',
		'Beast Boy',
		'Billy Batson',
		'Bishop',
		'Black Bolt',
		'Black Canary',
		'Black Knight',
		'Black Panther',
		'Black Widow',
		'Bucky Barnes',
		'Bullseye',
		'Cable',
		'Cannonball',
		'Captain America',
		'Captain Britain',
		'Captain Carrot',
		'Captain Marvel',
		'Captain Universe',
		'Carol Danvers',
		'Catwoman',
		'Century',
		'Clea',
		'Cloak',
		'Colossus',
		'Conan',
		'Coyote',
		'Cyborg',
		'Cyclops',
		'Dagger',
		'Daken',
		'Daredevil',
		'Darkhawk',
		'Dazzler',
		'Deadpool',
		'Deathbird',
		'Deathlok',
		'Dick Grayson',
		'Doc Samson',
		'Doctor Doom',
		'Doctor Druid',
		'Doctor Fate (Kent Nelson)',
		'Doctor Octopus',
		'Doctor Strange',
		'Domino',
		'Donna Troy',
		'Dormammu',
		'Drax the Destroyer',
		'Dreadstar',
		'Dream of the Endless (Morpheus)',
		'Elektra',
		'Elongated Man',
		'Emma Frost',
		'Falcon',
		'Firestar',
		'Firestorm',
		'Franklin Richards',
		'Galactus',
		'Gambit',
		'Genis-Vell',
		'Ghost Rider (Blaze)',
		'Ghost Rider (Ketch)',
		'Green Arrow',
		'Hal Jordan',
		'Hank Pym',
		'Havok',
		'Hawkeye',
		'Hawkgirl',
		'Hawkman',
		'Hellcat',
		'Hellstorm',
		'Hercules',
		'Human Torch',
		'Hulk',
		'Hyperion',
		'Iceman',
		'Invisible Woman',
		'Iron Fist',
		'Iron Man',
		'Jack of Hearts',
		'Jean Grey',
		'Jimmy Olsen',
		'Jimmy Woo',
		'Jocasta',
		'John Constantine',
		'Joker',
		'Jubilee',
		'Juggernaut',
		'Kamandi',
		'Kate Bishop',
		'Ka-Zar',
		'Kingpin',
		'Kitty Pryde',
		'Kyle Rayner',
		'Lady Death',
		'Legion',
		'Lex Luthor',
		'Lobo',
		'Lockheed',
		'Lois Lane',
		'Loki',
		'Luke Cage',
		'Machine Man',
		'Madelyne Pryor',
		'Madrox',
		'Magik',
		'Magneto',
		'Mandarin',
		'Man-Thing',
		'Mantis',
		'Martian Manhunter',
		'Meggan',
		'Mephisto',
		'Mockingbird',
		'Mole Man',
		'Moondragon',
		'Moon Knight',
		'Moonstar',
		'Moonstone',
		'Morbius',
		'Mr. Fantastic',
		'Mr. Miracle',
		'Mystique',
		'Namor',
		'Namora',
		'Nick Fury',
		'Nightcrawler',
		'Nighthawk',
		'Night Thrasher',
		'Norman Osborn',
		'Nova',
		'Odin',
		'Omega the Unknown',
		'Penguin',
		'Phantom Stranger',
		'Plastic Man',
		'Polaris',
		'Power Girl',
		'Professor X',
		'Psylocke',
		'Punisher',
		'Purgatori',
		'Quicksilver',
		'Rachel Grey',
		'Rage',
		'Raven',
		'Ray Palmer',
		'Red Skull',
		'Red Tornado',
		'Rick Jones',
		'Rictor',
		'Riddler',
		'Rogue',
		'Sabretooth',
		'Sasquatch',
		'Scarlet Witch',
		'Sentry',
		'Sersi',
		'Shang-Chi',
		'Sharon Carter',
		'She-Hulk',
		'Shi',
		'Silver Surfer',
		'Snowbird',
		'Speedball',
		'Spider-Man',
		'Spider-Woman',
		'Starfire',
		'Starfox',
		'Starhawk',
		'Storm',
		'Strong Guy',
		'Sunfire',
		'Sunspot',
		'Supergirl',
		'Superman',
		'Swamp Thing',
		'Swordsman',
		'Talisman',
		'Thanos',
		'Thing',
		'Thor',
		'Thunderstrike',
		'Tigra',
		'Tim Drake',
		'Two-Face',
		'Ultron',
		'U.S.Agent',
		'Valkyrie',
		'Venom',
		'Venus',
		'Vision',
		'Wally West',
		'Warlock',
		'War Machine',
		'Wasp',
		'Watcher',
		'Witchblade',
		'Wolfsbane',
		'Wolverine',
		'Wonder Man',
		'Wonder Woman',
		'X-Man',
		'Zatanna'
	);
}