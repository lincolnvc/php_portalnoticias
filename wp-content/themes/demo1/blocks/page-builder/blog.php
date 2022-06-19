<?php

class Bunyad_PageBuilder_Blog extends Bunyad_PageBuilder_WidgetBase
{
	
	public $no_container = 1;
	public $title_field  = 'heading,type';
	
	public function __construct()
	{
		parent::__construct(
			'bunyad_pagebuilder_blog',
			__('Blog/Listing Block', 'bunyad'),
			array('description' => __('Used for category style listing - ex. a blog view. Supports pagination.', 'bunyad'))
		);
	}
	
	public function widget($args, $instance)
	{
		extract($args);
		
		// supported attributes
		$attrs = array('pagination', 'heading', 'heading_type', 'posts', 'type', 'cats', 'tags', 'sort_order', 'sort_by', 'offset', 'post_type', 'pagination_type');
		
		// do_shortcode will be run by pagebuilder		
		echo '[blog '. implode(' ', $this->shortcode_attribs($instance, $attrs)) .' /]';
		
	}
	
	public function form($instance)
	{
		$defaults = array('pagination' => 0, 'heading' => '', 'heading_type' => '', 'posts' => 4, 'type' => '', 'cats' => '', 'post_type' => '');
		$instance = array_merge($defaults, (array) $instance);
		extract($instance);
				
		$render = Bunyad::factory('admin/option-renderer'); /* @var $render Bunyad_Admin_OptionRenderer */
		
		
	?>
	
	<input type="hidden" name="<?php echo $this->get_field_name('no_container'); ?>" value="1" />
		
	<p>
		<label><?php _e('Number of Posts:', 'bunyad'); ?></label>
		<input name="<?php echo esc_attr($this->get_field_name('posts')); ?>" type="text" value="<?php echo esc_attr($posts); ?>" />
	</p>
	<p class="description"><?php _e('Configures posts to show for each listing. Leave empty to use theme default number of posts.', 'bunyad'); ?></p>
	
	<p>
		<label><?php _e('Sort By:', 'bunyad'); ?></label>
		<select name="<?php echo esc_attr($this->get_field_name('sort_by')); ?>">
			<option value=""><?php _e('Published Date', 'bunyad'); ?></option>
			<option value="modified"><?php _e('Modified Date', 'bunyad'); ?></option>
			<option value="random"><?php _e('Random', 'bunyad'); ?></option>
		</select>
		
		<select name="<?php echo esc_attr($this->get_field_name('sort_order')); ?>">
			<option value="desc"><?php _e('Latest First - Descending', 'bunyad'); ?></option>
			<option value="asc"><?php _e('Oldest First - Ascending', 'bunyad'); ?></option>
		</select>
	</p>
	
	<p>
		<label><?php _e('Listing Style:', 'bunyad'); ?></label>
		
		<select class="widefat" name="<?php echo esc_attr($this->get_field_name('type')); ?>">
			<option value=""><?php _e('Default Style (In Theme Settings)', 'bunyad'); ?></option>
			<option value="modern"><?php _e('Modern Style - 2 Column', 'bunyad'); ?></option>
			<option value="modern-3"><?php _e('Modern Style - 3 Column', 'bunyad'); ?></option>
			<option value="grid-overlay"><?php _e('Grid Overlay - 2 Column', 'bunyad'); ?></option>
			<option value="grid-overlay-3"><?php _e('Grid Overlay - 3 Column', 'bunyad'); ?></option>
			<option value="alt"><?php _e('Blog Style', 'bunyad'); ?></option>
			<option value="classic"><?php _e('Classic - Large Blog Style', 'bunyad'); ?></option>
			<option value="timeline"><?php _e('Timeline Style', 'bunyad'); ?></option>
		</select>

	</p>
	<p class="description"><?php _e('Check docs and demo to choose the right style.', 'bunyad'); ?></p>
	
	<p>
		<label><?php _e('Heading: (Optional)', 'bunyad'); ?></label>
		<input class="widefat" name="<?php echo esc_attr($this->get_field_name('heading')); ?>" type="text" value="<?php echo esc_attr($heading); ?>" />
	</p>
	<p class="description"><?php _e('Optional heading.', 'bunyad'); ?></p>
	
	<p>
		<label><?php _e('Heading Style:', 'bunyad'); ?></label>
		<select class="widefat" name="<?php echo esc_attr($this->get_field_name('heading_type')); ?>">
			<option value=""><?php _e('Page Heading Style', 'bunyad'); ?></option>
			<option value="block"><?php _e('Block Section Heading Style', 'bunyad'); ?></option>
		</select>
	</p>
	<p class="description"><?php _e('Page heading style is normal heading style used for pages. Block section heading style is what you would see often on 
		homepage with multiple sections.', 'bunyad'); ?></p>
		
			
	<div>
		<label><?php _e('Pagination:', 'bunyad'); ?></label>
		<select class="widefat" name="<?php echo esc_attr($this->get_field_name('pagination')); ?>">
			<option value="0"><?php _e('Disabled', 'bunyad'); ?></option>
			<option value="1"><?php _e('Enabled', 'bunyad'); ?></option>
		</select>
	</div>
	
			
	<div>
		<label><?php _e('Pagination Type:', 'bunyad'); ?></label>
		<select class="widefat" name="<?php echo esc_attr($this->get_field_name('pagination_type')); ?>">
			<option value=""><?php _e('Normal', 'bunyad'); ?></option>
			<option value="infinite"><?php _e('Infinite Scroll', 'bunyad'); ?></option>
		</select>
	</div>
	<p class="description"><?php _e('WARNING: Infinite Scroll will only work for the last block on a page. Infinite scroll loads more posts as user scrolls.', 'bunyad'); ?></p>
	
	<div class="taxonomydiv"> <!-- borrow wp taxonomydiv > categorychecklist css rules -->
		<label><?php _e('Limit Categories:', 'bunyad'); ?></label>
		
		<div class="tabs-panel">
			<ul class="categorychecklist">
				<?php
				ob_start();
				wp_category_checklist();
				
				echo str_replace('post_category[]', $this->get_field_name('cats') .'[]', ob_get_clean());
				?>
			</ul>			
		</div>
	</div>
	<p class="description"><?php _e('By default, all categories will be used. Tick categories to limit to a specific category or categories.', 'bunyad'); ?></p>
	
	<p class="tag">
		<?php _e('or Limit with Tags: (optional)', 'bunyad'); ?> 
		<input type="text" name="<?php echo $this->get_field_name('tags'); ?>" value="" class="widefat" />
	</p>
	
	<p class="description"><?php _e('Separate tags with comma. e.g. cooking,sports', 'bunyad'); ?></p>
	
	<p>
		<label><?php _e('Offset: (Advanced)', 'bunyad'); ?></label> 
		<input type="text" name="<?php echo $this->get_field_name('offset'); ?>" value="0" />
	</p>
	<p class="description"><?php _e('By specifying an offset as 10 (for example), you can ignore 10 posts in the results.', 'bunyad'); ?></p>
	
	<p>
		<label><?php _e('Post Types: (Advanced)', 'bunyad'); ?></label>
		<input name="<?php echo esc_attr($this->get_field_name('post_type')); ?>" type="text" value="<?php echo esc_attr($post_type); ?>" />
	</p>
	<p class="description"><?php _e('Only for advanced users! You can use a custom post type here - multiples supported when separated by comma. Leave empty to use the default format. .', 'bunyad'); ?></p>
	
	<?php
	}
}
