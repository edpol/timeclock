<?php
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!$session->is_logged_in()) {
		redirect_to("login.php");
	}

	$list = $database->columns;

	$sql = "select employeeid, fname, lname, grp from employee";
	$result = $database->query($sql);
	$grp_table = "<table>\n";
	$grp_table .= "<tr><th>id</th><th>first</th><th>last</th><th>group</th></tr>\n";
	while ($row = $database->fetch_array($result)) {
		extract($row); 
		$grp_table .= "<tr><td>$employeeid</td><td>$fname</td><td>$lname</td><td>$grp</td></tr>\n";
	}
	$grp_table .= "</table>\n";

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Group", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 

?>
<body>
<div id="wrapper">
<?php 
	echo $grp_table;
?>

	<select>
<?php	echo $database->group_list(); ?>
	</select>

</div>
<script type="text/javascript" language="JavaScript" src="../mysrc.js">
</script>
</body>
</html>