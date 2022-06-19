<?php
/*
Plugin Name: post-views
Plugin URI: http://ziming.org/dev/post-views
Description: Record how many times the post have been views and show it. <a href= "http://ziming.org/dev/post-views" target="_blank"> [Usage]</a>
Version: 2.6.3
Author: Suny Tse
Author URI: http://ziming.org
*/

/*  Copyright 2010  Suny Tse  (email : message@ziming.org)

    This program is free software; you can redistribute it and/or modify
    it under the Donation of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!function_exists('add_action')) {
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

global $table_prefix;
$table_prefix = ( isset( $table_prefix ) ) ? $table_prefix : $wpdb->prefix;
define('WP_POST_VIEWS_TABLE', $table_prefix . 'post_views_realtime');
define('WP_POST_VIEWS_HIS_TABLE', $table_prefix . 'post_views_history');
define('WP_POST_VIEWS_SUMMARY_TABLE', $table_prefix . 'post_views_summary');
define('WP_POST_TABLE', $table_prefix . 'posts');
define('PV_ADMIN_URL', 'index.php?page=post-views');       

add_action('init', 'post_views_textdomain');
add_action('init', 'post_views_setcookie');
add_action('admin_menu', 'post_views_menu');
add_action('wp_head', 'process_post_views_content');
add_action('widgets_init', 'widget_post_views_init');
add_filter('query_vars', 'post_views_variables');
add_action('pre_get_posts', 'post_views_sorting');
add_action('delete_post', 'delete_post_views');
add_filter('get_the_excerpt', 'update_excerpt_views',5);

add_action('admin_init','add_post_views_contextual_help');

function add_post_views_contextual_help(){

   add_contextual_help('dashboard_page_post-views',
   		'<p><strong>Donation:</strong></p> 
   		<p>PayPal Account ( Global ): <a href="https://www.paypal.com/" target="_blank"> message@ziming.org</a>
   		&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; AliPay Account ( China ): <a href="https://www.alipay.com//" target="_blank"> message@ziming.org</a></p>
   	'); 

}

function update_excerpt_views(){
	global $post;	
	process_post_views_excerpt();
	$output = $post->post_excerpt;
	if ( post_password_required($post) ) {
		$output = 'There is no excerpt because this is a protected post';		
	}
	return $output;
}

register_activation_hook(__FILE__,'check_post_views_install');
cache_support();

function post_views_textdomain() {
	load_plugin_textdomain('post-views', false, 'post-views/lang');
}

function post_views_menu() {	
	if (function_exists('add_dashboard_page')) {
	  	add_dashboard_page( 'Post-Views',__('Post Views','post-views'), 2, 'post-views',  'post_views');
	}
	
	if (function_exists('add_options_page')) {
			add_options_page('Post Views', 'Post Views',8, 'post-views', 'post_views');
	}//add_options_page(page_title, menu_title, access_level/capability, file, [function]);
}


/******************************* Cache Related Functions *********************************/

function cache_enable(){
	$cache_enable = false;
	$pv_rec_options = get_option('post_views_rec_options');
	$using_cache = $pv_rec_options['cache_plugin'];
	
	if($using_cache == '1'){	
		$cache_enable = true;
	}
	
	return $cache_enable;
}

function post_views_setcookie(){
	$pv_rec_options = get_option('post_views_rec_options');
	$timeout = $pv_rec_options['cookie_timeout'];
	$cookie = strtotime(date('Y-m-d H:i:s'));
	$pv_url = md5($_SERVER['REQUEST_URI']);
	setcookie($pv_url, $cookie, time()+60*$timeout,"/");
}

function cache_support() {	
	$cache_type = $_GET['cache_view_type'];
	if($cache_type != "robot"){
		$cache_type = "normal";
	}
	$output_type = $_GET['cache_output_type'];
	if($output_type != "excerpt"){
		$output_type = "content";
	}
	$cache_id = intval($_GET['cache_post_id']);
	$cache_time_span = $_GET['cache_time_span'];
	if(!empty($cache_time_span) && ($cache_time_span != "today") && ($cache_time_span != "week") && ($cache_time_span != "month") && ($cache_time_span != "halfyear")){
		$cache_time_span = "year";
	}
	$last_view_time = $_GET['last_view_time'];

	if(cache_enable()){
			if(($cache_id > 0) && !empty($cache_type) && !empty($output_type) && empty($cache_time_span)) {
				post_views_update($cache_type,$cache_id,$output_type);
			}
			if(($cache_id > 0) && !empty($cache_type) && !empty($output_type) && !empty($cache_time_span)) {
				echo get_post_views($cache_type,$cache_time_span,$output_type,true,$cache_id,true);
			}
			if(($cache_id > 0) && !empty($last_view_time)){
				echo get_post_views_time_cache($cache_id);	
			}
	}	
}

function cache_print($v_type,$id,$o_type){
	  if(!empty($v_type) && !empty($id) && !empty($o_type)){
		     echo '<!-- Start Of Script Generated By Post-Views -->'."\n";
		     echo '<script type="text/javascript">'."\n";
		     echo 'jQuery.ajax({type:"GET",async:false,url:"'.plugins_url('post-views/post-views.php').'",data:"cache_post_id='.$id.'&cache_view_type='.$v_type.'&cache_output_type='.$o_type.'",cache:false});'; 
		     echo '</script>'."\n";						
		     echo '<!-- End Of Script Generated By Post-Views -->'."\n";
		 }
	
}


function check_post_views_install(){ 

  global $wpdb,$table_prefix;
  $post_views_table_exists = false;
  $post_views_his_table_exists = false;
  $post_views_table_exists = false;
  $post_views_summary_table_exists = false;

/************************ Check which table not exist *********************/

  $tables = $wpdb->get_results("show tables");
  foreach ( $tables as $table ){
      foreach ( $table as $value ){
	  			if ($value == WP_POST_VIEWS_TABLE){
	      			$post_views_table_exists = true;
	    		}
	    		if ($value == WP_POST_VIEWS_HIS_TABLE){
	      			$post_views_his_table_exists = true;
	    		}
	    		if ($value == WP_POST_VIEWS_SUMMARY_TABLE){
	    				$post_views_summary_table_exists = true;
	    		}
  		}
  }
  
  if(!$post_views_table_exists){
  	  $sql = "CREATE TABLE ". WP_POST_VIEWS_TABLE ." (
                                post_id BIGINT(20) UNSIGNED NOT NULL,
                                view_type varchar(20) NOT NULL,
                                output_type varchar(20)  NOT NULL,
                                post_views_today BIGINT(20) UNSIGNED NOT NULL,
                                post_views_week BIGINT(20) UNSIGNED NOT NULL,
                                post_views_month BIGINT(20) UNSIGNED NOT NULL,
                                post_views_halfyear BIGINT(20) UNSIGNED NOT NULL,
                                post_views_year BIGINT(20) UNSIGNED NOT NULL,
                                post_views_total BIGINT(20) UNSIGNED NOT NULL,
                                latest_view_time timestamp NOT NULL default CURRENT_TIMESTAMP,
                                PRIMARY KEY (post_id,view_type,output_type)
                        )";
      $wpdb->get_results($sql);	
      
      /************************************* import data from wp-postviews ********************************/
      		
      $old__views_data = $wpdb->get_results("select * from ".$table_prefix."postmeta where meta_key='views' ");
      if(count($old__views_data)){
      		foreach ( $old__views_data as $old_views ){
      				$wpdb->get_results("insert into ".WP_POST_VIEWS_TABLE." set post_id = ".$old_views->post_id.", view_type = 'normal',output_type = 'content', post_views_today = 0, post_views_week = 0, post_views_month = 0, post_views_halfyear = 0, post_views_year = 0, post_views_total = ".$old_views->meta_value.", latest_view_time='".gmdate("Y-n-d H:i:s")."'");	
      		}
     	}
  }
	
	
  if(!$post_views_his_table_exists){
			$sql = "CREATE TABLE IF NOT EXISTS ".WP_POST_VIEWS_HIS_TABLE." (
  						year_id int(4) NOT NULL,
  						post_id bigint(20) NOT NULL,
   						view_type varchar(20) NOT NULL,
   						output_type varchar(20)  NOT NULL,
   						post_views_today TEXT NOT NULL,
   						post_views_week TEXT NOT NULL,
   						post_views_month TEXT NOT NULL,
   						post_views_halfyear TEXT NOT NULL,
   						post_views_year TEXT NOT NULL,
   						PRIMARY KEY (year_id,post_id,view_type,output_type )
          )"; 
         
      	$wpdb->get_results($sql);
   }
   
   if(!$post_views_summary_table_exists){
   			$sql = "CREATE TABLE IF NOT EXISTS ".WP_POST_VIEWS_SUMMARY_TABLE." (
  						summary_type varchar(100) NOT NULL,
  						year_id int(4) NOT NULL,
   						post_views_today TEXT NOT NULL,
   						post_views_week TEXT NOT NULL,
   						post_views_month TEXT NOT NULL,
   						post_views_halfyear TEXT NOT NULL,
   						post_views_year TEXT NOT NULL,
   						PRIMARY KEY (summary_type,year_id)
          )"; 
         
      	$wpdb->get_results($sql);
      	
      	$sql = "select * from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0";
				$temp = $wpdb->get_results($sql);
				if(count($temp) == 0){
					  //post views summary
   					$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='normal_views', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);		    		
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='normal_previews', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);		    		
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='robot_views', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);		    		
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='robot_previews', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);
		    		
						//post viewed summary
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='normal_viewed', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);		    		
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='normal_previewed', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);		    		
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='robot_viewed', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);		    		
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='robot_previewed', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);
						
						//post viewed coverage summary
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='normal_viewed_coverage', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);		    		
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='normal_previewed_coverage', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);		    		
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='robot_viewed_coverage', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);		    		
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set summary_type ='robot_previewed_coverage', year_id =".get_year().", post_views_today ='-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear ='-1', post_views_year = '-1'";
						$wpdb->get_results($sql);
					
				}else{					
					 	//post views summary
   					$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'normal_views', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='normal' and year_id =".get_year()." and output_type = 'content' ";
						$wpdb->get_results($sql);						
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'normal_previews', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='normal' and year_id =".get_year()." and output_type = 'excerpt' ";
						$wpdb->get_results($sql);						
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'robot_views', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='robot' and year_id =".get_year()." and output_type = 'content' ";
						$wpdb->get_results($sql);						
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'robot_previews', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='robot' and year_id =".get_year()." and output_type = 'excerpt' ";
						$wpdb->get_results($sql);
						//post viewed summary
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'normal_viewed', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='normal_viewed' and year_id =".get_year()." and output_type = 'content' ";
						$wpdb->get_results($sql);
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'normal_previewed', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='normal_viewed' and year_id =".get_year()." and output_type = 'excerpt' ";
						$wpdb->get_results($sql);
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'robot_viewed', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='robot_viewed' and year_id =".get_year()." and output_type = 'content' ";
						$wpdb->get_results($sql);
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'robot_previewed', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='robot_viewed' and year_id =".get_year()." and output_type = 'excerpt' ";
						$wpdb->get_results($sql);
						//post viewed coverage summary
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'normal_viewed_coverage', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='normal_coverage' and year_id =".get_year()." and output_type = 'content' ";
						$wpdb->get_results($sql);
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'normal_previewed_coverage', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='normal_coverage' and year_id =".get_year()." and output_type = 'excerpt' ";
						$wpdb->get_results($sql);
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'robot_viewed_coverage', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='robot_coverage' and year_id =".get_year()." and output_type = 'content' ";
						$wpdb->get_results($sql);
						$sql = "insert into ".WP_POST_VIEWS_SUMMARY_TABLE."(summary_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) select 'robot_previewed_coverage', ".get_year().", post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year from ".WP_POST_VIEWS_HIS_TABLE." where post_id = 0 and view_type ='robot_coverage' and year_id =".get_year()." and output_type = 'excerpt' ";
						$wpdb->get_results($sql);		    		
					
				}		
  }
	 
	 /************************ Check options *********************/
	
	$pv_rec_options = get_option('post_views_rec_options');
  if(empty($pv_rec_options)){
  		$pv_rec_options['rec_option'] = 0;
  		$pv_rec_options['rec_cookie'] = 0;
  		$pv_rec_options['cookie_timeout'] = 60;
			$pv_rec_options['cache_plugin'] = 0;
			$pv_rec_options['real_time_views'] = 0;
					
  		if(!update_option('post_views_rec_options', $pv_rec_options)){
    		add_option('post_views_rec_options',$pv_rec_options);
  		}
	}
	
  $pv_update_process['update_process'] = 'updated';
					
  if(!update_option('post_views_update_process', $pv_update_process)){
    	add_option('post_views_update_process',$pv_update_process);
  }		
  
}

function check_post_views_update_lock(){ 
	$pv_update_process = get_option('post_views_update_process');
	if($pv_update_process['update_process'] != 'updated'){
			$pv_update_process['update_process'] = 'updated';
			update_option('post_views_update_process', $pv_update_process);
	}
}


function get_custom_time($time,$type){
	
	 $timezone = post_views_timezone();
	 $dateTime = new DateTime($time);
	 
	 $dateTime->setTimezone(new DateTimeZone($timezone));
	
	 $date =  (int)($dateTime->format($type));
	 return $date;
}
							
							
### Option Page
function post_views(){
	
	if($_REQUEST['pv_page'] == 'content-analytics' ) post_views_analytics();
	else if($_REQUEST['pv_page'] == 'summary' ) 	post_views_summary();
	else if($_REQUEST['pv_page'] == 'ranking' )   post_views_list();		
	else if($_REQUEST['pv_page'] == 'category' )   post_category_list();			
	else if($_REQUEST['pv_page'] == 'analytics' ) post_views_analytics();					
	else if($_REQUEST['pv_page'] == 'options' )   post_views_options();
	else if($_REQUEST['pv_page'] == 'detail' )   post_views_detail();	
	else 	post_views_analytics('content');
}

/************************************* Post Views Common Functions  ************************/
function get_percent($n){
  return sprintf( "%01.0f ",   $n*100). '% ';		
}

function get_graphy_str($data,$now_data,$cust_max = 'none'){
	$views_arr = explode(',',$data);
	$views_total = count($views_arr);
	$max = max($views_arr);
	
	if($cust_max != 'none'){
				 $max_str = $cust_max;
	}else if(ceil($max*1.2) < 10){
				 $max_str = 10;
	}else{
				 $max_str = ceil($max*1.2);
	}

  if($views_total <= 7){   
  		if($views_total == 1){
  				for($i = 1;$i < 7;$i++){
      				$data_str .= "-1,";
      		}
      		$data_str .= "-1";
  		}else{
      		for($i=0;$i<=(7 - $views_total);$i++){
      				$data_str .= "-1,";
      		}
      		for($i=1;$i< $views_total-1;$i++){
      				$data_str .= $views_arr[$i].",";
      		}
      		$data_str .= $views_arr[$views_total-1];
      }
      $label_str ="|-7|-6|-5|-4|-3|-2|-1";
   }else{
      for($i=1;$i< $views_total-1;$i++){
      		$data_str .= $views_arr[$i].",";
      		if(($i%ceil($views_total/30))==0){
      				$label_str .= "|-".($views_total - $i -1 + ceil($views_total/30));
      		}
      }
      $data_str .= $views_arr[$views_total-1];    			
      $label_str .= "|-1";
   }
   if($views_total > 300){
   	  $span = 30;
   }else if($views_total > 250){
   		$span = 25;
   }else if($views_total > 200){
   		$span = 20;
   }else if($views_total > 150){
   		$span = 15;
   }else if($views_total > 100){
   		$span = 10;
   }else if($views_total > 50){
   	  $span = 5;
   }else if($views_total > 30){
   	  $span = 3;
   }else if($views_total > 10){
   	  $span = 2;
   }else{
   	  $span = 1;
   }
   $chm = "chm=o,0066FF,0,-".$span.",6|N*s*,0066FF,0,-".$span.",12";
   
   $str['data_str']= $data_str;
   $str['label_str']= $label_str;
   $str['chm']= $chm;
   $str['max_str']= $max_str;
	 return $str;
}

function get_year(){
	$timezone = post_views_timezone();
	$dateTime = new DateTime(date('Y-m-d H:i:s'));
	$dateTime->setTimezone(new DateTimeZone($timezone));
	$year = (int)($dateTime->format("Y"));
	return $year;
}

function post_views_timezone(){
	$timezone = get_option('timezone_string');
	if (empty($timezone)){
		$x = floor(get_option('gmt_offset'));
		if ($x>0){
			 $x="-".$x;
		}else{
			$x= "+".-$x;
		}
		$timezone = "Etc/GMT$x";
	}
	return $timezone;
}

function get_time_diff($time1, $time2){
	$diff = floor($time1 - $time2);
	if ($diff<1){
		return __('Right now','post-views');
	}else{
		if ($diff<60){
	 			if($diff == 1){
						return $diff.' '.__('second ago','post-views');
				}else{
						return $diff.' '.__('seconds ago','post-views');
				}
		}else{
				$diff = floor($diff/60);
				if ($diff<60){
						if($diff == 1){
								return $diff.' '.__('minute ago','post-views');
						}else{
								return $diff.' '.__('minutes ago','post-views');
						}
				}else{
						$diff = floor($diff/60);
						if ($diff<24){
								if($diff == 1){
										return $diff.' '.__('hour ago','post-views');
								}else{
										return $diff.' '.__('hours ago','post-views');
								}
						}else{
								$diff = floor($diff/24);
								if ($diff<7){
										if($diff == 1){
												return $diff.' '.__('day ago','post-views');
										}else{
											  return $diff.' '.__('days ago','post-views');
										}
								}else{
										$diff_1 = floor($diff/7);
										if($diff_1 < 5){
												if($diff_1 == 1){
														return $diff_1.' '.__('week ago','post-views');
												}else{
														return $diff_1.' '.__('weeks ago','post-views');
												}
										}else{
												$diff_2 = floor($diff/31);
											  if($diff_2 == 1){
														return $diff_2.' '.__('month ago','post-views');
												}else{
														return $diff_2.' '.__('months ago','post-views');
												}
											
										}
								}
						}
				}
		}
	}
}

function post_views_localtime($utcTime,$timeZone,$format = 'Y-m-d H:i:s'){
	$dateTime = new DateTime($utcTime);
	$dateTime->setTimezone(new DateTimeZone($timeZone));
	return $dateTime->format($format);
}

