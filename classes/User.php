<?php
class User{
	
	public function login($username=false, $password=false){
		logDebug('user->login');
		$user_id = $this->db->verifyUser($username, $password);
		logDebug('user_id: '.var_export($user_id, true));
		if($user_id){
			logDebug('SESSION: '.var_export($_SESSION, true));
			$_SESSION['loggedin'] = true;
			$_SESSION['username'] = $username;
			$_SESSION['user_id'] = $user_id;
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