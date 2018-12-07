<?php

class Timeclock {

	public $name="";
	public $lastname;
	public $firstname;
	public $columns=80;
	public $padding=0;
	public $num_rows;
	protected $select_employee;
	protected $todays_array=array("todays_total"=>0);
	protected $margin;
	protected $left_right_margin;
	protected $column_headings;
	protected $limit;
	protected $date_and_2_spaces_length=10;
	protected $date_and_2_spaces;
	protected $eol;
	protected $ff;
	protected $first_page = true;
	protected $file_name = "reports/report.rtf";

	function __construct () {
		$this->margin = str_repeat(" ",$this->padding);
		$this->left_right_margin = 2 * $this->padding;
		$this->limit = $this->columns - $this->padding - 6; // strlen($days_total) + 1 total _hh:mm
		$this->date_and_2_spaces=str_repeat(" ",$this->date_and_2_spaces_length); // mm/dd/yy__
		$this->eol = "\line"  . PHP_EOL;
		$this->ff  = "\page" . $this->eol;
	}

	public function getName($id) {
		global $database;
		$result = $database->getEmployee($id);
		$this->num_rows = $database->numRows($result);
		if ($this->num_rows == 0) {
			$_SESSION["message"] = "Employee Record Not Found";
		}
		if ($this->num_rows == 1) {
			$row = $database->fetchArray($result);
			$_SESSION["employee_name"] = trim($row["fname"]) . " " . trim($row["lname"]);
			$this->name = $_SESSION["employee_name"];
		}
		return $this->name;
	}

	/* find employeeid with lastname or barcode */
	public function getId(&$lastname, &$barcode="", $list_all=TRUE) {
		global $database;

		$employeeid=0;
		$result = NULL;
		if ($barcode<>"") {
			$result = $database->findBarcode($barcode);
		} else {
			if ($list_all) {
				$result = $database->findLikeLastName($lastname);
			} else {
				if (!empty($lastname)) {
					$result = $database->findLastName($lastname);
				} else {
					$_SESSION["message"] = "No Last Name, no Bar Code";
				}
			}
		}

		if ($result!==NULL) {
			$this->num_rows = $database->numRows($result);
			if ($this->num_rows == 0) {
				$_SESSION["message"] = "Record Not Found";
			}
			if ($this->num_rows == 1) {
				$row = $database->fetchArray($result);
				$lastname = $row['lname'];
				$barcode = $row['barcode'];
				$employeeid = $row["employeeid"];
				$_SESSION["employee_name"] = trim($row["fname"]) . " " . trim($row["lname"]);
				$this->name = $_SESSION["employee_name"];
			}
			if ($this->num_rows > 1) {
				$this->select_employee = $this->buildSelectEmployee($result);
				return $this->select_employee;
			}
		}
		return (int) $employeeid;
	}

	protected function buildSelectEmployee($result) {
	   /*
		*	so we have more than one match on last name, 
		*	create a list with radio buttons so the user can select their name and return the barcode
		*	return to index and diplay this list (cancel button?)
		*	they select the person they want, and they are punched in by the barcode number (or index?)
		*/
		global $database;
		$output  = "\t\t<form action='" . $_SESSION["popup_next_page"] . "' method='post'>\n";
		$output .= "\t\t<h1>Select Employee</h1><br />\n";
		if ($this->num_rows>10) $output .= "\t\t<div class='divScrollAuto'> \n";
		for ($i=1; $i<=$this->num_rows; $i++) {
			$row = $database->fetchArray($result);
			$barcode = $row['barcode'];
			$employeeid = $row["employeeid"];
			$this->name = trim($row["fname"]) . " " . trim($row["lname"]);
			if ($row["is_active"]==0) {
				$output .= "\t\t\t &nbsp;&nbsp; <font color='#CCCCCC'><del>" . $this->name . "</del></font><br />\n";
			} else {
				$output .= "\t\t\t<input type='radio' name='barcode' value='{$barcode}' onChange='this.form.submit();'>" . $this->name . "<br />\n";
			}
		}
		if ($this->num_rows>10) $output .= "\t\t</div>\n";
		$output .= "\t\t</form>\n";
		return $output;
	}

	public function delete($id) {
		global $database;
		$sql = "delete from stamp where id={$id} limit 1 ";
		$result = $database->q($sql);
	}

	public function seconds2HourMinutes($sec) {
		$min = floor($sec/60);
		$hr  = floor($min/60);
		$min = $min - ($hr*60);
		return sprintf("%02d:%02d", $hr, $min);
	}

