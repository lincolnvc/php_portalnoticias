<?php

/**
 * SmartMag Theme!
 * 
 * This is the typical theme initialization file. Sets up the Bunyad Framework
 * and the theme functionality.
 * 
 * ----
 * 
 * Other Code Locations:
 * 
 *  /          -  WordPress default template files
 *  lib/       -  Contains the core Bunyad framework files
 *  admin/     -  Admin-only content
 *  partials/  -  Template parts (partials) called via get_template_part()
 *  blocks/    -  Page-builder block views
 *  
 * Note: If you're looking to edit HTML, look for default WordPress templates in
 * top-level / and in partials/ folder.
 * 
 */

// already initialized? some buggy plugin call?
if (class_exists('Bunyad_Core')) {
	return;
}

// Initialize Framework
require_once get_template_directory() . '/lib/bunyad.php';

// fire up the theme-specific extra functionality
$smart_mag = new Bunyad_Theme_SmartMag;

/**
 * Main Framework Configuration
 */
$bunyad = Bunyad::core()->init(apply_filters('bunyad_init_config', array(

	'theme_name' => 'smartmag',
	'meta_prefix' => '_bunyad',
	'theme_version' => '2.5.2',

	// widgets enabled
	'widgets'    => array('about', 'latest-posts', 'popular-posts', 'tabbed-recent', 'flickr', 'ads', 'latest-reviews', 'bbp-login', 'tabber', 'blocks'),
	'post_formats' => array('gallery', 'image', 'video', 'audio'),

	'shortcode_config' => array(
		'font_icons' => true,
		'social_font' => true,
		'button_colors' => array('default', 'red', 'orange', 'yellow', 'blue', 'black'),
	),
	
	// enabled metaboxes and prefs
	'meta_boxes' => array(
		array('id' => 'post-options', 'title' => __('Post Options', 'bunyad'), 'priority' => 'high', 'page' => array('post')),
		array('id' => 'post-reviews', 'title' => __('Review', 'bunyad'), 'priority' => 'high', 'page' => array('post')),
		array('id' => 'page-options', 'title' => __('Page Options', 'bunyad'), 'priority' => 'high', 'page' => array('page')),
	),
	
	// page builder blocks
	'page_builder_blocks' => array(
	
		// special
		'highlights' => 'Bunyad_PageBuilder_Highlights',
		'news-focus' => 'Bunyad_PageBuilder_NewsFocus',
		'blog' => 'Bunyad_PageBuilder_Blog',
		'latest-gallery' => 'Bunyad_PageBuilder_LatestGallery',
		'separator' => 'Bunyad_PbBasic_Separator',
		'rich-text' => 'Bunyad_PbBasic_RichText',
		
		// native
		'text' => 'WP_Widget_Text',
		'latest-posts' => array('class' => 'Bunyad_LatestPosts_Widget', 'name' => __('Latest Posts', 'bunyad')),
		'flickr' => array('class' => 'Bunyad_Flickr_Widget', 'name' => __('Flickr Images', 'bunyad')),
		'ads' => array('class' => 'Bunyad_Ads_Widget', 'name' => __('Advertisement', 'bunyad')),
		'latest-reviews' => array('class' => 'Bunyad_LatestReviews_Widget', 'name' => __('Latest Reviews', 'bunyad'))
	),

)));


/**
 * SmartMag Theme!
 * 
 * Anything theme-specific that won't go into the core framework goes here. Rest goes into lib/core.php
 */
class Bunyad_Theme_SmartMag
{
	public $woocommerce;
	public $registry = array();
	
	public function __construct() 
	{
		// setup plugins before init
		$this->setup_plugins();

		// perform the after_setup_theme 
		add_action('after_setup_theme', array($this, 'theme_init'), 12);
		
		// include WooCommerce 
		if (function_exists('is_woocommerce')) {
			require_once get_template_directory() . '/woocommerce/init.php';
			$this->woocommerce = new Bunyad_Theme_SmartMag_WooCommerce;
		}
	}
	
