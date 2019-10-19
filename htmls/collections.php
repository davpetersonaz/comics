<?php $pageLength = (isset($_SESSION['table_length']['home']) && $_SESSION['table_length']['home'] > 0 ? $_SESSION['table_length']['home'] : 100); ?>
<?php $collections = Collection::getAllCollections($db); ?>
<?php // logDebug('collections: '.var_export($collections, true)); ?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-collections'>Add Collections</button>
</div>

<table id='collectionsTable' class="display">
	<thead><tr><th>id</th><th>collection</th><th>edit name</th><th>usage</th><th>description</th><th></th></tr></thead>
	<tbody>
<?php foreach($collections as $collection){ ?>
		<tr>
			<td><?=$collection->getId()?></td>
			<td><?=$collection->getName()?></td>
			<td><input type="text" class='collection_name' id='collection<?=$collection->getId()?>' value='<?=$collection->getName()?>'></td>
			<td><?=$collection->getIssueCount()?></td>
			<td><input type="text" class='description' id='collection<?=$collection->getId()?>' value='<?=$collection->getDescription()?>'></td>
			<td><span class='delete' id='delete<?=$collection->getId()?>' data-collection-text='<?=$collection->getName()?>' data-collection-issues='<?=$collection->getIssueCount()?>'><i class='fa fa-times'></i></span></td>
		</tr>
<?php } ?>
	</tbody>
	<tfoot><tr><th>id</th><th>collection</th><th>edit name</th><th>usage</th><th>description</th><th></th></tr></tfoot>
</table>

<script>
$(document).ready(function(){

	$('#collectionsTable').DataTable({
		"order": [[ 1, 'asc' ]],
		"pageLength": <?=$pageLength?>,
		"columnDefs": [ 
			{ "searchable": false, "targets": [ 2, 5 ] },
			{ "orderable": false, "targets": [ 2, 5 ] },
			{ "width": '1em', "targets": [ 5 ] },
			{ "className": "dt-center", "targets": [ 0, 1, 2, 3, 4, 5 ] }// Center align both header and body content of columns
		]
	});

	$('#collectionsTable').on('change', '.collection_name', function(){
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

	$('#collectionsTable').on('change', '.description', function(){
		console.warn('onChange', this);
		var element_id = $(this).attr('id');
		var id = element_id.slice(10);
		console.warn('id', id);
		var new_description = $(this).val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { collection_description_change: id, new_description: new_description } 
		}).done(function(data){
			console.warn('collection description changed');
		});
	});

	$('#collectionsTable').on('click', '.delete', function(){
		console.log($(this).parent());
		var element_id = $(this).attr('id');
		var collection_id = element_id.slice(6);
		var collection_name = $(this).attr('data-collection-text');
		var issue_count = $(this).attr('data-collection-issues');
		if(issue_count > 0){
			alert('you must delete all issues in this collection first');
			window.location.href='/issues?coll='+collection_id;
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

	//https://stackoverflow.com/questions/21609257/jquery-datatables-scroll-to-top-when-pages-clicked-from-bottom
	$('#collectionsTable').on('page.dt', function() {
		$('html, body').animate({ scrollTop: $(".dataTables_wrapper").offset().top }, 'slow');
		$('thead tr th:first-child').focus().blur();
	});

});
</script>