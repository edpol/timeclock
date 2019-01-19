<?php
	require_once("../../include/initialize.php");

	// Already logged in? 
	if(!$session->isLoggedIn()) {
		redirectTo("login.php");
	}
	redirectTo("menu.php");
