<?php

class tcCalendar {

	function tcCalendar($cal_id, $from_time = null, $to_time = null) {
		
		$ezphpicalendarini = eZINI::instance( 'tccalendar.ini' );
		
		$is_master_id = $ezphpicalendarini->variable( 'ClassSettings', 'IsMasterAttributeIdentifier' );
		
		$this->node_id = $cal_id;

		$cal_node = eZContentObjectTreeNode::fetch($cal_id);

		$cal_node_data = $cal_node->dataMap();
		
		$this->is_master = $cal_node_data[$is_master_id]->content();
		
		if ($this->is_master) $cal_id = 2;
		
		$eventclasses = $ezphpicalendarini->variable( "ClassSettings", "EventClassIds" );
		
		$this->title_id = $ezphpicalendarini->variable( "ClassSettings", "TitleAttributeIdentifier");
		$this->calcol_id = $ezphpicalendarini->variable( "ClassSettings", "CalColorAttributeIdentifier");
		$this->sd = $ezphpicalendarini->variable( "ClassSettings", "StartDateAttributeIdentifier");
		$this->st = $ezphpicalendarini->variable( "ClassSettings", "StartTimeAttributeIdentifier");
		$this->ed = $ezphpicalendarini->variable( "ClassSettings", "EndDateAttributeIdentifier");
		$this->et = $ezphpicalendarini->variable( "ClassSettings", "EndTimeAttributeIdentifier");
		
		if (!is_array($eventclasses)) $eventclasses = array($eventclasses);

		$params = array('ClassFilterType' => 'include', 'ClassFilterArray' => $eventclasses);
		
		
		if (!$this->is_master) {
			$params['Depth'] = 1;
			$params['DepthOperator'] = 'eq';
		}
		
		$attribute_filter = array();
		if ($to_time != null) $attribute_filter[] = array("event/from_time", "<=", strtotime($to_time.'T23:59:59'));
		if ($from_time != null) $attribute_filter[] = array("event/to_time", ">=", strtotime($from_time));
		if (count($attribute_filter)) $params['AttributeFilter'] = $attribute_filter;

		$events = eZContentObjectTreeNode::subTreeByNodeID( $params, $cal_id );
		
		if (in_array($cal_node->object()->contentClass()->attribute('id'), $eventclasses) && $for_output) $events = array($cal_node);

		$this->events = $events;
		
	}
	
	function monthtojson() {
		$col_r = array();
		$output =  "var tcevents = [\r\n";
		foreach($this->events as $e) {
			$this->allDay = false;
			$parent_node_id = $e->attribute('parent_node_id');
			
			if (!array_key_exists('node_'.$parent_node_id, $col_r)) {
				$parent_data = $e->fetchParent()->dataMap();
				if (!array_key_exists($this->calcol_id,$parent_data)) {
					$parent_col = '#000000';
				} else {
					$parent_col = $parent_data[$this->calcol_id]->content();
				}
				$col_r['node_'.$parent_node_id] = $parent_col;
			} else {
				$parent_col = $col_r['node_'.$parent_node_id];
			}
			$event_id = $e->attribute('node_id');
			$objData = $e->dataMap();
			$e_o = new stdClass();
			$e_o->backgroundColor = '"'.$parent_col.'"';
			$e_o->id = $event_id;
			$e_o->title = '"'.addslashes(preg_replace('/[^(\x20-\x7F)]*/','', $objData[$this->title_id]->content())).'"';
			$e_o->start = $this->get_event_start($objData);
			$e_o->end = $this->get_event_end($objData);
			if ($this->allDay === false) $e_o->allDay = 'false';
			$e_o->url = '"/' . $e->urlAlias(). '"';
			$out = chr(123);
			foreach($e_o as $k=>$v) {
				if ($v) $out .= "$k: $v,\r\n";
			}
			$output .= preg_replace("/,\r\n$/", "", $out) . chr(125) . ",\r\n" ;
		}
		
		$output .= "];\r\n";
		return $output;
	}
	
	function get_event_start($objData) {
		
		if (!is_object($objData[$this->sd])) return false;
		if (!is_object($objData[$this->st])) {
			$this->allDay = true;
			return false;
		}
					
		$date_from = $objData[$this->sd]->content();
		$time_from = $objData[$this->st]->content();
		if (!is_object($time_from)) $time_from = new eZDateTime($date_from);
		
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
	var $allDay;
}

?>