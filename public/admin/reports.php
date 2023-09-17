<?php
	require_once("../../include/initialize.php");

	// Already logged in? 
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}

	$_SESSION['calling_url'] = "admin/reports.php";
	$_SESSION["list_all"] = true;

	$lastname = "";
	$checked  = "";
	$display  = "none";
	$report_type = "pdf";
	$group_id = "";
	$employeeid = "";

	$end_date    = strftime("%m/%d/%Y", strtotime(" last Saturday "));
	$start_date  = strftime("%m/%d/%Y", strtotime($end_date . "-6 day "));

	// Create variables from list, preference to POST
	include("../post_list.php");
	foreach ($post_list as $value) {
		if (isset($_POST[$value])) {
			$$value = $_POST[$value];
			unset($_POST[$value]);
		} else {
			if (isset($_SESSION[$value])) {
				$$value = $_SESSION[$value];
				unset($_SESSION[$value]);
			}
		}
	}

	if (isset($submit) && $submit=="submit") {
		if (isset($only_print_one) && $only_print_one == true) {
			$checked = "checked";
			$display = "block";
		}

		if (isset($report_type) && $report_type=="pdf") {
			$pdf = new PDF();
			$pdf->printReport($start_date,$end_date,$group_id,$employeeid);
		} else {
			$timeclock   = new Timeclock();
			$timeclock->printReport($start_date,$end_date,$group_id,$employeeid);
		}
	}

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Reports", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 

?>
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
<div id="back">
	<h2>
		<form action="../find_id.php" method="post">
			<span class="employee">
				<span style="margin:0 auto; display:table;">
					<div align="center">Reports</div>
					<div class="labels">
						<p>Start: </p>
						<p>End:   </p>
						<p>Group: </p>
					</div>
					<div style="float:left;">
						<input type="text" id="datepicker"  name="start_date" value="<?= $start_date;?>"/><br />
						<input type="text" id="datepicker2" name="end_date"   value="<?= $end_date;  ?>"/><br />
						<select name="group_id">
							<?= $database->groupList($group_id) ; ?>
						</select>
					</div>
					<br clear="all" />
					<input type="checkbox" name="only_print_one" value="true" onChange="showHide('emp_id')" <?php echo $checked; ?>/> One Employee Only<br />
					<div id="emp_id" style="display:<?php echo $display . "\n"; ?>">
						Last Name: <input type="text"     name="lastname"    value="<?php echo $lastname;  ?>" />
					</div>
				</span>
				<div align="center" style="clear:both; padding-top:6px;">
					<input type="button" name="submit" value="back"   class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" onClick="parent.location='menu.php'" />
					<input type="submit" name="submit" value="submit" class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" /> <br />
				</div>
				<div style="margin-left:20%;">
					<input type="radio" name="report_type" value="pdf" id="pdf" checked="checked" /> <label for="pdf">PDF Format</label><br />
					<input type="radio" name="report_type" value="rtf" id="rtf"                   /> <label for="rtf">Rich Text Format</label><br />
<!--				<input type="radio" name="report_type" value="xls" id="xls"                   /> <label for="xls">Excel Format</label><br />
-->				</div>
			</span>
		</form>
		<span class="employee">
			<?php echo $session->message(); ?>
			<?php if (isset($_SESSION["message"])) { echo $_SESSION["message"]; unset($_SESSION["message"]);} ?>
		</span>
	</h2>
</div>
<?php render("footer", __DIR__, []); ?>
</body>
</html>