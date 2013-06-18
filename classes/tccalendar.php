<?php

class tcCalendar {

	function tcCalendar($cal_id, $from_time = null, $to_time = null) {
		
		$ezphpicalendarini = eZINI::instance( 'tccalendar.ini' );
		
		$is_master_id = $ezphpicalendarini->variable( 'ClassSettings', 'IsMasterAttributeIdentifier' );
		
		$this->node_id = $cal_id;

		$cal_node = eZContentObjectTreeNode::fetch($cal_id);
		
		if (is_object($cal_node)) {

			$cal_node_data = $cal_node->dataMap();
		
			$this->is_master = (array_key_exists($is_master_id, $cal_node_data)) ? $cal_node_data[$is_master_id]->content() : true;
		
			if ($this->is_master) $cal_id = 2;
		
			$eventclasses = $ezphpicalendarini->variable( "ClassSettings", "EventClassIds" );
		
			$this->title_id = $ezphpicalendarini->variable( "ClassSettings", "TitleAttributeIdentifier");
			$this->location_id = $ezphpicalendarini->variable( "ClassSettings", "LocationAttributeIdentifier");
			$this->venue_id = $ezphpicalendarini->variable( "ClassSettings", "VenueAttributeIdentifier");
			$this->description_id = $ezphpicalendarini->variable( "ClassSettings", "DescriptionAttributeIdentifier");
			$this->region_id = $ezphpicalendarini->variable( "ClassSettings", "RegionAttributeIdentifier");
			$this->category_id = $ezphpicalendarini->variable( "ClassSettings", "CategoryAttributeIdentifier");
			$this->calcol_id = $ezphpicalendarini->variable( "ClassSettings", "CalColorAttributeIdentifier");
			$this->image_id = $ezphpicalendarini->variable( "ClassSettings", "ImageAttributeIdentifier");
			$this->sd = $ezphpicalendarini->variable( "ClassSettings", "StartDateAttributeIdentifier");
			$this->st = $ezphpicalendarini->variable( "ClassSettings", "StartTimeAttributeIdentifier");
			$this->ed = $ezphpicalendarini->variable( "ClassSettings", "EndDateAttributeIdentifier");
			$this->et = $ezphpicalendarini->variable( "ClassSettings", "EndTimeAttributeIdentifier");
			$this->r = $ezphpicalendarini->variable( "ClassSettings", "EventClassRepeatAttributes");
			$this->HasPopup = $ezphpicalendarini->variable( "PopupOptions", "HasPopup");
			$this->col_r=array();
		
			if (!is_array($eventclasses)) $eventclasses = array($eventclasses);

			$params = array('ClassFilterType' => 'include', 'ClassFilterArray' => $eventclasses);
		
		
			if (!$this->is_master) {
				$params['Depth'] = 1;
				$params['DepthOperator'] = 'eq';
			}
			
			$this->ed_i = false;
			$this->sd_i = false;

			$attribute_filter = array();
			if ($to_time != null) {
				$this->ed_i = strtotime($to_time.'T23:59:59');
				$attribute_filter[] = array("event/".$this->sd, "between", array(0,strtotime($to_time.'T23:59:59')));
			}
			if ($from_time != null) {
				$this->sd_i = strtotime($from_time);
				$attribute_filter[] = array("event/".$this->ed, "not_between", array(1,strtotime($from_time)));
				//$attribute_filter[] = array("event/".$this->sd, "not_between", array(0,strtotime($from_time)));
			}
			if (count($attribute_filter)) $params['AttributeFilter'] = $attribute_filter;

			$events = eZContentObjectTreeNode::subTreeByNodeID( $params, $cal_id );
		      
			if (in_array($cal_node->object()->contentClass()->attribute('id'), $eventclasses) && $for_output) $events = array($cal_node);

			$this->events = $events;
		
		}
		
	}
	
	static function outputasjson($cal_id, $from_time = null, $to_time = null, $event_id=null) {
		if ($event_id !== null) return ezjscAjaxContent::nodeEncode(eZContentObjectTreeNode::fetch($event_id)->Object(), array('dataMap'=>array('all')));
		$cal = new tcCalendar($cal_id, $from_time, $to_time);
		return $cal->monthtojson('fulldata');
	}
	