function get_time_span($old_time){
	
	$time_span['diff_d'] = false;
	$time_span['diff_w'] = false;
	$time_span['diff_m'] = false;
	$time_span['diff_hy'] = false;
	$time_span['diff_y'] = false;
	
	if(!empty($old_time)){	 
	 
		$new_time = gmdate("Y-n-d H:i:s");
		
		$timezone = post_views_timezone();
		$dateTime1 = new DateTime($new_time);
		$dateTime2 = new DateTime($old_time);
		
		$dateTime1->setTimezone(new DateTimeZone($timezone));
		$dateTime2->setTimezone(new DateTimeZone($timezone));
 	
		$date1 =  (int)($dateTime1->format("z"));
		$date2 =  (int)($dateTime2->format("z"));
		$diff = $date1 - $date2;
		$time_span['aa']= $date1.'|-|-|'.$date2;
		if($diff != 0 ) $time_span['diff_d'] = true;
 	
		
		$date1 =  (int)($dateTime1->format("W"));			
	 	$date2 =  (int)($dateTime2->format("W"));
	 	$diff = $date1 - $date2;
	 	if($diff != 0 ) $time_span['diff_w'] = true;
	 	
	 	$date1 =  (int)($dateTime1->format("n"));			
	 	$date2 =  (int)($dateTime2->format("n"));
	 	$diff = $date1 - $date2;
	 	if($diff != 0 ) $time_span['diff_m'] = true;
	 	
	 	$diff_1 = $date1 - 6;
	 	$diff_2 = $date2 - 6;
	 	$diff_3 = $date1 - 0;
	 	$diff_4 = $date2 - 0;
   	
	 	if((($diff_1 > 0) && ($diff_2 < 1)) || (($diff_3 < 7) && ($diff_4 > 6))) $time_span['diff_hy'] = true;
	 	
	 	$date1 =  (int)($dateTime1->format("Y"));			
	 	$date2 =  (int)($dateTime2->format("Y"));
	 	$diff = $date1 - $date2;
	 	if($diff != 0 ) $time_span['diff_y'] = true;
	}
	return $time_span;	 	
}


if(!function_exists('snippet_text')) {
	function snippet_text($text, $length = 0) {
		if (defined('MB_OVERLOAD_STRING')) {
		  $text = @html_entity_decode($text, ENT_QUOTES, get_option('blog_charset'));
		 	if (mb_strlen($text) > $length) {
				return htmlentities(mb_substr($text,0,$length), ENT_COMPAT, get_option('blog_charset')).'...';
		 	} else {
				return htmlentities($text, ENT_COMPAT, get_option('blog_charset'));
		 	}
		} else {
			$text = @html_entity_decode($text, ENT_QUOTES, get_option('blog_charset'));
		 	if (strlen($text) > $length) {
				return htmlentities(substr($text,0,$length), ENT_COMPAT, get_option('blog_charset')).'...';
		 	} else {
				return htmlentities($text, ENT_COMPAT, get_option('blog_charset'));
		 	}
		}
	}
}

if(!function_exists(is_robot)){
	function is_robot($agent) {
		
		$is_robot = false;		
		if ($agent ==''){
			$is_robot = true;
		}
		if (strlen($agent) < 50){
			$is_robot = true;
		}
		$robots = array('Google Bot' => 'google', 'MSN Bot' => 'msn', 'Baidu Bot' => 'baidu', 'YaHoo Bot' => 'yahoo', 'Alexa' =>'alexa','SoSo Bot' => 'soso', 'Sogou Bot' => 'sogou', 'Spider Bot' => 'spider', 'Bot' => 'bot', 'Search Bot' => 'search', 'Alexa' => 'alexa', 'Rss Reader' => 'feed', 'Rss Reader' => 'rss', 'CIBA' => 'ciba');
		foreach ($robots as $name => $lookfor) { 
			if (stristr($agent, $lookfor) !== false) { 
					$is_robot = true;
					break;
			}
		}
		return $is_robot;
	}
}


function get_post_viewed_coverage($view_type = 'normal',$time_span = 'total',$output_type = 'content',$direction = '+',$percent = ''){
	global $wpdb;
	$post_count = $wpdb->get_var($wpdb->prepare("SELECT count(ID) FROM ".WP_POST_TABLE." where post_date < '".current_time('mysql')."' AND post_status = 'publish' AND post_password = ''", APP_POST_TYPE));
  $need_count =  get_post_viewed_count($view_type,$time_span,$output_type);
	if($direction == '+'){
		if($percent == '%'){
			return get_percent($need_count / $post_count);
		}else if($percent == '100'){
				return ceil( ($need_count / $post_count) * 100 ) ;
		}else{			
		 	return number_format(($need_count / $post_count), 2, '.', '');       	
		}
	}else{
		if($percent == '%'){
			return get_percent( 1 - ($need_count / $post_count) );
		}else if($percent == '100'){
				return ceil( (1 - ($need_count / $post_count)) * 100 ) ;
		}else{
			return number_format((1 - ($need_count / $post_count)), 2, '.', '');       	
		}
	}
}

