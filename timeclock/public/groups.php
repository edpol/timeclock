<?php 
	require_once("../include/initialize.php");

	$data = array("background_image" => "search.jpg", "background_color" => "FCF");
	render("header", __DIR__, $data); 
?>
<!-- this clears the form every time you navigate away or reload the page --> 
<body>
	<div id="back">
		<h1>
			<div style="color:#FCF; font-weight:bold; font-size:36px;">Inquire</div>
			<br />
			<?= date('m/d/y');?><br />
			<?= $database->list_names(); ?>
		</h1>
	</div>
<script type="text/javascript" language="JavaScript" src="mysrc.js">
</script>
</body>
</html>
