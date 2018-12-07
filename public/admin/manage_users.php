<?php 
	require_once("../../include/initialize.php");
	require_once(LIB_PATH.DS.'utilities.php');

	// Already logged in? 
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}

	if (isset($_SESSION["employeeid"])) {
		redirectTo("manage_users2.php");
	} else {
		$_SESSION["calling_url"] = "admin/manage_users.php";
	}

	if (isset($_SESSION["list_all"])) unset($_SESSION["list_all"]);

	$data = array("background_image"=>"Clock-Wallpaper-2.jpg", "background_color"=>"FFC", "tab"=>"Edit Users", "background_body_color"=>"#07111A");
	render("header", __DIR__, $data); 

	$show_table = true;

	/*
	 *    Delete users
	 */
	if (isset($_POST["delete"])) {
		$message = $utilities->deleteUser($_POST["delete"]);
	}

	/*
	 *    Edit Users
	 *    then Update Users
	 */
	if (isset($_POST["edit"])) {

		$row = $utilities->findUserById($_POST["edit"]);

		$data = array("row"=>$row, "message"=>$message);
		render("users_edit", __DIR__, $data); 
 
		$show_table = false;
	} 
	/*
	 *    Update Users
	 */
	if (isset($_POST["update"])) {
		$message = $utilities->userUpdate($_POST); // $_POST has userid and new values
	}

	/*
	 *    Add Users
	 *        render a page that asks all of the info
	 *        when you press submit we need to run view save_users
	 *    then Save Users
	 */
	if (isset($_POST["add"])) {
		$data = array("message"=>$message);
		render("users_add", __DIR__, $data); 

		$show_table = false;
	}
	/*
	 *    Save Users
	 */
	if (isset($_POST["save"])) {
		$message = $utilities->userAdd($_POST);   // $_POST has userid and new values
	}


	/*
	 *    Show Users Table
	 */
	if ($show_table) {
		$output = $utilities->userTable("Manage Users","manage_users.php","manage_users.php");

		$data = array("form_page"=>"find_id.php", "return_page"=>"admin/manage_users.php", "levels"=>countLevels(__DIR__), "table"=>$output, "message"=>$message);
		render("users_list", __DIR__, $data); 
	}
?>