/********************************** Post Views Summary *************************************/
function post_views_summary(){
	global $wpdb;
	
	if(!empty($_REQUEST['view_type'])){
  		$view_type = $_REQUEST['view_type'];		
  }else{
			$view_type = "normal";
  }
  
  if(!empty($_REQUEST['summary_type'])){
  		$summary_type = $_REQUEST['summary_type'];		
  }else{
			$summary_type = "coverage";
  }
  
  if(!empty($_REQUEST['output_type'])){
  		$output_type = $_REQUEST['output_type'];		
  }else{
			$output_type = "content";
  }
  
  if(!empty($_REQUEST['year_id'])){
  		$year_id = $_REQUEST['year_id'];		
  }else{
			$year_id = get_year();
  }
  
  if($year_id == get_year()){
		$column = 3;
	}else{
		$column = 4;	
	}
?>
<div class="wrap">
<?php screen_icon('users');
?><h2><?php _e('Summary','post-views'); ?></h2>
<?php
$pv_menu = "
							<a href=\"".PV_ADMIN_URL."&pv_page=analytics\">".__('Analytics', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=summary\">".__('Summary','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=ranking\">".__('Ranking','post-views')."</a>&nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=category\">".__('Category','post-views')."</a>&nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\">".__('Donation','post-views')."</a>&nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\" target=\"_blank\">".__('Help','post-views')."</a>
	            ";

echo $pv_menu; 
?>
</p>
<div align="right">
<form name="filterform" id="filterform" method="get" action="<?php echo admin_url('index.php'); ?>">
<input type="hidden" name="page" value="post-views">
<input type="hidden" name="pv_page" value="summary">

<select name='year_id' id='year_id'>
<?php
$sql = 'SELECT DISTINCT year_id from '.WP_POST_VIEWS_SUMMARY_TABLE;
$year_id_rst = $wpdb->get_results($sql);

foreach ($year_id_rst as $year_ids) {
		if ( $year_ids->year_id == $year_id ){
				$default = ' selected="selected"';
		}else{
				$default = '';
		}
	echo "<option ".$default." value='" .$year_ids->year_id. "'>".$year_ids->year_id."</option>\n";
}
?>
</select>

<input type="hidden" name="view_type" id="view_type" value="normal" />
<select name="output_type" id="output_type">
		<option <?php selected($output_type, "content" ); ?> value="content"><?php _e('Read', 'post-views'); ?></option>
		<option <?php selected($output_type, "excerpt" ); ?> value="excerpt"><?php _e('Preview', 'post-views'); ?></option>
</select>
<select name="summary_type" id="summary_type">
		<option <?php selected($summary_type, "coverage" ); ?> value="coverage"><?php _e('Viewed Coverage', 'post-views'); ?></option>
		<option <?php selected($summary_type, "trend" ); ?> value="trend"><?php _e('Views Summation', 'post-views'); ?></option>
		<option <?php selected($summary_type, "viewed" ); ?> value="viewed"><?php _e('Viewed Posts', 'post-views'); ?></option>
</select>
<input type="submit" value="<?php echo _e('Switch','post-views'); ?>" class="button" />
</form>
</div>
</p>
<table class="widefat page fixed" width="100%" cellpadding="0" cellspacing="0">
<?php
	$post_count = $wpdb->get_var($wpdb->prepare("SELECT count(ID) FROM ".WP_POST_TABLE." where post_date < '".current_time('mysql')."' AND post_status = 'publish' AND post_password = ''", APP_POST_TYPE));
	
	$today_count =  get_post_viewed_count($view_type,'today',$output_type);
	$week_count =  get_post_viewed_count($view_type,'week',$output_type);
	$month_count =  get_post_viewed_count($view_type,'month',$output_type);
	$halfyear_count =  get_post_viewed_count($view_type,'halfyear',$output_type);
	$year_count =  get_post_viewed_count($view_type,'year',$output_type);
	$total_count =  get_post_viewed_count($view_type,'total',$output_type);
	
	$today_sum =  get_post_views_sum($view_type,'today',$output_type);
	$week_sum =  get_post_views_sum($view_type,'week',$output_type);
	$month_sum =  get_post_views_sum($view_type,'month',$output_type);
	$halfyear_sum =  get_post_views_sum($view_type,'halfyear',$output_type);
	$year_sum =  get_post_views_sum($view_type,'year',$output_type);
	$total_sum =  get_post_views_sum($view_type,'total',$output_type);

  if($summary_type == 'coverage'){	
  		$today_coverage_trend =  get_post_viewed_coverage_trend($view_type,'today',$output_type,$year_id);
  		$today_coverage_trend_str = get_graphy_str($today_coverage_trend,get_post_viewed_coverage($view_type,'today',$output_type,'+','100'),100);

  		$week_coverage_trend =  get_post_viewed_coverage_trend($view_type,'week',$output_type,$year_id);
  		$week_coverage_trend_str = get_graphy_str($week_coverage_trend,get_post_viewed_coverage($view_type,'week',$output_type,'+','100'),100);
  
  		$month_coverage_trend =  get_post_viewed_coverage_trend($view_type,'month',$output_type,$year_id);
  		$month_coverage_trend_str = get_graphy_str($month_coverage_trend,get_post_viewed_coverage($view_type,'month',$output_type,'+','100'),100);

			if($column == 3){
?>
	<thead>
	<tr>			

				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( Today )','post-views') ?></th>
				<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Viewed Coverage (%)','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'+','100');?>,<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'-','100');?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x180&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $today_coverage_trend_str['max_str']; ?>&amp;chds=0,<?php echo $today_coverage_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $today_coverage_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $today_coverage_trend_str['chm'];?>&amp;chd=t:<?php echo $today_coverage_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>

	<thead>
	<tr>				
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( This Week )','post-views') ?></th>
				<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Viewed Coverage (%)','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'+','100');?>,<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'-','100');?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $week_coverage_trend_str['max_str']; ?>&amp;chds=0,<?php echo $week_coverage_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $week_coverage_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $week_coverage_trend_str['chm'];?>&amp;chd=t:<?php echo $week_coverage_trend_str['data_str']; ?>" alt="" width="100%" height="180" >
	</tr>
	<thead>
	<tr>	<th class="manage-column" scope="col" ><?php _e('Viewed Coverage ( This Month )','post-views') ?></th>
				<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Viewed Coverage (%)','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'+','100');?>,<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'-','100');?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $month_coverage_trend_str['max_str']; ?>&amp;chds=0,<?php echo $month_coverage_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $month_coverage_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $month_coverage_trend_str['chm'];?>&amp;chd=t:<?php echo $month_coverage_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>

	<thead>
	<tr>				
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( This Half Year )','post-views') ?></th>
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( This Year )','post-views') ?></th>
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( All Past Days )','post-views') ?></th>
				<th class="manage-column" scope="col"><?php _e('Views Contribution ( This Year )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=t:<?php echo $halfyear_count;?>,<?php echo $post_count - $halfyear_count;?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'halfyear',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'halfyear',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td><img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=t:<?php echo $year_count;?>,<?php echo $post_count - $year_count;?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'year',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'year',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td><img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=t:<?php echo $total_count;?>,<?php echo $post_count - $total_count;?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'total',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'total',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td><img src="https://chart.googleapis.com/chart?cht=bvs&amp;chbh=r,0.2,0.5&amp;chd=t:<?php echo $today_sum;?>,<?php echo $week_sum;?>,<?php echo $month_sum;?>,<?php echo $halfyear_sum;?>|<?php echo ($week_sum - $today_sum);?>,<?php echo ($month_sum - $week_sum);?>,<?php echo ($halfyear_sum - $month_sum);?>,<?php echo ($year_sum - $halfyear_sum);?>&amp;chds=0,<?php echo $year_sum; ?>&amp;chxr=1,0,<?php echo $year_sum; ?>&amp;chs=270x140&amp;chxt=x,y&amp;chxp=1,<?php echo $month_sum;?><?php if($year_sum != $month_sum) echo ",".$year_sum;?>&amp;chxl=0:|T / W|W / M|M / HY|HY / Y||1:|<?php echo number_format($month_sum);?><?php if($year_sum != $month_sum) echo "|".number_format($year_sum);?>&amp;chco=206582,C6D9FD"></td>
	</tr>
<?php
	}else{
?>
	<thead>
	<tr>			
				<th class="manage-column" scope="col" colspan="4"><?php _e('Historical Viewed Coverage (%)','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td colspan="4"><img src="http://chart.apis.google.com/chart?chs=1000x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $today_coverage_trend_str['max_str']; ?>&amp;chds=0,<?php echo $today_coverage_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $today_coverage_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $today_coverage_trend_str['chm'];?>&amp;chd=t:<?php echo $today_coverage_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>

	<thead>
	<tr>				
				<th class="manage-column" scope="col" colspan="4"><?php _e('Historical Viewed Coverage (%)','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td colspan="4"><img src="http://chart.apis.google.com/chart?chs=1000x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $week_coverage_trend_str['max_str']; ?>&amp;chds=0,<?php echo $week_coverage_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $week_coverage_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $week_coverage_trend_str['chm'];?>&amp;chd=t:<?php echo $week_coverage_trend_str['data_str']; ?>" alt="" width="100%" height="180" >
	</tr>
	<thead>
				<th class="manage-column" scope="col"  colspan="4"><?php _e('Historical Viewed Coverage (%)','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td colspan="4"><img src="http://chart.apis.google.com/chart?chs=1000x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $month_coverage_trend_str['max_str']; ?>&amp;chds=0,<?php echo $month_coverage_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $month_coverage_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $month_coverage_trend_str['chm'];?>&amp;chd=t:<?php echo $month_coverage_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
<?php
	}
}else if($summary_type == 'trend'){		
		$today_trend =  get_post_views_trend($view_type,'today',$output_type,$year_id);
		$week_trend =  get_post_views_trend($view_type,'week',$output_type,$year_id);
		$month_trend =  get_post_views_trend($view_type,'month',$output_type,$year_id);
		$halfyear_trend =  get_post_views_trend($view_type,'halfyear',$output_type,$year_id);
		$year_trend =  get_post_views_trend($view_type,'year',$output_type,$year_id);
  	
  	$today_trend_str= get_graphy_str($today_trend,$today_sum);
  	$week_trend_str= get_graphy_str($week_trend,$week_sum);
  	$month_trend_str= get_graphy_str($month_trend,$month_sum);
  
  	if($column == 3){
?>
	<thead>
	<tr>				
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( Today )','post-views') ?></th>
				<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Views Summation ( Day )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'+','100');?>,<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'-','100');?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x180&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $today_trend_str['max_str']; ?>&amp;chds=0,<?php echo $today_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $today_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $today_trend_str['chm'];?>&amp;chd=t:<?php echo $today_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
	<thead>
	<tr>				
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( This Week )','post-views') ?></th>
				<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Views Summation ( Week )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'+','100');?>,<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'-','100');?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $week_trend_str['max_str']; ?>&amp;chds=0,<?php echo $week_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $week_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $week_trend_str['chm'];?>&amp;chd=t:<?php echo $week_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
	<thead>
	<tr>	<th class="manage-column" scope="col" ><?php _e('Viewed Coverage ( This Month )','post-views') ?></th>
				<th class="manage-column" scope="col"  colspan="3"><?php _e('Historical Views Summation ( Month )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'+','100');?>,<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'-','100');?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $month_trend_str['max_str']; ?>&amp;chds=0,<?php echo $month_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $month_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $month_trend_str['chm'];?>&amp;chd=t:<?php echo $month_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>

	<thead>
	<tr>				
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( This Half Year )','post-views') ?></th>
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( This Year )','post-views') ?></th>
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( All Past Days )','post-views') ?></th>
				<th class="manage-column" scope="col"><?php _e('Views Contribution ( This Year )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=t:<?php echo $halfyear_count;?>,<?php echo $post_count - $halfyear_count;?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'halfyear',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'halfyear',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td><img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=t:<?php echo $year_count;?>,<?php echo $post_count - $year_count;?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'year',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'year',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td><img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=t:<?php echo $total_count;?>,<?php echo $post_count - $total_count;?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'total',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'total',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td><img src="https://chart.googleapis.com/chart?cht=bvs&amp;chbh=r,0.2,0.5&amp;chd=t:<?php echo $today_sum;?>,<?php echo $week_sum;?>,<?php echo $month_sum;?>,<?php echo $halfyear_sum;?>|<?php echo ($week_sum - $today_sum);?>,<?php echo ($month_sum - $week_sum);?>,<?php echo ($halfyear_sum - $month_sum);?>,<?php echo ($year_sum - $halfyear_sum);?>&amp;chds=0,<?php echo $year_sum; ?>&amp;chxr=1,0,<?php echo $year_sum; ?>&amp;chs=270x140&amp;chxt=x,y&amp;chxp=1,<?php echo $month_sum;?><?php if($year_sum != $month_sum) echo ",".$year_sum;?>&amp;chxl=0:|T / W|W / M|M / HY|HY / Y||1:|<?php echo number_format($month_sum);?><?php if($year_sum != $month_sum) echo "|".number_format($year_sum);?>&amp;chco=206582,C6D9FD"></td>
	</tr>
<?php	
	}else{
?>
<thead>
	<tr>				
				<th class="manage-column" scope="col" colspan="4"><?php _e('Historical Views Summation ( Day )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td colspan="4"><img src="http://chart.apis.google.com/chart?chs=1000x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $today_trend_str['max_str']; ?>&amp;chds=0,<?php echo $today_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $today_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $today_trend_str['chm'];?>&amp;chd=t:<?php echo $today_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
	<thead>
	<tr>				
				<th class="manage-column" scope="col" colspan="4"><?php _e('Historical Views Summation ( Week )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td colspan="4"><img src="http://chart.apis.google.com/chart?chs=1000x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $week_trend_str['max_str']; ?>&amp;chds=0,<?php echo $week_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $week_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $week_trend_str['chm'];?>&amp;chd=t:<?php echo $week_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
	<thead>
	<tr>
				<th class="manage-column" scope="col"  colspan="4"><?php _e('Historical Views Summation ( Month )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td colspan="4"><img src="http://chart.apis.google.com/chart?chs=1000x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $month_trend_str['max_str']; ?>&amp;chds=0,<?php echo $month_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $month_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $month_trend_str['chm'];?>&amp;chd=t:<?php echo $month_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>	
<?php
	}
}else{
		$today_trend =  get_post_viewed_count_trend($view_type,'today',$output_type,$year_id);
		$week_trend =  get_post_viewed_count_trend($view_type,'week',$output_type,$year_id);
		$month_trend =  get_post_viewed_count_trend($view_type,'month',$output_type,$year_id);
		$halfyear_trend =  get_post_viewed_count_trend($view_type,'halfyear',$output_type,$year_id);
		$year_trend =  get_post_viewed_count_trend($view_type,'year',$output_type,$year_id);
  	
  	$today_trend_str= get_graphy_str($today_trend,$today_count);
  	$week_trend_str= get_graphy_str($week_trend,$week_count);
  	$month_trend_str= get_graphy_str($month_trend,$month_count);  
  	
  	if($column == 3){
?>
<thead>
	<tr>				
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( Today )','post-views') ?></th>
				<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Viewed Posts ( Day )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'+','100');?>,<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'-','100');?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x180&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'today',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $today_trend_str['max_str']; ?>&amp;chds=0,<?php echo $today_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $today_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $today_trend_str['chm'];?>&amp;chd=t:<?php echo $today_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>

	<thead>
	<tr>				
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( This Week )','post-views') ?></th>
				<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Viewed Posts ( Week )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'+','100');?>,<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'-','100');?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'week',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $week_trend_str['max_str']; ?>&amp;chds=0,<?php echo $week_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $week_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $week_trend_str['chm'];?>&amp;chd=t:<?php echo $week_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
	<thead>
	<tr>	<th class="manage-column" scope="col" ><?php _e('Viewed Coverage ( This Month )','post-views') ?></th>
				<th class="manage-column" scope="col"  colspan="3"><?php _e('Historical Viewed Posts ( Month )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'+','100');?>,<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'-','100');?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'month',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $month_trend_str['max_str']; ?>&amp;chds=0,<?php echo $month_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $month_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $month_trend_str['chm'];?>&amp;chd=t:<?php echo $month_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>

	<thead>
	<tr>				
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( This Half Year )','post-views') ?></th>
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( This Year )','post-views') ?></th>
				<th class="manage-column" scope="col"><?php _e('Viewed Coverage ( All Past Days )','post-views') ?></th>
				<th class="manage-column" scope="col"><?php _e('Views Contribution ( This Year )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td><img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=t:<?php echo $halfyear_count;?>,<?php echo $post_count - $halfyear_count;?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'halfyear',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'halfyear',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td><img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=t:<?php echo $year_count;?>,<?php echo $post_count - $year_count;?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'year',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'year',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td><img src="https://chart.googleapis.com/chart?cht=p3&amp;chd=t:<?php echo $total_count;?>,<?php echo $post_count - $total_count;?>&amp;chds=0,<?php echo $post_count; ?>&amp;chs=250x160&amp;chdl=<?php echo get_post_viewed_coverage($view_type,'total',$output_type,'+','%'); ?> Read|<?php echo get_post_viewed_coverage($view_type,'total',$output_type,'-','%');?> Unread&amp;chco=206582"></td>
				<td><img src="https://chart.googleapis.com/chart?cht=bvs&amp;chbh=r,0.2,0.5&amp;chd=t:<?php echo $today_sum;?>,<?php echo $week_sum;?>,<?php echo $month_sum;?>,<?php echo $halfyear_sum;?>|<?php echo ($week_sum - $today_sum);?>,<?php echo ($month_sum - $week_sum);?>,<?php echo ($halfyear_sum - $month_sum);?>,<?php echo ($year_sum - $halfyear_sum);?>&amp;chds=0,<?php echo $year_sum; ?>&amp;chxr=1,0,<?php echo $year_sum; ?>&amp;chs=270x140&amp;chxt=x,y&amp;chxp=1,<?php echo $month_sum;?><?php if($year_sum != $month_sum) echo ",".$year_sum;?>&amp;chxl=0:|T / W|W / M|M / HY|HY / Y||1:|<?php echo number_format($month_sum);?><?php if($year_sum != $month_sum) echo "|".number_format($year_sum);?>&amp;chco=206582,C6D9FD"></td>
	</tr>
<?php	
	}else{
?>
<thead>
	<tr>				
				<th class="manage-column" scope="col" colspan="4"><?php _e('Historical Viewed Posts ( Day )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td colspan="4"><img src="http://chart.apis.google.com/chart?chs=1000x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $today_trend_str['max_str']; ?>&amp;chds=0,<?php echo $today_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $today_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $today_trend_str['chm'];?>&amp;chd=t:<?php echo $today_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>

	<thead>
	<tr>				
				<th class="manage-column" scope="col" colspan="4"><?php _e('Historical Viewed Posts ( Week )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td colspan="4"><img src="http://chart.apis.google.com/chart?chs=1000x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $week_trend_str['max_str']; ?>&amp;chds=0,<?php echo $week_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $week_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $week_trend_str['chm'];?>&amp;chd=t:<?php echo $week_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
	<thead>
	<tr>
				<th class="manage-column" scope="col"  colspan="4"><?php _e('Historical Viewed Posts ( Month )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
				<td colspan="4"><img src="http://chart.apis.google.com/chart?chs=1000x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $month_trend_str['max_str']; ?>&amp;chds=0,<?php echo $month_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $month_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $month_trend_str['chm'];?>&amp;chd=t:<?php echo $month_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
<?php
	}
}
?>	
	</table>
</div>
<?php
}

/********************************** Post Views Analytics *************************************/

function post_views_analytics(){
	global $wpdb,$wp_locale;
	
	check_post_views_update_lock(); // check whether the update having been lock
	
	if($_GET['m'] != 0 ){
  		$date_str = " CONCAT( YEAR( post_date ) , MONTH( post_date ) ) = ".$_GET['m'];  	
  }else{
  		$date_str = " 1=1 ";
  }
  
  if($_GET['cat'] != 0 ){
  		$use_cat = "INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";
  		$cat = " $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id = ".(int)$_GET['cat'];  	
  }else{
  	  $use_cat = " ";
  		$cat = " 1=1 ";
  }
    
  if(!empty($_GET['order_type'])){
  	if($_GET['order_type'] == "id"){
  		$order_type = " ORDER  BY post_id DESC";
  	}else if($_GET['order_type'] == "latest"){
  		$order_type = " ORDER  BY latest_view_time DESC";
  	}else if($_GET['order_type'] == "today"){
  		$order_type = " ORDER  BY post_views_today DESC";
  	}else if($_GET['order_type'] == "week"){
  		$order_type = " ORDER  BY post_views_week DESC";
  	}else if($_GET['order_type'] == "month"){
  		$order_type = " ORDER  BY post_views_month DESC";
  	}else if($_GET['order_type'] == "halfyear"){
  		$order_type = " ORDER  BY post_views_halfyear DESC";
  	}else if($_GET['order_type'] == "year"){
  		$order_type = " ORDER  BY post_views_year DESC";
  	}else if($_GET['order_type'] == "total"){
  		$order_type = " ORDER  BY post_views_total DESC";
  	}
  }else{
  		$order_type = " ORDER  BY latest_view_time DESC";
  }
  
  if(!empty($_GET['view_type'])){
  		$view_type = sanitize_text_field($_GET['view_type']);		
  }else{
			$view_type = "normal";
  }
  
  if(!empty($_GET['output_type'])){
  		$output_type = sanitize_text_field($_GET['output_type']);		
  }else{
			$output_type = "content";
  }
  
  if(!empty($_GET['search_input'])){
  		$search_str = " (post_title like '%".like_escape(sanitize_text_field($_GET['search_input']))."%' OR post_content like '%".like_escape(sanitize_text_field($_GET['search_input']))."%')";
  		$sql= $wpdb->prepare("SELECT DISTINCT ".WP_POST_VIEWS_TABLE.".* FROM ".WP_POST_VIEWS_TABLE." 
         LEFT JOIN $wpdb->posts ON ".WP_POST_VIEWS_TABLE.".post_id = $wpdb->posts.ID   
         $use_cat
         WHERE post_date < '".current_time('mysql')."' 
         AND post_status = 'publish' 
         AND $cat 
         AND view_type = '".$view_type."' 
         AND output_type = '".$output_type."'
         AND post_password = '' 
         AND $date_str
         AND (post_title like %s OR post_content like %s)
         $order_type ",
         '%'.like_escape(sanitize_text_field($_GET['search_input'])).'%',
         '%'.like_escape(sanitize_text_field($_GET['search_input'])).'%');
  }else{
  	 	$sql= $wpdb->prepare("SELECT DISTINCT ".WP_POST_VIEWS_TABLE.".* FROM ".WP_POST_VIEWS_TABLE." 
         LEFT JOIN $wpdb->posts ON ".WP_POST_VIEWS_TABLE.".post_id = $wpdb->posts.ID   
         $use_cat
         WHERE post_date < '".current_time('mysql')."' 
         AND post_status = 'publish' 
         AND $cat 
         AND view_type = '".$view_type."' 
         AND output_type = '".$output_type."'
         AND post_password = '' 
         AND $date_str
         $order_type ", APP_POST_TYPE);
  }


  
	//$sql = "SELECT * FROM " . WP_POST_VIEWS_TABLE . " ORDER BY post_views_today DESC";	
	$post_data = $wpdb->get_results($sql);	
?>
<div class="wrap">
<?php screen_icon('users');?>
<h2><?php _e('Analytics','post-views');?></h2>
<?php
$pv_menu = "
							<a href=\"".PV_ADMIN_URL."&pv_page=analytics\">".__('Analytics','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=summary\">".__('Summary','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=ranking\">".__('Ranking', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=category\">".__('Category', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=options\">".__('Options', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\">".__('Donation','post-views')."</a>&nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\" target=\"_blank\">".__('Help','post-views')."</a>
	            ";

echo $pv_menu; 
?><p>
<div align="left">
<form name="filterform" id="filterform" method="get" action="<?php echo admin_url('index.php'); ?>">
	<input type="hidden" name="page" value="post-views">
	<input type="hidden" name="pv_page" value="analytics">
<?php
  //echo $sql;
			$dropdown_options = array('show_option_all' => __('View all categories', 'post-views'), 'hide_empty' => 0, 'hierarchical' => 1,
		'show_count' => 0, 'orderby' => 'name', 'selected' => $_GET['cat']);
	wp_dropdown_categories($dropdown_options);

$arc_query = $wpdb->prepare("SELECT DISTINCT YEAR(post_date) AS yyear, MONTH(post_date) AS mmonth FROM $wpdb->posts ORDER BY post_date DESC", APP_POST_TYPE);
$arc_result = $wpdb->get_results( $arc_query );
$month_count = count($arc_result);
if ( $month_count && !( 1 == $month_count && 0 == $arc_result[0]->mmonth ) ) {
$m = isset($_GET['m']) ? (int)$_GET['m'] : 0;
 ?>
<select name='m'>
<option<?php selected( $m, 0 ); ?> value='0'><?php _e('Show all dates', 'post-views'); ?></option>
<?php
foreach ($arc_result as $arc_row) {
	if ( $arc_row->yyear == 0 )
		continue;
	if ( $arc_row->yyear . $arc_row->mmonth == $m )
		$default = ' selected="selected"';
	else
		$default = '';

	echo "<option$default value='" . esc_attr("$arc_row->yyear$arc_row->mmonth") . "'>";
	echo $wp_locale->get_month($arc_row->mmonth) . " $arc_row->yyear";
	echo "</option>\n";
}
}
?>
</select>
<select name="order_type" id="order_type">
		<option <?php selected( $_GET['order_type'], "latest" ); ?> value="latest"><?php _e('Order by Last Viewed', 'post-views'); ?></option>
		<option <?php selected( $_GET['order_type'], "id" ); ?> value="id"><?php _e('Order by Post ID', 'post-views'); ?></option>		
		<option <?php selected( $_GET['order_type'], "today" ); ?> value="today"><?php _e('Order by Today', 'post-views'); ?></option>
		<option <?php selected( $_GET['order_type'], "week" ); ?> value="week"><?php _e('Order by Week', 'post-views'); ?></option>
		<option <?php selected( $_GET['order_type'], "month" ); ?> value="month"><?php _e('Order by Month', 'post-views'); ?></option>
		<option <?php selected( $_GET['order_type'], "halfyear" ); ?> value="halfyear"><?php _e('Order by Half Year', 'post-views'); ?></option>
		<option <?php selected( $_GET['order_type'], "year" ); ?> value="year"><?php _e('Order by Year', 'post-views'); ?></option>
		<option <?php selected( $_GET['order_type'], "total" ); ?> value="total"><?php _e('Order by Total', 'post-views'); ?></option>
</select>
<select name="view_type" id="view_type">
		<option <?php selected( $_GET['view_type'], "normal" ); ?> value="normal"><?php _e('Visitor', 'post-views'); ?></option>
		<option <?php selected( $_GET['view_type'], "robot" ); ?> value="robot"><?php _e('Robot', 'post-views'); ?></option>
</select>
<select name="output_type" id="output_type">
		<option <?php selected( $_GET['output_type'], "content" ); ?> value="content"><?php _e('Read', 'post-views'); ?></option>
		<option <?php selected( $_GET['output_type'], "excerpt" ); ?> value="excerpt"><?php _e('Preview', 'post-views'); ?></option>
</select>
<input type="hidden" name="filter" value="1">
<input type="submit" id="views-query-submit" value="<?php _e('Filter','post-views'); ?>" class="button-secondary" />
<input type="text" id="search_input" name="search_input" value="<?php if(isset($_GET['search_input'])) echo sanitize_text_field($_GET['search_input']); ?>" />
<input type="hidden" name="search" value="1">
<input type="submit" value="<?php echo _e('Search','post-views'); ?>" class="button" />

<div class="view-switch">
	<a href="<?php echo esc_url(add_query_arg('mode', 'list', $_SERVER['REQUEST_URI'])) ?>"><img <?php if ( ('list' == $_GET['mode']) or (empty($_GET['mode'])) ) echo 'class="current"'; ?> id="view-switch-list" src="<?php echo esc_url( includes_url( 'images/blank.gif' ) ); ?>" width="20" height="20" title="<?php _e('List View') ?>" alt="<?php _e('List View') ?>" /></a>
	<a href="<?php echo esc_url(add_query_arg('mode', 'graphy', $_SERVER['REQUEST_URI'])) ?>"><img <?php if ( 'graphy' == $_GET['mode'] ) echo 'class="current"'; ?> id="view-switch-excerpt" src="<?php echo esc_url( includes_url( 'images/blank.gif' ) ); ?>" width="20" height="20" title="<?php _e('Trend View') ?>" alt="<?php _e('Trend View') ?>" /></a>
</div>
</form>
</div>
</p>
<table class="widefat page fixed" width="100%" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
				<th class="manage-column" scope="col" width="5%"><?php _e('ID','post-views') ?></th>
				<th class="manage-column" scope="col" width="20%"><?php _e('Title','post-views') ?></th>
				<th class="manage-column" scope="col" width="15%"><?php _e('Last Viewed Time','post-views') ?></th>
				<th class="manage-column" scope="col" width="10%"><?php _e('Today','post-views') ?></th>
				<th class="manage-column" scope="col" width="10%"><?php _e('Week','post-views') ?></th>
				<th class="manage-column" scope="col" width="10%"><?php _e('Month','post-views') ?></th>
		    <th class="manage-column" scope="col" width="10%"><?php _e('Half Year ','post-views') ?> </th>
		    <th class="manage-column" scope="col" width="10%"><?php _e('Year','post-views') ?></th>
				<th class="manage-column" scope="col" width="10%"><?php _e('Total','post-views') ?></th>
		</tr>
	</thead>
<?php 

$filterstring .="&amp;m=".$_GET['m']."&amp;mode=".$_GET['mode']."&amp;cat=".$_GET['cat']."&amp;order_type=".$_GET['order_type']."&amp;view_type=".$_GET['view_type']."&amp;search_input=".$_GET['search_input'];
if(isset($_GET['page_no'])){
	 $page_no = $_GET['page_no'];
}else{
	 $page_no = 1;
}
if ( !empty($post_data) ){
		$total	=	count($post_data);
		if(isset($_GET['mode']) && ($_GET['mode'] == "graphy")){
				$per_page = 4;
		}else{
			  $per_page = 30;
		}
		$show_flag = 0;
 		foreach($post_data as $pd){
 				$show_flag = $show_flag + 1;
				if($show_flag > (($page_no-1)*$per_page) and $show_flag <= ($page_no*$per_page)){
						$title = get_the_title($pd->post_id);
?>
 	<tr>
			<td width="5%"><?php echo $pd->post_id; ?></th>
			<td width="20%"><a href="<?php echo get_permalink($pd->post_id);?>" target="_blank"><?php echo $title; ?></></td>
			<td width="15%"><?php echo get_time_diff(time(),strtotime($pd->latest_view_time)); ?></td>
			<td width="10%"><a href="<?php echo PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pd->post_id;?>" target="_blank"><?php echo number_format($pd->post_views_today); ?></a></td>
			<td width="10%"><a href="<?php echo PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pd->post_id;?>" target="_blank"><?php echo number_format($pd->post_views_week); ?></a></td>
			<td width="10%"><a href="<?php echo PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pd->post_id;?>" target="_blank"><?php echo number_format($pd->post_views_month); ?></a></td>
			<td width="10%"><a href="<?php echo PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pd->post_id;?>" target="_blank"><?php echo number_format($pd->post_views_halfyear); ?></a></td>
			<td width="10%"><a href="<?php echo PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pd->post_id;?>" target="_blank"><?php echo number_format($pd->post_views_year); ?></a></td>
			<td width="10%"><a href="<?php echo PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pd->post_id;?>" target="_blank"><?php echo number_format($pd->post_views_total); ?></a></td>
	</tr>
<?php
if(isset($_GET['mode']) && ($_GET['mode'] == "graphy")){
  $sql = "SELECT post_views_today FROM ".WP_POST_VIEWS_HIS_TABLE." where post_id=".$pd->post_id." AND view_type='".$view_type."' and output_type= '".$output_type."' AND year_id=".get_year();
	$today_views_data = $wpdb->get_var($wpdb->prepare($sql, APP_POST_TYPE));	
	if(!empty($today_views_data))	{	
		 $today_trend_str= get_graphy_str($today_views_data,$pd->post_views_today);
?>
<tr><td colspan="9">
			<img src="http://chart.apis.google.com/chart?chs=1000x200&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $today_trend_str['max_str']; ?>&amp;chds=0,<?php echo $today_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $today_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $today_trend_str['chm'];?>&amp;chd=t:<?php echo $today_trend_str['data_str']; ?>" alt="" width="100%" >
  </td></tr>
<?php
	}
	$data_str = "";
	$label_str = "";
} 
			}
	}
?><tfoot>
			<tr>
				<th colspan="2"><a href="<?php echo PV_ADMIN_URL ?>&amp;pv_page=<?php echo $view_type;?>-<?php echo $output_type;?>-analytics&amp;page_no=1<?php echo $filterstring; ?>" > << <?php _e('First','post-views');?></a></th>
				<th colspan="1"><a href="<?php echo PV_ADMIN_URL ?>&amp;pv_page=<?php echo $view_type;?>-<?php echo $output_type;?>-analytics&amp;page_no=<?php echo (($page_no-1)==0) ? 1 : ($page_no-1); echo $filterstring; ?>" > < <?php _e('PrePage','post-views');?></a></th>
				<th colspan="2"  align="center"><?php _e('Current','post-views');?> : <?php echo $page_no; ?>/<?php echo ceil($total/$per_page); ?></th>
				<th colspan="1"><a href="<?php echo PV_ADMIN_URL ?>&amp;pv_page=<?php echo $view_type;?>-<?php echo $output_type;?>-analytics&amp;page_no=<?php 
				if($page_no == ceil($total/$per_page)){
	  				echo $page_no;
	  		}else{ 
	  				echo $page_no+1 ;
	  		}
	  		echo $filterstring;
?>"><?php _e('NextPage','post-views');?> ></a></th>
			<th colspan="2"></th>
			<th colspan="1"><a href="<?php echo PV_ADMIN_URL ?>&amp;pv_page=<?php echo $view_type;?>-<?php echo $output_type;?>-analytics&amp;page_no=<?php echo ceil($total/$per_page); echo $filterstring; ?>" > <?php _e('Last','post-views');?> >></a></th>
	</tr>
</tfoot>
<?php
}
echo '</table>';
echo '</div>';
}

/********************************** Post Views Option *************************************/

function post_views_options(){
	global $wpdb;
	echo '<div class="wrap">';
	screen_icon('users');
?>
<h2><?php _e('Options', 'post-views');?></h2>
<?php
$pv_menu = "
							<a href=\"".PV_ADMIN_URL."&pv_page=analytics\">".__('Analytics','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=summary\">".__('Summary','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=ranking\">".__('Ranking', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=category\">".__('Category', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\">".__('Donation','post-views')."</a>&nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\" target=\"_blank\">".__('Help','post-views')."</a>
	            ";

echo $pv_menu; 

	$pv_rec_options = get_option('post_views_rec_options');
	if (isset($_POST['update_setting'])) {
			$pv_rec_options['rec_option'] = intval($_POST['rec_option']);
			$pv_rec_options['rec_cookie'] = intval($_POST['rec_cookie']);
			$pv_rec_options['cookie_timeout'] = intval($_POST['cookie_timeout']);
			$pv_rec_options['cache_plugin'] = intval($_POST['cache_plugin']);
			$pv_rec_options['real_time_views'] = intval($_POST['real_time_views']);
  		
			update_option('post_views_rec_options', $pv_rec_options);
			echo '<div id="message" class="updated fade"><p>';
			echo _e('Setting Updated...','post-views');
			echo '</p></div>';
	}else if (isset($_POST['set_default'])) {
			$pv_rec_options['rec_option'] = 0;
			$pv_rec_options['rec_cookie'] = 0;
			$pv_rec_options['cookie_timeout'] = 60;
			$pv_rec_options['cache_plugin'] = 0;
			$pv_rec_options['real_time_views'] = 0;
			
			update_option('post_views_rec_options', $pv_rec_options);
			echo '<div id="message" class="updated fade"><p>';
			echo _e('Default setting loaded...','post-views');
			echo '</p></div>';
	}else if (isset($_POST['clear_history']) && is_numeric($_POST['year_id'])){
			$sql = "DELETE FROM ".WP_POST_VIEWS_HIS_TABLE." WHERE year_id = ".$_POST['year_id'];
			$wpdb->get_results($sql);
			echo '<div id="message" class="updated fade"><p>';
			echo _e('Records have been deleted','post-views');
			echo '</p></div>';
	}	
?>
<p>
<form method="post">
		<p>
			<?php _e('Logging include :', 'post-views'); ?>
			<select name="rec_option" size="1">
					<option value="0"<?php selected('0', $pv_rec_options['rec_option']); ?>><?php _e('Everyone', 'post-views'); ?></option>
					<option value="1"<?php selected('1', $pv_rec_options['rec_option']); ?>><?php _e('Guests Only', 'post-views'); ?></option>
					<option value="2"<?php selected('2', $pv_rec_options['rec_option']); ?>><?php _e('Registered Users Only', 'post-views'); ?></option>
			</select>
		</p>
		<p>
			<?php _e('Use Cookie :', 'post-views'); ?>
			<select name="rec_cookie" size="1">
				<option value="0"<?php selected('0', $pv_rec_options['rec_cookie']); ?>><?php _e('Not', 'post-views'); ?></option>
				<option value="1"<?php selected('1', $pv_rec_options['rec_cookie']); ?>><?php _e('Yes', 'post-views'); ?></option>
			</select>
			<?php _e('Cookie Timeout :', 'post-views'); ?>
			<input type="text" name="cookie_timeout" size="10" value="<?php echo $pv_rec_options['cookie_timeout'];?>"/ ><?php _e('minutes', 'post-views'); ?>
		</p>
		<p>
			<?php _e('Using Cache Plugin? ', 'post-views'); ?>
			<select name="cache_plugin" size="1">
					<option value="0"<?php selected('0', $pv_rec_options['cache_plugin']); ?>><?php _e('Not', 'post-views'); ?></option>
					<option value="1"<?php selected('1', $pv_rec_options['cache_plugin']); ?>><?php _e('Yes', 'post-views'); ?></option>
			</select>
			<?php _e('Show real time post views? ', 'post-views'); ?>
			<select name="real_time_views" size="1">
					<option value="0"<?php selected('0', $pv_rec_options['real_time_views']); ?>><?php _e('Not', 'post-views'); ?></option>
					<option value="1"<?php selected('1', $pv_rec_options['real_time_views']); ?>><?php _e('Yes', 'post-views'); ?></option>
			</select>
		</p>
	<div class="submit">
		<input type="submit" name="set_default" value="<?php _e('Load Default', 'post-views'); ?>" />
		<input type="submit" name="update_setting" value="<?php _e('Update Setting', 'post-views'); ?>" />
	</div>
</form>	
</p>
<h3 style="border-bottom:1px dashed #CCC;"></h3>
<p><strong>Historic Records Cleaner</strong></p>
<form name="cleaner_form" id="cleaner_form" method="post" action="<?php echo admin_url('index.php'); ?>?page=post-views&pv_page=options">
<Label><?php echo _e("Year","post-views")?>:&nbsp;</label><select name='year_id' id='year_id'>
<?php
$sql = 'SELECT DISTINCT year_id from '.WP_POST_VIEWS_HIS_TABLE;
$year_id_rst = $wpdb->get_results($sql);
$current_year = get_year();
foreach ($year_id_rst as $year_ids) {		
		if ( $year_ids->year_id != $current_year ){
				echo "<option value='" .$year_ids->year_id. "'>".$year_ids->year_id."</option>\n";
		}	
}
?>
</select>
<input type="submit" name="clear_history" value="<?php _e('Delete','post-views'); ?>" />
</form>
<?php
	echo '</div>';
}

/********************************** Post Views Ranking *************************************/

function post_views_list(){
	global $wpdb;
	
	if(empty($_GET['view_type'])){
  		$view_type = 'normal';
  }else{
  		$view_type = $_GET['view_type'];
  }
  
  if(empty($_GET['order_type'])){
  		$order_type = 'most';
  }else{
  		$order_type = $_GET['order_type'];
  }
  
  if(empty($_GET['output_type'])){
  		$output_type = 'content';
  }else{
  		$output_type = $_GET['output_type'];
  }
  
  if($_GET['cat'] != 0 ){
  		$cat = $_GET['cat'];
  }else{
  		$cat = 0;
  		$mode = 'both';
  }
  
  if(empty($_GET['per_page'])){
  		$per_page = 15;  	
  }else{
  		$per_page = $_GET['per_page'];
  }
?>
<div class="wrap">
<?php screen_icon('users');?>
<h2><?php _e('Ranking','post-views');?></h2>
<?php
$pv_menu = "
							<a href=\"".PV_ADMIN_URL."&pv_page=analytics\">".__('Analytics', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=summary\">".__('Summary','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=ranking\">".__('Ranking','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=category\">".__('Category','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\">".__('Donation','post-views')."</a>&nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\" target=\"_blank\">".__('Help','post-views')."</a>
	            ";

echo $pv_menu; 
?>
<p>
<div align="right">
<form name="filterform" id="filterform" method="get" action="<?php echo admin_url('index.php'); ?>">
<input type="hidden" name="page" value="post-views">
<input type="hidden" name="pv_page" value="ranking">
<?php
	$dropdown_options = array('show_option_all' => __('View all categories', 'post-views'), 'hide_empty' => 0, 'hierarchical' => 1,'show_count' => 0, 'orderby' => 'name', 'selected' => $cat);
	wp_dropdown_categories($dropdown_options);
?>
<select name="view_type" id="view_type">
		<option <?php selected($view_type, "normal" ); ?> value="normal"><?php _e('Visitor', 'post-views'); ?></option>
		<option <?php selected($view_type, "robot" ); ?> value="robot"><?php _e('Robot', 'post-views'); ?></option>
</select>

<select name="order_type" id="order_type">
		<option <?php selected($order_type, "most" ); ?> value="most"><?php _e('Most', 'post-views'); ?></option>
		<option <?php selected($order_type, "least" ); ?> value="least"><?php _e('Least', 'post-views'); ?></option>
</select>
<select name="output_type" id="output_type">
		<option <?php selected($output_type, "content" ); ?> value="content"><?php _e('Read', 'post-views'); ?></option>
		<option <?php selected($output_type, "excerpt" ); ?> value="excerpt"><?php _e('Preview', 'post-views'); ?></option>
</select>
<select name="per_page" id="per_page">
		<option <?php selected($per_page, "10" ); ?> value="10">10</option>
		<option <?php selected($per_page, "15" ); ?> value="15">15</option>
		<option <?php selected($per_page, "20" ); ?> value="20">20</option>
		<option <?php selected($per_page, "30" ); ?> value="30">30</option>
</select>
<input type="submit" value="<?php echo _e('Switch','post-views'); ?>" class="button" />
</form>
</div>
</p>
<table class="widefat page fixed" width="100%" cellpadding="0" cellspacing="0">
<?php
	if($order_type == 'least'){
			$order_type = 'ASC';
	}else{
			$order_type = 'DESC';		
	}                                                                     
 	$pv_total = get_post_views_list($mode, $view_type, $output_type, 'total','views',$order_type, $per_page, 40, $cat);
 	$pv_year = get_post_views_list($mode, $view_type, $output_type, 'year','views',$order_type, $per_page, 40, $cat);
 	$pv_halfyear = get_post_views_list($mode,$view_type, $output_type, 'halfyear','views',$order_type, $per_page, 40, $cat);
 	$pv_month = get_post_views_list($mode, $view_type, $output_type, 'month','views',$order_type, $per_page, 40, $cat);
 	$pv_week = get_post_views_list($mode, $view_type, $output_type, 'week','views',$order_type, $per_page, 40, $cat);
 	$pv_today = get_post_views_list($mode, $view_type, $output_type, 'today','views',$order_type, $per_page, 40, $cat);
 
	echo '<thead><tr><th width="20%">'.__('By Total','post-views').'</th><th width="13%">'.__('Views','post-views').'</th><th width="20%">'.__('By Year','post-views').'</th><th width="13%">'.__('Views','post-views').'</th><th width="20%">'.__('By Half Year','post-views').'</th><th width="14%">'.__('Views','post-views').'</th></tr></thead>';
  
 	for($i=0;$i < $per_page;$i++){
 		if(!empty($pv_total[$i]['title']) || !empty($pv_year[$i]['title']) || !empty($pv_halfyear[$i]['title'])){
 			echo '<tr><td width="20%">'.$pv_total[$i]['title'].'</td><td width="13%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_total[$i]['post_id'].'" target="_blank">'.$pv_total[$i]['views'].'</a></td><td width="20%">'.$pv_year[$i]['title'].'</td><td width="13%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_year[$i]['post_id'].'" target="_blank">'.$pv_year[$i]['views'].'</a></td><td width="20%">'.$pv_halfyear[$i]['title'].'</td><td width="14%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_halfyear[$i]['post_id'].'" target="_blank">'.$pv_halfyear[$i]['views'].'</a></td></tr>';
 		}
 	}
 	echo '<thead><tr><th width="20%">'.__('By Month','post-views').'</th><th width="13%">'.__('Views','post-views').'</th><th width="20%">'.__('By Week','post-views').'</th><th width="13%">'.__('Views','post-views').'</th><th width="20%">'.__('By Today','post-views').'</th><th width="14%">'.__('Views','post-views').'</th></tr></thead>';
 	for($i=0;$i < $per_page;$i++){
 		if(!empty($pv_month[$i]['title']) || !empty($pv_week[$i]['title']) || !empty($pv_today[$i]['title'])){
 			echo '<tr><td>'.$pv_month[$i]['title'].'</td><td><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_month[$i]['post_id'].'" target="_blank">'.$pv_month[$i]['views'].'</a></td><td width="20%">'.$pv_week[$i]['title'].'</td><td width="13%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_week[$i]['post_id'].'" target="_blank">'.$pv_week[$i]['views'].'</a></td><td width="20%">'.$pv_today[$i]['title'].'</td><td width="14%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_today[$i]['post_id'].'" target="_blank">'.$pv_today[$i]['views'].'</a></td></tr>';
    }
 	}
	echo '</table></div>';
}

/********************************** Post Views Ranking *************************************/

function post_category_list(){
	global $wpdb;
	
	if(empty($_GET['view_type'])){
  		$view_type = 'normal';
  }else{
  		$view_type = $_GET['view_type'];
  }
  
  if(empty($_GET['order_type'])){
  		$order_type = 'most';
  }else{
  		$order_type = $_GET['order_type'];
  }
  
  if(empty($_GET['output_type'])){
  		$output_type = 'content';
  }else{
  		$output_type = $_GET['output_type'];
  }
  
  if($_GET['cat'] != 0 ){
  		$cat = $_GET['cat'];
  }else{
  		$cat = 0;
  		$mode = 'both';
  }
  
  if(empty($_GET['per_page'])){
  		$per_page = 15;  	
  }else{
  		$per_page = $_GET['per_page'];
  }
?>
<div class="wrap">
<?php screen_icon('users');?>
<h2><?php _e('Categories','post-views');?></h2>
<?php
$pv_menu = "
							<a href=\"".PV_ADMIN_URL."&pv_page=analytics\">".__('Analytics', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=summary\">".__('Summary','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=ranking\">".__('Ranking','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=category\">".__('Category','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\">".__('Donation','post-views')."</a>&nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\" target=\"_blank\">".__('Help','post-views')."</a>
	            ";

echo $pv_menu; 
?>
<p>
<div align="right">
<form name="filterform" id="filterform" method="get" action="<?php echo admin_url('index.php'); ?>">
<input type="hidden" name="page" value="post-views">
<input type="hidden" name="pv_page" value="category">
<select name="view_type" id="view_type">
		<option <?php selected($view_type, "normal" ); ?> value="normal"><?php _e('Visitor', 'post-views'); ?></option>
		<option <?php selected($view_type, "robot" ); ?> value="robot"><?php _e('Robot', 'post-views'); ?></option>
</select>

<select name="order_type" id="order_type">
		<option <?php selected($order_type, "most" ); ?> value="most"><?php _e('Most', 'post-views'); ?></option>
		<option <?php selected($order_type, "least" ); ?> value="least"><?php _e('Least', 'post-views'); ?></option>
</select>
<select name="output_type" id="output_type">
		<option <?php selected($output_type, "content" ); ?> value="content"><?php _e('Read', 'post-views'); ?></option>
		<option <?php selected($output_type, "excerpt" ); ?> value="excerpt"><?php _e('Preview', 'post-views'); ?></option>
</select>
<select name="per_page" id="per_page">
		<option <?php selected($per_page, "10" ); ?> value="10">10</option>
		<option <?php selected($per_page, "15" ); ?> value="15">15</option>
		<option <?php selected($per_page, "20" ); ?> value="20">20</option>
		<option <?php selected($per_page, "30" ); ?> value="30">30</option>
</select>
<input type="submit" value="<?php echo _e('Switch','post-views'); ?>" class="button" />
</form>
</div>
</p>
<table class="widefat page fixed" width="100%" cellpadding="0" cellspacing="0">
<?php
	if($order_type == 'least'){
			$order_type = 'ASC';
	}else{
			$order_type = 'DESC';		
	}                                                                     
 	$pv_total = get_post_category_list($mode, $view_type, $output_type, 'total','views',$order_type, $per_page, 40);
 	$pv_year = get_post_category_list($mode, $view_type, $output_type, 'year','views',$order_type, $per_page, 40);
 	$pv_halfyear = get_post_category_list($mode,$view_type, $output_type, 'halfyear','views',$order_type, $per_page, 40);
 	$pv_month = get_post_category_list($mode, $view_type, $output_type, 'month','views',$order_type, $per_page, 40);
 	$pv_week = get_post_category_list($mode, $view_type, $output_type, 'week','views',$order_type, $per_page, 40);
 	$pv_today = get_post_category_list($mode, $view_type, $output_type, 'today','views',$order_type, $per_page, 40);
 
	echo '<thead><tr><th width="20%">'.__('By Total','post-views').'</th><th width="13%">'.__('Views','post-views').'</th><th width="20%">'.__('By Year','post-views').'</th><th width="13%">'.__('Views','post-views').'</th><th width="20%">'.__('By Half Year','post-views').'</th><th width="14%">'.__('Views','post-views').'</th></tr></thead>';
  
 	for($i=0;$i < $per_page;$i++){
 		if(!empty($pv_total[$i]['title']) || !empty($pv_year[$i]['title']) || !empty($pv_halfyear[$i]['title'])){
 			echo '<tr><td width="20%">'.$pv_total[$i]['title'].'</td><td width="13%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_total[$i]['post_id'].'" target="_blank">'.$pv_total[$i]['views'].'</a></td><td width="20%">'.$pv_year[$i]['title'].'</td><td width="13%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_year[$i]['post_id'].'" target="_blank">'.$pv_year[$i]['views'].'</a></td><td width="20%">'.$pv_halfyear[$i]['title'].'</td><td width="14%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_halfyear[$i]['post_id'].'" target="_blank">'.$pv_halfyear[$i]['views'].'</a></td></tr>';
 		}
 	}
 	echo '<thead><tr><th width="20%">'.__('By Month','post-views').'</th><th width="13%">'.__('Views','post-views').'</th><th width="20%">'.__('By Week','post-views').'</th><th width="13%">'.__('Views','post-views').'</th><th width="20%">'.__('By Today','post-views').'</th><th width="14%">'.__('Views','post-views').'</th></tr></thead>';
 	for($i=0;$i < $per_page;$i++){
 		if(!empty($pv_month[$i]['title']) || !empty($pv_week[$i]['title']) || !empty($pv_today[$i]['title'])){
 			echo '<tr><td>'.$pv_month[$i]['title'].'</td><td><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_month[$i]['post_id'].'" target="_blank">'.$pv_month[$i]['views'].'</a></td><td width="20%">'.$pv_week[$i]['title'].'</td><td width="13%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_week[$i]['post_id'].'" target="_blank">'.$pv_week[$i]['views'].'</a></td><td width="20%">'.$pv_today[$i]['title'].'</td><td width="14%"><a href="'.PV_ADMIN_URL.'&pv_page=detail&view_type='.$view_type.'&output_type='.$output_type.'&post_id='.$pv_today[$i]['post_id'].'" target="_blank">'.$pv_today[$i]['views'].'</a></td></tr>';
    }
 	}
	echo '</table></div>';
}
/********************************** Post Views Detail *************************************/
function post_views_detail(){
	global $wpdb;
	
	if(!empty($_REQUEST['post_id'])){
  		$post_id = $_REQUEST['post_id'];		
  }else{
			$post_id = 0;
  }
  
	if(!empty($_REQUEST['view_type'])){
  		$view_type = $_REQUEST['view_type'];		
  }else{
			$view_type = "normal";
  }
  
  if(!empty($_REQUEST['output_type'])){
  		$output_type = $_REQUEST['output_type'];		
  }else{
			$output_type = "content";
  }
  
  if(!empty($_REQUEST['year_id'])){
  		$year_id = $_REQUEST['year_id'];
  }else{
			$year_id = get_year();
  }
  
?>
<div class="wrap">
<?php 
screen_icon('users');
echo '<h2>';
echo get_the_title($post_id);
echo '</h2>';

$pv_menu = "
							<a href=\"".PV_ADMIN_URL."&pv_page=analytics\">".__('Analytics', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=summary\">".__('Summary', 'post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=ranking\">".__('Ranking','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"".PV_ADMIN_URL."&pv_page=category\">".__('Category','post-views')."</a> &nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\">".__('Donation','post-views')."</a>&nbsp;|&nbsp;
							<a href=\"http://ziming.org/dev/post-views\" target=\"_blank\" target=\"_blank\">".__('Help','post-views')."</a>
	            ";

echo $pv_menu; 

?>
</p>
<div align="right">
<form name="filterform" id="filterform" method="get" action="<?php echo admin_url('index.php'); ?>">
<input type="hidden" name="page" value="post-views">
<input type="hidden" name="pv_page" value="detail">
<input type="hidden" name="post_id" value="<?php echo $post_id; ?>">

<select name='year_id' id='year_id'>
<?php
$sql = 'SELECT DISTINCT year_id from '.WP_POST_VIEWS_HIS_TABLE;
$year_id_rst = $wpdb->get_results($sql);

foreach ($year_id_rst as $year_ids) {
		if ( $year_ids->year_id == $year_id ){
				$default = ' selected="selected"';
		}else{
				$default = '';
		}
	echo "<option ".$default." value='" .$year_ids->year_id. "'>".$year_ids->year_id."</option>\n";
}
?>
</select>

<input type="hidden" id="view_type" name="view_type" value="normal" />
<select name="output_type" id="output_type">
		<option <?php selected($output_type, "content" ); ?> value="content"><?php _e('Read', 'post-views'); ?></option>
		<option <?php selected($output_type, "excerpt" ); ?> value="excerpt"><?php _e('Preview', 'post-views'); ?></option>
</select>
<input type="submit" value="<?php echo _e('Switch','post-views'); ?>" class="button" />
</form>
</div>
</p>
<table class="widefat page fixed" width="100%" cellpadding="0" cellspacing="0">
<?php
	
	$today_trend =  get_post_views_trend($view_type,'today',$output_type,$year_id,$post_id);
	$week_trend =  get_post_views_trend($view_type,'week',$output_type,$year_id,$post_id);
	$month_trend =  get_post_views_trend($view_type,'month',$output_type,$year_id,$post_id);

	$today_views = get_post_views($view_type,'today',$output_type,false,$post_id,false);
	$week_views = get_post_views($view_type,'week',$output_type,false,$post_id,false);
	$month_views = get_post_views($view_type,'month',$output_type,false,$post_id,false);
	
  $today_trend_str= get_graphy_str($today_trend,$today_views);
  $week_trend_str= get_graphy_str($week_trend,$week_views);
  $month_trend_str= get_graphy_str($month_trend,$month_views);
  
  $read_count_today = 0;
  $read_count_week = 0;
  $read_count_month = 0;
  
  $arr_today_trend = explode(',',$today_trend);
  foreach ($arr_today_trend as $today){
  		if($today > 0)  $read_count_today  =  $read_count_today + 1;
  }
  if($today_views > 0)  $read_count_today  =  $read_count_today + 1;  
  $unread_count_today = count($arr_today_trend) - $read_count_today;
  
  
	$arr_week_trend = explode(',', $week_trend);
	foreach ($arr_week_trend as $week){
  		if($week > 0)  $read_count_week  =  $read_count_week + 1;
  }
  if($week_views > 0)  $read_count_week  =  $read_count_week + 1;
  
  $unread_count_week = count($arr_week_trend) - $read_count_week;
  
  
	$arr_month_trend = explode(',', $month_trend);
	foreach ($arr_month_trend as $month){
  		if($month > 0)  $read_count_month  =  $read_count_month + 1;
  }  
  if($month_views > 0)  $read_count_month  =  $read_count_month + 1;
	$unread_count_month = count($arr_month_trend) - $read_count_month;
	
?>
	<thead>
	<tr>
			<th class="manage-column" scope="col" colspan="1"><?php _e('Viewed Or Not ( Day )','post-views') ?></th>			
			<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Views ( Day )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
			<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo $read_count_today;?>,<?php echo $unread_count_today;?>&amp;chds=0,<?php echo $read_count_today + $unread_count_today; ?>&amp;chs=250x180&amp;chdl=<?php if($read_count_today > 1){ echo $read_count_today.' Days';}else{echo $read_count_today.' Day';} ?> Read|<?php if($unread_count_today > 1){echo $unread_count_today.' Days';}else{echo $unread_count_today.' Day';}?> Unread&amp;chco=206582"></td>
			<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $today_trend_str['max_str']; ?>&amp;chds=0,<?php echo $today_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $today_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $today_trend_str['chm'];?>&amp;chd=t:<?php echo $today_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
	
	<thead>
	<tr>			
			<th class="manage-column" scope="col" colspan="1"><?php _e('Viewed Or Not ( Week )','post-views') ?></th>			
			<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Views ( Week )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
			<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo $read_count_week;?>,<?php echo $unread_count_week;?>&amp;chds=0,<?php echo $read_count_week + $unread_count_week; ?>&amp;chs=250x180&amp;chdl=<?php if($read_count_week > 1){ echo $read_count_week.' Weeks';}else{echo $read_count_week.' Week';} ?> Read|<?php if($unread_count_week > 1){echo $unread_count_week.' Weeks';}else{echo $unread_count_week.' Week';}?> Unread&amp;chco=206582"></td>
			<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $week_trend_str['max_str']; ?>&amp;chds=0,<?php echo $week_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $week_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $week_trend_str['chm'];?>&amp;chd=t:<?php echo $week_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>
	
	<thead>
	<tr>		
			<th class="manage-column" scope="col" colspan="1"><?php _e('Viewed Or Not ( Month )','post-views') ?></th>				
			<th class="manage-column" scope="col" colspan="3"><?php _e('Historical Views ( Month )','post-views') ?></th>
	</tr>
	</thead>
	<tr>
			<td><img src="https://chart.googleapis.com/chart?cht=p&amp;chd=t:<?php echo $read_count_month;?>,<?php echo $unread_count_month;?>&amp;chds=0,<?php echo $read_count_month + $unread_count_month; ?>&amp;chs=250x180&amp;chdl=<?php if($read_count_month > 1){ echo $read_count_month.' Months';}else{echo $read_count_month.' Month';} ?> Read|<?php if($unread_count_month > 1){echo $unread_count_month.' Months';}else{echo $unread_count_month.' Month';}?> Unread&amp;chco=206582"></td>

			<td colspan="3"><img src="http://chart.apis.google.com/chart?chs=750x180&amp;chxt=x,y&amp;chco=206582&amp;chxr=1,0,<?php echo $month_trend_str['max_str']; ?>&amp;chds=0,<?php echo $month_trend_str['max_str']; ?>&amp;chxl=0:<?php echo $month_trend_str['label_str']; ?>&amp;cht=lc&amp;<?php echo $month_trend_str['chm'];?>&amp;chd=t:<?php echo $month_trend_str['data_str']; ?>" alt="" width="100%" height="180" ></td>
	</tr>	
	</table>
</div>
<?php
}
/********************************** Post Views Process Functions *************************************/

function process_post_views_content(){	
		process_post_views_action('content');
}

function process_post_views_excerpt(){	
		process_post_views_action('excerpt');
}


function process_post_views_action($output_type = 'content') {
	global $wpdb,$post,$user_ID;	
	$id = intval($post->ID);
	if(cache_enable()){
		wp_enqueue_script('jquery'); 
	}
	if(!wp_is_post_revision($post)) {
			if($output_type == 'excerpt'){
			  	$need_rec = true;
			}else{
					if(is_single() || is_page()) {
							$need_rec = true;
					}else{
							$need_rec = false;
					}
			}
			if($need_rec) {
					$agent = $_SERVER['HTTP_USER_AGENT'];
					$pv_is_rec = false;
					
					$pv_rec_options = get_option('post_views_rec_options');
					$rec_option = $pv_rec_options['rec_option'];
					$is_usecookie = $pv_rec_options['rec_cookie'];
					if($is_usecookie == 1){
							$pv_url = md5($_SERVER['REQUEST_URI']);	
							$pv_cookie = $_COOKIE[$pv_url];
		
							if(empty($pv_cookie)){
									if($rec_option == 1){
											if(intval($user_ID) == 0) {
													$pv_is_rec = true;
											}
									}else	if($rec_option == 2){
											if(intval($user_ID) > 0) {
													$pv_is_rec = true;
											}
									}else{
											$pv_is_rec = true;
									}
							}
					}else{					
							if($rec_option == 1){
									if(intval($user_ID) == 0) {
											$pv_is_rec = true;
									}
							}else	if($rec_option == 2){
									if(intval($user_ID) > 0) {
											$pv_is_rec = true;
									}
							}else{
									$pv_is_rec = true;
							}
					}
	
					if(is_robot($agent)){
							if(cache_enable()) {
									if(is_feed()){
											post_views_update('robot',$id,$output_type);
									}else{
											cache_print('robot',$id,$output_type);
									}
							}else{
									post_views_update('robot',$id,$output_type);
							}
					}else{
							if($pv_is_rec){
								if(cache_enable()) {
										if(is_feed()){
												post_views_update('normal',$id,$output_type);
										}else{
												cache_print('normal',$id,$output_type);
										}
								}else{
										post_views_update('normal',$id,$output_type);
								}
							}
					}
			}
	}
}


function post_views_last_viewed_update($id = '',$o_type){
		global $wpdb,$last_visited_time;
		if(!empty($id)){
				$last_visited_time['normal'][$o_type] = $wpdb->get_var($wpdb->prepare("SELECT latest_view_time FROM ".WP_POST_VIEWS_TABLE." where view_type='normal'and output_type='".$o_type."' and post_id=".$id, APP_POST_TYPE));
				$last_visited_time['robot'][$o_type] = $wpdb->get_var($wpdb->prepare("SELECT latest_view_time FROM ".WP_POST_VIEWS_TABLE." where view_type='robot' and output_type='".$o_type."' and post_id=".$id, APP_POST_TYPE));
		}
}

function post_view_trigger_update($v_type,$id,$o_type){
	global $wpdb;
	$having_record = $wpdb->get_var($wpdb->prepare("SELECT latest_view_time FROM ".WP_POST_VIEWS_TABLE." where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id, APP_POST_TYPE));
	$current_year = get_year();
	
	$max_latest_view_time = $wpdb->get_var($wpdb->prepare("SELECT max(latest_view_time) FROM ".WP_POST_VIEWS_TABLE, APP_POST_TYPE));
	$max_year = $wpdb->get_var($wpdb->prepare("SELECT max(year_id) FROM ".WP_POST_VIEWS_HIS_TABLE, APP_POST_TYPE));
	
	$max_time_span = get_time_span($max_latest_view_time);

	$temp_normal_views = -1;
	$temp_normal_previews = -1;
	
	$temp_normal_viewed = -1;
	$temp_normal_previewed = -1;
	
	$temp_normal_viewed_coverage = -1;
	$temp_normal_previewed_coverage = -1;
	
	if(!$having_record){
		if($v_type =='normal'){
				$wpdb->get_results("insert into ".WP_POST_VIEWS_HIS_TABLE." set year_id =".$current_year.", post_id = $id, view_type ='".$v_type."', output_type ='".$o_type."', post_views_today = '-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear = '-1', post_views_year = '-1' ");	
		}
		$wpdb->get_results("insert into ".WP_POST_VIEWS_TABLE." set post_id=$id, view_type='".$v_type."', output_type ='".$o_type."', post_views_today = 0,post_views_week = 0, post_views_month = 0, post_views_halfyear = 0, post_views_year = 0, post_views_total = 0, latest_view_time='".gmdate("Y-n-d H:i:s")."'");
	}
	
	if($max_time_span['diff_d']){
		$temp_normal_views = $wpdb->get_results("SELECT sum(post_views_today) as post_views_today, sum(post_views_week) as post_views_week, sum(post_views_month) as post_views_month, sum(post_views_halfyear) as post_views_halfyear, sum(post_views_year) as post_views_year FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='content' ");
		$temp_normal_views_today = $temp_normal_views[0]->post_views_today;
		$temp_normal_views_week = $temp_normal_views[0]->post_views_week;
		$temp_normal_views_month = $temp_normal_views[0]->post_views_month;
		$temp_normal_views_halfyear = $temp_normal_views[0]->post_views_halfyear;
		$temp_normal_views_year = $temp_normal_views[0]->post_views_year;
		
		$temp_normal_previews = $wpdb->get_results("SELECT sum(post_views_today) as post_views_today, sum(post_views_week) as post_views_week, sum(post_views_month) as post_views_month, sum(post_views_halfyear) as post_views_halfyear, sum(post_views_year) as post_views_year FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='excerpt' ");
		$temp_normal_previews_today = $temp_normal_previews[0]->post_views_today;
		$temp_normal_previews_week = $temp_normal_previews[0]->post_views_week;
		$temp_normal_previews_month = $temp_normal_previews[0]->post_views_month;
		$temp_normal_previews_halfyear = $temp_normal_previews[0]->post_views_halfyear;
		$temp_normal_previews_year = $temp_normal_previews[0]->post_views_year;		
		
		$temp_normal_viewed_today = $wpdb->get_var($wpdb->prepare("select count(post_views_today) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='content' and post_views_today !=0 ", APP_POST_TYPE));
		$temp_normal_viewed_week = $wpdb->get_var($wpdb->prepare("select count(post_views_week) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='content' and post_views_week !=0 ", APP_POST_TYPE));
		$temp_normal_viewed_month = $wpdb->get_var($wpdb->prepare("select count(post_views_month) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='content' and post_views_month !=0 ", APP_POST_TYPE));
		$temp_normal_viewed_halfyear = $wpdb->get_var($wpdb->prepare("select count(post_views_halfyear) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='content' and post_views_halfyear !=0 ", APP_POST_TYPE));
		$temp_normal_viewed_year = $wpdb->get_var($wpdb->prepare("select count(post_views_year) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='content' and post_views_year !=0 ", APP_POST_TYPE));
		
		$temp_normal_previewed_today = $wpdb->get_var($wpdb->prepare("select count(post_views_today) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='excerpt' and post_views_today !=0 ", APP_POST_TYPE));
		$temp_normal_previewed_week = $wpdb->get_var($wpdb->prepare("select count(post_views_week) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='excerpt' and post_views_week !=0 ", APP_POST_TYPE));
		$temp_normal_previewed_month = $wpdb->get_var($wpdb->prepare("select count(post_views_month) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='excerpt' and post_views_month !=0 ", APP_POST_TYPE));
		$temp_normal_previewed_halfyear = $wpdb->get_var($wpdb->prepare("select count(post_views_halfyear) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='excerpt' and post_views_halfyear !=0 ", APP_POST_TYPE));
		$temp_normal_previewed_year = $wpdb->get_var($wpdb->prepare("select count(post_views_year) FROM ".WP_POST_VIEWS_TABLE." where view_type='normal' and output_type='excerpt' and post_views_year !=0 ", APP_POST_TYPE));
		
		$post_count = $wpdb->get_var($wpdb->prepare("SELECT count(ID) FROM ".WP_POST_TABLE." where post_date < '".current_time('mysql')."' AND post_status = 'publish' AND post_password = ''", APP_POST_TYPE));
		
		$temp_normal_viewed_coverage_today = ceil( ($temp_normal_viewed_today / $post_count) * 100 );
		$temp_normal_viewed_coverage_week = ceil( ($temp_normal_viewed_week / $post_count) * 100 );
		$temp_normal_viewed_coverage_month = ceil( ($temp_normal_viewed_month / $post_count) * 100 );
		$temp_normal_viewed_coverage_halfyear = ceil( ($temp_normal_viewed_halfyear / $post_count) * 100 );
		$temp_normal_viewed_coverage_year = ceil( ($temp_normal_viewed_year / $post_count) * 100 );
		
		$temp_normal_previewed_coverage_today = ceil( ($temp_normal_previewed_today / $post_count) * 100 );
		$temp_normal_previewed_coverage_week = ceil( ($temp_normal_previewed_week / $post_count) * 100 );
		$temp_normal_previewed_coverage_month = ceil( ($temp_normal_previewed_month / $post_count) * 100 );
		$temp_normal_previewed_coverage_halfyear = ceil( ($temp_normal_previewed_halfyear / $post_count) * 100 );
		$temp_normal_previewed_coverage_year = ceil( ($temp_normal_previewed_year / $post_count) * 100 );
		
		
		$pv_update_process = get_option('post_views_update_process');
		if($pv_update_process['update_process'] != 'waiting'){  // synchro mark begin
				$pv_update_process['update_process'] = 'waiting';
				update_option('post_views_update_process', $pv_update_process);
				
				if($max_time_span['diff_d']){
						$wpdb->get_results("update ".WP_POST_VIEWS_HIS_TABLE." htb set post_views_today = concat(concat(post_views_today,','),(select post_views_today from ".WP_POST_VIEWS_TABLE." tb where tb.post_id = htb.post_id AND tb.view_type = htb.view_type AND tb.output_type = htb.output_type )) where htb.post_id!= 0 AND htb.year_id = ".$max_year);
            
            //update post views summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_today = concat(concat(post_views_today,','),".$temp_normal_views_today.") where year_id =".$max_year." and summary_type = 'normal_views' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_today = concat(concat(post_views_today,','),".$temp_normal_previews_today.") where year_id =".$max_year." and summary_type = 'normal_previews' ");
            
            //update post viewed summary
          	$wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_today = concat(concat(post_views_today,','),".$temp_normal_viewed_today.") where year_id =".$max_year." and summary_type = 'normal_viewed' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_today = concat(concat(post_views_today,','),".$temp_normal_previewed_today.") where year_id =".$max_year." and summary_type = 'normal_previewed' ");
            
            //update post viewed coverage summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_today = concat(concat(post_views_today,','),".$temp_normal_viewed_coverage_today.") where year_id =".$max_year." and summary_type = 'normal_viewed_coverage' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_today = concat(concat(post_views_today,','),".$temp_normal_previewed_coverage_today.") where year_id =".$max_year." and summary_type = 'normal_previewed_coverage' ");

						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_today = 0 ");
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_today = 1 where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);				
				}
								
				if($max_time_span['diff_w']){
						$wpdb->get_results("update ".WP_POST_VIEWS_HIS_TABLE." htb set post_views_week = concat(concat(post_views_week,','),(select post_views_week from ".WP_POST_VIEWS_TABLE." tb where tb.post_id = htb.post_id AND tb.view_type = htb.view_type AND tb.output_type = htb.output_type )) where htb.post_id!= 0 AND htb.year_id = ".$max_year);

						//update post views summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_week = concat(concat(post_views_week,','),".$temp_normal_views_week.") where year_id =".$max_year." and summary_type = 'normal_views' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_week = concat(concat(post_views_week,','),".$temp_normal_previews_week.") where year_id =".$max_year." and summary_type = 'normal_previews' ");
            
            //update post viewed summary
          	$wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_week = concat(concat(post_views_week,','),".$temp_normal_viewed_week.") where year_id =".$max_year." and summary_type = 'normal_viewed' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_week = concat(concat(post_views_week,','),".$temp_normal_previewed_week.") where year_id =".$max_year." and summary_type = 'normal_previewed' ");
            
            //update post viewed coverage summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_week = concat(concat(post_views_week,','),".$temp_normal_viewed_coverage_week.") where year_id =".$max_year." and summary_type = 'normal_viewed_coverage' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_week = concat(concat(post_views_week,','),".$temp_normal_previewed_coverage_week.") where year_id =".$max_year." and summary_type = 'normal_previewed_coverage' ");

						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_week = 0 ");
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_week = 1 where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);
				}else{
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_week = post_views_week + 1 where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);
				}
				
				if($max_time_span['diff_m']){
						$wpdb->get_results("update ".WP_POST_VIEWS_HIS_TABLE." htb set post_views_month = concat(concat(post_views_month,','),(select post_views_month from ".WP_POST_VIEWS_TABLE." tb where tb.post_id = htb.post_id AND tb.view_type = htb.view_type AND tb.output_type = htb.output_type )) where htb.post_id!= 0 AND htb.year_id = ".$max_year);
						
						//update post views summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_month = concat(concat(post_views_month,','),".$temp_normal_views_month.") where year_id =".$max_year." and summary_type = 'normal_views' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_month = concat(concat(post_views_month,','),".$temp_normal_previews_month.") where year_id =".$max_year." and summary_type = 'normal_previews' ");
            
            //update post viewed summary
          	$wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_month = concat(concat(post_views_month,','),".$temp_normal_viewed_month.") where year_id =".$max_year." and summary_type = 'normal_viewed' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_month = concat(concat(post_views_month,','),".$temp_normal_previewed_month.") where year_id =".$max_year." and summary_type = 'normal_previewed' ");
            
            //update post viewed coverage summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_month = concat(concat(post_views_month,','),".$temp_normal_viewed_coverage_month.") where year_id =".$max_year." and summary_type = 'normal_viewed_coverage' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_month = concat(concat(post_views_month,','),".$temp_normal_previewed_coverage_month.") where year_id =".$max_year." and summary_type = 'normal_previewed_coverage' ");

						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_month = 0 ");
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_month = 1 where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);
				}else{
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_month = post_views_month + 1 where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);	
				}				
				
				if($max_time_span['diff_hy']){
						$wpdb->get_results("update ".WP_POST_VIEWS_HIS_TABLE." htb set post_views_halfyear = concat(concat(post_views_halfyear,','),(select post_views_halfyear from ".WP_POST_VIEWS_TABLE." tb where tb.post_id = htb.post_id AND tb.view_type = htb.view_type AND tb.output_type = htb.output_type )) where htb.post_id!= 0  AND htb.year_id = ".$max_year);
				
						//update post views summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_halfyear = concat(concat(post_views_halfyear,','),".$temp_normal_views_halfyear.") where year_id =".$max_year." and summary_type = 'normal_views' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_halfyear = concat(concat(post_views_halfyear,','),".$temp_normal_previews_halfyear.") where year_id =".$max_year." and summary_type = 'normal_previews' ");
            
            //update post viewed summary
          	$wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_halfyear = concat(concat(post_views_halfyear,','),".$temp_normal_viewed_halfyear.") where year_id =".$max_year." and summary_type = 'normal_viewed' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_halfyear = concat(concat(post_views_halfyear,','),".$temp_normal_previewed_halfyear.") where year_id =".$max_year." and summary_type = 'normal_previewed' ");
            
            //update post viewed coverage summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_halfyear = concat(concat(post_views_halfyear,','),".$temp_normal_viewed_coverage_halfyear.") where year_id =".$max_year." and summary_type = 'normal_viewed_coverage' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_halfyear = concat(concat(post_views_halfyear,','),".$temp_normal_previewed_coverage_halfyear.") where year_id =".$max_year." and summary_type = 'normal_previewed_coverage' ");

						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_halfyear = 0 ");
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_halfyear = 1 where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);
				}else{
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_halfyear = post_views_halfyear + 1  where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);
				}				
				
				if($max_time_span['diff_y']){
						$wpdb->get_results("update ".WP_POST_VIEWS_HIS_TABLE." htb set post_views_year = concat(concat(post_views_year,','),(select post_views_year from ".WP_POST_VIEWS_TABLE." tb where tb.post_id = htb.post_id AND tb.view_type = htb.view_type AND tb.output_type = htb.output_type )) where htb.post_id!= 0 AND htb.year_id = ".$max_year);

						$wpdb->get_results("insert into ".WP_POST_VIEWS_HIS_TABLE."(post_id, view_type, output_type, year_id, post_views_today, post_views_week, post_views_month, post_views_halfyear, post_views_year) (select post_id, view_type, output_type, ".$current_year.", '-1', '-1', '-1', '-1', '-1' from ".WP_POST_VIEWS_TABLE.")");	

						//update post views summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_year = concat(concat(post_views_year,','),".$temp_normal_views_year.") where year_id =".$max_year." and summary_type = 'normal_views' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_year = concat(concat(post_views_year,','),".$temp_normal_previews_year.") where year_id =".$max_year." and summary_type = 'normal_previews' ");
            
            //update post viewed summary
          	$wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_year = concat(concat(post_views_year,','),".$temp_normal_viewed_year.") where year_id =".$max_year." and summary_type = 'normal_viewed' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_year = concat(concat(post_views_year,','),".$temp_normal_previewed_year.") where year_id =".$max_year." and summary_type = 'normal_previewed' ");
            
            //update post viewed coverage summary
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_year = concat(concat(post_views_year,','),".$temp_normal_viewed_coverage_year.") where year_id =".$max_year." and summary_type = 'normal_viewed_coverage' ");
            $wpdb->get_results("update ".WP_POST_VIEWS_SUMMARY_TABLE." set post_views_year = concat(concat(post_views_year,','),".$temp_normal_previewed_coverage_year.") where year_id =".$max_year." and summary_type = 'normal_previewed_coverage' ");

						//init post views summary for new year
						$wpdb->get_results("insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set year_id =".$current_year.", summary_type = 'normal_views', post_views_today = '-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear = '-1', post_views_year = '-1' ");
						$wpdb->get_results("insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set year_id =".$current_year.", summary_type = 'normal_previews', post_views_today = '-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear = '-1', post_views_year = '-1' ");		

						//init post viewed summary for new year
						$wpdb->get_results("insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set year_id =".$current_year.", summary_type = 'normal_viewed', post_views_today = '-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear = '-1', post_views_year = '-1' ");
						$wpdb->get_results("insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set year_id =".$current_year.", summary_type = 'normal_previewed', post_views_today = '-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear = '-1', post_views_year = '-1' ");		

						//init post viewed coverage summary for new year
						$wpdb->get_results("insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set year_id =".$current_year.", summary_type = 'normal_viewed_coverage', post_views_today = '-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear = '-1', post_views_year = '-1' ");
						$wpdb->get_results("insert into ".WP_POST_VIEWS_SUMMARY_TABLE." set year_id =".$current_year.", summary_type = 'normal_previewed_coverage', post_views_today = '-1', post_views_week = '-1', post_views_month = '-1', post_views_halfyear = '-1', post_views_year = '-1' ");		
									
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_year = 0 ");
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_year = 1 where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);
				}else{
						$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_year = post_views_year + 1 where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);
				}

				// always increase total and update the last viewed time
				$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_total = post_views_total + 1, latest_view_time='".gmdate("Y-n-d H:i:s")."' where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);
				// synchro mark end
  			$pv_update_process['update_process'] = 'updated';
  			update_option('post_views_update_process', $pv_update_process);	
		}else{
				$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_today = post_views_today + 1,post_views_week = post_views_week + 1,post_views_month = post_views_month + 1,post_views_halfyear = post_views_halfyear + 1,post_views_year = post_views_year + 1,post_views_total = post_views_total + 1, latest_view_time = latest_view_time where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);		
		}
	}else{ // if not next day
				$wpdb->get_results("update ".WP_POST_VIEWS_TABLE." set post_views_today = post_views_today + 1,post_views_week = post_views_week + 1,post_views_month = post_views_month + 1,post_views_halfyear = post_views_halfyear + 1,post_views_year = post_views_year + 1,post_views_total = post_views_total + 1, latest_view_time='".gmdate("Y-n-d H:i:s")."' where view_type='".$v_type."' and output_type='".$o_type."' and post_id=".$id);	
	}
	
}

function post_views_update($v_type,$id,$o_type){

	post_views_last_viewed_update($id,$o_type);
	post_view_trigger_update($v_type,$id,$o_type);
}

function delete_post_views($post_ID) {
	global $wpdb;
	if(!wp_is_post_revision($post_ID)) {
			$wpdb->get_results("delete from ".WP_POST_VIEWS_TABLE." where post_id=".$post_ID);
			$wpdb->get_results("delete from ".WP_POST_VIEWS_HIS_TABLE." where post_id=".$post_ID);
	}
}

### Function: Sort Views Posts
function post_views_sorting($local_wp_query) {

	if(($local_wp_query->get('v_sortby') == 'views') || ($local_wp_query->get('v_sortby') == 'robot_views')) {
		add_filter('posts_fields', 'post_views_fields');
		add_filter('posts_join', 'post_views_join');
		add_filter('posts_where', 'post_views_where');
		add_filter('posts_orderby', 'post_views_orderby');
	} else {
		remove_filter('posts_fields', 'post_views_fields');
		remove_filter('posts_join', 'post_views_join');
		remove_filter('posts_where', 'post_views_where');
		remove_filter('posts_orderby', 'post_views_orderby');
	}
	
}

function post_views_variables($public_query_vars) {
	$public_query_vars[] = 'v_sortby';
	$public_query_vars[] = 'v_timespan';
	$public_query_vars[] = 'v_ordertype';
	$public_query_vars[] = 'v_orderby';
	$public_query_vars[] = 'v_outtype';
	return $public_query_vars;
}

function post_views_fields($content) {
	global $wpdb;
	$v_timespan = trim(addslashes(get_query_var('v_timespan')));
	if($v_timespan == 'today') {
    	$v_timespan = 'post_views_today';
	}else if($v_timespan == 'week') {
    	$v_timespan = 'post_views_week';
	}else if($v_timespan == 'month') {
    	$v_timespan = 'post_views_month';
	}else if($v_timespan == 'halfyear') {
    	$v_timespan = 'post_views_halfyear';
	}else if($v_timespan == 'year') {
    	$v_timespan = 'post_views_year';
	}else{
	 		$v_timespan = 'post_views_total';
	}
	
	$v_visit_time = trim(addslashes(get_query_var('v_ordertype')));
	if($v_visit_time == 'latest_view_time') {
		$v_visit_time = ' , latest_view_time';
	}else{
		$v_visit_time = '';
	}
	
	$content .= ", (".WP_POST_VIEWS_TABLE.".".$v_timespan." + 0) AS views $v_visit_time";
	return $content;
}
function post_views_join($content) {
	global $wpdb;
	$content .= " LEFT JOIN ".WP_POST_VIEWS_TABLE." ON ".WP_POST_VIEWS_TABLE.".post_id = $wpdb->posts.ID";	
	return $content;
}
function post_views_where($content) {
	global $wpdb;
		
	$v_sortby = trim(addslashes(get_query_var('v_sortby')));
	if($v_sortby == 'robot_views') {
    	$v_sortby = 'robot';
	}else{
	 		$v_sortby = 'normal';
	}
	
	$v_outtype = trim(addslashes(get_query_var('v_outtype')));
	if($v_outtype == 'excerpt') {	
	
	}else{
	    $v_outtype = 'content';
	}
	
	$v_timespan = trim(addslashes(get_query_var('v_timespan')));
	if($v_timespan == 'today') {
    	$v_timespan = 'post_views_today';
	}else if($v_timespan == 'week') {
    	$v_timespan = 'post_views_week';
	}else if($v_timespan == 'month') {
    	$v_timespan = 'post_views_month';
	}else if($v_timespan == 'halfyear') {
    	$v_timespan = 'post_views_halfyear';
	}else if($v_timespan == 'year') {
    	$v_timespan = 'post_views_year';
	}else{
	 		$v_timespan = 'post_views_total';
	}

	$content .= " AND ".WP_POST_VIEWS_TABLE.".view_type = '".$v_sortby."' AND ".WP_POST_VIEWS_TABLE.".output_type = '".$v_outtype."' AND ".WP_POST_VIEWS_TABLE.".".$v_timespan." != 0 ";
	return $content;
}

function post_views_orderby($content) {
	$ordertype = trim(addslashes(get_query_var('v_ordertype')));
	if(empty($ordertype) || ($ordertype != 'latest_view_time' && $ordertype != 'views')) {
		$ordertype = 'views';
	}
	
	$orderby = strtolower(trim(addslashes(get_query_var('v_orderby'))));
	if($orderby != 'asc') {
		$orderby = 'desc';
	}
	$content = " ".$ordertype." ".$orderby;
	return $content;
}

/************************************* Post Views Widget ************************************/

function widget_post_views_init() {
	register_widget('WP_Widget_Post_Views');
}

class WP_Widget_Post_Views extends WP_Widget {
	// Constructor
	function WP_Widget_Post_Views() {
		$widget_ops = array('description' => __('Post Views', 'post-views'));
		$this->WP_Widget('post-views', __('Post Views', 'post-views'), $widget_ops);
	}

	// Display Widget
	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', esc_attr($instance['title']));
		$type = esc_attr($instance['type']);
		$view_type = esc_attr($instance['view_type']);
		$output_type = esc_attr($instance['output_type']);
		$time_span = esc_attr($instance['time_span']);
		$mode = esc_attr($instance['mode']);
		$limit = intval($instance['limit']);
		$chars = intval($instance['chars']);
		if(esc_attr($instance['cat_or_tag_ids'])==0){
				$cat_or_tag_ids =	0;
		}else{
				$cat_or_tag_ids = explode(',', esc_attr($instance['cat_or_tag_ids']));
		}

		echo $before_widget.$before_title.$title.$after_title;
		echo '<ul>'."\n";
		switch($type) {   //($mode = '', $view_type = 'normal' , $output_type = 'content', $time_span = 'total_views', $order_type = 'DESC', $limit = 10, $chars = 0, $category_id = 0,$tag_id = 0)
			case 'most_viewed':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'views','DESC', $limit, $chars);
				break;
			case 'most_viewed_category':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'views','DESC', $limit, $chars, $cat_or_tag_ids,0);
				break;
			case 'most_viewed_tag':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'views','DESC', $limit, $chars,0, $cat_or_tag_ids);
				break;
				
			case 'least_viewed':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'views','ASC', $limit, $chars);
				break;
			case 'least_viewed_category':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'views','ASC',$limit, $chars, $cat_or_tag_ids,0);
				break;
			case 'least_viewed_tag':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'views','ASC', $limit, $chars,0, $cat_or_tag_ids);
				break;
			
			case 'latest_viewed':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'latest_view_time','DESC', $limit, $chars);
				break;
			case 'latest_viewed_category':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'latest_view_time','DESC',$limit, $chars, $cat_or_tag_ids,0);
				break;
			case 'latest_viewed_tag':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'latest_view_time','DESC', $limit, $chars,0, $cat_or_tag_ids);
				break;
				
			case 'oldest_viewed':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'latest_view_time','ASC', $limit, $chars);
				break;
			case 'oldest_viewed_category':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'latest_view_time','ASC',$limit, $chars, $cat_or_tag_ids,0);
				break;
			case 'oldest_viewed_tag':
				show_post_views_list($mode, $view_type, $output_type, $time_span,'latest_view_time','ASC', $limit, $chars,0, $cat_or_tag_ids);
				break;
		}
		echo '</ul>'."\n";
		echo $after_widget;
	}

	// When Widget Control Form Is Posted
	function update($new_instance, $old_instance) {
		if (!isset($new_instance['submit'])) {
			return false;
		}
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['type'] = strip_tags($new_instance['type']);
		$instance['view_type'] = strip_tags($new_instance['view_type']);
		$instance['output_type'] = strip_tags($new_instance['output_type']);
		$instance['time_span'] = strip_tags($new_instance['time_span']);
		$instance['mode'] = strip_tags($new_instance['mode']);
		$instance['limit'] = intval($new_instance['limit']);
		$instance['chars'] = intval($new_instance['chars']);
		$instance['cat_or_tag_ids'] = strip_tags($new_instance['cat_or_tag_ids']);
		return $instance;
	}

	// DIsplay Widget Control Form
	function form($instance) {
		global $wpdb;
		$instance = wp_parse_args((array) $instance, array('title' => __('Post Views', 'post-views'), 'type' => 'most_viewed', 'view_type' => 'normal', 'output_type' => 'content', 'time_span' => 'total', 'mode' => 'both', 'limit' => 10, 'chars' => 20, 'cat_or_tag_ids' => '0'));
		$title = esc_attr($instance['title']);
		$type = esc_attr($instance['type']);
		$view_type = esc_attr($instance['view_type']);
		$output_type = esc_attr($instance['output_type']);
		$time_span = esc_attr($instance['time_span']);
		$mode = esc_attr($instance['mode']);
		$limit = intval($instance['limit']);
		$chars = intval($instance['chars']);
		$cat_or_tag_ids = esc_attr($instance['cat_or_tag_ids']);

?>	
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'post-views'); ?> : <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('type'); ?>"><?php _e('Statistics Type', 'post-views'); ?> :
				<select name="<?php echo $this->get_field_name('type'); ?>" id="<?php echo $this->get_field_id('type'); ?>" class="widefat" onchange="<?php echo $rand_js; ?>">
					<option value="least_viewed"<?php selected('least_viewed', $type); ?>><?php _e('Least Viewed', 'post-views'); ?></option>
					<option value="least_viewed_tag"<?php selected('least_viewed_tag', $type); ?>><?php _e('Least Viewed By Tag', 'post-views'); ?></option>
					<option value="least_viewed_category"<?php selected('least_viewed_category', $type); ?>><?php _e('Least Viewed By Category', 'post-views'); ?></option>
					<optgroup>--------------</optgroup>
					<option value="most_viewed"<?php selected('most_viewed', $type); ?>><?php _e('Most Viewed', 'post-views'); ?></option>
					<option value="most_viewed_tag"<?php selected('most_viewed_tag', $type); ?>><?php _e('Most Viewed By Tag', 'post-views'); ?></option>
					<option value="most_viewed_category"<?php selected('most_viewed_category', $type); ?>><?php _e('Most Viewed By Category', 'post-views'); ?></option>
					<optgroup>--------------</optgroup>
					<option value="latest_viewed"<?php selected('latest_viewed', $type); ?>><?php _e('Latest Viewed', 'post-views'); ?></option>
					<option value="latest_viewed_tag"<?php selected('latest_viewed_tag', $type); ?>><?php _e('Latest Viewed By Tag', 'post-views'); ?></option>
					<option value="latest_viewed_category"<?php selected('latest_viewed_category', $type); ?>><?php _e('Latest Viewed By Category', 'post-views'); ?></option>
					<optgroup>--------------</optgroup>
					<option value="oldest_viewed"<?php selected('oldest_viewed', $type); ?>><?php _e('Oldest Viewed', 'post-views'); ?></option>
					<option value="oldest_viewed_tag"<?php selected('oldest_viewed_tag', $type); ?>><?php _e('Oldest Viewed By Tag', 'post-views'); ?></option>
					<option value="oldest_viewed_category"<?php selected('oldest_viewed_category', $type); ?>><?php _e('Oldest Viewed By Category', 'post-views'); ?></option>				
				</select>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('time_span'); ?>"><?php _e('Time Span:', 'post-views'); ?>
				<select name="<?php echo $this->get_field_name('time_span'); ?>" id="<?php echo $this->get_field_id('time_span'); ?>" class="widefat">
					<option value="today"<?php selected('today', $time_span); ?>><?php _e('By Today Views', 'post-views'); ?></option>
					<option value="week"<?php selected('week', $time_span); ?>><?php _e('By Week Views', 'post-views'); ?></option>
					<option value="month"<?php selected('month', $time_span); ?>><?php _e('By Month Views', 'post-views'); ?></option>
					<option value="halfyear"<?php selected('halfyear', $time_span); ?>><?php _e('By Half Year Views', 'post-views'); ?></option>
					<option value="year"<?php selected('year', $time_span); ?>><?php _e('By Year Views', 'post-views'); ?></option>
					<option value="total"<?php selected('total', $time_span); ?>><?php _e('By Total Views', 'post-views'); ?></option>
				</select>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('view_type'); ?>"><?php _e('View Type:', 'post-views'); ?>
				<select name="<?php echo $this->get_field_name('view_type'); ?>" id="<?php echo $this->get_field_id('view_type'); ?>" class="widefat">
					<option value="normal"<?php selected('normal', $view_type); ?>><?php _e('Normal Visitor Viewed', 'post-views'); ?></option>
					<option value="robot"<?php selected('robot', $view_type); ?>><?php _e('Robot Viewed', 'post-views'); ?></option>
				</select>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('output_type'); ?>"><?php _e('Output Type:', 'post-views'); ?>
				<select name="<?php echo $this->get_field_name('output_type'); ?>" id="<?php echo $this->get_field_id('output_type'); ?>" class="widefat">
					<option value="content"<?php selected('content', $output_type); ?>><?php _e('Read', 'post-views'); ?></option>
					<option value="excerpt"<?php selected('excerpt', $output_type); ?>><?php _e('Preview', 'post-views'); ?></option>
				</select>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('mode'); ?>"><?php _e('Include Views From:', 'post-views'); ?>
				<select name="<?php echo $this->get_field_name('mode'); ?>" id="<?php echo $this->get_field_id('mode'); ?>" class="widefat">
					<option value="both"<?php selected('both', $mode); ?>><?php _e('All Types', 'post-views'); ?></option>
					<option value="post"<?php selected('post', $mode); ?>><?php _e('Post', 'post-views'); ?></option>
					<option value="page"<?php selected('page', $mode); ?>><?php _e('Page', 'post-views'); ?></option>
