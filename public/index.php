<?php
/*
 *	timeclock
 */
require_once("../include/initialize.php");

$_SESSION['calling_url'] = "index.php";
if (isset($_SESSION["employeeid"])) {
    $employeeid = $_SESSION["employeeid"];
    unset($_SESSION["employeeid"]);
    $today = showDate();
    $tc = new Timeclock();
    $tc->punchIn($employeeid);
    /*
    // you either timeout or grey the screen, can't seem to get both ath the same time
    $_SESSION["timeout"] ="id=showup";
    $_SESSION["output"] =  $tc->buildToday($employeeid,$today,$add_name_to_header);
    redirectTo("popup.php");
    */
    $display  = "<div id='showup' class='popup' style='z-index:20'>\n";
    $display .= $tc->buildToday($employeeid,$today,true);
    $display .= "<a class='close' href=''></a>\n";
    $display .= "</div>\n";

    // $display .= '<a id="showup" class="overlay" href="' . $calling_url . '">' . "\n" . "</a>";
    $_SESSION["display"] = $display;
} else {
    $_SESSION["list_all"] = FALSE;
}

$display = "";
if (isset($_SESSION["display"])) {
    $display = $_SESSION["display"];
    unset($_SESSION["display"]);
}

$data = array("background_image"=>"work.jpg", "background_color"=>"FF0", "background_body_color"=>"#232F3D");
render("header", __DIR__, $data);
?>
<!-- this clears the form every time you navigate away or reload the page -->
<body>
<div id="back">
    <h2>
        <?= $display; ?>
        <div class="bluebox">
            <div class="centered-div" style="margin-bottom:6px;">
                <?php include("clock.php"); ?>
            </div>
            <form id="form1" action="find_id.php" autocomplete="off" method="post" data-enter-submit>
                <!-- doesn't need a submit button, just press return -->
                <div class="form-group">
                    <label>ID Number: <input type="password" name="barcode" value="" id="barcode" autofocus /></label>
                    <label>Last Name: <input type="text" name="lastname" value="" id="lastname" /></label>
                </div>

                <!-- Buttons on the bottom -->
                <div style="clear:both; padding-top:10px;">
                    <a id="login"   class="blue_btn" href="admin/login.php">Sign In</a>
                    <a id="inquire" class="blue_btn" href="inquire.php"    >Inquire</a>
                </div>
                <?php if(!empty($message)) { echo "<div style='padding-top: 20px;'>$message</div>"; } ?>
            </form>
        </div>
    </h2>
</div>
<!-- Active input tag - Has to be after the input tag named barcode -->
<?php render("footer", __DIR__);  ?>
</body>
<?php render("close_html", __DIR__);  ?>
