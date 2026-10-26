<?php

class Database extends Common {

	public array $columns;

	function __construct() {
		parent::__construct();
		$db_name = DB_NAME;
		$this->openConnection();
		$this->columns = $this->getColumns($db_name);
	}

    /**
     * Get list of tables in this database, then get list of columns for each table
     */
    public function getColumns($db_name): array
    {
		$table_list = array();
        $result = $this->db_query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA LIKE ? ", [$db_name]);
        while ($row = $result->fetch_assoc()) {
			$table_list[] = $row["TABLE_NAME"];
		}
		$column_name = array ();
		foreach ($table_list as $table_name) {
			$escaped_table = str_replace('`', '``', $table_name);
            $result = $this->db_query("SELECT * FROM `$escaped_table` LIMIT 0");
		    $finfo = $this->fetchFields($result);
		    foreach ($finfo as $val) {
				$column_name[$table_name][$val->name] = $val->name;
			}
		}
		return $column_name;
	}

    public function isBarcodeUnique($target): bool
    {
		$result = $this->db_query("select * from employees where barcode = ? ", [$target]);
		return !($this->numRows($result) > 0);
	}

    public function groupList($groupName=""): string
    {
		//$group_list = array();
		$sql = "select groupname from groupz group by groupname";
		$result = $this->db_query($sql);
		$option = "";
		while ($row = $this->fetchArray($result)) {
			//$group_list[] = $row["groupname"];
			$checked = ($row["groupname"]==$groupName) ? "selected='selected'" : "";
			$option .= tabs(2) . "<option value='". $row["groupname"] . "' " . $checked . ">". $row["groupname"] ."</option>" . PHP_EOL;
		}
		return $option;
	}

	public function getEmployee($id): mysqli_result|bool
    {
		$sql = "select * from employees where employeeid=?";
		return $this->db_query($sql,[$id]);
	}

	public function employeesByLastName(): mysqli_result|bool
    {
		$sql = "select * from employees order by lname";
		return $this->db_query($sql);
	}

	public function listNames(): string
    {
		$output  = "<table style='margin: 0 auto;'>" . PHP_EOL;
		$output .= "<tr><th style='width:50px'>id</th><th>first name</th><th>last name</th><th>group</th></tr>" . PHP_EOL;
		$result=$this->employeesByLastName();
		while ($row = $this->fetchArray($result)) {
			extract($row);
			if ($is_active) $output .= "<tr><td>$employeeid</td><td>$fname</td><td>$lname</td><td>$group_id</td></tr>" . PHP_EOL;
		}
		$output .= "</table>" . PHP_EOL;
		return $output;
	}

    public function findBarcode($barcode): mysqli_result|bool
    {
		$sql = 'select * from employees where barcode = ? ';
		return $this->db_query($sql, [$barcode]);
	}

    public function findLikeLastName($lastname): mysqli_result|bool
    {
		$sql = 'select * from employees where lname like ? order by lname';
		return $this->db_query($sql, [$lastname . "%"]);
	}

    public function findLastName($lastname): mysqli_result|bool
    {
		$sql = 'select * from employees where lname = ? ';
		return $this->db_query($sql, [$lastname]);
	}

    public function findEmployees($group_id, $employeeid=""): mysqli_result|bool
    {
        if (!empty($employeeid)) {
            $sql = "select * from employees where is_active=true and employeeid = ? order by lname";
            $param = [$employeeid];
        } else {
            $sql = "select * from employees where is_active=true and group_id = ? order by lname";
            $param = [$group_id];
        }
        return $this->db_query($sql, $param);
	}

	public function setInactive($employeeid): void
    {
		global $session;
		$sql = "update employees set is_active = 0 where employeeid = ? ";
		if ($this->db_query($sql,[$employeeid])) {
			$session->message("User " . $_SESSION["employee_name"] . " no longer active $employeeid"); // . "<br />" . $sql;
		} else {
			$session->message("Did not work <br />");
		}
	}

}
