<?php 
	require_once("../../include/initialize.php");
	$session->logout();
	redirectTo("../index.php");
?>