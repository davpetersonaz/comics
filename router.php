<?php


//TODO: load a cache of all issues/series/collections only if the current time is after the last-update-time (new db table w/ 1 row)
//PROBABLY DO THAT AFTER A CHECKIN, SO I CAN REVERT IF ITS NOT RIGHT



include_once('config.php');

$p = (isset($_GET['p']) ? $_GET['p'] : '');
logDebug('router, p='.$p);
if(!$p){//default
	$p = 'home';
}elseif(substr($p, -4) === '.php'){//strip .php
	$p = (substr($p, 0, -4));
}
$queryArray = explode('/', $p);
$page = $queryArray[0];
logDebug('page='.$page);

if(file_exists(HTMLS_PATH.$page.'.php') !== true){
	logDebug('page doesnt exist: '.$page);
	$page = 'home';//default to home
}else{
	//just drop through, $page is already set to a valid php file
}

//and go on to display the page requested