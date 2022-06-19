<?php
class poll_uninstall{
	
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
		if(isset( $_POST['uninstall_polls_bad'] )   && wp_verify_nonce( $_POST['uninstall_polls_bad'], 'uninstall_polls')){
			$this->remove_databese_and_deactivete();
			return;
		}
		$this->display_uninstall_main();
	}
	
	/*#################### Table List ########################*/
	/*#################### Table List ########################*/
	/*#################### Table List ########################*/
	private function display_uninstall_main(){
		?>
        <form method="post" action="admin.php?page=Polls-uninstall" style="width:99%;">
     <?php wp_nonce_field('uninstall_polls','uninstall_polls_bad'); ?>
      <div class="wrap">
        <span class="uninstall_icon"></span>
        <h2>Uninstall Polls</h2>
        <p>
          Deactivating Polls plugin does not remove any data that may have been created. To completely remove this plugin, you can uninstall it here.
        </p>
        <p style="color: red;">
          <strong>WARNING:</strong>
          Once uninstalled, this can't be undone. You should use a Database Backup plugin of WordPress to back up all the data first.
        </p>
        <p style="color: red">
          <strong>The following Database Tables will be deleted:</strong>
        </p>
        <table class="widefat">
          <thead>
            <tr>
              <th>Database Tables</th>
            </tr>
          </thead>
          <tr>
            <td valign="top">
              <ol>
                  <li><?php echo $prefix; ?>polls_users</li>
                  <li><?php echo $prefix; ?>polls_question</li>
                  <li><?php echo $prefix; ?>polls</li>
                  <li><?php echo $prefix; ?>polls_templates</li>
              </ol>
            </td>
          </tr>
        </table>
        <p style="text-align: center;">
          Do you really want to uninstall Polls :( ?
        </p>
        <p style="text-align: center;">
          <input type="checkbox" id="check_yes" value="yes" />&nbsp;<label for="check_yes">Yes</label>
        </p>
        <p style="text-align: center;">
          <input type="submit" value="UNINSTALL" class="button-primary" onclick="if (check_yes.checked) { 
                                                                                    if (confirm('You are About to Uninstall poll.\nThis Action Is Not Reversible.')) {
                                                                                       
                                                                                    } else {
                                                                                        return false;
                                                                                    }
                                                                                  }
                                                                                  else {
                                                                                    return false;
                                                                                  }" />
        </p>
      </div>
    </form>
  <?php
    
		
		
	}
	private function remove_databese_and_deactivete(){
		global $wpdb;
		$wpdb->query("DROP TABLE " . $wpdb->prefix . "polls_users");
		$wpdb->query("DROP TABLE " . $wpdb->prefix . "polls_question");
		$wpdb->query("DROP TABLE " . $wpdb->prefix . "polls");
		$wpdb->query("DROP TABLE " . $wpdb->prefix . "polls_templates");
		
		?>
		<div id="message" class="updated fade">
		  <p>The following Database Tables successfully deleted:</p>
		  <p><?php echo $wpdb->prefix; ?>polls_users,</p>
		  <p><?php echo $wpdb->prefix; ?>polls_question,</p>
		  <p><?php echo $wpdb->prefix; ?>polls,</p>
		  <p><?php echo $wpdb->prefix; ?>polls_templates,</p>

		</div>
		<div class="wrap">
		  <h2>Uninstall Photo Gallery</h2>
		  <p><strong><a href="<?php echo wp_nonce_url('plugins.php?action=deactivate&amp;plugin=polls/polls.php', 'deactivate-plugin_polls/polls.php'); ?>">Click Here</a> To Finish the Uninstallation</strong></p>
		  <input id="task" name="task" type="hidden" value="" />
		</div>
	  <?php	
	}
}


 ?>