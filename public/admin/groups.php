<?php
	require_once("../../include/initialize.php");

	// Already logged in? Go to menu
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}


	$sql = "select * from groups";
	if ($result=$database->q($sql)) {
		$list = "";
		while ($row = $database->fetchArray($result)) {
			extract ($row);
//			$list .= "{$id} {$groupname} <br />";
			$list .= "{$id} <input type='text' value={$groupname} /><br />\n"; 
		}
	}

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Group", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 
/*

i want to list all of the groups and give us a chance to edit or delete it
block deletion if people are in the group
or ask what group do you want to move them to
*/

?>
<body>
<div id="back">
	<h2>
		<form id="form1" action="edit_employee2.php" autocomplete="off" method="post"> <!-- onKeyDown="pressed(event)"> -->
			<span class="employee">
				<span style=" margin:0 auto; display:table; float:left;">
					<div align="center">Edit Groups</div>

					<div class="info">
						<p><?= $list; ?></p>
					</div>
					<br />
					<div align="center">
						<input type="submit" name="submit" value="add"  class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" />
						<input type="submit" name="submit" value="save" class="admin_up" onMouseUp="this.className='admin_up'" onMouseDown="this.className='admin_down'" />
					</div>
				</span>
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