<?php
class poll_manager_answers{
	private $menu_name;
	private $databese_names;
	
	
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
	
	/*#################### CONTROLERRR ########################*/
	/*#################### CONTROLERRR ########################*/
	/*#################### CONTROLERRR ########################*/
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
		case 'add_edit_poll':	
			$this->add_edit_answer();
			break;
			
			
		case 'save_poll':		
			if($id)	
				$this->update_poll($id);
			else
				$this->save_poll();
				
			$this->display_table_list_answers();	
			break;
			
			
		case 'update_poll':		
			if($id)
				$this->update_poll($id);
			else
			{
				$this->save_poll();
				$_GET['id']=$wpdb->get_var("SELECT MAX(id) FROM ".$wpdb->prefix."polls_question");
			}
			$this->add_edit_answer();
			break;
		case 'remove_poll':	
			$this->remove_poll($id);
			$this->display_table_list_answers();
			break;
				
		default:
			$this->display_table_list_answers();
		}
	}
	
	/*#################### Table List ########################*/
	/*#################### Table List ########################*/
	/*#################### Table List ########################*/
	private function display_table_list_answers(){
		
		?>
        <style>
        .description_row:nth-child(odd){
			background-color: #f9f9f9;
		}
        </style>
        <script> var my_table_list=<?php echo $this->generete_jsone_list(); ?></script>
        <div class="wrap">
            <form method="post"  action="" id="admin_form" name="admin_form" ng-app="" ng-controller="customersController">
			<h2>Polls Manager <a href="admin.php?page=Polls&task=add_edit_poll" class="add-new-h2">Add New</a></h2>            
   
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
                        <th data-ng-click="order_by='question'; reverse=!reverse; ordering($event,order_by,reverse)" class="manage-column sortable desc"><a><span>Question</span><span class="sorting-indicator" ></span></a></th>
                        <th data-ng-click="order_by='count_vote'; reverse=!reverse; ordering($event,order_by,reverse)" class="manage-column sortable desc"><a><span>Total Voters</span><span class="sorting-indicator"></span></a></th>
                        <th style="width:80px">Edit</th>
                        <th  style="width:80px">Delete</th>
                    </tr>
                </thead>
                <tbody>
                 <tr ng-repeat="rows in names | filter:filtering_table" class="description_row">
                         <td>{{rows.id}}</td>
                         <td><a href="admin.php?page=Polls&task=add_edit_poll&id={{rows.id}}">{{rows.name}}</a></td>
                         <td><a href="admin.php?page=Polls&task=add_edit_poll&id={{rows.id}}">{{rows.question}}</a></td>
                         <td><a href="admin.php?page=Polls&task=add_edit_poll&id={{rows.id}}">{{rows.count_vote}}</a></td>
                         <td><a href="admin.php?page=Polls&task=add_edit_poll&id={{rows.id}}">Edit</a></td>
                         <td><a href="admin.php?page=Polls&task=remove_poll&id={{rows.id}}">Delete</a></td>
                               
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
	}
	private function generete_jsone_list(){
		global $wpdb;
		$query = "SELECT SUM( ".$wpdb->prefix."polls.vote ) AS count_vote, ".$wpdb->prefix."polls_question.name, ".$wpdb->prefix."polls_question.id, ".$wpdb->prefix."polls_question.question FROM  ".$wpdb->prefix."polls RIGHT JOIN  ".$wpdb->prefix."polls_question ON ".$wpdb->prefix."polls.question_id = ".$wpdb->prefix."polls_question.id GROUP BY ".$wpdb->prefix."polls_question.id";
		$rows=$wpdb->get_results($query);
		$json="[";
		$no_frst_storaket=1;
		foreach($rows as $row){
			$json.=(($no_frst_storaket) ? '' : ',' )."{";
			$no_frst_storaket=1;
			foreach($row as $key=>$value){
				if($key!='id'){
					$json.= "".(($no_frst_storaket) ? '' : ',' )."'".$key."':"."'".(($value)?preg_replace('/^\s+|\n|\r|\s+$/m', '',htmlspecialchars_decode(addslashes(strip_tags(preg_replace("/&#?[a-z0-9]+;/i", '',$value))))):'0')."'";				
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
	
	
	/*#################### EDIT ANSWER ########################*/
	/*#################### EDIT ANSWER ########################*/
	/*#################### EDIT ANSWER ########################*/
	private function generete_add_edit_answer_parametrs(){
			global $wpdb;
			$answers='';
			$max_answer_id=1;
			$queshon='';
			$poll_name='';
			$id=0;
			$poll_name='';
			$poll_answer_type=0;
			$add_or_edit_title='Add New Poll';
			if(isset($_GET['id']))
				$id=$_GET['id'];
			if($id){
				
				$max_answer_id=1;
				$row_question=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'polls_question WHERE id='.$id);
				$queshon=$row_question->question;
				$poll_name=$row_question->name;
				$poll_answer_type=$row_question->answer_type;
				$add_or_edit_title='Edit Poll ';
				$answers=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'polls WHERE question_id='.$id." ORDER BY `answer_name` ASC");
			
				foreach($answers as $answer){
					if($max_answer_id<$answer->answer_name)
					$max_answer_id=$answer->answer_name;
				}
				$max_answer_id++;
			}		
			
			return array('answers'=>$answers,'max_answer_id'=>$max_answer_id,'queshon'=>$queshon,'id'=>$id,'poll_title'=>$add_or_edit_title,'poll_name'=>$poll_name,'poll_answer_type'=>$poll_answer_type);	
	}
	
	private function add_edit_answer(){
			$settings=$this->generete_add_edit_answer_parametrs();
			$answers=$settings['answers'];
			$max_answer_id=$settings['max_answer_id'];
			$queshon=$settings['queshon'];
			$id=$settings['id'];
			$poll_answer_type=$settings['poll_answer_type'];
			$poll_name=$settings['poll_name'];
			$poll_title=$settings['poll_title'];
			$settings=array('textarea_rows' => 10 ,	'editor_css' => '<style>.wp-editor-wrap{width:500px}</style>'   );
	?>
    <style></style>
		<script language="javascript" type="text/javascript">
		
			function submitbutton(pressbutton){						
				submitform( pressbutton );		
			}
			
			function submitform(pressbutton){
				document.getElementById('adminForm').action=document.getElementById('adminForm').action+"&task="+pressbutton;
				document.getElementById('adminForm').submit();
			}

			var count_of_elements=<?php echo  $max_answer_id; ?>;
			function add_answer(id_upload){
	
				if(id_upload>=count_of_elements)	
				{		
					count_of_elements++;
							
					var divelem = document.createElement('div');
					divelem.setAttribute('id','answer_'+count_of_elements);
					
					
					var inpElement = document.createElement('input');
					inpElement.setAttribute('type','text');
					inpElement.setAttribute('style','width:200px;');
					inpElement.setAttribute('id','answerpoll_no_'+count_of_elements);
					inpElement.setAttribute('value','');
					inpElement.setAttribute('onchange','add_answer('+count_of_elements+')');
					inpElement.setAttribute('class','text_input');
					inpElement.setAttribute('name','answer['+count_of_elements+']');
					
					var btnElement = document.createElement('input');
					btnElement.setAttribute('type','button');
					btnElement.setAttribute('value',' Remove ');
					btnElement.setAttribute('onclick',"remov_answer("+count_of_elements+")");
					btnElement.setAttribute('title',"Delete");
					btnElement.setAttribute('class','button-secondary');		
					
					divelem.appendChild(inpElement);
					divelem.appendChild(btnElement);
					divelem.appendChild(document.createElement('br'));
					document.getElementById('answers_td').appendChild(divelem);
	
					document.getElementById('answerpoll_no_'+count_of_elements).focus();
				} 				
			}


			function remov_answer(id_upload){
				if(document.getElementById('answers_td').getElementsByTagName('div').length!=1 && id_upload!=count_of_elements){
					var div = document.getElementById("answer_" + id_upload);
					div.parentNode.removeChild(div);
				}			
			}
	
			function referesh_created_tags()
			{
				var lists="";
				document.getElementById('answers_list').value='';
				for(i=0;i<count_of_elements;i++){
					if(document.getElementById('answerpoll_no_'+i)){
						if(document.getElementById('answers_list').value)
							document.getElementById('answers_list').value=document.getElementById('answers_list').value+";;;"+document.getElementById('answerpoll_no_'+i).value;
						else
							document.getElementById('answers_list').value=document.getElementById('answerpoll_no_'+i).value;
					}
				}
			}
			var text_of_upgrate_version='If you want to use this feature upgrade to Polls Pro!';
		</script>  
        <style>
		.param_question{
			float:left;
			margin-right:40px;
		}
		.param_answer{
			display: inline-block;
		}
		.top_description_table{
			max-width:817px;
		}
		.poll_name{
			padding: 0px 8px 0px 8px;
			font-size: 1.7em;
			height: 1.7em;
			width: 100%;
			outline: 0;
			margin: 0;
			background-color: #fff;
		}
		.subtitle_poll{
			font-size: 18px;
			padding-bottom: 22px;
			display: inline-block;
		}
        </style>
        <table class="top_description_table">
            <tr>
                <td width="100%"><h2 class="poll_title"><?php echo $poll_title; ?></h2></td>
                <td align="right"><input type="button" onclick="submitbutton('save_poll')" value="Save" class="button-primary action"> </td>  
                <td align="right"><input type="button" onclick="submitbutton('update_poll')" value="Apply" class="button-primary action"> </td> 
                <td align="right"><input type="button" onclick="window.location.href='admin.php?page=Polls'" value="Cancel" class="button-secondary action"> </td> 
            </tr>
        </table>
        <form action="admin.php?page=Polls<?php if($id) echo '&id='.$id; ?>" method="post" name="adminForm" class="top_description_table" id="adminForm">
            <div class="param_name">
           		<br><br>
               <input type="text" class="poll_name" name="poll_name" placeholder="Enter name here" value="<?php echo $poll_name ?>" style="width:818px">
               <br><br>
            </div>
            <div class="param_question">
                <b class="subtitle_poll">Question</b>
                <?php wp_editor( $queshon, 'Question', $settings ); ?>
                <a target="_blank" class="update_pro" href="http://wpdevart.com/wordpress-polls-plugin/"><span style="color: rgba(10, 154, 62, 1); font-weight: bold; font-size: 21px;">Upgrade to Pro Version</span></a>

            </div>
            <div id="answers_td" class="param_answer">
                <b class="subtitle_poll">Answers</b>
                <div class="answer_type_main_div">
                	<div class="radio_poll_div">
                    <input type="radio" name="answer_type" value="0" id="answer_type_single" checked="checked" class="radio_poll"/>
                    <label for="answer_type_single" class="radio_poll_label" style="margin-right: 9px;">Single</label>
                    </div>
                    <div class="radio_poll_div">
                    <input type="radio" onClick="alert(text_of_upgrate_version); return false"  id="answer_type_multi" class="radio_poll"/>
                    <label for="answer_type_multi" class="radio_poll_label">Multiple <span class='pro_subtitle_span'>Pro</span></label>
                    </div>                    
                </div>
                <?php if($answers==''){ ?>
                <div id="answer_1">
                            <input type="text" id="answerpoll_no_1" name="answer[1]" value="" onchange="add_answer('1');" class="text_input" style="width:200px;"><input class="button-secondary" type="button" value=" Remove " title=" Remove " onclick="remov_answer('1')"><br>
                </div>
                <?php }
                else{                        
                    foreach($answers as $answer){
                ?>
                 <div id="answer_<?php echo $answer->answer_name ?>">
                            <input type="text" id="answerpoll_no_<?php echo $answer->answer_name ?>" name="answer[<?php echo $answer->answer_name ?>]" value="<?php echo $answer->answer ?>" onchange="add_answer('<?php echo $answer->answer_name ?>');" class="text_input" style="width:200px;"><input class="button-secondary" type="button" value=" Remove " title=" Remove " onclick="remov_answer('<?php echo $answer->answer_name ?>')"><br>
                </div>
                <?php
                 }
                ?>
                 <div id="answer_<?php echo $max_answer_id ?>">
                            <input type="text" id="answerpoll_no_<?php echo $max_answer_id ?>" name="answer[<?php echo $max_answer_id?>]" value="" onchange="add_answer('<?php echo $max_answer_id ?>');" class="text_input" style="width:200px;"><input class="button-secondary" type="button" value=" Remove " title=" Remove " onclick="remov_answer('<?php echo $max_answer_id ?>')"><br>
                </div>
                <?php }?>
                <input type="hidden" value="" name="answers" id="answers_list" />
             </div>   
            
        
        </form>

<?php
	}
	private function save_poll(){
		global $wpdb;
		$save_or_no=$wpdb->insert( $wpdb->prefix.'polls_question', 
			array( 
				'question' => stripslashes($_POST['Question']),
				'name' => htmlspecialchars(stripslashes($_POST['poll_name'])),
				'answer_type' => $_POST['answer_type'],
				
			), 
			array( 
				'%s', 
				'%s',
				'%d',
			) 
		);
		if($save_or_no){
			$id_question=$wpdb->get_var('SELECT MAX(id) FROM '.$wpdb->prefix.'polls_question');
			$answers=$_POST['answer'];
			foreach($answers as $key=>$answer){
				if($answer){
					$save_or_no=$wpdb->insert( $wpdb->prefix.'polls', 
						array( 
							'question_id' => $id_question,
							'answer' => $answer,
							'answer_name' =>$key
						), 
						array( 
							'%d', 
							'%s', 
							'%d', 
						) 
					);
				}
	
			}
		}	
		if($save_or_no){
			?><div class="updated"><p><strong>Item Saved</strong></p></div><?php
		}
		else{
			?><div id="message" class="error"><p>Item Not Saved</p></div> <?php
		}
	}
	private function update_poll($id){
		global $wpdb;
		$wpdb->update( $wpdb->prefix.'polls_question', 
			array( 
				'question' => stripslashes($_POST['Question']),
				'name' => htmlspecialchars(stripslashes($_POST['poll_name'])),
				'answer_type' => $_POST['answer_type'],
			), 
			array( 
				'id'=>$id 
			),
			array(
				'%s', 
				'%s', 
				'%d'
			),
			array( 
				'%d'
			)  
		);
		$answers_names=array();
		$id_question=$id;
		$answers_base=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'polls WHERE question_id='.$id_question);
		foreach($answers_base as $answer_base){
			array_push($answers_names,$answer_base->answer_name);
			$asnwer_base_names[$answer_base->answer_name]=$answer_base->id;
		}
		$answers=$_POST['answer'];
		foreach($answers as $key=>$answer)
		{
			if(!in_array($key,$answers_names)){
				array_push($answers_names,$key);
			}
		}
		foreach($answers_names as $answer_name){
			if(isset($answers[$answer_name]) && isset($asnwer_base_names[$answer_name])){
				if($answers[$answer_name]){
					$wpdb->update($wpdb->prefix.'polls', 
						array( 
							'question_id' => $id_question,
							'answer' => $answers[$answer_name],
							'answer_name' =>$answer_name
						), 
						array( 'id' => $asnwer_base_names[$answer_name] ), 
						array( 
							'%d',	
							'%s',
							'%d'	
						), 
						array( '%d' ) 
					);
				}
				else{
						$query='DELETE FROM '.$wpdb->prefix.'polls WHERE id='.$asnwer_base_names[$answer_name].'';
						$wpdb->query($query);
				}
			}
			if(isset($answers[$answer_name]) && !isset($asnwer_base_names[$answer_name])){
		
				if($answers[$answer_name])
				$save_or_no=$wpdb->insert( $wpdb->prefix.'polls', 
					array( 
						'question_id' => $id_question,
						'answer' => $answers[$answer_name],
						'answer_name' =>$answer_name
					), 
					array( 
						'%d', 
						'%s', 
						'%d', 
					) 
				);
			}
			if((!isset($answers[$answer_name])) && isset($asnwer_base_names[$answer_name])){
				$query='DELETE FROM '.$wpdb->prefix.'polls WHERE id='.$asnwer_base_names[$answer_name].'';
				$wpdb->query($query);
			}
		}
	 ?><div class="updated"><p><strong>Item Saved</strong></p></div><?php
	}
	private function remove_poll($id){
		global $wpdb;
		$query='DELETE FROM '.$wpdb->prefix.'polls WHERE question_id='.$id.'';
		$wpdb->query($query);
		$query='DELETE FROM '.$wpdb->prefix.'polls_users WHERE question_id='.$id.'';
		$wpdb->query($query);
		$query='DELETE FROM '.$wpdb->prefix.'polls_question WHERE id='.$id.'';
		$wpdb->query($query);
		?><div class="updated"><p><strong>Item Deleted</strong></p></div><?php
	}
	public function csripts_styles_for_menu(){
		
	}
}


 ?>