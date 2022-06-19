		$.fbuilder.typeList.push(
			{
				id:"fnumber",
				name:"Number",
				control_category:1
			}
		);
        $.fbuilder.controls[ 'fnumber' ] = function(){};
		$.extend(
			$.fbuilder.controls[ 'fnumber' ].prototype, 
			$.fbuilder.controls[ 'ffields' ].prototype,
			{
				title:"Number",
				ftype:"fnumber",
				predefined:"",
				predefinedClick:false,
				required:false,
				size:"small",
				thousandSeparator:"",
				decimalSymbol:".",
				min:"",
				max:"",
				dformat:"digits",
				formats:new Array("digits","number"),
			display:function()
				{
					return 'Available in <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">pro version</a>.';
				}
		});