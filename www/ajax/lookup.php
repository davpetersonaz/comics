<?php
include_once('../../config.php');
logDebug('ajax/lookup POST: '.var_export($_POST, true));

if(isset($_POST['chrono_index_change'], $_POST['new_chrono_index'])){
	$issue = new Issue($db, $curl, $_POST['chrono_index_change']);
	if($issue->isIssue()){
		$prev = $issue->getChronoIndex();
		$rowsAffected = $issue->changeChronoIndex($_POST['new_chrono_index']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['chrono_index'] = array('prev'=>$prev, 'now'=>$issue->getChronoIndex());
			$changes->addChange(2, $issue->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no issue found';
	}
}

elseif(isset($_POST['collection_change'], $_POST['new_collection_id'])){
	$issue = new Issue($db, $curl, $_POST['collection_change']);
	if($issue->isIssue()){
		$previd = $issue->getCollectionId();
		$prevname = $issue->getCollectionName();
		$rowsAffected = $issue->changeCollection($_POST['new_collection_id']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['collection_id'] = array('prev'=>$previd, 'now'=>$issue->getCollectionId());
			$values['collection_name'] = array('prev'=>$prevname, 'now'=>$issue->getCollectionName());
			$changes->addChange(2, $issue->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no issue found';
	}
}

elseif(isset($_POST['collection_name_change'], $_POST['new_name'])){
	$collection = new Collection($db, $_POST['collection_name_change']);
	if($collection->isCollection()){
		$prev = $collection->getName();
		$rowsAffected = $collection->changeCollectionName($_POST['new_name']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['collection_name'] = array('prev'=>$prev, 'now'=>$issue->getCollectionName());
			$changes->addChange(4, $collection->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no collection found';
	}
}

elseif(isset($_POST['collection_description_change'], $_POST['new_description'])){
	$collection = new Collection($db, $_POST['collection_description_change']);
	if($collection->isCollection()){
		$prev = $collection->getDescription();
		$rowsAffected = $collection->changeDescription($_POST['new_description']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['description'] = array('prev'=>$prev, 'now'=>$collection->getDescription());
			$changes->addChange(4, $collection->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no collection found';
	}
}

//comes from addSeriesSelect, create a new series from the comicvine info
elseif(isset($_POST['comicvine']) && is_array($_POST['comicvine'])){
	$comicvine = $_POST['comicvine'];
	$series = new Series($db, $curl);
	//see if the series already exists
	$exists = $series->doesSeriesExist($comicvine[9]);
	if($exists){
		echo 'series exists already: '.$_POST['seriesname'];
	}else{
		//comicvine, seriesname, collectionid, volume
		$series_id = $series->createSeries($_POST['seriesname'], $_POST['volume'], $comicvine);
		logDebug('series created: '.var_export($series_id, true));
		$values['series_name'] = array('prev'=>'', 'now'=>$series->getName());
		$values['volume'] = array('prev'=>'', 'now'=>$series->getVolume());
		$values['comicvine'] = array('prev'=>'', 'now'=>$comicvine);
		$changes->addChange(3, $series->getId(), $values);
		echo 'done';
	}
}

elseif(isset($_POST['comicvine_issue_id'])){
	$issue_link = Curl::getComivineIssueUrl($_POST['comicvine_issue_id']);
	logDebug('issue-link: '.$issue_link);
	echo $issue_link;
}

//TODO: i dont think REGEN is working
elseif(isset($_POST['comicvine_regen'])){
	if(isset($_POST['series_id']) && $_POST['series_id']){
		$series = new Series($db, $curl, $_POST['series_id']);
		$allseries = array(0=>$series);
	}else{
		$allseries = Series::getAllSeries($db, $curl);
	}
	logDebug('total series: '.count($allseries));
	$rowsAffected = 0;
	foreach($allseries as $series){
		if(	!$series->getYear() ||
			!$series->getPublisher() ||
			!$series->getFirstIssue() ||
			!$series->getLastIssue() ||
			!$series->getSeriesIssueCount() ||
			!$series->getImageThumb() ||
			!$series->getImageFull()
		){ 
			logDebug('series->comicvinefull: '.$series->getComicvineIdFull());
			$curl_series = $curl->getSeriesByComicvineId($series->getComicvineIdFull());
//			logDebug('retrieved series: '.var_export($curl_series, true));
			if($curl_series){
				$values = array(
							'year'=>$curl_series['start_year'], 
							'publisher'=>$curl_series['publisher']['name'], 
							'first_issue'=>$curl_series['first_issue']['issue_number'], 
							'last_issue'=>$curl_series['last_issue']['issue_number'], 
							'series_issue_count'=>$curl_series['count_of_issues'], 
							'image_thumb'=>$curl_series['image']['thumb_url'], 
							'image_full'=>$curl_series['image']['super_url']
				);
				$rowsAffected += $series->updateSeriesValues($values);
			}
		}
		if($rowsAffected > 99){ break; }//dont wanna overload comicvine
	}
	logDebug('total series updated: '.$rowsAffected);
	echo 'done';
}

elseif(isset($_POST['comicvine_series_id'])){
	$series_link = Curl::getComivineSeriesUrl($_POST['comicvine_series_id']);
	logDebug('series-link: '.$series_link);
	echo $series_link;
}

elseif(isset($_POST['delete_collection'])){
	$collection = new Collection($db, $_POST['delete_collection']);
	if($collection->isCollection()){
		$prev = $collection->toArray();
		$rowsAffected = $collection->delete();
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['collection'] = array('prev'=>$prev, 'now'=>'delete');
			$changes->addChange(4, $collection->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no collection found';
	}
}

elseif(isset($_POST['delete_issue'])){
	$issue = new Issue($db, $curl, $_POST['delete_issue']);
	if($issue->isIssue()){
		$prev = $issue->toArray();
		$rowsAffected = $issue->delete();
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['issue'] = array('prev'=>$prev, 'now'=>'delete');
			$changes->addChange(2, $issue->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no issue found';
	}
}

elseif(isset($_POST['delete_series'])){
	$series = new Series($db, $curl, $_POST['delete_series']);
	if($series->isSeries()){
		$prev = $series->toArray();
		$rowsAffected = $series->delete();
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['series'] = array('prev'=>$prev, 'now'=>'deleted');
			$changes->addChange(3, $series->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no series found';
	}
}

elseif(isset($_POST['grade_change'], $_POST['new_grade_id'])){
	$issue = new Issue($db, $curl, $_POST['grade_change']);
//	logDebug('issue: '.var_export($issue, true));
	if($issue->isIssue()){
		$prev = $issue->getGrade();
		$rowsAffected = $issue->changeGrade($_POST['new_grade_id']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['grade'] = array('prev'=>$prev, 'now'=>$issue->getGrade());
			$changes->addChange(2, $issue->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no issue found';
	}
}

elseif(isset($_POST['issue_number_change'], $_POST['new_issue_number'])){
	$issue = new Issue($db, $curl, $_POST['issue_number_change']);
	if($issue->isIssue()){
		$prev = $issue->getIssue();
		$rowsAffected = $issue->changeIssueNumber($_POST['new_issue_number']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['issue_number'] = array('prev'=>$prev, 'now'=>$issue->getIssue());
			$changes->addChange(2, $issue->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no issue found';
	}
}

elseif(isset($_POST['notes_change'], $_POST['new_notes'])){
	$issue = new Issue($db, $curl, $_POST['notes_change']);
//	logDebug('issue: '.var_export($issue, true));
	if($issue->isIssue()){
		$prev = $issue->getNotes();
		$rowsAffected = $issue->changeNotes($_POST['new_notes']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['notes'] = array('prev'=>$prev, 'now'=>$issue->getNotes());
			$changes->addChange(2, $issue->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no issue found';
	}
}

elseif(isset($_POST['series_change'], $_POST['new_series_id'])){
	$issue = new Issue($db, $curl, $_POST['series_change']);
//	logDebug('issue: '.var_export($issue, true));
	if($issue->isIssue()){
		$previd = $issue->getSeriesId();
		$prevname = $issue->getSeriesName();
		$rowsAffected = $issue->changeSeries($_POST['new_series_id']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['series_id'] = array('prev'=>$previd, 'now'=>$issue->getSeriesId());
			$values['series_name'] = array('prev'=>$prevname, 'now'=>$issue->getSeriesName());
			$changes->addChange(2, $issue->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no issue found';
	}
}

elseif(isset($_POST['series_name_change'], $_POST['new_name'])){
	$series = new Series($db, $curl, $_POST['series_name_change']);
	logDebug('series: '.var_export($series, true));
	if($series->isSeries()){
		$prev = $series->getName();
		$rowsAffected = $series->changeSeriesName($_POST['new_name']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['series_name'] = array('prev'=>$prev, 'now'=>$series->getName());
			$changes->addChange(3, $series->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no series found';
	}
}

elseif(isset($_POST['volume_change'], $_POST['new_volume'])){
	$series = new Series($db, $curl, $_POST['volume_change']);
	if($series->isSeries()){
		$prev = $series->getVolume();
		$rowsAffected = $series->changeSeriesVolume($_POST['new_volume']);
		logDebug('rowsAffected: '.$rowsAffected);
		if($rowsAffected === 0){
			echo 'no rows affected';
		}else{
			$values['volume'] = array('prev'=>$prev, 'now'=>$series->getVolume());
			$changes->addChange(3, $series->getId(), $values);
			echo 'done';
		}
	}else{
		echo 'no series found';
	}
}

/*
	stdClass::__set_state(array(
	   'aliases' => 'The Mighty Avengers',
	   'api_detail_url' => 'https://comicvine.gamespot.com/api/volume/4050-2128/',
	   'count_of_issues' => 402,
	   'date_added' => '2008-06-06 11:08:10',
	   'date_last_updated' => '2019-05-29 22:29:07',
	   'deck' => 'Volume 1.',
	   'description' => '<p>The first Avengers ongoing series..., </p>',
	   'first_issue' => 
	  stdClass::__set_state(array(
		 'api_detail_url' => 'https://comicvine.gamespot.com/api/issue/4000-6686/',
		 'id' => 6686,
		 'name' => 'The Coming of the Avengers',
		 'issue_number' => '1',
	  )),
	   'id' => 2128,
	   'image' => 
	  stdClass::__set_state(array(
		 'icon_url' => 'https://comicvine.gamespot.com/api/image/square_avatar/2464633-avengers001.jpg',
		 'medium_url' => 'https://comicvine.gamespot.com/api/image/scale_medium/2464633-avengers001.jpg',
		 'screen_url' => 'https://comicvine.gamespot.com/api/image/screen_medium/2464633-avengers001.jpg',
		 'screen_large_url' => 'https://comicvine.gamespot.com/api/image/screen_kubrick/2464633-avengers001.jpg',
		 'small_url' => 'https://comicvine.gamespot.com/api/image/scale_small/2464633-avengers001.jpg',
		 'super_url' => 'https://comicvine.gamespot.com/api/image/scale_large/2464633-avengers001.jpg',
		 'thumb_url' => 'https://comicvine.gamespot.com/api/image/scale_avatar/2464633-avengers001.jpg',
		 'tiny_url' => 'https://comicvine.gamespot.com/api/image/square_mini/2464633-avengers001.jpg',
		 'original_url' => 'https://comicvine.gamespot.com/api/image/original/2464633-avengers001.jpg',
		 'image_tags' => 'All Images',
	  )),
	   'last_issue' => 
	  stdClass::__set_state(array(
		 'api_detail_url' => 'https://comicvine.gamespot.com/api/issue/4000-42792/',
		 'id' => 42792,
		 'name' => 'End of the Line',
		 'issue_number' => '402',
	  )),
	   'name' => 'The Avengers',
	   'publisher' => 
	  stdClass::__set_state(array(
		 'api_detail_url' => 'https://comicvine.gamespot.com/api/publisher/4010-31/',
		 'id' => 31,
		 'name' => 'Marvel',
	  )),
	   'site_detail_url' => 'https://comicvine.gamespot.com/the-avengers/4050-2128/',
	   'start_year' => '1963',
	)),

*/

exit;