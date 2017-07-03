<?php
$d1=new DateTime();
var_dump($d1);
//echo strftime("%Y-%m-%d %H:%I:%S",time());
//echo "<br />";
$punch = $d1->date;
echo $punch;

$date = date_create();
echo date_format($date, 'Y-m-d H:i:s');
?>