	function monthtojson($type = false) {
		$output =  "var tcevents = [\r\n";
		$j_output = array();
		foreach($this->events as $e) {

			$e_o = $this->eventtoobject($e, $type);
			if ($e_o === false) continue; 
			$myclass_id = $e->object()->contentClass()->attribute('id');
			$repeaters = $this->r;
			$normal = true;
			if (array_key_exists($myclass_id, $repeaters)) {
				$dm = $e->dataMap();
				if (array_key_exists($repeaters[$myclass_id], $dm) && $dm[$repeaters[$myclass_id]]->hasContent()) {
					$mycontent = $dm[$repeaters[$myclass_id]]->content();
					if (strpos($mycontent->text, 'repeats') !== false && strpos($mycontent->text, 'repeats=never') === false) {
						$normal = false;
						$start_times = $dm[$repeaters[$myclass_id]]->content()->get_timestamps();
						foreach ($start_times as $t) {
							
							if ($this->sd_i && $t < $this->sd_i) continue;
							if ($this->ed_i && $t > $this->ed_i) continue;
							
							$mytime = new eZDateTime($t);
							$e_o->start = "new Date(" . $mytime->year() . ", " . (floor($mytime->month()) -1) . ", " . $mytime->day() . ", " . $e_o->hour . ", " . $e_o->minute .")";
							if ($type == 'fulldata') $out = strtotime((floor($mytime->month()) -1) ."/". $mytime->day() ."/". $mytime->year() ." ".$e_o->hour.":".$e_o->minute);
							if ($type == 'fulldata') {
								$j_output[] = $e_o;
							} else {
								$output .= $this->eventobjecttojson($e_o);
							}
							
						}
					}
				}
			} 
			if ($normal && $e_o->status != 'error_without_repeat') {
				if ($type == 'fulldata') {
					$j_output[] = $e_o;
				} else {
					$output .= $this->eventobjecttojson($e_o);
				}
			}
		}
		if ($type == 'fulldata') return json_encode($j_output);
		$output .= "];\r\n";
		$output .= "var tc_cal_id = " . $this->node_id . ";";
		return $output;
	}
	
	function eventtoobject($e, $type = false) {
		$objData = $e->dataMap();
		
		$forjs = ($type == 'fulldata') ? '' : '"';
		
		$this->allDay = false;
		$parent_node_id = $e->attribute('parent_node_id');
		
		if (!array_key_exists('node_'.$parent_node_id, $this->col_r)) {
			$parent_data = $e->fetchParent()->dataMap();
			if (!array_key_exists($this->calcol_id,$parent_data)) {
				$parent_col = '#000000';
			} else {
				$parent_col = $parent_data[$this->calcol_id]->content();
			}
			$this->col_r['node_'.$parent_node_id] = $parent_col;
		} else {
			$parent_col = $this->col_r['node_'.$parent_node_id];
		}
		$event_id = $e->attribute('node_id');

		if (array_key_exists('hide_from_calendar', $objData) && $objData['hide_from_calendar']->content()) return false; 
		$e_o = new stdClass();
		if (class_exists('tcEventDataFetcher')) {
			$event_data = tcEventDataFetcher::fetchData($e);
			foreach($event_data as $event_data_k => $event_data_v) {
				$e_o->$event_data_k = ($type == 'fulldata') ? trim($event_data_v, '"') : $event_data_v;
			}
		} else {
			$e_o->backgroundColor = $forjs.$parent_col.$forjs;
		}
		$e_o->id = $event_id;
		$e_o->start = $this->get_event_start($objData, $e_o, $type);
		$e_o->end = $this->get_event_end($objData, $e_o, $type);
		
		if (array_key_exists($this->title_id, $objData) && is_object($objData[$this->title_id])) {
			$e_o->title = $forjs.addslashes($objData[$this->title_id]->content()).$forjs;
		}
		if (array_key_exists($this->location_id, $objData) && is_object($objData[$this->location_id])) {
			$e_o->location = $forjs.addslashes($objData[$this->location_id]->content()).$forjs;
		}
		
		if ($type == 'fulldata') {
		
			if (array_key_exists($this->region_id, $objData) && is_object($objData[$this->region_id])) {
				if ($objData[$this->region_id]->hasContent()) $e_o->region = trim(preg_replace("/\r|\n/", "", nl2br($objData[$this->region_id]->metaData())));
			}
			if (array_key_exists($this->category_id, $objData) && is_object($objData[$this->category_id])) {
				if ($objData[$this->category_id]->hasContent()) $e_o->cagetory = trim(preg_replace("/\r|\n/", "", nl2br($objData[$this->category_id]->metaData())));
			}
			if (array_key_exists($this->description_id, $objData) && is_object($objData[$this->description_id])) {
				if ($objData[$this->description_id]->hasContent()) $e_o->description = trim(preg_replace("/\r|\n/", "", nl2br($objData[$this->description_id]->metaData())));
			}
			if (array_key_exists($this->description_id, $objData) && is_object($objData[$this->description_id])) {
				$im_data = $objData[$this->image_id]->content()->imageAlias('original');
				if ($objData[$this->image_id]->hasContent()) $e_o->image = $im_data['url'];
			}
			$ven = array();
			foreach(explode(",",$this->venue_id) as $v) {
				if (array_key_exists($v, $objData) && is_object($objData[$v])) {
					$addme = addslashes($objData[$v]->content());
					if ($addme != "") $ven[] = $addme;
				}
			}
			$e_o->venue = $forjs.implode(", ", $ven).$forjs;
		
		}
		
		if ($this->allDay === false) $e_o->allDay = 'false';
		$e_o->HasPopup = ($this->HasPopup == enabled) ? 'true' : 'false';
		$e_o->url = $forjs.'/' . $e->urlAlias(). $forjs;
		
		return $e_o;
	}
	
