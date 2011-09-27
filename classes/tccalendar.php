<?php

class tcCalendar {

	function tcCalendar($cal_id, $from_time = null, $to_time = null) {
		
		$this->node_id = $cal_id;

		$cal_node = eZContentObjectTreeNode::fetch($cal_id);

		$cal_node_data = $cal_node->dataMap();
		
		$ezphpicalendarini = eZINI::instance( 'tccalendar.ini' );
		
		$eventclasses = $ezphpicalendarini->variable( "ClassSettings", "EventClassIds" );
		
		$this->title_id = $ezphpicalendarini->variable( "ClassSettings", "TitleAttributeIdentifier");
		$this->sd = $ezphpicalendarini->variable( "ClassSettings", "StartDateAttributeIdentifier");
		$this->st = $ezphpicalendarini->variable( "ClassSettings", "StartTimeAttributeIdentifier");
		$this->ed = $ezphpicalendarini->variable( "ClassSettings", "EndDateAttributeIdentifier");
		$this->et = $ezphpicalendarini->variable( "ClassSettings", "EndTimeAttributeIdentifier");
		
		if (!is_array($eventclasses)) $eventclasses = array($eventclasses);

		$params = array('ClassFilterType' => 'include', 'ClassFilterArray' => $eventclasses, 'Depth' => 1, 'DepthOperator' => 'eq');
		
		$attribute_filter = array();
		if ($to_time != null) $attribute_filter[] = array("event/from_time", "<=", strtotime($to_time.'T23:59:59'));
		if ($from_time != null) $attribute_filter[] = array("event/to_time", ">=", strtotime($from_time));
		if (count($attribute_filter)) $params['AttributeFilter'] = $attribute_filter;

		$events = eZContentObjectTreeNode::subTreeByNodeID( $params, $cal_id );
		
		if (in_array($cal_node->object()->contentClass()->attribute('id'), $eventclasses) && $for_output) $events = array($cal_node);

		$this->events = $events;
		
	}
	
	function monthtojson() {
		header("Content-type: text/javascript");
		echo "var tcevents = [\r\n";
		foreach($this->events as $e) {
			
			$event_id = $e->attribute('node_id');
			$objData = $e->dataMap();
			$e_o = new stdClass();
			$e_o->id = $event_id;
			$e_o->title = '"'.addslashes($objData[$this->title_id]->content()).'"';
			$e_o->start = $this->get_event_start($objData);
			$e_o->end = $this->get_event_end($objData);
			$out = chr(123);
			foreach($e_o as $k=>$v) {
				if ($v) $out .= "$k: $v,\r\n";
			}
			echo preg_replace("/,\r\n$/", "", $out) . chr(125) . ",\r\n" ;
		}
		echo "];\r\n";
		
	}
	
	function get_event_start($objData) {
		
		if (!is_object($objData[$this->sd])) return false;
		if (!is_object($objData[$this->st])) return false;
					
		$date_from = $objData[$this->sd]->attribute('data_int');
		$time_from = $objData[$this->st]->attribute('data_int');
		
		$out = "new Date(" . date('Y',$date_from) . ", " . date('m',$date_from) . ", " . date('d',$date_from) . ", " . date('i',$time_from) . ", " . date('s',$time_from) .")";
		
		return $out;
			 
	}
	
	function get_event_end($objData) {
	
		if (!is_object($objData[$this->ed])) return false;
		if (!is_object($objData[$this->et])) return false;
					
		$date_to = $objData[$this->ed]->attribute('data_int');
		$time_to = $objData[$this->et]->attribute('data_int');
		
		if ($time_to == 0 || date('His', $time_to) == '000000') return false;
		
		$out =  "new Date(".date('Y',$date_to).", ".date('m',$date_to).", ".date('d',$date_to).", ".date('i',$time_to).", ".date('s',$time_to).")";
		
		return $out;
				
	}
	
	var $node_id;
	var $events;
	var $output;
	var $sd;
	var $st;
	var $ed;
	var $et;
	
}

?>