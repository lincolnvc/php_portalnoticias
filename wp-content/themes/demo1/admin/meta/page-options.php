<?php

/**
 * Options metabox for pages
 */

$rev_slider = (class_exists('RevSlider') ? array('rev-slider' => __('Revolution Slider Plugin', 'bunyad')) : array()); 

$options = array(
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
		'label' => __('Show Page Title?', 'bunyad'),
		'name'  => 'page_title', 
		'type'  => 'select',
		'options' => array('yes' => 'Yes', 'no' => 'No'),
		'value' => 'yes' // default
	),
	
	array(
		'label' => __('Show Featured Slider?', 'bunyad'),
		'name'  => 'featured_slider',
		'type'  => 'select',
		'options' => array_merge(array(
			''	=> __('None', 'bunyad'),
			'default' => __('Default Slider - Use Posts Marked as "Featured Slider Post?"', 'bunyad'),
			'default-latest' => __('Default Slider - Use Latest Posts from Whole Site', 'bunyad'),
		), $rev_slider),
		'value' => '' // default
	),

	array(
		'label' => __('Number of Slides', 'bunyad'),
		'name'  => 'slider_number',
		'type'  => 'text',
		'desc'  => __('Number of posts to show on the left side of the slider. 3 are displayed on the right as a post grid.', 'bunyad'),
		'value' => 5, // default
	),
	
	array(
		'label' => __('Slider Limit by Tag', 'bunyad'),
		'name'  => 'slider_tags',
		'desc'  => __('Optional: To limit slider to certain tag or tags. If multiple, separate tag slugs by comma.', 'bunyad'),
		'type'  => 'text',
		'value' => '' // default
	),
	
	array(
		'label' => __('Slider Manual Post Ids', 'bunyad'),
		'name'  => 'slider_posts',
		'desc'  => __('Optional: ADVANCED! If you only want to show a set of selected pre-selected posts. Enter post ids separated by comma.', 'bunyad'),
		'type'  => 'text',
		'value' => '' // default
	),
	
	array(
		'label' => __('Revolution Slider Alias', 'bunyad'),
		'name'  => 'slider_rev',
		'desc'  => __('Enter alias of a slider you created in revolution slider plugin.', 'bunyad'),
		'type'  => 'text',
		'value' => '' // default
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

$options = $this->options(apply_filters('bunyad_metabox_page_options', $options));

?>

<div class="bunyad-meta cf">

<?php foreach ($options as $element): ?>
	
	<div class="option <?php echo esc_attr($element['name']); ?>">
		<span class="label"><?php echo esc_html($element['label']); ?></span>
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
	$('._bunyad_featured_slider select').on('change', function() {

		var depend_default = '._bunyad_slider_number, ._bunyad_slider_posts, ._bunyad_slider_tags',
			depend_rev = '._bunyad_slider_rev';

		// hide all dependents
		$([depend_default, depend_rev].join(',')).hide();
		
		if ($(this).val() == 'rev-slider') {
			$(depend_rev).show();
		}
		else if ($(this).val() != '') {
			$(depend_default).show();
		}

		return;
	});

	// on-load
	$('._bunyad_featured_slider select').trigger('change');
		
});
</script>