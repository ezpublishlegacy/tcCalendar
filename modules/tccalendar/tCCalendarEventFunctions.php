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
		
		
		$es = eZSearch::search($query, array('SearchLimit' => 99999, 'SortBy' => array('meta_name_t' => 'asc'),'Filter' => array($base_filter)));

		$out = array();

		foreach ($es['SearchResult'] as $e) {
			$dm = $e->dataMap();
			$r = $dm['event_repeat'];
			if ($r->hasContent() && $r->content()->isValid()) {
				$next_start_r = $r->content()->get_timestamps(strtotime($from_date), false, 1);
				$next_start = $next_start_r[0];
				$pre = 'Multiple dates from ';
			} else {
				$next_start = $dm[$sd_i]->attribute('data_int');
				$pre = '';
			}
			$out[] = array('start' => $next_start, 'event' => $e, 'prefix' => $pre);
		} 
		
		if ($sort_by == 'Date') {
			usort($out,'eventTimeSort');
		}

		return array('result' => array('SearchResults' => array_slice($out, $offset, $limit), 'SearchCount' => count($out)));
	}

}

function eventTimeSort($item1,$item2)
{
    if ($item1['start'] == $item2['start']) return 0;
    return ($item1['start'] > $item2['start']) ? 1 : -1;
}

?>