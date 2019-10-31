			<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
				<div class='full-width'>
					<div class='dropdown-on-hover'>
						<a class="navbar-brand" href="/home.php"><?=User::getUserHeader($db, $_SESSION['siteUser'])?></a>
						<div class="dropdown-content bg-dark">
							<a href="/grading" class='bg-dark'>Grading Info</a>
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

<style>
	.navbar.navbar-expand-md{
		height: 56px;
		/*overflow: hidden;*/
	}
	.admin-buttons{
		display: inline-block;
		padding-top: 4px;
	}
	.dropdown-on-hover{
		display: inline-block;
		overflow: hidden;
		padding-top: 2px;
	}
	.dropdown-content{
		border-radius: 3px;
		color: white;
		display: none;
		padding: 10px 20px;
	}
	.dropdown-content a{
		color: white;
		display: block;
		text-decoration: none;
	}
	.dropdown-on-hover:hover .dropdown-content{
		display: block;
		position: absolute;
		z-index: 1;
	}
	.dropdown-on-hover:hover .dropdown-content a{
		/*z-index: 1;*/
	}
</style>
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
});
</script>