	public function getWeekday($today="") {
		// strtotime uses string $today and returns type integer in seconds
		// getdate uses time in seconds and returns array 
		if ($today=="") { 
			$x=getdate();
//			$weekday=strftime('%a');
		} else {
			$x=getdate(strtotime($today));
//			$weekday=strftime('%a', strtotime($today));
		}
		$weekday=substr($x["weekday"],0,3);
		return $weekday;
	}

	public function formatAmPm($time) {
		$string = strtolower(strftime('%I:%M %p', $time));  	// for display
		$string = str_replace(' ', '', $string);
		$string = str_replace('m', '', $string);
		return $string;
	}

	protected function getData($row,$result) {
		global $database;

		$id_in = $row["id"];
		$punch1  = strtotime($row["punch"]);		// for math
		$punch_in = $this->formatAmPm($punch1);  	// for display
		$row = $database->fetchArray($result);
		if (isset($row)) {
			$id_out = $row["id"];
			$punch2 = strtotime($row["punch"]);
			$punch_out  = $this->formatAmPm($punch2);
			$delta_seconds = $punch2 - $punch1;
		} else {
			$id_out = 0;
			$punch_out = "";
			$delta_seconds = 0;
		}
		$delta_string = $this->seconds2HourMinutes($delta_seconds);

		$x = array ("in"=>$punch_in, "out"=>$punch_out, "delta_string"=>$delta_string, "delta_seconds"=>$delta_seconds);
		$x["id_in"]  = $id_in;
		$x["id_out"] = $id_out;
		return $x;
	}

	public function selectToday($employeeid, $today) {
		global $database;
		if ($today=="") { 
			$today = strftime('%Y-%m-%d 00:00:00', time()); 
		} else {
			$today = strftime('%Y-%m-%d 00:00:00', strtotime($today));
		}
		$tomorrow  = strftime('%Y-%m-%d 00:00:00', strtotime($today . ' + 1 day'));
		$sql = "select * from stamp where employeeid='{$employeeid}' and punch>='{$today}' and punch<'{$tomorrow}' order by punch\n";
		$result = $database->q($sql);
		$this->num_rows = $database->numRows($result);
		return $result;
	}

	public function buildTodaysArray($employeeid,&$today="") {
		global $database;

		$result = $this->selectToday($employeeid, $today);

//		$t = array("todays_total" => 0);
		$todays_total = 0;
		$t = array ();
		if ($this->num_rows>0) {
			while ($row = $database->fetchArray($result)) {
				$x = $this->getData($row,$result);
				$todays_total += $x["delta_seconds"];
				$t[] = $x;
			}
			$t["todays_total"] = $todays_total;
		} else {
			$t = false;
		}
		return $t;
	}


	public function buildToday($employeeid,$target_date="",$add_name=false) {

		if ($target_date=="") {$target_date=strftime('%Y-%m-%d 00:00:00',time());}
		$target_date_total = 0;

		$output = "";
		if ($add_name) {
			$output .= "<div align='center' style='color:black; background-color:white;'>" . $this->getName($employeeid) . "</div>\n";
		}
		$output .= "\t\t<table class='one_day' style='max-width:200px;'>\n";
		$output .= "\t\t\t<tr><th colspan='5'>" . $this->getWeekday($target_date) . "<br >" . strftime("%m/%d",strtotime($target_date)) . "</th></tr>\n";

		$this->todays_array = $this->buildTodaysArray($employeeid,$target_date);

		$output .= "\t\t\t<tr><td style='text-align:center'>IN</td><td> </td><td style='text-align:center'>OUT</td><td> </td><td style='text-align:center'>SUM</td></tr>\n";
		if (is_array($this->todays_array)) {
			foreach ($this->todays_array as $key => $x) {
				if (is_numeric($key)) {
					$target_date_total += $x["delta_seconds"];
					$output .= "\t\t\t<tr><td>" . $x["in"] . "</td><td>-</td><td>" . $x["out"] . "</td><td>=</td><td>" . $x["delta_string"] . "</td></tr>\n";
				}
			}
		}
		$delta = $this->seconds2HourMinutes($target_date_total);
		$output .= "\t\t\t<tr><td colspan=5>{$delta}</td></tr>\n";
		$output .= "\t\t</table>\n";
		if ($add_name) {
			$seconds_sinceSunday = $this->sinceSunday($employeeid);
			$week_total = "Week Total " . $this->seconds2HourMinutes($seconds_sinceSunday);
			$output .= "<div align='center' style='color:black; background-color:white;'>" . $week_total . "</div>\n";
		}
		return $output;
	}

