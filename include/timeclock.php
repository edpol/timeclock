<?php

class Timeclock Extends Database{

	public string $name="";
	public string $lastname;
	public string $firstname;
	public int $column_count=80;
	public int $padding=0;
	public int $num_rows;
    protected string $select_employee;
	protected array $todays_array=["todays_total"=>0];
    protected string $in_out;
	protected string $margin;
	protected int|float $left_right_margin;
	protected string $column_headings;
	protected int $limit;
	protected int $date_and_2_spaces_length=10;
	protected string $date_and_2_spaces;
	protected string $eol;
	protected string $ff;
	protected bool $first_page = true;
	protected string $file_name = PUBLIC_ROOT . DS . "admin" . DS . "reports" . DS . "report.rtf";  // this is set in pdf.php

	function __construct () {
		$this->margin = str_repeat(" ",$this->padding);
		$this->left_right_margin = 2 * $this->padding;
		$this->limit = $this->column_count - $this->padding - 6; // strlen($days_total) + 1 total _hh:mm
		$this->date_and_2_spaces=str_repeat(" ",$this->date_and_2_spaces_length); // mm/dd/yy__
		$this->eol = "\line"  . PHP_EOL;
		$this->ff  = "\page" . $this->eol;
	}

	public function getName($id): string
    {
		$result = $this->getEmployee($id);
		$this->num_rows = $this->numRows($result);
		if ($this->num_rows == 0) {
			$_SESSION["message"] = "Employee Record Not Found";
		}
		if ($this->num_rows == 1) {
			$row = $this->fetchArray($result);
			$_SESSION["employee_name"] = trim($row["fname"]) . " " . trim($row["lname"]);
			$this->name = $_SESSION["employee_name"];
		}
		return $this->name;
	}

