<?php
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!$session->is_logged_in()) {
		redirect_to("login.php");
	}

	$list = $database->columns["employee"];

	$error = array();
	if (isset($_POST["submit"]) && $_POST["submit"]=="submit") {
		foreach ($_POST as $key =>$value) {
			$$key = $database->escape_value(strtoupper($value));
		}

		if (!isset($barcode) || empty($barcode))     { $error["barcode"] = "Barcode can't be blank"; }
		if (strlen($barcode) <> 12)                  { $error["barcode"] = "Barcode must be 12 characters"; }
		if (!$database->is_barcode_unique($barcode)) { $error["barcode"] = "This Barcode already exists"; }
		if (!isset($lname) || empty($lname))         { $error["lname"]   = "Last Name can't be blank"; }

		// hire date might need to be reformatted from mm/dd/yyyy to yyyy-mm-dd
		if (empty($hire_date)) {
			$hire_date = date('Y-m-d 0:00:00');
		} else {
			$date = new DateTime($hire_date);
			$hire_date = $date->format('Y-m-d H:i:s');
		}
		if (empty($error)) { 

			// before you save to database... escape value
			$sql  = "insert into employee ";
			$sql .= "(  barcode,   fname,   lname,   email,   add1,   add2,   city,   st,   zip,   phone,   social,   hire_date,    emergency_contact,    emergency_number,    grp)  ";
			$sql .= "values ";
			$sql .= "('$barcode','$fname','$lname','$email','$add1','$add2','$city','$st','$zip','$phone','$social','$hire_date', '$emergency_contact', '$emergency_number', '$grp') ";

			/* SUCCESS */
			if ($result = $database->query($sql)) {
				$session->message("New employee entered successfully ");

				// the variables should be cleared
				foreach ($database->columns["employee"] as $key => $value) { 
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
		<form id="form1" action="add_employee.php" autocomplete="off" method="post"> <!-- onKeyDown="pressed(event)"> -->
			<span class="employee">
				<span style=" margin:0 auto; display:table; float:left;">
					<div align="center">New Employee</div>

					<div class="labels">
						<p>Active:     </p>
						<?php echo (isset($error["barcode"])) ? "<p style='color:red'>" : "<p>"; ?>
						Barcode:<font color="#FF0000">*</font></p>
						<p>First Name: </p>
						<?php echo (isset($error["lname"])) ? "<p style='color:red'>" : "<p>"; ?>
						Last Name:<font color="#FF0000">*</font></p>
						<p>email:             </p>
						<p>Address 1:         </p>
						<p>Address 2:         </p>
						<p>City:              </p>
						<p>State:             </p>
						<p>Zip Code:          </p>
						<p>Phone:             </p>
						<p>SS#:               </p>
						<p>Hire Date:         </p>
						<p>Emergency Contact: </p>
						<p>Emergency Number:  </p>
						<p>Group:             </p>
					</div>

					<div class="info" style="float:left;">
						<!-- we don't have employeeid yet  -->
						<input type="hidden"   name="is_active"  value=0>
						<input type="checkbox" name="is_active"  value=1 checked /><br />
						<input type="text"     name="barcode"    value="<?= $barcode;    ?>" id="focus"/> <br />
						<input type="text"     name="fname"      value="<?= $fname;      ?>" /> <br />
						<input type="text"     name="lname"      value="<?= $lname;      ?>" /> <br />
						<input type="email"    name="email"      value="<?= $email;      ?>" /> <br />
						<input type="text"     name="add1"       value="<?= $add1;       ?>" /> <br />
						<input type="text"     name="add2"       value="<?= $add2;       ?>" /> <br />
						<input type="text"     name="city"       value="<?= $city;       ?>" /> <br />
						<?php include ("us_states.php"); ?><br />
						<input type="text"     name="zip"        value="<?= $zip;        ?>" maxlength="10" size="10" /><br />
						<input type="text"     name="phone"      value="<?= $phone;      ?>" maxlength="10" onKeyPress="return numbersonly(event);" ></input><br />
						<input type="text"     name="social"     value="<?= $social;     ?>" maxlength="10" onKeyPress="return numbersonly(event);" ></input><br />
						<input type="text"     name="hire_date"  value="<?= $hire_date;  ?>" id="datepicker" /> <br />
						<input type="text"     name="emergency_contact" value="<?= $emergency_contact;   ?>" /> <br />
						<input type="text"     name="emergency_number"  value="<?= $emergency_number;    ?>" maxlength="10" onKeyPress="return numbersonly(event);" ></input><br />
						<select name="grp">
							<?= $database->group_list() ; ?>
						</select>
					</div>
					<br clear="all" />
					<div align="center">
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