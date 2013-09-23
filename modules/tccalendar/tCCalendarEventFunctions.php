<?php

Class tCCalendarEventFunctions {
	
	function fetchEvents($from_date, $to_date, $offset = 0, $limit = 10, $query = '*', $filters, $sort_by, $parent_node) {
		
		if (!$from_date) {
			$from_date = date('Y-m-d');
		}
		
		$ezphpicalendarini = eZINI::instance( 'tccalendar.ini' );
		
		$locale = eZLocale::instance();
		
		$dtINI = eZINI::instance( 'datetime.ini' );
        $formats = $dtINI->variable( 'ClassSettings', 'Formats' );
        $classFormat = $formats['solr'];
		
		$solr_from = $locale->formatDateTimeType( $classFormat, strtotime($from_date) - 1);
		
		$sd_i = $ezphpicalendarini->variable( "ClassSettings", "StartDateAttributeIdentifier");
		$r_i = $ezphpicalendarini->variable( "ClassSettings", "EventClassRepeatAttributes");
		
		$base_filter = "meta_class_identifier_ms:event";

		if ($to_date && $to_date != '') {
			$solr_to = $locale->formatDateTimeType( $classFormat, strtotime($to_date) + 1 );
			
			$base_filter .= " AND (attr_date_from_dt:[$solr_from TO $solr_to] OR attr_date_to_dt:[$solr_from TO $solr_to])";
			
		} else {
			
			$base_filter .= " AND ((attr_date_to_dt:[1969-12-31T19:00:00Z TO 1969-12-31T19:00:00Z] AND attr_date_from_dt:[$solr_from TO *]) OR (-attr_date_to_dt:[1969-12-31T19:00:00Z TO $solr_from]))";
			
		}
		
		foreach ($filters as $fk => $f) {
			if ($f != '') $base_filter .= ' AND '.$fk.':"'.$f.'"';
		}
		
		
		$es = eZSearch::search($query, array('FieldsToReturn' => array('attr_signature_event_b', 'attr_event_repeat_t', 'attr_'.$sd_i.'_dt', 'meta_node_id_si'), 'AsObjects' => false, 'SearchLimit' => 99999, 'SortBy' => array('attr_signature_event_b' => 'desc','attr_name_s' => 'asc'),'Filter' => array($base_filter)));

		$out = array();

		foreach ($es['SearchResult'] as $e) {
			
			$r = new eZRepeatEvent($e['event_repeat_t']);
			if ($r->isValid()) {
				$r->end = $r->get_end_time();
				$next_start_r = $r->get_timestamps(strtotime($from_date), false, 1);
				$next_start = $next_start_r[0];
				$pre = 'Multiple dates including ';
			} else {
				$usedate_r = explode($e[$sd_i.'_dt'], 'T00');
				$next_start = strtotime($usedate_r[0]);
				$pre = '';
			}
			$out[] = array('start' => $next_start, 'event' => $e, 'prefix' => $pre, 'type' => $e['signature_event_b']);
		} 
		
		if ($sort_by == 'Date') {
			usort($out,'eventTimeSort');
		}

		return array('result' => array('SearchResults' => array_slice($out, $offset, $limit), 'SearchCount' => count($out)));
	}

}

function eventTimeSort($item1,$item2)
{
	if ($item1['type'] != $item2['type']) {
		return ($item1['type']) ? -1 : 1;
	}
    if ($item1['start'] == $item2['start']) return 0;
    return ($item1['start'] > $item2['start']) ? 1 : -1;
}

?>