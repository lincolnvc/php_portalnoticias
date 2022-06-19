	$.fbuilder.typeList.push(
		{
			id:"fdate",
			name:"Date Time",
			control_category:1
		}
	);
	$.fbuilder.controls[ 'fdate' ] = function(){};
	$.extend(
		$.fbuilder.controls[ 'fdate' ].prototype,
		$.fbuilder.controls[ 'ffields' ].prototype,
		{
			title:"Date",
			ftype:"fdate",
			predefined:"",
			predefinedClick:false,
			size:"medium",
			required:false,
			dformat:"mm/dd/yyyy",
			showDropdown:false,
			dropdownRange:"-10:+10",
			
			minDate:"",
			maxDate:"",
			minHour:0,
			maxHour:23,
			minMinute:0,
			maxMinute:59,
			
			stepHour: 1,
			stepMinute: 1,
			
			showTimepicker: false,
				
			defaultDate:"",
			defaultTime:"",
			working_dates:[true,true,true,true,true,true,true],
			
			formats:new Array("mm/dd/yyyy","dd/mm/yyyy"),
			
			display:function()
				{
					return 'Available in <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">pro version</a>.';
				}
	});