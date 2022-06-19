<?php
/**
 * Plugin Name: Polls
 * Plugin URI: http://wpdevart.com/wordpress-polls-plugin/
 * Description: WordPress Polls plugin is an nice tool for creating polls and survey forms. You can use our polls on widgets, posts and pages. WordPress Polls plugin have user-friendly admin panel, so you can create polls and survey forms easily and quickly.   
 * Version: 1.0.7
 * Author: wpdevart
 * Author URI:    http://wpdevart.com
 * License URI: GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
 

class polls{
	// required variables
	
	private $plugin_url;
	
	private $plugin_path;
	
	private $version;
	
	public $options;
	
	
	function __construct(){
		
		$this->plugin_url  = trailingslashit( plugins_url('', __FILE__ ) );
		$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		$this->version     = 1.0;
		$this->call_base_filters();
		$this->install_databese();
		$this->create_admin_menu();	
		$this->front_end();
		
	}
	
	private function create_admin_menu(){
		
		require_once($this->plugin_path.'admin/admin_menu.php');
		
		$admin_menu = new poll_admin_menu(array('plugin_url' => $this->plugin_url,'plugin_path' => $this->plugin_path));
		
		add_action('admin_menu', array($admin_menu,'create_menu'));
		
	}
	
	private function install_databese(){	
		
		require_once($this->plugin_path.'includes/install_database.php');
		
		$coming_install_database = new polls_install_database(array('plugin_url' => $this->plugin_url,'plugin_path' => $this->plugin_path));
				
	}
	
	public function front_end(){
				
		require_once($this->plugin_path.'fornt_end/front_end.php');
		global $poll_front_end;
		$poll_front_end = new poll_front_end(array('menu_name' => 'Polls','plugin_url' => $this->plugin_url,'plugin_path' => $this->plugin_path));
		require_once($this->plugin_path.'fornt_end/fornt_end_widget.php');
		
		
		add_action('widgets_init', create_function('', 'return register_widget("poll_widget");'));
	}
	
	public function registr_requeried_scripts(){
		
		wp_register_script('angularejs',$this->plugin_url.'admin/scripts/angular.min.js');
		wp_register_script('poll_front_end_script',$this->plugin_url.'fornt_end/scripts/scripts_front_end_poll.js');
		wp_register_style('admin_style',$this->plugin_url.'admin/styles/admin_themplate.css');
		wp_register_style('front_end_poll',$this->plugin_url.'fornt_end/styles/baze_styles_for_poll.css');
		wp_register_style('jquery-ui-style',$this->plugin_url.'admin/styles/jquery-ui.css');
		
	}
	
	public function call_base_filters(){
		add_action( 'init',  array($this,'registr_requeried_scripts') );
	}
  	
	private function include_file(){
	
	}
}
$poll = new polls();

?>