<?php
	include ("../include/connect.php");

	function desc_table ($table) {
		global $dbh;
		$sql = "desc " . $table;
		$sth = $dbh->prepare($sql);
		$sth->execute();
		$result = $sth->fetchAll(PDO::FETCH_ASSOC);
		$first = true;
		$output = "<b>Describe Table <em>{$table}</em></b><br /><table>\n";
		$header = "<tr>";
		foreach ($result as $key => $value) {
			$msg = "<tr>";
			foreach($value as $idx => $data) {
				if ($first) {
					$header .= "<th>{$idx}</th>";
				}
				$msg .= "<td>{$data}</td>";
			}
			$msg .= "</tr>\n";
			if ($first) {
				$header .= "</tr>\n";
				$first = false;
				$output .= $header;
			}
			$output .= $msg;
		}
		$output .= "</table>\n";
		return $output;
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Modify DB</title>
<style>
body { color:white; background-color:black; font-family:Tahoma, Geneva, sans-serif; }
</style>
</head>
<body>
<pre>
<?php

	try {
		$dbh = new PDO('mysql:host=localhost;dbname=mdr_clock', DB_USER, DB_PASS);
		$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::FETCH_ASSOC);
	} 
	catch (PDOException $e) {
		print "Error!: " . $e->getMessage() . "<br/>";
		die();
	}

	echo desc_table("employee");

	$sql = "alter table employee add emergency_contact varchar(50)";//, emergency_number varchar(10)";
	$sql = "alter table employee add emergency_number varchar(10)";
	$sth = $dbh->prepare($sql);
	$sth->execute();

	echo desc_table("employee");
?>
</pre>
</body>
</html>