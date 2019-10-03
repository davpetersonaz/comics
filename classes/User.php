<?php
class User{
	
	public function login($username=false, $password=false){
		logDebug('user->login');
		$loggedIn = $this->db->verifyUser($username, $password);
		logDebug('loggedIn: '.var_export($loggedIn, true));
		if($loggedIn){
			logDebug('SESSION: '.var_export($_SESSION, true));
//			logDebug('session_start...');
//			session_start();
			//TODO: do i have to set these if the session is already started??
			$_SESSION['loggedin'] = true;
			$_SESSION['username'] = $username;
			return true;
		}else{
			error_log('login failed: '.var_export($username, true));
			return false;
		}
	}
	
	public function __construct($db){
		$this->db = $db;
	}
	
	protected $db = false;
}