<?php 
if(!$alreadyLoggedIn){ ?><script>window.location.href = '/';</script><?php }

//handle submit
//logDebug('addIssues: '.var_export($_POST, true));
if(isset($_POST['submit'])){
	$collections = $_POST['collection'];
	$series = $_POST['series'];
	$issues = $_POST['issue'];
	$chrono = $_POST['chrono'];
	$gradepos = $_POST['grade'];
	$notes = $_POST['notes'];
	$failures = array();
	//for error info...
	$collectionNames = Collection::getCollectionsIdName($db);
	$seriesNames = Series::getSeriesIdName($db);

	for($i=0; $i<count($series); $i++){
		if(!$series[$i]){ break; }
		$issue_id = Issue::createIssue($db, $collections[$i], $series[$i], $issues[$i], $chrono[$i], $gradepos[$i], $notes[$i]);
		logDebug('created issue: '.var_export($issue_id, true));
		$link = "https://comicvine.gamespot.com/search/?header=1&q=".urlencode($seriesNames[$series[$i]])."%20%23".urlencode($issues[$i]);
		if($issue_id){
			$issue = new Issue($db, $curl, $issue_id);
			$issue->addSeriesToIssue($series[$i]);
			$result = $issue->updateIssueDetails();
			logDebug('issue details updated: '.var_export($result, true));
			if($result){
				$values['issue'] = array('prev'=>'', 'now'=>$issue->toArray());
				$changes->addChange(2, $issue->getId(), $values);
				?>
				<div class='success-cover'>
					<a href='/details?id=<?=$issue->getId()?>' class='small' title="<?=$issue->getDisplayText()?>" target='_blank'>
						<img src='<?=$issue->getImageThumb()?>' class='img-responsive'>
						<img src='<?=$issue->getImageFull()?>' class='large popup-on-hover'>
					</a>
				</div>
				<?php
			}else{
				$failures[] = "Issue not found on ComicVine: {$seriesNames[$series[$i]]} #{$issues[$i]}, <a href='{$link}' target='_blank'>comicvine link</a><br />";
				$rowsAffected = Issue::deleteIssue($db, $issue_id);
				logDebug("issue [{$issue_id}] deleted: ".$rowsAffected);
			}
		}else{
			$failures[] = "Error creating issue in db: {$seriesNames[$series[$i]]} #{$issues[$i]}, <a href='{$link}' target='_blank'>comicvine link</a><br />";
		}
	}
	?>

	<?php if(count(array_filter($series)) - count($failures) > 0){ //array_filter removes empty elements ?>
		<p class='red-text'>Issues have been added<?php if(count($failures) === 0){ ?>, create some more..<?php } ?>.</p>
	<?php } ?>
	<?php if(count($failures) > 0){ ?>
		<p class='red-text'>
			<b>There was a problem<?php if(count($failures) > 1){ ?> or two<?php } ?>...</b><br />
		<?php foreach($failures as $failure){ ?>
			<?=$failure?><br />
		<?php } ?>
		</p>
	<?php }
}

$fields = 10;
$collections = Collection::getAllCollections($db);
$collection_options = '';
foreach($collections as $collection){
	$collection_options .= "<option value='{$collection->getId()}'>{$collection->getName()} ({$collection->getIssueCount()})</option>";
}
$series = Series::getAllSeries($db, $curl);
$series_options = '';
foreach($series as $serie){
	$series_options .= "<option value='{$serie->getId()}'>{$serie->getDisplayText()} ({$serie->getIssueCount()} issue".($serie->getIssueCount()===1?'':'s').")</option>";
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
		"<input type='text' name='issue[]' class='issue' placeholder='Issue'/>".
	"</td>".
	"<td>".
		"<input type='text' name='chrono[]' class='chrono' placeholder='Chrono'/>".
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

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-series'>Add Series</button>
</div>

<h2 class='add-header'>Add Issues</h2>

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

<script>
$(document).ready(function(){

	//focus on first input
	$('form:first *:input[type!=hidden]:first').focus();	

	//prevent enter button in form text fields
	window.addEventListener('keydown', function(e){
		if(e.keyIdentifier === 'U+000A' || e.keyIdentifier === 'Enter' || e.keyCode === 13){
			if(e.target.nodeName === 'INPUT' && e.target.type === 'text'){
				e.preventDefault();
				return false;
			}
		}
	}, true);

	$("form :input").change(function(){
		$("#addIssuesForm").data("changed", true);
	});

	$('.add-series').on('click', function(){
		var changed = $("#addIssuesForm").data("changed");
		if(!changed || (changed && confirm('Are you sure you want to add a new series? All form data will be lost.'))){
			window.location.href = '/addSeries';
		}
	});

	$(".popup-on-hover").hover(function(){
		console.warn('hover', this);
		$(this).find('.large').show();
	});

});
</script>
