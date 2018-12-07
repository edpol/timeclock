<?php 
	require_once("../../include/initialize.php");

	// Already logged in? 
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}

	if (isset($_SESSION["employeeid"])) {
		$employeeid = $_SESSION["employeeid"];
		unset($_SESSION["employeeid"]);
//		$tc = new Timeclock();
		$database->setInactive($employeeid);
//		reload the page so session gets updated, or just call the updating method
//		redirectTo("delete_employee.php");  
	} else {
		$_SESSION["calling_url"] = "admin/delete_employee.php";
	}

	if (isset($_SESSION["list_all"])) unset($_SESSION["list_all"]);

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Delete", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 

?>
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
	<div id="back">
		<h2>
			<span class="employee">
				<div style="text-align:center;">
					<?php echo date('m/d/y');?><br />
					Employee to DELETE<br />
				</div>
				<form id="form1" action="../find_id.php" autocomplete="off" method="post" onKeyDown="pressed(event)"> <!-- doesnt need a submit button, just press return -->
					<div class="labels">
						<p>ID Number: </p>
						<p>Last Name: </p>
					</div>
					<div style="float:left;">
						<input type="text"   name="barcode"     value="" /><br />
						<input type="text"   name="lastname"    value="" id="focus" /><br />  <!-- onclick="openWindow('hello.htm')" --> 
						<input type="hidden" name="calling_url" value="admin/delete_employee.php" />
					</div>
					<br clear="all" />
					<div style="padding-top:20px; text-align:center;">
						<input type="button" name="submit" value="back"   class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" onClick="parent.location='menu.php'" />
						<input type="submit" name="submit" value="submit" class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" />
					</div>
				</form>
			</span>
			<span class="employee">
				<?php $session->checkMessage();
					  echo $session->message(); ?>
			</span>
		</h2>
	</div>
<script type="text/javascript" language="JavaScript" src="../mysrc.js">
</script>
</body>
</html>
