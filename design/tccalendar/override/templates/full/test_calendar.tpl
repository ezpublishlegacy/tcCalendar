<div class="attribute-header">
    <h1>{$node.name|wash}</h1>
</div>

{ezcss_require(array('fullcalendar.css', 'tcfullcalendar.css'))}
{ezscript_require(array('fullcalendar.js'))}

{literal}

<script type='text/javascript' src='/layout/set/monthtojson/Medicare/Medicare-Event-Calendar'></script>
<script type='text/javascript'>
	$(document).ready(function() {
		
		$('#calendar').fullCalendar({
			editable: false,
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			events: tcevents
		});
		
	});

</script>
{/literal}


<div id='tcfullcalendar'>
	<div id='calendar'></div>
</div>