<?php
class poll_manager_design{
	private $menu_name;
	private $databese_names;
	private $parametrs;
	private $plugin_url;
	private $plugin_path;
	
	function __construct($params){
		// set plugin url
		if(isset($params['plugin_url']))
			$this->plugin_url=$params['plugin_url'];
		else
			$this->plugin_url=trailingslashit(dirname(plugins_url('',__FILE__)));
		// set plugin path
		if(isset($params['plugin_path']))
			$this->plugin_path=$params['plugin_path'];
		else
			$this->plugin_path=trailingslashit(dirname(plugin_dir_path('',__FILE__)));
					
	}
	
	public function controller_page(){
		global $wpdb;
		$task="default";
		$id=0;
		if(isset($_GET["task"])){
			$task=$_GET["task"];
		}
		if(isset($_GET["id"])){
			$id=$_GET["id"];
		}
		
		switch($task){
		case 'add_poll_theme':	
			$this->add_edit_theme($id);
			break;
			
		case 'add_edit_theme':	
			$this->add_edit_theme($id);
			break;
		
		case 'save_poll':		
			if($id)	
				$this->update_poll($id);
			else
				$this->save_poll();
				
			$this->display_table_list_theme();	
			break;
			
			
		case 'update_poll':		
			if($id){
				$this->update_poll($id);
			}else{
				$this->save_poll();
				$_GET['id']=$wpdb->get_var("SELECT MAX(id) FROM ".$wpdb->prefix."polls_templates");
				$id=$_GET['id'];
			}
			$this->add_edit_theme($id);
			break;
		case 'set_default_theme':
			$this->set_default_theme($id);
			$this->display_table_list_theme();	
		break;
		case 'remove_theme':	
			$this->remove_theme($id);
			$this->display_table_list_theme();
			break;
				
		default:
			$this->display_table_list_theme();
		}
	}
	private function save_poll(){
		global $wpdb;
		$name=$_POST['poll_name'];
		$_POST['parametrs']['poll_background_color'] = '#ffffff';
		$_POST['parametrs']['poll_background_color_hower'] = '#ffffff';
		$_POST['parametrs']['poll_background_opacity'] = '73';
		$_POST['parametrs']['poll_background_opacity_hower'] = '65';
		$_POST['parametrs']['poll_answer_inner_distance'] = '5';
		$_POST['parametrs']['poll_answer_fontsize'] = '14';
		$_POST['parametrs']['poll_answer_fontsize_hower'] = '14';
		$_POST['parametrs']['poll_answer_of_the_left'] = '5';
		$_POST['parametrs']['poll_answer_of_the_left_hower'] = '5';
		$_POST['parametrs']['color_responding_of_the_answer'] = '#afafaf';
		$_POST['parametrs']['color_responding_of_the_answer_hower'] = '#afafaf';
		$_POST['parametrs']['color_of_the_answer_text'] = '#0c0101';
		$_POST['parametrs']['color_of_the_answer_text_hower'] = '#0c0101';
		$_POST['parametrs']['font_family_of_answer_text'] = 'Arial,Helvetica Neue,Helvetica,sans-serif';
		$_POST['parametrs']['font_family_of_answer_text_hower'] = 'Arial,Helvetica Neue,Helvetica,sans-serif';
		$_POST['parametrs']['font_style_of_answer_text_bold_hower'] = 'on';
		$_POST['parametrs']['poll_answer_border_width'] = '1';
		$_POST['parametrs']['poll_answer_border_width_hower'] = '1';
		$_POST['parametrs']['poll_answer_border_radius'] = '5';
		$_POST['parametrs']['poll_answer_border_radius_hower'] = '5';
		$_POST['parametrs']['poll_answer_border_type'] = 'outset';
		$_POST['parametrs']['poll_answer_border_type_hower'] = 'outset';
		$_POST['parametrs']['color_of_the_border'] = '#3b5998';
		$_POST['parametrs']['color_of_the_border_hower'] = '#3b5998';
		$_POST['parametrs']['poll_show_count_option'] = '0';
		$_POST['parametrs']['poll_show_count_option_hower'] = '1';
		$_POST['parametrs']['poll_count_fontsize'] = '13';
		$_POST['parametrs']['poll_count_fontsize_hower'] = '13';
		$_POST['parametrs']['poll_count_of_the_left'] = '7';
		$_POST['parametrs']['poll_count_of_the_left_hower'] = '7';
		$_POST['parametrs']['color_of_the_count'] = '#878787';
		$_POST['parametrs']['color_of_the_count_hower'] = '#0c0c0c';
		$_POST['parametrs']['font_family_of_count_text'] = 'Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif';
		$_POST['parametrs']['font_family_of_count_text_hower'] = 'Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif';
		$_POST['parametrs']['font_style_of_count_text_bold'] = 'on';
		$_POST['parametrs']['font_style_of_count_text_bold_hower'] = 'on';
		$_POST['parametrs']['font_style_of_count_text_italick_hower'] = 'on';
		$parametrs=json_encode($_POST['parametrs']);
		$save_or_no=$wpdb->insert( $wpdb->prefix.'polls_templates', 
			array( 
				'name' => $name,
				'option_value' => $parametrs,
				'default' => 0,
				
			), 
			array( 
				'%s', 
				'%s',
				'%d',
			) 
		);
		if($save_or_no){
			?><div class="updated"><p><strong>Item Saved</strong></p></div><?php
		}
		else{
			?><div id="message" class="error"><p>Error please reinstall plugin</p></div> <?php
		}
	}
	
	
	private function update_poll($id){
		global $wpdb;
		$name=$_POST['poll_name'];
		$_POST['parametrs']['poll_background_color'] = '#ffffff';
		$_POST['parametrs']['poll_background_color_hower'] = '#ffffff';
		$_POST['parametrs']['poll_background_opacity'] = '73';
		$_POST['parametrs']['poll_background_opacity_hower'] = '65';
		$_POST['parametrs']['poll_answer_inner_distance'] = '5';
		$_POST['parametrs']['poll_answer_fontsize'] = '14';
		$_POST['parametrs']['poll_answer_fontsize_hower'] = '14';
		$_POST['parametrs']['poll_answer_of_the_left'] = '5';
		$_POST['parametrs']['poll_answer_of_the_left_hower'] = '5';
		$_POST['parametrs']['color_responding_of_the_answer'] = '#afafaf';
		$_POST['parametrs']['color_responding_of_the_answer_hower'] = '#afafaf';
		$_POST['parametrs']['color_of_the_answer_text'] = '#0c0101';
		$_POST['parametrs']['color_of_the_answer_text_hower'] = '#0c0101';
		$_POST['parametrs']['font_family_of_answer_text'] = 'Arial,Helvetica Neue,Helvetica,sans-serif';
		$_POST['parametrs']['font_family_of_answer_text_hower'] = 'Arial,Helvetica Neue,Helvetica,sans-serif';
		$_POST['parametrs']['font_style_of_answer_text_bold_hower'] = 'on';
		$_POST['parametrs']['poll_answer_border_width'] = '1';
		$_POST['parametrs']['poll_answer_border_width_hower'] = '1';
		$_POST['parametrs']['poll_answer_border_radius'] = '5';
		$_POST['parametrs']['poll_answer_border_radius_hower'] = '5';
		$_POST['parametrs']['poll_answer_border_type'] = 'outset';
		$_POST['parametrs']['poll_answer_border_type_hower'] = 'outset';
		$_POST['parametrs']['color_of_the_border'] = '#3b5998';
		$_POST['parametrs']['color_of_the_border_hower'] = '#3b5998';
		$_POST['parametrs']['poll_show_count_option'] = '0';
		$_POST['parametrs']['poll_show_count_option_hower'] = '1';
		$_POST['parametrs']['poll_count_fontsize'] = '13';
		$_POST['parametrs']['poll_count_fontsize_hower'] = '13';
		$_POST['parametrs']['poll_count_of_the_left'] = '7';
		$_POST['parametrs']['poll_count_of_the_left_hower'] = '7';
		$_POST['parametrs']['color_of_the_count'] = '#878787';
		$_POST['parametrs']['color_of_the_count_hower'] = '#0c0c0c';
		$_POST['parametrs']['font_family_of_count_text'] = 'Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif';
		$_POST['parametrs']['font_family_of_count_text_hower'] = 'Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif';
		$_POST['parametrs']['font_style_of_count_text_bold'] = 'on';
		$_POST['parametrs']['font_style_of_count_text_bold_hower'] = 'on';
		$_POST['parametrs']['font_style_of_count_text_italick_hower'] = 'on';
		$parametrs=json_encode($_POST['parametrs']);
		
		$wpdb->update( $wpdb->prefix.'polls_templates', 
			array( 
				'name' => $name,
				'option_value' => $parametrs,
			), 
			array( 
				'id'=>$id 
			),
			array( 
				'%s', 
				'%s'
			),
			array( 
				'%d'
			)  
		);
		if($save_or_no){
			?><div class="updated"><p><strong>Item Saved</strong></p></div><?php
		}
	}
	
	
	private function remove_theme($id){
		global $wpdb;
		$default_theme = $wpdb->get_var($wpdb->prepare('SELECT `default` FROM ' . $wpdb->prefix . 'polls_templates WHERE id="%d"', $id));
		if (!$default_theme) {
			$wpdb->query($wpdb->prepare('DELETE FROM ' . $wpdb->prefix . 'polls_templates WHERE id="%d"', $id));
		}
		else{
			?><div id="message" class="error"><p>You cannot remove default theme</p></div> <?php
		}
	}
	
