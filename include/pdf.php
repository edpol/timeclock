<?php

require(LIB_PATH.DS.'fpdf.php');

class PDF extends Timeclock {

	protected $file_name = "reports/report.pdf"; // or send to new tab?

	private   $ln_to_the_right = 0;
	private   $ln_to_the_beginning_of_the_next_line = 1;
	private   $ln_below = 2;

	function __construct () {
		$this->margin = "";
		$this->left_right_margin = 2 * $this->padding;
		$this->limit = $this->columns - $this->padding - 6; // strlen($days_total) + 1 total _hh:mm
		$this->date_and_2_spaces=str_repeat(" ",$this->date_and_2_spaces_length); // mm/dd/yy__
		$this->ff  = '';
		$this->eol = ","; // when i write eol i reall create a new cell with the input already in it..... this is not going to work well
	}

/*
 *	Printing Time Cards
 */
	protected function justify($left_side,$right_side) {
		$output = $left_side . "," . $right_side . "," ;
		return $output;
	}

	public function printReport($start_date,$end_date,$group_id,$employeeid="") {
		global $database;
		$connection = $this->openFile($this->file_name); 
//		fwrite($connection, $this->fileHeader()); // rtf formatting
		if ($employeeid<>"") $group_id="";
		$this->column_headings = $this->pageHeaderPrep($start_date,$end_date,$group_id);

		$result = $database->findEmployees($group_id, $employeeid);
		$fpdf = new FPDF();
		$fpdf->SetFont('Courier','',11);

		while ($row=$database->fetchArray($result)) {
			$this->name = trim($row["fname"]) . " " . trim($row["lname"]);
			$temp = $this->printOneEmployee( $row["employeeid"], $start_date, $end_date ) ;

			$myArray = explode(',', $temp);
			$myArray[0] = trim($myArray[0]);

			$fpdf->AddPage();
/*
Parameters

w      Cell width. If 0, the cell extends up to the right margin. 
h      Cell height. Default value: 0. 
txt    String to print. Default value: empty string. 
border Indicates if borders must be drawn around the cell. The value can be either a number:
       Default 0: no border, 1, T, R, B, L
ln     Indicates where the current position should go after the call. Possible values are:
        0: to the right
        1: to the beginning of the next line
        2: below

    Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0. 

align  Allows to center or align the text. Possible values are:
        L or empty string: left align (default value)
        C: center
        R: right align
*/
			$w = 0; $h = 5; $border = 0; $ln = 1; $align = "C";
			$fpdf->Cell($w, $h, $myArray[0], $border, $ln, $align); // Header
			$fpdf->Ln();

			$w = 95; $ln = $this->ln_to_the_right;
			$align = "L";
			$fpdf->Cell($w, $h, $myArray[2], $border, $ln, $align); // Date Range
			$align = "R"; $ln = $this->ln_to_the_beginning_of_the_next_line;
			$fpdf->Cell($w, $h, $myArray[3], $border, $ln, $align); // Run Date
			$w = 0; 
			$align = "L";
			$fpdf->Cell($w, $h, $myArray[4], $border, $ln, $align); // Employee Name
			$fpdf->Ln();

			$fpdf->Cell($w, $h, $myArray[6], $border, $ln, $align); // in out in out

			$w = 0; 
			$j = sizeof($myArray);
			for ($i=7; $i<$j-1; $i++) {
				$fpdf->Cell($w, $h, $myArray[$i], $border, $ln, $align); 
			}

			$w = 0; $ln = $this->ln_to_the_beginning_of_the_next_line; $align = "R";
			$fpdf->Cell($w, $h, $myArray[$j-1], $border, $ln, $align); 
		}
		$fpdf->Output();
	}

}
?>