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

if(isset($_POST['logout'])){
	$_SESSION = array();//unset all session variables
	//delete the session cookie, this will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
	session_destroy();	//destroy the session
}

exit;