	public function build2Weeks($employeeid,$today="") {
		if ($today=="") { $today = strftime('%Y-%m-%d 00:00:00',time()); }
		$t = getdate();
		$adjust = -1 * $t["wday"] - 7; // Sunday=0 .. Saturday=6
		$target = strftime('%Y-%m-%d 00:00:00', strtotime($today . $adjust . ' day'));
		$grand_total = 0;
		if ( !isset($this->name) || empty($this->name) ) {
			if (isset($_SESSION["employee_name"])) { 
				$this->name = $_SESSION["employee_name"]; 
			} else {
				$this->name = $this->getName($employeeid);
			}
		}
		$spacer = "<tr><td class='spacer' colspan='8'></td></tr>\n";
		$two_weeks  = "<div align='center' style='color:black'>" . $this->name . "</div>\n";
		$two_weeks .= "<table class='two_weeks' cellspacing='1'>\n";
		for ($j=1; $j<=2; $j++) {
			$week_total = 0;
			$two_weeks .= "<tr>\n";
			for ($i=1; $i<=7; $i++) {
				$output = $this->buildToday($employeeid,$target);
				$week_total  += $this->todays_array["todays_total"];
				$two_weeks .= "\t<td>\n" . $output . "\t</td>\n";
				$target = strftime('%Y-%m-%d 00:00:00', strtotime($target . '+1 day'));
			}
			$grand_total += $week_total;
			$delta = $this->seconds2HourMinutes($week_total);
			$two_weeks .= "\t<td>\n\t\t<table>\n\t\t\t<tr><th>Week<br />Total</th></tr>\n\t\t\t<tr><td style='vertical-align:bottom; height:100px; border:none;'>" . $delta . "</td></tr>\n\t\t</table>\n\t</td>\n";
			$two_weeks .= "</tr>\n";
			$two_weeks .= $spacer;
		}

		//then we want one more row totaling BOTH week_totals
		$delta = $this->seconds2HourMinutes($grand_total);
		$two_weeks .= "<tr><td colspan='8'>{$delta}</td>\n</tr>\n";
		$two_weeks .= "</table>\n";
		return $two_weeks;
	}

	public function sinceSunday($employeeid) {
		$today = strftime("%Y-%m-%d 00:00:00", time());
		$today_sec = strtotime($today);
		$date  = strftime("%Y-%m-%d 00:00:01", strtotime("last Sunday"));
		$date_sec = strtotime($date); //plus 1 second, just to be sure
		$week_total = 0;
		while ($date <= $today) {
			$this->todays_array = $this->buildTodaysArray($employeeid,$date);
			$week_total += $this->todays_array["todays_total"];
			$date = strftime("%Y-%m-%d 00:00:00", strtotime($date . " + 1 day"));
		}
		return $week_total;
	}

	public function punchIn($employeeid, $punch="") {
		global $database;
		if ($punch=="") { 
			$punch = strftime('%Y-%m-%d %H:%M:00',time());
		} else {
			$punch = strftime('%Y-%m-%d %H:%M:00',strtotime($punch));
		}
		$sql = "insert into stamp (employeeid, punch) value ({$employeeid}, '{$punch}')";
		$result = $database->q($sql);
	}

	public function inputTimeSetup($target_date) {
		$output  = "\t\t\t\t\t\t<input type='submit' name='submit'      value='add' class='edit_up' />\n";
		$output .= "\t\t\t\t\t\t<input type='text'   name='time'        value='' />\n";
		$output .= "\t\t\t\t\t\t<input type='hidden' name='target_date' value='{$target_date}' />\n";
		return $output;
	}

	public function inputTimeSetup2($target_date) {
		$output  = "\t\t" . '<input type="submit" name="submit" value="add" class="admin_up" onMouseUp="this.className';
		$output .= "'admin_up'" . '" onMouseDown="this.className=' . "'admin_down'" . '" />' . "\n";

		$output .= "\t\t" . '<select style="width:50px;" name="hour">' . "\n";
		for ($i=1; $i<=12; $i++) { 
			$selected = ($i==9) ? "selected='selected'" : "";
			$t = sprintf("%02d",$i);
			$output .= "\t\t\t<option value='{$t}' {$selected}/>{$t}\n"; 
		} 
		$output .= "\t\t</select>\n";

		$output .= "\t\t" . '<select style="width:50px;" name="minute">' . "\n";
		for ($i=0; $i<=59; $i++) {
			$t = sprintf("%02d",$i);
			$output .= "\t\t\t<option value='{$t}' {$selected}/>{$t}\n"; 
		} 
		$output .= "\t\t</select>\n";

		$output .= "\t\t" . '<select style="width:50px;" name="am_pm">' . "\n";
		$output .= "\t\t\t" . '<option value="am" />am' . "\n";
		$output .= "\t\t\t" . '<option value="pm" />pm' . "\n";
		$output .= "\t\t</select>\n";
		$output .= "\t\t<input type='hidden' name='target_date' value='{$target_date}' />\n";
		return $output;
	}

