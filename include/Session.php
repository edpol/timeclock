<?php
// A class to help work with Sessions
// In our case, primarily to manage logging users in and out

// Keep in mind when working with sessions that it is generally 
// inadvisable to store DB-related objects in sessions

class Session {

	private bool $logged_in=false;
	public string $user_name;
	public $user_id;
	public $message;

	function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkMessage();
		$this->checkLogin();
/*
		if($this->logged_in) {
			// actions to take right away if user is logged in
		} else {
			// actions to take right away if user is not logged in
		}
*/
	}

	public function isLoggedIn(): bool
    {
		return $this->logged_in;
	}

	public function login($user): void
    {
		// database should find user based on username/password
		if($user) {
			// Rotate session ID on login to prevent session fixation attacks.
			session_regenerate_id(true);
			$this->user_id = $_SESSION['user_id'] = $user["userid"];
			$this->logged_in = true;
			$this->user_name = $_SESSION['user_name'] = trim($user["fname"]) . " " . trim($user["lname"]);
		}
	}

	public function logout(): void
    {
		unset($_SESSION['user_id']);
		unset($this->user_id);
		$this->logged_in = false;
	}

	private function checkLogin(): void
    {
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

	public function checkMessage(): void
    {
		// Is there a message stored in the session?
		if(isset($_SESSION['message'])) {
			// Add it as an attribute and erase the stored version
			$this->message = $_SESSION['message'];
			unset($_SESSION['message']);
		} else {
			$this->message = "";
		}
	}

	// -------------------------------------------------------------------------
	// CSRF helpers
	// -------------------------------------------------------------------------

	/**
	 * Return (and lazily create) the per-session CSRF token.
	 */
	public function csrfToken(): string
	{
		if (empty($_SESSION['csrf_token'])) {
			$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		}
		return $_SESSION['csrf_token'];
	}

	/**
	 * Render a hidden <input> carrying the CSRF token.
	 * Call this inside every <form> that uses POST.
	 */
	public function csrfField(): string
	{
		$token = htmlspecialchars($this->csrfToken(), ENT_QUOTES);
		return "<input type='hidden' name='csrf_token' value='$token' />\n";
	}

	/**
	 * Verify the CSRF token submitted with a POST request.
	 * Exits with 403 if the token is missing or wrong.
	 */
	public function verifyCsrf(): void
	{
		$submitted = $_POST['csrf_token'] ?? '';
		if (!hash_equals($this->csrfToken(), $submitted)) {
			http_response_code(403);
			die("Invalid or missing CSRF token.");
		}
	}

}

$session = new Session();
$message = $session->message();
