<?php
include ("../include/connect.php");
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
<?php
	try {
		$dbh = new PDO('mysql:host=localhost;dbname=mdr_clock', DB_USER, DB_PASS);
		$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION, PDO::FETCH_ASSOC);
	} 
	catch (PDOException $e) {
		print "Error!: " . $e->getMessage() . "<br/>";
		die();
	}

	// build list of tables in this database
	$sql = "show tables";
	$sth = $dbh->prepare($sql);
	$sth->execute();
/*	$result = $sth->fetchAll((PDO::FETCH_NUM); */
	$table_list = array();
	while ($result = $sth->fetch(PDO::FETCH_NUM)) {
		$table_list[] = $result[0];
	}
	
/*	foreach ($x->query($sql) as $row) {
		print_r($row);
		echo "<hr />";
	}
*/

	// get column list per table
	$columns = array ();
	foreach ($table_list as $key => $value) {
		$q = $dbh->prepare("DESCRIBE " . $value);
		$q->execute();
		$table_fields = $q->fetchAll(PDO::FETCH_COLUMN);
		$columns[$value] = $table_fields;
	}

	foreach($columns as $key => $values) {
		echo "<b>" . $key . "</b><br />";
		foreach ($values as $idx => $colname) {
			echo "-- " . $colname . "<br />";
		}
		echo "<br />";
	}
?>
</body>
</html>