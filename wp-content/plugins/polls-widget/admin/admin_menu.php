<?php 

/*############  Poll Admin Menu Class ################*/

class poll_admin_menu{
	
	private $menu_name;	
		
	private $databese_parametrs;
	
	private $plugin_url;
	
	private $plugin_path;
	
	private $text_parametrs;
	
	function __construct($param){
		$this->menu_name='Polls';
		$this->text_parametrs=array(
			''=>'',
		);		
		$this->databese_parametrs=$param['databese_parametrs']; //databese parametrs
		
		
		// set plugin url
		if(isset($param['plugin_url']))
			$this->plugin_url=$param['plugin_url'];
		else
			$this->plugin_url=trailingslashit(dirname(plugins_url('',__FILE__)));
		// set plugin path
		if(isset($param['plugin_path']))
			$this->plugin_path=$param['plugin_path'];
		else
			$this->plugin_path=trailingslashit(dirname(plugin_dir_path(__FILE__)));
		//admin style
		add_action('admin_head',array($this,'include_all_admin_styles'));
		//// add editor new mce button
		add_filter('mce_external_plugins', array($this,"poll_button_register"));
		add_filter('mce_buttons',  array($this,'poll_add_button'), 0);
		add_action('wp_ajax_poll_window_manager',array($this,'poll_create_window'));


	}
	/// function for add new button
	function poll_add_button($buttons)
	{
		array_push($buttons, "poll_mce");
		return $buttons;
	}
	
	
	/// function for registr new button
	function poll_button_register($plugin_array)
	{
		$url = $this->plugin_url.'admin/scripts/editor_plugin.js';
		$plugin_array["poll_mce"] = $url;
		return $plugin_array;
	
	}

	public function include_all_admin_styles(){
		?><style>#toplevel_page_Poll img{padding-top:4px !important;}</style>
		<script>var poll_admin_url = "<?php echo $this->plugin_url; ?>";</script>
        <script>var poll_admin_ajax= '<?php echo admin_url("admin-ajax.php"); ?>';</script>
		<?php
	}
	public function poll_create_window(){
		?>
		<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Polls</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script language="javascript" type="text/javascript" src="<?php echo get_option("siteurl"); ?>/wp-includes/js/jquery/jquery.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo get_option("siteurl"); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>        
        <script language="javascript" type="text/javascript" src="<?php echo get_option("siteurl"); ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
        <script language="javascript" type="text/javascript" src="<?php echo get_option("siteurl"); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
        <base target="_self">
        <style>
            #link .panel_wrapper,
            #link div.current {
                height: 160px !important;
            }

