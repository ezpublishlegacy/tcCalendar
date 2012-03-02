<!--[if lt IE 7]> <style type="text/css"> @import url({"stylesheets/repeat_date_ie.css"|ezdesign(no)}); </style> <![endif]-->

{ezscript_require(array('repeat_date.js'))}
{ezcss_require(array('repeat_date.css'))}

{def $repeats=first_set($attribute.data_text|explode('repeats=').1|explode('&').0, 'weekly')}

<span id="repeatevents">
<div id="ajaxcontent"></div>
</span>

{literal}
<script type="text/javascript">
$RepeatEvents.panel('repeats', {
	'id':'repeats',
	'label':'Repeats',
	'selected':'{/literal}{$repeats}{literal}',
	'container':'#repeatevents',
	'target':'#ajaxcontent',
	'options':{
		'daily':{
			'label':'Daily',
			'source':'/extension/tccalendar/design/tccalendar/javascript/include/daily.html'
		},
		'weekday':{
			'label':'Every Weekday(Mon-Fri)',
			'source':'/extension/tccalendar/design/tccalendar/javascript/include/weekday.html'
		},
		'mon-wed-fri':{
			'label':'Every Mon., Wed., and Fri.',
			'source':'/extension/tccalendar/design/tccalendar/javascript/include/mon-wed-fri.html'
		},
		'tues-thurs':{
			'label':'Every Tues. and Thurs.',
			'source':'/extension/tccalendar/design/tccalendar/javascript/include/tues-thurs.html'
		},
		'weekly':{
			'label':'Weekly',
			'source':'/extension/tccalendar/design/tccalendar/javascript/include/weekly.html'
		},
		'monthly':{
			'label':'Monthly',
			'source':'/extension/tccalendar/design/tccalendar/javascript/include/monthly.html'
		},
		'annual':{
			'label':'Yearly',
			'source':'/extension/tccalendar/design/tccalendar/javascript/include/annual.html'
		}
	}
});
{/literal}

$(function(){ldelim}
	{if $attribute.data_text} 
	$.fn.unserializeForm("{$attribute.data_text}");
	{/if}
	add_repeat_element_change_hooks();
{rdelim})

function update_repeat_element_post_val() {ldelim}
	output = $("#repeatevents input, #repeatevents select").serialize();
	$("#ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}").val(output);
{rdelim}

function add_repeat_element_change_hooks(){ldelim}
	event_ends_on_picker();
	$("#repeatevents input, #repeatevents select").change(function(){ldelim}
		update_repeat_element_post_val();
	{rdelim})
{rdelim}
</script>

<input id="ezcoa-{if ne( $attribute_base, 'ContentObjectAttribute' )}{$attribute_base}-{/if}{$attribute.contentclassattribute_id}_{$attribute.contentclass_attribute_identifier}" type="hidden" name="{$attribute_base}_ezstring_data_text_{$attribute.id}" value="{$attribute.data_text|wash( xhtml )}" />