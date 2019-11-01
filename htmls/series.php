<?php 


//TODO: i dont think REGEN is working


if(!$alreadyLoggedIn){ ?><script>window.location.href = '/';</script><?php }
$pageLength = (isset($_SESSION['table_length']['home']) && $_SESSION['table_length']['home'] > 0 ? $_SESSION['table_length']['home'] : 100);
$seriesChoice = (isset($_GET['ser']) ? $_GET['ser'] : false);
$getParams = ($seriesChoice ? "?ser={$seriesChoice}" : '');
?>

<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-series'>Add Series</button>
	<button class='btn btn-primary bg-dark add-issues'>Add Issues</button>
	<button class='btn btn-primary bg-dark comicvine-regen'><span style="font-size:smaller;">Regen Comicvine</span></button>
</div>

<table id='seriesTable' class="display">
	<thead>
		<tr>
			<th> </th>
			<th>id</th>
			<th>title</th>
			<th>volume</th>
			<th>year</th>
			<th>publisher</th>
			<th>first</th>
			<th>last</th>
			<th>comicvine (short)</th>
			<th>comicvine (full)</th>
			<th>usage</th>
			<th> </th>
			<th> </th>
		</tr>
	</thead>
	<tbody>
	</tbody>
	<tfoot>
		<tr>
			<th> </th>
			<th>id</th>
			<th>title</th>
			<th>volume</th>
			<th>year</th>
			<th>publisher</th>
			<th>first</th>
			<th>last</th>
			<th>comicvine (short)</th>
			<th>comicvine (full)</th>
			<th>usage</th>
			<th> </th>
			<th> </th>
		</tr>
	</tfoot>
</table>

<script>
$(document).ready(function(){

	var seriesTable = $('#seriesTable').dataTable({
		"ajax": "/ajax/series.php<?=$getParams?>",
		"dom": 'frtip',
		"order": [[ 2, 'asc' ],[ 3, 'asc' ],[ 4, 'asc' ]],//i could just go title/year instead of title/vol/year
		"pageLength": <?=$pageLength?>,
		"processing": true,
		"searchDelay": 1000,
		"serverSide": true,
		"columnDefs": [ 
			{ "orderable": false, "targets": [ 0, 11, 12 ] },
			{ "searchable": false, "targets": [ 0, 10, 11, 12 ] },
			{ "visible": false, "targets": [ 12 ] },
			{ "width": '1em', "targets": [ 11 ] },
			{ "className": "dt-center", "targets": [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ] }// Center align both header and body content of columns
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
		window.open('https://comicvine.gamespot.com/volume/'+id+'/', '_blank');
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
	
	$('#seriesTable tbody').on('click', 'td', function(){
//		alert(seriesTable.cell(this).data());//this is invalid, cell() is not a function
	});

	$('.add-issues').on('click', function(){
		window.location.href = '/addIssues';
	});

	$('.add-series').on('click', function(){
		window.location.href = '/addSeries';
	});
	
	$('.comicvine-regen').on('click', function(){
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { comicvine_regen: true } 
		}).done(function(data){
			if(data === 'done'){
				alert('regen completed');
				window.location.href = '/series';
			}else{
				alert(data);
			}
		});
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