	/* find employeeid with lastname or barcode */
    /**
     * @throws Exception
     */
    public function getId(&$lastname, &$barcode="", $list_all=TRUE): int|string
    {
		$employeeid=0;
		$result = NULL;
		if ($barcode<>"") {
			$result = $this->findBarcode($barcode);
		} else {
			if ($list_all) {
				$result = $this->findLikeLastName($lastname);
			} else {
				if (!empty($lastname)) {
					$result = $this->findLastName($lastname);
				} else {
					$_SESSION["message"] = "No Last Name, no Bar Code";
				}
			}
		}

		if ($result!==NULL) {
			$this->num_rows = $this->numRows($result);
			if ($this->num_rows == 0) {
				$_SESSION["message"] = "Record Not Found";
			}
			if ($this->num_rows == 1) {
				$row = $this->fetchArray($result);
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

	protected function buildSelectEmployee($result): string
    {
	   /*
		*	so we have more than one match on last name, 
		*	create a list with radio buttons so the user can select their name and return the barcode
		*	return to index and diplay this list (cancel button?)
		*	they select the person they want, and they are punched in by the barcode number (or index?)
		*/
		$output  = "\t\t<form action='" . $_SESSION["popup_next_page"] . "' method='post'>\n";
		$output .= "\t\t<h1>Select Employee</h1><br />\n";
		if ($this->num_rows>10) $output .= "\t\t<div class='divScrollAuto'> \n";
		for ($i=1; $i<=$this->num_rows; $i++) {
			$row = $this->fetchArray($result);
			$barcode = $row['barcode'];
			//$employeeid = $row["employeeid"];
			$this->name = trim($row["fname"]) . " " . trim($row["lname"]);
			if ($row["is_active"]==0) {
				$output .= "\t\t\t &nbsp;&nbsp; <span style='color:#CCCCCC'><del>" . $this->name . "</del></span><br />\n";
			} else {
				$output .= "\t\t\t<input type='radio' name='barcode' value='$barcode' onChange='this.form.submit();'>" . $this->name . "<br />\n";
			}
		}
		if ($this->num_rows>10) $output .= "\t\t</div>\n";
		$output .= "\t\t</form>\n";
		return $output;
	}

    /**
     * @throws Exception
     */
    public function delete($id): void
    {
		$sql = "delete from stamp where id = ? limit 1 ";
        $this->db_query($sql,[$id]);
	}

	public function seconds2HourMinutes($sec): string
    {
		$min = floor($sec/60);
		$hr  = floor($min/60);
		$min = $min - ($hr*60);
		return sprintf("%02d:%02d", $hr, $min);
	}

    function getWeekday($date = ''): string
    {
        $timestamp = !empty($date) && strtotime($date) !== false
            ? strtotime($date)
            : time();
        return substr(date('l', $timestamp), 0, 3);
    }

	public function formatAmPm($time): array|string
    {
        $string = strtolower((new DateTime())->setTimestamp($time)->format('h:i A'));  	// for display
        return str_replace([' ', 'm'], '', $string);
	}

	protected function getData($row,$result): array
    {
		$id_in = $row["id"];
		$punch1  = strtotime($row["punch"]);		// for math
		$punch_in = $this->formatAmPm($punch1);  	// for display
		$row = $this->fetchArray($result);
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

    /**
     * @throws Exception
     */
    public function selectToday($employeeid, $today): mysqli_result|bool
    {
        if ($today == "") {
            $today = (new DateTime())->format('Y-m-d 00:00:00');
        } else {
            $today = changeDateFormat($today, 'Y-m-d 00:00:00');
        }
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $today);
        $dt->add(new DateInterval('P1D'));
        $tomorrow = $dt->format('Y-m-d 00:00:00');

		$sql = "select * from stamp where employeeid = ? and punch >= ? and punch < ? order by punch\n";
		$result = $this->db_query($sql,[$employeeid, $today, $tomorrow]);
		$this->num_rows = $this->numRows($result);
		return $result;
	}

    /**
     * @throws Exception
     */
    public function buildTodaysArray($employeeid, $today=""): array
    {
		$result = $this->selectToday($employeeid, $today);
		$todays_total = 0;
		$t = array ();
		if ($this->num_rows>0) {
			while ($row = $this->fetchArray($result)) {
				$x = $this->getData($row,$result);
				$todays_total += $x["delta_seconds"];
				$t[] = $x;
			}
			$t["todays_total"] = $todays_total;
		} else {
            $t["todays_total"] = 0;
		}
        return $t;
	}


    /**
     * @throws Exception
     */
    public function buildToday($employeeid, $target_date="", $add_name=false): string
    {
		if ($target_date=="") {
            $target_date = (new DateTime())->format('Y-m-d 00:00:00');
        }
		$target_date_total = 0;

		$output = "";
		if ($add_name) {
			$output .= "<div style='width:98%; margin: 0 auto; text-align:center; color:black; background-color:white;'>";
            $output .= $this->getName($employeeid) . "</div>\n";
		}
		$output .= "\t\t<table class='one_day' style='width:98%; margin: 0 auto;'>\n";
		$output .= "\t\t\t<tr><th style='text-align:center;' colspan='5'>" . $this->getWeekday($target_date) . "<br >";
//        $month_day = (new DateTime($target_date))->format('m/d');
        $month_day = changeDateFormat($target_date, 'm/d');
        $output .= "$month_day</th></tr>\n";
        $this->todays_array = $this->buildTodaysArray($employeeid,$target_date);

        $output .= "\t\t\t<tr><td style='text-align:center'>IN</td><td> </td><td style='text-align:center'>OUT</td><td> </td><td style='text-align:center'>SUM</td></tr>\n";
		//if (is_array($this->todays_array)) {
			foreach ($this->todays_array as $key => $x) {
				if (is_numeric($key)) {
					$target_date_total += $x["delta_seconds"];
					$output .= "\t\t\t<tr><td>" . $x["in"] . "</td><td>-</td><td>" . $x["out"] . "</td><td>=</td><td>" . $x["delta_string"] . "</td></tr>\n";
				}
			}
		//}
		$delta = $this->seconds2HourMinutes($target_date_total);
		$output .= "\t\t\t<tr><td colspan=5>$delta</td></tr>\n";
		$output .= "\t\t</table>\n";
		if ($add_name) {
			$seconds_sinceSunday = $this->sinceSunday($employeeid);
			$week_total = "Week Total " . $this->seconds2HourMinutes($seconds_sinceSunday);
			$output .= "<div style='text-align: center; color:black; background-color:white;'>" . $week_total . "</div>\n";
		}
		return $output;
	}

    /**
     * Render a two-week grid for an employee.
     * Uses a single DB query for all 14 days instead of 14 separate queries.
     *
     * @throws Exception
     */
    public function build2Weeks($employeeid, $today=""): string
    {
        if (gettype($this->todays_array) !== "array") {
            $this->todays_array = ["todays_total" => 0];
        }

        if ($today == "") { $today = date('Y-m-d 00:00:00'); }

        // Anchor to the Sunday two weeks ago (same logic as before).
        $t      = getdate();
        $adjust = -1 * $t["wday"] - 7; // Sunday=0 .. Saturday=6
        $start  = (new DateTime($today))->modify($adjust . ' day')->format('Y-m-d 00:00:00');
        // End = 14 days after start (exclusive upper bound for the query).
        $end    = (new DateTime($start))->modify('+14 days')->format('Y-m-d 00:00:00');

        if (empty($this->name)) {
            $this->name = $_SESSION["employee_name"]
                ?? $this->getName($employeeid);
        }

        // ONE query for the whole two-week window.
        $day_arrays = $this->buildRangeArrays((int)$employeeid, $start, $end);

        $spacer     = "<tr><td class='spacer' colspan='8'></td></tr>\n";
        $two_weeks  = "<div style='text-align:center; color:black'>" . $this->name . "</div>\n";
        $two_weeks .= "<table class='two_weeks' style='border-collapse: collapse; border-spacing: 1px;'>\n";

        $target      = $start;
        $grand_total = 0;
        for ($j = 1; $j <= 2; $j++) {
            $week_total = 0;
            $two_weeks .= "<tr>\n";
            for ($i = 1; $i <= 7; $i++) {
                $day_key = (new DateTime($target))->format('Y-m-d');
                $this->todays_array = $day_arrays[$day_key] ?? ["todays_total" => 0];

                // buildToday() still does the HTML — pass the pre-fetched array via the
                // instance property so it doesn't fire another query.
                $output      = $this->buildTodayFromArray($target);
                $week_total += $this->todays_array["todays_total"];
                $two_weeks  .= "\t<td>\n" . $output . "\t</td>\n";
                $target      = (new DateTime($target))->modify('+1 day')->format('Y-m-d 00:00:00');
            }
            $grand_total += $week_total;
            $delta        = $this->seconds2HourMinutes($week_total);
            $two_weeks   .= "\t<td>\n\t\t<table>\n\t\t\t<tr><th>Week<br />Total</th></tr>\n\t\t\t<tr><td style='vertical-align:bottom; height:100px; border:none;'>" . $delta . "</td></tr>\n\t\t</table>\n\t</td>\n";
            $two_weeks   .= "</tr>\n";
            $two_weeks   .= $spacer;
        }

        $delta      = $this->seconds2HourMinutes($grand_total);
        $two_weeks .= "<tr><td colspan='8'>$delta</td>\n</tr>\n";
        $two_weeks .= "</table>\n";
        return $two_weeks;
    }

    /**
     * Render one day cell from $this->todays_array (already populated).
     * Unlike buildToday(), this does NOT fire a DB query.
     */
    protected function buildTodayFromArray(string $target_date): string
    {
        $target_date_total = 0;
        $output  = "\t\t<table class='one_day' style='width:98%; margin: 0 auto;'>\n";

        $month_day = changeDateFormat($target_date, 'm/d');

        $output .= "\t\t\t<tr><th style='text-align:center;' colspan='5'>"
                 . $this->getWeekday($target_date) . "<br >"
                 . "$month_day</th></tr>\n";
        $output .= "\t\t\t<tr>";
        $output .= "<td style='text-align:center'>IN</td><td> </td>";
        $output .= "<td style='text-align:center'>OUT</td><td> </td>";
        $output .= "<td style='text-align:center'>SUM</td></tr>\n";
        foreach ($this->todays_array as $key => $x) {
            if (is_numeric($key)) {
                $target_date_total += $x["delta_seconds"];
                $output .= "\t\t\t<tr><td>" . $x["in"] . "</td><td>-</td><td>" . $x["out"] . "</td><td>=</td><td>" . $x["delta_string"] . "</td></tr>\n";
            }
        }
        $delta   = $this->seconds2HourMinutes($target_date_total);
        $output .= "\t\t\t<tr><td colspan=5>$delta</td></tr>\n";
        $output .= "\t\t</table>\n";
        return $output;
    }

    /**
     * Fetch all punch rows for an employee between $start (inclusive) and
     * $end (exclusive, midnight of the day after the last day wanted).
     * Returns a flat array of assoc rows ordered by punch time.
     *
     * @throws Exception
     */
    public function fetchPunchRange(int $employeeid, string $start, string $end): array
    {
        $sql    = "SELECT * FROM stamp WHERE employeeid = ? AND punch >= ? AND punch < ? ORDER BY punch";
        $result = $this->db_query($sql, [$employeeid, $start, $end]);
        $rows   = [];
        while ($row = $this->fetchArray($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Consume a flat ordered array of punch rows and return the same structure
     * as buildTodaysArray(): numeric keys for each in/out pair, plus
     * 'todays_total' in seconds.
     *
     * Punches are consumed pairwise (odd index = in, even index = out).
     */
    public function buildDayArrayFromRows(array $rows): array
    {
        $t     = [];
        $total = 0;
        $count = count($rows);
        for ($i = 0; $i < $count; $i += 2) {
            $in_row   = $rows[$i];
            $out_row  = ($i + 1 < $count) ? $rows[$i + 1] : null;
            $punch1   = strtotime($in_row["punch"]);
            $punch_in = $this->formatAmPm($punch1);
            if ($out_row !== null) {
                $punch2      = strtotime($out_row["punch"]);
                $punch_out   = $this->formatAmPm($punch2);
                $delta_sec   = $punch2 - $punch1;
                $id_out      = $out_row["id"];
            } else {
                $punch_out = "";
                $delta_sec = 0;
                $id_out    = 0;
            }
            $total += $delta_sec;
            $t[] = [
                "in"           => $punch_in,
                "out"          => $punch_out,
                "delta_string" => $this->seconds2HourMinutes($delta_sec),
                "delta_seconds"=> $delta_sec,
                "id_in"        => $in_row["id"],
                "id_out"       => $id_out,
            ];
        }
        $t["todays_total"] = $total;
        return $t;
    }

    /**
     * Fetch all punches for a range of days in ONE query and split them by
     * calendar date (Y-m-d). Returns an array keyed by 'Y-m-d' where each
     * value is a day array (same format as buildTodaysArray).
     *
     * @throws Exception
     */
    protected function buildRangeArrays(int $employeeid, string $start, string $end): array
    {
        $all_rows = $this->fetchPunchRange($employeeid, $start, $end);

        // Bucket rows by calendar date.
        $by_date = [];
        foreach ($all_rows as $row) {
            $day = (new DateTime($row["punch"]))->format('Y-m-d');
            $by_date[$day][] = $row;
        }

        // Build a day array for each bucket.
        return array_map(function ($rows) {
            return $this->buildDayArrayFromRows($rows);
        }, $by_date);
    }

    /**
     * Total seconds worked since last Sunday — single DB query.
     *
     * @throws Exception
     */
    public function sinceSunday($employeeid) {
        $today    = date('Y-m-d 00:00:00');
        $tomorrow = (new DateTime($today))->modify('+1 day')->format('Y-m-d 00:00:00');
        $sunday   = (new DateTime('last Sunday'))->format('Y-m-d 00:00:00');

        $day_arrays = $this->buildRangeArrays((int)$employeeid, $sunday, $tomorrow);
        $week_total = 0;
        foreach ($day_arrays as $day_array) {
            $week_total += $day_array["todays_total"];
        }
        return $week_total;
    }

    /**
     * @throws Exception
     */
    public function punchIn($employeeid, $punch=""): bool
    {
        $result = false;
        if(!empty($employeeid)) {
            $punchObj = $punch ? new DateTime($punch) : new DateTime();
            $punch = $punchObj->format('Y-m-d H:i:00');
            $sql = "insert into stamp (employeeid, punch) value (?, ?)";
            $result = $this->db_query($sql,[$employeeid, $punch]);
        }
        return $result;
	}

	public function inputTimeSetup($target_date): string
    {
		$output  = "\t\t\t\t\t\t<input type='submit' name='submit'      value='add' class='edit_up' />\n";
		$output .= "\t\t\t\t\t\t<input type='text'   name='time'        value='' />\n";
		$output .= "\t\t\t\t\t\t<input type='hidden' name='target_date' value='$target_date' />\n";
		return $output;
	}

	public function inputTimeSetup2($target_date): string
    {
		$output  = "\t\t" . '<input type="submit" name="submit" value="add" class="admin_up" onMouseUp="this.className';
		$output .= "'admin_up'" . '" onMouseDown="this.className=' . "'admin_down'" . '" />' . "\n";

		$output .= "\t\t" . '<select style="width:50px;" name="hour">' . "\n";
		for ($i=1; $i<=12; $i++) { 
			$selected = ($i==9) ? "selected='selected'" : "";
			$t = sprintf("%02d",$i);
			$output .= "\t\t\t<option value='$t' $selected/>$t\n";
		} 
		$output .= "\t\t</select>\n";

		$output .= "\t\t" . '<select style="width:50px;" name="minute">' . "\n";
		for ($i=0; $i<=59; $i++) {
			$t = sprintf("%02d",$i);
			$output .= "\t\t\t<option value='$t' $selected/>$t\n";
		} 
		$output .= "\t\t</select>\n";

		$output .= "\t\t" . '<select style="width:50px;" name="am_pm">' . "\n";
		$output .= "\t\t\t" . '<option value="am" />am' . "\n";
		$output .= "\t\t\t" . '<option value="pm" />pm' . "\n";
		$output .= "\t\t</select>\n";
		$output .= "\t\t<input type='hidden' name='target_date' value='$target_date' />\n";
		return $output;
	}

	public function buildDeleteButton($id): string
    {
		$del  = '<button onClick="submitForm(' . "'change_time.php'" . ')" ';
		$del .= "name='delete' value='$id' class='del_up' ";
		$del .= 'onMouseUp="this.className=' . "'del_up'" . '" ';
		$del .= 'onMouseDown="this.className=' . "'del_down'" . '">del</button>';
		return $del;
	}

	public function displayChangeTime($target_array): string
    {
        //	$del_img = '<img width="20px" src="../images/delete.gif"  alt="delete"/>';

		if (!$target_array) {
			$count = 0;
		} else {
			$count = count($target_array) - 1;
		}
        // $todays_total_seconds = 0;

		$output = "<table class='default' style='border:none; width:85%;'>\n";
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
    public function printReport($start_date, $end_date, $group_id, $employeeid=""): void
    {
        try {
            $handle = $this->openFile($this->file_name);
        } catch (Exception $e) {
            die("Error: " . $e->getMessage() . " (" . $e->getCode() . ")");
        }

        fwrite($handle, $this->fileHeader());
		if ($employeeid<>"") $group_id="";
		$this->column_headings = $this->pageHeaderPrep($start_date,$end_date,$group_id);

		$result = $this->findEmployees($group_id, $employeeid);

		while ($row=$this->fetchArray($result)) {
			$this->name = trim($row["fname"]) . " " . trim($row["lname"]);
			$temp = $this->printOneEmployee( $row["employeeid"], $start_date, $end_date ) ;
			fwrite($handle, $temp . $this->eol);
		}
		fwrite($handle, $this->fileFooter());

		$this->closeFile($handle,$this->file_name);
	}

    /**
     * @throws Exception
     */
    protected function openFile($target_file)
    {
        $handle = fopen($target_file, 'w');
        if ($handle === false) {
            throw new Exception("Unable to open file $target_file!");
        }
        return $handle;
    }

	protected function closeFile($handle,$target_file): void
    {
		fclose($handle);
		$session->message("Download <a href='$target_file'>Report</a>");
	}


	// pad to center text
	protected function center($line): string
    {
		$blanks = str_repeat(" ", floor(($this->column_count-strlen($line))/2));
        return $blanks . $line . $this->eol;
	}

	// pad to justify text
	protected function justify($left_side,$right_side): string
    {
		$left_side  = $this->margin . $left_side;
		$right_side = $right_side . $this->margin;
		$blanks = str_repeat(" ",$this->column_count - strlen($left_side) - strlen($right_side));
        return $left_side . $blanks . $right_side . $this->eol;
	}

    protected function pageHeaderPrep($start_date, $end_date, $group_id=""): string
    {
        $date1 = (new DateTime($start_date))->format('m/d/y') . "-" . (new DateTime($end_date))->format('m/d/y');
        $date2 = date('m/d/y');
		$output  = $this->center("EMPLOYEE TIMECARD REPORT " . $group_id);
		$output .= $this->eol;
		$output .= $this->justify("Date Range: $date1", "Run Date: $date2");

//                 hh:mm am   123456789 1234
		$h  = "  in    ";
		$h .= "  out   ";
		/*
		$date_size = "mm/dd/yy" ;
		how mani $h can we fin in one line
		| padding | date_size | h's | padding |

		$space_left = $this->column_count - $this->padding*2 - strlen($date_size)+2 - strlen($total);
		*/

		$total = "hhh:mm";
		$space_left = $this->column_count - $this->padding*2 - strlen($this->date_and_2_spaces) - strlen($total);
		$xh = ceil( $space_left / strlen($h) );
		$this->in_out = $this->margin . $this->date_and_2_spaces;
		for ($i=1; $i<= $xh; $i++) {
			$this->in_out .= $h;
		}
		$this->in_out = substr($this->in_out,0,-1) . "  Total";
		return $output;
	}

	public function getEmployeeData($id) {
		$result = $this->getEmployee($id);
		$this->num_rows=$this->numRows($result);
		if ($this->num_rows==0) {
			$row = false;
		} else {
			$row=$this->fetchArray($result);
			$this->firstname = trim($row["fname"]);
			$this->lastname  = trim($row["lname"]);
			$this->name =  $this->firstname . " " . $this->lastname;
		}
		return $row;
	}

    /**
     * @throws Exception
     */
    public function printOneEmployee($employeeid, $start, $end): string
    {
        $start = (new DateTime($start))->format('Y-m-d 00:00:00 ');
        $end   = (new DateTime($end))->format('Y-m-d 00:00:00 ');
        $grand_total_sec = 0;
        $cutoff = (new DateTime($end))->modify('+1 day')->getTimestamp();
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

        // Pre-fetch all punch rows for the entire date range in a single query
        $end_exclusive = (new DateTime($end))->modify('+1 day')->format('Y-m-d 00:00:00');
        $day_arrays = $this->buildRangeArrays((int)$employeeid, $start, $end_exclusive);

        while (strtotime($target) < $cutoff) {
            $day_key = (new DateTime($target))->format('Y-m-d');
            $this->todays_array = $day_arrays[$day_key] ?? ["todays_total" => 0];

            $days_total_sec = $this->todays_array["todays_total"];

            //if (is_array($this->todays_array)) {
                $grand_total_sec  += $days_total_sec;

                $one_line = $this->margin . (new DateTime($target))->format('m/d/y');

                // enter all of the punch in and punch out times in a string
                foreach ($this->todays_array as $x) {
                    if (is_array($x)) {
                        $one_line .= "  " . $x["in"] . "  " . $x["out"];
                    }
                }
                // if the string is too long cut it in parts
                while (strlen($one_line) > $this->limit) {
                    $output .= substr($one_line, 0, $this->limit) . $this->eol;
                    $one_line = $this->margin . $this->date_and_2_spaces . trim(substr($one_line, $this->limit + 1));
                }
                $days_total = $this->seconds2HourMinutes($days_total_sec);
                $blanks = str_repeat(" ", $this->column_count - strlen($one_line) - strlen($days_total) - $this->padding);
                $one_line .= $blanks . $days_total;

                $output .= $one_line . $this->eol;
            //}
            $target = (new DateTime($start))->modify('+' . $adjust++ . ' day')->format('Y-m-d 00:00:00');
        }

        $grand_total = $this->seconds2HourMinutes($grand_total_sec);
        $one_line = $this->margin . "Grand Total ";
        $blanks = str_repeat(" ", $this->column_count - strlen($one_line) - strlen($grand_total) - $this->padding);
        $one_line .= $blanks . $grand_total;
        $output .= $one_line . $this->eol;

        return $output;
    }

	protected function fileHeader(): string
    {
		$output  = '{\rtf1\ansi\deff0 {\fonttbl {\f0 Courier New;}}' . PHP_EOL;
//		$output .= '{\colortbl;\red0\green0\blue0;\red255\green0\blue0;}' . PHP_EOL;
//		$output .= '\paperw15840\paperh12240';
		$output .= '\margl720\margr720\margt720\margb720' . PHP_EOL;
//		$output .= '\tx720\tx1440\tx2880\tx5760' . PHP_EOL;  // Tabs
		$output .= '\fs22\line ' . PHP_EOL;  // font size

		return $output;
	}

	protected function fileFooter(): string
    {
        return '}' . PHP_EOL;
	}

}
