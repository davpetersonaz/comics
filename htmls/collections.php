<?php $collections = Collection::getCollections($db); ?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-collections'>Add Collections</button>
</div>

<table id='collectionsTable' class="display">
	<thead><tr><th>id</th><th>collection</th><th>edit name</th></tr></thead>
	<tbody>
<?php foreach($collections as $collection){ ?>
		<tr>
			<td><?=$collection['collection_id']?></td>
			<td><?=$collection['collection_name']?></td>
			<td><input type="text" class='collection_name' id='collection<?=$collection['collection_id']?>' value='<?=$collection['collection_name']?>'></td>
		</tr>
<?php } ?>
	</tbody>
	<tfoot><tr><th>id</th><th>collection</th><th>edit name</th></tr></tfoot>
</table>

<script>
$(document).ready(function(){
	
	$('#collectionsTable').DataTable({
		"order": [[ 1, 'asc' ]],
		"columnDefs": [ 
			{ "searchable": false, "targets": [ 2 ] },
			{ "orderable": false, "targets": [ 2 ] }
		]		
	});
	
	$('.collection_name').change(function(){
		console.warn('onChange', this);
		var id = $(this).attr('id');
		var new_name = $(this).val();
		console.warn('id', id);
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { collection_change: id, new_name: new_name } 
		}).done(function(data){
			//complete.
		});
	});
	
	$('.add-collections').on('click', function(){
		window.location.href = '/addCollections';
	});
	
});
</script>