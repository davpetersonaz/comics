<?php
if(!$alreadyLoggedIn){ ?><script>window.location.href = '/';</script><?php }
logDebug('series_details GET: '. var_export($_GET, true));
$series_id = $_GET['id'];//my db series id, not comicvine's
$series = new Series($db, $curl, $series_id);
//logDebug('series details: '.var_export($series, true));
$issues = ($series->getFirstIssue() === $series->getLastIssue() 
			? 'Issue: '.$series->getFirstIssue() 
			: 'Issues: '.$series->getFirstIssue().' - '.$series->getLastIssue().' ('.$series->getSeriesIssueCount().')'
);
$comicvine_url = $series->getComicvineUrl();
?>	

<div class='details row'>
	<div class='col-xs-12 col-sm-6'>
		<div class="image-border">
			<img class='img-responsive float-right' src='<?=$series->getImageFull()?>'>
		</div>
	</div>
	<div class='col-xs-12 col-sm-6'>
		<h1><?=$series->getName()?></h1>
		<h4>Year: <?=$series->getYear()?></h4>
		<h5>Volume <?=$series->getVolume()?></h5>
		<h4>Publisher: <?=$series->getPublisher()?></h4>
		<h5>Issues: <?=$issues?></h5>
		Comicvine link: <a href='<?=$comicvine_url?>' target='_blank'><?=$comicvine_url?></a>
		<br /><br />
		<p><button class='btn btn-primary bg-dark comicvine-regen'><span style="font-size:smaller;">Regen Comicvine</span></button></p>
	</div>
</div>


<script>
$(document).ready(function(){

	$('.comicvine-regen').on('click', function(){
		$.ajax({
			method: 'POST',
			url: '/ajax/lookup.php',
			data: { comicvine_regen: true, series_id: <?=$series_id?> } 
		}).done(function(data){
			if(data === 'done'){
				alert('regen completed');
				window.location.href = '/series_details?id=<?=$series_id?>';
			}else{
				alert(data);
			}
		});
	});

});
</script>