	public function buildDeleteButton($id) {
		$del  = '<button onClick="submitForm(' . "'change_time.php'" . ')" ';
		$del .= "name='delete' value='{$id}' class='del_up' ";
		$del .= 'onMouseUp="this.className=' . "'del_up'" . '" ';
		$del .= 'onMouseDown="this.className=' . "'del_down'" . '">del</button>';
		return $del;
	}

	public function displayChangeTime($target_array) {
		$del_img = '<img width="20px" src="../images/delete.gif" />'; 

		if ($target_array==false) {
			$count = 0;
		} else {
			$count = count($target_array) - 1;
		}
		$todays_total_seconds = 0;

		$output = "<table border='0' class='default' style='width:85%;'>\n";
		for ($i=0; $i<$count; $i++) {
			$in  = $target_array[$i]["in"];
			$out = $target_array[$i]["out"];
			$id  = $target_array[$i]["id_in"];
			$del = $this->buildDeleteButton($id);

			$output .= "<tr>\n\t<td style='text-align:right;'>" . $del . "</td>\n\t<td>" . $in ."&nbsp;</td>\n\t<td style='text-align:right;'>"; 
			if (!empty($target_array[$i]["out"])) { 
				$id = $target_array[$i]["id_out"];
				$del = $this->buildDeleteButton($id);
				$output .= $del . "</td>\n\t<td>" . $out . "&nbsp;"; 
			} else {
				$output .= "</td>\n\t<td>"; 
			}
			$output .= "</td>\n\t<td style='text-align:right;'>&nbsp;" . $target_array[$i]["delta_string"] . "&nbsp;</td>\n</tr>\n";
		}
		$time = $this->seconds2HourMinutes($target_array["todays_total"]); 
		$output .= "<tr><td colspan='4'></td><td style='text-align:right; border-top:yellow solid thin;'>$time&nbsp;</td>\n</tr>\n</table>\n";
		return $output;
	}

/*
 *	Printing Time Cards
 */

	public function printReport($start_date,$end_date,$group_id,$employeeid="") {
		global $database;
		$connection = $this->openFile($this->file_name);
		fwrite($connection, $this->fileHeader());
		if ($employeeid<>"") $group_id="";
		$this->column_headings = $this->pageHeaderPrep($start_date,$end_date,$group_id);

		$result = $database->findEmployees($group_id, $employeeid);

		while ($row=$database->fetchArray($result)) {
			$this->name = trim($row["fname"]) . " " . trim($row["lname"]);
			$temp = $this->printOneEmployee( $row["employeeid"], $start_date, $end_date ) ;
			fwrite($connection, $temp . $this->eol);
		}
		fwrite($connection, $this->fileFooter());

		$this->closeFile($connection,$this->file_name);
	}

	protected function openFile($file_name) {
		$connection = fopen($file_name, 'w') or die("Unable to open file {$file_name}!");
		return $connection;
	}
	protected function closeFile($connection,$file_name) {
		global $session;
		fclose($connection);
		$session->message("Download <a href='{$file_name}'>Report</a>");
	}


	// pad to center text
	protected function center($line) {
		$blanks = str_repeat(" ", floor(($this->columns-strlen($line))/2));
		$output = $blanks . $line . $this->eol;
		return $output;
	}

	// pad to justify text
	protected function justify($left_side,$right_side) {
		$left_side  = $this->margin . $left_side;
		$right_side = $right_side . $this->margin;
		$blanks = str_repeat(" ",$this->columns - strlen($left_side) - strlen($right_side));
		$output = $left_side . $blanks . $right_side . $this->eol;
		return $output;
	}

