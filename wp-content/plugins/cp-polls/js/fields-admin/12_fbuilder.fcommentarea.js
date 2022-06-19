	$.fbuilder.typeList.push(
		{
			id:"fCommentArea",
			name:"Instruct. Text",
			control_category:1
		}
	);
	$.fbuilder.controls[ 'fCommentArea' ]=function(){};
	$.extend(
		$.fbuilder.controls[ 'fCommentArea' ].prototype,
		$.fbuilder.controls[ 'ffields' ].prototype,
		{
			title:"Comments here",
			ftype:"fCommentArea",
			userhelp:"A description of the section goes here.",
			display:function()
				{
					return 'Available in <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">pro version</a>.';
				}
	});