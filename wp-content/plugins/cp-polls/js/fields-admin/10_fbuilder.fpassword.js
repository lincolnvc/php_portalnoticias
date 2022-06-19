	$.fbuilder.typeList.push(
		{
			id:"fpassword",
			name:"Password",
			control_category:1
		}
	);
	$.fbuilder.controls[ 'fpassword' ] = function(){};
	$.extend(
		$.fbuilder.controls[ 'fpassword' ].prototype,
		$.fbuilder.controls[ 'ffields' ].prototype,
		{
			title:"Untitled",
			ftype:"fpassword",
			predefined:"",
			predefinedClick:false,
			required:false,
			size:"medium",
			minlength:"",
			maxlength:"",
			equalTo:"",
			display:function()
				{
					return 'Available in <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">pro version</a>.';
				}
	});