	protected function pageHeaderPrep($start_date,$end_date,$group_id="") {
		$date1 = strftime('%m/%d/%y', strtotime($start_date)) . "-" . date('m/d/y', strtotime($end_date));
		$date2 = strftime('%m/%d/%y', time());

		$output  = $this->center("EMPLOYEE TIMECARD REPORT " . $group_id);
		$output .= $this->eol;
		$output .= $this->justify("Date Range: {$date1}", "Run Date: {$date2}");

//                 hh:mm am   123456789 1234
		$h     = "  in    ";
		$h    .= "  out   ";
		$date_size = "mm/dd/yy" ;
		/*
		how mani $h can we fin in one line
		| padding | date_size | h's | padding |

		$space_left = $this->columns - $this->padding*2 - strlen($date_size)+2 - strlen($total);
		*/

		$total = "hhh:mm";
		$space_left = $this->columns - $this->padding*2 - strlen($this->date_and_2_spaces) - strlen($total);
		$xh = ceil( $space_left / strlen($h) );
		$this->in_out = $this->margin . $this->date_and_2_spaces;
		for ($i=1; $i<= $xh; $i++) {
			$this->in_out .= $h;
		}
		$this->in_out = substr($this->in_out,0,-1) . "  Total";
		return $output;
	}

	public function getEmployeeData($id) {
		global $database;
		$result = $database->getEmployee($id);
		$this->num_rows=$database->numRows($result);
		if ($this->num_rows==0) {
			$row = false;
		} else {
			$row=$database->fetchArray($result);
			$this->firstname = trim($row["fname"]);
			$this->lastname  = trim($row["lname"]);
			$this->name =  $this->firstname . " " . $this->lastname;
		}
		return $row;
	}

	public function printOneEmployee($employeeid, $start, $end) {
		global $database;
		$start = strftime('%Y-%m-%d 00:00:00 ', strtotime($start));
		$end   = strftime('%Y-%m-%d 00:00:00 ', strtotime($end));
		$grand_total_sec = 0;
		$cutoff = strtotime($end . "1 day");
		$target = $start;
		$adjust = 1;

		$output = "";
		if ($this->first_page) {
			$this->first_page = false;
		} else {
			$output .= $this->ff;
		}

		$output .= $this->column_headings;
		$output .= $this->margin . $this->name . $this->eol;
		$output .= $this->eol;
		$output .= $this->in_out . $this->eol;

		while (strtotime($target)<$cutoff) {
			$this->todays_array = $this->buildTodaysArray($employeeid,$target);

			$days_total_sec = $this->todays_array["todays_total"];

			if (is_array($this->todays_array)) {
				$grand_total_sec  += $days_total_sec;

				$one_line = $this->margin . strftime('%m/%d/%y', strtotime($target));

				// enter all of the punch in and punch out times in a string
				foreach ($this->todays_array as $key => $x) {
					if (is_array($x)) {
						$one_line .= "  " . $x["in"] . "  " . $x["out"];
					}
				}
				// if the string is too long cut it in parts
				while (strlen($one_line)>$this->limit) {				
					$output .= substr($one_line,0,$this->limit) . $this->eol;
					$one_line = $this->margin . $this->date_and_2_spaces . trim(substr($one_line,$this->limit+1));
				}
				$days_total = $this->seconds2HourMinutes($days_total_sec);
				$blanks = str_repeat(" ",$this->columns - strlen($one_line) - strlen($days_total) - $this->padding);
				$one_line .= $blanks . $days_total;

				$output .= $one_line . $this->eol;
			}
			$target = strftime('%Y-%m-%d 00:00:00', strtotime($start . $adjust++ . ' day'));
		}

		$grand_total = $this->seconds2HourMinutes($grand_total_sec);
		$one_line = $this->margin . "Grand Total ";
		$blanks = str_repeat(" ",$this->columns - strlen($one_line) - strlen($grand_total) - $this->padding);
		$one_line .= $blanks . $grand_total;
		$output .= $one_line . $this->eol; // . "\page" . $this->eol; //chr(12);
// calculate blank lines needed to put next line on bottom if using text format
		return $output;
	}

	protected function fileHeader() {
		$output  = '{\rtf1\ansi\deff0 {\fonttbl {\f0 Courier New;}}' . PHP_EOL;
//		$output .= '{\colortbl;\red0\green0\blue0;\red255\green0\blue0;}' . PHP_EOL;
//		$output .= '\paperw15840\paperh12240';
		$output .= '\margl720\margr720\margt720\margb720' . PHP_EOL;
//		$output .= '\tx720\tx1440\tx2880\tx5760' . PHP_EOL;  // Tabs
		$output .= '\fs22\line ' . PHP_EOL;  // font size

		return $output;
	}

	protected function fileFooter() {
		$output  = '}' . PHP_EOL;
		return $output;
	}

}
?>