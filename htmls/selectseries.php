<?php //TODO: THIS IS NOT NEEDED NO MORE 
/*

<?php logDebug('selectseries: '.var_export($_POST, true)); ?>
<?php $series_data = json_decode($_POST['data']); ?>

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

<?php /*			
	//then once the user selects an issue, another ajax call to save the comicvine issue id, 
	//and also to set the comicvine-volume-id for the rest of the issues in this series (defined by me, in the db)
	//and then i can bring up the details of the issue
*//* ?>

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
		var issueid = '<?=$_POST['issueid']?>';
		var seriesid = '<?=$_POST['seriesid']?>';
		var collectionid = '<?=$_POST['collectionid']?>';
		var volume = '<?=$_POST['volume']?>';
		var issuenumber = '<?=$_POST['issuenumber']?>';
		console.warn('currentRowData', currentRowData);
		console.warn('series title', currentRowData[1]);
		alert('posting to lookup');
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { comicvine: currentRowData, seriesid: seriesid, issueid: issueid, collectionid: collectionid, volume: volume, issuenumber: issuenumber } 
		}).done(function(data){
			console.warn('issue_id', data);//issueid
			alert('selectseries: redirecting to details');
			location.href = '/details?id='+data;
		});
	});

	$(".popup-on-hover").hover(function(){
		console.warn('hover', this);
		$(this).show();
	});
});
</script> 

*/