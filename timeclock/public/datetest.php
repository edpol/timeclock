<?php
	function nozero($timestamp) {
		$m = strftime("%m",$timestamp);
		if (substr($m,0,1)=="0") $m = substr($m,1,1);
		$d = strftime("%d",$timestamp);
		if (substr($d,0,1)=="0") $d = substr($d,1,1);
		$y = strftime("%y",$timestamp);
		return "{$m}/{$d}/{$y} ";
	}

	function nozerotime($timestamp) {
		$H = strftime("%H",$timestamp);
		if (substr($H,0,1)=="0") $H = substr($H,1,1);
		$M = strftime("%M",$timestamp);
		$S = strftime("%S",$timestamp);
		return "{$H}:{$M}:{$S} ";
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<?php
$timestamp = time();
echo strftime("The date today is %m/%d/%y", $timestamp);
echo "<br />";

echo "The date today is " . nozero($timestamp) . "<br />";
echo "The date today is " . nozerotime($timestamp) . "<br />";

?>
</body>
</html>