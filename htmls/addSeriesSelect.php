<?php

//TODO: on the javascript seriesvolume prompt, parse the series volume from comicvine and use that as the default (instead of default=1)

logDebug('addSeriesSelect: '.var_export($_POST, true));
$seriesname = $_POST['series'];
$series_data = array();
//$pageLength = (isset($_SESSION['table_length']['home']) && $_SESSION['table_length']['home'] > 0 ? $_SESSION['table_length']['home'] : 100);
$pageLength = 100;

$results = $curl->getSeriesByName($seriesname);
logDebug('getSeriesByName results: '.count($results));
if(!$results){
	?> <script>window.location.href=addSeries?notfound=<?= urlencode($seriesname)?></script> <?php
}
foreach($results as $result){
	$newArray = array();
//	$newArray[] = "<a href='{$result['image']['super_url']}' class='preview' title='{$result['name']} {$result['start_year']}'><img src='{$result['image']['thumb_url']}' alt='gallery thumbnail' />";
	$newArray[] =	"<div class='picture'>".
						"<a class='small' href='#nogo' title='small image'>".
							"<img src='{$result['image']['thumb_url']}' class='img-responsive'>".
							"<img src='{$result['image']['super_url']}' class='large popup-on-hover'>".
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
//	$comicvineSeriesFull = (preg_match('/^.*\/(\d+-\d+)\/$/', $result['site_detail_url'], $matches) === 1 && isset($matches[1]) ? "{$matches[1]}" : '');//comicvine series full-id (xxxx-xxxx)
//	$newArray[] = "<a href='{$result['site_detail_url']}' target='_blank'>{$comicvineSeriesFull}</a>";
	$newArray[] = $result['image']['thumb_url'];
	$newArray[] = $result['image']['super_url'];
//	logDebug('row: '.implode(', ', $newArray));
	$series_data[] = $newArray;
	//return the list to selectseries 
}		
?>

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
			<th>image thumb</th>
			<th>image full</th>
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
			<th>image thumb</th>
			<th>image full</th>
		</tr>
	</tfoot>
</table>

<script>
$(document).ready(function(){
	var seriesdatatable = $('#whatSeriesDatatable').DataTable({
		"pageLength": <?=$pageLength?>,
		"columnDefs": [ 
			{ "searchable": false, "targets": [ 0, 8, 9, 10, 11 ] },
			{ "orderable": false, "targets": [ 0, 8, 9, 10, 11 ] },
			{ "visible": false, "targets": [ 8, 9, 10, 11 ] },
			{ "width": "2em", "targets": [ 0, 4, 5, 6, 7, 8, 9 ] }
		]
	});

	$('#whatSeriesDatatable tbody').on('click', 'tr', function(){
		console.warn('row click', this);
		$('body').css('cursor', 'progress');
		$('body').css('pointer-events', 'none');
		var currentRowData = seriesdatatable.row(this).data();
		console.warn('currentRowData', currentRowData);
		var seriesname = prompt("what is the series name?", currentRowData[1]);
		if(seriesname === null){ window.location.href = '/addSeries'; return false; }
		var volume = prompt("what is the volume number?", 1);
		if(volume === null){ window.location.href = '/addSeries'; return false; }
//		alert('posting to lookup');
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { comicvine: currentRowData, volume: volume, seriesname: seriesname } 
		}).done(function(data){
			console.warn('response (series_id)', data);//series_id
			if(data !== 'done'){
				alert(data);
				window.location.reload();
			}else{
//				alert('addSelectSeries: redirecting to addSeries');
				window.location.href = '/addSeries';
			}
		});
		return false;
	});

	$(".popup-on-hover").hover(function(){
		console.warn('hover', this);
		$(this).show();
	});

	//https://stackoverflow.com/questions/21609257/jquery-datatables-scroll-to-top-when-pages-clicked-from-bottom
	$('#whatSeriesDatatable').on('page.dt', function() {
		$('html, body').animate({ scrollTop: $(".dataTables_wrapper").offset().top }, 'slow');
		$('thead tr th:first-child').focus().blur();
	});

});
</script>
