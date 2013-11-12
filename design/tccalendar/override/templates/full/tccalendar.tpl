<!--[if lt IE 7]> <style type="text/css"> @import url({"stylesheets/tcfullcalendar_ie.css"|ezdesign(no)}); </style> <![endif]-->
<div class="content medium-radius">
<div class="attribute-header">
    <h1>{$node.name|wash}</h1>
</div>

{ezcss_require(array('fullcalendar.css', 'tcfullcalendar.css'))}
{ezscript_require(array('fullcalendar.js', 'jquery.dataTables.min.js', 'overlib.js'))}

{def $vars = ezservervars()
	 $mycol = ''
	 $data_src = concat('/layout/set/monthtojson', $node.object.main_node.url_alias|ezroot(no))
	 $has_search = ezini('SearchSettings', 'HasSearch', 'tccalendar.ini')|eq('enabled')
	 $has_date_range = ezini('SearchSettings', 'HasDateRange', 'tccalendar.ini')|eq('enabled')
	 $CustomFilters = ezini('SearchSettings', 'CustomFilters', 'tccalendar.ini')
	 $filter_options = array()
	 $option_r = array()
	 $event_classes = ezini('ClassSettings', 'EventClassIds', 'tccalendar.ini')
}

{literal}

<script type='text/javascript' src='{/literal}{if is_set(sitelink)}{$data_src|sitelink("no")}{/else}{$data_src}{/if}{literal}'></script>
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

<div id='tcfullcalendar' class='cal_nav_tab_long'>
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
	{if $has_search}
	<div id='tccal_search'>
		<form id='searchform' name='searchform' onsubmit = "javascript: get_callendar_html(this); return false;">
			<fieldset class='cal_s_1'><legend>Search Events</legend>
			{if $has_date_range}<div class='cal_block'><div class='cal_line'>
			<label>From </label><input class='sendme' type='date' value='mm/dd/yyyy' name='from_date' id='from_date'"/>
			</div>
			<div class='cal_line'>
			<label>&nbsp;To </label><input class='sendme' type='date' value='mm/dd/yyyy' name='to_date' id='to_date'"/>
			</div>
			<div id="cal1Container" class="yui-calcontainer single" style="display: none"></div>
			</div>
			{/if}
			<div class='cal_block'>
			<label for='filters'>Filter By </label>
			{foreach $CustomFilters as $filter}
				{set $filter_options = ezini(concat('SearchFilter_', $filter), 'FilterOptions', 'tccalendar.ini')}
				<select class='filterme' name="{ezini(concat('SearchFilter_', $filter), 'AttributeIdent', 'tccalendar.ini')}">
					{foreach $filter_options as $k => $option}
						{set $option_r = $option|explode('|')}
						{if count($option_r)|eq(1)}{set $option_r = $option_r|append($option_r[0])}{/if}
						<option value="{cond($k|eq(0),'' ,concat('dtfilter',$option_r[0]))}">{$option_r[1]}</option>
					{/foreach}
				</select>
			{/foreach}
			</div>
			<div class='cal_block'>
			<input type="hidden" name="cpath" value=""/>
			<input type="hidden" name="cal" value=""/>
			<input type="hidden" name="getdate" value=""/>
			<input type="hidden" name="classID" class='sendme' value='{$event_classes|serialize}' />
			<label for='query'>For </label><input type="text" size="18" name="query" class='sendme' value=""/>
			<input id="search_events" type="submit" name="submit" value="Search"/>
			</div>
			</fieldset>
		</form>
	</div>
	<div id='cal_search'><div id='cal_search_result'></div><a id='return_to_cal' href='#' onclick="$('#cal_search, #calendar').toggle();return false;">Return to calendar view</a></div>
	{/if}
	<div id='calendar'></div>
</div> 
</div>