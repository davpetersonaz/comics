<?php $pageLength = (isset($_SESSION['table_length']['home']) && $_SESSION['table_length']['home'] > 0 ? $_SESSION['table_length']['home'] : 25); ?>
<?php $collections = Collection::getAllCollections($db); ?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-collections'>Add Collections</button>
</div>

<table id='collectionsTable' class="display">
	<thead><tr><th>id</th><th>collection</th><th>edit name</th><th>usage</th><th></th></tr></thead>
	<tbody>
<?php foreach($collections as $collection){ ?>
		<tr>
			<td><?=$collection->getId()?></td>
			<td><?=$collection->getName()?></td>
			<td><input type="text" class='collection_name' id='collection<?=$collection->getId()?>' value='<?=$collection->getName()?>'></td>
			<td><?=$collection->getSeriesCount()?></td>
			<td><span class='delete' id='delete<?=$collection->getId()?>' data-collection-text='<?=$collection->getName()?>' data-collection-series='<?=$collection->getSeriesCount()?>'><i class='fa fa-times'></i></span></td>
		</tr>
<?php } ?>
	</tbody>
	<tfoot><tr><th>id</th><th>collection</th><th>edit name</th><th>usage</th><th></th></tr></tfoot>
</table>

<script>
$(document).ready(function(){

	$('#collectionsTable').DataTable({
		"order": [[ 1, 'asc' ]],
		"pageLength": <?=$pageLength?>,
		"columnDefs": [ 
			{ "searchable": false, "targets": [ 2, 4 ] },
			{ "orderable": false, "targets": [ 2, 4 ] },
			{ "width": '1em', "targets": [ 4 ] },
			{ "width": '2em', "targets": [ 3 ] },
			{ "className": "dt-center", "targets": [ 0, 1, 2, 3, 4 ] }// Center align both header and body content of columns
		]
	});

	$('.collection_name').change(function(){
		console.warn('onChange', this);
		var element_id = $(this).attr('id');
		var id = element_id.slice(10);
		console.warn('id', id);
		var new_name = $(this).val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { collection_name_change: id, new_name: new_name } 
		}).done(function(data){
			console.warn('collection name changed');
		});
	});

	$('#collectionsTable').on('click', '.delete', function(){
		console.log($(this).parent());
		var element_id = $(this).attr('id');
		var collection_id = element_id.slice(6);
		var collection_name = $(this).attr('data-collection-text');
		var series_count = $(this).attr('data-collection-series');
		if(series_count > 0){
			alert('you must delete all series for this collection first');
			window.location.href='/series?coll='+collection_id;
			return;
		}
		if(confirm("are you sure you wish to delete: "+collection_name)){
			$(this).parent().parent().remove();
			$.ajax({
				method: 'POST',
				url: '/ajax/lookup.php',
				data: { delete_collection: collection_id } 
			}).done(function(data){
				if(data === 'done'){
					console.warn('collection deleted');
				}else{
					alert(data);
				}
			});
		}
	});

	$('.add-collections').on('click', function(){
		window.location.href = '/addCollections';
	});

});
</script>