        </style>
    </head>
    <body id="link" onLoad="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="" dir="ltr"  class="forceColors">
    <?php
		global $wpdb;
		$defaults = array( 'title' => '', calendar => '0', theme => '0');
		$instance = wp_parse_args( (array) $instance, $defaults );
		$poll_answers=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'polls_question');
		$poll_themes=$wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'polls_templates');
		?>
		<table width="100%" class="paramlist admintable" cellspacing="1" style="margin-bottom: 40px;">
            <tbody>
                <tr>
                    <td class="paramlist_key">
                    	<span class="editlinktip">
                        	<label style="font-size:14px" id="paramsstandcatid-lbl" for="Category" class="hasTip">Select Poll: </label>
                        </span>
                    </td>
                    <td class="paramlist_value" >
                    <select id="poll_answer_id" style="font-size:12px;width:100%" class="inputbox">
                    <option value="0">Select poll</option>
                    <?php                    
                    foreach($poll_answers as $poll_answer)
                    {
                       ?><option value="<?php echo $poll_answer->id?>" ><?php echo $poll_answer->name ?></option><?php
                    }
                    ?>
                    </select>
                    </td>
                </tr>
                <tr>
                    <td class="paramlist_key">
                    	<span class="editlinktip">
                        	<label style="font-size:14px" id="paramsstandcatid-lbl" for="Category" class="hasTip">Select Poll Theme: </label>
                        </span>
                    </td>
                    <td class="paramlist_value" >
                    <select name="" id="poll_theme_id" style="font-size:12px;width:100%" class="inputbox">
                    <option value="0">Select poll theme</option>
                    <?php                    
                    foreach($poll_themes as $poll_theme)
                    {                 
                        ?><option value="<?php echo $poll_theme->id?>"><?php echo $poll_theme->name ?></option><?php                        
                    }
                    ?>
                    </select>
                    </td>
                </tr>
                <tr>
                	<td><br /></td>
                </tr>
            </tbody>
		</table>
   <div class="mceActionPanel">
            <div style="float: left">
                <input type="button" id="cancel" name="cancel" value="Cancel" onClick="tinyMCEPopup.close();"/>
            </div>

            <div style="float: right">
                <input type="submit" id="insert" name="insert" value="Insert" onClick="insert_poll();"/>
                <input type="hidden" name="iden" value="1"/>
            </div>
        </div>
	

    <script type="text/javascript">
        function insert_poll() {
              
			if(jQuery('#poll_answer_id').val()!='0'){
                var tagtext;
                tagtext = '[wpdevart_poll id="' + jQuery('#poll_answer_id').val() + '" theme="' +  jQuery('#poll_theme_id').val() + '"]';
                window.tinyMCE.execCommand('mceInsertContent', false, tagtext);
                tinyMCEPopup.close();
            }
			else{
				tinyMCEPopup.close();
			}
        }

    </script>
    </body>
    </html>
    <?php
	die();
	}
	public function create_menu(){
		//include classes
		require_once($this->plugin_path.'admin/answers_page.php');
		require_once($this->plugin_path.'admin/themplate_page.php');
		require_once($this->plugin_path.'admin/uninstall.php');
		//initial class objects
		$answers_class  =new poll_manager_answers( array( 'plugin_url'=> $this->plugin_url, 'plugin_path' => $this->plugin_path));
		$template_class =new poll_manager_design( array( 'plugin_url' => $this->plugin_url, 'plugin_path' => $this->plugin_path));
		$uninstall_class=new poll_uninstall( array( 'plugin_url' => $this->plugin_url, 'plugin_path' => $this->plugin_path));
		
		$manage_page_main = add_menu_page( $this->menu_name, $this->menu_name, 'manage_options', str_replace( ' ', '-', $this->menu_name), array($answers_class, 'controller_page'),$this->plugin_url.'admin/images/icon-polling.png');
							add_submenu_page( str_replace( ' ', '-', $this->menu_name), 'Polls manager', 'Polls manager', 'manage_options', str_replace( ' ', '-', $this->menu_name), array($answers_class, 'controller_page'));
		$page_design	  = add_submenu_page( str_replace( ' ', '-', $this->menu_name), 'Polls design', 'Polls design', 'manage_options', 'Polls-design', array($template_class, 'controller_page'));
		$uninstall		  = add_submenu_page( str_replace( ' ', '-', $this->menu_name), 'Uninstall'  , 'Uninstall', 'manage_options', 'Polls-uninstall', array($uninstall_class, 'controller_page'));
		add_action('admin_print_styles-' .$manage_page_main, array($this,'menu_requeried_scripts'));
		add_action('admin_print_styles-' .$page_design, array($this,'menu_requeried_scripts'));	
	}
	public function menu_requeried_scripts(){
		wp_enqueue_script('jquery-ui-style');+
		wp_enqueue_script('jquery');	
		wp_enqueue_script('angularejs');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script("jquery-ui-widget");
		wp_enqueue_script("jquery-ui-mouse");
		wp_enqueue_script("jquery-ui-slider");
		wp_enqueue_script("jquery-ui-sortable");
		wp_enqueue_script('wp-color-picker');	
		wp_enqueue_style("jquery-ui-style");
		wp_enqueue_style("admin_style");		
		wp_enqueue_style( 'wp-color-picker' );
			
	}
	
}