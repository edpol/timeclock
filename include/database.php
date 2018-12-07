<?php
require_once(LIB_PATH.DS.'connect.php');

class Database extends Common {

	public  $columns;

	function __construct() {
		parent::__construct();
		$db_name = DB_NAME;
		$this->openConnection();
		$this->columns = $this->getColumns($db_name);
	}

/*
 *	Get list of tables in this database, then get list of columns for each table
 */
	public function getColumns($db_name) {
		$table_list = array();
		$sql = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE '" . DB_NAME . "' "; // Table names
		$result = $this->q($sql);
		while ($row = $this->fetchArray($result)) {
			$table_list[] = $row["TABLE_NAME"];
		}
		$column_name = array ();
		foreach ($table_list as $key => $value) {
			// Tables in database mdr_clock: employee, timeclock
			$sql = "select * from {$value}";
			$result = $this->q($sql);
		    $finfo = $this->fetchFields($result);
		    foreach ($finfo as $val) {
				$column_name[$value][$val->name]   = $val->name;
			}
		}
		return $column_name;
	}

	public function isBarcodeUnique($target) {
		$sql = "select * from employees where barcode='{$target}'";
		$result = $this->q($sql);
		if ($this->numRows($result) > 0) {
			return false;
		} else {
			return true;
		}
	}

	public function groupList($groupname="") {
		$group_list = array();
		$sql = "select groupname from groups group by groupname";
		$result=$this->q($sql);
		$option = "";
		while ($row = $this->fetchArray($result)) {
			$group_list[] = $row["groupname"];
			$checked = ($row["groupname"]==$groupname) ? "selected='selected'" : "";
			$option .= tabs(2) . "<option value='". $row["groupname"] . "' " . $checked . ">". $row["groupname"] ."</option>\n";
		}
		return $option;
	}

	public function getEmployee($id) {
		$sql = "select * from employees where employeeid='{$id}'";
		$result = $this->q($sql);
		return $result;
	}

	public function employeesByLastName() {
		$sql = "select * from employees order by lname";
		$result = $this->q($sql);
		return $result;		
	}

	public function listNames() {
		$output  = "<table align='center'>\n";
		$output .= "<tr><th width='50'>id</th><th>first name</th><th>last name</th><th>group</th></tr>\n";
		$result=$this->employeesByLastName();
		while ($row = $this->fetchArray($result)) {
			extract($row);
			if ($is_active) $output .= "<tr><td>{$employeeid}</td><td>{$fname}</td><td>{$lname}</td><td>{$group_id}</td></tr>\n";
		}
		$output .= "</table>\n";
		return $output;
	}

	public function findBarcode($barcode) {
		$sql = 'select * from employees where barcode="' . $barcode  . '"';
		$result = $this->q($sql);
		return $result;
	}

	public function findLikeLastName($lastname) {
		$sql = 'select * from employees where lname like "' . $lastname . '%" order by lname';
		$result = $this->q($sql);
		return $result;
	}

	public function findLastName($lastname) {
		$sql = 'select * from employees where lname ="' . $lastname . '"';
		$result = $this->q($sql);
		return $result;
	}

	public function findEmployees($group_id, $employeeid="") {
		$x = (!empty($employeeid)) ? "and employeeid='{$employeeid}' " : "and group_id='{$group_id}' ";
		$sql = "select * from employees where is_active=true {$x} order by lname";
		$result = $this->q($sql);
		return $result;
	}

	public function setInactive($employeeid) {
		global $session;
		$sql = "update employees set is_active = 0 where employeeid='{$employeeid}'";
		if ($result=$this->q($sql)) {
			$session->message("User " . $_SESSION["employee_name"] . " no longer active {$employeeid}"); // . "<br />" . $sql;
		} else {
			$session->message("Did not work <br />");
		}
	}

}
$database = new Database();
$db =& $database;
