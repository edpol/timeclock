<?php

class Common {

	public  $last_query;
	protected $connection;
	protected $magic_quotes_active;
	protected $real_escape_string_exists;

	function __construct() {
		$this->magic_quotes_active = false; //get_magic_quotes_gpc();
		$this->real_escape_string_exists = function_exists( "mysqli_real_escape_string" );
	}

	public function openConnection() {
//		$this->connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, $db_name);
        if(!function_exists('mysqli_connect')) {
            die('function mysqli_connect not found');
        }
		$this->connection = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		if (!$this->connection) {
//			die("Database connection failed: " . mysqli_error($this->connection));
			die("Database connection failed: " . $this->connection->connect_error . " (" . $this->connection->connect_errno . ")");
		}
	}

	public function closeConnection() {
		if(isset($this->connection)) {
			$this->connection->close();
			unset($this->connection);
		}
	}

	public function q($sql) {
		$this->last_query = $sql;
//		$result = mysqli_query($this->connection, $sql);
		$result = $this->connection->query($sql);
		$this->confirmQuery($result);
		return $result;
	}

	private function confirmQuery($result) {
		if (!$result) {
			$output = "Database query failed: " . $this->connection->error . "<br /><br />";
			$output .= "Last SQL query: " . $this->last_query;
			die( $output );
		}
	}

	public function fetchFields($result_set) {
		return $result_set->fetch_fields();		
	}

	// "database-neutral" methods
	public function fetchArray($result_set) {
//		return mysqli_fetch_array($result_set, MYSQLI_ASSOC);
		return $result_set->fetch_array(MYSQLI_ASSOC);
	}

	public function numRows($result_set) {
//		return mysqli_num_rows($result_set);
		return $result_set->num_rows;
	}

	public function insertId() {
		// get the last id inserted over the current db connection
//		return mysqli_insert_id($this->connection);
		return $this->connection->insert_id;
	}

	public function affectedRows() {
//		return mysqli_affected_rows($this->connection);
		return $this->connection->affected_rows;
	}

	public function escapeValue( $value ) {
		if( $this->real_escape_string_exists ) { // PHP v4.3.0 or higher
			// undo any magic quote effects so mysqli_real_escape_string can do the work
			if( $this->magic_quotes_active ) { $value = stripslashes( $value ); }
			$value = mysqli_real_escape_string( $this->connection, $value );
		} else { // before PHP v4.3.0
			// if magic quotes aren't already on then add slashes manually
			if( !$this->magic_quotes_active ) { $value = addslashes( $value ); }
			// if magic quotes are active, then the slashes already exist
		}
		return $value;
	}


}