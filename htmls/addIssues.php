<?php
logDebug('addIssues: '.var_export($_POST, true));
if(isset($_POST['submit'])){
	$collections = $_POST['collection'];
	$series = $_POST['series'];
	$issues = $_POST['issue'];
	$chrono = $_POST['chrono'];
	$gradepos = $_POST['grade'];
	$notes = $_POST['notes'];

	for($i=0; $i<count($series); $i++){
		if(!$series[$i]){ break; }
		$issue_id = Issue::createIssue($db, $collections[$i], $series[$i], $issues[$i], $chrono[$i], $gradepos[$i], $notes[$i]);
		logDebug('created issue: '.$issue_id);
		$issue = new Issue($db, $curl, $issue_id);
		$issue->addSeriesToIssue($series[$i]);
		logDebug('issue is currently: '.var_export($issue, true));
		$issue->updateIssueDetails();
	}
	?>
		<p class='red-text'>Issues have been added, create some more...</p>
	<?php
}

$fields = 8;
$collections = Collection::getAllCollections($db);
$collection_options = '';
foreach($collections as $collection){
	$collection_options .= "<option value='{$collection->getId()}'>{$collection->getName()}</option>";
}
$series = Series::getAllSeries($db);
$series_options = '';
foreach($series as $serie){
	$series_options .= "<option value='{$serie->getId()}'>{$serie->getName()} vol.{$serie->getVolume()} ({$serie->getYear()})</option>";
}
$grading = $grades->getAllGrades();
$gradingOptions = array();
foreach($grading as $cond){
	$gradingOptions[] = "<option value='{$cond['position']}' title='{$cond['long_desc']}'>{$cond['grade_name']}</option>";
}

$inputFieldCells =
	"<td>".
		"<select name='collection[]' class='collection' placeholder='Collection'>".
			"<option value=''></option>{$collection_options}".
		"</select>".
	"</td>".
	"<td>".
		"<select name='series[]' class='series' placeholder='Series'>".
			"<option value=''></option>{$series_options}".
		"</select>".
	"</td>".
	"<td>".
		"<input type='text' name='issue[]' class='issue' placeholder='Issue Number'/>".
	"</td>".
	"<td>".
		"<input type='text' name='chrono[]' class='chrono' placeholder='Chronological Index'/>".
	"</td>".
	"<td>".
		"<select name='grade[]' class='grade' placeholder='Grading'>".
			"<option value=''></option>";
foreach($gradingOptions as $options){
	$inputFieldCells .= $options;
}
$inputFieldCells .= 
		"</select>".
	"</td>".
	"<td>".
		"<input type='text' name='notes[]' class='notes' placeholder='Notes'/>".
	"</td>";
?>

<h2>Add Issues</h2>

<form id='addIssuesForm' method='POST' action=''>
	<table id='addIssuesTable'>
		<thead>
			<tr>
				<td>Collection</td><td>Series</td><td>Issue</td><td>Chrono-index</td><td>Grading</td><td>Notes</td>
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
