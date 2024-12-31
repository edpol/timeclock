<?php
require_once("../include/initialize.php");
?>
<!doctype html>
<html lang="en-US">
<head>
</head>
<body>
<?php
$target = date('Y-m-d',strtotime("-90 days"));
$sql = "select * from stamp where employeeid=107 and punch>'$target' order by punch";

if ($result = $database->q($sql)) {
    foreach ($result as $row){
        print $row['punch'] . "<br>";
    }
}
?>
</body>
</html>
