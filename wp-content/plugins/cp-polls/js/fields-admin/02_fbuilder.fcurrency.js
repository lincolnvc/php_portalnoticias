		$.fbuilder.typeList.push(
			{
				id:"fcurrency",
				name:"Currency",
				control_category:1
			}
		);
        $.fbuilder.controls[ 'fcurrency' ] = function(){};
		$.extend(
			$.fbuilder.controls[ 'fcurrency' ].prototype, 
			$.fbuilder.controls[ 'ffields' ].prototype,
			{
				title:"Currency",
				ftype:"fcurrency",
				predefined:"",
				predefinedClick:false,
				required:false,
				size:"small",
				readonly:false,
				currencySymbol:"$",
				currencyText:"USD",
				thousandSeparator:",",
				centSeparator:".",
				formatDynamically:false,
			display:function()
				{
					return 'Available in <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">pro version</a>.';
				}
		});