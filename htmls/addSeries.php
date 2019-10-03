<?php
/*
logDebug('addSeries: '.var_export($_POST, true));
if(isset($_POST['submit'])){
	$collection = $_POST['collection'];
	$newSeries = $_POST['series'];
	$volume = $_POST['volume'];
	$existingSeries = Series::getSeriesByName($db, $newSeries, $volume);
	if(!$existingSeries){
		
		
		
		$series = new Series($db);
		$series_id = $series->createSeries($title, $volume, $collection_id, $comicvine_info);
		logDebug('created series: '.$series_id);
	}else{
		logDebug('series already exists: '.$existingSeries['title']);
	}
	?>
		<p class='red-text'>The series has been added, create more...</p>
	<?php
}
*/

$collections = Collection::getCollections($db);
$collection_options = '';
foreach($collections as $collection){
	$collection_options .= "<option value='{$collection['collection_id']}'>{$collection['collection_name']}</option>";
}
?>

<h2>Add Series</h2>
<form id='addSeriesForm' method='POST' action=''>
	<table id='addSeriesTable'>
		<thead>
			<tr>
				<td>Collection</td><td>Series</td><td>Volume</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<select name='collection' placeholder='Collection'>
						<option value=''></option><?=$collection_options?>
					</select>
				</td>
				<td>
					<input type='text' name='series' placeholder='Series Name'/>
				</td>
				<td>
					<input type='text' name='volume' placeholder='Volume'/>
				</td>
			</tr>
		</tbody>
	</table>
	<div class='action-buttons'>
		<button type='submit' name='submit' class="btn btn-primary bg-dark">Submit</button>
	</div>
</form>

<script>
$(document).ready(function(){
	//reshow this page on submit, or display selectseries if series is unknown.
	var seriesForm = $('#addSeriesForm');
	seriesForm.on('submit', function(event){
		event.preventDefault();
		var inputs = $('#addSeriesForm :input');
		var form = document.createElement('form');
		form.method = 'POST'; form.action = 'addSeriesSelect';
		var hiddenField1 = document.createElement('input');
		hiddenField1.type = 'hidden'; hiddenField1.name = 'collectionid'; hiddenField1.value = $(inputs[0]).val(); 
		form.appendChild(hiddenField1);
		var hiddenField2 = document.createElement('input');
		hiddenField2.type = 'hidden'; hiddenField2.name = 'series'; hiddenField2.value = $(inputs[1]).val();  
		form.appendChild(hiddenField2);
		var hiddenField3 = document.createElement('input');
		hiddenField3.type = 'hidden'; hiddenField3.name = 'volume'; hiddenField3.value = $(inputs[2]).val(); 
		form.appendChild(hiddenField3);
		document.body.appendChild(form);
		console.warn('form', form);
		alert('submitting form');
		form.submit();//to addSeriesSelect
	});
});
</script>
