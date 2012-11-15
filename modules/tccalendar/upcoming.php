<?

$tpl = eZTemplate::factory();

$tc = new tcCalendar($cal_id, $from_time, $to_time);

$tpl->setVariable('events', $tc->events);

echo $tpl->fetch('design:tccalendar/upcoming.tpl');

eZExecution::cleanExit();

?>