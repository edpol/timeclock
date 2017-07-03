<?php
require_once(LIB_PATH.DS.'connect.php');

class MySQLiDatabase {

	public  $columns;
	public  $last_query;
	private $connection;
	private $magic_quotes_active;
	private $real_escape_string_exists;

	function __construct() {
		$this->open_connection();
		$db_name = DB_NAME;
		$this->columns = $this->get_columns($db_name);
		$this->magic_quotes_active = get_magic_quotes_gpc();
		$this->real_escape_string_exists = function_exists( "mysqli_real_escape_string" );
	}

	public function open_connection() {
		$this->connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
		if (!$this->connection) {
//			die("Database connection failed: " . mysqli_error($this->connection));
			die("Database connection failed: " . mysqli_connect_error() . " (" . mysqli_connect_errno() . ")");
		}
	}

/*
 *	Get list of tables in this database, then get list of columns for each table
 */
	public function get_columns ($db_name) {
		$table_list = array ();
		$sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE '{$db_name}'"; // Table names
		$result = $this->query($sql);
		while ($row = $this->fetch_array($result)) {
			$table_list[] = $row["TABLE_NAME"];
		}
		$column_name = array ();
		foreach ($table_list as $key => $value) {
			// Tables in database mdr_clock: employee, timeclock
			$sql = "select * from {$value}";
			$result = $this->query($sql);
		    $finfo = mysqli_fetch_fields($result);
		    foreach ($finfo as $val) {
				$column_name[$value][$val->name]   = $val->name;
			}
		}
		return $column_name;
	}

	public function close_connection() {
		if(isset($this->connection)) {
			mysqli_close($this->connection);
			unset($this->connection);
		}
	}

	public function query($sql) {
		$this->last_query = $sql;
		$result = mysqli_query($this->connection, $sql);
		$this->confirm_query($result);
		return $result;
	}
	
	public function escape_value( $value ) {
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

	// "database-neutral" methods
	public function fetch_array($result_set) {
		return mysqli_fetch_array($result_set, MYSQLI_ASSOC);
	}

	public function num_rows($result_set) {
		return mysqli_num_rows($result_set);
	}

	public function insert_id() {
		// get the last id inserted over the current db connection
		return mysqli_insert_id($this->connection);
	}

	public function affected_rows() {
		return mysqli_affected_rows($this->connection);
	}

	private function confirm_query($result) {
		if (!$result) {
			$output = "Database query failed: " . mysqli_error($this->connection) . "<br /><br />";
			$output .= "Last SQL query: " . $this->last_query;
			die( $output );
		}
	}

	public function is_barcode_unique($target) {
		$sql = "select * from employee where barcode='{$target}'";
		$result = $this->query($sql);
		if ($this->num_rows($result) > 0) {
			return false;
		} else {
			return true;
		}
	}

	public function group_list ($grp="") {
		$group_list = array();
		$sql = "select grp from employee group by grp";
		$result=$this->query($sql);
		$option = "";
		while ($row = $this->fetch_array($result)) {
			$group_list[] = $row["grp"];
			$checked = ($row["grp"]==$grp) ? "selected='selected'" : "";
			$option .= tabs(2) . "<option value='". $row["grp"] . "' " . $checked . ">". $row["grp"] ."</option>\n";
		}
		return $option;
	}

	public function list_names () {
		$output  = "<table align='center'>\n";
		$output .= "<tr><th width='50'>id</th><th>first name</th><th>last name</th><th>group</th></tr>\n";
		$sql = "select * from employee order by lname";
		$result=$this->query($sql);
		while ($row = $this->fetch_array($result)) {
			extract($row);
			if ($is_active) $output .= "<tr><td>{$employeeid}</td><td>{$fname}</td><td>{$lname}</td><td>{$grp}</td></tr>\n";
		}
		$output .= "</table>\n";
		return $output;
	}

}

$database = new MySQLiDatabase();
$db =& $database;







?>