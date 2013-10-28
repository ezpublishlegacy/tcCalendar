<?php

class tcCalendar {

	function tcCalendar($cal_id, $from_time = null, $to_time = null) {
		
		$ezphpicalendarini = eZINI::instance( 'tccalendar.ini' );
		
		$is_master_id = $ezphpicalendarini->variable( 'ClassSettings', 'IsMasterAttributeIdentifier' );
		
		$this->node_id = $cal_id;

		$cal_node = eZContentObjectTreeNode::fetch($cal_id);

		$cal_node_data = $cal_node->dataMap();
		
		$this->is_master = (array_key_exists($is_master_id, $cal_node_data)) ? $cal_node_data[$is_master_id]->content() : true;
		
		if ($this->is_master) $cal_id = 2;
		
		$eventclasses = $ezphpicalendarini->variable( "ClassSettings", "EventClassIds" );
		
		$this->title_id = $ezphpicalendarini->variable( "ClassSettings", "TitleAttributeIdentifier");
		$this->location_id = $ezphpicalendarini->variable( "ClassSettings", "LocationAttributeIdentifier");
		$this->calcol_id = $ezphpicalendarini->variable( "ClassSettings", "CalColorAttributeIdentifier");
		$this->sd = $ezphpicalendarini->variable( "ClassSettings", "StartDateAttributeIdentifier");
		$this->st = $ezphpicalendarini->variable( "ClassSettings", "StartTimeAttributeIdentifier");
		$this->ed = $ezphpicalendarini->variable( "ClassSettings", "EndDateAttributeIdentifier");
		$this->et = $ezphpicalendarini->variable( "ClassSettings", "EndTimeAttributeIdentifier");
		$this->r = $ezphpicalendarini->variable( "ClassSettings", "EventClassRepeatAttributes");
		$this->HasPopup = $ezphpicalendarini->variable( "PopupOptions", "HasPopup");
		$this->col_r=array();
		
		if (!is_array($eventclasses)) $eventclasses = array($eventclasses);

		$params = array('ClassFilterType' => 'include', 'ClassFilterArray' => $eventclasses, 'SortBy' => array(array('attribute', true, "event/".$this->sd),array('name', true)));
		
		
		if (!$this->is_master) {
			$params['Depth'] = 1;
			$params['DepthOperator'] = 'eq';
		}

		$attribute_filter = array();
		if ($to_time != null) {
			$attribute_filter[] = array("event/".$this->sd, "between", array(0,strtotime($to_time.'T23:59:59')));
		}
		if ($from_time != null) {
			$attribute_filter[] = array("event/".$this->ed, "not_between", array(1,strtotime($from_time)));
			$attribute_filter[] = array("event/".$this->sd, "not_between", array(0,strtotime($from_time)));
		}
		if (count($attribute_filter)) $params['AttributeFilter'] = $attribute_filter;

		$events = eZContentObjectTreeNode::subTreeByNodeID( $params, $cal_id );
		      
		if (in_array($cal_node->object()->contentClass()->attribute('id'), $eventclasses) && $for_output) $events = array($cal_node);

		$this->events = $events;
		
	}
	
	function monthtojson() {
		$output =  "var tcevents = [\r\n";
		foreach($this->events as $e) {

			$e_o = $this->eventtoobject($e);
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
							$mytime = new eZDateTime($t);
							$e_o->start = "new Date(" . $mytime->year() . ", " . (floor($mytime->month()) -1) . ", " . $mytime->day() . ", " . $e_o->hour . ", " . $e_o->minute .")";
						
							$output .= $this->eventobjecttojson($e_o);
						}
					}
				}
			} 
			if ($normal) {
				$output .= $this->eventobjecttojson($e_o);
			}
		}
		
		$output .= "];\r\n";
		$output .= "var tc_cal_id = " . $this->node_id . ";";
		return $output;
	}
	
	function eventtoobject($e) {
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
		$objData = $e->dataMap();
		$e_o = new stdClass();
		$e_o->backgroundColor = '"'.$parent_col.'"';
		$e_o->id = $event_id;
		if (array_key_exists($this->title_id, $objData) && is_object($objData[$this->title_id])) {
			$e_o->title = '"'.addslashes(preg_replace('/[^(\x20-\x7F)]*/','', $objData[$this->title_id]->content())).'"';
		}
		if (array_key_exists($this->location_id, $objData) && is_object($objData[$this->location_id])) {
			$e_o->location = '"'.addslashes(preg_replace('/[^(\x20-\x7F)]*/','', $objData[$this->location_id]->content())).'"';
		}
		$e_o->start = $this->get_event_start($objData, $e_o);
		$e_o->end = $this->get_event_end($objData);
		if ($this->allDay === false) $e_o->allDay = 'false';
		$e_o->HasPopup = ($this->HasPopup == enabled) ? 'true' : 'false';
		$e_o->url = '"/' . $e->urlAlias(). '"';
		
		return $e_o;
	}
	
	function eventobjecttojson($e_o) {
		$out = chr(123);
		foreach($e_o as $k=>$v) {
			if ($v) $out .= "$k: $v,\r\n";
		}
		return preg_replace("/,\r\n$/", "", $out) . chr(125) . ",\r\n";
	}
	
	function get_event_start($objData, $e_o) {
		
		if (!is_object($objData[$this->sd])) return false;
		if (!is_object($objData[$this->st])) {
			$this->allDay = true;
			return false;
		}
					
		$date_from = $objData[$this->sd]->content();
		$time_from = $objData[$this->st]->content();
		if (!is_object($time_from)) $time_from = new eZDateTime($date_from);
		$e_o->hour = $time_from->hour();
		$e_o->tz = "'" . $objData[$this->st]->attribute('data_text') . "'";
		$e_o->minute = $time_from->minute();
		$out = "new Date(" . $date_from->year() . ", " . (floor($date_from->month()) -1) . ", " . $date_from->day() . ", " . $time_from->hour() . ", " . $time_from->minute() .")";
		
		return $out;
			 
	}
	
	function get_event_end($objData) {
	
		if (!is_object($objData[$this->ed])) {
			$this->allDay = true;
			return false;
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
		
		return $out;
				
	}
	
	var $node_id;
	var $events;
	var $output;
	var $sd;
	var $st;
	var $ed;
	var $et;
	var $r;
	var $col_r;
	var $allDay;
}

?>