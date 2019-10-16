<?php
logDebug('addCollections: '.var_export($_POST, true));
if(isset($_POST['submit'])){
	$newCollections = $_POST['name'];
	for($i=0; $i<count($newCollections); $i++){
		if(!$newCollections[$i]){ break; }
		$existingCollection = Collection::getCollectionByName($db, $newCollections[$i]);
		if(!$existingCollection){
			$collection_id = Collection::createCollection($db, $newCollections[$i]);
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
$inputFieldCells =
	"<td>".
		"<input type='text' name='name[]' class='name' placeholder='Collection Name'/>".
	"</td>";
?>

<h2 class='add-header'>Add Collections</h2>

<form id='addCollectionsForm' method='POST' action=''>
	<table id='addCollectionsTable'>
		<thead>
			<tr>
				<td>Collection</td>
			</tr>
		</thead>
		<tbody>
<?php for($i=0; $i<$fields; $i++){ ?>
			<tr>
				<?=$inputFieldCells?>
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
