<?php 
if(!$alreadyLoggedIn){ ?><script>window.location.href = '/';</script><?php }

logDebug('issues GET: '.var_export($_GET, true));
$pageLength = 100;//(isset($_SESSION['table_length']['home']) && $_SESSION['table_length']['home'] > 0 ? $_SESSION['table_length']['home'] : 25);

$collectionsChoice = (isset($_GET['coll']) ? $_GET['coll'] : false);
$seriesChoice = (isset($_GET['ser']) ? $_GET['ser'] : false);
$getParams = ($collectionsChoice ? "?coll={$collectionsChoice}" : '');
$getParams .= ($seriesChoice ? ($getParams?'&':'?')."ser={$seriesChoice}" : '');
logDebug("getParams: [{$getParams}]");

$collections = Collection::getAllCollections($db);
$series = Series::getAllSeries($db, $curl);
?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-issues'>Add Issues</button>
	<button class='btn btn-primary bg-dark add-series'>Add Series</button>
	<button class='btn btn-primary bg-dark add-collection'>Add Collection</button>
</div>

<div class='dropdown-above-table' style='float:left;'>
	filter by collection:<br />
	<select id='issues-by-collection'>
		<option value=''></option>
<?php foreach($collections as $collection){ ?>
	<?php $selected = ($collectionsChoice && intval($collectionsChoice) === intval($collection->getId()) ? 'selected' : ''); ?>
		<option value='<?=$collection->getId()?>' <?=$selected?>><?=$collection->getName()?> (<?=$collection->getIssueCount()?> issue<?=($collection->getIssueCount()!==1?'s':'')?>)</option>
<?php } ?>
	</select>
</div>

<div class='dropdown-above-table' style='float:left;margin-left:2em;'>
	filter by series:<br />
	<select id='issues-by-series'>
		<option value=''></option>
<?php foreach($series as $serie){ ?>
	<?php $selected = ($seriesChoice && intval($seriesChoice) === intval($serie->getId()) ? 'selected' : ''); ?>
		<option value='<?=$serie->getId()?>' <?=$selected?>><?=$serie->getDisplayText()?> (<?=$serie->getIssueCount()?> issue<?=($serie->getIssueCount()!==1?'s':'')?>)</option>
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
			<th>id</th>
			<th> </th>
		</tr>
	</thead>
	<tbody>
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
			<th>id</th>
			<th> </th>
		</tr>
	</tfoot>
</table>

<script>
$(document).ready(function(){

	$('#issuesTable').dataTable({
		"ajax": "/ajax/issues.php<?=$getParams?>",
		"dom": 'frtip',
		"order": [[ 1, 'asc' ],[ 2, 'asc' ],[ 3, 'asc' ],[ 6, 'asc' ]],
		"pageLength": <?=$pageLength?>,
		"processing": true,
		"searchDelay": 1000,
		"serverSide": true,
		"columnDefs": [ 
			{ "orderable": false, "targets": [ 0, 10 ] },
			{ "searchable": false, "targets": [ 0, 5, 10 ] },
			{ "width": '1em', "targets": [ 10 ] },
			{ "className": "dt-center", "targets": [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ] }//center align both header and body content
		]
	});

	$('#issuesTable').on('click', '.comicvine-link', function(){
		var element_id = $(this).attr('id');
		var id = element_id.slice(9);
		console.warn('id', id);
		window.open('https://comicvine.gamespot.com/issue/'+id, '_blank');
	});

	$('#issues-by-collection').change(function(){
		console.warn('issues-by-collection change', this);
		var collection_id = $(this).find(":selected").val();
		window.location.href = '/issues?coll='+collection_id;
		//TODO:	ADD JS-FUNCTIONS TO MANIPULATE URL-PARAMS SO I CAN ADD THE SERIES (IF SELECTED)
		//OR, USE JQUERY TO RETRIEVE THE CURRENT SERIES SELECTION
	});

	$('#issues-by-series').change(function(){
		console.warn('issues-by-series change', this);
		var series_id = $(this).find(":selected").val();
		window.location.href = '/issues?ser='+series_id;
		//TODO:	ADD JS-FUNCTIONS TO MANIPULATE URL-PARAMS SO I CAN ADD THE COLLECTION (IF SELECTED)
		//OR, USE JQUERY TO RETRIEVE THE CURRENT COLLECTION SELECTION
	});

	$('#issuesTable').on('click', '.picture', function(){
		var element_id = $(this).attr('id');
		var issue_id = element_id.slice(7);
		window.open('/details?id='+issue_id, '_blank');
	});

	$('#issuesTable').on('change', '.collection', function(){
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

	$('#issuesTable').on('change', '.series', function(){
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
				window.location.reload();
			}else{
				alert(data);
			}
		});
	});

	$('#issuesTable').on('change', '.issue', function(){
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
				window.location.reload();
			}else{
				alert(data);
			}
		});
	});

	$('#issuesTable').on('change', '.chrono', function(){
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

	$('#issuesTable').on('change', '.grade', function(){
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

	$('#issuesTable').on('change', '.comicvine-link', function(){
		console.warn('comicvine onChange', this);
		var id = $(this).attr('data-comicvine-issue-id');
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { comicvine_issue_id: id } 
		}).done(function(data){
			window.open(data, '_blank');
		});
	});

	$('#issuesTable').on('change', '.notes', function(){
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

	$('.add-series').on('click', function(){
		window.location.href = '/addSeries';
	});

	$('.add-collection').on('click', function(){
		window.location.href = '/addCollections';
	});

	$(".popup-on-hover").hover(function(){
		console.warn('hover', this);
		$(this).show();
	});

	//https://stackoverflow.com/questions/21609257/jquery-datatables-scroll-to-top-when-pages-clicked-from-bottom
	$('#issuesTable').on('page.dt', function() {
		$('html, body').animate({ scrollTop: $(".dataTables_wrapper").offset().top }, 'slow');
		$('thead tr th:first-child').focus().blur();
	});

});
</script>
