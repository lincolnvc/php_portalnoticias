<?php 

/// class for Pools install databese
class polls_install_database{
	
	public $installed_options; // all standart_options
	private $plugin_url;
	private $plugin_path;
	function __construct($params=NULL){
		global $wpdb;
		
		if(isset($params['plugin_url']))
			$this->plugin_url=$params['plugin_url'];
		else
			$this->plugin_url=trailingslashit(dirname(plugins_url('',__FILE__)));	
		if(isset($params['plugin_path']))
			$this->plugin_path=$params['plugin_path'];
		else
			$this->plugin_path=trailingslashit(dirname(plugins_url('',__FILE__)));	
		register_activation_hook($this->plugin_path.'polls.php', array($this,'install_databese'));
		
	}
	public function install_databese(){		
		global $wpdb;
		///////////////////////////    users tabel for geting users.
		$table_name=$wpdb->prefix.'polls_users';
		$sql_table_creator="
		CREATE TABLE IF NOT EXISTS `".$table_name."` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_ip` varchar(255) NOT NULL,
		  `question_id` int(15) NOT NULL,
		  `answers` varchar(32768) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";		
		$wpdb->query($sql_table_creator);
			
			
			
			///////////////////////////   queshon table
		$table_name=$wpdb->prefix.'polls_question';
		$sql_table_creator="
		CREATE TABLE IF NOT EXISTS `".$table_name."` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(512) NOT NULL,
		  `answer_type` tinyint(4) NOT NULL,
		  `question` text NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";		
		$wpdb->query($sql_table_creator);
			
			
			
			///////////////////////////    main Polls table include answers i procuyu erundu
		$table_name=$wpdb->prefix.'polls';
		$sql_table_creator="
		CREATE TABLE IF NOT EXISTS `".$table_name."` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,		  
		  `question_id` int(15) NOT NULL,
		  `answer` varchar(32768) NOT NULL,
		  `answer_name` int(15) NOT NULL,
		  `vote` int(15) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$wpdb->query($sql_table_creator);
		
		
		
		$table_name=$wpdb->prefix.'polls_templates';
		$sql_table_creator="
		CREATE TABLE IF NOT EXISTS `".$table_name."` (
		  `id` int(10) NOT NULL AUTO_INCREMENT,
		  `name` varchar(512) NOT NULL,
		  `option_value` longtext NOT NULL,
		  `default` tinyint(4) NOT NULL,
		   PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$wpdb->query($sql_table_creator);
		$themes[0]['name']='Default Theme';
		$themes[0]['value']='{"poll_answer_width":"100","poll_answer_width_hower":"100","poll_answer_height":"23","poll_answer_height_hower":"23","poll_background_color":"#ffffff","poll_background_color_hower":"#ffffff","poll_background_opacity":"73","poll_background_opacity_hower":"65","poll_answer_inner_distance":"5","poll_answer_fontsize":"14","poll_answer_fontsize_hower":"14","poll_answer_of_the_top":"4","poll_answer_of_the_top_hower":"4","poll_answer_of_the_left":"5","poll_answer_of_the_left_hower":"5","color_responding_of_the_answer":"#afafaf","color_responding_of_the_answer_hower":"#afafaf","color_of_the_answer_text":"#0c0101","color_of_the_answer_text_hower":"#0c0101","font_family_of_answer_text":"Arial,Helvetica Neue,Helvetica,sans-serif","font_family_of_answer_text_hower":"Arial,Helvetica Neue,Helvetica,sans-serif","font_style_of_answer_text_bold_hower":"on","poll_answer_border_width":"1","poll_answer_border_width_hower":"1","poll_answer_border_radius":"5","poll_answer_border_radius_hower":"5","poll_answer_border_type":"outset","poll_answer_border_type_hower":"outset","color_of_the_border":"#3b5998","color_of_the_border_hower":"#3b5998","poll_show_count_option":"0","poll_show_count_option_hower":"1","poll_count_fontsize":"13","poll_count_fontsize_hower":"13","poll_count_of_the_left":"7","poll_count_of_the_left_hower":"7","poll_count_of_the_top":"5","poll_count_of_the_top_hower":"5","color_of_the_count":"#878787","color_of_the_count_hower":"#0c0c0c","font_family_of_count_text":"Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif","font_family_of_count_text_hower":"Arial Narrow,Arial,Helvetica Neue,Helvetica,sans-serif","font_style_of_count_text_bold":"on","font_style_of_count_text_bold_hower":"on","font_style_of_count_text_italick_hower":"on"}';
		
			
		
		foreach($themes as $key=>$theme){
			$wpdb->insert( $wpdb->prefix.'polls_templates', 
				array( 
					'id' =>$key+1,
					'name' => $theme['name'],
					'option_value' => $theme['value'],
					'default' =>(($key==0)?1:0)
				), 
				array( 
					'%d', 
					'%s', 
					'%s', 
					'%d', 
				) 			
			);
		}
	}
}