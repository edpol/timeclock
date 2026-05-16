<?php
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}

	$list = $database->columns["employees"];

	$error = array();
	if (isset($_POST["submit"]) && $_POST["submit"]=="submit") {
		$session->verifyCsrf();

		// Pull and sanitise each field individually (not a blind POST extract).
		$barcode           = strtoupper(trim($_POST["barcode"]           ?? ""));
		$fname             = strtoupper(trim($_POST["fname"]             ?? ""));
		$lname             = strtoupper(trim($_POST["lname"]             ?? ""));
		$email             = strtoupper(trim($_POST["email"]             ?? ""));
		$add1              = strtoupper(trim($_POST["add1"]              ?? ""));
		$add2              = strtoupper(trim($_POST["add2"]              ?? ""));
		$city              = strtoupper(trim($_POST["city"]              ?? ""));
		$st                = strtoupper(trim($_POST["st"]                ?? ""));
		$zip               = strtoupper(trim($_POST["zip"]               ?? ""));
		$phone             = trim($_POST["phone"]             ?? "");
		$social            = trim($_POST["social"]            ?? "");
		$emergency_contact = strtoupper(trim($_POST["emergency_contact"] ?? ""));
		$emergency_number  = trim($_POST["emergency_number"]  ?? "");
		$group_id          = trim($_POST["group_id"]          ?? "");

		if (empty($barcode))                       { $error["barcode"] = "Barcode can't be blank"; }
		elseif (strlen($barcode) !== 12)           { $error["barcode"] = "Barcode must be 12 characters"; }
		elseif (!$database->isBarcodeUnique($barcode)) { $error["barcode"] = "This Barcode already exists"; }
		if (empty($lname))                         { $error["lname"]   = "Last Name can't be blank"; }

		// hire date: reformat from mm/dd/yyyy to yyyy-mm-dd
		$raw_hire = trim($_POST["hire_date"] ?? "");
		$hire_date = empty($raw_hire)
			? date('Y-m-d 00:00:00')
			: (new DateTime($raw_hire))->format('Y-m-d H:i:s');

		if (empty($error)) {
			$sql  = "INSERT INTO employees ";
			$sql .= "(barcode, fname, lname, email, add1, add2, city, st, zip, phone, social, hire_date, emergency_contact, emergency_number, group_id) ";
			$sql .= "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ";

			$database->db_query($sql, [
				$barcode, $fname, $lname, $email, $add1, $add2, $city, $st,
				$zip, $phone, $social, $hire_date, $emergency_contact, $emergency_number, $group_id
			]);

			$session->message("New employee entered successfully ");
			// clear the form
			foreach ($database->columns["employees"] as $value) {
				$$value = "";
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
		<form id="form1" action="add_employee.php" autocomplete="off" method="post">
			<?= $session->csrfField(); ?>
			<span class="employee">
                <table class="small">
                    <caption>New Employee</caption>
                    <tr><th><label for="is_active">Active:</label></th>
                        <td>
                            <input type="hidden"   name="is_active"  value=0>
                            <input type="checkbox" name="is_active"  value=1 checked id="is_active"/>
                        </td>
                    </tr>
                    <tr><th><label for="focus">Barcode:<?= RED_STAR; ?>      </label></th><td><input type="text"    id="focus"             name="barcode"           value="<?= $barcode           ?? ""; ?>" /></td></tr>
                    <tr><th><label for="fname">First Name:                   </label></th><td><input type="text"    id="fname"             name="fname"             value="<?= $fname             ?? ""; ?>" /></td></tr>
                    <tr><th><label for="lname">Last Name:<?= RED_STAR; ?>    </label></th><td><input type="text"    id="lname"             name="lname"             value="<?= $lname             ?? ""; ?>" /></td></tr>
                    <tr><th><label for="email">email:                        </label></th><td><input type="email"   id="email"             name="email"             value="<?= $email             ?? ""; ?>" /></td></tr>
                    <tr><th><label for="add1">Address 1:                     </label></th><td><input type="text"    id="add1"              name="add1"              value="<?= $add1              ?? ""; ?>" /></td></tr>
                    <tr><th><label for="add2">Address 2:                     </label></th><td><input type="text"    id="add2"              name="add2"              value="<?= $add2              ?? ""; ?>" /></td></tr>
                    <tr><th><label for="city">City:                          </label></th><td><input type="text"    id="city"              name="city"              value="<?= $city              ?? ""; ?>" /></td></tr>
                    <tr><th><label for="states">State:                       </label></th><td><?php include ("us_states.php"); ?></td></tr>
                    <tr><th><label for="zip">Zip Code:                       </label></th><td><input type="text"    id="zip"               name="zip"               value="<?= $zip               ?? ""; ?>" maxlength="10" size="10" /></td></tr>
                    <tr><th><label for="phone">Phone:                        </label></th><td><input type="text"    id="phone"             name="phone"             value="<?= $phone             ?? ""; ?>" maxlength="10" onKeyPress="return numbersonly(event);" ></td></tr>
                    <tr><th><label for="social">SS#:                         </label></th><td><input type="text"    id="social"            name="social"            value="<?= $social            ?? ""; ?>" maxlength="10" onKeyPress="return numbersonly(event);" /></td></tr>
                    <tr><th><label for="datepicker">Hire Date:               </label></th><td><input type="text"    id="datepicker"        name="hire_date"         value="<?= $hire_date         ?? ""; ?>" /></td></tr>
                    <tr><th><label for="emergency_contact">Emergency Contact:</label></th><td><input type="text"    id="emergency_contact" name="emergency_contact" value="<?= $emergency_contact ?? ""; ?>" /> </td></tr>
                    <tr><th><label for="emergency_number">Emergency Number:  </label></th><td><input type="text"    id="emergency_number"  name="emergency_number"  value="<?= $emergency_number  ?? ""; ?>" maxlength="10" onKeyPress="return numbersonly(event);" ></td></tr>
                    <tr><th><label for="group_id">Group:                     </label></th><td><select               id="group_id"          name="group_id"><?= $database->groupList() ; ?></select></td></tr>
                </table>

                <div style="margin:10px; text-align: center;">
                    <input type="button" name="submit" value="back"   class="admin_up" onClick="parent.location='menu.php'" />
                    <input type="submit" name="submit" value="submit" class="admin_up" />
                </div>
            </span>
            <div class="labels">
                <p>&nbsp;</p>
                <p><?php if (isset($error["barcode"])) echo $error["barcode"]; ?></p>
                <p>&nbsp;</p>
                <p><?php if (isset($error["lname"])) echo $error["lname"]; ?></p>
            </div>
            <div class="get_barcode">To generate <a href="https://barcode.tec-it.com/en/UPCA" target="_blank">barcode jpg</a></div>
			<?= $session->message(); ?>
		</form>
	</h2>
</div>
<?php render("footer", __DIR__, []); ?>
</body>
</html>