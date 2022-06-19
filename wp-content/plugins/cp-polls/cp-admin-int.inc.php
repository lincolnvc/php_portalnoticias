<?php

if ( !is_admin() ) 
{
    echo 'Direct access not allowed.';
    exit;
}

$this->item = intval($_GET["cal"]);
    

define('CP_POLLS_DEFAULT_fp_from_email', get_the_author_meta('user_email', get_current_user_id()) );
define('CP_POLLS_DEFAULT_fp_destination_emails', CP_POLLS_DEFAULT_fp_from_email);

if ( 'POST' == $_SERVER['REQUEST_METHOD'] && isset( $_POST[$this->prefix.'_post_options'] ) )
    echo "<div id='setting-error-settings_updated' class='updated settings-error'> <p><strong>Settings saved.</strong></p></div>";

?>
<div class="wrap">
<h1><?php echo $this->plugin_name; ?></h1>

<input type="button" name="backbtn" value="Back to items list..." onclick="document.location='options-general.php?page=<?php echo $this->menu_parameter; ?>';">
<br /><br />

<form method="post" action="" name="cpformconf"> 
<input name="<?php echo $this->prefix; ?>_post_options" type="hidden" value="1" />
<input name="<?php echo $this->prefix; ?>_id" type="hidden" value="<?php echo $this->item; ?>" />

   
<div id="normal-sortables" class="meta-box-sortables">


 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Polls option</span></h3>
  <div class="inside">
    
       <table class="form-table">    
        <tr valign="top">
        <th scope="row">Limit votes to:</th>
        <td>
          <?php $option = $this->get_option('poll_limit', CP_POLLS_POLL_LIMIT); ?>
          <select name="poll_limit">
           <option value="0"<?php if (!$option || $option == '0') echo ' selected'; ?>>One per visitor (using cookies)</option>
           <option value="1"<?php if ($option == '1') echo ' selected'; ?>>One per IP address</option>
           <option value="3"<?php if ($option == '3') echo ' selected'; ?>>Both limits: one per IP address and Cookie</option>
           <option value="2"<?php if ($option == '2') echo ' selected'; ?>>Don't limit votes</option>
          </select>
        <br ><span style=font-size:11px;">* Note: <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">Professional version</a> also supports the option "<strong>One per WordPress logged in user - retricts votes to logged in users</strong>".</span>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Keep reports private:</th>
        <td>
          <?php $option = $this->get_option('poll_private_reports', CP_POLLS_POLL_PRIVATE_REPORTS); ?>
          <select name="poll_private_reports">
            <option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>
            <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
          </select>            
        </td>
        </tr>         
        <tr valign="top">
        <th scope="row">Display "see results" option (not compatible with private reports):</th>
        <td>
          <?php $option = $this->get_option('poll_see_results', CP_POLLS_POLL_SEE_RESULTS); ?>
          <select name="poll_see_results">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>
          </select>            
        </td>
        </tr>      
        <tr valign="top">
        <th scope="row">Text for "see results" link:</th>
        <td><input type="text" name="poll_text_seeres" size="40" value="<?php $label = esc_attr($this->get_option('poll_text_seeres', CP_POLLS_POLL_TEXT_SEERES)); echo ($label==''?CP_POLLS_POLL_TEXT_SEERES:$label); ?>" /></td>
        </tr>   
        <tr valign="top">
        <th scope="row">Text for private reports (after voting):</th>
        <td><input type="text" name="poll_text_private" size="40" value="<?php $label = esc_attr($this->get_option('poll_text_private', CP_POLLS_POLL_TEXT_PRIVATE)); echo ($label==''?CP_POLLS_POLL_TEXT_PRIVATE:$label); ?>" /></td>
        </tr> 
        <tr valign="top">
        <th scope="row">Text for "votes" counter:</th>
        <td><input type="text" name="poll_text_votes" size="40" value="<?php $label = esc_attr($this->get_option('poll_text_votes', CP_POLLS_POLL_TEXT_VOTES)); echo ($label==''?CP_POLLS_POLL_TEXT_VOTES:$label); ?>" /></td>
        </tr>         
        <tr valign="top">
        <th scope="row">Thank you page (after voting, leave empty to don't redirect after voting)</th>
        <td><input type="text" name="fp_return_page" size="70" value="<?php echo esc_attr($this->get_option('fp_return_page', CP_POLLS_DEFAULT_fp_return_page)); ?>" /></td>
        </tr>                           
       </table>
       
  </div> 
 </div>  

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Poll Form Builder</span></h3>
  <div class="inside">   
 
     <div style="padding:10px;background-color:#ffffdd;border:1px dotted black;">
        <p><STRONG>This version</STRONG> is designed to build classic <STRONG>polls based in radio-buttons</STRONG>.</p>
        <p>There are also commercial versions with more advanced form builders for creting forms with other features:</p>
        <ul>
            <li>- <a STYLE="FONT-WEIGHT:BOLD;" href="http://wordpress.dwbooster.com/forms/cp-polls#download">CP Polls Pro</a> with a <strong>complete set of form fields</strong>, multi-page forms, etc...</li>
            <li>- <a STYLE="FONT-WEIGHT:BOLD;" href="http://wordpress.dwbooster.com/forms/calculated-fields-form#download">Calculated Fields Form</a> for <strong>displaying calculations</strong> (ex: quote calculkators) based in the items selected in the form.</li>
            <li>- <a STYLE="FONT-WEIGHT:BOLD;" href="http://wordpress.dwbooster.com/forms/contact-form-to-email#download">Contact Form to Email</a> for <strong>contact forms</strong> with reports.</li>
            <li>- <a STYLE="FONT-WEIGHT:BOLD;" href="http://wordpress.dwbooster.com/forms/cp-contact-form-with-paypal#download">Contact Form with PayPal</a> for connecting the form to <strong>PayPal</strong> payments.</li>
     </div>
     
     <input type="hidden" name="form_structure" id="form_structure" size="180" value="<?php echo str_replace('"','&quot;',str_replace("\r","",str_replace("\n","",esc_attr($this->cleanJSON($this->get_option('form_structure', CP_POLLS_DEFAULT_form_structure)))))); ?>" />
     
     <link href="<?php echo plugins_url('css/style.css', __FILE__); ?>" type="text/css" rel="stylesheet" />   
     <link href="<?php echo plugins_url('css/cupertino/jquery-ui-1.8.20.custom.css', __FILE__); ?>" type="text/css" rel="stylesheet" />   
        
        
     <script type="text/javascript">                 
       if (typeof jQuery === "undefined") {
          document.write ("<"+"script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'></"+"script>");
          document.write ("<"+"script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.20/jquery-ui.min.js'></"+"script>");
       }
       $easyFormQuery = jQuery.noConflict();
       if (typeof $easyFormQuery == 'undefined')
       {
          // This code won't be used in most cases. This code is for preventing problems in wrong WP themes and conflicts with third party plugins.                  
          document.write ("<"+"script type='text/javascript' src='<?php echo plugins_url('js/jQuery.stringify.js', __FILE__); ?>'></"+"script>");
          document.write ("<"+"script type='text/javascript' src='<?php echo plugins_url('js/jquery.validate.js', __FILE__); ?>'></"+"script>");         
          document.write ("<"+"script type='text/javascript' src='<?php echo plugins_url('js/fbuilder-loader-admin.php', __FILE__); ?>'></"+"script>");         
       } 
     </script> 
             
     <script>
         
         $easyFormQuery(document).ready(function() {
            var f = $easyFormQuery("#fbuilder").fbuilder();
            f.fBuild.loadData("form_structure");
            
            $easyFormQuery("#saveForm").click(function() {       
                f.fBuild.saveData("form_structure");
            });  
                 
            $easyFormQuery(".itemForm").click(function() {
     	       f.fBuild.addItem($easyFormQuery(this).attr("id"));
     	   });  
          
           $easyFormQuery( ".itemForm" ).draggable({revert1: "invalid",helper: "clone",cursor: "move"});
     	   $easyFormQuery( "#fbuilder" ).droppable({
     	       accept: ".button",
     	       drop: function( event, ui ) {
     	           f.fBuild.addItem(ui.draggable.attr("id"));				
     	       }
     	   });
     		    
         }); 
        var randcaptcha = 1;
        function generateCaptcha()
        {            
           var d=new Date();
           var f = document.cpformconf;    
           var qs = "&width="+f.cv_width.value;
           qs += "&height="+f.cv_height.value;
           qs += "&letter_count="+f.cv_chars.value;
           qs += "&min_size="+f.cv_min_font_size.value;
           qs += "&max_size="+f.cv_max_font_size.value;
           qs += "&noise="+f.cv_noise.value;
           qs += "&noiselength="+f.cv_noise_length.value;
           qs += "&bcolor="+f.cv_background.value;
           qs += "&border="+f.cv_border.value;
           qs += "&font="+f.cv_font.options[f.cv_font.selectedIndex].value;
           qs += "&r="+(randcaptcha++);
           
           document.getElementById("captchaimg").src= "<?php echo $this->get_site_url().'/?'.$this->prefix.'_captcha=captcha&inAdmin=1'; ?>"+qs;
        }

     </script>
     
     <div style="background:#fafafa;width:780px;" class="form-builder">
     
         <div class="column width50">
             <div id="tabs">
     			<ul>
     				<li><a href="#tabs-1">Add a Field</a></li>
     				<li><a href="#tabs-2">Field Settings</a></li>
     				<li><a href="#tabs-3">Form Settings</a></li>
     			</ul>
     			<div id="tabs-1">
     			    
     			</div>
     			<div id="tabs-2"></div>
     			<div id="tabs-3"></div>
     		</div>	
         </div>
         <div class="columnr width50 padding10" id="fbuilder">
             <div id="formheader"></div>
             <div id="fieldlist"></div>
             <!--<div class="button" id="saveForm">Save Form</div>-->
         </div>
         <div class="clearer"></div>
         
     </div>        
   
  </div>    
 </div> 
 

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Vote Button</span></h3>
  <div class="inside">   
     <table class="form-table">    
        <tr valign="top">
        <th scope="row">Vote button label (text):</th>
        <td><input type="text" name="vs_text_submitbtn" size="40" value="<?php $label = esc_attr($this->get_option('vs_text_submitbtn', 'Vote')); echo ($label==''?'Submit':$label); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Previous button label (text):</th>
        <td><input type="text" name="vs_text_previousbtn" size="40" value="<?php $label = esc_attr($this->get_option('vs_text_previousbtn', 'Previous')); echo ($label==''?'Previous':$label); ?>" /></td>
        </tr>    
        <tr valign="top">
        <th scope="row">Next button label (text):</th>
        <td><input type="text" name="vs_text_nextbtn" size="40" value="<?php $label = esc_attr($this->get_option('vs_text_nextbtn', 'Next')); echo ($label==''?'Next':$label); ?>" /></td>
        </tr>  
        <tr valign="top">
        <td colspan="2"> - The  <em>class="pbSubmit"</em> can be used to modify the button styles. <br />
        - The styles can be applied into any of the CSS files of your theme or into the CSS file <em>"contact-form-to-email\css\stylepublic.css"</em>. <br />
        - For further modifications the vote button is located at the end of the file <em>"cp-public-int.inc.php"</em>.<br />
        - For general CSS styles modifications to the form and samples <a href="http://wordpress.dwbooster.com/faq/contact-form-to-email#q77" target="_blank">check this FAQ</a>.
        </tr>
     </table>
  </div>    
 </div> 
 

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Validation Settings</span></h3>
  <div class="inside">
     <table class="form-table">    
        <tr valign="top">
        <th scope="row">Use Validation?</th>
        <td>
          <?php $option = $this->get_option('vs_use_validation', CP_POLLS_DEFAULT_vs_use_validation); ?>
          <select name="vs_use_validation">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
           <!--<option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>-->
          </select>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">"is required" text:</th>
        <td><input type="text" name="vs_text_is_required" size="40" value="<?php echo esc_attr($this->get_option('vs_text_is_required', CP_POLLS_DEFAULT_vs_text_is_required)); ?>" /></td>
        </tr>    
        <tr valign="top">
        <th scope="row">"is valid captcha" text:</th>
        <td><input type="text" name="cv_text_enter_valid_captcha" size="70" value="<?php echo esc_attr($this->get_option('cv_text_enter_valid_captcha', CP_POLLS_DEFAULT_cv_text_enter_valid_captcha)); ?>" /></td>
        </tr>
                         
         <tr valign="top">
        <th scope="row">"is email" text:</th>
        <td><input type="text" name="vs_text_is_email" size="70" value="<?php echo esc_attr($this->get_option('vs_text_is_email', CP_POLLS_DEFAULT_vs_text_is_email)); ?>" /></td>
        </tr>       

        <tr valign="top">
        <th scope="row">"is valid date (mm/dd/yyyy)" text:</th>
        <td><input type="text" name="vs_text_datemmddyyyy" size="70" value="<?php echo esc_attr($this->get_option('vs_text_datemmddyyyy', CP_POLLS_DEFAULT_vs_text_datemmddyyyy)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"is valid date (dd/mm/yyyy)" text:</th>
        <td><input type="text" name="vs_text_dateddmmyyyy" size="70" value="<?php echo esc_attr($this->get_option('vs_text_dateddmmyyyy', CP_POLLS_DEFAULT_vs_text_dateddmmyyyy)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"is number" text:</th>
        <td><input type="text" name="vs_text_number" size="70" value="<?php echo esc_attr($this->get_option('vs_text_number', CP_POLLS_DEFAULT_vs_text_number)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"only digits" text:</th>
        <td><input type="text" name="vs_text_digits" size="70" value="<?php echo esc_attr($this->get_option('vs_text_digits', CP_POLLS_DEFAULT_vs_text_digits)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"under maximum" text:</th>
        <td><input type="text" name="vs_text_max" size="70" value="<?php echo esc_attr($this->get_option('vs_text_max', CP_POLLS_DEFAULT_vs_text_max)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">"over minimum" text:</th>
        <td><input type="text" name="vs_text_min" size="70" value="<?php echo esc_attr($this->get_option('vs_text_min', CP_POLLS_DEFAULT_vs_text_min)); ?>" /></td>
        </tr>             
        
     </table>  
  </div>    
 </div>   
 

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Form Processing / Email Settings</span></h3>
  <div class="inside">
     <table class="form-table">    
     
        <tr valign="top">
        <th scope="row">Send email "From" </th>
        <td>
          <?php $option = $this->get_option('fp_emailfrommethod', "fixed"); ?>
           <select name="fp_emailfrommethod">
             <option value="fixed"<?php if ($option == 'fixed') echo ' selected'; ?>>From fixed email address indicated below - Recommended option</option>
             <option value="customer"<?php if ($option == 'customer') echo ' selected'; ?>>From the email address indicated by the customer - if any -</option>
            </select><br />
            <span style="font-size:10px;color:#666666">
            * If you select "from fixed..." the customer email address will appear in the "to" address when you hit "reply", this is the recommended setting to avoid mail server restrictions. 
            <br />
            * If you select "from customer email" then the customer email will appear also visually when you receive the email, but this isn't supported by all hosting services, so this
            option isn't recommended in most cases.
            </span>
        </td>
        </tr>       
        <tr valign="top">
        <th scope="row">"From" email (for fixed "from" addresses)</th>
        <td><input type="text" name="fp_from_email" size="40" value="<?php echo esc_attr($this->get_option('fp_from_email', CP_POLLS_DEFAULT_fp_from_email)); ?>" /></td>
        </tr>             
        <tr valign="top">
        <th scope="row">Destination emails (comma separated, leave empty to disable email notifications)</th>
        <td><input type="text" name="fp_destination_emails" size="40" value="<?php echo esc_attr($this->get_option('fp_destination_emails', CP_POLLS_DEFAULT_fp_destination_emails)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Email subject</th>
        <td><input type="text" name="fp_subject" size="70" value="<?php echo esc_attr($this->get_option('fp_subject', CP_POLLS_DEFAULT_fp_subject)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Include additional information?</th>
        <td>
          <?php $option = $this->get_option('fp_inc_additional_info', CP_POLLS_DEFAULT_fp_inc_additional_info); ?>
          <select name="fp_inc_additional_info">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>
          </select>
        </td>
        </tr>        
        <tr valign="top">
        <th scope="row">Email format?</th>
        <td>
          <?php $option = $this->get_option('fp_emailformat', CP_POLLS_DEFAULT_email_format); ?>
          <select name="fp_emailformat">
           <option value="text"<?php if ($option != 'html') echo ' selected'; ?>>Plain Text (default)</option>
           <option value="html"<?php if ($option == 'html') echo ' selected'; ?>>HTML (use html in the textarea below)</option>
          </select>
        </td>
        </tr>        
        <tr valign="top">
        <th scope="row">Message</th>
        <td><textarea type="text" name="fp_message" rows="6" cols="80"><?php echo $this->get_option('fp_message', CP_POLLS_DEFAULT_fp_message); ?></textarea></td>
        </tr>                                                               
     </table>  
  </div>    
 </div>   
 
 
 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Email Copy to User</span></h3>
  <div class="inside">
     <table class="form-table">    
        <tr valign="top">
        <th scope="row">Send confirmation/thank you message to user?</th>
        <td>
          <?php $option = $this->get_option('cu_enable_copy_to_user', CP_POLLS_DEFAULT_cu_enable_copy_to_user); ?>
          <select name="cu_enable_copy_to_user">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>
          </select>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Email field on the form</th>
    <td><select id="cu_user_email_field" name="cu_user_email_field" def="<?php echo esc_attr($this->get_option('cu_user_email_field', CP_POLLS_DEFAULT_cu_user_email_field)); ?>"></select><br /><em> - Note: Not available in this version since the form builder doesn't contain the email field. <a href="http://wordpress.dwbooster.com/forms/cp-polls#download">Check other versions</a>.</em>
        </td>
        </tr>             
        <tr valign="top">
        <th scope="row">Email subject</th>
        <td><input type="text" name="cu_subject" size="70" value="<?php echo esc_attr($this->get_option('cu_subject', CP_POLLS_DEFAULT_cu_subject)); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Email format?</th>
        <td>
          <?php $option = $this->get_option('cu_emailformat', CP_POLLS_DEFAULT_email_format); ?>
          <select name="cu_emailformat">
           <option value="text"<?php if ($option != 'html') echo ' selected'; ?>>Plain Text (default)</option>
           <option value="html"<?php if ($option == 'html') echo ' selected'; ?>>HTML (use html in the textarea below)</option>
          </select>
        </td>
        </tr>  
        <tr valign="top">
        <th scope="row">Message</th>
        <td><textarea type="text" name="cu_message" rows="6" cols="80"><?php echo $this->get_option('cu_message', CP_POLLS_DEFAULT_cu_message); ?></textarea></td>
        </tr>        
     </table>  
  </div>    
 </div>  
 

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Captcha Verification</span></h3>
  <div class="inside">
     <table class="form-table">    
        <tr valign="top">
        <th scope="row">Use Captcha Verification?</th>
        <td colspan="5">
          <?php $option = $this->get_option('cv_enable_captcha', CP_POLLS_DEFAULT_cv_enable_captcha); ?>
          <select name="cv_enable_captcha">
           <option value="true"<?php if ($option == 'true') echo ' selected'; ?>>Yes</option>
           <option value="false"<?php if ($option == 'false') echo ' selected'; ?>>No</option>
          </select>
        </td>
        </tr>
        
        <tr valign="top">
         <th scope="row">Width:</th>
         <td><input type="text" name="cv_width" size="10" value="<?php echo esc_attr($this->get_option('cv_width', CP_POLLS_DEFAULT_cv_width)); ?>"  onblur="generateCaptcha();"  /></td>
         <th scope="row">Height:</th>
         <td><input type="text" name="cv_height" size="10" value="<?php echo esc_attr($this->get_option('cv_height', CP_POLLS_DEFAULT_cv_height)); ?>" onblur="generateCaptcha();"  /></td>
         <th scope="row">Chars:</th>
         <td><input type="text" name="cv_chars" size="10" value="<?php echo esc_attr($this->get_option('cv_chars', CP_POLLS_DEFAULT_cv_chars)); ?>" onblur="generateCaptcha();"  /></td>
        </tr>             

        <tr valign="top">
         <th scope="row">Min font size:</th>
         <td><input type="text" name="cv_min_font_size" size="10" value="<?php echo esc_attr($this->get_option('cv_min_font_size', CP_POLLS_DEFAULT_cv_min_font_size)); ?>" onblur="generateCaptcha();"  /></td>
         <th scope="row">Max font size:</th>
         <td><input type="text" name="cv_max_font_size" size="10" value="<?php echo esc_attr($this->get_option('cv_max_font_size', CP_POLLS_DEFAULT_cv_max_font_size)); ?>" onblur="generateCaptcha();"  /></td>        
         <td colspan="2" rowspan="">
           Preview:<br />
             <br />
            <img src="<?php echo $this->get_site_url().'/?'.$this->prefix.'_captcha=captcha&inAdmin=1'; ?>"  id="captchaimg" alt="security code" border="0"  />            
         </td> 
        </tr>             
                

        <tr valign="top">
         <th scope="row">Noise:</th>
         <td><input type="text" name="cv_noise" size="10" value="<?php echo esc_attr($this->get_option('cv_noise', CP_POLLS_DEFAULT_cv_noise)); ?>" onblur="generateCaptcha();" /></td>
         <th scope="row">Noise Length:</th>
         <td><input type="text" name="cv_noise_length" size="10" value="<?php echo esc_attr($this->get_option('cv_noise_length', CP_POLLS_DEFAULT_cv_noise_length)); ?>" onblur="generateCaptcha();" /></td>        
        </tr>          
        

        <tr valign="top">
         <th scope="row">Background:</th>
         <td><input type="text" name="cv_background" size="10" value="<?php echo esc_attr($this->get_option('cv_background', CP_POLLS_DEFAULT_cv_background)); ?>" onblur="generateCaptcha();" /></td>
         <th scope="row">Border:</th>
         <td><input type="text" name="cv_border" size="10" value="<?php echo esc_attr($this->get_option('cv_border', CP_POLLS_DEFAULT_cv_border)); ?>" onblur="generateCaptcha();" /></td>        
        </tr>    
        
        <tr valign="top">
         <th scope="row">Font:</th>
         <td>
            <select name="cv_font" onchange="generateCaptcha();" >
              <option value="font-1.ttf"<?php if ("font-1.ttf" == $this->get_option('cv_font', CP_POLLS_DEFAULT_cv_font)) echo " selected"; ?>>Font 1</option>
              <option value="font-2.ttf"<?php if ("font-2.ttf" == $this->get_option('cv_font', CP_POLLS_DEFAULT_cv_font)) echo " selected"; ?>>Font 2</option>
              <option value="font-3.ttf"<?php if ("font-3.ttf" == $this->get_option('cv_font', CP_POLLS_DEFAULT_cv_font)) echo " selected"; ?>>Font 3</option>
              <option value="font-4.ttf"<?php if ("font-4.ttf" == $this->get_option('cv_font', CP_POLLS_DEFAULT_cv_font)) echo " selected"; ?>>Font 4</option>
            </select>            
         </td>              
        </tr>                          
           
        
     </table>  
  </div>    
 </div>    
 
 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Automatic Reports: Send submissions in CSV format via email</span></h3>
  <div class="inside">
     <table class="form-table">    
        <tr valign="top">
        <th scope="row">Enable Reports?</th>
        <td>
          <?php $option = $this->get_option('rep_enable', 'no'); ?>
          <select name="rep_enable">
           <option value="no"<?php if ($option == 'no' || $option == '') echo ' selected'; ?>>No</option>
           <option value="yes"<?php if ($option == 'yes') echo ' selected'; ?>>Yes</option>
          </select>
        </td>
        </tr>
        <tr valign="top">
        <th scope="row">Send report every</th>
        <td><input type="text" name="rep_days" size="4" value="<?php echo esc_attr($this->get_option('rep_days', '1')); ?>" /> days</td>
        </tr>        
        <tr valign="top">
        <th scope="row">Send report after this hour (server time)</th>
        <td>
          <select name="rep_hour">
           <?php
             $hour = $this->get_option('rep_hour', '0');
             for ($k=0;$k<24;$k++)
                 echo '<option value="'.$k.'"'.($hour==$k?' selected':'').'>'.($k<10?'0':'').$k.'</option>';
           ?>
          </select>
        </td>
        </tr>        
        <tr valign="top">
        <th scope="row">Send the report to the following email addresses (comma separated)</th>
        <td><input type="text" name="rep_emails" size="70" value="<?php echo esc_attr($this->get_option('rep_emails', '')); ?>" /></td>
        </tr>             
        <tr valign="top">
        <th scope="row">Email subject</th>
        <td><input type="text" name="rep_subject" size="70" value="<?php echo esc_attr($this->get_option('rep_subject', 'Submissions report...')); ?>" /></td>
        </tr>
        <tr valign="top">
        <th scope="row">Email format?</th>
        <td>
          <?php $option = $this->get_option('rep_emailformat', 'text'); ?>
          <select name="rep_emailformat">
           <option value="text"<?php if ($option != 'html') echo ' selected'; ?>>Plain Text (default)</option>
           <option value="html"<?php if ($option == 'html') echo ' selected'; ?>>HTML (use html in the textarea below)</option>
          </select>
        </td>
        </tr>  
        <tr valign="top">
        <th scope="row">Email Text (CSV file will be attached with the submissions)</th>
        <td><textarea type="text" name="rep_message" rows="3" cols="80"><?php echo $this->get_option('rep_message', 'Attached you will find the data from the form submissions.'); ?></textarea></td>
        </tr>        
     </table>  
  </div>    
 </div>   
 
 
<div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Note</span></h3>
  <div class="inside">
   To insert this form in a post/page, use the dedicated icon <?php echo '<img hspace="5" src="'.plugins_url('/images/cp_form.gif', __FILE__).'" alt="'.__('Insert '.$this->plugin_name).'" /></a>';     ?>
   which has been added to your Upload/Insert Menu, just below the title of your Post/Page.
   <br /><br />
  </div>
</div>
  
</div> 


<p class="submit"><input type="submit" name="submit" id="submit" class="button-primary" value="Save Changes"  /></p>


[<a href="http://wordpress.dwbooster.com/support?product=cp-polls&ref=dashboard" target="_blank">Request Custom Modifications</a>] | [<a href="<?php echo $this->plugin_URL; ?>" target="_blank">Help</a>]
</form>
</div>
<script type="text/javascript">generateCaptcha();</script>