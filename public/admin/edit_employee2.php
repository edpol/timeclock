<?php 
	require_once("../../include/initialize.php");
    global $session, $database;

	// Already logged in? Go to menu
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}

	// Create and set variables from columns for employee table
	// ASSUMING first one is the primary key, array_shift
    //	$temp = array_shift($list);

	if (isset($_POST["submit"]) && $_POST["submit"]=="submit") {
		$session->verifyCsrf();
		/*
		 *	here we come in from ourselves to update employee information
		 */

		$list = $database->columns["employees"];
		// Remove columns handled separately
		$employeeid_pk = (int)$_POST["employeeid"];
		unset($list["employeeid"]); // never update PK
		unset($list["is_active"]);  // boolean — appended manually below

		// Reformat hire_date before building params.
		if (empty($_POST["hire_date"])) {
			$_POST["hire_date"] = date('Y-m-d H:i:00', time());
		} else {
			$_POST["hire_date"] = date('Y-m-d H:i:00', strtotime($_POST["hire_date"]));
		}

		$is_active = (isset($_POST["is_active"]) && $_POST["is_active"] == 1) ? 1 : 0;

		// Build parameterized UPDATE — column names come from the whitelist, values bound via ?
		$set_parts = [];
		$params    = [];
		foreach ($list as $column) {
			$set_parts[] = "{$column} = ?";
			$params[]    = isset($_POST[$column]) ? strtoupper($_POST[$column]) : "";
		}
		$set_parts[] = "is_active = ?";
		$params[]    = $is_active;
		$params[]    = $employeeid_pk; // for WHERE clause

		$fname = isset($_POST["fname"]) ? strtoupper($_POST["fname"]) : "";
		$lname = isset($_POST["lname"]) ? strtoupper($_POST["lname"]) : "";

		$sql = "UPDATE employees SET " . implode(", ", $set_parts) . " WHERE employeeid = ?";

		$name = trim($fname . " " . $lname);
		$name = preg_replace('/\s+/', ' ', $name); // Remove multiple blanks
		if ($database->db_query($sql, $params)) {
			$session->message("Updated " . $name . " Successfully ");
		} else {
			$session->message("Error Updating Record ");
		}
		unset($_SESSION["employeeid"]);
		redirectTo("edit_employee.php");

	} else {
		/*
		 *	here we come in from edit_employee.php displaying employee information
		 */

		if ( isset($_SESSION["employeeid"]) ) {
			$employeeid = $_SESSION["employeeid"];
			unset($_SESSION["employeeid"]);
		} else {
			$employeeid = 0;
		}

		$timeclock = new Timeclock();
		$row = $timeclock->getEmployeeData($employeeid);

		if ($row) {

			// Here set the values from database
			foreach ($row as $key => $value) {
				$$key = $value; 
				//echo $key . " " . $value . "<br />";
			}
			$checked = ($is_active) ? " checked " : ""; 

			if (empty($hire_date)) {
                $hire_date = date('m/d/Y', time());
			} else {
                $hire_date = date('m/d/Y', strtotime($hire_date));
			}
		} else {
			$session->message("I don't know what happened, start over");
			redirectTo("edit_employee.php");
		}
	}

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Edit", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 
?>
<body>
<div id="back">
	<h2>
		<form id="form1" action="edit_employee2.php" autocomplete="off" method="post">
			<?= $session->csrfField(); ?>
			<span class="employee">
                <input type="hidden"   name="employeeid" id="employeeid" value="<?= $employeeid; ?>" />
                <table class="small">
                    <caption>Edit Employee</caption>
                    <tr>
                        <th><label for="is_active">Active:    </label></th>
                        <td><input type="checkbox" name="is_active"  id="is_active"  value=1 '<?= $checked; ?>'  /> <br /></td>
                    </tr>
                    <tr>
                        <th>
                            <?php $color = (isset($error["barcode"])) ? "style='color:red'" : ""; ?>
                            <label <?= $color; ?> for="focus">Barcode:</label><?= RED_STAR; ?>
                        </th>
                        <td><input type="text"     name="barcode"    id="focus"      value="<?= $barcode;    ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="fname">First Name:    </label></th>
                        <td><input type="text"     name="fname"      id="fname"      value="<?= $fname;      ?>" /></td>
                    </tr>
                    <tr>
                        <th>
                            <?php $color = (isset($error["lname"])) ? "style='color:red'" : ""; ?>
                            <label <?= $color; ?> for="lname">Last Name:</label><?= RED_STAR; ?>
                        </th>
                        <td><input type="text"     name="lname"      id="lname"      value="<?= $lname;      ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="email">email:         </label></th>
                        <td><input type="email"    name="email"      id="email"      value="<?= $email;      ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="add1">Address 1:      </label></th>
                        <td><input type="text"     name="add1"       id="add1"       value="<?= $add1;       ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="add2">Address 2:      </label></th>
                        <td><input type="text"     name="add2"       id="add2"       value="<?= $add2;       ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="city">City:           </label></th>
                        <td><input type="text"     name="city"       id="city"       value="<?= $city;       ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="us_states">State:     </label></th>
                        <td><?php include ("us_states.php"); ?></td>
                    </tr>
                    <tr>
                        <th><label for="zip">Zip Code:        </label></th>
                        <td><input type="text"     name="zip"        id="zip"        value="<?= $zip;        ?>" maxlength="10" size="10" /></td>
                    </tr>
                    <tr>
                        <th><label for="phone">Phone:         </label></th>
                        <td><input type="tel"      name="phone"      id="phone"      value="<?= $phone;      ?>" maxlength="10" inputmode="numeric" oninput="digitsOnly(this);" /></td>
                    </tr>
                    <tr>
                        <th><label for="social">SS#:          </label></th>
                        <td><input type="text"     name="social"     id="social"     value="<?= $social;     ?>" maxlength="10" inputmode="numeric" oninput="digitsOnly(this);" /></td>
                    </tr>
                    <tr>
                        <th><label for="datepicker">Hire Date: </label></th>
                        <td><input type="text"     name="hire_date"  id="datepicker" value="<?= $hire_date;  ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="emergency_contact">Emergency Contact: </label></th>
                        <td><input type="text"     name="emergency_contact" id="emergency_contact" value="<?= $emergency_contact; ?>"   /></td>
                    </tr>
                    <tr>
                        <th><label for="emergency_number">Emergency Number:   </label></th>
                        <td><input type="tel"      name="emergency_number"  id="emergency_number"  value="<?= $emergency_number;  ?>" maxlength="10" inputmode="numeric" oninput="digitsOnly(this);" /></td>
                    </tr>
                    <tr>
                        <th><label for="group_id">Group:      </label></th>
                        <td>
                            <select name="group_id" id="group_id">
							    <?= $database->groupList($group_id) ; ?>
						    </select>
                        </td>
                    </tr>
                </table>

                <div class="centered-div">
                    <input type="button" name="submit" value="back"   class="admin_up" onClick="parent.location='menu.php'" />
                    <input type="submit" name="submit" value="submit" class="admin_up" />
                </div>

				<div class="labels">
					<p>&nbsp;</p>
					<p><?php if (isset($error["barcode"])) echo $error["barcode"]; ?></p>
					<p>&nbsp;</p>
					<p><?php if (isset($error["lname"])) echo $error["lname"]; ?></p>
				</div>
                <div class="clear"></div>
				<div class="get_barcode">To generate <a href="http://generator.barcoding.com/" target="_blank">barcode jpg</a></div>
			</span>
			<span class="employee">
				<?php echo $session->message(); ?>
			</span>
		</form>
	</h2>
</div>
<?php render("footer", __DIR__); ?>
</body>
<?php render("html_close", __DIR__); ?>