	function eventobjecttojson($e_o) {
		$out = chr(123);
		foreach($e_o as $k=>$v) {
			if ($v && $k !='status') $out .= "$k: $v,\r\n";
		}
		return preg_replace("/,\r\n$/", "", $out) . chr(125) . ",\r\n";
	}
	
	function get_event_start($objData, $e_o, $type=false) {
		
		if ((!is_object($objData[$this->sd])) || $objData[$this->sd]->hasContent() != 1) return false;
		$date_from = $objData[$this->sd]->content();
		if ((!is_object($objData[$this->st])) || $objData[$this->st]->hasContent() != 1) {
			$this->allDay = true;
			$time_from = new eZDateTime($date_from);
			$time_from->setHour(0);
			$time_from->setMinute(0);
			$time_from->setSecond(0);
		} else {
			$time_from = $objData[$this->st]->content();
		}
		$e_o->hour = $time_from->hour();
		$e_o->minute = $time_from->minute();
		$out = "new Date(" . $date_from->year() . ", " . (floor($date_from->month()) -1) . ", " . $date_from->day() . ", " . $time_from->hour() . ", " . $time_from->minute() .")";
		if ($type == 'fulldata') $out = strtotime((floor($date_from->month()) -1) ."/". $date_from->day() ."/". $date_from->year() ." ".$time_from->hour().":".$time_from->minute());
		
		$test_start = strtotime((floor($date_from->month()) -1) ."/". $date_from->day() ."/". $date_from->year());
		if ($this->ed_i && $test_start > $this->ed_i) $e_o->status = 'error_without_repeat';
		
		return $out;
			 
	}
	
	function get_event_end($objData, $e_o, $type=false) {
	
		if (!is_object($objData[$this->ed])) {
			$this->allDay = true;
			if (($objData[$this->sd]->attribute('data_int') + (60*60*24) -1) < $this->sd_i ) $e_o->status = 'error_without_repeat';
			return false;
		} else {
			if ($objData[$this->ed]->attribute('data_int') == 0) {
				$this->allDay = true;
				if (($objData[$this->sd]->attribute('data_int') + (60*60*24) -1) < $this->sd_i ) $e_o->status = 'error_without_repeat';
			}
		}
		if (!is_object($objData[$this->et])) {
			$this->allDay = true;
			return false;
		}
					
		$date_to = $objData[$this->ed]->content();
		$time_to = $objData[$this->et]->content();
		if (!is_object($time_to)) $time_to = new eZDateTime($date_to);
		
		if (!is_object($time_to) || date('His', $time_to->timeStamp()) == '000000') {
			$this->allDay = true;
			return false;
		}
		
		$out = "new Date(" . $date_to->year() . ", " . (floor($date_to->month()) -1) . ", " . $date_to->day() . ", " . $time_to->hour() . ", " . $time_to->minute() .")";
		if ($type == 'fulldata') $out = strtotime((floor($date_to->month()) -1) ."/". $date_to->day() ."/". $date_to->year() ." ".$time_to->hour().":".$time_to->minute());
		
		$test_end = strtotime((floor($date_to->month()) -1) ."/". $date_to->day() ."/". $date_to->year());
		if ($this->sd_i && $test_end < $this->sd_i) return false;
		
		return $out;
				
	}
	
	var $node_id;
	var $events;
	var $output;
	var $sd;
	var $st;
	var $sd_i;
	var $ed;
	var $et;
	var $ed_i;
	var $r;
	var $col_r;
	var $allDay;
}

?>