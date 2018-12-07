<?php 	
/*
 *	timeclock
 */
	require_once("../include/initialize.php");

	$_SESSION['calling_url'] = "index.php";

	if (isset($_SESSION["employeeid"])) {
		$employeeid = $_SESSION["employeeid"];
		unset($_SESSION["employeeid"]);
		$today = strftime('%Y-%m-%d 00:00:00',time());
		$tc = new Timeclock();
		$tc->punchIn($employeeid);
		$add_name_to_header = true;
/*
		// you either timeout or grey the screen, can't seem to get both ath the same time
		$_SESSION["timeout"] ="id=showup"; 
		$_SESSION["output"] =  $tc->buildToday($employeeid,$today,$add_name_to_header);
		redirectTo("popup.php");
*/
		$display  = "<div id='showup' class='popup' style='z-index:20'>\n";
		$display .= $tc->buildToday($employeeid,$today,$add_name_to_header);
		$display .= "<a class='close' href=''></a>\n";
		$display .= "</div>\n";

//		$display .= '<a id="showup" class="overlay" href="' . $calling_url . '">' . "\n" . "</a>";
		$_SESSION["display"] = $display;
	} else {
		$_SESSION["list_all"] = FALSE;
	}

	$display = "";
	if (isset($_SESSION["display"])) {
		$display = $_SESSION["display"];
		unset($_SESSION["display"]);
	}

	$data = array("background_image"=>"work.jpg", "background_color"=>"FF0", "background_body_color"=>"#232F3D");
	render("header", __DIR__, $data); 
?>
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
	<div id="back">
		<h2>
<?= 		$display; ?>
			<div class="bluebox">
				<div align="center">
					<span id="time"></span><br />
					<?= showDate(); ?>
				</div>
				<form id="form1" action="find_id.php" autocomplete="off" method="post" onKeyDown="pressed(event)"> <!-- doesnt need a submit button, just press return -->
					<div class="labels">
						<p>ID Number: </p>
						<p>Last Name: </p>  <!-- onclick="openWindow('hello.htm')" --> 
					</div>
					<div style="float:left; padding-top:0px;">
						<input type="password" name="barcode"  value="" id="focus" autofocus style="margin-bottom:6px;"  /><br />
						<input type="text"     name="lastname" value="" /><br /> <!-- onclick="openWindow('hello.htm')" --> 
					</div>
					<br clear="all" />
					<input type="hidden"   name="calling_url" value="index.php" />					
					<div style="clear:both; padding-top:20px;">
						<input type="submit" style="display: none" />
						<a name="login"   class="blue_up" href="admin/login.php">Sign In</a>
						<a name="inquire" class="blue_up" href="inquire.php"    >Inquire</a>
					</div>
<?=					$message; ?>
				</form>
			</div>
		</h2>
	</div>
<!-- Active input tag - Has to be after the input tag named barcode -->
<script type="text/javascript" language="JavaScript" src="mysrc.js">
</script>
<?php 
	$data = array();
//	render("footer", __DIR__, $data); 
?>
</body>
</html>
