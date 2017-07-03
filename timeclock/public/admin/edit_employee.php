<?php 
	require_once("../../include/initialize.php");

	// Already logged in? 
	if(!$session->is_logged_in()) {
		redirect_to("login.php");
	}

	if (isset($_SESSION["employeeid"])) {
		redirect_to("edit_employee2.php");
	} else {
		$_SESSION["calling_url"] = "admin/edit_employee.php";
	}

	if (isset($_SESSION["list_all"])) unset($_SESSION["list_all"]);

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Edit", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 

?>
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
	<div id="back">
		<h2>
			<span class="employee">
				<div style="text-align:center;">
					<?php echo date('m/d/y'); ?><br />
					Employee to edit
				</div>
				<form id="form1" action="../find_id.php" autocomplete="off" method="post" onKeyDown="pressed(event)"> <!-- doesnt need a submit button, just press return -->
					<div class="labels">
						<p>ID Number: </p>
						<p>Last Name: </p>
					</div>
					<div style="float:left;">
						<input type="text"   name="barcode"     value="" /><br />
						<input type="text"   name="lastname"    value="" id="focus" /><br />  <!-- onclick="openWindow('hello.htm')" --> 
						<input type="hidden" name="calling_url" value="admin/edit_employee.php" />
					</div>
					<br clear="all" />
					<div style="padding-top:20px; text-align:center;">
						<input type="button" name="submit" value="back"   class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" onClick="parent.location='menu.php'" />
						<input type="submit" name="submit" value="submit" class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" />
					</div>
				</form>
			</span>
			<span class="employee">
				<?php echo $message; ?>
			</span>
		</h2>
	</div>
<script type="text/javascript" language="JavaScript" src="../mysrc.js">
</script>
</body>
</html>
