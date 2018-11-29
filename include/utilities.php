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
			$output = "Database query failed: " . mysqli_error($this->connection) . "<br /><br />";
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

	private function generate_salt($length) {
		// Not 100% unique, not 100% random, but good enough for a salt
		// MD5 returns 32 characters
		$unique_random_string = md5(uniqid(mt_rand(), true));

		// Valid characters for a salt are [a-zA-Z0-9./]
		$base64_string = base64_encode($unique_random_string);

		// But not '+' which is valid in base64 encoding
		$modified_base64_string = str_replace('+', '.', $base64_string);

		// Truncate string to the correct length
		$salt = substr($modified_base64_string, 0, $length);

		return $salt;
	}

	private function password_encrypt($password) {
		$hash_format = "$2y$10$";   // Tells PHP to use Blowfish with a "cost" of 10
		$salt_length = 22; 			// Blowfish salts should be 22-characters or more
		$salt = $this->generate_salt($salt_length);
		$format_and_salt = $hash_format . $salt;
		$hash = crypt($password, $format_and_salt);
		return $hash;
	}

	public function password_update($password, $userid) {
		$hashed_password = $this->password_encrypt($password);
		$query  = "UPDATE users SET ";
		$query .= "hashed_password = '{$hashed_password}' ";
		$query .= "WHERE userid = {$userid} ";
		$query .= "LIMIT 1";
		$result = $this->query($query);

		if ($result && $this->affected_rows() == 1) {
			// Success
			$_SESSION["message"] = "User ID {$userid} Password updated.";
			return true;
		} else {
			// Failure
			$_SESSION["message"] = "password_update for user id {$userid} failed.";
			return false;
		}
	}

	public function user_add($array) {
		extract($array);
		$hashed_password = $this->password_encrypt($password);
		$sql  = "INSERT INTO users ( username, hashed_password, fname, lname ) VALUES ";
		$sql .= "('{$username}',  '{$hashed_password}', '{$fname}', '{$lname}') ";
		$result = $this->query($sql);
		if ($result && $this->affected_rows() == 1) {
			$message ="Added username {$username} {$fname} {$lname} ";
		} else {
			$message ="user_add failed - username {$username} {$fname} {$lname} ";
		}
		return $message;
/*
		i need a view that will ask for the information.
		it is just like update except that username and password are requirements
*/
	}

	// mysqli_query returns FALSE on failure
	public function delete_user($userid) {
		$query  = "DELETE FROM USERS WHERE userid = '{$userid}' LIMIT 1";
		$result = $this->query($query);
		if ($result && $this->affected_rows() == 1) {
			$message = "Deleted User " . $userid . "<br />\n";
		} else {
			$message = "Deleting User " . $userid . " <u>FAILED<u> <br />\n";
		}
		return $message;
	}

	public function user_update($array) {
		extract($array);
		$row = $this->find_user_by_id($userid);

		$message = "";
		if ($fname <> $row["fname"] || $lname <> $row["lname"]) {
			$sql = "update users set fname='" . $fname . "', lname='" . $lname . "' where userid=" . $userid;
			$result = $this->query($sql);

			if ($result && $this->affected_rows() == 1) {
				$message .= "User ID {$userid} Name updated.<br />\n";
			} else {
				$message .= "user_update for user id {$userid} failed.<br />\n";
			}
		}

		if (isset($password) && !empty($password)) {
			$hashed_password = $this->password_encrypt($password);
			if ($hashed_password <> $row["hashed_password"]) {
				$this->password_update($password, $userid);
				$message .= $_SESSION["message"];
				unset ($_SESSION["message"]);
			}
		}
		return $message;
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
		if($user = $this->fetch_array($user_set)) {
			return $user;
		} else {
			return null;
		}
	}


	/*
	 *    list all users with manage choice and add choice and delete choice
	 */
	public function user_table ($title="Manage Users",$edit_page="",$delete_page="") {
		$result = $this->find_all_users();
		$output  = "<table border=1>\n";
		$output .= "\t\t\t\t\t\t\t<caption>{$title}</caption>\n";
		$output .= "\t\t\t\t\t\t\t<tr>\n";
		$output .= "\t\t\t\t\t\t\t\t<th style='text-align:center;'>Name</th>\n";
		$output .= "\t\t\t\t\t\t\t\t<th style='text-align:center;'>Username</th>\n";
		$output .= "\t\t\t\t\t\t\t\t<th colspan='2' style='text-align:center;'>Actions</th>\n";
		$output .= "\t\t\t\t\t\t\t</tr>\n";

		while($user = $this->fetch_array($result)) { 
			$name = trim($user["fname"]) . " " . trim($user["lname"]);
			$output .= "\t\t\t\t\t\t\t<tr>\n";
			$output .= "\t\t\t\t\t\t\t\t<td>" . htmlentities($name) . "</td>\n";
			$output .= "\t\t\t\t\t\t\t\t<td>" . htmlentities($user["username"]). "</td>\n";
			$output .= "\t\t\t\t\t\t\t\t<td><button type='submit' class='edit_up' name='edit'   value='" . urlencode($user["userid"]) . "' formaction='{$edit_page}'>Edit</button></td>\n";
			$output .= "\t\t\t\t\t\t\t\t<td><button type='submit' class='del_up'  name='delete' value='" . urlencode($user["userid"]) . "' formaction='{$delete_page}'>X</button></td>\n";
			$output .= "\t\t\t\t\t\t\t</tr>\n";
		}
		$output .= "\t\t\t\t\t\t</table>\n";
		return $output;
	}

	public function insert_id() {
		// get the last id inserted over the current db connection
		return mysqli_insert_id($this->connection);
	}

}
$utilities = new utilities();
?>