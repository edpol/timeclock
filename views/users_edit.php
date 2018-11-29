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
<?php 				if (!empty($row)) {  ?>
						<div class="labels">
							<p>userid:     </p>
							<p>username:   </p>
							<p>First Name: </p>
							<p>Last Name:  </p>
							<p>Password:   </p>
						</div>
						<div class="info" style="float:left;" >
							<?= $row['userid'];   ?> <br /> <input type='hidden'   name='userid'   value='<?= $row["userid"];?>' />
							<?= $row['username']; ?> <br />
							<input type='text'     name='fname'    value='<?= $row["fname"]; ?>' /> <br />
							<input type='text'     name='lname'    value='<?= $row["lname"]; ?>' /> <br />
							<input type='password' name='password' value='' id='focus' autofocus /> <br />
						</div>
						<br clear="all" />

<?php				}	?>
						<div align="center">
							<button type="submit" name="submit" class="edit_up" formaction="manage_users.php">back</button>
							<button type='submit' name='update' class='edit_up' formaction='manage_users.php'>Submit</button>
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
