<?php 
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}

	// Create and set variables from columns for employee table
	// ASSUMING first one is the primary key, array_shift
//	$temp = array_shift($list);

	if (isset($_POST["submit"]) && $_POST["submit"]=="submit") {
		/*
		 *	here we come in from ourselves to update employee information
		 */

		// here we get all the values from $_POST
		$list = $database->columns["employees"];
		unset($list["is_active"]); // get rid of flag, can't use the quotes around new value

		foreach ($list as $key => $value) {
//echo $value . " - " . gettype($_POST[$value]) . " - " . $_POST[$value] . "<br />";
			if (isset($_POST[$value])) {
				$x = $_POST[$value];
				switch (gettype($x)) {
				case "string":
					$$value = $database->escapeValue(strtoupper($_POST[$value]));
					break;
				case "integer":
					$$value = $database->escapeValue(intval($_POST[$value]));
					break;
				default:
					$$value = $database->escapeValue($_POST[$value]);
				}
			} else {
				$$value = "";
			}
/*			if (gettype($value)== "string")
				$$value = isset($_POST["$value"]) ? $database->escapeValue(strtoupper($_POST[$value])) : "";
			if (gettype($value)== "integer")
				$$value = isset($_POST["$value"]) ? $database->escapeValue(intval($_POST[$value])) : "";
*/
		}
//die();
		$is_active = (isset($_POST["is_active"]) && $_POST["is_active"]==1) ? "true" : "false";

		// hire date might need to be reformatted from mm/dd/yyyy to yyyy-mm-dd
		if (empty($_POST["hire_date"])) {
			$_POST["hire_date"] = strftime('%Y-%m-%d %H:%M:00',time());
		} else {
			$_POST["hire_date"] = strftime('%Y-%m-%d %H:%M:00',strtotime($_POST["hire_date"]));
		}

		$temp = array_shift($list); // get rid of employeeid, don't want to update that

		$sql  = "update employees set ";
		foreach ($_POST as $key => $value) {
			if (in_array($key, $list)) {
				$sql .= "{$key}='" . $value . "', ";
			}
		}
//		$sql = substr($sql,0,-2) . " ";
		$sql .= "is_active={$is_active} ";
		$sql .= "where employeeid='{$employeeid}' ";

		$name = trim($fname . " " . $lname);
		$name = preg_replace('/\s+/', ' ', $name); // Remove multipleblanks
		if ($result=$database->q($sql)) {
			$session->message("Updated " . $name . " Successfully "); // . "<br />" . $sql);
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
				$hire_date = strftime('%m/%d/%Y',time());
			} else {
				$hire_date = strftime('%m/%d/%Y',strtotime($hire_date));
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
		<form id="form1" action="edit_employee2.php" autocomplete="off" method="post"> <!-- onKeyDown="pressed(event)"> -->
			<span class="employee">
				<span style=" margin:0 auto; display:table; float:left;">
					<div align="center">Edit Employee</div>

					<div class="labels">
                        <p><label for="is_active">Active:    </label></p>
						<?php echo (isset($error["barcode"])) ? "<p style='color:red'>" : "<p>"; ?>
                        <label for="focus">Barcode:</label><span style="color:#FF0000">*</span></p>
                        <p><label for="fname">First Name:    </label></p>
						<?php echo (isset($error["lname"])) ? "<p style='color:red'>" : "<p>"; ?>
                        <p><label for="lname">Last Name:<span style="color:#FF0000">*</span></label></p>
                        <p><label for="email">email:         </label></p>
                        <p><label for="add1">Address 1:      </label></p>
                        <p><label for="add2">Address 2:      </label></p>
                        <p><label for="city">City:           </label></p>
                        <p><label for="us_states">State:     </label></p>
                        <p><label for="zip">Zip Code:        </label></p>
                        <p><label for="phone">Phone:         </label></p>
                        <p><label for="social">SS#:          </label></p>
                        <p><label for="hire_date">Hire Date: </label></p>
                        <p><label for="emergency_contact">Emergency Contact: </label></p>
                        <p><label for="emergency_number">Emergency Number:   </label></p>
                        <p><label for="group_id">Group:      </label></p>
					</div>

					<div class="info" style="float:left;">
						<input type="hidden"   name="employeeid" id="employeeid" value="<?php echo $employeeid; ?>" />
						<input type="checkbox" name="is_active"  id="is_active"  value=1 '<?= $checked; ?>'  /> <br />
						<input type="text"     name="barcode"    id="focus"      value="<?php echo $barcode;    ?>" /> <br />
						<input type="text"     name="fname"      id="fname"      value="<?php echo $fname;      ?>" /> <br />
						<input type="text"     name="lname"      id="lname"      value="<?php echo $lname;      ?>" /> <br />
						<input type="email"    name="email"      id="email"      value="<?php echo $email;      ?>" /> <br />
						<input type="text"     name="add1"       id="add1"       value="<?php echo $add1;       ?>" /> <br />
						<input type="text"     name="add2"       id="add2"       value="<?php echo $add2;       ?>" /> <br />
						<input type="text"     name="city"       id="city"       value="<?php echo $city;       ?>" /> <br />
						<?php include ("us_states.php"); ?><br />
						<input type="text"     name="zip"        id="zip"        value="<?php echo $zip;        ?>" maxlength="10" size="10" /><br />
						<input type="tel"      name="phone"      id="phone"      value="<?php echo $phone;      ?>" maxlength="10" onKeyPress="return numbersonly(event);" /> <br />
						<input type="text"     name="social"     id="social"     value="<?php echo $social;     ?>" maxlength="10" onKeyPress="return numbersonly(event);" /> <br />
						<input type="text"     name="hire_date"  id="datepicker" value="<?php echo $hire_date;  ?>" /> <br />
						<input type="text"     name="emergency_contact" id="emergency_contact" value="<?php echo $emergency_contact; ?>"   /> <br />
						<input type="tel"      name="emergency_number"  id="emergency_number"  value="<?php echo $emergency_number;  ?>" maxlength="10" onKeyPress="return numbersonly(event);" /> <br />
						<select name="group_id" id="group_id">
							<?= $database->groupList($group_id) ; ?>
						</select>
					</div>
					<div class="clear"></div>
					<div class="centered-div">
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
                <div class="clear"></div>
				<div class="get_barcode">To generate <a href="http://generator.barcoding.com/" target="_blank">barcode jpg</a></div>
			</span>
			<span class="employee">
				<?php echo $session->message(); ?>
			</span>
		</form>
	</h2>
</div>
<?php render("footer", __DIR__, []); ?>
</body>
</html>