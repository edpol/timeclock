<?php
/*
	These database functions are in a seperate class 
	so we can use a different database to log on to site
*/
require_once(LIB_PATH.DS.'connect_utilities.php');

class utilities {

	public  $last_query;
	private $connection;
	private $magic_quotes_active;
	private $real_escape_string_exists;

	function __construct() {
		$this->open_connection(); 
		$this->magic_quotes_active = get_magic_quotes_gpc();
		$this->real_escape_string_exists = function_exists( "mysqli_real_escape_string" );
	}

	public function open_connection() {
		$this->connection = mysqli_connect(LOGIN_SERVER,LOGIN_USER,LOGIN_PASS,LOGIN_DB);
		if (!$this->connection) {
			die("Database connection failed (" . LOGIN_DB . "): " . mysqli_connect_error() . " (" . mysqli_connect_errno() . ")");
		}
	}

	public function query($sql) {
		$this->last_query = $sql;
		$result = mysqli_query($this->connection, $sql);
		$this->confirm_query($result);
		return $result;
	}

	private function confirm_query($result) {
		if (!$result) {
			$output = "Database query failed: " . mysqli_error() . "<br /><br />";
			$output .= "Last SQL query: " . $this->last_query;
			die( $output );
		}
	}

	public function close_connection() {
		if(isset($this->connection)) {
			mysqli_close($this->connection);
			unset($this->connection);
		}
	}

	public function affected_rows() {
		return mysqli_affected_rows($this->connection);
	}

	public function num_rows($result_set) {
		return mysqli_num_rows($result_set);
	}

	public function fetch_array($result) {
//		return mysqli_fetch_assoc($result);
		return mysqli_fetch_array($result, MYSQLI_ASSOC);
	}

	// stripslashes better, addslashes better than nothing
	public function escape_value( $value ) {
		if( $this->real_escape_string_exists ) { // PHP v4.3.0 or higher
			// undo any magic quote effects so mysqli_real_escape_string can do the work
			if(  $this->magic_quotes_active )  { $value = stripslashes( $value ); }
			$value = mysqli_real_escape_string( $this->connection, $value );
		} else { // before PHP v4.3.0
			// if magic quotes aren't already on then add slashes manually
			if( !$this->magic_quotes_active ) { $value = addslashes( $value ); }
			// if magic quotes are active, then the slashes already exist
		}
		return $value;
	}

	public function find_user_by_username($username) {	
		$safe_username = $this->escape_value($username);
		$query  = "SELECT * ";
		$query .= "FROM users ";
		$query .= "WHERE username = '{$safe_username}' ";
		$query .= "LIMIT 1";
		$user_set = $this->query($query);
		if($user = $this->fetch_array($user_set)) {
			return $user;
		} else {
			return null;
		}
	}

	public function sql_prep($string) {
		$escaped_string = mysqli_real_escape_string($this->connection, $string);
		return $escaped_string;
	}

	public function attempt_login($username, $password) {
		$user = $this->find_user_by_username($username);
		if ($user) {
			// found user, now check password
			if ($this->password_check($password, $user["hashed_password"])) {
				// password matches
				return $user;
			} else {
				// password does not match
				return false;
			}
		} else {
			// user not found
			return false;
		}
	}

	private function password_check($password, $existing_hash) {
		// existing hash contains format and salt at start
		$hash = crypt($password, $existing_hash);
		if ($hash === $existing_hash) {
			return true;
		} else {
			return false;
		}
	}

	public function find_all_users() {
		$query  = "SELECT * ";
		$query .= "FROM users ";
		$query .= "ORDER BY username ASC";
		$user_set = $this->query($query);
		return $user_set;
	}

	public function find_user_by_id($id) {
		$query  = "SELECT * ";
		$query .= "FROM users ";
		$query .= "WHERE userid = '{$id}' ";
		$query .= "LIMIT 1";
		$user_set = $this->query($query);
		if($user = mysqli_fetch_assoc($user_set)) {
			return $user;
		} else {
			return null;
		}
	}

	public function insert_id() {
		// get the last id inserted over the current db connection
		return mysqli_insert_id($this->connection);
	}

}
$utilities = new utilities();
?>