	private function set_default_theme($id){
		global $wpdb;
		$wpdb->update($wpdb->prefix . 'polls_templates', array('default' => 0), array('default' => 1));
		$save = $wpdb->update($wpdb->prefix . 'polls_templates', array('default' => 1), array('id' => $id));		
	}
	private function display_table_list_theme(){
		
		
		?>
        <style>
        .description_row:nth-child(odd){
			background-color: #f9f9f9;
		}
		
        </style>
        <script> var my_table_list=<?php echo $this->generete_jsone_list(); ?></script>
        <div class="wrap">
            <form method="post"  action="" id="admin_form" name="admin_form" ng-app="" ng-controller="customersController">
			<h2>Polls Themes <a href="admin.php?page=Polls-design&task=add_poll_theme" class="add-new-h2">Add New</a></h2>            
   
            <div class="tablenav top" style="width:95%">  
                <input type="text" placeholder="Search" ng-change="filtering_table();" ng-model="searchText">            
                <div class="tablenav-pages"><span class="displaying-num">{{filtering_table().length}} items</span>
                <span ng-show="(numberOfPages()-1)>=1">
                    <span class="pagination-links"><a class="first-page" ng-class="{disabled:(curPage < 1 )}" title="Go to the first page" ng-click="curPage=0">«</a>
                    <a class="prev-page" title="Go to the previous page" ng-class="{disabled:(curPage < 1 )}" ng-click="curPage=curPage-1; curect()">‹</a>
                    <span class="paging-input"><span class="total-pages">{{curPage + 1}}</span> of <span class="total-pages">{{ numberOfPages() }}</span></span>
                    <a class="next-page" title="Go to the next page" ng-class="{disabled:(curPage >= (numberOfPages() - 1))}" ng-click=" curPage=curPage+1; curect()">›</a>
                    <a class="last-page" title="Go to the last page" ng-class="{disabled:(curPage >= (numberOfPages() - 1))}" ng-click="curPage=numberOfPages()-1">»</a></span></div>
                </span>
            </div>
            <table class="wp-list-table widefat fixed pages" style="width:95%">
                <thead>
                    <tr>
                        <th style="width: 100px;" id='oreder_by_id' data-ng-click="order_by='id'; reverse=!reverse; ordering($event,order_by,reverse);" class="manage-column sortable desc" scope="col"><a><span>ID</span><span class="sorting-indicator"></span></a></th>
                        <th data-ng-click="order_by='name'; reverse=!reverse; ordering($event,order_by,reverse)" class="manage-column sortable desc"><a><span>Name</span><span class="sorting-indicator"></span></a></th>
                        <th style="width:100px"><a>Default</span></a></th>
                        <th style="width:80px">Edit</th>
                        <th  style="width:80px">Delete</th>
                    </tr>
                </thead>
                <tbody>
                 <tr ng-repeat="rows in names | filter:filtering_table" class="description_row">
                         <td>{{rows.id}}</td>
                         <td><a href="admin.php?page=Polls-design&task=add_edit_theme&id={{rows.id}}">{{rows.name}}</a></td>
                         <td><a href="admin.php?page=Polls-design&task=set_default_theme&id={{rows.id}}"><img src="<?php echo $this->plugin_url.'admin/images/default' ?>{{rows.default}}.png"></a></td>
                         <td><a href="admin.php?page=Polls-design&task=add_edit_theme&id={{rows.id}}">Edit</a></td>
                         <td><a href="admin.php?page=Polls-design&task=remove_theme&id={{rows.id}}">Delete</a></td>
                               
                  </tr> 
                </tbody>
            </table>
        </form>
        </div>
    <script>

jQuery(document).ready(function(e) {
    jQuery('a.disabled').click(function(){return false});
	jQuery('form').on("keyup keypress", function(e) {
		var code = e.keyCode || e.which; 
		if (code  == 13) {               
			e.preventDefault();
			return false;
		}
	});
});
    function customersController($scope,$filter) {
		var orderBy = $filter('orderBy');
		$scope.previsu_search_result='';
		$scope.oredering=new Array();
		$scope.baza = my_table_list;
		$scope.curPage = 0;
		$scope.pageSize = 20;
		$scope.names=$scope.baza.slice( $scope.curPage* $scope.pageSize,( $scope.curPage+1)* $scope.pageSize)
		$scope.numberOfPages = function(){
		   return Math.ceil($scope.filtering_table().length / $scope.pageSize);
	   };
	   $scope.filtering_table=function(){
		   var new_searched_date_array=new Array;
		   new_searched_date_array=[];
		   angular.forEach($scope.baza,function(value,key){
			   var catched=0;
			   angular.forEach(value,function(value_loc,key_loc){
				   if((''+value_loc).indexOf($scope.searchText)!=-1 || $scope.searchText=='' || typeof($scope.searchText) == 'undefined')
					  catched=1;
			   })
			  if(catched)
				  new_searched_date_array.push(value);
		   })
		   if($scope.previsu_search_result != $scope.searchText){
			  
			  $scope.previsu_search_result=$scope.searchText;
			   $scope.ordering($scope.oredering[0],$scope.oredering[1], $scope.oredering[2]);
			   
		   }
		   if(new_searched_date_array.length<=$scope.pageSize)
		   		$scope.curPage = 0;
		   return new_searched_date_array;
	   }
	   $scope.curect=function(){
		   if( $scope.curPage<0){
				$scope.curPage=0;
		   }
		   if( $scope.curPage> $scope.numberOfPages()-1)
			   $scope.curPage=$scope.numberOfPages()-1;
		  $scope.names=$scope.filtering_table().slice( $scope.curPage* $scope.pageSize,( $scope.curPage+1)* $scope.pageSize)
	   }
		
		$scope.ordering=function($event,order_by,revers){
		   if( typeof($event) != 'undefined' && typeof($event.currentTarget) != 'undefined')
		   		element=$event.currentTarget;
			else
				element=jQuery();
		   
			if(revers)
			  indicator='asc'
			else
			  indicator='desc'
			 $scope.oredering[0]=$event;
			 $scope.oredering[1]=order_by;
			 $scope.oredering[2]=revers;
			jQuery(element).parent().find('.manage-column').removeClass('sortable desc asc sorted');
			jQuery(element).parent().find('.manage-column').not(element).addClass('sortable desc');
			jQuery(element).addClass('sorted '+indicator);		  
			$scope.names=orderBy($scope.filtering_table(),order_by,revers).slice( $scope.curPage* $scope.pageSize,( $scope.curPage+1)* $scope.pageSize)
		}
	}
    </script>
		<?php
		$this->generete_jsone_list();
	}
	private function generete_jsone_list(){
		global $wpdb;
		$query = "SELECT `id`,`name`,`default` FROM ".$wpdb->prefix."polls_templates";
		$rows=$wpdb->get_results($query);
		$json="[";
		$no_frst_storaket=1;
		foreach($rows as $row){
			$json.=(($no_frst_storaket) ? '' : ',' )."{";
			$no_frst_storaket=1;
			foreach($row as $key=>$value){
				if($key!='id'){
					$json.= "".(($no_frst_storaket) ? '' : ',' )."'".$key."':"."'".(($value)?preg_replace('/^\s+|\n|\r|\s+$/m', '',htmlspecialchars_decode(addslashes(strip_tags($value)))):'0')."'";				
				}
				else{					
					$json.= "".(($no_frst_storaket) ? '' : ',' )."'".$key."':".(($value)?htmlspecialchars_decode(addslashes($value)):'0'); 
				}
				
				$no_frst_storaket=0;
			 }			 
			 $json.="}";
		}
		$json.="]";
		return $json;
	}	
	
