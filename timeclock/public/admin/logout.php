<?php 
	require_once("../../include/initialize.php");
	$session->logout();
	redirect_to("../index.php");
?>