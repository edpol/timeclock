<?php
// A class to help work with Sessions
// In our case, primarily to manage logging users in and out

// Keep in mind when working with sessions that it is generally 
// inadvisable to store DB-related objects in sessions

class Session {

	private $logged_in=false;
	public $user_name;
	public $user_id;
	public $message;

	function __construct() {
		session_start();
		$this->checkMessage();
		$this->checkLogin();
		if($this->logged_in) {
			// actions to take right away if user is logged in
		} else {
			// actions to take right away if user is not logged in
		}
	}

	public function isLoggedIn() {
		return $this->logged_in;
	}

	public function login($user) {
		// database should find user based on username/password
		if($user) {
			$this->user_id = $_SESSION['user_id'] = $user["userid"];
			$this->logged_in = true;
			$this->user_name = $_SESSION['user_name'] = trim($user["fname"]) . " " . trim($user["lname"]);
		}
	}

	public function logout() {
		unset($_SESSION['user_id']);
		unset($this->user_id);
		$this->logged_in = false;
	}

	private function checkLogin() {
		if(isset($_SESSION['user_id'])) {
			$this->user_id = $_SESSION['user_id'];
			$this->logged_in = true;
			if(isset($_SESSION['user_name'])) {
				$this->user_name = $_SESSION['user_name'];
			}
		} else {
			unset($this->user_id);
			$this->logged_in = false;
		}
	}
  
	public function message($msg="") {
		if(!empty($msg)) {
			// then this is "set message"
			// make sure you understand why $this->message=$msg wouldn't work
			$_SESSION['message'] = $msg;
		} else {
			// then this is "get message"
			return $this->message;
		}
	}

	public function checkMessage() {
		// Is there a message stored in the session?
		if(isset($_SESSION['message'])) {
			// Add it as an attribute and erase the stored version
			$this->message = $_SESSION['message'];
			unset($_SESSION['message']);
		} else {
			$this->message = "";
		}
	}

}

$session = new Session();
$message = $session->message();

?>