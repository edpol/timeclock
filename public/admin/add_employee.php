<?php
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}

	$list = $database->columns["employees"];

	$error = array();
	if (isset($_POST["submit"]) && $_POST["submit"]=="submit") {
		foreach ($_POST as $key =>$value) {
			$$key = $database->escapeValue(strtoupper($value));
		}

		if (!isset($barcode) || empty($barcode))   { $error["barcode"] = "Barcode can't be blank"; }
		if (strlen($barcode) <> 12)                { $error["barcode"] = "Barcode must be 12 characters"; }
		if (!$database->isBarcodeUnique($barcode)) { $error["barcode"] = "This Barcode already exists"; }
		if (!isset($lname) || empty($lname))       { $error["lname"]   = "Last Name can't be blank"; }

		// hire date might need to be reformatted from mm/dd/yyyy to yyyy-mm-dd
		if (empty($hire_date)) {
			$hire_date = date('Y-m-d 0:00:00');
		} else {
			$date = new DateTime($hire_date);
			$hire_date = $date->format('Y-m-d H:i:s');
		}
		if (empty($error)) { 

			// before you save to database... escape value
			$sql  = "insert into employees ";
			$sql .= "(  barcode,   fname,   lname,   email,   add1,   add2,   city,   st,   zip,   phone,   social,   hire_date,    emergency_contact,    emergency_number,    group_id)  ";
			$sql .= "values ";
			$sql .= "('$barcode','$fname','$lname','$email','$add1','$add2','$city','$st','$zip','$phone','$social','$hire_date', '$emergency_contact', '$emergency_number', '$group_id') ";

			/* SUCCESS */
			if ($result = $database->q($sqllo)) {
				$session->message("New employee entered successfully ");

				// the variables should be cleared
				foreach ($database->columns["employees"] as $key => $value) { 
					$$value = ""; 
				}
			}
		}

	} else {
		foreach ($list as $key => $value) { 
			if (!isset($$key)) {
				$$key = ""; 
			}
		}
	}

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"New", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 

?>
<body>
<div id="back">
	<h2>
		<form id="form1"  class="form2" action="add_employee.php" autocomplete="off" method="post"> <!-- onKeyDown="pressed(event)"> -->
			<input type="hidden"   name="is_active"  value=0>

			<table>
				<caption>New Employee</caption>
				<tr><th>Active:                </th><td><input type="checkbox" name="is_active"  value=1 checked /></td></tr>
				<tr><th>Barcode:   <?= STAR ?> </th><td><input type="text"     name="barcode"    value="<?= $barcode;    ?>" id="focus"/></td></tr>
				<tr><th>First Name:            </th><td><input type="text"     name="fname"      value="<?= $fname;      ?>" /></td></tr>
				<tr><th>Last Name: <?= STAR ?> </th><td><input type="text"     name="lname"      value="<?= $lname;      ?>" /></td></tr>
				<tr><th>email:                 </th><td><input type="email"    name="email"      value="<?= $email;      ?>" /></td></tr>
				<tr><th>Address 1:             </th><td><input type="text"     name="add1"       value="<?= $add1;       ?>" /></td></tr>
				<tr><th>Address 2:             </th><td><input type="text"     name="add2"       value="<?= $add2;       ?>" /></td></tr>
				<tr><th>City:                  </th><td><input type="text"     name="city"       value="<?= $city;       ?>" /></td></tr>
				<tr><th>State:                 </th><td><?php include ("us_states.php"); ?></td></tr>
				<tr><th>Zip Code:              </th><td><input type="text"     name="zip"        value="<?= $zip;        ?>" maxlength="10" size="10" /></td></tr>
				<tr><th>Phone:                 </th><td><input type="text"     name="phone"      value="<?= $phone;      ?>" maxlength="10" onKeyPress="return numbersOnly(event);" ></input></td></tr>
				<tr><th>SS#:                   </th><td><input type="text"     name="social"     value="<?= $social;     ?>" maxlength="10" onKeyPress="return numbersOnly(event);" ></input></td></tr>
				<tr><th>Hire Date:             </th><td><input type="text"     name="hire_date"  value="<?= $hire_date;  ?>" id="datepicker" /></td></tr>
				<tr><th>Emergency Contact:     </th><td><input type="text"     name="emergency_contact" value="<?= $emergency_contact; ?>" /></td></tr>
				<tr><th>Emergency Number:      </th><td><input type="text"     name="emergency_contact" value="<?= $emergency_contact; ?>" /></td></tr>
				<tr><th>Group:                 </th><td><select name="group_id"><?= $database->groupList() ; ?></select></td></tr>
			</table>

            <span class="employee">
				<div style="clear: both;"></div>
				<div align="center">
                    <button type="submit" class="admin_up" formaction="menu.php">back</button>
                    <button type="submit" class="admin_up">submit</button>
				</div>
				<div class="labels">
					<p><?php if (isset($error["barcode"])) echo "<br>" . $error["barcode"]; ?></p>
					<p><?php if (isset($error["lname"]  )) echo "<br>" . $error["lname"];   ?></p>
				</div>
				<br clear="all" />
				<div class="get_barcode">To generate <a href="http://generator.barcoding.com/" target="_blank">barcode jpg</a></div>
			</span>
			<?= $session->message(); ?>
		</form>
	</h2>
</div>
<script type="text/javascript" language="JavaScript" src="../mysrc.js">
</script>
</body>
</html>