	private function generete_theme_parametrs($id=0){
		global $wpdb;
		
		if($id){
			return $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'polls_templates WHERE id='.$id);	
		}
		return $wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'polls_templates WHERE `default`=1');	
	}
	
	
	private function add_edit_theme($id=0){
		global $wpdb;
		$theme=$this->generete_theme_parametrs($id);
		$poll_parametrs=json_decode($theme->option_value,true);
		$poll_name=$theme->name;
		?>
        
			<style>
            .poll_theme_title{
                display:inline-block;
            }
            .header_action_buttons{
                display:inline-block;
                float:right;
                margin-top:14px;
            }
            .header{
                width:100%;
            }
            .conteiner{
                width:100%;
                max-width:1280px;
            }
            .poll_name{
                padding: 0px 8px 0px 8px;
                margin-bottom: 5px !important;
                margin-top: 15px !important;
                font-size: 1.7em;
                height: 1.7em;
                width: 100%;
                outline: 0;
                margin: 0;
                background-color: #fff;
            }
            .all_options_panel_table th{
                font-size: 16px;
                font-weight: bold;
                /*text-align:center;*/
            }
			</style>
    <style id="style_poll_background_opacity_input">.main_div_of_the_poll_answer{background-color: <?php echo $this->hex2rgba($poll_parametrs['poll_background_color'],$poll_parametrs['poll_background_opacity'] ) ?>;}</style>
	<style id="style_poll_background_opacity_hower_input">.main_div_of_the_poll_answer:hover{background-color: <?php echo $this->hex2rgba($poll_parametrs['poll_background_color_hower'],$poll_parametrs['poll_background_opacity_hower'] ) ?>;}</style>
	<style id="style_poll_answer_width_input">.main_div_of_the_poll_answer{width: <?php echo $poll_parametrs['poll_answer_width'] ?>%;}</style>
	<style id="style_poll_answer_width_hower_input">.main_div_of_the_poll_answer:hover{width: <?php echo $poll_parametrs['poll_answer_width_hower'] ?>%;}</style>
	<style id="style_poll_answer_height_input">.main_div_of_the_poll_answer{height: <?php echo $poll_parametrs['poll_answer_height'] ?>px;}</style>
	<style id="style_poll_answer_height_hower_input">.main_div_of_the_poll_answer:hover{height: <?php echo $poll_parametrs['poll_answer_height_hower'] ?>px;}</style>
	<style id="style_poll_answer_inner_distance_input">.main_div_of_the_poll_answer{margin-bottom: <?php echo $poll_parametrs['poll_answer_inner_distance'] ?>px;}</style>
	<style id="style_poll_answer_fontsize_input">.poll_answer_title{font-size: <?php echo $poll_parametrs['poll_answer_fontsize'] ?>px;}</style>
	<style id="style_poll_answer_fontsize_hower_input">.main_div_of_the_poll_answer:hover .poll_answer_title{font-size: <?php echo $poll_parametrs['poll_answer_fontsize_hower'] ?>px;}</style>
	<style id="style_poll_answer_of_the_top_input">.main_div_of_the_poll_answer .poll_answer_title{ top: <?php echo $poll_parametrs['poll_answer_of_the_top'] ?>px; }</style>
	<style id="style_poll_answer_of_the_top_hower_input">.main_div_of_the_poll_answer:hover .poll_answer_title{ top: <?php echo $poll_parametrs['poll_answer_of_the_top_hower'] ?>px; }</style>
	<style id="style_poll_answer_of_the_left_input">.main_div_of_the_poll_answer .poll_answer_title{ left: <?php echo $poll_parametrs['poll_answer_of_the_left'] ?>px; }</style>
	<style id="style_poll_answer_of_the_left_hower_input">.main_div_of_the_poll_answer:hover .poll_answer_title{ left: <?php echo $poll_parametrs['poll_answer_of_the_left_hower'] ?>; }</style>
	<style id="style_color_responding_of_the_answer_input">.main_div_of_the_poll_answer .pracents_of_the_poll{background-color: <?php echo $poll_parametrs['color_responding_of_the_answer'] ?>;}</style>
	<style id="style_color_responding_of_the_answer_hower_input">.main_div_of_the_poll_answer:hover .pracents_of_the_poll{background-color: <?php echo $poll_parametrs['color_responding_of_the_answer_hower'] ?>;}</style>
	<style id="style_color_of_the_answer_text_input">.main_div_of_the_poll_answer .poll_answer_title{color: <?php echo $poll_parametrs['color_of_the_answer_text'] ?>;}</style>
	<style id="style_color_of_the_answer_text_hower_input">.main_div_of_the_poll_answer:hover .poll_answer_title{color: <?php echo $poll_parametrs['color_of_the_answer_text_hower'] ?>;}</style>
	<style id="style_font_family_of_answer_text">.main_div_of_the_poll_answer .poll_answer_title{font-family: <?php echo $poll_parametrs['font_family_of_answer_text'] ?>;}</style>
	<style id="style_font_family_of_answer_text_hower">.main_div_of_the_poll_answer:hover .poll_answer_title{font-family: <?php echo $poll_parametrs['font_family_of_answer_text_hower'] ?>;}</style>
	<style id="style_font_style_of_answer_text_bold">.main_div_of_the_poll_answer .poll_answer_title{font-weight: <?php echo $poll_parametrs['font_style_of_answer_text_bold']?'bold':'normal' ?>;}</style>
	<style id="style_font_style_of_answer_text_italick">.main_div_of_the_poll_answer .poll_answer_title{font-style: <?php echo $poll_parametrs['font_style_of_answer_text_italick']?'italic':'normal' ?>;}</style>
	<style id="style_font_style_of_answer_text_bold_hower">.main_div_of_the_poll_answer:hover .poll_answer_title{font-weight: <?php echo $poll_parametrs['font_style_of_answer_text_bold_hower']?'bold':'normal' ?>;}</style>
	<style id="style_font_style_of_answer_text_italick_hower">.main_div_of_the_poll_answer:hover .poll_answer_title{font-style: <?php echo $poll_parametrs['font_style_of_answer_text_italick_hower']?'italic':'normal' ?>;}</style>
	<style id="style_poll_answer_border_width_input">.main_div_of_the_poll_answer{border-width:<?php echo $poll_parametrs['poll_answer_border_width'] ?>px;}</style>
	<style id="style_poll_answer_border_width_hower_input">.main_div_of_the_poll_answer:hover{border-width:<?php echo $poll_parametrs['poll_answer_border_width_hower'] ?>px;}</style>
	<style id="style_poll_answer_border_radius_input">.main_div_of_the_poll_answer{border-radius:<?php echo $poll_parametrs['poll_answer_border_radius'] ?>px;}</style>
	<style id="style_poll_answer_border_radius_hower_input">.main_div_of_the_poll_answer:hover{border-radius:<?php echo $poll_parametrs['poll_answer_border_radius_hower'] ?>px;}</style>
	<style id="style_poll_answer_border_type">.main_div_of_the_poll_answer{border-style:<?php echo $poll_parametrs['poll_answer_border_type'] ?>;}</style>
	<style id="style_poll_answer_border_type_hower">.main_div_of_the_poll_answer:hover{border-style:<?php echo $poll_parametrs['poll_answer_border_type_hower'] ?>;}</style>    
    <style id="style_color_of_the_border_input">.main_div_of_the_poll_answer{border-color:<?php echo $poll_parametrs['color_of_the_border'] ?>;}</style>
	<style id="style_color_of_the_border_input_hower">.main_div_of_the_poll_answer:hover{border-color:<?php echo $poll_parametrs['color_of_the_border_hower'] ?>;}</style>    
	<style id="style_poll_show_count_option">.main_div_of_the_poll_answer .poll_span_voted_count{display:<?php echo $poll_parametrs['poll_show_count_option']?'inline-block':'none' ?>}</style>
	<style id="style_poll_show_count_option_hower">.main_div_of_the_poll_answer:hover .poll_span_voted_count{display:<?php echo $poll_parametrs['poll_show_count_option_hower']?'inline-block':'none' ?>}</style>
	<style id="style_poll_count_fontsize_input">.main_div_of_the_poll_answer .poll_span_voted_count{font-size: <?php echo $poll_parametrs['poll_count_fontsize'] ?>px;}</style>
	<style id="style_poll_count_fontsize_hower_input">.main_div_of_the_poll_answer:hover .poll_span_voted_count{font-size: <?php echo $poll_parametrs['poll_count_fontsize_hower'] ?>px;}</style>
	<style id="style_poll_count_of_the_left_input">.main_div_of_the_poll_answer .poll_span_voted_count{right: <?php echo $poll_parametrs['poll_count_of_the_left'] ?>px;}</style>
	<style id="style_poll_count_of_the_left_hower_input">.main_div_of_the_poll_answer:hover .poll_span_voted_count{right: <?php echo $poll_parametrs['poll_count_of_the_left_hower'] ?>px;}</style>
	<style id="style_poll_count_of_the_top_input">.main_div_of_the_poll_answer .poll_span_voted_count{top: <?php echo $poll_parametrs['poll_count_of_the_top'] ?>px;}</style>
	<style id="style_poll_count_of_the_top_hower_input">.main_div_of_the_poll_answer:hover .poll_span_voted_count{top: <?php echo $poll_parametrs['poll_count_of_the_top_hower'] ?>px;}</style>
	<style id="style_color_of_the_count_input">.main_div_of_the_poll_answer .poll_span_voted_count{color: <?php echo $poll_parametrs['color_of_the_count_input'] ?>;}</style>
	<style id="style_color_of_the_count_hower_input">.main_div_of_the_poll_answer:hover .poll_span_voted_count{color: <?php echo $poll_parametrs['color_of_the_count_hower'] ?>;}</style>	
	<style id="style_font_family_of_count_text">.main_div_of_the_poll_answer .poll_span_voted_count{font-family: <?php echo $poll_parametrs['font_family_of_count_text'] ?>;}</style>
	<style id="style_font_family_of_count_text_hower">.main_div_of_the_poll_answer:hover .poll_span_voted_count{font-family: <?php echo $poll_parametrs['font_family_of_count_text_hower'] ?>;}</style>   
    <style id="style_font_style_of_count_text_bold">.main_div_of_the_poll_answer .poll_span_voted_count{font-weight: <?php echo $poll_parametrs['font_style_of_count_text_bold']?'bold':'normal' ?>;}</style>
	<style id="style_font_style_of_count_text_italick">.main_div_of_the_poll_answer .poll_span_voted_count{font-style: <?php echo $poll_parametrs['font_style_of_count_text_italick']?'italic':'normal' ?>;}</style>
	<style id="style_font_style_of_count_text_bold_hower">.main_div_of_the_poll_answer:hover .poll_span_voted_count{font-weight: <?php echo $poll_parametrs['font_style_of_count_text_bold_hower']?'bold':'normal' ?>;}</style>
	<style id="style_font_style_of_count_text_italick_hower">.main_div_of_the_poll_answer:hover .poll_span_voted_count{font-style: <?php echo $poll_parametrs['font_style_of_count_text_italick_hower']?'italic':'normal' ?>;}</style>
    <style>	
.main_div_of_the_poll_answer{	
	vertical-align: top;
    overflow: hidden;
	position: relative;
}
.main_div_of_the_poll_answer:hover{
	vertical-align: top;
	overflow:hidden;
	position: relative;
}
.pracents_of_the_poll {
	height: 100%;
	position: absolute;
}
.poll_answer_title{
	position: relative;
}
.label{
	float: left;
}
.poll_div_voted_count{
	float: right;
	position:relative;
}
.poll_span_voted_count{
	position:relative;
	display: none;
}
            </style>
            <script>
			var text_of_upgrate_version='If you want to use this feature upgrade to Polls Pro!';
			function submitbutton(pressbutton){						
				submitform( pressbutton );		
			}
			
			function submitform(pressbutton){
				document.getElementById('adminForm').action=document.getElementById('adminForm').action+"&task="+pressbutton;
				document.getElementById('adminForm').submit();
			}
			function convertHex(hex,opacity){
				hex = hex.replace('#','');
				console.log=hex;
				r = parseInt(hex.substring(0,2), 16);
				g = parseInt(hex.substring(2,4), 16);
				b = parseInt(hex.substring(4,6), 16);
			
				result = 'rgba('+r+','+g+','+b+','+opacity/100+')';
				return result;
			}
			/* opacity background*/
				jQuery(function() {
					jQuery( "#poll_background_opacity" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_background_opacity']?$poll_parametrs['poll_background_opacity']:'0'; ?>,
						min: 0,
						max: 100,
						slide: function( event, ui ) {							
							return false;
						}
						});
					jQuery( "#poll_background_opacity_input" ).val(jQuery( "#poll_background_opacity" ).slider( "value" ) );
					jQuery( "#poll_background_opacity_span" ).html(jQuery( "#poll_background_opacity" ).slider( "value" ) +'%');
				});
				/*  opacity background hower*/
				jQuery(function() {
					jQuery( "#poll_background_opacity_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_background_opacity_hower']?$poll_parametrs['poll_background_opacity_hower']:'0'; ?>,
						min: 1,
						max: 100,
						slide: function( event, ui ) {							
							return false;
						}
						});
					jQuery( "#poll_background_opacity_hower_input" ).val(jQuery( "#poll_background_opacity_hower" ).slider( "value" ) );
					jQuery( "#poll_background_opacity_hower_span" ).html(jQuery( "#poll_background_opacity_hower" ).slider( "value" ) +'%');
				});
				/* Width slider*/
				jQuery(function() {
					jQuery( "#poll_answer_width" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_width'] ?>,
						min: 0,
						max: 100,
						slide: function( event, ui ) {
							jQuery( "#poll_answer_width_input" ).val( ui.value);
							jQuery( "#poll_answer_width_span" ).html( ui.value+'%' );
							jQuery( "#poll_answer_width_hower_span" ).html( ui.value+'%' );
							
							jQuery( "#poll_answer_width_hower" ).slider( "value", ui.value );
							jQuery( "#poll_answer_width_hower_input" ).val( ui.value);
							
							jQuery( "#style_poll_answer_width_input" ).html(".main_div_of_the_poll_answer{width:"+ui.value+"%;}");
							jQuery( "#style_poll_answer_width_hower_input" ).html(".main_div_of_the_poll_answer:hover{width:"+ui.value+"%;}");
						}
						});
					jQuery( "#poll_answer_width_input" ).val(jQuery( "#poll_answer_width" ).slider( "value" ) );
					jQuery( "#poll_answer_width_span" ).html(jQuery( "#poll_answer_width" ).slider( "value" ) +'%');
				});
				/* Width slider hower*/
				jQuery(function() {
					jQuery( "#poll_answer_width_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_width_hower'] ?>,
						min: 1,
						max: 100,
						slide: function( event, ui ) {
							jQuery( "#poll_answer_width_hower_input" ).val( ui.value);
							jQuery( "#poll_answer_width_hower_span" ).html( ui.value+'%' );
							
							jQuery( "#style_poll_answer_width_hower_input" ).html(".main_div_of_the_poll_answer:hover{width:"+ui.value+"%;}");
						}
						});
					jQuery( "#poll_answer_width_hower_input" ).val(jQuery( "#poll_answer_width_hower" ).slider( "value" ) );
					jQuery( "#poll_answer_width_hower_span" ).html(jQuery( "#poll_answer_width_hower" ).slider( "value" ) +'%');
				});
				
				
				
				/* Heghth Slider*/
				jQuery(function() {
					jQuery( "#poll_answer_height" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_height'] ?>,
						min: 10,
						max: 200,
						slide: function( event, ui ) {
							jQuery( "#poll_answer_height_input" ).val( ui.value);
							jQuery( "#poll_answer_height_span" ).html( ui.value+'Px' );
							
							jQuery( "#poll_answer_height_hower" ).slider( "value", ui.value );
							jQuery( "#poll_answer_height_hower_input" ).val( ui.value);
							jQuery( "#poll_answer_height_hower_span" ).html( ui.value+'Px' );
							
							jQuery( "#style_poll_answer_height_input" ).html( ".main_div_of_the_poll_answer{height: "+ui.value+"px;}");
							jQuery( "#style_poll_answer_height_hower_input" ).html(".main_div_of_the_poll_answer:hover{height: "+ui.value+"px;}");
						}
						});
					jQuery( "#poll_answer_height_input" ).val(jQuery( "#poll_answer_height" ).slider( "value" ) );
					jQuery( "#poll_answer_height_span" ).html(jQuery( "#poll_answer_height" ).slider( "value" ) +'Px');
				});
				/* Heghth Slider Hower*/
				jQuery(function() {
					jQuery( "#poll_answer_height_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_height_hower'] ?>,
						min: 10,
						max: 200,
						slide: function( event, ui ) {
							jQuery( "#poll_answer_height_hower_input" ).val( ui.value);
							jQuery( "#poll_answer_height_hower_span" ).html( ui.value+'Px' );
							
							jQuery( "#style_poll_answer_height_hower_input" ).html(".main_div_of_the_poll_answer:hover{height: "+ui.value+"px;}");
						}
						});
					jQuery( "#poll_answer_height_hower_input" ).val(jQuery( "#poll_answer_height_hower" ).slider( "value" ) );
					jQuery( "#poll_answer_height_hower_span" ).html(jQuery( "#poll_answer_height_hower" ).slider( "value" ) +'Px');
				});
				
				
				/* Answer inner Distance*/
				jQuery(function() {
					jQuery( "#poll_answer_inner_distance" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_inner_distance'] ?>,
						min: 0,
						max: 100,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_answer_inner_distance_input" ).val(jQuery( "#poll_answer_inner_distance" ).slider( "value" ) );
					jQuery( "#poll_answer_inner_distance_span" ).html(jQuery( "#poll_answer_inner_distance" ).slider( "value" ) +'Px');
				});
				
				
				/* Answer Font Size Slider*/
				jQuery(function() {
					jQuery( "#poll_answer_fontsize" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_fontsize'] ?>,
						min: 5,
						max: 80,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_answer_fontsize_input" ).val(jQuery( "#poll_answer_fontsize" ).slider( "value" ) );
					jQuery( "#poll_answer_fontsize_span" ).html(jQuery( "#poll_answer_fontsize" ).slider( "value" ) +'Px');
				});
				/* Answer Font Size Slider Hower*/
				jQuery(function() {
					jQuery( "#poll_answer_fontsize_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_fontsize_hower'] ?>,
						min: 5,
						max: 80,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_answer_fontsize_hower_input" ).val(jQuery( "#poll_answer_fontsize_hower" ).slider( "value" ) );
					jQuery( "#poll_answer_fontsize_hower_span" ).html(jQuery( "#poll_answer_fontsize_hower" ).slider( "value" ) +'Px');
				});
				
				
				/* Position Answer OF the Top*/
				jQuery(function() {
					jQuery( "#poll_answer_of_the_top" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_of_the_top'] ?>,
						min: 0,
						max: 80,
						slide: function( event, ui ) {
							jQuery( "#poll_answer_of_the_top_input" ).val( ui.value);
							jQuery( "#poll_answer_of_the_top_span" ).html( ui.value+'Px' );
							
							jQuery( "#poll_answer_of_the_top_hower" ).slider( "value", ui.value );
							jQuery( "#poll_answer_of_the_top_hower_input" ).val( ui.value);
							jQuery( "#poll_answer_of_the_top_hower_span" ).html( ui.value+'Px' );
							
							jQuery( "#style_poll_answer_of_the_top_input" ).html(".main_div_of_the_poll_answer .poll_answer_title{ top:"+ ui.value+"px; }")
							jQuery( "#style_poll_answer_of_the_top_hower_input" ).html(".main_div_of_the_poll_answer:hover .poll_answer_title{ top: "+ui.value+"px; }")
						}
						});
					jQuery( "#poll_answer_of_the_top_input" ).val(jQuery( "#poll_answer_of_the_top" ).slider( "value" ) );
					jQuery( "#poll_answer_of_the_top_span" ).html(jQuery( "#poll_answer_of_the_top" ).slider( "value" ) +'Px');
				});
				/* Position Answer OF the Top Hower*/
				jQuery(function() {
					jQuery( "#poll_answer_of_the_top_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_of_the_top_hower'] ?>,
						min: 0,
						max: 80,
						slide: function( event, ui ) {
							jQuery( "#poll_answer_of_the_top_hower_input" ).val( ui.value);
							jQuery( "#poll_answer_of_the_top_hower_span" ).html( ui.value+'Px' );
							
							jQuery( "#style_poll_answer_of_the_top_hower_input" ).html(".main_div_of_the_poll_answer:hover .poll_answer_title{ top: "+ui.value+"px; }")
						}
						});
					jQuery( "#poll_answer_of_the_top_hower_input" ).val(jQuery( "#poll_answer_of_the_top_hower" ).slider( "value" ) );
					jQuery( "#poll_answer_of_the_top_hower_span" ).html(jQuery( "#poll_answer_of_the_top_hower" ).slider( "value" ) +'Px');
				});
				
				/* Position Answer OF the Left*/
				jQuery(function() {
					jQuery( "#poll_answer_of_the_left" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_of_the_left'] ?>,
						min: 0,
						max: 80,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_answer_of_the_left_input" ).val(jQuery( "#poll_answer_of_the_left" ).slider( "value" ) );
					jQuery( "#poll_answer_of_the_left_span" ).html(jQuery( "#poll_answer_of_the_left" ).slider( "value" ) +'Px');
				});
				/* Position Answer OF the Left Hower*/
				jQuery(function() {
					jQuery( "#poll_answer_of_the_left_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_of_the_left_hower'] ?>,
						min: 0,
						max: 80,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_answer_of_the_left_hower_input" ).val(jQuery( "#poll_answer_of_the_left_hower" ).slider( "value" ) );
					jQuery( "#poll_answer_of_the_left_hower_span" ).html(jQuery( "#poll_answer_of_the_left_hower" ).slider( "value" ) +'Px');
				});
				
				
				
				/* Border width of answer*/
				jQuery(function() {
					jQuery( "#poll_answer_border_width" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_border_width'] ?>,
						min: 0,
						max: 30,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_answer_border_width_input" ).val(jQuery( "#poll_answer_border_width" ).slider( "value" ) );
					jQuery( "#poll_answer_border_width_span" ).html(jQuery( "#poll_answer_border_width" ).slider( "value" ) +'Px');
				});
				/* Border width of answer Hower*/
				jQuery(function() {
					jQuery( "#poll_answer_border_width_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_border_width_hower'] ?>,
						min: 0,
						max: 30,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_answer_border_width_hower_input" ).val(jQuery( "#poll_answer_border_width_hower" ).slider( "value" ) );
					jQuery( "#poll_answer_border_width_hower_span" ).html(jQuery( "#poll_answer_border_width_hower" ).slider( "value" ) +'Px');
				});
                    
					
				/* Border radius of answer*/
				jQuery(function() {
					jQuery( "#poll_answer_border_radius" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_border_radius'] ?>,
						min: 0,
						max: 100,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_answer_border_radius_input" ).val(jQuery( "#poll_answer_border_radius" ).slider( "value" ) );
					jQuery( "#poll_answer_border_radius_span" ).html(jQuery( "#poll_answer_border_radius" ).slider( "value" ) +'Px');
				});
				/* Border radius of answer hower*/
				jQuery(function() {
					jQuery( "#poll_answer_border_radius_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_answer_border_radius_hower'] ?>,
						min: 0,
						max: 100,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_answer_border_radius_hower_input" ).val(jQuery( "#poll_answer_border_radius_hower" ).slider( "value" ) );
					jQuery( "#poll_answer_border_radius_hower_span" ).html(jQuery( "#poll_answer_border_radius_hower" ).slider( "value" ) +'Px');
				});
				
				/* Count Font Size*/
				jQuery(function() {
					jQuery( "#poll_count_fontsize" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_count_fontsize'] ?>,
						min: 0,
						max: 50,
						slide: function( event, ui ) {
							return false;

						}
						});
					jQuery( "#poll_count_fontsize_input" ).val(jQuery( "#poll_count_fontsize" ).slider( "value" ) );
					jQuery( "#poll_count_fontsize_span" ).html(jQuery( "#poll_count_fontsize" ).slider( "value" ) +'Px');
				});
				/* Count Font Size hower*/
				jQuery(function() {
					jQuery( "#poll_count_fontsize_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_count_fontsize_hower'] ?>,
						min: 0,
						max: 50,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_count_fontsize_hower_input" ).val(jQuery( "#poll_count_fontsize_hower" ).slider( "value" ) );
					jQuery( "#poll_count_fontsize_hower_span" ).html(jQuery( "#poll_count_fontsize_hower" ).slider( "value" ) +'Px');
				});
				
				
				
				/* count position Left Right*/
				jQuery(function() {
					jQuery( "#poll_count_of_the_left" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_count_of_the_left'] ?>,
						min: 0,
						max: 150,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_count_of_the_left_input" ).val(jQuery( "#poll_count_of_the_left" ).slider( "value" ) );
					jQuery( "#poll_count_of_the_left_span" ).html(jQuery( "#poll_count_of_the_left" ).slider( "value" ) +'Px');
				});
				/* count position Left Right*/
				jQuery(function() {
					jQuery( "#poll_count_of_the_left_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_count_of_the_left_hower'] ?>,
						min: 0,
						max: 150,
						slide: function( event, ui ) {
							return false;
						}
						});
					jQuery( "#poll_count_of_the_left_hower_input" ).val(jQuery( "#poll_count_of_the_left_hower" ).slider( "value" ) );
					jQuery( "#poll_count_of_the_left_hower_span" ).html(jQuery( "#poll_count_of_the_left_hower" ).slider( "value" ) +'Px');
				});
				
				
				
				/* count position top*/
				jQuery(function() {
					jQuery( "#poll_count_of_the_top" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_count_of_the_top'] ?>,
						min: 0,
						max: 150,
						slide: function( event, ui ) {
							jQuery( "#poll_count_of_the_top_input" ).val( ui.value);
							jQuery( "#poll_count_of_the_top_span" ).html( ui.value+'Px' );
							
							jQuery( "#poll_count_of_the_top_hower" ).slider( "value", ui.value );
							jQuery( "#poll_count_of_the_top_hower_input" ).val( ui.value);
							jQuery( "#poll_count_of_the_top_hower_span" ).html( ui.value+'Px' );
							
							jQuery( "#style_poll_count_of_the_top_input" ).html(".main_div_of_the_poll_answer .poll_span_voted_count{top:"+ui.value+"px;}");
							jQuery( "#style_poll_count_of_the_top_hower_input" ).html(".main_div_of_the_poll_answer:hover .poll_span_voted_count{top:"+ui.value+"px;}");

						}
						});
					jQuery( "#poll_count_of_the_top_input" ).val(jQuery( "#poll_count_of_the_top" ).slider( "value" ) );
					jQuery( "#poll_count_of_the_top_span" ).html(jQuery( "#poll_count_of_the_top" ).slider( "value" ) +'Px');
				});
				/* count position top*/
				jQuery(function() {
					jQuery( "#poll_count_of_the_top_hower" ).slider({
						range: "min",
						value: <?php echo $poll_parametrs['poll_count_of_the_top_hower'] ?>,
						min: 0,
						max: 150,
						slide: function( event, ui ) {
							jQuery( "#poll_count_of_the_top_hower_input" ).val( ui.value);
							jQuery( "#poll_count_of_the_top_hower_span" ).html( ui.value+'Px' );
							
							jQuery( "#style_poll_count_of_the_top_hower_input" ).html(".main_div_of_the_poll_answer:hover .poll_span_voted_count{top:"+ui.value+"px;}");
						}
						});
					jQuery( "#poll_count_of_the_top_hower_input" ).val(jQuery( "#poll_count_of_the_top_hower" ).slider( "value" ) );
					jQuery( "#poll_count_of_the_top_hower_span" ).html(jQuery( "#poll_count_of_the_top_hower" ).slider( "value" ) +'Px');
				});




					
					
				jQuery(document).ready(function(e) {
				
						
						/*font famili of the answer*/
						jQuery('#font_family_of_answer_text').click(function(){
							alert(text_of_upgrate_version);
							return false;
						});
						jQuery('#font_family_of_answer_text_hower').click(function(){
							alert(text_of_upgrate_version);
							return false;
						})
						
						/*Answer bold or no*/
						jQuery('#font_style_of_answer_text_bold').click(function(){
							alert(text_of_upgrate_version);
							return false;
							
						})
						jQuery('#font_style_of_answer_text_bold_hower').click(function(){
							alert(text_of_upgrate_version);
							return false;
						})
						
						/*Answer italick*/
						jQuery('#font_style_of_answer_text_italick').click(function(){
							alert(text_of_upgrate_version);
							return false;
							
						})
						jQuery('#font_style_of_answer_text_italick_hower').click(function(){
							alert(text_of_upgrate_version);
							return false;
						})
						
						
						/*border Style*/
						jQuery('#poll_answer_border_type').click(function(){
							alert(text_of_upgrate_version);
							return false;
						});
						jQuery('#poll_answer_border_type_hower').click(function(){
							alert(text_of_upgrate_version);
							return false;
						})
						
						/*Count Option Show*/
						jQuery('input[name="parametrs[poll_show_count_option]"]').click(function(){
							alert(text_of_upgrate_version);
							return false;
						})
						jQuery('input[name="parametrs[poll_show_count_option_hower]"]').click(function(){
							alert(text_of_upgrate_version);
							return false;
						})
						
						/*Font Famely Of count*/
						jQuery('#font_family_of_count_text').click(function(){
							alert(text_of_upgrate_version);
							return false;
						});
						jQuery('#font_family_of_count_text_hower').click(function(){							
							alert(text_of_upgrate_version);
							return false;
						})
						
						/*font style count bold*/
						jQuery('#font_style_of_count_text_bold').click(function(){
							alert(text_of_upgrate_version);
							return false;
							
						})
						jQuery('#font_style_of_count_text_bold_hower').click(function(){
							alert(text_of_upgrate_version);
							return false;
						})
						
						/*Font style count italick*/
						jQuery('#font_style_of_count_text_italick').click(function(){
							alert(text_of_upgrate_version);
							return false;
							
						})
						jQuery('#font_style_of_count_text_italick_hower').click(function(){
							alert(text_of_upgrate_version);
							return false;
						})
						
				});	
				
				
			jQuery(document).ready(function(e) {
                jQuery('.disabled_for_pro').click(function(){
					alert(text_of_upgrate_version);
					return false;
				});
				jQuery('.pro_paramets .slider_parametrs,.pro_paramets .ui-slider-handle').mousedown(function(){
					alert(text_of_upgrate_version);
					return false;
				})
            });            
            </script>
             <form action="admin.php?page=Polls-design<?php if($id) echo '&id='.$id; ?>" method="post" name="adminForm" class="top_description_table" id="adminForm">
            <div class="conteiner">
                <div class="header">
                    <span><h2 class="poll_theme_title">Add Theme</h2></span>
                    <div class="header_action_buttons">
                        <span><input type="button" onclick="submitbutton('save_poll')" value="Save" class="button-primary action"> </span> 
                        <span><input type="button" onclick="submitbutton('update_poll')" value="Apply" class="button-primary action"> </span> 
                        <span><input type="button" onclick="window.location.href='admin.php?page=Polls-design'" value="Cancel" class="button-secondary action"> </span> 
                    </div>
                </div>
                <div class="option_panel">            
                    <div class="parametr_name"></div>
                    <div class="all_options_panel">
                        <input type="text" class="poll_name" name="poll_name" placeholder="Enter name here" value="<?php echo $poll_name ?>" >
                        <table class="all_options_panel_table wp-list-table widefat fixed posts">
                            <thead>
                                <tr>
                                    <th>
                                        <span> Options description </span>
                                    </th>
                                    <th>
                                        <span> Options value </span>
                                    </th>
                                    <th>
                                        <span> Options value on hover </span>
                                    </th>
                                    <th>
                                        <span> Preview </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        Poll Width <span title="Select the Poll width. Select 100% to set full with option for Poll. Then Select the Poll width when hovering." class="desription_class">?</span>
                                    </td>
                                     <td>
                                                                    
                                        <input type="text" name="parametrs[poll_answer_width]" id="poll_answer_width_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_width"></div><span id="poll_answer_width_span" class="slider_span"></span>
                                    </td>
                                    <td>
                                    	 <input type="text" name="parametrs[poll_answer_width_hower]" id="poll_answer_width_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_width_hower"></div><span id="poll_answer_width_hower_span" class="slider_span"></span>
                                    </td>
                                     <td rowspan="10000"  style="background-color:#FFFFFF">
                                    	<div class="main_div_of_the_poll_answer" id="main_of_the_answer_1" title="Answer1">
                                            <div class="pracents_of_the_poll colorwell" id="pracent_1" style="width: 25%;"></div>
                                            <div class="label"><span id="poll_answer_title_1" class="poll_answer_title">Answer 1</span></div>
                                            <div></div>
                                            <div class="poll_div_voted_count"><span id="poll_span_voted_count_1" class="poll_span_voted_count">30 Vote</span></div>
                                        </div>
                                        <div class="main_div_of_the_poll_answer" id="main_of_the_answer_1" title="Answer1">
                                            <div class="pracents_of_the_poll colorwell" id="pracent_1" style="width: 15%;"></div>
                                            <div class="label"><span id="poll_answer_title_1" class="poll_answer_title">Answer 2</span></div>
                                            <div></div>
                                            <div class="poll_div_voted_count"><span id="poll_span_voted_count_1" class="poll_span_voted_count">25 Vote</span></div>
                                        </div>
                                        <div class="main_div_of_the_poll_answer" id="main_of_the_answer_1" title="Answer1">
                                            <div class="pracents_of_the_poll colorwell" id="pracent_1" style="width: 50%;"></div>
                                            <div class="label"><span id="poll_answer_title_1" class="poll_answer_title">Answer 3</span></div>
                                            <div></div>
                                            <div class="poll_div_voted_count"><span id="poll_span_voted_count_1" class="poll_span_voted_count">50 Vote</span></div>
                                        </div>
                                        <div class="main_div_of_the_poll_answer" id="main_of_the_answer_1" title="Answer1">
                                            <div class="pracents_of_the_poll colorwell" id="pracent_1" style="width: 10%;"></div>
                                            <div class="label"><span id="poll_answer_title_1" class="poll_answer_title">Answer 4</span></div>
                                            <div></div>
                                            <div class="poll_div_voted_count"><span id="poll_span_voted_count_1" class="poll_span_voted_count">10 Vote</span></div>
                                        </div>
                                   		<a target="_blank" class="update_pro" href="http://wpdevart.com/wordpress-polls-plugin/"><span style="color: rgba(10, 154, 62, 1); font-weight: bold; font-size: 21px;">Upgrade to Pro Version</span></a>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>
                                    	Poll height <span title="Select the Poll height. Then select the Poll height when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                        <input type="text" name="parametrs[poll_answer_height]" id="poll_answer_height_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_height"></div><span id="poll_answer_height_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                        <input type="text" name="parametrs[poll_answer_height_hower]" id="poll_answer_height_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_height_hower"></div><span id="poll_answer_height_hower_span" class="slider_span"></span>
                                    </td>
                                </tr>
                            <tr class="pro_paramets">
                                <td>
                                    Poll background color <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll background color. Then select the Poll background color when hovering." class="desription_class">?</span>
                                </td>
                                <td>
                                    <div class='disabled_for_pro'>
                                        <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(255, 255, 255);"></a></div>
                                    </div>
                                </td>
                                <td>
                                    <div class='disabled_for_pro'>
                                        <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(255, 255, 255);"></a></div>
                                    </div>
                                </td>
                            </tr>
                             <tr class="pro_paramets">
                                   <td>
                                        Poll background opacity  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll background opacity. Then select the Poll background opacity when hovering." class="desription_class">?</span>
                                    </td>
                                     <td>
                                                                    
                                        <input type="text" name="parametrs[poll_background_opacity]" id="poll_background_opacity_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_background_opacity"></div><span id="poll_background_opacity_span" class="slider_span"></span>
                                    </td>
                                    <td>
                                    	 <input type="text" name="parametrs[poll_background_opacity_hower]" id="poll_background_opacity_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_background_opacity_hower"></div><span id="poll_background_opacity_hower_span" class="slider_span"></span>
                                    </td>
                                </tr>
                                
                                
                                <tr class="pro_paramets">
                                    <td>
                                   	 	Distance among Poll answers  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select distance among Poll answers." class="desription_class">?</span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_answer_inner_distance]" id="poll_answer_inner_distance_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_inner_distance"></div><span id="poll_answer_inner_distance_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                    	<span class="not_paramter">None</span>
                                    </td>
                                </tr>
                                
                                <tr class="pro_paramets">
                                    <td>
                                   	 	Answer font size  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll answer font size. Then select the Poll answer font size when hovering." class="desription_class">?</span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_answer_fontsize]" id="poll_answer_fontsize_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_fontsize"></div><span id="poll_answer_fontsize_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                    	<input type="text" name="parametrs[poll_answer_fontsize_hower]" id="poll_answer_fontsize_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_fontsize_hower"></div><span id="poll_answer_fontsize_hower_span" class="slider_span"></span>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>
                                   	 	Answer distance from top  <span title="Select the Poll answer distance from top. Then select the Poll answer distance from top when hovering." class="desription_class">?</span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_answer_of_the_top]" id="poll_answer_of_the_top_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_of_the_top"></div><span id="poll_answer_of_the_top_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                    	<input type="text" name="parametrs[poll_answer_of_the_top_hower]" id="poll_answer_of_the_top_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_of_the_top_hower"></div><span id="poll_answer_of_the_top_hower_span" class="slider_span"></span>
                                    </td>
                                </tr>
                                
                                <tr class="pro_paramets">
                                    <td>
                                   	 	Answer distance from left  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll answer distance from left. Then select the Poll answer distance from left when hovering. " class="desription_class">?</span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_answer_of_the_left]" id="poll_answer_of_the_left_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_of_the_left"></div><span id="poll_answer_of_the_left_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                    	<input type="text" name="parametrs[poll_answer_of_the_left_hower]" id="poll_answer_of_the_left_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_of_the_left_hower"></div><span id="poll_answer_of_the_left_hower_span" class="slider_span"></span>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>
                                   	 	Background color corresponding counts  <span class="pro_subtitle_span">Pro feature!</span>  <span title="Select the Poll background color corresponding counts. Then select the Poll background color corresponding counts when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                        <div class='disabled_for_pro'>
                                            <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(175, 175, 175);"></a></div>
                                        </div>
                                    </td>
                                     <td>
                                        <div class='disabled_for_pro'>
                                            <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(175, 175, 175);"></a></div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>
                                   	 	Answer color  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll answer color.  Then select the Poll answer color when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                        <div class='disabled_for_pro'>
                                            <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(12, 1, 1);"></a></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class='disabled_for_pro'>
                                            <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(12, 1, 1);"></a></div>
                                        </div>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>
                                   	 	Answer font family  <span class="pro_subtitle_span">Pro feature!</span>  <span title="Choose the Poll answer font family. Then select the Poll answer font family when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                   <?php 
                                   		 $this->select_font_with_label('font_family_of_answer_text',$poll_parametrs['font_family_of_answer_text']); ?> 
                                    </td>
                                    <td>
                                    	<?php $this->select_font_with_label('font_family_of_answer_text_hower',$poll_parametrs['font_family_of_answer_text_hower'],'font_family_of_answer_text'); ?>
                                      
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>
                                   	 	Answer font style  <span class="pro_subtitle_span">Pro feature!</span>  <span title="Select the Poll answer font style. Then select the Poll answer font style when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                   		<label for="font_style_of_answer_text">Bold </label><input <?php if(isset($poll_parametrs['font_style_of_answer_text_bold'] )) checked($poll_parametrs['font_style_of_answer_text_bold'],'on');?> type="checkbox" name="parametrs[font_style_of_answer_text_bold]" id="font_style_of_answer_text_bold"> 
                                        <label for="font_style_of_answer_text_italick">Italick </label><input <?php if(isset($poll_parametrs['font_style_of_answer_text_italick'] )) checked($poll_parametrs['font_style_of_answer_text_italick'],'on');?> type="checkbox" name="parametrs[font_style_of_answer_text_italick]" id="font_style_of_answer_text_italick"> 
                                    </td>
                                    <td>
                                   		<label for="font_style_of_answer_text_bold_hower">Bold </label><input <?php if(isset($poll_parametrs['font_style_of_answer_text_bold_hower'] )) checked($poll_parametrs['font_style_of_answer_text_bold_hower'],'on');?> type="checkbox" name="parametrs[font_style_of_answer_text_bold_hower]" id="font_style_of_answer_text_bold_hower"> 
                                        <label for="font_style_of_answer_text_italick_hower">Italick </label><input <?php if(isset($poll_parametrs['font_style_of_answer_text_italick_hower'] )) checked($poll_parametrs['font_style_of_answer_text_italick_hower'],'on');?> type="checkbox" name="parametrs[font_style_of_answer_text_italick_hower]" id="font_style_of_answer_text_italick_hower">                                       
                                    </td>
                                </tr>
                                
                                <tr class="pro_paramets">
                                    <td>
                                   	 	Poll border width  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll border width. Then select the Poll border width when hovering" class="desription_class">?</span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_answer_border_width]" id="poll_answer_border_width_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_border_width"></div><span id="poll_answer_border_width_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                    	<input type="text" name="parametrs[poll_answer_border_width_hower]" id="poll_answer_border_width_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_border_width_hower"></div><span id="poll_answer_border_width_hower_span" class="slider_span"></span>
                                    </td>
                                </tr>
                                
                                 <tr class="pro_paramets">
                                    <td>
                                   	 	Poll border Radius  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll border Radius. Then select the Poll border Radius when hovering." class="desription_class">?</span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_answer_border_radius]" id="poll_answer_border_radius_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_border_radius"></div><span id="poll_answer_border_radius_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_answer_border_radius_hower]" id="poll_answer_border_radius_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_answer_border_radius_hower"></div><span id="poll_answer_border_radius_hower_span" class="slider_span"></span>
                                    </td>
                                    
                                </tr>
                                
                                <tr>
                                    <td>
                                   	 	Poll border type   <span class="pro_subtitle_span">Pro feature!</span> <span title="Choose the Poll Border type. Then select the Poll Border type when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                   		<?php $this->select_border_with_label('poll_answer_border_type',$poll_parametrs['poll_answer_border_type']); ?>
                                    </td>
                                    <td>
                                    	<?php $this->select_border_with_label('poll_answer_border_type_hower',$poll_parametrs['poll_answer_border_type_hower']); ?>
                                      
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                   	 	Poll border color  <span class="pro_subtitle_span">Pro feature!</span>  <span title="Select the Poll border color. Then select the Poll border color when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                        <div class='disabled_for_pro'>
                                            <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(59, 89, 152);"></a></div>
                                        </div>
                                    </td>
                                     <td>
                                        <div class='disabled_for_pro'>
                                            <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(59, 89, 152);"></a></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                   	 	Show count option  <span class="pro_subtitle_span">Pro feature!</span>  <span title="If you want to show count options then select yes. Then select the same option for hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                   		<label for="poll_show_count_option_on">Yes </label><input <?php if(isset($poll_parametrs['poll_show_count_option'] )) checked($poll_parametrs['poll_show_count_option'],'1');?> type="radio" value="1" name="parametrs[poll_show_count_option]" id="poll_show_count_option_on"> 
                                        <label for="poll_show_count_option_off">No </label><input <?php if(isset($poll_parametrs['poll_show_count_option'] )) checked($poll_parametrs['poll_show_count_option'],'0');?> type="radio" value="0" name="parametrs[poll_show_count_option]" id="poll_show_count_option_off"> 
                                    </td>
                                    <td>
                                   		<label for="poll_show_count_option_hower_on">Yes </label><input <?php if(isset($poll_parametrs['poll_show_count_option_hower'] )) checked($poll_parametrs['poll_show_count_option_hower'],'1');?> type="radio" value="1" name="parametrs[poll_show_count_option_hower]" id="poll_show_count_option_hower_on"> 
                                        <label for="poll_show_count_option_hower_off">No </label><input <?php if(isset($poll_parametrs['poll_show_count_option_hower'] )) checked($poll_parametrs['poll_show_count_option_hower'],'0');?> type="radio" value="0" name="parametrs[poll_show_count_option_hower]" id="poll_show_count_option_hower_off">                                       
                                    </td>
                                </tr>
                                
                                <tr class="pro_paramets">
                                    <td>
                                   	 	Count font size  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll count font size. Then select the Poll count font size when hovering." class="desription_class">?</span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_count_fontsize]" id="poll_count_fontsize_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_count_fontsize"></div><span id="poll_count_fontsize_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                    	<input type="text" name="parametrs[poll_count_fontsize_hower]" id="poll_count_fontsize_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_count_fontsize_hower"></div><span id="poll_count_fontsize_hower_span" class="slider_span"></span>
                                    </td>
                                </tr>
                                
                                <tr class="pro_paramets">
                                    <td>
                                   	 	Count distance from left  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll count distance from left. Then select the Poll count distance from left when hovering." class="desription_class">?</span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_count_of_the_left]" id="poll_count_of_the_left_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_count_of_the_left"></div><span id="poll_count_of_the_left_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_count_of_the_left_hower]" id="poll_count_of_the_left_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_count_of_the_left_hower"></div><span id="poll_count_of_the_left_hower_span" class="slider_span"></span>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>
                                   	 	Count distance from top <span title="Select the Poll count distance from top. Then select the Poll count distance from top when hovering." class="desription_class">?</span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_count_of_the_top]" id="poll_count_of_the_top_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_count_of_the_top"></div><span id="poll_count_of_the_top_span" class="slider_span"></span>
                                    </td>
                                     <td>
                                   		<input type="text" name="parametrs[poll_count_of_the_top_hower]" id="poll_count_of_the_top_hower_input" class="slider_input">
                                        <div class="slider_parametrs" id="poll_count_of_the_top_hower"></div><span id="poll_count_of_the_top_hower_span" class="slider_span"></span>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td>
                                   	 	Count text color  <span class="pro_subtitle_span">Pro feature!</span>  <span title="Select the Poll count text color. Then select the Poll count text color when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                        <div class='disabled_for_pro'>
                                            <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(135, 135, 135);"></a></div>
                                        </div>
                                    </td>
                                     <td>
                                        <div class='disabled_for_pro'>
                                            <div class="wp-picker-container"><a tabindex="0" class="wp-color-result" title="Select Color" data-current="Current Color" style="background-color: rgb(0, 0, 0);"></a></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                   	 	Count text font family  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll count text font family. Then select the Poll count text font family when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                   		<?php $this->select_font_with_label('font_family_of_count_text',$poll_parametrs['font_family_of_count_text']); ?>
                                    </td>
                                    <td>
                                    	<?php $this->select_font_with_label('font_family_of_count_text_hower',$poll_parametrs['font_family_of_count_text_hower']); ?>
                                      
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                   	 	Count text font style  <span class="pro_subtitle_span">Pro feature!</span> <span title="Select the Poll count text font style. Then select the Poll count text font style when hovering." class="desription_class">?</span>
                                    </td>
                                    <td>
                                   		<label for="font_style_of_count_text">Bold </label><input <?php if(isset($poll_parametrs['font_style_of_count_text_bold'] )) checked($poll_parametrs['font_style_of_count_text_bold'],'on');?> type="checkbox" name="parametrs[font_style_of_count_text_bold]" id="font_style_of_count_text_bold"> 
                                        <label for="font_style_of_count_text_italick">Italick </label><input <?php if(isset($poll_parametrs['font_style_of_count_text_italick'] )) checked($poll_parametrs['font_style_of_count_text_italick'],'on');?> type="checkbox" name="parametrs[font_style_of_count_text_italick]" id="font_style_of_count_text_italick"> 
                                    </td>
                                    <td>
                                   		<label for="font_style_of_count_text_hower">Bold </label><input <?php if(isset($poll_parametrs['font_style_of_count_text_bold_hower'] )) checked($poll_parametrs['font_style_of_count_text_bold_hower'],'on');?> type="checkbox" name="parametrs[font_style_of_count_text_bold_hower]" id="font_style_of_count_text_bold_hower"> 
                                        <label for="font_style_of_count_text_italick_hower">Italick </label><input <?php if(isset($poll_parametrs['font_style_of_count_text_italick_hower'] )) checked($poll_parametrs['font_style_of_count_text_italick_hower'],'on');?> type="checkbox" name="parametrs[font_style_of_count_text_italick_hower]" id="font_style_of_count_text_italick_hower">                                       
                                    </td>
                                </tr>
                                
                            </tbody>
                        </table>  
                    </div>
                </div>
            </div>
		</form>
		<?php

		 
	}
	private function border_types(){
		$border_type[ 'dotted' ] = 'dotted';
		$border_type[ 'dashed' ] = 'dashed';
		$border_type[ 'solid' ] = 'solid';
		$border_type[ 'double' ] = 'double';
		$border_type[ 'groove' ] = 'groove';
		$border_type[ 'ridge' ] = 'ridge';
		$border_type[ 'inset' ] = 'inset';	
		$border_type[ 'outset' ] = 'outset';
		return $border_type;
	}
	private function fonts_options(){
		  $font_choices[ 'Arial,Helvetica Neue,Helvetica,sans-serif' ] = 'Arial *';
		  $font_choices[ 'Arial Black,Arial Bold,Arial,sans-serif' ] = 'Arial Black *';
		  $font_choices[ 'Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif' ] = 'Arial Narrow *';
		  $font_choices[ 'Courier,Verdana,sans-serif' ] = 'Courier *';
		  $font_choices[ 'Georgia,Times New Roman,Times,serif' ] = 'Georgia *';
		  $font_choices[ 'Times New Roman,Times,Georgia,serif' ] = 'Times New Roman *';
		  $font_choices[ 'Trebuchet MS,Lucida Grande,Lucida Sans Unicode,Lucida Sans,Arial,sans-serif' ] = 'Trebuchet MS *';
		  $font_choices[ 'Verdana,sans-serif' ] = 'Verdana *';
		  $font_choices[ 'American Typewriter,Georgia,serif' ] = 'American Typewriter';
		  $font_choices[ 'Andale Mono,Consolas,Monaco,Courier,Courier New,Verdana,sans-serif' ] = 'Andale Mono';
		  $font_choices[ 'Baskerville,Times New Roman,Times,serif' ] = 'Baskerville';
		  $font_choices[ 'Bookman Old Style,Georgia,Times New Roman,Times,serif' ] = 'Bookman Old Style';
		  $font_choices[ 'Calibri,Helvetica Neue,Helvetica,Arial,Verdana,sans-serif' ] = 'Calibri';
		  $font_choices[ 'Cambria,Georgia,Times New Roman,Times,serif' ] = 'Cambria';
		  $font_choices[ 'Candara,Verdana,sans-serif' ] = 'Candara';
		  $font_choices[ 'Century Gothic,Apple Gothic,Verdana,sans-serif' ] = 'Century Gothic';
		  $font_choices[ 'Century Schoolbook,Georgia,Times New Roman,Times,serif' ] = 'Century Schoolbook';
		  $font_choices[ 'Consolas,Andale Mono,Monaco,Courier,Courier New,Verdana,sans-serif' ] = 'Consolas';
		  $font_choices[ 'Constantia,Georgia,Times New Roman,Times,serif' ] = 'Constantia';
		  $font_choices[ 'Corbel,Lucida Grande,Lucida Sans Unicode,Arial,sans-serif' ] = 'Corbel';
		  $font_choices[ 'Franklin Gothic Medium,Arial,sans-serif' ] = 'Franklin Gothic Medium';
		  $font_choices[ 'Garamond,Hoefler Text,Times New Roman,Times,serif' ] = 'Garamond';
		  $font_choices[ 'Gill Sans MT,Gill Sans,Calibri,Trebuchet MS,sans-serif' ] = 'Gill Sans MT';
		  $font_choices[ 'Helvetica Neue,Helvetica,Arial,sans-serif' ] = 'Helvetica Neue';
		  $font_choices[ 'Hoefler Text,Garamond,Times New Roman,Times,sans-serif' ] = 'Hoefler Text';
		  $font_choices[ 'Lucida Bright,Cambria,Georgia,Times New Roman,Times,serif' ] = 'Lucida Bright';
		  $font_choices[ 'Lucida Grande,Lucida Sans,Lucida Sans Unicode,sans-serif' ] = 'Lucida Grande';
		  $font_choices[ 'Palatino Linotype,Palatino,Georgia,Times New Roman,Times,serif' ] = 'Palatino Linotype';
		  $font_choices[ 'Tahoma,Geneva,Verdana,sans-serif' ] = 'Tahoma';
		  $font_choices[ 'Rockwell, Arial Black, Arial Bold, Arial, sans-serif' ] = 'Rockwell';
		  $font_choices[ 'Segoe UI' ] = 'Segoe UI';
		  return $font_choices;
	}
	private function select_font_with_label($select_name,$main_value='',$bind=''){
		?>
        
		<select class="poll_select" name="<?php echo 'parametrs['.$select_name.']'; ?>" id="<?php echo $select_name ?>" >
		<?php
		
		foreach($this->fonts_options() as $key => $value){
			?>
			<option <?php selected($key,$main_value) ?> value="<?php echo $key ?>" ><?php echo $value ?></option>
			<?php 					
		}
		?>
		</select>																

		<?php
	}
	private function select_border_with_label($select_name,$main_value='',$bind=''){
		?>
		<select class="poll_select" name="<?php echo 'parametrs['.$select_name.']'; ?>" id="<?php echo $select_name ?>" >
		<?php
		
		foreach($this->border_types() as $key => $value){
			?>
			<option <?php selected($key,$main_value) ?> value="<?php echo $key ?>"><?php echo $value ?></option>
			<?php 					
		}
		?>
		</select>																

		<?php
	}
	private function hex2rgba($color, $opacity = false) {

		$default = 'rgba(0,0,0,1)';
		$opacity=$opacity/100;
		if(empty($color))
			  return $default; 
			if ($color[0] == '#' ) {
				$color = substr( $color, 1 );
			}	
			if (strlen($color) == 6) {
					$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
			} elseif ( strlen( $color ) == 3 ) {
					$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
			} else {
					return $default;
			}
			$rgb =  array_map('hexdec', $hex);
			if($opacity){
				if(abs($opacity) > 1)
					$opacity = 1.0;
				$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
			} else {
				$output = 'rgb('.implode(",",$rgb).')';
			}
			return $output;
	}
	
}


 ?>