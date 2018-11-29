<!-- view: id_lname -->
<!-- this clears the form every time you navigate away or reload the page --> 
<body onLoad="document.forms[0].reset();" onUnload="document.forms[0].reset();">
	<div id="back">
		<h2>
			<span class="employee whitebox">
				<div style="text-align:center;">
					<?= $title; ?><br />
				</div>
<!-- OK, wee need to lookup in users table NOT employee table -->
				<form id="form1" action="<?= $levels . $form_page; ?>" autocomplete="off" method="post" onKeyDown="pressed(event)"> <!-- doesnt need a submit button, just press return -->
					<div class="labels">
						<p>ID Number: </p>
						<p>Last Name: </p>
					</div>
					<div style="float:left;">
						<input type="text"   name="barcode"     value="" /><br />
						<input type="text"   name="lastname"    value="" id="focus" autofocus /><br />  <!-- onclick="openWindow('hello.htm')" --> 
						<input type="hidden" name="calling_url" value="<?= $return_page; ?>" />
					</div>
					<br clear="all" />
					<div style="padding-top:20px; text-align:center;">
						<button type="submit" name="submit" class="edit_up" >submit</button>
						<button type="submit" name="submit" class="edit_up" formaction="menu.php" >back</button>
					</div>
				</form>
				<div id="date_time">
					<?= date('m/d/y'); ?> <span id="time"></span>
				</div>
				<?= $message; ?>
			</span>
		</h2>
	</div>
<script type="text/javascript" language="JavaScript" src="<?=$levels;?>mysrc.js">
</script>
</body>
</html>
