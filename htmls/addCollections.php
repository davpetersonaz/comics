<?php if(!$alreadyLoggedIn){ ?><script>window.location.href = '/';</script><?php } ?>

<?php
logDebug('addCollections: '.var_export($_POST, true));
if(isset($_POST['submit'])){
	$newCollections = $_POST['name'];
	$newDescriptions = $_POST['description'];
	for($i=0; $i<count($newCollections); $i++){
		if(!$newCollections[$i]){ break; }
		$existingCollection = Collection::getCollectionByName($db, $newCollections[$i]);
		if(!$existingCollection){
			$collection_id = Collection::createCollection($db, $newCollections[$i], $newDescriptions[$i]);
			logDebug('created collection: '.$collection_id);
		}else{
			logDebug('collection already exists: '.$existingCollection['collection_name']);
		}
	}
	?>
	<script>window.location.href='/collections';</script>
	<?php
}

$fields = 5;
?>

<h2 class='add-header'>Add Collections</h2>

<form id='addCollectionsForm' method='POST' action=''>
	<table id='addCollectionsTable'>
		<thead>
			<tr>
				<td>Collection</td>
				<td>Description</td>
			</tr>
		</thead>
		<tbody>
<?php for($i=0; $i<$fields; $i++){ ?>
			<tr>
				<td><input type='text' name='name[]' class='name' placeholder='Collection Name'/></td>
				<td><input type='text' name='description[]' class='description' placeholder='Description'/></td>
			</tr>
<?php } ?>
		</tbody>
	</table>
	<div class='action-buttons'>
		<button type='submit' name='submit' class="btn btn-primary bg-dark">Submit</button>
	</div>
</form>

<script>
$(document).ready(function(){
	//focus on first input
	$('form:first *:input[type!=hidden]:first').focus();		
});
</script>
