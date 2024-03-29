<?php 
	require_once("../../include/initialize.php");

	// Already logged in? 
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}

	if (isset($_SESSION["employeeid"])) {
		redirectTo("edit_employee2.php");
	} else {
		$_SESSION["calling_url"] = "admin/edit_employee.php";
	}

	if (isset($_SESSION["list_all"])) unset($_SESSION["list_all"]);

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Edit Employee", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 

	$data = array("title"=>"Edit Employee", "form_page"=>"find_id.php", "return_page"=>"admin/edit_employee.php", "levels"=>countLevels(__DIR__), "message"=>$message);
	render("id_lname", __DIR__, $data); 


