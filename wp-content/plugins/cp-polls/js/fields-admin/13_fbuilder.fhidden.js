	$.fbuilder.typeList.push(
		{
			id:"fhidden",
			name:"Hidden",
			control_category:1
		}
	);
	$.fbuilder.controls[ 'fhidden' ]=function(){};
	$.extend(
		$.fbuilder.controls[ 'fhidden' ].prototype,
		$.fbuilder.controls[ 'ffields' ].prototype,
		{
			title:"Hidden",
			ftype:"fhidden",
			predefined:"",
			display:function()
				{
					return 'Available only in <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">commercial versions</a>.';
				}
	});