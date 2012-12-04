{def $has_date_range = ezini('SearchSettings', 'HasDateRange', 'tccalendar.ini')|eq('enabled')
	 $CustomFilters = ezini('SearchSettings', 'CustomFilters', 'tccalendar.ini')
	 $filter_options = array()
	 $option_r = array()
	 $cal_colors = ezini('ColorSettings', 'CalColor', 'tccalendar.ini')
	 $cal_id = ''
	 $event_classes = ezini('ClassSettings', 'EventClassIds', 'tccalendar.ini')}
		
	<div class='legend' id='legend_categories'>
		<h2>Categories</h2>
		{foreach fetch( 'content', 'class_attribute', hash( 'attribute_id', 407 ) ).content.options as $c}
		{set $cal_id = $c.name|preg_replace("/[^A-Za-z\s]/", "")|explode(" ")|implode("_")|downcase}
		<div class='legend_block'>
		<input id="caltog_{$cal_id}" class="caltoggle" type="checkbox" checked=1 onclick="togglecals('{$cal_id}', this.checked)" /><span style="background: #{$cal_colors[$cal_id]}" class='legend_color'></span>{$c.name}
		</div>
		{/foreach}
	</div>
	
	<div class='legend' id='legend_regions'>
		<h2>Regions</h2>
		{foreach fetch( 'content', 'class_attribute', hash( 'attribute_id', 410 ) ).content.options as $c}
		{set $cal_id = $c.name|preg_replace("/[^A-Za-z\s]/", "")|explode(" ")|implode("_")|downcase}
		<div class='legend_block'>
		<input id="caltog_{$cal_id}" class="caltoggle" type="checkbox" checked=1 onclick="togglecals('{$cal_id}', this.checked)" /> {$c.name}
		</div>
		{/foreach}
	</div>
	
	<div class='legend' id='legend_type'>
		<h2>Type</h2>
		<div class='legend_block'>
		<input id="caltog_all" class="caltoggle" type="checkbox" checked=1 onclick="togglecals('all', this.checked)" />All Events
		</div>
		<div class='legend_block'>
		<input id="caltog_signature" class="caltoggle" type="checkbox" checked=1 onclick="togglecals('signature', this.checked)" />Signature Events
		</div>
	</div>


<form id='searchform' name='searchform' onsubmit='search_pre_process(this)' action='/functionview/output/ezfModuleFunctionCollection/search/eventstable'>
	<fieldset class='cal_s_1'><legend>Search Events</legend>
	{if $has_date_range}<div class='cal_block'><div class='cal_line'>
	<label>From </label><input class='procme' type='date' value='mm/dd/yyyy' name='filters[]' id='from_date_pl'/>
	<input class='sendme' type='hidden' name='filters[]' id='from_date' value='mm/dd/yyyy'>
	</div>
	<div class='cal_line'>
	<label>&nbsp;To </label><input class='procme' type='date' value='mm/dd/yyyy' name='filters[]' id='to_date_pl'/>
	<input class='sendme' type='hidden' name='filters[]' id='to_date' value='mm/dd/yyyy'>
	</div>
	<div id="cal1Container" class="yui-calcontainer single" style="display: none"></div>
	</div>
	{/if}
	<div class='cal_block'>
	<input type="hidden" name="cpath" value=""/> 
	<input class='sendme' type="hidden" name="offset" value="0"/>  
	<input class='sendme' type="hidden" name="limit" value="10"/>  
	<input type="hidden" name="cal" value=""/>
	<input type="hidden" name="getdate" value=""/>
	<input type="hidden" name="filters[]" class='sendme' value='({foreach $event_classes as $e}meta_contentclass_id_si:{$e}{delimiter} OR {/delimiter}{/foreach})' />
	<label for='query'>For </label><input type="text" size="18" name="query" class='sendme' value=""/>
	<input id="search_events" type="submit" name="submit" value="Search"/>
	</div>
	</fieldset>
</form>
{literal}
<script type='text/javascript'>
function search_pre_process(searchform) {
	$(searchform).find('.procme').each(function(){
		if ($(this).val()!='all' && $(this).val()!='mm/dd/yyyy') {
			if ($(this).attr('id') == 'to_date_pl') {
			to_date_tmp = $(this).val();
				$("#to_date").val("attr_date_from_dt:[0000-00-00T00:00:00Z TO " + dasheddate(tc_date) + "T00:00:00Z]");
			}
			else if ($(this).attr('id') == 'from_date_pl') {
			tc_date = new Date($(this).val());
				$("#from_date").val("(attr_date_to_dt:[" + dasheddate(tc_date) + "T00:00:00Z TO *] OR attr_date_from_dt:[" + dasheddate(tc_date) + "T00:00:00Z TO *])");
			}
		}
	});
}

function dasheddate(date) {
	var upcoming_start_currentDate = date
	var upcoming_start_day = upcoming_start_currentDate.getDate();
	var upcoming_start_month = upcoming_start_currentDate.getMonth() + 1;
	var upcoming_start_year = upcoming_start_currentDate.getFullYear();
	return upcoming_start_year + "-" + upcoming_start_month + "-" + upcoming_start_day;
}
</script>
{/literal}