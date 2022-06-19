	$.fbuilder.typeList.push(
		{
			id:"femail",
			name:"Email",
			control_category:1
		}
	);
	$.fbuilder.controls[ 'femail'] = function(){};
	$.extend(
		$.fbuilder.controls[ 'femail' ].prototype,
		$.fbuilder.controls[ 'ffields' ].prototype,
		{
			title:"Email",
			ftype:"femail",
			predefined:"",
			predefinedClick:false,
			required:false,
			size:"medium",
			equalTo:"",
			display:function()
				{
					return 'Available in <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">pro version</a>.';
				}
	});