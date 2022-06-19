	$.fbuilder.typeList.push(
		{
			id:"fPageBreak",
			name:"Page break",
			control_category:1
		}
	);
	$.fbuilder.controls[ 'fPageBreak' ] = function(){};
	$.extend(
		$.fbuilder.controls[ 'fPageBreak' ].prototype,
		$.fbuilder.controls[ 'ffields' ].prototype,
		{
			title:"Page Break",
			ftype:"fPageBreak",
			display:function()
				{
					return 'Available in <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">pro version</a>.';
				}
	});