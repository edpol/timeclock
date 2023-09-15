<?php
	require_once("../include/initialize.php");
/* 	all this script does is get the record number of the employee
	we need this function for the inquire page so we cant check for logged in
	if(!$session->isLoggedIn()) {
		redirectTo("admin/login.php");
	}
*/

	/**
	 *	Horrible cheat, hate this, but it works
	 *	when calling this routine from reports I lose the $_POST information so I need to save it in $_SESSION
	 *	I don't want to blindly copy everything because it may save sensitive data, 
	 *	so I am hardcoding the fields that need to be saved.
	 */
	// if only_print_one is false we are not looking for names so go back to calling page

	include("post_list.php");
	foreach ($post_list as $key => $value) {
		if (isset($_POST[$value])) { $_SESSION[$value] = $_POST[$value]; }
	}

	$getout=false;
	if (isset($_SESSION['calling_url']) && $_SESSION['calling_url'] == "admin/reports.php") {
		if (!isset($_SESSION["only_print_one"])) {
			$getout=true;
		}
	}

	// have last name, find id
	$calling_url = isset($_SESSION['calling_url']) ? htmlspecialchars($_SESSION['calling_url'], ENT_QUOTES)  : "index.php";
	if ( (isset($_POST['employeeid']) && !empty($_POST['employeeid']) ) || $getout) {
		if (isset($_POST['employeeid'])) $_SESSION['employeeid'] = $_POST["employeeid"];
		redirectTo($calling_url);
	}

	if (isset($_SESSION["list_all"])) {
		$list_all = $_SESSION["list_all"];
		unset($_SESSION["list_all"]);
	} else {
		$list_all = TRUE;
	}

	if (isset($_POST['target_date'])) {
		$_SESSION['target_date'] = htmlentities($_POST['target_date'], ENT_QUOTES);
	}
	$barcode  = isset($_POST['barcode'])   ? $db->escapeValue($_POST['barcode'],   ENT_QUOTES) : "";
	$lastname = isset($_POST['last_name']) ? $db->escapeValue($_POST['last_name'], ENT_QUOTES) : "";
	$lastname = strtoupper($lastname);

	// No data to work with, go back
	if ( ($barcode=="") && ($lastname=="") && (!$list_all) ){
		if ($calling_url=="inquire.php") $calling_url="index.php";
		$session->message("No one to find");
		redirectTo($calling_url); 
	}

	$tc = new Timeclock(); 

	$_SESSION["popup_next_page"]="find_id.php";
	$employeeid = $tc->getId($lastname,$barcode,$list_all);

	// Found Multiple
	if (gettype($employeeid) == "string" && $calling_url<>"index.php") {
		$_SESSION["output"] = $employeeid; // ?
		redirectTo("popup.php");  // calls this script again, but with barcode, not lastname
	}

	// Found nothing 
	if ($employeeid==0) {
		if ($calling_url=="inquire.php") $calling_url="index.php";
        $m = "";
//      don't want to display barcode, it's a secret
//      if (isset($_POST['barcode']))  $m .= $_POST['barcode']  . " ";
        if (isset($_POST['lastname'])) $m .= $_POST['lastname'] . " ";
        $m .= "Not Found";
        $session->message($m);	
	}

	// Found one
	if ($employeeid>0) {
		$_SESSION["employeeid"] = $employeeid;
		$_SESSION["lastname"] = $lastname;
	}

	redirectTo($calling_url); 

?>
