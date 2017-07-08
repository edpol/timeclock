<!-- view: users_edit -->
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
	<div id="back">
		<h2>
			<div class="employee whitebox">
				<div id="date_time">
					<?= date('m/d/y'); ?> <span id="time"></span>
				</div>
				<div>
					<form method="post">
						<div class="labels">
							<p>username:   </p>
							<p>First Name: </p>
							<p>Last Name:  </p>
							<p>Password:   </p>
						</div>
<?php
	$username = isset($_POST["username"]) ? $_POST["username"] : "";
	$fname    = isset($_POST["fname"])    ? $_POST["fname"]    : "";
	$lname    = isset($_POST["lname"])    ? $_POST["lname"]    : "";
?>
						<div class="info" style="float:left;" >
							<input type='text'     name='username' value='<?= $username; ?>' id="mandatoryUsername" /> <span id="errorUsername"></span> <br />
							<input type='text'     name='fname'    value='<?= $fname;    ?>' /> <br />
							<input type='text'     name='lname'    value='<?= $lname;    ?>' /> <br />
							<input type='password' name='password' value=''                  id="mandatoryPassword" /> <span id="errorPassword"></span><br />
						</div>
						<br clear="all" />

						<div align="center">
							<button type="submit" name="submit" class="edit_up" formaction="manage_users.php">back</button>
							<button id="frmContact" type='submit' name='save'   class='edit_up' formaction='manage_users.php'>Submit</button>
						</div>
					</form>
				</div>
				<?= $message; ?><br />
			</div>
		</h2>
	</div>
<script type="text/javascript" language="JavaScript" src="<?=$levels;?>mysrc.js">
</script>
</body>
</html>
