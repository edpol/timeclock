<?php 
	require_once("../../include/initialize.php");

	// Already logged in? 
	if(isset($session) && !$session->isLoggedIn()) {
		redirectTo("../login.php");
	}

	unset($_SESSION["list_all"]);
	$_SESSION['calling_url'] = "admin/change_time.php";

//	echo "<pre>"; print_r($_POST); echo "</pre>";

	if ( isset($_POST["employeeid"]) ) {
		$employeeid = $_POST["employeeid"];
	} else {
		if ( isset($_SESSION["employeeid"]) ) {
			$employeeid = $_SESSION["employeeid"];
		} else {
			$employeeid = "";
		}
	}

	if (isset($_POST["target_date"])) {
		$target_date = $_POST["target_date"]; 
	} else {
		if (isset($_SESSION["target_date"])) {
			$target_date = $_SESSION["target_date"]; 
		} else {
			$target_date = strftime("%m/%d/%Y",time()); 
		}
	}

	$timeclock = new Timeclock();

	if (isset($_POST["delete"])) {
		$id = $_POST["delete"];
		$timeclock->delete($id);
		unset($_POST["delete"]);
	}

	if (isset($_POST["submit"])) {
		// Add
		if ($_POST["submit"]=="add" && !empty($_POST["time"])) {
//			$punch = $target_date . " " . $_POST["hour"] . ":" . $_POST["minute"] . ":00 " . $_POST["am_pm"]; 

			$time = strtolower(trim($_POST["time"]));

			// if you forget the "m"
			$last_char = substr($time,-1,1);
			if ($last_char=="p" || $last_char=="a") { $time .= "m"; }

			// append time to target date
			$punch = $target_date . " " . $time; 
			$time_seconds = strtotime($punch);

			// comes back empty if it fails
			if (isset($time_seconds) && !empty($time_seconds)) {
				$punch = strftime('%Y-%m-%d %H:%M:00',strtotime($punch));
				$timeclock->punchIn($employeeid, $punch);
			}
			unset($_POST["submit"]);
		}
	}

	if (isset($employeeid) && !empty($employeeid)) {
		$employee_data   = $timeclock->getEmployeeData($employeeid);
		$target_array    = $timeclock->buildTodaysArray($employeeid,$target_date);
		$existing_entries= $timeclock->displayChangeTime($target_array);
		$lastname        = $timeclock->lastname;
	} else {
		$existing_entries = "";
		$lastname = "";
	}

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Change", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 

	$admin = 'class="admin_up" ' .'onMouseUp="this.className=' . "'admin_up'" . '" ' . 'onMouseDown="this.className=' . "'admin_down'" . '" '; 
?>

<body>
	<div id="back">

		<span class="employee">
			<form id="form1" action="../find_id.php" autocomplete="off" method="post" onKeyDown="pressed(event)"> <!-- doesnt need a submit button, just press return -->
				<table style="border-collapse: collapse;">
					<caption>Employee time to change</caption>
					<tr>
						<th><label for="focus">Last Name: </label></th>
						<td style="padding-bottom:0;">
							<input	style="text-align:center" type="text"  tabindex=1 name="last_name" id="focus"
								onClick="submitForm('../find_id.php')" value="<?php echo $lastname;?>" />
						</td>
					</tr>
					<tr style="padding:0">
						<th><label for="datepicker">Date: </label></th>
						<td style="padding-top:0;">
							<input	style="text-align:center" type="text"  tabindex=2 name="target_date" id="datepicker" 
								onClick="submitForm('../find_id.php')" value="<?php echo $target_date;?>" />
						</td>
					</tr>
				</table>
				<input type="hidden" name="calling_url" value="admin/change_time.php" />
				<div style="padding-top:20px; text-align:center;">
					<input type="button" tabindex=3 name="submit" value="menu"   <?= $admin; ?> onClick="parent.location='menu.php'" />
					<input type="submit" tabindex=4 name="submit" value="submit" <?= $admin; ?> />
				</div>
			</form>
			<br />

			<div class="greenbox" style="width:360px;">
				<form id="form1" action="change_time.php" autocomplete="off" method="post"> <!-- onKeyDown="pressed(event)"> -->

					<!-- Heading -->
					<div class="labels">
						<p>Name</p>
						<p>Date</p>
					</div>
					<div class="info" style="float:left;">
<?=	 					"\t\t\t\t\t\t" . $timeclock->name . "<br />\n"; ?>
<?=						"\t\t\t\t\t\t" . strftime("%A %m/%d/%Y", strtotime($target_date) ) . "<br />\n"; ?>
					</div>
                    <div style='clear:both'></div>

					<!-- Add Time -->
					<div style="padding-top:10px; padding-bottom:10px;">
<?=				 		$timeclock->inputTimeSetup($target_date); ?>
					</div>

					<!-- List/Delete Time -->
<?php				echo $existing_entries;
                    if(isset($session)) {
                        echo "<span class='employee'>\r\n";
                        echo $session->message();
                        echo "</span>\r\n";
                    }
?>
				</form>
			</div>
		</span>
	</div>
<?php
    $data = array();
    render("footer", __DIR__, $data);
?>
</body>
</html>
