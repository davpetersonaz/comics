<?php 
logDebug('issues GET: '.var_export($_GET, true));
$pageLength = (isset($_SESSION['table_length']['home']) && $_SESSION['table_length']['home'] > 0 ? $_SESSION['table_length']['home'] : 25);

//TODO: i could add another option -- select by both collection and series, but i'd have to add a submit button though.
$collectionChoice = (isset($_GET['coll']) ? intval($_GET['coll']) : false);
$seriesChoice = (isset($_GET['ser']) ? intval($_GET['ser']) : false);
if($collectionChoice){
	$issues = Issue::getAllIssuesInCollection($db, $curl, $collectionChoice);
}elseif($seriesChoice){
	$issues = Issue::getAllIssuesInSeries($db, $curl, $seriesChoice);
}else{
	$issues = Issue::getAllIssues($db, $curl);
}
$collections = Collection::getAllCollections($db);
$series = Series::getAllSeries($db);
//logDebug('grades->getAllGrades(): '.var_export($grades->getAllGrades(), true));
?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-issues'>Add Issues</button>
</div>

<div class='btn-above-table' style='float:left;'>
	filter by collection:<br />
	<select id='issues-by-collection'>
		<option value=''></option>
<?php foreach($collections as $collection){ ?>
	<?php $selected = ($collectionChoice && intval($collectionChoice) === intval($collection->getId()) ? 'selected' : ''); ?>
		<option value='<?=$collection->getId()?>' <?=$selected?>><?=$collection->getName()?></option>
<?php } ?>
	</select>
</div>

<div class='btn-above-table' style='float:left;margin-left:2em;'>
	filter by series:<br />
	<select id='issues-by-series'>
		<option value=''></option>
<?php foreach($series as $serie){ ?>
	<?php $selected = ($seriesChoice && intval($seriesChoice) === intval($serie->getId()) ? 'selected' : ''); ?>
		<option value='<?=$serie->getId()?>' <?=$selected?>><?=$serie->getDisplayText()?></option>
<?php } ?>
	</select>
</div>

<table id='issuesTable' class="display">
	<thead>
		<tr>
			<th> </th>
			<th>collection</th>
			<th>series</th>
			<th>issue</th>
			<th>chrono</th>
			<th>cover date</th>
			<th>grade</th>
			<th>comicvine (full)</th>
			<th>notes</th>
			<th> </th>
		</tr>
	</thead>
	<tbody>