	/**
	 * Setup enque data and actions
	 */
	public function theme_init()
	{
		/*
		 * Enqueue assets (css, js)
		 * 
		 * Register Custom CSS at a lower priority for CSS specificity
		 */
		add_action('wp_enqueue_scripts', array($this, 'register_assets'));
		add_action('wp_enqueue_scripts', array($this, 'register_custom_css'), 99);
		
		/*
		 * Featured images settings
		 */
		set_post_thumbnail_size(110, 96, true); // 17:15, also used in 85x75 and more similar aspect ratios

		// 1280x612 images for no cropping of featured and slider image
		add_image_size('main-full', 1078, 516, true); // main post image in full width
		add_image_size('main-slider', 702, 336, true);
		
		add_image_size('main-block', 351, 185, true); // also usable at 326x160
		add_image_size('slider-small', 168, 137, true); // small thumb for slider
		add_image_size('gallery-block', 214, 140, true); // small thumb for slider
		
		add_image_size('grid-overlay', 343, 215, true); // size for grid overlay listing

		// i18n
		load_theme_textdomain('bunyad', get_template_directory() . '/languages');
		
		// setup navigation menu with "main" key
		register_nav_menu('main', __('Main Navigation', 'bunyad'));
		
		/*
		 * Category meta 
		 */
		add_action('category_edit_form_fields', array($this, 'edit_category_meta'), 10, 2);
		add_action('category_add_form_fields', array($this, 'edit_category_meta'), 10, 2);
		
		add_action('edited_category', array($this, 'save_category_meta'), 10, 2);
		add_action('create_category', array($this, 'save_category_meta'), 10, 2);
		
		// user fields
		add_filter('user_contactmethods', array($this, 'add_profile_fields'));
		
		/*
		 * Reviews Support
		 */
		add_filter('the_content', array($this, 'add_review'));
		add_filter('bunyad_review_main_snippet', array($this, 'add_review_snippet'), 10, 2);
		
		// 3.5 has content_width removed, add it for oebmed
		global $content_width;
		
		if (!isset($content_width)) {
			$content_width = 702;
		}
		
		/*
		 * Register Sidebars
		 */		
		$this->register_sidebars();

		/*
		 * Mega menu support
		 */
		add_filter('bunyad_custom_menu_fields', array($this, 'custom_menu_fields'));
		add_filter('bunyad_mega_menu_end_lvl', array($this, 'attach_mega_menu'));
		
		// menu sticky logo support
		add_filter('wp_nav_menu_items', array($this, 'add_navigation_logo'), 10, 2);
		
		/*
		 * Posts related filter
		 */
		
		// add authorship
		add_filter('wp_head', array($this, 'add_header_meta'));
		
		// custom font icons for post formats
		add_filter('bunyad_post_formats_icon', array($this, 'post_format_icon'));
		
		// video format auto-embed
		add_filter('bunyad_featured_video', array($this, 'video_auto_embed'));
		
		// add custom category per_page limits, if any
		add_filter('pre_get_posts', array($this, 'add_category_limits'));
		
		// remove hentry microformat, we use schema.org/Article
		add_action('post_class', array($this, 'fix_post_class'));
		
		// add the orig_offset for offset support in blocks
		add_filter('bunyad_block_query_args', array(Bunyad::posts(), 'add_query_offset'), 10, 1);
		
		// add post type to blocks
		add_filter('bunyad_block_query_args', array($this, 'add_post_type'), 10, 3);
		
		// ajax post content slideshow - add wrapper
		add_filter('the_content', array($this, 'add_post_slideshow_wrap'));
		
		// limit search to posts?
		if (Bunyad::options()->search_posts_only) {
			add_filter('pre_get_posts', array($this, 'limit_search'));
		}
		
		/*
		 * Prevent duplicate posts
		 */
		if (Bunyad::options()->no_home_duplicates) {
			
			// add to removal list on each loop
			add_filter('loop_end', array($this, 'update_duplicate_posts'));
			
			// exclude on blocks
			add_filter('bunyad_block_query_args', array($this, 'add_duplicate_exclude'));
			
			// exclude on widgets
			foreach (array('tabbed_recent', 'popular_posts', 'latest_posts') as $widget) {
				add_filter('bunyad_widget_' . $widget . '_query_args', array($this, 'add_duplicate_exclude'));
			}
		}
		
		/*
		 * Widgets related hooks
		 */
		
		add_filter('bunyad_widget_tabbed_recent_options', array($this, 'tabbed_recent_options'));
		
		
		/*
		 * bbPress
		 */
		add_theme_support('bbpress');
		
		// is bbpress active?
		if (class_exists('bbpress')) {
			add_action('wp_footer', array($this, 'bbpress_footer'));
		}
		
		add_filter('nav_menu_css_class', array($this, 'add_nav_login'), 10, 2);
				
		// add image sizes to the editor
		add_filter('image_size_names_choose', array($this, 'add_image_sizes_editor'));
		
		// sample import actions
		add_filter('bunyad_import_menu_fields', array($this, 'import_menu_fields'));
		add_action('bunyad_import_completed', array($this, 'import_fix_menu'));
		
		// set dynamic widget columns for footer
		add_filter('dynamic_sidebar_params', array($this, 'set_footer_columns'));
		
		// add support for live search
		add_action('wp_ajax_bunyad_live_search', array($this, 'live_search'));
		add_action('wp_ajax_nopriv_bunyad_live_search', array($this, 'live_search'));
		
		// setup the init hook
		add_action('init', array($this, 'init'));
		

	}
	
	/**
	 * Action callback: Setup that needs to be done at init hook
	 */
	public function init() 
	{		
		if ($this->has_custom_css()) {			
			add_action('template_redirect', array($this, 'global_external_custom_css'), 1);
		}
		
		Bunyad::reviews();
		
		/*
		 * Setup shortcodes, and page builder assets 
		 */
		
		// setup theme-specific shortcodes and blocks
		$this->setup_shortcodes();
		
		// setup page builder blocks
		$this->setup_page_builder();
	}
		