<?php			
					$post_types=get_post_types(array('public'=>true,'_builtin'=>false), 'names'); 
					foreach ($post_types as $post_type ) { 
						echo '<option value="'.$post_type.'"'.selected($post_type,$mode).'>'. $post_type. '</option>'; 
					}
?>
				</select>
			</label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Records To Show:', 'post-views'); ?> <input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="text" value="<?php echo $limit; ?>" /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('chars'); ?>"><?php _e('Maximum Title Length:', 'post-views'); ?> <input class="widefat" id="<?php echo $this->get_field_id('chars'); ?>" name="<?php echo $this->get_field_name('chars'); ?>" type="text" value="<?php echo $chars; ?>" /></label><br />
			<small><strong>0</strong> <?php _e('to disable.', 'post-views'); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('cat_or_tag_ids'); ?>"><?php _e('Category / Tag IDs:', 'post-views'); ?> <span style="color: red;">*</span> <input class="widefat" id="<?php echo $this->get_field_id('cat_or_tag_ids'); ?>" name="<?php echo $this->get_field_name('cat_or_tag_ids'); ?>" type="text" value="<?php echo $cat_or_tag_ids; ?>" /></label><br />
			<small><?php _e('Seperate mutiple categories/tags with commas, positive to include and \'-\' to exlude(if not using \'* by category/tag\' ignore it).', 'post-views'); ?></small>
		</p>
		<input type="hidden" id="<?php echo $this->get_field_id('submit'); ?>" name="<?php echo $this->get_field_name('submit'); ?>" value="1" />
