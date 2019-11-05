<?php $pageLength = (isset($_SESSION['table_length']['home']) && $_SESSION['table_length']['home'] > 0 ? $_SESSION['table_length']['home'] : 100); ?>

<?php if($alreadyLoggedIn){ ?>
<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-issues'>Add Issues</button>
</div>
<?php } ?>

<table id="comiclist" class="display" style="width:100%">
	<thead>
		<tr>
			<th> </th><!-- 0 - cover image -->
			<th>collection</th><!-- 1 - collection name -->
			<th>series</th><!-- 2 - comic name -->
			<th>volume</th><!-- 3 - comic volume -->
			<th>issue</th><!-- 4 - issue number -->
			<th>date</th><!-- 5 - cover date -->
			<th>grade</th><!-- 6 - comic condition -->
			<th>id</th><!-- 7 - comic's id in my db -->
			<th>collection id</th> <!-- 8 - collection id -->
			<th>series id</th><!-- 9 - link to my series-db -->
			<th>comicvine</th><!-- 10 - comicvine short-id -->
			<th>comicvine full</th><!-- 11 - comicvine long-id -->
			<th>grade abbr</th><!-- 12 - grade abbreviation -->
			<th>grade short</th><!-- 13 - grade short desc -->
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th> </th>
			<th>collection</th>
			<th>series</th>
			<th>volume</th>
			<th>issue</th>
			<th>date</th>
			<th>grade</th>
			<th>id</th>
			<th>collection id</th>
			<th>series id</th>
			<th>comicvine</th>
			<th>comicvine full</th>
			<th>grade abbr</th>
			<th>grade short</th>
		</tr>
	</tfoot>
</table>

<script>
var months = Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$(document).ready(function(){
//	var userIsAdmin = <?=($alreadyLoggedIn?'true':'false')?>;

	var comiclistdatatable = $('#comiclist').DataTable({
        "dom": 'Bfrtip',
		"processing": true,
		"serverSide": true,
		"ajax": "/ajax/listing.php",
		"pageLength": <?=$pageLength?>,
<?php if($alreadyLoggedIn){ ?>
		"order": [[ 5, 'asc' ],[ 6, 'asc' ]],
<?php }else{ ?>
		"order": [[ 1, 'asc' ],[ 2, 'asc' ],[ 3, 'asc' ],[ 5, 'asc' ],[ 4, 'asc' ]],
<?php } ?>
		"buttons": [ 'print' ],
		"columnDefs": [ 
			{ "orderable": false, "targets": [ 0, 7, 8, 9, 10, 11, 12, 13 ] },
			{ "searchable": false, "targets": [ 0, 7, 8, 9, 10, 11, 12, 13 ] },
			{ "visible": false, "targets": [ 7, 8, 9, 10, 11, 12, 13 ] },
			{ "width": '2em', "targets": [ 0 ] },
			{ "className": "dt-center", "targets": [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13 ] },// Center align both header and body content
			{
				"render": function ( data, type, row ) {
//					console.warn('data:', data, 'type:', type, 'row:', row);
//					console.warn('data 5', row[5]);
					var dateString = row[5];
					var returnDate = '&lt;unknown&gt;';
					if(dateString && dateString != 0){
						console.warn('dateString', dateString);
						var date = new Date(dateString);
						date.setHours(0);
						date.setMinutes(0);
						date.setSeconds(0);
						console.warn('date', date);
//						console.warn('date.getDate()', date.getDate());
//						console.warn('date.getMonth()', date.getMonth());
//						console.warn('months[date.getMonth()]', months[date.getMonth()]);
						if(date.getDate() > 1){
							returnDate = months[date.getMonth()]+' '+date.getDate()+', '+date.getFullYear();
						}else{
							returnDate = months[date.getMonth()]+' '+date.getFullYear();
						}
					}
					return returnDate;
				},
				"targets": [ 5 ]
			},
			{
				"render": function ( data, type, row ) {
					return '<span title="'+row[13]+'">'+row[12]+'</span>';
				},
				"targets": [ 6 ]
			}
		]
	});

	//get issue details on row-click
	$('#comiclist tbody').on('click', 'tr', function(){
		$('body').css('cursor', 'progress');
		$('body').css('pointer-events', 'none');
		var currentRowData = comiclistdatatable.row(this).data();
//		console.warn('currentRowData', currentRowData);
		var issueid = currentRowData[7];
		location.href = '/details?id='+issueid;
	});

	$(".popup-on-hover").hover(function(){
//		console.warn('hover', this);
		$(this).show();
	});

	$('.add-issues').on('click', function(){
		window.location.href = '/addIssues';
	});

	$('#comiclist tbody').on('page.dt', function() {
		$('html, body').animate({ scrollTop: $(".dataTables_wrapper").offset().top }, 'slow');
		$('thead tr th:first-child').focus().blur();
	});

});
</script>
