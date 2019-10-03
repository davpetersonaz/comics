<?php
logDebug('details GET: '. var_export($_GET, true));
$issue_id = $_GET['id'];//my db issue id, not comicvine
$issue = new Issue($db, $curl, $issue_id);
logDebug('comic issue details: '.var_export($issue, true));
//if(!$issue->comicvine_series_id){
//	//TODO: can't find, put up "unknown" page
//	logDebug('unknown issue: '.var_export($issue, true));
//	exit;
//}

//check if we should retrieve the comicvine issue id for the comics-issue-db
if($issue && !$issue->comicvine_issue_id && $issue->comicvine_series_id){
	logDebug('comicvine-issue-id is false, get further details');
	$issue->updateIssueDetails();
}

logDebug('formatting details: '.var_export($issue, true));
$comicvine_issue_id = $issue->comicvine_issue_id;
$comicvine_link = $issue->comicvine_url;//https://comicvine.gamespot.com/the-avengers-1-the-coming-of-the-avengers/4000-6686/
$cover_date = new DateTime($issue->cover_date);
$cover_date = (intval($cover_date->format('d')) === 1 ? $cover_date->format('F Y') : $cover_date->format('F d, Y'));
$characters = $issue->getCharactersArray();
logDebug('characters: '.var_export($characters, true));
$creators = $issue->getCreatorsArray();
logDebug('creators: '.var_export($creators, true));
$character_firsts = $issue->getFirstAppearanceCharactersArray();
$object_firsts = $issue->getFirstAppearanceObjectsArray();
$team_firsts = $issue->getFirstAppearanceTeamsArray();
$character_died = $issue->getCharactersDiedArray();
$image_full = $issue->image_full;
$grade = $grades->getGrade($issue->grade);
?>

<div class='details row'>
	<div class='col-xs-12 col-sm-6'>
		<div class="image-border">
			<img class='img-responsive float-right' src='<?=$image_full?>'>
		</div>
	</div>
	<div class='col-xs-12 col-sm-6'>
		
		<h5 class='collection'><span class='smaller'>collection:</span> <?=$issue->collection_name?> <?=$issue->chrono_index?></h5>
		<h1><?=$issue->title?><span class='smaller'> (vol. <?=$issue->volume?>)</span></h1>
		<h4>Issue <?=($issue->issue == '88888' ? '<i class="fa fa-infinity"></i>' : $issue->issue)?></h4>
		<h5><?=$cover_date?></h5>
		<h5 title='<?=$grade['long_desc']?>'>condition: <?=$grade['name']?></h5>
		<h3><?=$issue->issue_title?></h3>
		Comicvine link: <a href='<?=$comicvine_link?>'><?=$comicvine_link?></a>
		
		<table class="creators">
			<tr><td colspan=2 class="list-header">Creators:</td></tr>
<?php foreach($creators as $creator=>$position){ ?>
			<tr>
				<td class='table-left-column'>
					<b><?=$creator?></b>:
				</td>
				<td class='table-right-column'>
					<?=$position?>
				</td>
			</tr>
<?php } ?>
		</table>
		
		<table class="characters">
			<tr><td colspan=2 class="list-header">Characters:</td></tr>
<?php for($i=0; $i<count($characters); ){ ?>
	<?php if($i%2 === 0){ ?>
			<tr>
	<?php } ?>
				<td class='table-left-column'>
					<?=$characters[$i++]?>
				</td>
				<td class='table-right-column'>
					<?=(isset($characters[$i]) ? $characters[$i++] : '')?>
				</td>
	<?php if($i%2 === 0){ ?>
			</tr>
	<?php } ?>
<?php } ?>
		</table>
		
		<p><?=$issue->synopsis?></p>
		
<?php if($character_firsts){ ?>
		Character first appearance(s):<br />
	<?php foreach($character_firsts as $character){ ?>
		<?=$character?><br />
	<?php } ?>
<?php } ?>
		
<?php if($team_firsts){ ?>
		Team first appearance(s):<br />
	<?php foreach($team_firsts as $team){ ?>
		<?=$team?><br />
	<?php } ?>
<?php } ?>
		
<?php if($object_firsts){ ?>
		Object first appearance(s):<br />
	<?php foreach($object_firsts as $object){ ?>
		<?=$object?><br />
	<?php } ?>
<?php } ?>
		
<?php if($character_died){ ?>
		Character Death(s):<br />
	<?php foreach($character_died as $character){ ?>
		<?=$character?><br />
	<?php } ?>
<?php } ?>
		
	</div>
</div>
