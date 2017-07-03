<?php 
	require_once("../../include/initialize.php");
	require_once(LIB_PATH.DS.'utilities.php');

	// Already logged in? Go to menu
	if($session->is_logged_in()) {
		redirect_to("menu.php");
	}

	// Remember to give your form's submit tag a name="submit" attribute!
	if (isset($_POST['submit'])) { // Form has been submitted.

		$error = array();
		$required_list = array ("username", "password");
		foreach($required_list as $key => $value) {
			if (isset($_POST[$value]) && $_POST[$value] !== "") {
				$$value = trim($_POST[$value]);
			} else {
				$$value = "";
				$error[$value] = "{$value} can't be blank";
			}
		}
		$found_user = false;
		if (empty($error)) {
			// Check database to see if username/password exist.
			$found_user = $utilities->attempt_login($username, $password);
		}
		if ($found_user) {
			// Username and password worked
			$program = "timeclock";
			$message = $utilities->check_acl($found_user,$program); 
			if (empty($message)) {
				$session->login($found_user);
//				log_action('Login', "{$found_user->username} logged in.");
				redirect_to("menu.php");
			}
		} else {
			// username/password combo was not found in the database
			$message = "Username/password<br />combination incorrect.";
		}

	} else { // Form has not been submitted.
		$username = "";
		$password = "";
	}

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Login", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 
?>
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();showClock();" onUnload="document.forms[0].reset();">
<div id="back">
	<h2>
		<span class="employee">

			<form id="form1" action="login.php" autocomplete="off" method="post"> <!-- onKeyDown="pressed(event)"> -->

				<!-- Login -->
				<div style="display:inline-block;"> <!-- so the time is centered on username password, not buttons -->
				<div align="center">
					<span id="time"></span><br />
					<?= show_date(); ?>
				</div>
					<div class="labels">
						<p>username: </p> 
						<p>password: </p> 
					</div>
					<div style="float:left;">
						<input type="text"     name="username" value="" id="focus" autofocus /><br />
						<input type="password" name="password" value="" /><br />
					</div>
					<br clear="all" />
				</div>

				<!-- Buttons -->
				<div align="center" style="padding-top:20px;">
					<a style=" padding:14px 65px;" href="../index.php" class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'">back</a>
					<input type="submit" name="submit" value="submit" class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" />
				</div>
				<?= $message; ?>
			</form>
		</span>
	</h2>
</div>
<?php if (isset($_SESSION["employeeid"])) { unset($_SESSION["employeeid"]); } ?>
<script type="text/javascript" language="JavaScript" src="../mysrc.js">
</script>
</body>
</html>