<?php
	}
}

function show_post_views_list($mode = '', $view_type = 'normal', $output_type = 'content', $time_span = 'total', $order_type = 'views', $order_by = 'DESC', $limit = 10, $chars = 0, $category_id = 0, $tag_id = 0, $before = '<li>', $after = '</li>'){
		$output='';
		$post_views_list = get_post_views_list($mode, $view_type, $output_type, $time_span, $order_type, $order_by, $limit, $chars, $category_id, $tag_id);
		if($post_views_list){
			for($i=0;$i < $limit;$i++){
			 	if(!empty($post_views_list[$i]['title'])){
			 		$output .= $before.' '.$post_views_list[$i]['title'].' '.$after;
			 	}
			}
			echo $output;
		}
}
function show_post_category_list($mode = '', $view_type = 'normal', $output_type = 'content', $time_span = 'total', $order_type = 'views', $order_by = 'DESC', $limit = 10, $chars = 0, $before = '<li>', $after = '</li>'){
		$output='';
		$post_views_list = get_post_category_list($mode, $view_type, $output_type, $time_span, $order_type, $order_by, $limit, $chars);
		if($post_views_list){
			for($i=0;$i < $limit;$i++){
			 	if(!empty($post_views_list[$i]['title'])){
			 		$output .= $before.' '.$post_views_list[$i]['title'].' '.$after;
			 	}
			}
			echo $output;
		}
}
function get_post_views_list($mode = '', $view_type = 'normal', $output_type = 'content', $time_span = 'total', $order_type = 'views', $order_by = 'DESC', $limit = 10, $chars = 0, $category_id = 0, $tag_id = 0){
		global $wpdb, $post;
		for($i=0;$i < $limit;$i++){
				$pv_views[$i] = array('title' => '', 'views' => '', 'time' => '' );
		}
		
		if(!empty($mode) && $mode != 'both') {
			$mode = "post_type = '$mode'";
		} else {
			$mode = '1=1';
		}
		if($time_span == 'today'){
			$time_span = 'post_views_today';
		}else if($time_span == 'week'){
			$time_span = 'post_views_week';
		}else if($time_span == 'month'){
			$time_span = 'post_views_month';
		}else if($time_span == 'halfyear'){
			$time_span = 'post_views_halfyear';
		}else if($time_span == 'year'){
			$time_span = 'post_views_year';
		}else{
			$time_span = 'post_views_total';
		}

		if($category_id != 0){
			$user_cat_tag = "INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";
			if(is_array($category_id)) {
				  foreach($category_id as $catid){
				  	 $catid = intval($catid);
				  	 if($catid > 0){
				  	 		$include_ids[] = $catid;
				  	 }else{
				  	 	  $exlude_ids[] = -$catid;
				  	 }
				  }
				  if(!empty($include_ids)){
				  	 if(count($include_ids) != 1){
				  	 	 $cat_or_tag_include_sql = " $wpdb->term_taxonomy.term_id IN (".join(',', $include_ids).") ";
				  	 }else{
				  	 	 $cat_or_tag_include_sql = " $wpdb->term_taxonomy.term_id = $include_ids[0] ";
				  	 }
				  }else{
				  	 $cat_or_tag_include_sql = " 1=1 ";
				  }
				  if(!empty($exlude_ids)){
				  	 if(count($exlude_ids) != 1){
				  	 	 $cat_or_tag_exlude_sql = " $wpdb->term_taxonomy.term_id NOT IN (".join(',', $exlude_ids).") ";
				  	 }else{
				  	 	 $cat_or_tag_exlude_sql = " $wpdb->term_taxonomy.term_id != $exlude_ids[0] ";
				  	 }
				  }else{
				  	 $cat_or_tag_exlude_sql = " 1=1 ";
				  }
				  
				  $cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'category' AND ".$cat_or_tag_include_sql." AND ".$cat_or_tag_exlude_sql;
			}else {
				  $catid = intval($category_id);
				  if($catid > 0){
							$cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id = $catid ";
				  }else{
				  	 	$catid = -$catid;
				  	 	$cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id != $catid ";
				  }
			}
		}else if($tag_id!=0){
				$user_cat_tag = "INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";
				if(is_array($tag_id)) {
					foreach($tag_id as $tagid){
				  	 $tagid = intval($tagid);
				  	 if($tagid > 0){
				  	 		$include_ids[] = $tagid;
				  	 }else{
				  	 	  $exlude_ids[] = -$tagid;
				  	 }
					}
					
					if(!empty($include_ids)){
				  	 if(count($include_ids) != 1){
				  	 	 $cat_or_tag_include_sql = " $wpdb->term_taxonomy.term_id IN (".join(',', $include_ids).") ";
				  	 }else{
				  	 	 $cat_or_tag_include_sql = " $wpdb->term_taxonomy.term_id = $include_ids[0] ";
				  	 }
				  }else{
				  	 $cat_or_tag_include_sql = " 1=1 ";
				  }
				  if(!empty($exlude_ids)){
				  	 if(count($exlude_ids) != 1){
				  	 	 $cat_or_tag_exlude_sql = " $wpdb->term_taxonomy.term_id NOT IN (".join(',', $exlude_ids).") ";
				  	 }else{
				  	 	 $cat_or_tag_exlude_sql = " $wpdb->term_taxonomy.term_id != $exlude_ids[0] ";
				  	 }
				  }else{
				  	 $cat_or_tag_exlude_sql = " 1=1 ";
				  }
				  
				  $cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'post_tag' AND ".$cat_or_tag_include_sql." AND ".$cat_or_tag_exlude_sql;
		 		}else{
		 	 	$tagid = intval($tag_id);
				if($tagid > 0){
						$cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'post_tag' AND $wpdb->term_taxonomy.term_id = $tagid ";
				}else{
				  	$tagid = -$tagid;
				  	$cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'post_tag' AND $wpdb->term_taxonomy.term_id != $tagid ";
				}
		 		}
		}else{
			    $user_cat_tag= '';
				  $cat_or_tag_sql = '1=1';
		}
		$order_by = strtolower($order_by);
		if($orderby != 'asc') {
				$orderby = 'desc';
		}
	
		$sql= "SELECT DISTINCT $wpdb->posts.*, (".$time_span." + 0) AS views, latest_view_time FROM $wpdb->posts LEFT JOIN ".WP_POST_VIEWS_TABLE." ON ".WP_POST_VIEWS_TABLE.".post_id = $wpdb->posts.ID   $user_cat_tag  WHERE post_date < '".current_time('mysql')."' AND post_status = 'publish' AND $cat_or_tag_sql AND ".WP_POST_VIEWS_TABLE.".view_type = '".$view_type."' AND ".WP_POST_VIEWS_TABLE.".output_type = '".$output_type."' AND $mode AND post_password = '' ORDER  BY $order_type $order_by LIMIT $limit";
		$post_viewed = $wpdb->get_results($sql);
		$count=0;
		if($post_viewed) {
			foreach ($post_viewed as $post) {
				$post_views = intval($post->views);
				$post_title = get_the_title();		
				$view_time = post_views_localtime($post->latest_view_time,post_views_timezone());
				if($chars > 0) {
						if(!function_exists('cut_str')){
								$post_title = snippet_text($post_title, $chars);
						}else{
								$post_title = cut_str($post_title, $chars);
						}
				}
				$pv_views[$count] = array('title' => '<a href="'.get_permalink().'"  class="dashedline" >'.$post_title.'</a>', 'views' => number_format($post_views), 'time' => $view_time, 'post_id' => $post->ID );
				$count++;
			}
		}
		return $pv_views;
}

