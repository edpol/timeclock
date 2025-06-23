<?php
/*
	These database functions are in a seperate class 
	so we can use a different database to log on to site
*/
require_once(LIB_PATH.DS.'connect_utilities.php');

class Utilities extends Common {

	function __construct() {
		parent::__construct();
		$this->openConnection(); 
	}

	public function attemptLogin($username, $password) {
		$user = $this->findUserByUsername($username);
		if ($user) {
			// found user, now check password
			if ($this->passwordCheck($password, $user["hashed_password"])) {
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

	private function passwordCheck($password, $existing_hash) {
		// existing hash contains format and salt at start
		$hash = crypt($password, $existing_hash);
		if ($hash === $existing_hash) {
			return true;
		} else {
			return false;
		}
	}

	private function generateSalt($length) {
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

	private function passwordEncrypt($password) {
		$hash_format = "$2y$10$";   // Tells PHP to use Blowfish with a "cost" of 10
		$salt_length = 22; 			// Blowfish salts should be 22-characters or more
		$salt = $this->generateSalt($salt_length);
		$format_and_salt = $hash_format . $salt;
		$hash = crypt($password, $format_and_salt);
		return $hash;
	}

	// Hashing is a one way function
	public function passwordUpdate($password, $userid) {
		$hashed_password = $this->passwordEncrypt($password);
		$sql  = "UPDATE users SET ";
		$sql .= "hashed_password = '{$hashed_password}' ";
		$sql .= "WHERE userid = {$userid} ";
		$sql .= "LIMIT 1";
		$result = $this->q($sql);

		if ($result && $this->affectedRows() == 1) {
			// Success
			$_SESSION["message"] = "User ID {$userid} Password updated.";
			return true;
		} else {
			// Failure
			$_SESSION["message"] = "passwordUpdate for user id {$userid} failed.";
			return false;
		}
	}

	public function userAdd($array) {
		extract($array);
		$hashed_password = $this->passwordEncrypt($password);
		$sql  = "INSERT INTO users ( username, hashed_password, fname, lname ) VALUES ";
		$sql .= "('{$username}',  '{$hashed_password}', '{$fname}', '{$lname}') ";
		$result = $this->q($sql);
		if ($result && $this->affectedRows() == 1) {
			$message ="Added username {$username} {$fname} {$lname} ";
		} else {
			$message ="userAdd failed - username {$username} {$fname} {$lname} ";
		}
		return $message;
/*
		i need a view that will ask for the information.
		it is just like update except that username and password are requirements
*/
	}

	// mysqli_query returns FALSE on failure
	public function deleteUser($userid) {
		$sql  = "DELETE FROM USERS WHERE userid = '{$userid}' LIMIT 1";
		$result = $this->q($sql);
		if ($result && $this->affectedRows() == 1) {
			$message = "Deleted User " . $userid . "<br />\n";
		} else {
			$message = "Deleting User " . $userid . " <u>FAILED<u> <br />\n";
		}
		return $message;
	}

	public function userUpdate($array) {
		extract($array);
		$row = $this->findUserById($userid);

		$message = "";
		if ($fname <> $row["fname"] || $lname <> $row["lname"]) {
			$sql = "update users set fname='" . $fname . "', lname='" . $lname . "' where userid=" . $userid;
			$result = $this->q($sql);

			if ($result && $this->affectedRows() == 1) {
				$message .= "User ID {$userid} Name updated.<br />\n";
			} else {
				$message .= "userUpdate for user id {$userid} failed.<br />\n";
			}
		}

		if (isset($password) && !empty($password)) {
			$hashed_password = $this->passwordEncrypt($password);
			if ($hashed_password <> $row["hashed_password"]) {
				$this->passwordUpdate($password, $userid);
				$message .= $_SESSION["message"];
				unset ($_SESSION["message"]);
			}
		}
		return $message;
	}

	public function findUserByUsername($username) {	
		$safe_username = $this->escapeValue($username);
		$sql  = "SELECT * FROM users WHERE username = '{$safe_username}' LIMIT 1 ";
		$user_set = $this->q($sql);
		if($user = $this->fetchArray($user_set)) {
			return $user;
		} else {
			return null;
		}
	}

	public function findAllUsers() {
		$sql = "SELECT * FROM users ORDER BY username ASC ";
		$user_set = $this->q($sql);
		return $user_set;
	}

	public function findUserById($id) {
		$sql = "SELECT * FROM users WHERE userid = '{$id}' LIMIT 1 ";
		$user_set = $this->q($sql);
		if($user = $this->fetchArray($user_set)) {
			return $user;
		} else {
			return null;
		}
	}

	/*
	 *    list all users with manage choice and add choice and delete choice
	 */
	public function userTable ($title="Manage Users",$edit_page="",$delete_page="") {
		$result = $this->findAllUsers();
		$output  = "<table border=1>\n";
		$output .= "\t\t\t\t\t\t\t<caption>{$title}</caption>\n";
		$output .= "\t\t\t\t\t\t\t<tr>\n";
		$output .= "\t\t\t\t\t\t\t\t<th style='text-align:center;'>Name</th>\n";
		$output .= "\t\t\t\t\t\t\t\t<th style='text-align:center;'>Username</th>\n";
		$output .= "\t\t\t\t\t\t\t\t<th colspan='2' style='text-align:center;'>Actions</th>\n";
		$output .= "\t\t\t\t\t\t\t</tr>\n";

		while($user = $this->fetchArray($result)) { 
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

	public function insertId() {
		// get the last id inserted over the current db connection
		return mysqli_insert_id($this->connection);
	}

}
$utilities = new Utilities();
?>