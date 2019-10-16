<?php 


//TODO: MAYBE ... click on a row and it brings up /issues?serXX

//TODO: add "publisher" column

//TODO: click on 'usage' and it should go to a 'issues' listing filtered on the series


$pageLength = (isset($_SESSION['table_length']['home']) && $_SESSION['table_length']['home'] > 0 ? $_SESSION['table_length']['home'] : 100);
$series = Series::getAllSeries($db); 
?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-series'>Add Series</button>
	<button class='btn btn-primary bg-dark add-issues'>Add Issues</button>
</div>

<table id='seriesTable' class="display">
	<thead>
		<tr>
			<th> </th>
			<th>id</th>
			<th>title</th>
			<th>volume</th>
			<th>year</th>
			<th>first</th>
			<th>last</th>
			<th>comicvine (short)</th>
			<th>comicvine (full)</th>
			<th>usage</th>
			<th> </th>
		</tr>
	</thead>
	<tbody>
<?php foreach($series as $serie){ ?>
		<tr>
			<td>
				<div id='picture<?=$serie->getId()?>' class='picture'>
					<a href='#nogo' class='small' title='<?=$serie->getDisplayText()?>'>
						<img class='img-responsive' src='<?=$serie->getImageThumb()?>'>
						<img class='large popup-on-hover' src='<?=$serie->getImageFull()?>'>
					</a>
				</div>
			</td>
			<td><?=$serie->getId()?></td>
			<td><input type="text" class='series_name' id='series<?=$serie->getId()?>' value="<?=$serie->getName()?>"></td>
			<td><input type="text" class='volume' id='volume<?=$serie->getId()?>' value="<?=$serie->getVolume()?>"></td>
			<td><?=$serie->getYear()?></td>
			<td><?=$serie->getFirstIssue()?></td>
			<td><?=$serie->getLastIssue()?></td>
			<td><?=$serie->getComicvineId()?></td>
			<td id='comicvine<?=$serie->getComicvineIdFull()?>' class='comicvine-link'><?=$serie->getComicvineIdFull()?></td><?php /* TODO: MAKE THIS A LINK TO COMICVINE-API */ ?>
			<td><?=$serie->getIssueCount()?></td>
			<td><span class='delete' id='delete<?=$serie->getId()?>' data-series-text='<?=$serie->getDisplayText()?>' data-series-issues='<?=$serie->getIssueCount()?>'><i class='fa fa-times'></i></span></td>
		</tr>
<?php } ?>
	</tbody>
	<tfoot>
		<tr>
			<th> </th>
			<th>id</th>
			<th>title</th>
			<th>volume</th>
			<th>year</th>
			<th>first</th>
			<th>last</th>
			<th>comicvine (short)</th>
			<th>comicvine (full)</th>
			<th>usage</th>
			<th> </th>
		</tr>
	</tfoot>
</table>

<script>
$(document).ready(function(){

	//this creates an array of values for string input boxes
	$.fn.dataTable.ext.order['dom-text'] = function(settings, col){
		return this.api().column( col, {order:'index'} ).nodes().map( function(td, i){
			return $('input', td).val();
		});
	};

	//this creates an array of values for numeric input boxes, parsed as numbers
	$.fn.dataTable.ext.order['dom-text-numeric'] = function (settings, col){
		return this.api().column( col, {order:'index'} ).nodes().map( function(td, i){
			return $('input', td).val() * 1;
		});
	};

	$('#seriesTable').dataTable({
		"order": [[ 2, 'asc' ],[ 3, 'asc' ],[ 4, 'asc' ]],//i could just go title/year instead of title/vol/year
		"pageLength": <?=$pageLength?>,
		"columnDefs": [ 
			{ "orderable": false, "targets": [ 10 ] },
			{ "searchable": false, "targets": [ 10 ] },
			{ "width": '1em', "targets": [ 10 ] },
			{ "className": "dt-center", "targets": [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ] }// Center align both header and body content of columns
		],
		//and declare the input columns for the functions above
		"columns": [
			null,
			null,
			{ "orderDataType": "dom-text", type: 'string' },
			{ "orderDataType": "dom-text-numeric" },
			null,
			null,
			null,
			null,
			null,
			null,
			null
		]
	});

	$('#seriesTable').on('change', '.series_name', function(){
		console.warn('onChange', this);
		var element_id = $(this).attr('id');
		var id = element_id.slice(6);
		console.warn('id', id);
		var new_name = $(this).val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { series_name_change: id, new_name: new_name } 
		}).done(function(data){
			console.warn('series name changed');
		});
	});

	$('#seriesTable').on('change', '.volume', function(){
		console.warn('onChange', this);
		var element_id = $(this).attr('id');
		var id = element_id.slice(6);
		console.warn('id', id);
		var new_volume = $(this).val();
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { volume_change: id, new_volume: new_volume } 
		}).done(function(data){
			console.warn('series volume changed');
		});
	});

	$('#seriesTable').on('click', '.comicvine-link', function(){
		var element_id = $(this).attr('id');
		var id = element_id.slice(9);
		console.warn('id', id);
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { comicvine_series_id: id } 
		}).done(function(data){
			window.open(data, '_blank');
		});
	});

	$('#seriesTable').on('click', '.delete', function(){
		console.log($(this).parent());
		var element_id = $(this).attr('id');
		var series_id = element_id.slice(6);
		var series_name = $(this).attr('data-series-text');
		var issues_count = $(this).attr('data-series-issues');
		if(issues_count > 0){
			alert('you must delete all issues in this series first');
			window.location.href='/issues?ser='+series_id;
			return;
		}
		if(confirm("are you sure you wish to delete: "+series_name)){
			$(this).parent().parent().remove();
			$.ajax({
				method: 'POST',
				url: '/ajax/lookup.php',
				data: { delete_series: series_id } 
			}).done(function(data){
				if(data === 'done'){
					console.warn('series deleted');
				}else{
					alert(data);
				}
			});
		}
	});

	$('.add-issues').on('click', function(){
		window.location.href = '/addIssues';
	});
	
	$('.add-series').on('click', function(){
		window.location.href = '/addSeries';
	});

	$(".popup-on-hover").hover(function(){
		console.warn('hover', this);
		$(this).show();
	});

	//https://stackoverflow.com/questions/21609257/jquery-datatables-scroll-to-top-when-pages-clicked-from-bottom
	$('#seriesTable').on('page.dt', function() {
		$('html, body').animate({ scrollTop: $(".dataTables_wrapper").offset().top }, 'slow');
		$('thead tr th:first-child').focus().blur();
	});

});
</script>