function get_post_category_list($mode = '', $view_type = 'normal', $output_type = 'content', $time_span = 'total', $order_type = 'views', $order_by = 'DESC', $limit = 10, $chars = 0){
		global $wpdb, $post;
		for($i=0;$i < $limit;$i++){
				$pv_views[$i] = array('title' => '', 'views' => '', 'time' => '' );
		}
		
		if(!empty($mode) && $mode != 'both') {
			$mode = "post_type = '$mode'";
		} else {
			$mode = '1=1';
		}
		if($time_span == 'today'){
			$time_span = 'post_views_today';
		}else if($time_span == 'week'){
			$time_span = 'post_views_week';
		}else if($time_span == 'month'){
			$time_span = 'post_views_month';
		}else if($time_span == 'halfyear'){
			$time_span = 'post_views_halfyear';
		}else if($time_span == 'year'){
			$time_span = 'post_views_year';
		}else{
			$time_span = 'post_views_total';
		}

		$user_cat_tag = "INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id and $wpdb->term_taxonomy.taxonomy = 'category') INNER JOIN $wpdb->terms ON ( $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id) ";
		$groupby = " group by $wpdb->term_taxonomy.term_id";

		$order_by = strtolower($order_by);
		if($orderby != 'asc') {
				$orderby = 'desc';
		}
	
		$sql= "SELECT $wpdb->terms.term_id as id, $wpdb->terms.name as name, sum(".$time_span." + 0) AS views, latest_view_time FROM $wpdb->posts LEFT JOIN ".WP_POST_VIEWS_TABLE." ON ".WP_POST_VIEWS_TABLE.".post_id = $wpdb->posts.ID  $user_cat_tag  WHERE  post_date < '".current_time('mysql')."' AND post_status = 'publish' AND ".WP_POST_VIEWS_TABLE.".view_type = '".$view_type."' AND ".WP_POST_VIEWS_TABLE.".output_type = '".$output_type."' AND $mode AND post_password = '' $groupby ORDER  BY $order_type $order_by LIMIT $limit";
		$post_viewed = $wpdb->get_results($sql);
		$count=0;
		if($post_viewed) {
			foreach ($post_viewed as $post) {
				$post_views = intval($post->views);
				$post_title = $post->name;
				if($chars > 0) {
						if(!function_exists('cut_str')){
								$post_title = snippet_text($post_title, $chars);
						}else{
								$post_title = cut_str($post_title, $chars);
						}
				}
				$pv_views[$count] = array('title' => '<a href="'.get_category_link($post->id).'"  class="dashedline" >'.$post_title.'</a>', 'views' => number_format($post_views), 'time' => "", 'post_id' => "" );
				$count++;
			}
		}
		return $pv_views;
}