	/**
	 * Check if the theme has any custom css
	 */
	public function has_custom_css()
	{
		if (count(Bunyad::options()->get_all('css_'))) {
			return true;
		} 
		
		// check if a cat has custom color
		foreach ((array) Bunyad::options()->get_all('cat_meta_') as $cat) 
		{
			if (!empty($cat)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Action callback: Output Custom CSS using external CSS method
	 */
	public function global_external_custom_css()
	{		
		// custom css requested?
		if (empty($_GET['bunyad_custom_css']) OR intval($_GET['bunyad_custom_css']) != 1) {
			return;
		}
		
		// set 200 - might be 404
		status_header(200);
		header("Content-type: text/css; charset: utf-8"); 

		include_once get_template_directory() . '/custom-css.php';
		
		/*
		 * Output the CSS customizations
		 */
		$render = new Bunyad_Custom_Css;
		$render->args = $_GET;
		echo $render->render();
		exit;
	}
	
	/**
	 * Register and enqueue theme CSS and JS files
	 */
	public function register_assets()
	{
		if (!is_admin()) {
			
			// add jquery, theme js
			wp_enqueue_script('jquery');
			wp_enqueue_script('bunyad-theme', get_template_directory_uri() . '/js/bunyad-theme.js', array('jquery'), Bunyad::options()->get_config('theme_version'), true);

			/*
			 * Add CSS styles
			 */
			
			// add google fonts
			$args = array('family' => 'Open+Sans:400,400Italic,600,700|Roboto+Slab');
			if (Bunyad::options()->font_charset) {
				$args['subset'] = implode(',', array_keys(array_filter(Bunyad::options()->font_charset)));
			}
			
			// blockquote font for single
			if (is_singular()) {
				$args['family'] .= '|Merriweather:300italic';
			}
			
			wp_enqueue_style('smartmag-fonts', add_query_arg($args, (is_ssl() ? 'https' : 'http') . '://fonts.googleapis.com/css'),	array(), null);
			
			// add core
			if (is_rtl()) {
				wp_enqueue_style('smartmag-core', get_stylesheet_directory_uri() . '/css/rtl.css', array(), Bunyad::options()->get_config('theme_version'));
			}
			else {
				wp_enqueue_style('smartmag-core', get_stylesheet_uri(), array(), Bunyad::options()->get_config('theme_version'));
			}
			
			if (!Bunyad::options()->no_responsive) {
				wp_enqueue_style('smartmag-responsive', get_template_directory_uri() . '/css/'. (is_rtl() ? 'rtl-' : '') . 'responsive.css', array(), Bunyad::options()->get_config('theme_version'));
			}
			
			// add prettyphoto to pages and single posts
			if (Bunyad::options()->lightbox_prettyphoto && (is_single() OR is_page())) {
				wp_enqueue_script('pretty-photo-smartmag', get_template_directory_uri() . '/js/jquery.prettyPhoto.js', array(), false, false);
				wp_enqueue_style('pretty-photo', get_template_directory_uri() . '/css/prettyPhoto.css', array(), Bunyad::options()->get_config('theme_version'));
			}
			
			// bbPress?
			if (class_exists('bbpress')) {
				wp_enqueue_style('smartmag-bbpress', get_template_directory_uri() . '/css/' . (is_rtl() ? 'rtl-' : '') . 'bbpress-ext.css', array(), Bunyad::options()->get_config('theme_version'));
			}			
			
			// CDN for font awesome?
			if (Bunyad::options()->font_awesome_cdn) {
				wp_enqueue_style('smartmag-font-awesome', (is_ssl() ? 'https' : 'http') . '://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css');
			}
			else {
				wp_enqueue_style('smartmag-font-awesome', get_template_directory_uri() . '/css/fontawesome/css/font-awesome.min.css', array(), Bunyad::options()->get_config('theme_version'));
			}
			
			// flexslider to the footer
			wp_enqueue_script('flex-slider', 
				get_template_directory_uri() . '/js/' . (is_rtl() ? 'rtl-' : '') . 'jquery.flexslider-min.js', array('jquery'), 
				Bunyad::options()->get_config('theme_version'),
				true
			);
			
			// sticky sidebar where enabled
			wp_enqueue_script('sticky-sidebar',
				get_template_directory_uri() . '/js/jquery.sticky-sidebar.min.js', array('jquery'), 
				Bunyad::options()->get_config('theme_version'),
				true
			);
			
 			// register infinite scroll
			wp_register_script('smartmag-infinite-scroll', 
				get_template_directory_uri() . '/js/jquery.infinitescroll.min.js',
				array('jquery'), 
				Bunyad::options()->get_config('theme_version'),
				true
			);
		}
	}
	
	/**
	 * Action callback: Register Custom CSS with low priority 
	 */
	public function register_custom_css()
	{
		if (is_admin()) {
			return;
		}
		
		// pre-defined scheme / skin
		if (Bunyad::options()->predefined_style) {
			wp_enqueue_style('smartmag-skin', get_template_directory_uri() . '/css/skin-' . Bunyad::options()->predefined_style . '.css');
		}
		
		// add custom css
		if ($this->has_custom_css()) {
			
			$query_args = array();
			
			/*
			 * Global color changes?
			 */ 
			if (is_category() OR is_single()) {
	
				$object = get_queried_object();
				$query_args['anchor_obj'] = '';
				
				if (is_category()) {
					$query_args['anchor_obj'] = $object->cat_ID;
				}
				else {
					// be consistent with the behavior that's like cat labels
					$categories = current((array) get_the_category($object->ID));
					
					if (is_object($categories)) {
						$query_args['anchor_obj'] = $categories->cat_ID;
					}
				}
				
				// only used for main color
				$meta = Bunyad::options()->get('cat_meta_' . $query_args['anchor_obj']);
				if (empty($meta['main_color'])) {
					unset($query_args['anchor_obj']);
				}
				
			}
			
			$query_args = array_merge($query_args, array('bunyad_custom_css' => 1));
			
			/*
			 * Custom CSS Output Method - external or on-page?
			 */
			if (Bunyad::options()->css_custom_output == 'external') 
			{
				wp_enqueue_style('custom-css', add_query_arg($query_args, get_site_url() . '/'));
						
				// add css that's supposed to be per page
				$this->add_per_page_css();
			}
			else {

				include_once get_template_directory() . '/custom-css.php';

				// associate custom css at the end
				$source = 'smartmag-core';
				
				if (wp_style_is('smartmag-skin', 'enqueued')) {
					$source = 'smartmag-skin';
				}
				else if (wp_style_is('smartmag-woocommerce', 'enqueued')) {
					$source = 'smartmag-woocommerce';
				} 
				else if (wp_style_is('smartmag-font-awesome', 'enqueued')) {
					$source = 'smartmag-font-awesome';
				}
				
				// add to on-page custom css
				$render = new Bunyad_Custom_Css;
				$render->args = $query_args;
				Bunyad::core()->enqueue_css($source, $render->render() . $this->add_per_page_css(true));
			}
		}
	}
	
	/**
	 * Setup the sidebars
	 */
	public function register_sidebars()
	{
	
		// register dynamic sidebar
		register_sidebar(array(
			'name' => __('Main Sidebar', 'bunyad'),
			'id'   => 'primary-sidebar',
			'description' => __('Widgets in this area will be shown in the default sidebar.', 'bunyad'),
			'before_title' => '<h3 class="widgettitle">',
			'after_title'  => '</h3>',
		));

		
		// register dynamic sidebar
		register_sidebar(array(
			'name' => __('Top Bar (Above Header)', 'bunyad'),
			'id'   => 'top-bar',
			'description' => __('Please place only a single widget. Preferably a text widget.', 'bunyad'),
			'before_title' => '',
			'after_title'  => '',
			'before_widget' => '',
			'after_widget'  => ''
			
		));
		
		// register dynamic sidebar
		register_sidebar(array(
			'name' => __('Header Right', 'bunyad'),
			'id'   => 'header-right',
			'description' => __('Please place only a single widget. Preferably text-widget.', 'bunyad'),
			'before_title' => '',
			'after_title'  => '',
			'before_widget' => '',
			'after_widget'  => ''
			
		));
		
		// register dynamic sidebar
		register_sidebar(array(
			'name' => __('Footer (3 widgets columns)', 'bunyad'),
			'id'   => 'main-footer',
			'description' => __('Widgets in this area will be shown in the footer. Max 3 widgets.', 'bunyad'),
			'before_title' => '<h3 class="widgettitle">',
			'after_title'  => '</h3>',
			'before_widget' => '<li class="widget column %2$s">',
			'after_widget' => '</li>'
		));
		
		
		// register dynamic sidebar
		register_sidebar(array(
			'name' => __('Lower Footer', 'bunyad'),
			'id'   => 'lower-footer',
			'description' => __('Prefer simple text widgets here.', 'bunyad'),
			'before_title' => '',
			'after_title'  => '',
			'before_widget' => '',
			'after_widget'  => ''
		));
	}
	
	/**
	 * Custom CSS for pages and posts that shouldn't be cached through custom-css.php because 
	 * the size will increase exponentially.
	 * 
	 */
	public function add_per_page_css($return = false) 
	{
		if (!is_admin() && is_singular() && Bunyad::posts()->meta('bg_image')) {
			
			$bg_type = Bunyad::posts()->meta('bg_image_bg_type');
			$the_css = 'background: url("' . esc_attr(Bunyad::posts()->meta('bg_image')) . '");';
			
			if (!empty($bg_type)) {
				
				if ($bg_type == 'cover') {
					$the_css .= 'background-repeat: no-repeat; background-attachment: fixed; background-position: center center; '  
			 		. '-webkit-background-size: cover; -moz-background-size: cover;-o-background-size: cover; background-size: cover;';
				}
				else {
					$the_css .= 'background-repeat: ' . esc_attr($bg_type) .';';
				}
			}
			
			$the_css = 'body.boxed { ' . $the_css . ' }';
			
			// return the css?
			if ($return) {
				return $the_css;
			}
			
			// or enqueue it for inline css
			Bunyad::core()->enqueue_css(
				(wp_style_is('custom-css', 'enqueued') ? 'custom-css' : 'smartmag-core'), 
				$the_css
			);
		}
	}
	
	/**
	 * Action callback: Save custom meta for categories
	 */
	public function save_category_meta($term_id)
	{
		// have custom meta?
		if (!empty($_POST['meta']) && is_array($_POST['meta'])) 
		{
			$meta = $_POST['meta'];
			
			// editing?
			if (($option = Bunyad::options()->get('cat_meta_' . $term_id))) {
				$meta = array_merge($option, $_POST['meta']);
			}
			
			Bunyad::options()->update('cat_meta_' . $term_id, $meta);
			
			// clear custom css cache
			delete_transient('bunyad_custom_css_cache');
		}
	}
	
	/**
	 * Setup and recommend plugins
	 */
	public function setup_plugins()
	{
		// don't load if outside admin or if user doesn't have permission
		if (!is_admin() OR !current_user_can('install_plugins')) {
			return;
		}
		
		require_once get_template_directory() . '/lib/vendor/tgm-activation.php';

		$plugins = array(
			array(
				'name'     	=> 'Bunyad Shortcodes', // The plugin name
				'slug'     	=> 'bunyad-shortcodes', // The plugin slug (typically the folder name)
				'source'   	=> get_template_directory() . '/lib/vendor/plugins/bunyad-shortcodes.zip', // The plugin source
				'required' 	=> true, // If false, the plugin is only 'recommended' instead of required
				'force_activation' => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch

			),
	
			array(
				'name'     	=> 'Bunyad Page Builder',
				'slug'     	=> 'bunyad-siteorigin-panels',
				'source'   	=> get_template_directory() . '/lib/vendor/plugins/bunyad-siteorigin-panels.zip', 
				'required' 	=> true,
				'force_activation' => false,

			),
			
			array(
				'name'      => 'Bunyad Widgets',
				'slug'      => 'bunyad-widgets',
				'source'    => get_template_directory() . '/lib/vendor/plugins/bunyad-widgets.zip',
				'required'  => true,
				'force_activation' => false
			),
			
			array(
				'name' => 'Custom sidebars (Optional)',
				'slug' => 'custom-sidebars',
				'required' => false,			
			),
			
			array(
				'name' => 'WP Retina 2x (Recommended)',
				'slug' => 'wp-retina-2x',
				'required' => false,	
			),
			
			array(
				'name'   => 'Contact Form 7 (Optional)',
				'slug'   => 'contact-form-7',
				'required' => false,
			),
			
			array(
				'name'     => 'Revolution Slider (Optional)',
				'slug'     => 'revslider',
				'source'   => get_template_directory() . '/lib/vendor/plugins/revslider.zip',
				'required' => false,
			)
	
		);

		tgmpa($plugins, array('is_automatic' => true));
		
		// set revslider as packaged
		if (function_exists('set_revslider_as_theme')) {
			set_revslider_as_theme();
		}
		
	}
	
	/**
	 * Any layout blocks that are layout/page/theme-specific will be included to extend
	 * the default shortcodes supported by the Bunyad Shortcodes Plugin.
	 */
	public function setup_shortcodes()
	{
		if (!is_object(Bunyad::options()->shortcodes)) {
			return false;
		}
		
		Bunyad::options()->shortcodes->add_blocks(array(
			// file based
			'blog' => array('render' => locate_template('blocks/blog.php'), 'attribs' => array(
				'pagination' => 0, 'heading' => '', 'heading_type' => '', 'posts' => 4, 'type' => '', 'cats' => '', 'tags' => '',
				'sort_by' => '', 'sort_order' => '', 'taxonomy' => '', 'offset' => '', 'post_type' => '', 'pagination_type' => '',
			)),
			
			'highlights' => array('render' => locate_template('blocks/highlights.php'), 'attribs' => array(
				'type' => '', 'posts' => 4, 'cat' => null, 'column' => '', 'columns' => '', 'cats' => '', 'tags' => '', 
				'tax_tag' => '', 'headings' => '', 'title' => '', 'sort_by' => '', 'sort_order' => '', 'taxonomy' => '',
				'offset' => '', 'offsets' => '', 'post_type' => ''
			)),
			
			'review' => array('render' => locate_template('blocks/review.php'), 'attribs' => array('position' => 'bottom')),
			
			'news_focus' => array('render' => locate_template('blocks/news-focus.php'), 'attribs' => array(
				'posts' => 5, 'cat' => null, 'column' => '', 'tax_tag' => '', 'sub_cats' => '', 'sub_tags' => '',
				'sort_by' => '', 'sort_order' => '', 'highlights' => 1, 'taxonomy' => '', 'offset' => '', 'post_type' => '',
				'title' => ''
			)),
			
			// string based
			'main-color' => array('template' => '<span class="main-color">%text%</span>', 'attribs' => array('text' => '')),
		));
		
		// setup shortcode modifications
		if (is_admin()) {
			add_filter('bunyad_shortcodes_list', array($this, 'shortcodes_list'));
			add_filter('bunyad_shortcodes_lists_options', array($this, 'shortcodes_lists_options'));
			
			// add editor styling
			if (Bunyad::options()->editor_styling) {
				add_editor_style('css/editor-style.css');
			}
			
			add_filter('mce_buttons', array($this, 'add_editor_buttons'));
			add_filter('tiny_mce_before_init', array($this, 'add_editor_formats'), 1);
		}
	}


	public function add_editor_buttons($buttons) {
		array_push($buttons, 'styleselect');
		return $buttons;
	}

	/**
	 * Filter callback: Add formats to the TinyMCE Editor
	 * 
	 * @param array $settings
	 */
	public function add_editor_formats($settings) {
	
		$formats = array(
		
			array(
				'title'   => __('Quote - Modern', 'bunyad'),
				'block'   => 'blockquote',
				'classes' => 'modern-quote full',
				'wrapper' => true,
			),
			
			array(
				'title'  => __('Citation (for quote)', 'bunyad'),
				'inline' => 'cite',
			),
			
			array(
				'title'   => __('Quote Left - Modern', 'bunyad'),
				'block'   => 'aside',
				'classes' => 'modern-quote pull alignleft',
				'wrapper' => true,
			),
			
			array(
				'title'   => __('Quote Right - Modern', 'bunyad'),
				'block'   => 'aside',
				'classes' => 'modern-quote pull alignright',
				'wrapper' => true,
			),
		);
	
		$settings = array_merge($settings, array(
			'style_formats_merge' => false,
			'style_formats' =>  json_encode($formats),
		));
		
		// editor styling enabled?
		if (Bunyad::options()->editor_styling) {
			$settings['body_class'] = 'post-content';
		}
	
		// Return New Settings
		return $settings;
	}

	public function shortcodes_list($list)
	{
		// de-register unsupported shortcodes
		unset(
			$list['default']['box'],
			$list['default']['social']['dialog'], 
			$list['default']['social']['label']
		);
		return $list;
	}
	
	public function shortcodes_lists_options($options)
	{
		// remove arrow option from defaults for "Custom Lists" in gui creator
		$options['style']['options']['arrow-right'] = $options['style']['options']['arrow'];
		unset($options['style']['options']['arrow']);
		unset($options['ordered']);
		
		return $options;
	}
	
	/**
	 * Initialize the blocks used by page builder
	 */
	public function setup_page_builder()
	{
		// plugin is not active?
		if (!class_exists('Bunyad_PageBuilder_WidgetBase')) {
			return;
		}
		
		$blocks = Bunyad::options()->get_config('page_builder_blocks');
		add_filter('siteorigin_panels_widgets', array($this, 'register_builder_blocks'));
		
		foreach ($blocks as $block => $class) 
		{
			if (is_array($class)) {
				$class = $class['class'];
			}			
			
			if (strstr($class, 'Bunyad_PageBuilder')) {
				include_once get_template_directory() . '/blocks/page-builder/' . sanitize_file_name($block) . '.php';
			}
		}

		// pre-made layouts
		add_filter('siteorigin_panels_prebuilt_layouts', array($this, 'register_builder_layouts'));
	}
	
	/**
	 * Filter callback: Register usable page builder blocks
	 */
	public function register_builder_blocks($defaults)
	{
		$blocks = Bunyad::options()->get_config('page_builder_blocks');
		
		$pb_blocks = array();
		foreach ($blocks as $block => $class) {
			
			if (is_array($class)) {
				$pb_blocks[$block] = $class;
				continue;
			}
			
			$pb_blocks[$block] = array('class' => $class);
		}
		
		return array_merge((array) $defaults, $pb_blocks);
	}
	
	/**
	 * Filter callback: Setup pre-built layouts for page builder
	 * 
	 * @param array $layouts
	 */
	public function register_builder_layouts($layouts)
	{
		$layouts['Main Page'] = json_decode('{"widgets":[{"no_container":"1","posts":"","columns":"2","cat_1":"14","cat_2":"15","cat_3":"0","info":{"class":"Bunyad_PageBuilder_Highlights","id":"1","grid":"0","cell":"0"}},{"no_container":"1","posts":"","cat":"17","info":{"class":"Bunyad_PageBuilder_NewsFocus","id":"2","grid":"1","cell":"0"}},{"no_container":"1","posts":"","cat":"16","info":{"class":"Bunyad_PageBuilder_NewsFocus","id":"3","grid":"2","cell":"0"}},{"no_container":"1","type":"line","info":{"class":"Bunyad_PbBasic_Separator","id":"4","grid":"3","cell":"0"}},{"no_container":"1","posts":"","columns":"3","cat_1":"19","cat_2":"15","cat_3":"18","info":{"class":"Bunyad_PageBuilder_Highlights","id":"5","grid":"4","cell":"0"}},{"no_container":"1","title":"Recent Videos","number":"10","format":"video","cat":"0","info":{"class":"Bunyad_PageBuilder_LatestGallery","id":"6","grid":"5","cell":"0"}}],"grids":[{"cells":"1","style":""},{"cells":"1","style":""},{"cells":"1","style":""},{"cells":"1","style":""},{"cells":"1","style":""},{"cells":"1","style":""}],"grid_cells":[{"weight":"1","grid":"0"},{"weight":"1","grid":"1"},{"weight":"1","grid":"2"},{"weight":"1","grid":"3"},{"weight":"1","grid":"4"},{"weight":"1","grid":"5"}],"name":"Main Homepage Example"}', true);
		
		return $layouts;
	}
	
	/**
	 * Action callback: Add form fields to category editing / adding form
	 */
	public function edit_category_meta($term = null)
	{
		// add required assets
		wp_enqueue_style('cat-options', get_template_directory_uri() . '/admin/css/cat-options.css');
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');
		
		// add media scripts
		wp_enqueue_media(); 
		
		wp_enqueue_script('theme-options', get_template_directory_uri() . '/admin/js/options.js', array('jquery'));
		
		// get our category meta template
		include_once locate_template('admin/category-meta.php');
	}	
	
	/**
	 * Filter callback: Custom menu fields
	 */
	public function custom_menu_fields($fields)
	{
		$fields = array(
			'mega_menu' => array(
				'label' => __('Mega Menu', 'bunyad'), 
				'element' => array(
					'type' => 'select',
					'class' => 'widefat',
					'options' => array(
						0 => __('Disabled', 'bunyad'), 'category' => __('Category Mega Menu (Subcats, Featured & Recent)', 'bunyad'), 'normal' => __('Mega Menu for Links', 'bunyad')
					)
				),
				'parent_only' => true,
				'locations' => array('main'),
			)
		);
		
		return $fields;
	}
	
	/**
	 * Filter Callback: Add our custom mega-menus
	 *
	 * @param array $args
	 */
	public function attach_mega_menu($args)
	{
		extract($args);
		
		/**
		 * @todo when not using a cache plugin, wrap in functions or cache the menu
		 */
		
		// category mega menu
		if ($item->mega_menu == 'category') {
			$template = 'blocks/mega-menu-category.php';
		} 
		else if ($item->mega_menu == 'normal') {
			$template = 'blocks/mega-menu-links.php';
		}
		
		if ($template) {
			ob_start();
			include locate_template($template);
			$output = ob_get_clean();
			
			return $output;
		}
		
		return $sub_menu;
	}
	
	/**
	 * Filter callback: Add logo to the sticky navigation
	 */
	public function add_navigation_logo($items, $args)
	{
		if (!Bunyad::options()->sticky_nav OR !Bunyad::options()->sticky_nav_logo OR $args->theme_location != 'main') {
			return $items;
		}
		
		if (Bunyad::options()->image_logo_nav) {
			$logo = '<img src="' . esc_attr(Bunyad::options()->image_logo_nav) .'" />'; 
		}
		else {
			$logo = do_shortcode(Bunyad::options()->text_logo);
		}
		
		$items = '<li class="sticky-logo"><a href="'. esc_url(home_url('/')) .'">' . $logo . '</a></li>' . $items;
		
		return $items;
	}
	
	/**
	 * Filter callback: Add theme-specific profile fields
	 */
	public function add_profile_fields($fields)
	{
		$fields = array_merge((array) $fields, array(
			'twitter' => __('Twitter URL', 'bunyad'),
			'gplus'   => __('Google+ URL', 'bunyad'),
			'facebook' => __('Facebook URL', 'bunyad'),
			'linkedin' => __('LinkedIn URL', 'bunyad'),
		));
		
		return $fields;
	}
	
	/**
	 * Action callback: Add meta tags such as Google Authorship
	 */
	public function add_header_meta()
	{
		global $post; // get current post

		if (is_single()) {
			
			$gplus = get_the_author_meta('gplus', $post->post_author);
			
			if ($gplus) {
				echo '<link href="' . esc_url($gplus) .'" rel="author" />';
			}
		}
	}
	
	/**
	 * Fontawesome based post format icon
	 */
	public function post_format_icon() 
	{
		switch (get_post_format()) {
			
			case 'image':
			case 'gallery':
				$icon = 'fa-picture-o';
				break;
			
			case 'video';
				$icon = 'fa-film';
				break;
				
			case 'audio':
				$icon = 'fa-music';
				break;
				
			default:
				return '';
		}	
		
		return '<i class="fa ' . $icon .'"></i>';
	}
	
	/**
	 * Filter callback: Auto-embed video using a link
	 * 
	 * @param string $content
	 */
	public function video_auto_embed($content) 
	{
		global $wp_embed;
		
		if (!is_object($wp_embed)) {
			return $content;
		}
		
		return $wp_embed->autoembed($content);
	}
	
	/**
	 * Filter callback: Fix search by limiting to posts
	 * 
	 * @param object $query
	 */
	public function limit_search($query)
	{
		// not search query? reutrn early
		if (!$query->is_search) {
			return $query;
		}
		
		// ignore if on bbpress and woocommerce - is_bbpress() / is_woocommerce() cause 404 due to using get_queried_object()
		if (is_admin() OR (function_exists('bbp_get_query_name') && bbp_get_query_name()) OR (function_exists('is_shop') && is_shop())) {
			return $query;
		}
		
		// limit it to posts
		$query->set('post_type', 'post');
		
		return $query;
	}
	
	/**
	 * Filter callback: Add custom per page limits where set for individual category
	 * 
	 * @param object $query
	 */
	public function add_category_limits($query)
	{
		// bail out if incorrect query
		if (is_admin() OR !$query->is_category() OR !$query->is_main_query()) {
			return $query;
		}
		
		// permalinks have id or name?
		if ($query->get('cat')) {
			$category = get_category($query->get('cat'));
		}
		else {
			$category = get_category_by_slug($query->get('category_name'));	
		}
		
		// category meta
		$cat_meta = (array) Bunyad::options()->get('cat_meta_' . $category->term_id);
		
		// set user-specified per page
		if (!empty($cat_meta['per_page'])) {
			$query->set('posts_per_page', intval($cat_meta['per_page']));
		}
		
		return $query;
	}
	
	/**
	 * Add review/ratings to content
	 * 
	 * @param string $content
	 */
	public function add_review($content)
	{
		if (!is_single() OR !Bunyad::posts()->meta('reviews')) {
			return $content;
		}
		
		$position  = Bunyad::posts()->meta('review_pos');
		$shortcode = do_shortcode('[review position="'. esc_attr($position) .'"]');
		
		// based on placement
		if (strstr($position, 'top')) { 
			$content =  $shortcode . $content;
		}
		else if ($position == 'bottom') {
			$content .= $shortcode; 
		}
		
		return $content;
	}
	
	/**
	 * Filter callback: Add theme's default review snippet
	 * 
	 * @param string $content
	 */
	public function add_review_snippet($content, $type = null)
	{
		if (!Bunyad::posts()->meta('reviews')) {
			return $content;
		}	
		
		// star or bar rating?
		if ($type == 'stars') 
		{
			if (Bunyad::options()->review_style == 'stars') 
			{
				return '
					<span class="star-rating">
						<span class="main-stars"><span style="width: '. Bunyad::reviews()->decimal_to_percent(Bunyad::posts()->meta('review_overall')) .'%;">
							<strong class="rating">' . Bunyad::posts()->meta('review_overall') . '</strong></span>
						</span>
					</span>';
			}
			
			return $content;
		}
		else if (Bunyad::options()->review_style == 'bar') {
				
			return '<div class="review rate-number"><span class="progress"></span><span>' . Bunyad::posts()->meta('review_overall') . '</span></div>';
		}
		
		return $content;
	}
	
	/**
	 * Filter callback: Remove unnecessary classes
	 */
	public function fix_post_class($classes = array())
	{
		// remove hentry, we use schema.org
		$classes = array_diff($classes, array('hentry'));
		
		return $classes;
	}
	
	/**
	 * Filter callback: Add post types to page builder blocks
	 * 
	 * @param array $args  query args
	 * @param string|null $type 
	 * @param array|null $atts  shortcode attributes for this block
	 */
	public function add_post_type($args, $type = null, $atts = null)
	{
		if (is_array($atts) && !empty($atts['post_type'])) {
			$args['post_type'] = array_map('trim', explode(',', $atts['post_type']));
		}
	
		return $args;
	}
	
	/**
	 * Filter callback: Add a wrapper to the content slideshow wrapper
	 * 
	 * @param string $content
	 */
	public function add_post_slideshow_wrap($content)
	{
		if (is_single() && Bunyad::posts()->meta('content_slider') == 'ajax') {
			return '<div class="content-page">' . $content . '</div>';
		}
		
		return $content;
	}
	
	/**
	 * Action callback: Add to list processed posts to handle duplicates
	 * 
	 * @param object $query
	 */
	public function update_duplicate_posts(&$query)
	{
		// the query must enable logging
		if (empty($query->query_vars['handle_duplicates']) OR !did_action('bunyad_pre_main_content')) {
			return;
		}

		// add to list
		foreach ($query->posts as $post) 
		{
			$duplicates = (array) $this->registry['page_duplicate_posts'];
			array_push($duplicates, $post->ID); 
			
			$this->registry['page_duplicate_posts'] = $duplicates;
		}
	}
	
	/**
	 * Filter callback: Enable duplicate prevention on these query args
	 * 
	 * @param array $query  query arguments
	 */
	public function add_duplicate_exclude($query) 
	{
		if (!is_front_page()) {
			return $query;
		}
		
		if (!isset($this->registry['page_duplicate_posts'])) {
			$this->registry['page_duplicate_posts'] = array();
		}
		
		$query['post__not_in'] = $this->registry['page_duplicate_posts'];
		$query['handle_duplicates'] = true;
				
		return $query;
	}
	
	/**
	 * Modify available options for Recent Tabs widget
	 * 
	 * @param array $options
	 */
	public function tabbed_recent_options($options)
	{
		if (!empty($options['comments'])) {
			unset($options['comments']);
		}
		
		return $options;
	}	

	/**
	 * Action callback: Add login/register modal if bbPress is active
	 */
	public function bbpress_footer()
	{
		get_template_part('bbpress/auth-modal');
	}
	
	/**
	 * Filter callback: Add user login class to the correct menu item.
	 * 
	 * Mainly used for bbPress!
	 * 
	 * @param array $classes
	 */
	public function add_nav_login($classes, $item)
	{
		if (strstr($item->url, '#user-login')) {
			$classes[] = 'user-login';
		}
		
		return $classes;
	}
	
	/**
	 * Filter callback: Add custom image sizes to the editor image size selection
	 * 
	 * @param array $sizes
	 */
	public function add_image_sizes_editor($sizes) 
	{
		global $_wp_additional_image_sizes;
		
		if (empty($_wp_additional_image_sizes)) {
			return $sizes;
		}

		foreach ($_wp_additional_image_sizes as $id => $data) {

			if (in_array($id, array('main-full', 'main-slider', 'main-block', 'gallery-block')) && !isset($sizes[$id])) {
				$sizes[$id] = __('Theme - ', 'bunyad') . ucwords(str_replace('-', ' ', $id));
			}
		}
		
		return $sizes;
	}

	/**
	 * Filter callback: Set column for widgets where dynamic widths are set
	 * 
	 * @param array $params
	 * @see dynamic_sidebar()
	 */
	public function set_footer_columns($params)
	{
		static $count = 0, $columns, $last_id;
		
		if (empty($columns)) {
			$columns = array(
				'main-footer' => $this->parse_column_setting(Bunyad::options()->footer_columns)
			);
		}
		
		/**
		 * Set correct column class for each widget in footer
		 */
		
		$id = $params[0]['id'];
		
		// reset counter if last sidebar id was different than current
		if ($last_id != $id) {
			$count = 0;
		}
		
		// skip everything but these
		if (in_array($params[0]['id'], array('main-footer'))) {
			
			if (isset($columns[$id][$count])) {
				$params[0]['before_widget'] = str_replace('column', $columns[$id][$count], $params[0]['before_widget']);
			}
			
			$count++;	
		}
		
		$last_id = $id;
	
		return $params;	
	}	
	
	/**
	 * Parse columns of format 1/2+1/4+1/4 into an array of col-X
	 * 
	 * @param   array  $cols
	 * @return  array  Example: array('col-6', 'col-3', ...)
	 */
	public function parse_column_setting($cols)
	{
		$columns = array();
		
		foreach (explode('+', $cols) as $col) 
		{			
			$col = explode('/', trim($col));
			
			if (!empty($col[0]) && !empty($col[1])) {
				
				$width = number_format($col[0] / $col[1], 2);
				
				// pre-parsed map to save computation time
				$map = array(
					'0.08' => 'col-1', '0.17' => 'col-2', '0.25' => 'col-3', '0.33' => 'col-4', 
					'0.42' => 'col-5', '0.50' => 'col-6', '0.58' => 'col-7', '0.67' => 'col-8', 
					'0.75' => 'col-9', '0.83' => 'col-10', '0.92' => 'col-11', '1.00' => 'col-12'
				);
				
				if (array_key_exists($width, $map)) {
					array_push($columns, $map[$width]);
				}
			}	
		}
		
		return $columns;
	}
	
	/**
	 * Action callback: AJAX handler for live search results
	 */
	public function live_search()
	{
		
		get_template_part('partials/live-search');

		// terminate ajax request
		wp_die();
	}
	
	
	/**
	 * Action callback: Fix menu on sample import
	 * 
	 * @param object $import
	 */
	public function import_fix_menu($import)
	{
		// remove an item from menu
		$item = get_page_by_title('Shop With Sidebar', OBJECT, 'nav_menu_item');
		
		if (is_object($item)) {
			wp_delete_post($item->ID);
		}
	}

	/**
	 * Custom Menu fields for the sample menu
	 * 
	 * @param array $values
	 */
	public function import_menu_fields($values = array())
	{
		return array(
			'mega_menu' => array('Entertainment' => 'category', 'Tidbits' => 'category', 'Features' => 'normal'),
			'url' => array('Forums' => home_url('/forums/')),
		);
	}
}
