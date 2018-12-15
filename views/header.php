<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="author" content="Edward Pol" />
	<meta http-equiv="expires" content="0">
	<meta name="description" content="Company timeclock" />
	<link rel="shortcut icon" href="<?= $levels ?>images/index.png" type="image/png"  />
	<link rel="stylesheet" type="text/css" href=<?= "'" . $levels . "cssfiles/styles.css'"; ?> />
	<link rel="stylesheet" type="text/css" href=<?= "'" . $levels , "cssfiles/popup.css'";  ?> />
	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
	<title>Timeclock<?php if (isset($tab)) echo " | {$tab}"; ?></title>
	<style>
<?php if (isset($background_body_color)) {echo "\t\tbody { background-color:{$background_body_color}; }\n";} ?>
		input:focus, select:focus { background-color:#<?= $background_color; ?>; }
		#back{position:absolute; width:100%; height:100%; display:table;
		  background: url(<?= $levels . "images/" . $background_image; ?>) no-repeat center center fixed; 
			-webkit-background-size: cover;
			   -moz-background-size: cover;
			     -o-background-size: cover;
			        background-size: cover;
		}
	</style>
<?php
/*
	Update: Matt Litherland writes in to say that anyone trying to use the   
	above IE filters and having problems with scrollbars or dead links or   
	whatever else (like Pierre above) should try NOT using them on the html or 
	body element. But instead a fixed position div with 100% width and height.
*/
?>
</head>
<!-- view: header.php -->