function get_posts_list_with_views_details($mode = '', $view_type = 'normal', $output_type = 'content', $time_span = 'total', $order_type = 'views', $order_by = 'DESC', $limit = 10, $chars = 0, $category_id = 0, $tag_id = 0){
		global $wpdb, $post;
		for($i=0;$i < $limit;$i++){
				$pv_views[$i] = array('title' => '', 'views' => '', 'time' => '' );
		}
		
		if(!empty($mode) && $mode != 'both') {
			$mode = "post_type = '$mode'";
		} else {
			$mode = '1=1';
		}
		if($time_span == 'today'){
			$time_span = 'post_views_today';
		}else if($time_span == 'week'){
			$time_span = 'post_views_week';
		}else if($time_span == 'month'){
			$time_span = 'post_views_month';
		}else if($time_span == 'halfyear'){
			$time_span = 'post_views_halfyear';
		}else if($time_span == 'year'){
			$time_span = 'post_views_year';
		}else{
			$time_span = 'post_views_total';
		}

		if($category_id != 0){
			$user_cat_tag = "INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";
			if(is_array($category_id)) {
				  foreach($category_id as $catid){
				  	 $catid = intval($catid);
				  	 if($catid > 0){
				  	 		$include_ids[] = $catid;
				  	 }else{
				  	 	  $exlude_ids[] = -$catid;
				  	 }
				  }
				  if(!empty($include_ids)){
				  	 if(count($include_ids) != 1){
				  	 	 $cat_or_tag_include_sql = " $wpdb->term_taxonomy.term_id IN (".join(',', $include_ids).") ";
				  	 }else{
				  	 	 $cat_or_tag_include_sql = " $wpdb->term_taxonomy.term_id = $include_ids[0] ";
				  	 }
				  }else{
				  	 $cat_or_tag_include_sql = " 1=1 ";
				  }
				  if(!empty($exlude_ids)){
				  	 if(count($exlude_ids) != 1){
				  	 	 $cat_or_tag_exlude_sql = " $wpdb->term_taxonomy.term_id NOT IN (".join(',', $exlude_ids).") ";
				  	 }else{
				  	 	 $cat_or_tag_exlude_sql = " $wpdb->term_taxonomy.term_id != $exlude_ids[0] ";
				  	 }
				  }else{
				  	 $cat_or_tag_exlude_sql = " 1=1 ";
				  }
				  
				  $cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'category' AND ".$cat_or_tag_include_sql." AND ".$cat_or_tag_exlude_sql;
			}else {
				  $catid = intval($category_id);
				  if($catid > 0){
							$cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id = $catid ";
				  }else{
				  	 	$catid = -$catid;
				  	 	$cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id != $catid ";
				  }
			}
		}else if($tag_id!=0){
				$user_cat_tag = "INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)";
				if(is_array($tag_id)) {
					foreach($tag_id as $tagid){
				  	 $tagid = intval($tagid);
				  	 if($tagid > 0){
				  	 		$include_ids[] = $tagid;
				  	 }else{
				  	 	  $exlude_ids[] = -$tagid;
				  	 }
					}
					
					if(!empty($include_ids)){
				  	 if(count($include_ids) != 1){
				  	 	 $cat_or_tag_include_sql = " $wpdb->term_taxonomy.term_id IN (".join(',', $include_ids).") ";
				  	 }else{
				  	 	 $cat_or_tag_include_sql = " $wpdb->term_taxonomy.term_id = $include_ids[0] ";
				  	 }
				  }else{
				  	 $cat_or_tag_include_sql = " 1=1 ";
				  }
				  if(!empty($exlude_ids)){
				  	 if(count($exlude_ids) != 1){
				  	 	 $cat_or_tag_exlude_sql = " $wpdb->term_taxonomy.term_id NOT IN (".join(',', $exlude_ids).") ";
				  	 }else{
				  	 	 $cat_or_tag_exlude_sql = " $wpdb->term_taxonomy.term_id != $exlude_ids[0] ";
				  	 }
				  }else{
				  	 $cat_or_tag_exlude_sql = " 1=1 ";
				  }
				  
				  $cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'post_tag' AND ".$cat_or_tag_include_sql." AND ".$cat_or_tag_exlude_sql;
		 		}else{
		 	 	$tagid = intval($tag_id);
				if($tagid > 0){
						$cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'post_tag' AND $wpdb->term_taxonomy.term_id = $tagid ";
				}else{
				  	$tagid = -$tagid;
				  	$cat_or_tag_sql = " $wpdb->term_taxonomy.taxonomy = 'post_tag' AND $wpdb->term_taxonomy.term_id != $tagid ";
				}
		 		}
		}else{
			    $user_cat_tag= '';
				  $cat_or_tag_sql = '1=1';
		}
		$order_by = strtolower($order_by);
		if($orderby != 'asc') {
				$orderby = 'desc';
		}
	
		$sql= "SELECT DISTINCT $wpdb->posts.*, (".$time_span." + 0) AS views, latest_view_time FROM $wpdb->posts LEFT JOIN ".WP_POST_VIEWS_TABLE." ON ".WP_POST_VIEWS_TABLE.".post_id = $wpdb->posts.ID   $user_cat_tag  WHERE post_date < '".current_time('mysql')."' AND post_status = 'publish' AND $cat_or_tag_sql AND ".WP_POST_VIEWS_TABLE.".view_type = '".$view_type."' AND ".WP_POST_VIEWS_TABLE.".output_type = '".$output_type."' AND $mode AND post_password = '' ORDER  BY $order_type $order_by LIMIT $limit";

		$post_viewed = $wpdb->get_results($sql);
		return $post_viewed;		
}

