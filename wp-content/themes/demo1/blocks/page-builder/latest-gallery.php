<?php

class Bunyad_PageBuilder_LatestGallery extends Bunyad_PageBuilder_WidgetBase
{
	
	public $no_container = 1;
	public $title_field  = 'title';
	
	public function __construct()
	{
		parent::__construct(
			'bunyad_pagebuilder_latest_gallery',
			__('Latest Gallery Carousel', 'bunyad'),
			array('description' => __('Show latest videos or galleries in a carousel.', 'bunyad'))
		);
	}
	
	public function widget($args, $instance)
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		
		// supported attrs		
		$attrs = array('title', 'number', 'format', 'cat', 'tax_tag', 'offset', 'post_type');
		
		// do_shortcode will be run by pagebuilder		
		echo '[latest_gallery '. implode(' ', $this->shortcode_attribs($instance, $attrs)) .' /]';
		
	}
	
	public function form($instance)
	{
		$defaults = array('title' => '', 'number' => 10, 'format' => '', 'cat' => '', 'post_type' => '');
		$instance = array_merge($defaults, (array) $instance);
		extract($instance);
				
		$render = Bunyad::factory('admin/option-renderer'); /* @var $render Bunyad_Admin_OptionRenderer */
		
		
	?>
	
	<input type="hidden" name="<?php echo $this->get_field_name('no_container'); ?>" value="1" />
	
	<p>
		<label><?php _e('Title:', 'bunyad'); ?></label>
		<input class="widefat" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
	</p>
	
	<p>
		<label><?php _e('Number of Media:', 'bunyad'); ?></label>
		<input name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="text" value="<?php echo esc_attr($number); ?>" />
	</p>
	<p class="description"><?php _e(' Leave empty to use theme default number of posts.', 'bunyad'); ?></p>
	
	<div>
		<label><?php _e('Type of Media:', 'bunyad'); ?></label>
	
		<select class="widefat" name="<?php echo esc_attr($this->get_field_name('format')); ?>">
			<option value="video"><?php _e('Videos', 'bunyad'); ?></option>
			<option value="audio"><?php _e('Audio', 'bunyad'); ?></option>
			<option value="gallery"><?php _e('Gallery', 'bunyad'); ?></option>
			<option value="all"><?php _e('All Post Formats', 'bunyad'); ?></option>
		</select>
		
		<p class="description"><?php _e('Corresponds to "post format". e.g. videos would use posts marked as video format.', 'bunyad'); ?></p>
	</div>
	
	<p>
		<label><?php _e('Limit to Category:', 'bunyad'); ?></label>
		<?php wp_dropdown_categories(array(
			'hierarchical' => 1, 'show_option_all' => __('Not Limited', 'bunyad'), 'order_by' => 'name', 'class' => 'widefat', 'name' => $this->get_field_name('cat')
		)); ?>
	</p>
	
	<p>
		<label><?php _e('or Limit by Tags: (optional) ', 'bunyad'); ?></label>
		<input type="text" name="<?php echo $this->get_field_name('tax_tag'); ?>" value="" />
	</p>
	<p class="description"><?php _e('You can use a single or multiple tags. Separate tags with comma. e.g. cooking,sports', 'bunyad'); ?></p>
	
	<p>
		<label><?php _e('Post Types: (Advanced)', 'bunyad'); ?></label>
		<input name="<?php echo esc_attr($this->get_field_name('post_type')); ?>" type="text" value="<?php echo esc_attr($post_type); ?>" />
	</p>
	<p class="description"><?php _e('Only for advanced users! You can use a custom post type here - multiples supported when separated by comma. Leave empty to use the default format. .', 'bunyad'); ?></p>
	
	<?php
	}
}