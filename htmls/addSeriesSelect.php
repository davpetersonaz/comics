<?php
logDebug('addSeriesSelect: '.var_export($_POST, true));
$collection = $_POST['collectionid'];
$seriesname = $_POST['series'];
$volume = $_POST['volume'];
$series_data = array();

$results = $curl->getSeriesByName($seriesname);
logDebug('getSeriesByName results: '.count($results));
foreach($results as $result){
	$newArray = array();
//	$newArray[] = "<a href='{$result['image']['super_url']}' class='preview' title='{$result['name']} {$result['start_year']}'><img src='{$result['image']['thumb_url']}' alt='gallery thumbnail' />";
	$newArray[] = "<div class='picture'>".
						"<a class='small' href='#nogo' title='small image'>".
							"<img src='{$result['image']['thumb_url']}' class='img-responsive'>".
							"<img class='large popup-on-hover' src='{$result['image']['super_url']}'>".
						"</a>".
					"</div>";
	$newArray[] = $result['name'];
	$newArray[] = $result['deck'];//'deck' => 'Volume 1.'
	$newArray[] = $result['publisher']['name'];
	$newArray[] = $result['start_year'];
	$newArray[] = "{$result['count_of_issues']}";
	$newArray[] = $result['first_issue']['issue_number'];
	$newArray[] = $result['last_issue']['issue_number'];
	$newArray[] = "{$result['id']}";//comicvine series short-id (xxxx)
	$newArray[] = (preg_match('/^.*\/(\d+-\d+)\/$/', $result['site_detail_url'], $matches) === 1 && isset($matches[1]) ? "{$matches[1]}" : '');//comicvine series full-id (xxxx-xxxx)
//	logDebug('row: '.implode(', ', $newArray));
	$series_data[] = $newArray;
	//return the list to selectseries 
}		
?>

<style>
/*.preview{
	position: absolute;
	border: 1px solid #ccc;
	background: #black;
	padding: 5px;
	display: none;
	color: #fff;
}*/
</style>

<h3>What series is this from?</h3>
<table id="whatSeriesDatatable" class="display" style="width:100%">
	<thead>
		<tr>
			<th>image</th>
			<th>series</th>
			<th>volume</th>
			<th>publisher</th>
			<th>year</th>
			<th>issues</th>
			<th>first</th>
			<th>last</th>
			<th>comicvine id</th>
			<th>full id</th>
		</tr>
	</thead>
	<tbody>
<?php foreach($series_data as $series){ ?>
		<tr class='whatSeriesRow'>
	<?php foreach($series as $k=>$column){ ?>
			<td><?=$column?></td>
	<?php } ?>		
		</tr>
<?php } ?>		
	</tbody>
	<tfoot>
		<tr>
			<th>image</th>
			<th>series</th>
			<th>volume</th>
			<th>publisher</th>
			<th>year</th>
			<th>issues</th>
			<th>first</th>
			<th>last</th>
			<th>comicvine id</th>
			<th>full id</th>
		</tr>
	</tfoot>
</table>

<script>
$(document).ready(function(){
	var seriesdatatable = $('#whatSeriesDatatable').DataTable({
		"columnDefs": [ 
			{ "searchable": false, "targets": [ 0, 8, 9 ] },
			{ "orderable": false, "targets": [ 0, 8, 9 ] },
			{ "visible": false, "targets": [ 8, 9 ] },
			{ "width": "4em", "targets": [ 2 ] },
			{ "width": "2em", "targets": [ 0, 4, 5, 6, 7, 8, 9 ] }
		]
	});
	
	$('#whatSeriesDatatable tbody').on('click', 'tr', function(){
		console.warn('row click', this);
		$('body').css('cursor', 'progress');
		$('body').css('pointer-events', 'none');
		var currentRowData = seriesdatatable.row(this).data();
		console.warn('currentRowData', currentRowData);
		var collectionid = '<?=$_POST['collectionid']?>';
		var seriesname = '<?=$_POST['series']?>';
		var volume = '<?=$_POST['volume']?>';
		alert('posting to lookup');
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { comicvine: currentRowData, collectionid: collectionid, volume: volume, seriesname: seriesname } 
		}).done(function(data){
			console.warn('response (series_id)', data);//series_id
			alert('addSelectSeries: redirecting to addSeries');
			location.href = '/addSeries';
		});
	});

	$(".popup-on-hover").hover(function(){
		console.warn('hover', this);
		$(this).show();
	});
});
</script>
