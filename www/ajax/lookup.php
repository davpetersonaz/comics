<?php
include_once('../../config.php');
logDebug('ajax/lookup POST: '.var_export($_POST, true));

//TODO: this aint necessary, no more selectseries
//retrieve issue details, but make sure it has comicvine info first, or else we'll have to ask the user to pick the right series
//if(isset($_POST['getIssuesDetails'])){
//	$dbIssue = new Issue($db, $curl, $_POST['getIssuesDetails']);
//	logDebug('getIssueDetails result: '.var_export($dbIssue, true));
//	if(!$dbIssue->exists()){
//		//TODO: details not found!!??!!
//		echo 'not found';
//	}elseif(!$dbIssue->comicvine_series_id && $alreadyLoggedIn){//only admins can do this
//		logDebug('comicvine_series_id is false, find possibilities on comicvine');
//		//query comicvine to get a list of possibilities, based on name, issue number and year
//		$results = $curl->getSeriesByNameAndIssue($dbIssue->name, $dbIssue->issue);
//		logDebug('selectseries results: '.count($results));
//		$returnArray = array();
//		foreach($results as $result){
//			$newArray = array();
//			$newArray[] = "<div class='picture'>".
//								"<a class='small' href='#nogo' title='small image'>".
//									"<img src='{$result['image']['thumb_url']}' class='img-responsive'>".
//									"<img class='large' src='{$result['image']['super_url']}'>".
//								"</a>".
//							"</div>";
//			$newArray[] = $result['name'];
//			$newArray[] = $result['deck'];//'deck' => 'Volume 1.'
//			$newArray[] = $result['publisher']['name'];
//			$newArray[] = $result['start_year'];
//			$newArray[] = "{$result['count_of_issues']}";
//			$newArray[] = $result['first_issue']['issue_number'];
//			$newArray[] = $result['last_issue']['issue_number'];
//			$newArray[] = "{$result['id']}";//comicvine series short-id (xxxx)
//			$newArray[] = (preg_match('/^.*\/(\d+-\d+)\/$/', $result['site_detail_url'], $matches) === 1 && isset($matches[1]) ? "{$matches[1]}" : '');//comicvine series full-id (xxxx-xxxx)
////			logDebug('row: '.implode(', ', $newArray));
//			$returnArray[] = $newArray;
//			//return the list to selectseries 
//		}
//		echo json_encode($returnArray, JSON_UNESCAPED_SLASHES);
//	}else{
//		//just return so we can display the issue details
//		echo 'done';
//	}
//}

//comes from addSeriesSelect, create a new series from the comicvine info
if(isset($_POST['comicvine']) && is_array($_POST['comicvine'])){
	$comicvine = $_POST['comicvine'];
//	$issue_id = (isset($_POST['issueid']) ? $_POST['issueid'] : false);//will be false if coming from addSeriesSelect
	unset($comicvine['comic_id']);//TODO: i don't think this is necessary
	$series = new Series($db);//for later
	
//	if($issue_id){//coming from selectSeries, so we have the issue id
//		//comicvine, seriesid, collectionid, volume, issueid, issuenumber
//		$dbIssue = new Issue($db, $curl, $issue_id);
//		logDebug('dbIssue: '.var_export($dbIssue, true));
//
//		//save the comicvine info as a series if it doesn't exist
//		if(!$dbIssue->series_id){
//			logDebug('seriesid is false, save comicvine info as a series it it doesnt exist');
//			$series_id = $series->createSeries($dbIssue->name, $dbIssue->volume, $_POST['collectionid'], $comicvine);
//			logDebug('series created: '.var_export($series_id, true));
//			
//			//add the seriesid to the issue -- not going to use comicvine series name or volume in the comics table (volume is like this: "Volume 1.", and series-name like: "The Avengers")
//			$success = $dbIssue->addSeriesToIssue($series_id);
//			logDebug('addSeriesToIssue result: '.var_export($success, true));
//
//			$matchingIssues = $dbIssue->getIssuesThatMatch($comicvine);
//			logDebug('found matching: '.count($matchingIssues));
//			$rowsAffected = 0;
//			foreach($matchingIssues as $matchingIssue){
//				$temp = new Issue($db, $curl, $matchingIssue['comic_id']);
//				$rowsAffected += $temp->addSeriesToIssue($series_id);
//			}
//			logDebug('issues seriesid updated in: '.$rowsAffected);
//		}
//
//		echo $issue_id;
//	}else{//coming from addSeriesSelect, so no specific issue, just create the series
		//comicvine, seriesname, collectionid, volume
		$series_id = $series->createSeries($_POST['seriesname'], $_POST['volume'], $_POST['collectionid'], $comicvine);
		logDebug('series created: '.var_export($series_id, true));
		echo $series_id;
//	}
}

elseif(isset($_POST['collection_change'], $_POST['new_name'])){
	$collection_id = ltrim($_POST['collection_change'], 'collection');
	$rowsAffected = Collection::changeCollectionName($db, $collection_id, $_POST['new_name']);
	logDebug('rowsAffected: '.$rowsAffected);
	echo 'done';
}

elseif(isset($_POST['series_change'], $_POST['new_name'])){
	$series_id = ltrim($_POST['series_change'], 'series');
	$rowsAffected = Series::changeSeriesName($db, $series_id, $_POST['new_name']);
	logDebug('rowsAffected: '.$rowsAffected);
	echo 'done';
}

exit;

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