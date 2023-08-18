<?php 
	require_once("../../include/initialize.php");
	require_once(LIB_PATH.DS.'utilities.php');

	// Already logged in? Go to menu
	if(isset($session) && $session->isLoggedIn()) {
		redirectTo("menu.php");
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
			$found_user = $utilities->attemptLogin($username, $password);
		}
		if ($found_user) {
			// Username and password worked
			$program = "timeclock";
			$session->login($found_user);
//			log_action('Login', "{$found_user->username} logged in.");
			redirectTo("menu.php");
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
			    <div class="whitebox">
				    <form id="form1" action="login.php" autocomplete="off" method="post"> <!-- onKeyDown="pressed(event)"> -->

                        <!-- Login -->
                        <div style="text-align: center;">
                            <span id="time"></span><br />
                            <?= showDate(); ?>
                        </div>

                        <table class="form2">
                            <tr><th><label for="username">Username</label> </th><td><input id="username" name="username" type="text"     value="" autofocus /></td></tr>
                            <tr><th><label for="password">Password</label> </th><td><input id="password" name="password" type="password" value="" /></td></tr>
                        </table>

                        <!-- Buttons -->
                        <div style="margin:0 auto; padding-top:20px;">
                            <input type="submit" name="submit" value="submit" class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" />
                            <a style="padding:12px 74px;" href="../index.php" class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'">back</a>
                        </div>
                        <?php if(isset($message)) echo $message; ?>
                    </form>
                </div>
            </span>
        </h2>
    </div>
<?php if (isset($_SESSION["employeeid"])) { unset($_SESSION["employeeid"]); } ?>
<script type="text/javascript" src="../mysrc.js">
</script>
</body>
</html>
