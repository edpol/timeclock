<?php
/*
    These database functions are in a separate class
    so we can use a different database to log on to site
*/
require_once(LIB_PATH.DS.'connect_utilities.php');

class Utilities extends Common {

    function __construct()
    {
        parent::__construct();
        $this->openConnection(); 
    }

    public function attemptLogin($username, $password)
    {
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

    private function passwordCheck($user_input_password, $stored_hash): bool
    {
        return password_verify($user_input_password, $stored_hash);
    }

    private function generateSalt($length)
    {
        // Not 100% unique, not 100% random, but good enough for a salt
        // MD5 returns 32 characters
        $unique_random_string = md5(uniqid(mt_rand(), true));

        // Valid characters for a salt are [a-zA-Z0-9./]
        $base64_string = base64_encode($unique_random_string);

        // But not '+' which is valid in base64 encoding
        $modified_base64_string = str_replace('+', '.', $base64_string);

        // Truncate string to the correct length
        return substr($modified_base64_string, 0, $length); //salt;
    }

    public function passwordEncrypt($password): ?string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function passwordUpdate($password, $userid): bool
    {
        $hashed_password = $this->passwordEncrypt($password);
        $sql  = "UPDATE users SET hashed_password='$hashed_password' WHERE userid=$userid ";
        $result = $this->q($sql);

        if ($result && $this->affectedRows() == 1) {
            // Success
            $_SESSION["message"] = "User ID $userid Password updated.";
            return true;
        } else {
            // Failure
            $_SESSION["message"] = "passwordUpdate for user id $userid failed.";
            return false;
        }
    }

    public function userAdd($array): string
    {
        extract($array);
        $hashed_password = $this->passwordEncrypt($password);
        $sql  = "INSERT INTO users ( username, hashed_password, fname, lname ) VALUES ('$username',  '$hashed_password', '$fname', '$lname') ";
        $result = $this->q($sql);
        if ($result && $this->affectedRows() == 1) {
            $message ="Added username $username $fname $lname ";
        } else {
            $message ="userAdd failed - username $username $fname $lname ";
        }
        return $message;
/*
        i need a view that will ask for the information.
        it is just like update except that username and password are requirements
*/
    }

    // mysqli_query returns FALSE on failure
    public function deleteUser($userid): string
    {
        $sql  = "DELETE FROM USERS WHERE userid = '$userid' LIMIT 1";
        $result = $this->q($sql);
        if ($result && $this->affectedRows() == 1) {
            $message = "Deleted User " . $userid . "<br />\n";
        } else {
            $message = "Deleting User " . $userid . " <u>FAILED<u> <br />\n";
        }
        return $message;
    }

    public function userUpdate($array): string
    {
        extract($array);
        $row = $this->findUserById($userid);

        $message = "";
        if ($fname <> $row["fname"] || $lname <> $row["lname"]) {
            $sql = "update users set fname='" . $fname . "', lname='" . $lname . "' where userid=" . $userid;
            $result = $this->q($sql);

            if ($result && $this->affectedRows() == 1) {
                $message .= "User ID $userid Name updated.<br />\n";
            } else {
                $message .= "userUpdate for user id $userid failed.<br />\n";
            }
        }

        if (!empty($password)) {
            $hashed_password = $this->passwordEncrypt($password);
            if ($hashed_password <> $row["hashed_password"]) {
                $this->passwordUpdate($password, $userid);
                $message .= $_SESSION["message"];
                unset ($_SESSION["message"]);
            }
        }
        return $message;
    }

    public function findUserByUsername($username)
    {
        $safe_username = $this->escapeValue($username);
        $sql  = "SELECT * FROM users WHERE username = '$safe_username' LIMIT 1 ";
        $user_set = $this->q($sql);
        if($user = $this->fetchArray($user_set)) {
            return $user;
        } else {
            return null;
        }
    }

    public function findAllUsers()
    {
        $sql = "SELECT * FROM users ORDER BY username ";
        return $this->q($sql); //user_set;
    }

    public function findUserById($id)
    {
        $sql = "SELECT * FROM users WHERE userid = '$id' LIMIT 1 ";
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
    public function userTable ($title="Manage Users",$edit_page="",$delete_page=""): string
    {
        $result = $this->findAllUsers();
        $output  = "<table style='border: 1px solid black;'>\n";
        $output .= "\t\t\t\t\t\t\t<caption>$title</caption>\n";
        $output .= "\t\t\t\t\t\t\t<tr>\n";
        $output .= "\t\t\t\t\t\t\t\t<th style='text-align:center;'>Name</th>\n";
        $output .= "\t\t\t\t\t\t\t\t<th style='text-align:center;'>Username</th>\n";
        $output .= "\t\t\t\t\t\t\t\t<th colspan='2' style='text-align:center;'>Actions</th>\n";
        $output .= "\t\t\t\t\t\t\t</tr>\n";

        while($user = $this->fetchArray($result)) { 
            $name = trim($user["fname"]) . " " . trim($user["lname"]);
            $userid = urlencode($user["userid"]);
            $output .= "\t\t\t\t\t\t\t<tr>\n";
            $output .= "\t\t\t\t\t\t\t\t<td>" . htmlentities($name) . "</td>\n";
            $output .= "\t\t\t\t\t\t\t\t<td>" . htmlentities($user["username"]). "</td>\n";
            $output .= "\t\t\t\t\t\t\t\t<td><button type='submit' class='edit_up' name='edit'   value='$userid' formaction='$edit_page'>Edit</button></td>\n";
            $output .= "\t\t\t\t\t\t\t\t<td><button type='submit' class='del_up'  name='delete' value='$userid' formaction='$delete_page'>X</button></td>\n";
            $output .= "\t\t\t\t\t\t\t</tr>\n";
        }
        $output .= "\t\t\t\t\t\t</table>\n";
        return $output;
    }

    public function insertId()
    {
        // get the last id inserted over the current db connection
        return mysqli_insert_id($this->connection);
    }

}
$utilities = new Utilities();
