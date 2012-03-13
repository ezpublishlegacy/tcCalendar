<?

class eZRepeatEvent
{
    /*!
     Constructor
    */
    function eZRepeatEvent( $text, $ezcontentobjectattribute = false )
    {
        $this->text = $text;
		if (is_object($ezcontentobjectattribute)) $this->end = $ezcontentobjectattribute->attribute('sort_key_int');
    }

    /*!
     Sets the name of the matrix
    */
    function setText( $text )
    {
        $this->text = $text;
    }

    function attributes()
    {
        return array( 'text', 'rrule', 'start_time', 'attributes');
    }

    function hasAttribute( $name )
    {
        return in_array( $name, $this->attributes() );
    }

	function text_to_rrule() {
		parse_str($this->text);
		$RRULE = "";
		switch ( $repeats )
		{
			case "daily" : {
				$RRULE .= "FREQ=DAILY;INTERVAL=$repeat_every;";
			}break;
			case "weekday" : {
				$RRULE .= "FREQ=WEEKLY;INTERVAL=1;BYDAY=MO,TU,WE,TH,FR;";
			}break;
			case "mon-wed-fri" : {
				$RRULE .= "FREQ=WEEKLY;INTERVAL=1;BYDAY=MO,WE,FR;";
			}break;
			case "tues-thurs" : {
				$RRULE .= "FREQ=WEEKLY;INTERVAL=1;BYDAY=TU,TH;";
			}break;
			case "weekly" : {
				$RRULE .= "FREQ=WEEKLY;INTERVAL=$repeat_every;BYDAY=";
				foreach($day as $d) {
					$RRULE .= strtoupper(substr($d, 0, 2)) . ",";
				}
				$RRULE = trim($RRULE, ",") . ";";
			}break;
			case "monthly" : {
				$RRULE .= "FREQ=MONTHLY;INTERVAL=$repeat_every;";
				if ($repeat_by=='dayofmonth') {
					$my_day = date("j", $start_time);
					$RRULE .= "BYMONTHDAY=$my_day;";
				} else {
					$checkme = $start_time;
					$my_month = date("n", $checkme);
					$my_day = strtoupper(substr(date("D", $checkme), 0, 2));
					$day_count = 0;
					while ($my_month == date("n", $checkme)) {
						$day_count++;
						$checkme = $checkme - (60*60*24*7);
					}
					$RRULE .= "BYDAY=$day_count$my_day;";
				}
			}break;
			case "annual" : {
				$RRULE .= "FREQ=YEARLY;INTERVAL=$repeat_every";
			}break;
		}
		if ($occurrences !='') {
			$RRULE .= "COUNT=$occurrences;";
		} elseif ($ends_on != '') {
			$RRULE .= "UNTIL=" . date("Ymj",strtotime(urldecode($ends_on))) ."T". date("His",$start_time) .";";
		}
		return trim($RRULE,";");
	}

	function get_end_time() {
		parse_str($this->text);
		if ($ends == "never") {
			return -1;
		}
		if ($ends_on != '') {
			return strtotime(urldecode($ends_on));
		}
		switch ( $repeats )
		{
			case "daily" : {
				return strtotime("+$occurrences days", $start_time);
			}break;
			case "weekday" : {
				return strtotime("+$occurrences weekdays", $start_time);
			}break;
			case "mon-wed-fri" : {
				$checkme = $start_time;
				for ($i=0;$i<$occurrences;$i++) {
					$checkme = strtotime("1 day",$checkme);
					while (date("D", $checkme) != "Mon" && date("D", $checkme) != "Wed" && date("D", $checkme) != "Fri") {
						$checkme = strtotime("1 day",$checkme);
					}
				}
				return $checkme;
			}break;
			case "tues-thurs" : {
				$checkme = $start_time;
				for ($i=0;$i<$occurrences;$i++) {
					$checkme = strtotime("1 day",$checkme);
					while (date("D", $checkme) != "Tue" && date("D", $checkme) != "Thu") {
						$checkme = strtotime("1 day",$checkme);
					}
				}
				return $checkme;
			}break;
			case "weekly" : {
				$enddate = strtotime("+$occurrences weeks", $start_time);
				if (count($day)==0) return $enddate;
				$gooddays = array();
				foreach($day as $d) {
					$gooddays[] = strtoupper(substr($d, 0, 2));
				}
				while (!in_array(strtoupper(substr(date("D", $enddate), 0, 2)), $gooddays)) {
					$enddate = strtotime("-1 day",$enddate);
				}
				return $enddate;
			}break;
			case "monthly" : {
				if ($repeat_by=='dayofmonth') {
					return strtotime("+$occurrences months", $start_time);
				} else {
					$checkme = $start_time;
					$my_month = date("n", $checkme);
					$my_day = strtoupper(substr(date("D", $checkme), 0, 2));
					$day_count = 0;
					while ($my_month == date("n", $checkme)) {
						$day_count++;
						$checkme = $checkme - (60*60*24*7);
					}
					$start_from = strtotime("+$occurrences months", $start_time);
					$start_from_r = getDate($start_from); 
					$first_day = mktime(0,0,0,$start_from_r['mon'],1,$start_from_r['year']);
					return strtotime("+$occurrences ".date("l", $start_time)."s", $first_day);
				}
			}break;
			case "annual" : {
				return strtotime("+$occurrences years", $start_time);
			}break;
		}
		return -100;
	}
	
	function get_timestamps($start_time = FALSE, $end_time = FALSE) {
		if ($start_time === false) $start_time = $this->attribute('start_time');
		$result = array();
		$start = new iCalDate($start_time);
		$RRule = new RRule($start, $this->text_to_rrule());
		$running = true;
		$output = array();
		while ($running) {
			$next_date = $RRule->GetNext();
			if (!$next_date || $next_date->_epoch > ($this->end + (60*60*24) - 1)) {
				$running = false;
			} else {
				$output[] = $next_date->_epoch;
			}
		}
		return $output;
	}

    function attribute( $name )
    {
	parse_str($this->text);
        switch ( $name )
        {
            case "text" :
            {
                return $this->text;
            }break;
  			case "start_time" :
            {
                return $start_time;
            }break;
            case "rrule" :
            {
                return $this->text_to_rrule();
            }break;
            case "attributes" :
            {
                return $this->attributes;
            }break;
            default:
            {
                eZDebug::writeError( "Attribute '$name' does not exist", 'eZRepeatEvent::attribute' );
                return null;
            }break;
        }
    }

    public $text;
	public $end;

}

?>
