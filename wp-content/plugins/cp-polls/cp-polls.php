<?php
/*
Plugin Name: CP Polls
Plugin URI: http://wordpress.dwbooster.com/forms/cp-polls
Description: Create classic polls and advanced polls with dependant questions.
Version: 1.0.8
Author: CodePeople.net
Author URI: http://codepeople.net
License: GPL
*/

define('CP_POLLS_DEFER_SCRIPTS_LOADING', (get_option('CP_POLLS_LOAD_SCRIPTS',"1") == "1"?true:false));

define('CP_POLLS_DEFAULT_form_structure', '[[{"form_identifier":"","name":"fieldname1","shortlabel":"","index":0,"ftype":"fradio","userhelp":"","userhelpTooltip":false,"csslayout":"","title":"Select a Choice","layout":"one_column","required":true,"choiceSelected":"","showDep":false,"choices":["First Choice","Second Choice","Third Choice"],"choicesVal":["First Choice","Second Choice","Third Choice"],"choicesDep":[[],[],[]],"fBuild":{}}],[{"title":"Sample Poll","description":"Here is a sample question for the poll.","formlayout":"top_aligned","formtemplate":""}]]');
define('CP_POLLS_DEFAULT_form_structure2', '[[{"form_identifier":"","name":"fieldname1","shortlabel":"","index":0,"ftype":"fradio","userhelp":"","userhelpTooltip":false,"csslayout":"","title":"Do you like this plugin?","layout":"one_column","required":true,"choiceSelected":"","showDep":true,"choices":["Yes","No"],"choicesVal":["Yes","No"],"choicesDep":[["fieldname2"],["fieldname3"]],"fBuild":{}},{"form_identifier":"","name":"fieldname2","shortlabel":"","index":1,"ftype":"fradio","userhelp":"","userhelpTooltip":false,"csslayout":"","title":"Please select why you like this plugin?","layout":"one_column","required":true,"choiceSelected":"","showDep":false,"choices":["The drag and drop form builder","The reports","The availability of mutliple features"],"choicesVal":["The drag and drop form builder","The reports","The availability of mutliple features"],"choicesDep":[[],[],[]],"fBuild":{}},{"form_identifier":"","name":"fieldname3","shortlabel":"","index":2,"ftype":"fradio","userhelp":"","userhelpTooltip":false,"csslayout":"","title":"Please select why you don\'t like this plugin?","layout":"one_column","required":true,"choiceSelected":"","showDep":false,"choices":["I want more features","Form builder isn\'t enough","More reports needed"],"choicesVal":["I want more features","Form builder isn\'t enough","More reports needed"],"choicesDep":[[],[],[]],"fBuild":{}}],[{"title":"Poll with dependent/cascade fields","description":"Sample poll: Plugin evaluation. Note that this poll is just a sample, <b>the results won\'t be sent</b> to the developers.","formlayout":"top_aligned","formtemplate":""}]]');

define('CP_POLLS_POLL_LIMIT', '2');
define('CP_POLLS_POLL_PRIVATE_REPORTS', 'false');
define('CP_POLLS_POLL_SEE_RESULTS', 'true');
define('CP_POLLS_POLL_TEXT_SEERES', 'See results');
define('CP_POLLS_POLL_TEXT_PRIVATE', 'Thank you for your vote!');
define('CP_POLLS_POLL_TEXT_VOTES', 'votes');

define('CP_POLLS_DEFAULT_fp_subject', 'Contact from the blog...');
define('CP_POLLS_DEFAULT_fp_inc_additional_info', 'true');
define('CP_POLLS_DEFAULT_fp_return_page', '');
define('CP_POLLS_DEFAULT_fp_message', "The following contact message has been sent:\n\n<%INFO%>\n\n");

define('CP_POLLS_DEFAULT_cu_enable_copy_to_user', 'false');
define('CP_POLLS_DEFAULT_cu_user_email_field', '');
define('CP_POLLS_DEFAULT_cu_subject', 'Confirmation: Message received...');
define('CP_POLLS_DEFAULT_cu_message', "Thank you for your message. We will reply you as soon as possible.\n\nThis is a copy of the data sent:\n\n<%INFO%>\n\nBest Regards.");
define('CP_POLLS_DEFAULT_email_format','text');

define('CP_POLLS_DEFAULT_vs_use_validation', 'true');

define('CP_POLLS_DEFAULT_vs_text_is_required', 'This field is required.');
define('CP_POLLS_DEFAULT_vs_text_is_email', 'Please enter a valid email address.');

define('CP_POLLS_DEFAULT_vs_text_datemmddyyyy', 'Please enter a valid date with this format(mm/dd/yyyy)');
define('CP_POLLS_DEFAULT_vs_text_dateddmmyyyy', 'Please enter a valid date with this format(dd/mm/yyyy)');
define('CP_POLLS_DEFAULT_vs_text_number', 'Please enter a valid number.');
define('CP_POLLS_DEFAULT_vs_text_digits', 'Please enter only digits.');
define('CP_POLLS_DEFAULT_vs_text_max', 'Please enter a value less than or equal to {0}.');
define('CP_POLLS_DEFAULT_vs_text_min', 'Please enter a value greater than or equal to {0}.');

define('CP_POLLS_DEFAULT_cv_enable_captcha', 'false');
define('CP_POLLS_DEFAULT_cv_width', '170');
define('CP_POLLS_DEFAULT_cv_height', '60');
define('CP_POLLS_DEFAULT_cv_chars', '5');
define('CP_POLLS_DEFAULT_cv_font', 'font-1.ttf');
define('CP_POLLS_DEFAULT_cv_min_font_size', '25');
define('CP_POLLS_DEFAULT_cv_max_font_size', '35');
define('CP_POLLS_DEFAULT_cv_noise', '200');
define('CP_POLLS_DEFAULT_cv_noise_length', '4');
define('CP_POLLS_DEFAULT_cv_background', 'ffffff');
define('CP_POLLS_DEFAULT_cv_border', '000000');
define('CP_POLLS_DEFAULT_cv_text_enter_valid_captcha', 'Please enter a valid captcha code.');


/* initialization / install */

include_once dirname( __FILE__ ) . '/classes/cp-base-class.inc.php';
include_once dirname( __FILE__ ) . '/cp-main-class.inc.php';

$cp_plugin = new CP_Polls;

register_activation_hook(__FILE__, array($cp_plugin,'install') ); 
add_action( 'media_buttons', array($cp_plugin, 'insert_button'), 11);
add_action( 'init', array($cp_plugin, 'data_management'));

if ( is_admin() ) {    
    add_action('admin_enqueue_scripts', array($cp_plugin,'insert_adminScripts'), 1);    
    add_filter("plugin_action_links_".plugin_basename(__FILE__), array($cp_plugin,'plugin_page_links'));   
    add_action('admin_menu', array($cp_plugin,'admin_menu') );
} else {    
    add_shortcode( $cp_plugin->shorttag, array($cp_plugin, 'filter_content') );    
}  

?>