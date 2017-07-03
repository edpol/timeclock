<?php 
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!$session->is_logged_in()) {
		redirect_to("login.php");
	}

	if (isset($_SESSION["employeeid"])) { unset($_SESSION["employeeid"]); }
	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Menu", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 
?>
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
	<div id="back">
		<span class="color_size">
			<div class="whitebox">
				<?php echo date('m/d/y');?><br />
				<form>
				<input type="button" name="submit" value="Add Employee"    class="admin_up" onClick="parent.location='add_employee.php'"    /><br />
				<input type="button" name="submit" value="Edit Employee"   class="admin_up" onClick="parent.location='edit_employee.php'"   /><br />
				<input type="button" name="submit" value="Delete Employee" class="admin_up" onClick="parent.location='delete_employee.php'" /><br />
				<input type="button" name="submit" value="Change Time"     class="admin_up" onClick="parent.location='change_time.php'"     /><br />
				<input type="button" name="submit" value="Reports"         class="admin_up" onClick="parent.location='reports.php'"         /><br />
				<input type="button" name="submit" value="Contacts"        class="admin_up" onClick="parent.location='emergency.php'"       /><br />
				<input type="button" name="submit" value="Timeclock"       class="admin_up" onClick="parent.location='../index.php'"        /><br />
				<input type="button" name="submit" value="Log Out"         class="admin_up" onClick="parent.location='logout.php'"          /><br />
				</form>
			</div>
		</span>
	</div>
<script type="text/javascript" language="JavaScript" src="../mysrc.js">
</script>
</body>
</html>
