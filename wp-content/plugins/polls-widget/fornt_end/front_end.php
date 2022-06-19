<?php 
class poll_front_end{
	private $menu_name;
	private $plugin_url;
	private $plugin_path;	
	private $databese_parametrs;
	private $params;
	public static $prefix_main_poll;

	function __construct($params){
		$this->prefix_main_poll=0;
		$this->menu_name=$params['menu_name'];
		$this->databese_parametrs=$params['databese_parametrs'];
		if(isset($params['plugin_url']))
			$this->plugin_url=$params['plugin_url'];
		else
			$this->plugin_url=trailingslashit(dirname(plugins_url('',__FILE__)));
		// set plugin path
		if(isset($params['plugin_path']))
			$this->plugin_path=$params['plugin_path'];
		else
			$this->plugin_path=trailingslashit(dirname(plugin_dir_path(__FILE__)));
		
		add_action('wp_head',array($this,'script_and_styles'));
		add_shortcode( 'wpdevart_poll', array($this,'poll_shortcode') );
		add_action( 'wp_ajax_pollinsertvalues', array($this,'save_poll_in_databese') );
		add_action( 'wp_ajax_nopriv_pollinsertvalues', array($this,'save_poll_in_databese') );
			
	}
	public function save_poll_in_databese(){
		
		check_ajax_referer( 'poll_answer_securety','poll_answer_securety'  );
		global $wpdb;

		$curent_user_ip=$this->get_user_ip();
		$question_id=$_POST['question_id'];
		
		$user_information=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'polls_users WHERE question_id=%d AND user_ip=%s',$question_id,$curent_user_ip));
		
		$array_of_answered_user=array();
		if($user_information->user_ip)
			$array_of_answered_user=json_decode($user_information->answers,true);
		$new_voted_array=$_POST['date_answers'];
		if($user_information->user_ip){
			if($array_of_answered_user)
			foreach($array_of_answered_user as $answer)	{	
				$wpdb->query('UPDATE '.$wpdb->prefix.'polls SET vote = vote-1 WHERE `question_id` = '.$question_id.' AND `answer_name` = '.$answer.'');				
			}
			
			if($new_voted_array)
			foreach($new_voted_array as $new_answer)	{
				$wpdb->query('UPDATE '.$wpdb->prefix.'polls SET vote = vote+1 WHERE `question_id` = '.$question_id.' AND `answer_name` = '.$new_answer.'');			}
			$wpdb->update($wpdb->prefix.'polls_users', 
				array( 
				'user_ip' 		 =>$curent_user_ip,
				'question_id' 	 => $question_id,
				'answers'		 => json_encode($new_voted_array, JSON_FORCE_OBJECT), 
				), 
				array(
					'id' =>$user_information->id
				),
				array( 
					'%s',
					'%d',
					'%s'	
				), 
				array( '%d') 
			);	
		}else{
			$wpdb->insert($wpdb->prefix.'polls_users', 
				array( 
					'user_ip' 		 =>$curent_user_ip,
					'question_id' 	 => $question_id,
					'answers'		 => json_encode($new_voted_array, JSON_FORCE_OBJECT), 	
				), 
				array( 
					'%s', 
					'%d',
					'%s' 
				) 
			);
			if($new_voted_array)
			foreach($new_voted_array as $answer){
				$wpdb->query('UPDATE '.$wpdb->prefix.'polls SET vote = vote+1 WHERE `question_id` = '.$question_id.' AND `answer_name` = '.$answer.'');	
				
			}
		
		}
			
			
			
			

			$answer=$wpdb->get_results('SELECT `answer_name`,`vote` FROM '.$wpdb->prefix.'polls WHERE question_id='.$question_id,ARRAY_A);
			print_r(json_encode($answer, JSON_FORCE_OBJECT));
		die();
	}
	public function poll_shortcode( $atts ) {	
		return $this->generete_front_end_single_poll($atts['id'],$atts['theme']);
	}
	public function script_and_styles(){
		$poll_scripts_varable = array( 'poll_answer_securety' => wp_create_nonce( "poll_answer_securety" ), 'admin_ajax_url' => admin_url("admin-ajax.php") );		
		wp_enqueue_script('jquery');
		wp_enqueue_style("front_end_poll");
		wp_enqueue_script('poll_front_end_script');
		wp_localize_script( 'poll_front_end_script', 'poll_varables', $poll_scripts_varable );
		
	}
	private function genrerte_styles_for_curent_poll($prefix=0,$poll_design=''){
		
		$poll_parametrs=json_decode($poll_design->option_value,true);
		$curent_poll_style='<style>
			.poll_main_div_'.$prefix.' input{
				padding:0px !important;
				margin	:0px !important;
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer{
				background-color: '.$this->hex2rgba('#ffffff','100' ).';
				width: '.$poll_parametrs['poll_answer_width'].'%;
				height: '.$poll_parametrs['poll_answer_height'].'px;
				margin-bottom: '.'5'.'px;
				border-width:'.'1'.'px;
				border-radius:'.'5'.'px;
				border-style:'.'outset'.';
				border-color:'.'#3b5998'.';
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer:hover{
				background-color: '.$this->hex2rgba('#ffffff','100' ).';
				width: '.$poll_parametrs['poll_answer_width_hower'].'%;
				height: '.$poll_parametrs['poll_answer_height_hower'].'px;
				border-width:'.'1'.'px;
				border-radius:'.'5'.'px;
				border-style:'.'outset'.';
				border-color:'.'#3b5998'.';
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer .poll_answer_title{
				font-size: '.'14'.'px;
				top: '.$poll_parametrs['poll_answer_of_the_top'].'px;
				left: '.'5'.'px;
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer:hover .poll_answer_title{
				font-size: '.'14'.'px;
				top: '.$poll_parametrs['poll_answer_of_the_top_hower'].'px;
				left: '.'5'.'px;
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer .pracents_of_the_poll{
				background-color: '.'#afafaf'.';
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer:hover .pracents_of_the_poll{
				background-color: '.'#afafaf'.';
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer .poll_answer_title{
				color: '.'#0c0101'.';
				font-family: '.'Arial,Helvetica Neue,Helvetica,sans-serif'.';
				font-weight: '.('on'?'bold':'normal').';
				font-style: '.('on'?'italic':'normal').';
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer:hover .poll_answer_title{
				color: '.'#0c0101'.';
				font-family: '.'Arial,Helvetica Neue,Helvetica,sans-serif'.';
				font-weight: '.('on'?'bold':'normal').';
				font-style: '.('on'?'italic':'normal').';
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer .poll_span_voted_count{
				display:'.('0'?'inline-block':'none').';
				font-size: '.'13'.'px;
				right: '.'7'.'px;
				top: '.$poll_parametrs['poll_count_of_the_top'].'px;
				color: '.'#878787'.';
				font-family: '.'Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif'.';
				font-weight: '.('on'?'bold':'normal').';
				font-style: '.('on'?'italic':'normal').';
			}
			.poll_main_div_'.$prefix.' .main_div_of_the_poll_answer:hover .poll_span_voted_count{
				display:'.('1'?'inline-block':'none').';
				font-size: '.'13'.'px;
				right: '.'7'.'px;
				top: '.$poll_parametrs['poll_count_of_the_top_hower'].'px;
				color: '.'#878787'.';
				font-family: '.'Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif'.';
				font-weight: '.('on'?'bold':'normal').';
				font-style: '.('on'?'italic':'normal').';
			}
			.poll_main_div_'.$prefix.' .radio_or_select_span{
				padding-bottom: '.(5-3).'px;
			}	
    	</style>';
		
		return $curent_poll_style;
	}
	private function generete_html_front_end_poll($poll_answer_id){
		global $wpdb;		
		$poll_question_and_answers='';
		$ip=$this->get_user_ip();
		$user_information=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'polls_users WHERE `question_id`=%d AND `user_ip`=%s',$poll_answer_id,$ip));
		$array_of_chekced=NULL;
		if($user_information->answers){
			$array_of_chekced=json_decode($user_information->answers,true);
		}
		$question=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'polls_question WHERE id=%d',$poll_answer_id));
		$answers=$wpdb->get_results($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'polls WHERE question_id=%d ORDER BY answer_name',$poll_answer_id));
		$voted_count=$wpdb->get_var($wpdb->prepare('SELECT SUM(vote) FROM '.$wpdb->prefix.'polls WHERE question_id=%d',$poll_answer_id));
		$answer_with_all_information=array();
		$poll_question_and_answers.='<div class="poll_question">'.$question->question.'</div>';
		foreach($answers as $answer){
			$checked=0;
			if($array_of_chekced){
				if(in_array($answer->answer_name,$array_of_chekced))
					$checked=1;
			}
			$user_information->answers;
			$curent_answer_checked=0;
			$answer_type=$question->answer_type?'checkbox':'radio';
			$poll_question_and_answers.='<div class="div_with_ceckbox_or_select poll_element_'.$answer->answer_name.'">';
			$poll_question_and_answers.='<span class="radio_or_select_span"><input onclick="send_to_databese('.$poll_answer_id.','.$this->prefix_main_poll.')" class="poll_inputs" type="'.$answer_type.'" name="poll_'.$this->prefix_main_poll.'" value="'.$answer->answer_name.'" '.checked(1, $checked,false ).'></span>';
			$poll_question_and_answers.='<div class="div_only_answer">';
			$poll_question_and_answers.='<div class="main_div_of_the_poll_answer" onclick="clicked_in_poll_div(this,'.$poll_answer_id.','.$this->prefix_main_poll.')" title="'.$answer->answer.'">';
			$poll_question_and_answers.='<div class="pracents_of_the_poll colorwell" style="width: '.$this->pracents_of_answer($voted_count,$answer->vote).'%;"></div>';
			$poll_question_and_answers.='<div class="polllabel"><span class="poll_answer_title">'.$answer->answer.'</span></div>';
			$poll_question_and_answers.='<div class="poll_div_voted_count"><span class="poll_span_voted_count"> '.$answer->vote.' Vote</span></div>';
			$poll_question_and_answers.='</div></div></div>';
			
			?>

            <?php			
		}
		
		
		
		return $poll_question_and_answers;
	}
	/*Function for Geting User Ip*/
	private  function get_user_ip(){
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		   $ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = '000';
			$ipaddress=explode(',',$ipaddress);
			$ipaddress=$ipaddress[0];
		return $ipaddress;
	}
	/*pracent of curent answer*/
	private function pracents_of_answer($all_count_answer,$curent_answer_count){
		return $all_count_answer?($curent_answer_count/$all_count_answer)*100:0;	
	}
	public function generete_front_end_single_poll($poll_answer_id,$poll_design_id){
		global $wpdb;
		$this->prefix_main_poll++;
		$poll_front_end_code='';
		if(!$poll_answer_id){
			$poll_front_end_code=__('Poll answer not selected');
			return $poll_front_end_code;
		}
		if(!$poll_design_id){
			$poll_design_row=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'polls_templates WHERE `default`=1');
			$poll_design_id=$poll_design_row->id;	
		}
		else{
			$poll_design_row=$wpdb->get_row($wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'polls_templates WHERE id=%d',$poll_design_id));
			if(!$poll_design_row){
				$poll_design_row=$wpdb->get_row('SELECT * FROM '.$wpdb->prefix.'polls_templates WHERE `default`=1');
			}
			
		}
		
		$poll_front_end_code.=$this->genrerte_styles_for_curent_poll($this->prefix_main_poll,$poll_design_row);
		$poll_front_end_code.='<div class="poll_main_div_'.$this->prefix_main_poll.' poll_min_div_cur_answer_'.$poll_answer_id.' wpdevartpoll">'.$this->generete_html_front_end_poll($poll_answer_id).'</div>';
		return $poll_front_end_code;
	}
	private function hex2rgba($color, $opacity = 0) {

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
				$output = 'rgba('.implode(",",$rgb).','.$opacity.')';
			
			return $output;
	}
	
}




?>