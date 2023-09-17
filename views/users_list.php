<!-- view: users_list -->
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
	<div id="back">
		<h2>
			<div class="employee whitebox">
				<div id="date_time">
					<?= date('m/d/y'); ?> <span id="time"></span>
				</div>
				<div style="text-align:center;">
					<form method="post">
						<?= $table; ?><br />
						<button type='submit' name='add'    class='edit_up' formaction='manage_users.php'>Add User</button>
						<button type="submit" name="submit" class="edit_up" formaction="menu.php" >back</button>
					</form>
				</div>
				<?php if(isset($message)) { echo $message; } ?><br />
			</div>
		</h2>
	</div>
    <?php render("footer", __DIR__, []); ?>
</body>
</html>
