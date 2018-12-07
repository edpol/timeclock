<?php

	function redirectTo( $location = NULL ) {
		if ($location != NULL) {
// echo "<br /><pre>\$_SESSION "; print_r($_SESSION); echo $location . "</pre>"; 
			header("Location: {$location}");
			exit;
		}
	}

	function printArray ($target) {
		echo "<pre>"; print_r($target); echo "</pre>";
	}

/*
PUBLIC_ROOT C:\wamp64\www\P4BH\p4bh\timeclock\public $c = 6
SITE_ROOT   C:\wamp64\www\P4BH\p4bh\timeclock        $c = 5
so $levels = "../"
*/
	function countLevels ($dir, $adjust=0) {
		$c = substr_count($dir, DS) - substr_count(PUBLIC_ROOT, DS) - $adjust;
		$levels = "";
		$levels = str_repeat("../", $c);
		return $levels;
	}

	function render($template, $dir, $data = array()) {
//		$path = __DIR__ . '/../views/' . $template . '.php';
		$path = SITE_ROOT . DS . 'views' . DS . $template . '.php';
		$levels = countLevels($dir);
		if (file_exists($path)) {
			extract($data);
			require($path);
		}
	}

	function showDate() {
		//strftime('m/%d/%y %I:%H:%S %p');
		return strftime('%A &nbsp; %m-%d-%Y', time());	
	}

	function tabs($i=1) {
		if (!is_int($i)) $i=1;
		return str_repeat("\t",$i);
	}

	function phonef ($phone) {
		$output = $phone;
		if (strlen($phone) == 10) {
			$output = substr($phone,0,3) . "-" . substr($phone,3,3) . "-" . substr($phone,6,4);
		}
		if (strlen($phone) == 7) {
			$output = substr($phone,0,3) . "-" . substr($phone,3,4);
		}
		return $output;
	}

?>