<?php 
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!isset($session) || !$session->isLoggedIn()) {
		redirectTo("login.php");
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
				<span id="time"></span><br />
				<?= date('m/d/y'); ?><br />
				<form>
				<button type="submit" class="admin_up" formaction="add_employee.php"    >Add Employee   </button><br />
				<button type="submit" class="admin_up" formaction="edit_employee.php"   >Edit Employee  </button><br />
				<button type="submit" class="admin_up" formaction="delete_employee.php" >Delete Employee</button><br />
				<button type="submit" class="admin_up" formaction="change_time.php"     >Change Time    </button><br />
				<button type="submit" class="admin_up" formaction="reports.php"         >Reports        </button><br />
				<button type="submit" class="admin_up" formaction="emergency.php"       >Contacts       </button><br />
				<button type="submit" class="admin_up" formaction="../index.php"        >Timeclock      </button><br />
				<button type="submit" class="admin_up" formaction="manage_users.php"    >Manage Users   </button><br />
				<button type="submit" class="admin_up" formaction="logout.php"          >Log Out        </button><br />
				</form>
			</div>
		</span>
	</div>
    <?php render("footer", __DIR__, []); ?>
</body>
</html>
