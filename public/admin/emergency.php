<?php 
	require_once("../../include/initialize.php");

	// Already logged in? 
	if(!$session->is_logged_in()) {
		redirect_to("login.php");
	}

	$output  = "<table align='center'>\n";
	$output .= "<tr><th>Employee</th><th>Phone</th><th>Emergency<br />Contact</th><th>Emergency<br />Phone</th><th>email</th></tr>\n";
	$sql = "select * from employee order by lname";
	$result=$db->query($sql);
	while ($row = $db->fetch_array($result)) {
		extract($row);
		if ($is_active) {
			$name = ucwords(strtolower(trim($fname) . " " . trim($lname)));
			$contact = ucwords(strtolower(trim($emergency_contact)));
			$output .= "<tr>";
			$output .= "<td>{$name}</td>";
			$output .= "<td>" . phonef($phone) . "</td>";
			$output .= "<td>{$contact}</td>";
			$output .= "<td>" . phonef($emergency_number) . "</td>";
			$output .= "<td>{$email}</td>";
			$output .= "</tr>\n";
		}
	}
	$output .= "</table>\n<br />";

?>
<!DOCTYPE html>
<html>
<head>
<title>Emergency List</title>
<style>
body {font-family:Arial, Helvetica, sans-serif; font-size:16px; color:white; background-color:black;}
td {
    width: 1px;
    white-space: nowrap;
	padding-right:6px;
}
</style>
</head>
<body>
	<div align="center" style="font-size:24px;">
		Contact List
		<?= date('m/d/y');?>
	</div>
	<?= $output; ?>
</body>
</html>
