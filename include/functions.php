<?php

	function redirectTo( $location = NULL ): void
    {
		if ($location != NULL) {
// echo "<br /><pre>\$_SESSION "; print_r($_SESSION); echo $location . "</pre>"; 
			header("Location: $location");
			exit;
		}
	}

	function printArray ($target): void
    {
		echo "<pre>"; print_r($target); echo "</pre>";
	}

/*
PUBLIC_ROOT C:\wamp64\www\P4BH\p4bh\timeclock\public $c = 6
SITE_ROOT   C:\wamp64\www\P4BH\p4bh\timeclock        $c = 5
so $levels = "../"
*/
	function countLevels ($dir, $adjust=0): string
    {
		$c = substr_count($dir, DS) - substr_count(PUBLIC_ROOT, DS) - $adjust;
        return str_repeat("../", $c);
	}

	function render($template, $dir, $data = array()): void
    {
//		$path = __DIR__ . '/../views/' . $template . '.php';
		$path = SITE_ROOT . DS . 'views' . DS . $template . '.php';
		$levels = countLevels($dir);
		if (file_exists($path)) {
			extract($data);
			require($path);
		}
	}

	function showDate($format=""): string
    {
        if ($format=="") $format = 'l m-d-Y';
        return (new DateTime())->format($format);
	}

	function tabs($i=1): string
    {
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

    function changeDateFormat($date, $outputFormat): string
    {
        $dt = DateTime::createFromFormat('l m-d-Y', $date) ?:
              DateTime::createFromFormat('Y-m-d H:i:s', $date) ?:
              DateTime::createFromFormat('m/d/Y', $date);

        // should it die here?
        if ($dt === false) {
            echo "Could not parse date: $date";
            $dt = new DateTime();  // Fallback to today
        }

        return $dt->format($outputFormat);
    }