<?php 
	require_once("../include/initialize.php"); 

	$next_page   = isset($_SESSION["popup_next_page"]) ? htmlentities($_SESSION["popup_next_page"], ENT_QUOTES) : "index.php";
	$calling_url = isset($_SESSION["calling_url"])     ? htmlentities($_SESSION["calling_url"],     ENT_QUOTES) : "index.php";
	$timeout     = isset($_SESSION["timeout"])         ? htmlentities($_SESSION["timeout"],         ENT_QUOTES) : "";

	if (!isset($_SESSION["output"])) {
		redirectTo($calling_url);
	}

	$data = array("background_image" => "Clock-Wallpaper-1.jpg", "background_color" => "FCF");
	render("header", __DIR__, $data); 
?>
<!-- this is the popup screen -->
<body>
	<div id="back" <?= $timeout ?> >
		<div class="popup">
<?php		echo $_SESSION["output"]; 
			unset($_SESSION["output"]);
?>			<a class="close" href="<?php echo $calling_url; ?>"></a>
		</div>
		<a class="overlay" href="<?php echo $calling_url; ?>">
		</a>
	</div>
    <?php render("footer", __DIR__, []); ?>
</body>
</html>