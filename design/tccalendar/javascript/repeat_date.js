var $RepeatEvents=(function($){
	if(!$){return;}

	var $Panels={},
		$Lib={
			'ajax':function(parameters){
				var result=false;
				$.ajax($.extend(true, {
					'async':false,
					'success':function(data){
						result=$(data);
					}
				}, parameters));
				return result;
			},
			// returns a jquery object of the select
			'createSelect':function(panel){
				var select=false,
				string = '<select id="'+panel.id+'" name="'+panel.id+'">';

				for (var i in panel.options){
					string+= '<option value="'+[i]+'">'+panel.options[i].label+'</option>';
				}

				string +='</select>';
				return $('<label>'+panel.label+': '+string+'</label>');
			},
			'change':function(panel){
				$(panel.target).empty();
				var obj=$(this),
					item=panel.options[obj.find('#'+panel.id).val()]
				;
				if(typeof(item.content)=='undefined'){
					item.content=$Lib.ajax({
						'url':item.source
					});
				}
				item.content.prependTo(panel.target);
				add_repeat_element_change_hooks();
			},
		}
	;

	var $Self={
			'load':function(){
				$.each($Panels, function(identifier, panel){
					if(!panel.loaded){
						var select=$Lib.createSelect(panel).prependTo(panel.container).change(function(){
							$Lib.change.call(select, panel);
						});
						if(panel.selected){
							$("#"+panel.id).val(panel.selected);
							$Lib.change.call(select, panel);
						}
						panel.loaded=true;
					}
				});
			},
			'panel':function(identifier, parameters){
				$Panels[identifier]=$.extend(true, {
					'loaded':false
				}, parameters);
			}
		}
	;

	$(function(){
		$Self.load();
	});

	return $Self;

})(typeof(jQuery)!=='undefined'?jQuery:null);

(function($) {
    $.fn.unserializeForm = function(values) {

        if (!values) {
            return this;
        }

        values = values.split("&");

        var serialized_values = [];
        $.each(values, function() {
            var properties = this.split("=");

            if ((typeof properties[0] != 'undefined') && (typeof properties[1] != 'undefined')) {
				els = $("[name='" + decodeURIComponent((properties[0] + '').replace(/\+/g, '%20')) + "']");
				clearme = true;
				els.each(function(){
					el = $(this);
					if (el.attr('type') == 'checkbox' || el.attr('type') == 'radio') {
						if (clearme) {
							$("[name='" + decodeURIComponent((properties[0] + '').replace(/\+/g, '%20')) + "']").attr('checked','');
							clearme = false;
						}
						$("[name='" + decodeURIComponent((properties[0] + '').replace(/\+/g, '%20')) + "'][value='"+decodeURIComponent((properties[1] + '').replace(/\+/g, '%20'))+"']").attr('checked','checked');
					} else {
						$("[name='" + decodeURIComponent((properties[0] + '').replace(/\+/g, '%20')) + "']").val(decodeURIComponent((properties[1] + '').replace(/\+/g, '%20')));
					}
				})
            }
        });
    }
})(jQuery);

function event_ends_on_picker(){
	
	$("#repeatevents .date [name='occurrences']").change(function(){
		$("#repeatevents .date [name='ends']").each(function(){
			value = ($(this).attr('value') == 'after') ? "checked":"";
			$(this).attr('checked', value);
		})
	})
	$("#repeatevents .date [name='ends_on']").change(function(){
		$("#repeatevents .date [name='ends']").each(function(){
			value = ($(this).attr('value') == 'on') ? "checked":"";
			$(this).attr('checked', value);
		})
	})
	$("#repeatevents .date [name='ends']").change(function(){
		if ($(this).attr('value') == 'never') {
			$("#repeatevents .date [name='occurrences']").val('');
			$("#repeatevents .date [name='ends_on']").val('');
		} 
		if ($(this).attr('value') == 'after') {
			$("#repeatevents .date [name='ends_on']").val('');
		}
		if ($(this).attr('value') == 'on') {
			$("#repeatevents .date [name='occurrences']").val('');
		}
	})
	jQuery("[name='ends_on']").datepicker({showOn:'button',
                                         buttonText:"Calendar",
                                         buttonImageOnly:false,
                                         showAnim:'fadeIn',
                                         minDate:new Date( 1970, 0, 1),
                                         maxDate:new Date( 2038, 0, 19),
                                         changeMonth:true,
                                         changeYear:true,
                                         nextText:"Next",
                                         currentText:"Today",
                                         prevText:"Previous",
                                         dayNames:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],
                                         dayNamesMin:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],
                                         dayNamesShort:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],
                                         monthNames:["January","February","March","April","May","June","July","August","September","October","November","December"],
                                         monthNamesShort:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"], 
                                         firstDay:1,
                                         onSelect:function(textDate, picker)
                                                  {
													$("#repeatevents .date [name='ends']").each(function(){
														value = ($(this).attr('value') == 'on') ? "checked":"";
														$(this).attr('checked', value);
													})
													$("#repeatevents .date [name='occurrences']").val('');
													update_repeat_element_post_val();
                                                  },
                                         beforeShow:function(input)
                                                    {                                                        
                                                        var d = new Date();
                                                        return {defaultDate:d};
                                                    }
										});
}
