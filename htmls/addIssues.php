<?php if(!$alreadyLoggedIn){ ?><script>window.location.href = '/';</script><?php } ?>
<?php


//TODO: default the 'collection' option to the previous choice (not sure i can do, the collection options are determined when the page loads)
//i guess i can call ajax onchange, set the session var, create the collection options again and pass them back, 
//but then i'd have to figure out which are the remaining rows and only change the collection options for them (its possible)

//TODO: INCLUDE A CHECKBOX FOR 'AUTOGRAPHED' (low priority)


logDebug('addIssues: '.var_export($_POST, true));
if(isset($_POST['submit'])){
	$collections = $_POST['collection'];
	$series = $_POST['series'];
	$issues = $_POST['issue'];
	$chrono = $_POST['chrono'];
	$gradepos = $_POST['grade'];
	$notes = $_POST['notes'];
	$thumbs = $images = $failures = array();
	//for error info...
	$collectionNames = Collection::getCollectionsIdName($db);
	$seriesNames = Series::getSeriesIdName($db);

	for($i=0; $i<count($series); $i++){
		if(!$series[$i]){ break; }
		$issue_id = Issue::createIssue($db, $collections[$i], $series[$i], $issues[$i], $chrono[$i], $gradepos[$i], $notes[$i]);
		logDebug('created issue: '.var_export($issue_id, true));
		if($issue_id){
			$issue = new Issue($db, $curl, $issue_id);
			$issue->addSeriesToIssue($series[$i]);
			logDebug('issue is currently: '.var_export($issue, true));
			$issue->updateIssueDetails();
			$images[] = $issue->getImageFull();
			$thumbs[] = $issue->getImageThumb();
		}else{
			$failures[] = "Error creating issue: {$seriesNames[$series[$i]]} #{$issues[$i]}<br />" .
						"comicvine link: https://comicvine.gamespot.com/search/?header=1&q=".urlencode($seriesNames[$series[$i]])."%20%23".urlencode($issues[$i]).'<br />';
		}
	}

	for($i=0; $i<count($images); $i++){
		?>
		<div class='success-cover'>
			<a href='/details?id=<?=$issue->getId()?>' class='small' title="<?=$issue->getDisplayText()?>" target='_blank'>
				<img src='<?=$thumbs[$i]?>' class='img-responsive'>
				<img src='<?=$images[$i]?>' class='large popup-on-hover'>
			</a>
		</div>
	<?php } ?>

	<?php if($failures){ ?>
		<p class='red-text'>
			<b>There was a problem or two...</b><br />
		<?php foreach($failures as $failure){ ?>
			<?=$failure?>
		<?php } ?>
		</p>
	<?php } ?>
		<p class='red-text'>Issues have been added, create some more...</p>
	<?php
}

$fields = 10;
$collections = Collection::getAllCollections($db);
$collection_options = '';
foreach($collections as $collection){
	$collection_options .= "<option value='{$collection->getId()}'>{$collection->getName()} ({$collection->getIssueCount()})</option>";
}
$series = Series::getAllSeries($db);
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
