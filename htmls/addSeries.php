<div class='btn-above-table'>
	<button class='btn btn-primary bg-dark add-issues'>Add Issues</button>
</div>

<h2 class='add-header'>Add Series</h2>

<form id='addSeriesForm' method='POST' action=''>
	<table id='addSeriesTable'>
		<thead>
			<tr>
				<td>Series</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<input type='text' name='series' class='series' placeholder='Series Name' autofocus="autofocus"/>
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
		form.method = 'POST';
		form.action = 'addSeriesSelect';
		var hiddenField1 = document.createElement('input');
		hiddenField1.type = 'hidden';
		hiddenField1.name = 'series';
		hiddenField1.value = $(inputs[0]).val();  
		form.appendChild(hiddenField1);
		document.body.appendChild(form);
		console.warn('form', form);
//		alert('submitting form');
		form.submit();//to addSeriesSelect
	});
	
	//focus on first input
	$('form:first *:input[type!=hidden]:first').focus();		

	$('.add-issues').on('click', function(){
		window.location.href = '/addIssues';
	});
	
});
</script>
