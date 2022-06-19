<?php
/**
 * Meta box for post options
 */

$options = array(

	array(
		'label' => __('Featured Slider Post?', 'bunyad'),
		'name'  => 'featured_post', // _bunyad_featured_post
		'type'  => 'checkbox',
		'value' => 0
	),
	
	array(
		'label' => __('Post Layout', 'bunyad'),
		'name'  => 'layout_template', // will be _bunyad_layout_style
		'type'  => 'select',
		'options' => array(
			'' => __('Default (from Theme Settings)', 'bunyad'),
			'classic' => __('Classic', 'bunyad'),
			'cover' => __('Post Cover', 'bunyad'),
			'classic-above' => __('Classic - Title First', 'bunyad'),
		),
		'value' => '' // default
	),
	
	array(
		'label' => __('Layout Style', 'bunyad'),
		'name'  => 'layout_style', // will be _bunyad_layout_style
		'type'  => 'radio',
		'options' => array(
			'' => __('Default', 'bunyad'),
			'right' => __('Right Sidebar', 'bunyad'),
			'full' => __('Full Width', 'bunyad')),
		'value' => '' // default
	),
	
	array(
		'label' => __('Category Label Overlay', 'bunyad'),
		'name'  => 'cat_label', // _bunyad_cat_label
		'type'  => 'html',
		'html' =>  wp_dropdown_categories(array(
			'show_option_all' => __('-- Auto Detect--', 'bunyad'), 
			'hierarchical' => 1, 'order_by' => 'name', 'class' => '', 
			'name' => '_bunyad_cat_label', 'echo' => false,
			'selected' => Bunyad::posts()->meta('cat_label')
		)),
		'desc' => __('When you have multiple categories for a post, auto detection chooses one in alphabetical order. These labels are shown above image in category listings.', 'bunyad')
	),
	
	array(
		'label' => __('Multi-page Content Slideshow?', 'bunyad'),
		'desc' => __('You can use <!--nextpage--> to split a page into multi-page content slideshow.', 'bunyad'),
		'name'  => 'content_slider', // _bunyad_featured_post
		'type'  => 'select',
		'value' => 0,
		'options' => array(
			'' => __('Disabled', 'bunyad'),
			'ajax' => __('AJAX - No Refresh', 'bunyad'),
			'refresh'  => __('Multi-page - Refresh for next page', 'bunyad'), 
		),
	),
	
	
	array(
		'label_left' => __('Disable Featured?', 'bunyad'),
		'label' => __('Do not show featured Image, Video, or Gallery at the top for this post, on post page.', 'bunyad'),
		'name'  => 'featured_disable', // _bunyad_featured_post
		'type'  => 'checkbox',
		'value' => 0
	),
	
	array(
		'label' => __('Featured Video Code', 'bunyad'),
		'name'  => 'featured_video', // will be _bunyad_layout_style
		'type'  => 'textarea',
		'options' => array('rows' => 7, 'cols' => 90),
		'value' => '',
	),
);

if (Bunyad::options()->layout_style == 'boxed') {
	
	$options[] = array(
		'label' => __('Custom Background Image', 'bunyad'),
		'name'  => 'bg_image',
		'type' => 'upload',
		'options' => array(
				'type'  => 'image',
				'title' => __('Upload This Picture', 'bunyad'), 
				'button_label' => __('Upload',  'bunyad'),
				'insert_label' => __('Use as Background',  'bunyad')
		),	
		'value' => '', // default
		'bg_type' => array('value' => 'cover'),
	);
}

$options = $this->options($options);

?>

<div class="bunyad-meta cf">

<?php foreach ($options as $element): ?> 
	
	<div class="option <?php echo esc_attr($element['name']); ?>">
		<span class="label"><?php echo esc_html(isset($element['label_left']) ? $element['label_left'] : $element['label']); ?></span>
		<span class="field">
			<?php echo $this->render($element); ?>
		
			<?php if (!empty($element['desc'])): ?>
			
			<p class="description"><?php echo esc_html($element['desc']); ?></p>
		
			<?php endif;?>
		
		</span>
	</div>
	
<?php endforeach; ?>

</div>

<?php wp_enqueue_script('theme-options', get_template_directory_uri() . '/admin/js/options.js', array('jquery')); ?>

<script>
/**
 * Conditional show/hide 
 */

jQuery(function($) {

	/**
	 * Hide disable featured and featured video option on cover layout
	 */
	var default_layout = '<?php echo esc_js(Bunyad::options()->post_layout_template); ?>';
	
	$('._bunyad_layout_template select').on('change', function() {

		var depend = '._bunyad_featured_disable, ._bunyad_featured_video', layout = '';

		// if current selection is cover or the default is cover format
		if ($(this).val() == 'cover' || (!$(this).val() && default_layout == 'cover')) {
			layout = 'cover';
		}
		
		(layout == 'cover' ? $(depend).hide() : $(depend).show());
	});

	// on-load
	$('._bunyad_layout_template select').trigger('change');
		
});
</script>