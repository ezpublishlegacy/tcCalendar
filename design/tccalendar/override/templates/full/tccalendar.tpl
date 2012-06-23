<!--[if lt IE 7]> <style type="text/css"> @import url({"stylesheets/tcfullcalendar_ie.css"|ezdesign(no)}); </style> <![endif]-->
<div class="content medium-radius">
<div class="attribute-header">
    <h1>{$node.name|wash}</h1>
</div>

{ezcss_require(array('fullcalendar.css', 'tcfullcalendar.css'))}
{ezscript_require(array('fullcalendar.js'))}
{def $vars = ezservervars()
	 $mycol = ''}

{def $data_src = concat('/layout/set/monthtojson', $node.object.main_node.url_alias|ezroot(no))}

{literal}

<script type='text/javascript' src='{/literal}{$data_src}{literal}'></script>
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
		$('#tcfullcalendar').css('float', 'left');
	});

</script>
{/literal}

<div id='tcfullcalendar'>
	<div id='legend'>
		{if $node.data_map[ezini('ClassSettings', 'IsMasterAttributeIdentifier', 'tccalendar.ini')].content}
			{def $cals = fetch(content, tree, hash(parent_node_id, 2, class_filter_type, 'include', class_filter_array, array($node.class_identifier), main_node_only, true()))}
		{else}
			{def $cals = array($node)}
		{/if}
		{foreach $cals as $c}
			{set $mycol = $c.data_map[ezini('ClassSettings', 'CalColorAttributeIdentifier', 'tccalendar.ini')].content|explode("#")|implode('')}
			<div class='legend_block'><input id="caltog_{$mycol}" class="caltoggle" type="checkbox" checked=1 onclick="togglecals('{$mycol}', this.checked)" /><span style="background: #{$mycol}" class='legend_color'></span>{$c.name}</div>
		{/foreach}
	</div>
	<div id='calendar'></div>
</div> 
</div>