<?php 
logDebug('issues size: '.count($issues));
foreach($issues as $issue){
	logDebug('processing issue');
	$image_div = '';
	if($issue->image_thumb && $issue->getImageFull()){
		$image_div =	
					"<div id='picture{$issue->getId()}' class='picture'>".
						"<a href='#nogo' class='small' title='{$issue->getDisplayText()}'>".
							"<img class='img-responsive' src='{$issue->getImageThumb()}'>".
							"<img class='large popup-on-hover' src='{$issue->getImageFull()}'>".
						"</a>".
					"</div>";
	}
	logDebug('image_div: '.$image_div);
	$collection_div = "<select id='collection{$issue->getId()}' class='collection' name='collection[]'>";
	foreach($collections as $collection){
		$selected = (intval($collection->getId()) === intval($issue->getCollectionId()) ? ' selected' : '');
		$collection_div .= "<option value='{$collection->getId()}' {$selected}>{$collection->getName()}</option>";
	}
	$collection_div .= "</select>";
//	logDebug('collection_div: '.$collection_div);
	$series_div = "<select id='series{$issue->getId()}' class='series' name='series[]'>";
	foreach($series as $serie){
		$selected = (intval($serie->getId()) === intval($issue->getSeriesId()) ? ' selected' : '');
		$series_div .= "<option value='{$serie->getId()}' {$selected}>{$serie->getName()} vol.{$serie->getVolume()} ({$serie->getYear()})</option>";
	}
	$series_div .= "</select>";
//	logDebug('series_div: '.$series_div);
	$issue_div = "<input type='text' name='issue[]' class='issue' id='issue{$issue->getId()}' value='{$issue->getIssue()}'/>";
//	logDebug('issue_div: '.$issue_div);
	$chrono_div = "<input type='text' name='chrono[]' class='chrono' id='chrono{$issue->getId()}' value='{$issue->getChronoIndex()}'/>";
//	logDebug('chrono_div: '.$chrono_div);
	$coverdate = new DateTime("{$issue->getCoverDate()}");
	$cover_div = ($coverdate->format('m') === '01' ? $coverdate->format('M Y') : $coverdate->format('M j, Y'));
//	logDebug('cover_div: '.$cover_div);
	$grade_div = "<select id='grade{$issue->getId()}' class='grade' name='grade[]'>";
	foreach($grades->getAllGrades() as $grade_array){
//		logDebug('grade_array: '.var_export($grade_array, true));
		$selected = (intval($grade_array['position']) === intval($issue->getGrade()) ? ' selected' : '');
		$grade_div .= "<option value='{$grade_array['position']}' title='{$grade_array['short_desc']}' {$selected}>{$grade_array['grade_name']}</option>";
	}
	$grade_div .= "</select>";
	$comicvine_issue_id_div = "<span id='comicvine{$issue->getId()}' class='comicvine-link' data-comicvine-issue-id='{$issue->getComicvineIssueId()}'>{$issue->getComicvineIssueId()}</span>";
	$notes_div = "<input type='text' name='notes[]' class='notes' id='notes{$issue->getId()}' value='{$issue->getNotes()}'/>";
	$delete_div = "<span class='delete' id='delete{$issue->getId()}' data-issue-text='{$issue->getDisplayText()}'><i class='fa fa-times'></i></span>";
	?>
		<tr>
			<td><?=$image_div?></td>
			<td><?=$collection_div?></td>
			<td><?=$series_div?></td>
			<td><?=$issue_div?></td>
			<td><?=$chrono_div?></td>
			<td><?=$cover_div?></td>
			<td><?=$grade_div?></td>
			<td><?=$comicvine_issue_id_div?></td>
			<td><?=$notes_div?></td>
			<td><?=$delete_div?></td>
		</tr>
	<?php logDebug("finished processing issue: {$issue->getCollectionName()} / {$issue->getIssueTitle()} / {$issue->getVolume()} / {$issue->getIssue()}"); ?>
<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<th> </th>
			<th>collection</th>
			<th>series</th>
			<th>issue</th>
			<th>chrono</th>
			<th>cover date</th>
			<th>grade</th>
			<th>comicvine (full)</th>
			<th>notes</th>
			<th> </th>
		</tr>
	</tfoot>
</table>

<script>
$(document).ready(function(){

	//this creates an array of values for string input boxes
	$.fn.dataTable.ext.order['dom-text'] = function(settings, col){
		return this.api().column( col, {order:'index'} ).nodes().map( function(td, i){
			return $('input', td).val();
		});
	};

	//this creates an array of values for numeric input boxes, parsed as numbers
	$.fn.dataTable.ext.order['dom-text-numeric'] = function (settings, col){
		return this.api().column( col, {order:'index'} ).nodes().map( function(td, i){
			return $('input', td).val() * 1;
		});
	};

	//this creates an array of values for select options
	$.fn.dataTable.ext.order['dom-select'] = function(settings, col){
		return this.api().column( col, {order:'index'} ).nodes().map( function(td, i){
			return $('select', td).val();
		});
	};

	$('#issuesTable').dataTable({
		//how shall i sort this? 
		//by collection/coverdate/grade? by collection/chrono/grade? by collection/series/issue/grade?
		"order": [[ 1, 'asc' ],[ 2, 'asc' ],[ 3, 'asc' ],[ 6, 'asc' ]],
		"pageLength": <?=$pageLength?>,
		"columnDefs": [ 
			{ "orderable": false, "targets": [ 0, 9 ] },
			{ "searchable": false, "targets": [ 0, 9 ] },
			{ "width": '1em', "targets": [ 9 ] },
			{ "width": '3em', "targets": [ 7 ] },
			{ "className": "dt-center", "targets": [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9 ] }//center align both header and body content
		],
		//and declare the input columns for the functions above
		"columns": [
			null,
			{ "orderDataType": "dom-select" },
			{ "orderDataType": "dom-select" },
			{ "orderDataType": "dom-text-numeric" },
			{ "orderDataType": "dom-text", type: 'string' },
			null,
			{ "orderDataType": "dom-select" },
			null,
			{ "orderDataType": "dom-text", type: 'string' },
			null
		]
	});

	$('#issues-by-collection').change(function(){
		console.warn('issues-by-collection change', this);
		var collection_id = $(this).find(":selected").val();
		window.location.href = '/issues?coll='+collection_id;
	});

	$('#issues-by-series').change(function(){
		console.warn('issues-by-series change', this);
		var series_id = $(this).find(":selected").val();
		window.location.href = '/issues?ser='+series_id;
	});

	$('.picture').on('click', function(){
		var element_id = $(this).attr('id');
		var issue_id = element_id.slice(7);
		window.location.href = '/details?id='+issue_id;
	});

	$('.collection').change(function(){
		console.warn('collection change', this);
		var element_id = $(this).attr('id');
		var issue_id = element_id.slice(10);
		var new_collection_id = $(this).find(":selected").val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { collection_change: issue_id, new_collection_id: new_collection_id } 
		}).done(function(data){
			if(data === 'done'){
				console.warn('collection changed');
			}else{
				alert(data);
			}
		});
	});

	$('.series').change(function(){
		console.warn('series change', this);
		var element_id = $(this).attr('id');
		var issue_id = element_id.slice(6);
		var new_series_id = $(this).find(":selected").val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { series_change: issue_id, new_series_id: new_series_id } 
		}).done(function(data){
			if(data === 'done'){
				console.warn('series changed');
			}else{
				alert(data);
			}
		});
	});

	$('.issue').change(function(){
		console.warn('issue onChange', this);
		var element_id = $(this).attr('id');
		var issue_id = element_id.slice(5);
		var new_issue_number = $(this).val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { issue_number_change: issue_id, new_issue_number: new_issue_number } 
		}).done(function(data){
			if(data === 'done'){
				console.warn('issue number changed');
			}else{
				alert(data);
			}
		});
	});

	$('.chrono').change(function(){
		console.warn('chrono onChange', this);
		var element_id = $(this).attr('id');
		var issue_id = element_id.slice(6);
		var new_chrono_index = $(this).val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { chrono_index_change: issue_id, new_chrono_index: new_chrono_index } 
		}).done(function(data){
			if(data === 'done'){
				console.warn('chrono index changed');
			}else{
				alert(data);
			}
		});
	});

	$('.grade').change(function(){
		console.warn('grade change', this);
		var element_id = $(this).attr('id');
		var issue_id = element_id.slice(5);
		var new_grade_id = $(this).find(":selected").val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { grade_change: issue_id, new_grade_id: new_grade_id } 
		}).done(function(data){
			if(data === 'done'){
				console.warn('grade changed');
			}else{
				alert(data);
			}
		});
	});

	$('.comicvine-link').on('click', function(){
		var id = $(this).attr('data-comicvine-issue-id');
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { comicvine_issue_id: id } 
		}).done(function(data){
			window.open(data, '_blank');
		});
	});

	$('.notes').change(function(){
		console.warn('notes onChange', this);
		var element_id = $(this).attr('id');
		var issue_id = element_id.slice(5);
		var new_notes = $(this).val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { notes_change: issue_id, new_notes: new_notes } 
		}).done(function(data){
			if(data === 'done'){
				console.warn('notes changed');
			}else{
				alert(data);
			}
		});
	});

	$('#issuesTable').on('click', '.delete', function(){
		console.log($(this).parent());
		var element_id = $(this).attr('id');
		var issue_id = element_id.slice(6);
		var issue_name = $(this).attr('data-issue-text');
		if(confirm("are you sure you wish to delete: "+issue_name)){
			$(this).parent().parent().remove();
			$.ajax({
				method: 'POST',
				url: '/ajax/lookup.php',
				data: { delete_issue: issue_id } 
			}).done(function(data){
				if(data === 'done'){
					console.warn('issue deleted');
				}else{
					alert(data);
				}
			});
		}
	});

	$('.add-issues').on('click', function(){
		window.location.href = '/addIssues';
	});

	$(".popup-on-hover").hover(function(){
		console.warn('hover', this);
		$(this).show();
	});

});
</script>
