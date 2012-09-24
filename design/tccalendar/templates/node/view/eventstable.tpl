{def $CustomFilters = ezini('SearchSettings', 'CustomFilters', 'tccalendar.ini')
	 $AttributeIdent = ''
	 $temp_val = ''
}

<table>
	
<thead>
	<tr>
		<th>Name</th>
		<th>Location</th>
		<th>Date</th>
		<th>Description</th>
		{foreach $CustomFilters as $filter}
		<th class="extra_info">{$filter}</th>
		{/foreach}
	</tr>
</thead>
	
{foreach $nodes['result']['SearchResult'] as $found_node}

<tr class="odd">
<td><b><a>{$found_node.name|wash}</a></b></td>
<td>{$found_node.data_map.location.content|wash}</td>
<td class=" sorting_1"><a class="ps3" href="#">{node_view_gui content_node=$found_node view="event_micro_time"}</td>
<td valign="top">
<!-- switch description on -->
{node_view_gui content_node=$found_node view="event_micro_description"}
<!-- switch description off -->
</td>
{foreach $CustomFilters as $filter}
	{set $AttributeIdent = ezini(concat('SearchFilter_', $filter), 'AttributeIdent', 'tccalendar.ini')}
	
	{if $AttributeIdent|contains('.')}
		{set $temp_val = $found_node}
		{foreach $AttributeIdent|explode('.') as $step}
			{set $temp_val = $temp_val[$step]}
		{/foreach}
		<td class="extra_info">dtfilter{$temp_val}</td>
	{else}
		<td class="extra_info">dtfilter{attribute_view_gui attribute=$found_node.data_map[$AttributeIdent]}</td>
	{/if}
{/foreach}
</tr>
			
{/foreach}