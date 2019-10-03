<?php 
$fields = 10;
$issues = Issue::getAllIssues($db); 
$collections = Collection::getCollections($db);
$series = Series::getAllSeries($db);
$grading = $grades->getAllGrades();
?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-issues'>Add Issues</button>
</div>

<style>
.picture:hover .popup-on-hover{
	display: block;
}
</style>

<table id='issuesTable' class="display">
	<thead>
		<tr>
			<th> </th>
			<th>collection</th>
			<th>series</th>
			<th>volume</th>
			<th>issue</th>
			<th>chrono</th>
			<th>cover date</th>
			<th>grade</th>
		</tr>
	</thead>
	<tbody>
		
<?php 
logDebug('issues size: '.count($issues));
foreach($issues as $issue){
	logDebug('processing issue');
	$image_div = '';
	if($issue['image_thumb'] && $issue['image_full']){
		$image_div =	
					"<div class='picture'>".
						"<a class='small' href='#nogo' title='small image'>".
							"<img class='img-responsive' src='{$issue['image_thumb']}'>".
							"<img class='large popup-on-hover' src='{$issue['image_full']}'>".
						"</a>".
					"</div>";
	}
	logDebug('image_div: '.$image_div);
	$collection_div = "<select name='collection[]'>";
	foreach($collections as $collection){
		$selected = (intval($collection['collection_id']) === intval($issue['collection_id']) ? ' selected' : '');
		$collection_div .= "<option value='{$collection['collection_id']}' {$selected}>{$collection['collection_name']}</option>";
	}
	$collection_div .= "</select>";
//	logDebug('collection_div: '.$collection_div);
	$series_div = "<select name='series[]'>";
	foreach($series as $serie){
		$selected = (intval($serie['series_id']) === intval($issue['series_id']) ? ' selected' : '');
		$series_div .= "<option value='{$serie['series_id']}' {$selected}>{$serie['title']} vol.{$serie['volume']} ({$serie['year']})</option>";
	}
	$series_div .= "</select>";
//	logDebug('series_div: '.$series_div);
	$volume_div = "<input type='text' name='volume[]' value='{$issue['volume']}'/>";
//	logDebug('volume_div: '.$volume_div);
	$issue_div = "<input type='text' name='issue[]' value='{$issue['issue']}'/>";
//	logDebug('issue_div: '.$issue_div);
	$chrono_div = "<input type='text' name='chrono[]' value='{$issue['chrono_index']}'/>";
//	logDebug('chrono_div: '.$chrono_div);
	$coverdate = new DateTime("{$issue['cover_date']}");
	$cover_div = ($coverdate->format('m') === '01' ? $coverdate->format('M Y') : $coverdate->format('M j, Y'));
//	logDebug('cover_div: '.$cover_div);
	$grade_div = "<select name='grade[]'>";
	foreach($grading as $grade_array){
//		logDebug('grade_array: '.var_export($grade_array, true));
		$selected = (intval($grade_array['position']) === intval($issue['position']) ? ' selected' : '');
		$grade_div .= "<option value='{$grade_array['position']}' title='{$grade_array['short_desc']}' {$selected}>{$grade_array['name']}</option>";
	}
	$grade_div .= "</select>";
	?>
		<tr>
			<td><?=$image_div?></td>
			<td><?=$collection_div?></td>
			<td><?=$series_div?></td>
			<td><?=$volume_div?></td>
			<td><?=$issue_div?></td>
			<td><?=$chrono_div?></td>
			<td><?=$cover_div?></td>
			<td><?=$grade_div?></td>
		</tr>
	<?php logDebug("finished processing issue: {$issue['collection_name']} / {$issue['title']} / {$issue['volume']} / {$issue['issue']}"); ?>
<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<th> </th>
			<th>collection</th>
			<th>series</th>
			<th>volume</th>
			<th>issue</th>
			<th>chrono</th>
			<th>cover date</th>
			<th>grade</th>
		</tr>
	</tfoot>
</table>

<script>
$(document).ready(function(){
	
	$('#issuesTable').dataTable();
	
	$('.add-issues').on('click', function(){
		window.location.href = '/addIssues';
	});
	
	$(".popup-on-hover").hover(function(){
		console.warn('hover', this);
		$(this).show();
	});

});
</script>
