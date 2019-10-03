<?php
include_once('../../config.php');

if(isset($_POST['username'], $_POST['password'])){
	$username = $_POST['username'];
	$password = $_POST['password'];
	if(!$username || !$password){
		echo "the username and password cannot be empty";
		exit;
	}
	$user = new User($db);
	if($user->login($username, $password)){
		$_SESSION["loggedin"] = true;
		echo 'done';
	}else{
		echo 'invalid';
	}
}

exit;