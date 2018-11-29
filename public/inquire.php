<?php 
	require_once("../include/initialize.php");
	$barcode  = (isset($_POST["barcode"]))  ? $_POST["barcode"]  : ""; 
	$lastname = (isset($_POST["lastname"])) ? $_POST["lastname"] : "";

	if ( isset($_SESSION["employeeid"]) ) {
		$employeeid = $_SESSION["employeeid"];
		unset ($_SESSION["employeeid"]);
		$today = strftime('%Y-%m-%d 00:00:00',time());
		$tc = new timeclock();
/*
		$_SESSION['calling_url'] = "index.php";  // dont want to return to inqure
		$_SESSION["timeout"] ="id=showupinq";
		$_SESSION["output"] = $tc->build_2weeks($employeeid,$today);
		redirect_to("popup.php");
*/
		$display  = '<div id="showupinq" class="popup">';
		$display .= $tc->build_2weeks($employeeid,$today);
		$display .= '<a class="close"   href="' . $calling_url . '"></a>';
		$display .= "</div>\n";
//		$display .= '<a class="overlay" href="' . $calling_url . '">' . "</a>\n";
		$_SESSION["display"] = $display;
		redirect_to("index.php");

	} else {
		$_SESSION['calling_url'] = "inquire.php";  // find_id will send you back to this page
		$_SESSION["list_all"] = FALSE;
	}

	$data = array("background_image"=>"search.jpg", "background_color"=>"FCF", "background_body_color"=>"#000");
	render("header", __DIR__, $data); 

// if we have barcode or lastname we can just call display last 2 weeks routine
// if not we can ask for rhe id or name, should do it in java script and grey out the background
// so if they click they go back to normal
?>
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
	<div id="back">
		<h1>
			<div style="color:#FCF; font-weight:bold; font-size:36px; padding-bottom:18px;">Inquire</div>
			<div class="bluebox">
				<div align="center">
					<span id="time"></span><br />
					<?= show_date(); ?>
				</div>
				<form id="form2" action="find_id.php" autocomplete="off" method="post" onKeyDown="pressed(event)"> <!-- doesnt need a submit button, just press return -->
					<div class="labels">
						<p>ID Number: </p>
						<p>Last Name: </p>
					</div>
					<div style="float:left;">
						<input type="password" name="barcode"  value="<?php echo $barcode;  ?>" id="focus" autofocus style="margin-bottom:6px;"/><br />
						<input type="text"     name="lastname" value="<?php echo $lastname; ?>" /><br />  <!-- onclick="openWindow('hello.htm')" --> 
					</div>
					<br clear="all" />
					<div style="padding:30px 0 0px 0;">
						<input type="button" name="index" value="Go Back To Punch In" class="pink_up" onClick="parent.location='index.php'" />
					</div>
<?php echo $message; ?>
				</form>
			</div>
		</h1>
	</div>
<?php 	// if we are on this page we are not in edit mode
		if (isset($_SESSION["edit_employee"])) { unset($_SESSION["edit_employee"]); }?>
<script type="text/javascript" language="JavaScript" src="mysrc.js">
</script>
</body>
</html>
