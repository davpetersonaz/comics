<!doctype html>
<html lang="en">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- css -->
		<link href="/css/bootstrap4/bootstrap.min.css?v<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/css/bootstrap4/bootstrap.min.css')?>" rel="stylesheet" type="text/css"/>
		<link href="/css/datatables-1.10.18/jquery.dataTables.min.css?v<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/css/datatables-1.10.18/jquery.dataTables.min.css')?>" rel="stylesheet" type="text/css"/>
<?php if($page === 'home'){ ?>
		<link href="/css/Buttons-1.6.1/buttons.dataTables.min.css?v<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/css/Buttons-1.6.1/buttons.dataTables.min.css')?>" rel="stylesheet" type="text/css"/>
<?php } ?>
<?php if($page === 'addIssues'){ ?>
		<link href="/css/tempusdominus-bootstrap-4.min.css?v<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/css/tempusdominus-bootstrap-4.min.css')?>" rel="stylesheet" type="text/css"/>
<?php } ?>
		<link href="/images/fontawesome-5.2.0/css/all.css?v<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/images/fontawesome-5.2.0/css/all.css')?>" rel="stylesheet" type="text/css"/> <!-- i know, should probably split js/css into their own folders, but why? -->
		<link href="/css/core_style.css?v<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/css/core_style.css')?>" rel="stylesheet" type="text/css"/>
		<link href="/css/style.css?v<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css')?>" rel="stylesheet" type="text/css"/>
<?php if($page === 'print'){ ?>
		<link href="/css/print.css?v<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/css/print.css')?>" media="print"  rel="stylesheet" type="text/css"/>
<?php } ?>

		<!-- TODO: Place at the end of the document so the pages load faster -->
		<script src="/js/jquery/jquery-3.3.1.min.js" type="text/javascript"></script>
		<script src="/js/bootstrap4/bootstrap.min.js" type="text/javascript"></script>
		<script src="/js/datatables-1.10.18/jquery.dataTables.min.js" type="text/javascript"></script>
<?php if($page === 'home'){ ?>
		<script src="/js/Buttons-1.6.1/dataTables.buttons.min.js" type="text/javascript"></script>
		<script src="/js/Buttons-1.6.1/buttons.print.min.js" type="text/javascript"></script>
<?php } ?>
<?php if($page === 'addIssues'){ ?>
		<script src="/js/moment.js" type="text/javascript"></script>
		<script src="/js/tempusdominus-bootstrap-4.min.js" type="text/javascript"></script>
<?php } ?>
		<!-- title -->\
		<title><?=User::getUserHeader($db, $_SESSION['siteuser'])?></title>
	</head>
	<body>
		<header>
			<?php include('navbar.php'); ?>
		</header>
