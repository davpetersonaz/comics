<?php $series = Series::getAllSeries($db); ?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-series'>Add Series</button>
</div>

<table id='seriesTable' class="display">
	<thead>
		<tr>
			<th>id</th>
			<th>title</th>
			<th>volume</th>
			<th>collection</th>
			<th>year</th>
			<th>comicvine (short)</th>
			<th>comicvine (full)</th>
		</tr>
	</thead>
	<tbody>
<?php foreach($series as $serie){ ?>
		<tr>
			<td><?=$serie['series_id']?></td>
			<td><input type="text" class='series_name' id='series<?=$serie['series_id']?>' value='<?=$serie['title']?>'></td>
			<td><?=$serie['volume']?></td>
			<td><?=$serie['collection_name']?></td>
			<td><?=$serie['year']?></td>
			<td><?=$serie['comicvine_series_id']?></td>
			<td><?=$serie['comicvine_series_full']?></td>
		</tr>
<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<th>id</th>
			<th>title</th>
			<th>volume</th>
			<th>collection</th>
			<th>year</th>
			<th>comicvine (short)</th>
			<th>comicvine (full)</th>
		</tr>
	</tfoot>
</table>

<script>
$(document).ready(function(){
	
	$('#seriesTable').dataTable();
	
	$('.series_name').change(function(){
		console.warn('onChange', this);
		var id = $(this).attr('id');
		var new_name = $(this).val();
		console.warn('id', id);
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { series_change: id, new_name: new_name } 
		}).done(function(data){
			//complete.
		});
	});
	
	$('.add-series').on('click', function(){
		window.location.href = '/addSeries';
	});
	
});
</script>