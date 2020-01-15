			<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
				<div class='full-width'>
					<div class='dropdown-on-hover'>
						<a class="navbar-brand" href="/home.php"><?=User::getUserHeader($db, $_SESSION['siteuser'])?></a>
						<div class="dropdown-content bg-dark">
							<a href="/grading" class='bg-dark'>Grading Info</a>
						</div>
					</div>
					<div class='dropdown-on-hover'>
						<a class="navbar-brand" href="#">Print</a>
						<div class="dropdown-content bg-dark">
							<a href="#" id='print-summary'>Summary</a>
							<a href="#" id='print-detailed'>Detailed</a>
							<a href="#" id='print-missing'>Missing List</a>
						</div>
					</div>
<?php if($alreadyLoggedIn){ ?>
					<div class='admin-buttons'>
						<button class='btn' id='issues'>Issues</button>
						<button class='btn' id='series'>Series</button>
						<button class='btn' id='collections'>Collections</button>
					</div>
<?php } ?>
				</div>
			</nav>

<script>
$(document).ready(function(){
	$('#issues').on('click', function(){
		window.location.href = '/issues';
	});

	$('#series').on('click', function(){
		window.location.href = '/series';
	});

	$('#collections').on('click', function(){
		window.location.href = '/collections';
	});

	$('#print-summary').on('click', function(){
		callPrint('summary');
	});

	$('#print-detailed').on('click', function(){
		callPrint('detailed');
	});

	$('#print-missing').on('click', function(){
		callPrint('missing');
	});

});

function callPrint(type){
	console.warn('callPrint', type);
	window.location.href = '/print?type='+type;
}
</script>