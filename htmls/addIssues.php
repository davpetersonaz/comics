<?php

//TODO: probably can remove the cover date column, it isnt used to find the issue 
//(it just uses the comicvine series id and the issue number))
//and updateIssueDetails overrides it with comicvine info anyway.

logDebug('addIssues: '.var_export($_POST, true));
if(isset($_POST['submit'])){
	$collections = $_POST['collection'];
	$series = $_POST['series'];
	$issues = $_POST['issue'];
	$chrono = $_POST['chrono'];
	$gradepos = $_POST['grade'];
	
	for($i=0; $i<count($series); $i++){
		logDebug('series[i]: '.var_export($series[$i], true));
		if(!$series[$i]){ break; }
		$comic_id = Issue::createIssue($db, $collections[$i], $series[$i], $issues[$i], $chrono[$i], $gradepos[$i]);
		logDebug('created issue: '.$comic_id);
		$issue = new Issue($db, $curl, $comic_id);
		$issue->addSeriesToIssue($series[$i]);
		logDebug('issue is currently: '.var_export($issue, true));
		$issue->updateIssueDetails();
	}
	?>
		<p class='red-text'>Issues have been added, create some more...</p>
	<?php
}

$fields = 10;
$collections = Collection::getCollections($db);
$collection_options = '';
foreach($collections as $collection){
	$collection_options .= "<option value='{$collection['collection_id']}'>{$collection['collection_name']}</option>";
}
$series = Series::getAllSeries($db);
$series_options = '';
foreach($series as $serie){
	$series_options .= "<option value='{$serie['series_id']}'>{$serie['title']} vol.{$serie['volume']} ({$serie['year']})</option>";
}
$grading = $grades->getAllGrades();
$gradingOptions = array();
foreach($grading as $cond){
	$gradingOptions[] = "<option value='{$cond['position']}' title='{$cond['short_desc']}'>{$cond['name']}</option>";
}

$inputFieldCells =
	"<td>".
		"<select name='collection[]' placeholder='Collection'>".
			"<option value=''></option>{$collection_options}".
		"</select>".
	"</td>".
	"<td>".
		"<select name='series[]' placeholder='Series'>".
			"<option value=''></option>{$series_options}".
		"</select>".
	"</td>".
	"<td>".
		"<input type='text' name='issue[]' placeholder='Issue Number'/>".
	"</td>".
	"<td>".
		"<input type='text' name='chrono[]' placeholder='Chronological Index'/>".
	"</td>".
	"<td>".
		"<select name='grade[]' placeholder='Grading'>".
			"<option value=''></option>";
foreach($gradingOptions as $options){
	$inputFieldCells .= $options;
}
$inputFieldCells .= 
		"</select>".
	"</td>";
?>

<h2>Add Issues</h2>

<form id='addIssuesForm' method='POST' action=''>
	<table id='addIssuesTable'>
		<thead>
			<tr>
				<td>Collection</td><td>Series</td><td>Issue</td><td>Chrono-index</td><td>Grading</td>
			</tr>
		</thead>
		<tbody>
<?php for($i=0; $i<$fields; $i++){ ?>
			<tr>
				<?=$inputFieldCells?>
			</tr>
<?php } ?>
		</tbody>
	</table>
	<div class='action-buttons'>
		<button type='submit' name='submit' class="btn btn-primary bg-dark">Submit</button>
	</div>
</form>
