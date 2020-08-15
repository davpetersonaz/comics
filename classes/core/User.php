<?php
class User{
	
	public static function getUserHeader(DB $db, $user_id){
		$headerDisplay = $db->getUserHeader($user_id);
		return ($headerDisplay ? $headerDisplay : 'Comic List');
	}
	
	public function isDomainUser($user_id){
		$this->db->getUserByNumber($user_id);
	}
	
	public function login($username=false, $password=false){
		logDebug('user->login');
		$user_id = $this->db->verifyUser($username, $password);
		logDebug('user_id: '.var_export($user_id, true));
		if($user_id && !empty($_SESSION['siteuser']) && intval($user_id) !== intval($_SESSION['siteuser'])){
			logDebug('tried to login to incorrect domain');
			return false;
		}elseif($user_id){
			logDebug('SESSION: '.var_export($_SESSION, true));
			$_SESSION['loggedin'] = true;
			$_SESSION['username'] = $username;
			$_SESSION['siteuser'] = $user_id;
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