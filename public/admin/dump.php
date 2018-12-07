<?php
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}

	$list = $database->columns;

	$result = $database->employeesByLastName();

	$group_table = "<table>\n";
	$group_table .= "<tr><th>id</th><th>first</th><th>last</th><th>group</th></tr>\n";
	while ($row = $database->fetchArray($result)) {
		extract($row); 
		$group_table .= "<tr><td>$employeeid</td><td>$fname</td><td>$lname</td><td>$group_id</td></tr>\n";
	}
	$group_table .= "</table>\n";

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Group", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 

?>
<body>
<div id="wrapper">
<?php 
	echo $group_table;
?>

	<select>
<?php	echo $database->groupList(); ?>
	</select>

</div>
<script type="text/javascript" language="JavaScript" src="../mysrc.js">
</script>
</body>
</html>