function get_post_views($view_type = 'normal',$timespan = 'total',$output_type = 'content',$format = false,$post_id = '',$by_front = false) {
	global $wpdb,$post;
	if(empty($post_id)){
		$post_id = $post->ID;		
	}
	
	$pv_rec_options = get_option('post_views_rec_options');
  if(!empty($pv_rec_options)){
			$real_time_views = $pv_rec_options['real_time_views'];
	}
	
	$views =  intval($wpdb->get_var($wpdb->prepare("SELECT post_views_".$timespan." FROM ".WP_POST_VIEWS_TABLE." where view_type= '".$view_type."' and output_type= '".$output_type."' and post_id=".$post_id, APP_POST_TYPE)));
	
	if(cache_enable() && ($by_front != true) && ($real_time_views == 1)){
		 	$cache_views  = "";
		  $cache_views .= "<label id=\"post_views_".$view_type."_".$output_type."_".$timespan."_".$post_id."\">".number_format($views);	
		  $cache_views .= "</label>";
		  $cache_views .= "\n".'<script type="text/javascript">'."\n";
		  $cache_views .= 'jQuery.ajax({type:"GET",async:false,url:"'.plugins_url('post-views/post-views.php').'",data:"cache_post_id='.$post_id.'&cache_view_type='.$view_type.'&cache_output_type='.$output_type.'&cache_time_span='.$timespan.'",cache:false,success: function(responseText, textStatus, XMLHttpRequest){this;document.getElementById("post_views_'.$view_type.'_'.$output_type.'_'.$timespan.'_'.$post_id.'").innerText = responseText;}});'; 
		  $cache_views .= "\n</script>\n";		
		  
		  return $cache_views;
		  
	}else{
		if($format){
			return number_format($views);
		}else{
			return $views;
		}
	}

}
function get_post_views_time_cache($id){
		global $wpdb;
		if(!empty($id)){
				$last_visited_time = $wpdb->get_var($wpdb->prepare("SELECT latest_view_time FROM ".WP_POST_VIEWS_TABLE." where view_type='normal'and output_type='content' and post_id=".$id, APP_POST_TYPE));
		}
		
		return get_time_diff(time(),strtotime($last_visited_time));
}

function get_post_views_time($v_type = 'normal', $o_type = 'content', $format = 'Y-m-d H:i:s',$by_front = false){
	global $wpdb,$post,$last_visited_time;
	$post_id = $post->ID;		
	$pv_rec_options = get_option('post_views_rec_options');
  	if(!empty($pv_rec_options)){
			$real_time_views = $pv_rec_options['real_time_views'];
	}
	if(cache_enable() && ($by_front != true) && ($real_time_views == 1)){
			$cache_views  = "";
		  $cache_views .= "<label id=\"post_views_last_time_".$post_id."\">". __('Never','post-views');	
		  $cache_views .= "</label>";
		  $cache_views .= "\n".'<script type="text/javascript">'."\n";
		  $cache_views .= 'jQuery.ajax({type:"GET",async:false,url:"'.plugins_url('post-views/post-views.php').'",data:"last_view_time=timespan&cache_post_id='.$post_id.'",cache:false,success: function(responseText, textStatus, XMLHttpRequest){this;document.getElementById("post_views_last_time_'.$post_id.'").innerText = responseText;}});'; 
		  $cache_views .= "\n</script>\n";		
		  
		  return $cache_views;
	}else{
  	if($format == 'timespan'){
		 	if(empty($last_visited_time[$v_type][$o_type])){
		 	 			return __('Never','post-views');
		 	}else{
		 	 			return get_time_diff(time(),strtotime($last_visited_time[$v_type][$o_type]));
		 	}
		}else{		
		 	if(empty($last_visited_time[$v_type][$o_type])){
		 	 			return __('Never','post-views');
		 	}else{
		 	 			return post_views_localtime($last_visited_time[$v_type][$o_type],post_views_timezone(),$format);
		 	}
		}
	}
}

function get_post_views_sum($view_type = 'normal', $timespan = 'total',$output_type = 'content',$format = false) {
	global $wpdb;
	
	if($timespan == 'today') {
    	$timespan = 'post_views_today';
	}else if($timespan == 'week') {
    	$timespan = 'post_views_week';
	}else if($timespan == 'month') {
    	$timespan = 'post_views_month';
	}else if($timespan == 'halfyear') {
    	$timespan = 'post_views_halfyear';
	}else if($timespan == 'year') {
    	$timespan = 'post_views_year';
	}else{
	 		$timespan = 'post_views_total';
	}

	$sum_views = intval($wpdb->get_var("SELECT SUM(".$timespan.") FROM ".WP_POST_VIEWS_TABLE." WHERE view_type = '".$view_type."' and output_type='".$output_type."'"));
	if($format){
			return number_format($sum_views);
	}else{
			return $sum_views;
	}
}

function get_post_viewed_count($view_type,$time_span,$output_type){
	global $wpdb;
	$time_span = "post_views_".$time_span;
  $count = $wpdb->get_var($wpdb->prepare("SELECT count(post_id) FROM ".WP_POST_VIEWS_TABLE." where view_type='".$view_type."' and output_type='".$output_type."' and ".$time_span." !=0", APP_POST_TYPE));
	return $count;
}

function get_post_views_trend($view_type,$time_span,$output_type,$year_id,$post_id = 0){
	global $wpdb;
	$time_span = "post_views_".$time_span;
	if($year_id ==""){
			$year_id = get_year();
	}
	
	if($post_id == 0){
			if($output_type == "excerpt"){
					$output_type = "previews";
			}else{
					$output_type = "views";
			}
  		$trend = $wpdb->get_var($wpdb->prepare("SELECT ".$time_span." FROM ".WP_POST_VIEWS_SUMMARY_TABLE." where summary_type='".$view_type."_".$output_type."' and year_id=".$year_id, APP_POST_TYPE));
	}else{
		  $trend = $wpdb->get_var($wpdb->prepare("SELECT ".$time_span." FROM ".WP_POST_VIEWS_HIS_TABLE." where post_id =".$post_id." and view_type ='".$view_type."' and output_type ='".$output_type."' and year_id=".$year_id, APP_POST_TYPE));		
	}
	
	return  $trend;
}

function get_post_viewed_count_trend($view_type,$time_span,$output_type,$year_id = 0){
	global $wpdb;
	$time_span = "post_views_".$time_span;
	if($output_type == "excerpt"){
			$output_type = "previewed";
	}else{
			$output_type = "viewed";
	}
	if($year_id ==""){
			$year_id = get_year();
	}
  $trend = $wpdb->get_var($wpdb->prepare("SELECT ".$time_span." FROM ".WP_POST_VIEWS_SUMMARY_TABLE." where summary_type='".$view_type."_".$output_type."' and year_id=".$year_id, APP_POST_TYPE));
	
	return  $trend;
}

function get_post_viewed_coverage_trend($view_type,$time_span,$output_type,$year_id){
	global $wpdb;
	$time_span = "post_views_".$time_span;
	if($output_type == "excerpt"){
			$output_type = "previewed";
	}else{
			$output_type = "viewed";
	}
	if($year_id == ""){
		$year_id = get_year();
	}
	
  $trend = $wpdb->get_var($wpdb->prepare("SELECT ".$time_span." FROM ".WP_POST_VIEWS_SUMMARY_TABLE." where summary_type='".$view_type."_".$output_type."_coverage' and year_id=".$year_id, APP_POST_TYPE));
	return  $trend;
}

/**************************************** Invoke Functions *******************************/



/*****************************************************************
**  Post Last Viewed Time
*****************************************************************/

function get_post_last_viewed_time($format){
  return get_post_views_time('normal','content',$format,false);
}

function get_post_last_previewed_time($format){
  return get_post_views_time('normal','excerpt',$format,false);
}

function get_post_last_robotviewed_time($format){
  return get_post_views_time('robot','content',$format,false);
}

function get_post_last_robotpreviewed_time($format){
  return get_post_views_time('robot','excerpt',$format,false);
}



/*****************************************************************
**  Post Views Sum
*****************************************************************/

function get_post_total_views_sum(){
	return get_post_views_sum('normal','total','content');	
}
function get_post_today_views_sum(){
	return get_post_views_sum('normal','today','content');	
}
function get_post_week_views_sum(){
	return get_post_views_sum('normal','week','content');	
}
function get_post_month_views_sum(){
	return get_post_views_sum('normal','month','content');	
}
function get_post_halfyear_views_sum(){
	return get_post_views_sum('normal','halfyear','content');	
}
function get_post_year_views_sum(){
	return get_post_views_sum('normal','year','content');	
}
function get_post_total_robotviews_sum(){
	return get_post_views_sum('robot','total','content');	
}
function get_post_today_robotviews_sum(){
	return get_post_views_sum('robot','today','content');	
}
function get_post_week_robotviews_sum(){
	return get_post_views_sum('robot','week','content');	
}
function get_post_month_robotviews_sum(){
	return get_post_views_sum('robot','month','content');	
}
function get_post_halfyear_robotviews_sum(){
	return get_post_views_sum('robot','halfyear','content');	
}
function get_post_year_robotviews_sum(){
	return get_post_views_sum('robot','year','content');	
}

function get_post_total_previews_sum(){
	return get_post_views_sum('normal','total','excerpt');	
}
function get_post_today_previews_sum(){
	return get_post_views_sum('normal','today','excerpt');	
}
function get_post_week_previews_sum(){
	return get_post_views_sum('normal','week','excerpt');	
}
function get_post_month_previews_sum(){
	return get_post_views_sum('normal','month','excerpt');	
}
function get_post_halfyear_previews_sum(){
	return get_post_views_sum('normal','halfyear','excerpt');	
}
function get_post_year_previews_sum(){
	return get_post_views_sum('normal','year','excerpt');	
}
function get_post_total_robotpreviews_sum(){
	return get_post_views_sum('robot','total','excerpt');	
}
function get_post_today_robotpreviews_sum(){
	return get_post_views_sum('robot','today','excerpt');	
}
function get_post_week_robotpreviews_sum(){
	return get_post_views_sum('robot','week','excerpt');	
}
function get_post_month_robotpreviews_sum(){
	return get_post_views_sum('robot','month','excerpt');	
}
function get_post_halfyear_robotpreviews_sum(){
	return get_post_views_sum('robot','halfyear','excerpt');	
}
function get_post_year_robotpreviews_sum(){
	return get_post_views_sum('robot','year','excerpt');	
}

/*****************************************************************
**  Post Views
*****************************************************************/


function get_post_total_views(){	
	return get_post_views('normal','total','content');	
}
function get_post_total_previews(){	
	return get_post_views('normal','total','excerpt');	
}
function get_post_today_views(){	
	return get_post_views('normal','today','content');	
}
function get_post_today_previews(){	
	return get_post_views('normal','today','excerpt');	
}
function get_post_week_views(){	
	return get_post_views('normal','week','content');	
}
function get_post_week_previews(){	
	return get_post_views('normal','week','excerpt');	
}
function get_post_month_views(){	
	return get_post_views('normal','month','content');	
}
function get_post_month_previews(){	
	return get_post_views('normal','month','excerpt');	
}
function get_post_halfyear_views(){	
	return get_post_views('normal','week','content');	
}
function get_post_halfyear_previews(){	
	return get_post_views('normal','week','excerpt');	
}
function get_post_year_views(){	
	return get_post_views('normal','year','content');	
}
function get_post_year_previews(){	
	return get_post_views('normal','year','excerpt');	
}
function get_post_total_robotviews(){	
	return get_post_views('robot','total','content');	
}
function get_post_total_robotpreviews(){	
	return get_post_views('robot','total','excerpt');	
}
function get_post_today_robotviews(){	
	return get_post_views('robot','today','content');	
}
function get_post_today_robotpreviews(){	
	return get_post_views('robot','today','excerpt');	
}
function get_post_week_robotviews(){	
	return get_post_views('robot','week','content');	
}
function get_post_week_robotpreviews(){	
	return get_post_views('robot','week','excerpt');	
}
function get_post_month_robotviews(){	
	return get_post_views('robot','month','content');	
}
function get_post_month_robotpreviews(){	
	return get_post_views('robot','month','excerpt');	
}
function get_post_halfyear_robotviews(){	
	return get_post_views('robot','week','content');	
}
function get_post_halfyear_robotpreviews(){	
	return get_post_views('robot','week','excerpt');	
}
function get_post_year_robotviews(){	
	return get_post_views('robot','year','content');	
}
function get_post_year_robotpreviews(){	
	return get_post_views('robot','year','excerpt');	
}


/*****************************************************************
**  Post Views Coverage
*****************************************************************/
function get_post_total_views_coverage(){
	return get_post_viewed_coverage('normal','total','content','+','%');
}
function get_post_year_views_coverage(){
	return get_post_viewed_coverage('normal','year','content','+','%');
}
function get_post_halfyear_views_coverage(){
	return get_post_viewed_coverage('normal','halfyear','content','+','%');
}
function get_post_month_views_coverage(){
	return get_post_viewed_coverage('normal','month','content','+','%');
}
function get_post_week_views_coverage(){
	return get_post_viewed_coverage('normal','week','content','+','%');
}
function get_post_today_views_coverage(){
	return get_post_viewed_coverage('normal','today','content','+','%');
}
function get_post_total_previews_coverage(){
	return get_post_viewed_coverage('normal','total','excerpt','+','%');
}
function get_post_year_previews_coverage(){
	return get_post_viewed_coverage('normal','year','excerpt','+','%');
}
function get_post_halfyear_previews_coverage(){
	return get_post_viewed_coverage('normal','halfyear','excerpt','+','%');
}
function get_post_month_previews_coverage(){
	return get_post_viewed_coverage('normal','month','excerpt','+','%');
}
function get_post_week_previews_coverage(){
	return get_post_viewed_coverage('normal','week','excerpt','+','%');
}
function get_post_today_previews_coverage(){
	return get_post_viewed_coverage('normal','today','excerpt','+','%');
}
function get_post_total_robotviews_coverage(){
	return get_post_viewed_coverage('robot','total','content','+','%');
}
function get_post_year_robotviews_coverage(){
	return get_post_viewed_coverage('robot','year','content','+','%');
}
function get_post_halfyear_robotviews_coverage(){
	return get_post_viewed_coverage('robot','halfyear','content','+','%');
}
function get_post_month_robotviews_coverage(){
	return get_post_viewed_coverage('robot','month','content','+','%');
}
function get_post_week_robotviews_coverage(){
	return get_post_viewed_coverage('robot','week','content','+','%');
}
function get_post_today_robotviews_coverage(){
	return get_post_viewed_coverage('robot','today','content','+','%');
}
function get_post_total_robotpreviews_coverage(){
	return get_post_viewed_coverage('robot','total','excerpt','+','%');
}
function get_post_year_robotpreviews_coverage(){
	return get_post_viewed_coverage('robot','year','excerpt','+','%');
}
function get_post_halfyear_robotpreviews_coverage(){
	return get_post_viewed_coverage('robot','halfyear','excerpt','+','%');
}
function get_post_month_robotpreviews_coverage(){
	return get_post_viewed_coverage('robot','month','excerpt','+','%');
}
function get_post_week_robotpreviews_coverage(){
	return get_post_viewed_coverage('robot','week','excerpt','+','%');
}
function get_post_today_robotpreviews_coverage(){
	return get_post_viewed_coverage('robot','today','excerpt','+','%');
}

/*****************************************************************
**  Post Viewed
*****************************************************************/


function get_post_total_viewed(){	
	return get_post_viewed_count('normal','total','content');	
}
function get_post_total_previewed(){	
	return get_post_viewed_count('normal','total','excerpt');	
}
function get_post_today_viewed(){	
	return get_post_viewed_count('normal','today','content');	
}
function get_post_today_previewed(){	
	return get_post_viewed_count('normal','today','excerpt');	
}
function get_post_week_viewed(){	
	return get_post_viewed_count('normal','week','content');	
}
function get_post_week_previewed(){	
	return get_post_viewed_count('normal','week','excerpt');	
}
function get_post_month_viewed(){	
	return get_post_viewed_count('normal','month','content');	
}
function get_post_month_previewed(){	
	return get_post_viewed_count('normal','month','excerpt');	
}
function get_post_halfyear_viewed(){	
	return get_post_viewed_count('normal','week','content');	
}
function get_post_halfyear_previewed(){	
	return get_post_viewed_count('normal','week','excerpt');	
}
function get_post_year_viewed(){	
	return get_post_viewed_count('normal','year','content');	
}
function get_post_year_previewed(){	
	return get_post_viewed_count('normal','year','excerpt');	
}
function get_post_total_robotviewed(){	
	return get_post_viewed_count('robot','total','content');	
}
function get_post_total_robotpreviewed(){	
	return get_post_viewed_count('robot','total','excerpt');	
}
function get_post_today_robotviewed(){	
	return get_post_viewed_count('robot','today','content');	
}
function get_post_today_robotpreviewed(){	
	return get_post_viewed_count('robot','today','excerpt');	
}
function get_post_week_robotviewed(){	
	return get_post_viewed_count('robot','week','content');	
}
function get_post_week_robotpreviewed(){	
	return get_post_viewed_count('robot','week','excerpt');	
}
function get_post_month_robotviewed(){	
	return get_post_viewed_count('robot','month','content');	
}
function get_post_month_robotpreviewed(){	
	return get_post_viewed_count('robot','month','excerpt');	
}
function get_post_halfyear_robotviewed(){	
	return get_post_viewed_count('robot','week','content');	
}
function get_post_halfyear_robotpreviewed(){	
	return get_post_viewed_count('robot','week','excerpt');	
}
function get_post_year_robotviewed(){	
	return get_post_viewed_count('robot','year','content');	
}
function get_post_year_robotpreviewed(){	
	return get_post_viewed_count('robot','year','excerpt');	
}
?>