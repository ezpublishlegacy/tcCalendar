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
	 $has_legend = ezini('SearchSettings', 'HasLegend', 'tccalendar.ini')|eq('enabled')
}

{literal}

<script type='text/javascript' src='{/literal}{$data_src}{literal}'></script>

{/literal}

<div id='tcfullcalendar' class='cal_nav_tab_long'>
	{if $has_legend}
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
	{/if}
	{if $has_search}
	<div id='tccal_search'>
		{include uri='design:tccalendar/tc_search.tpl'}
	</div>
	<div id='cal_search'><div id='cal_search_result'></div><a id='return_to_cal' href='#' onclick="$('#cal_search, #calendar').toggle();return false;">Return to calendar view</a></div>
	{/if}
	<div id='calendar'></div>
</div> 
</div>