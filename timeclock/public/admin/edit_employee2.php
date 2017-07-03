<?php 
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!$session->is_logged_in()) {
		redirect_to("login.php");
	}

	// Create and set variables from columns for employee table
	// ASSUMING first one is the primary key, array_shift
//	$temp = array_shift($list);

	if (isset($_POST["submit"]) && $_POST["submit"]=="submit") {
		/*
		 *	here we come in from ourselves to update employee information
		 */

		// here we get all the values from $_POST
		$list = $database->columns["employee"];
		unset($list["is_active"]); // get rid of flag, can't use the quotes around new value

		foreach ($list as $key => $value) {
//echo $value . " - " . gettype($_POST[$value]) . " - " . $_POST[$value] . "<br />";
			if (isset($_POST[$value])) {
				$x = $_POST[$value];
				switch (gettype($x)) {
				case "string":
					$$value = $database->escape_value(strtoupper($_POST[$value]));
					break;
				case "integer":
					$$value = $database->escape_value(intval($_POST[$value]));
					break;
				default:
					$$value = $database->escape_value($_POST[$value]);
				}
			} else {
				$$value = "";
			}
/*			if (gettype($value)== "string")
				$$value = isset($_POST["$value"]) ? $database->escape_value(strtoupper($_POST[$value])) : "";
			if (gettype($value)== "integer")
				$$value = isset($_POST["$value"]) ? $database->escape_value(intval($_POST[$value])) : "";
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

		$sql  = "update employee set ";
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
		if ($result=$database->query($sql)) {
			$session->message("Updated " . $name . " Successfully "); // . "<br />" . $sql);
		} else {
			$session->message("Error Updating Record ");
		}
		unset($_SESSION["employeeid"]);
		redirect_to("edit_employee.php");

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

		$timeclock = new timeclock();
		$row = $timeclock->get_employee_data($employeeid);

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
			redirect_to("edit_employee.php");
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
						<p>Active:     </p>
						<?php echo (isset($error["barcode"])) ? "<p style='color:red'>" : "<p>"; ?>
						Barcode:<font color="#FF0000">*</font></p>
						<p>First Name: </p>
						<?php echo (isset($error["lname"])) ? "<p style='color:red'>" : "<p>"; ?>
						Last Name:<font color="#FF0000">*</font></p>
						<p>email:      </p>
						<p>Address 1:  </p>
						<p>Address 2:  </p>
						<p>City:       </p>
						<p>State:      </p>
						<p>Zip Code:   </p>
						<p>Phone:      </p>
						<p>SS#:        </p>
						<p>Hire Date:  </p>
						<p>Emergency Contact: </p>
						<p>Emergency Number:  </p>
						<p>Group:      </p>
					</div>

					<div class="info" style="float:left;">
						<input type="hidden"   name="employeeid" value="<?php echo $employeeid; ?>" />
						<input type="hidden"   name="is_active"  value=0>
						<input type="checkbox" name="is_active"  value=1 <?php echo "$checked"; ?>  /> <br />
						<input type="text"     name="barcode"    value="<?php echo $barcode;    ?>" id="focus" /> <br />
						<input type="text"     name="fname"      value="<?php echo $fname;      ?>" /> <br />
						<input type="text"     name="lname"      value="<?php echo $lname;      ?>" /> <br />
						<input type="email"    name="email"      value="<?php echo $email;      ?>" /> <br />
						<input type="text"     name="add1"       value="<?php echo $add1;       ?>" /> <br />
						<input type="text"     name="add2"       value="<?php echo $add2;       ?>" /> <br />
						<input type="text"     name="city"       value="<?php echo $city;       ?>" /> <br />
						<?php include ("us_states.php"); ?><br />
						<input type="text"     name="zip"        value="<?php echo $zip;        ?>" maxlength="10" size="10" /><br />
						<input type="tel"      name="phone"      value="<?php echo $phone;      ?>" maxlength="10" onKeyPress="return numbersonly(event);" /> <br />
						<input type="text"     name="social"     value="<?php echo $social;     ?>" maxlength="10" onKeyPress="return numbersonly(event);" /> <br />
						<input type="text"     name="hire_date"  value="<?php echo $hire_date;  ?>" id="datepicker" /> <br />
						<input type="text"     name="emergency_contact" value="<?php echo $emergency_contact; ?>"   /> <br />
						<input type="tel"      name="emergency_number"  value="<?php echo $emergency_number;  ?>" maxlength="10" onKeyPress="return numbersonly(event);" /> <br />
						<select name="grp">
							<?= $database->group_list($grp) ; ?>
						</select>
					</div>
					<br clear="all" />
					<div align="center">
						<input type="button" name="submit" value="back"   class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" onClick="parent.location='menu.php'" />
						<input type="submit" name="submit" value="submit" class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" />
					</div>
				</span>
				<div class="labels">
					<p>&nbsp;</p>
					<p><?php if (isset($error["barcode"])) echo $error["barcode"]; ?></p>
					<p>&nbsp;</p>
					<p><?php if (isset($error["lname"])) echo $error["lname"]; ?></p>
				</div>
				<br clear="all" />
				<div class="get_barcode">To generate <a href="http://generator.barcoding.com/" target="_blank">barcode jpg</a></div>
			</span>
			<span class="employee">
				<?php echo $session->message(); ?>
			</span>
		</form>
	</h2>
</div>
<script type="text/javascript" language="JavaScript" src="../mysrc.js">
</script>
</body>
</html>