<?php

/**
 * Tag or category taxonomy?
 */
$is_tag = $is_cat = false;

$query_args = array(
	'posts_per_page' => (!empty($posts) ? intval($posts) : 5), 
	'order' => ($sort_order == 'asc' ? 'asc' : 'desc'),
	'offset' => ($offset ? $offset : '')
);

if (!empty($taxonomy))
{
	$_taxonomy = $taxonomy; // preserve
	
	// get the tag
	$taxonomy = get_term_by('id', $cat, $_taxonomy);
	
	$link = get_term_link($taxonomy, $_taxonomy);
	$query_args['tax_query'] = array(array(
		'taxonomy' => $_taxonomy,
		'field' => 'id',
		'terms' => (array) $cat
	));
}
else if (!empty($cat)) {
	 
	// get latest from the specified category
	$is_cat = true;
	$taxonomy = is_numeric($cat) ? get_category(intval($cat)) : get_category_by_slug($cat);
	
	$link = get_category_link($taxonomy);
	$query_args['category_name'] =  $taxonomy->slug;
}
else if (!empty($tax_tag)) {
	
	$is_tag = true;
	$taxonomy = get_term_by('slug', $tax_tag, 'post_tag');
	
	$link = get_term_link($taxonomy, 'post_tag');
	$query_args['tag'] =  $taxonomy->slug;
}

/**
 * Setup Main Query
 */

if ($sort_by == 'modified') {
	$query_args['orderby'] = 'modified';
}
else if ($sort_by == 'random') {
	$query_args['orderby'] = 'rand';
}

$query_args = apply_filters('bunyad_block_query_args', $query_args, 'news_focus', $atts);

// main query
$query = new WP_Query($query_args);	

// check if it exists
if (!is_object($taxonomy)) {
	$sub_cats = array();
	$link = '#';
}
else if (empty($title)) {
	$title = $taxonomy->name;
}

$highlights = (empty($highlights) ? 1 : intval($highlights));

/**
 * Setup sub-categories to display
 */

$sub_cats = !empty($sub_cats) ? $sub_cats : array();

// selected sub-cats?
if (!empty($sub_cats)) {
	$sub_cats = get_categories(array('include' => $sub_cats, 'hierarchical' => false, 'taxonomy' => (!empty($_taxonomy) ? $_taxonomy : 'category')));
}
// entered sub tags instead?
else if (!empty($sub_tags)) {
	$sub_cats = array();
	
	foreach ((array) explode(',', $sub_tags) as $_tag) {
		array_push($sub_cats, get_term_by('slug', $_tag, 'post_tag'));
	}
}
else if (!empty($taxonomy) && property_exists($taxonomy, 'cat_ID')) {
	  
	// empty, default to child sub categories
	$sub_cats = get_categories(array('child_of' => $taxonomy->cat_ID, 'number' => 3, 'hierarchical' => false));
}

?>

<section class="news-focus">

	<div class="section-head heading cat-<?php echo $taxonomy->term_id; ?>">
		<a href="<?php echo esc_url($link); ?>" title="<?php echo esc_attr($title); ?>"><?php echo esc_html($title); ?></a>
	
		<?php if (count($sub_cats)): ?>
		<ul class="subcats">

			<li><a href="#" class="active" data-id="0"><?php _e('All', 'bunyad'); ?></a></li>
			
			<?php foreach ($sub_cats as $cat): ?>
				<li><a href="#" data-id="<?php echo $cat->term_id; ?>"><?php echo esc_html($cat->name); ?></a></li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</div>
	
	<?php 
	
	foreach (array_merge(array($taxonomy), $sub_cats) as $key => $sub_cat): 

		$count = $id = 0;
	
		
		if ($key !== 0) {
			
			// post tag?
			if ($sub_cat->taxonomy == 'post_tag') {
				$query = new WP_Query(array_merge($query_args, array('tag' => $sub_cat->slug)));
			}
			else if ($sub_cat->taxonomy != 'category') {
				
				// custom taxonomy
				$query_args['tax_query'][0]['terms'] = (array) $sub_cat->term_id;
				$query = new WP_Query($query_args);
			}
			else {
				// normal category
				$query = new WP_Query(array_merge($query_args, array('category_name' => $sub_cat->slug)));
			}
			
			$id = $sub_cat->term_id;
		}
		
	?>
	
	<div class="row news-<?php echo $id; ?> highlights">

		<div class="column half blocks">
		
		<?php
			// main posts - better highlighted 
			while ($query->have_posts()): $query->the_post(); $count++; 			
			?>
			<article itemscope itemtype="http://schema.org/Article">
					
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="image-link" itemprop="url">
					<?php the_post_thumbnail(
								(Bunyad::core()->get_sidebar() != 'none' && $column == '1/3' 
										? 'gallery-block' 
										: (Bunyad::core()->get_sidebar() == 'none' ?  'main-slider' : 'main-block')
								), 
								array('class' => 'image', 'title' => strip_tags(get_the_title()))); ?>
					
					<?php if (get_post_format()): ?>
						<span class="post-format-icon <?php echo esc_attr(get_post_format()); ?>"><?php
							echo apply_filters('bunyad_post_formats_icon', ''); ?></span>
					<?php endif; ?>
					
					<?php echo apply_filters('bunyad_review_main_snippet', '', 'stars'); ?>
				</a>
				
				<div class="meta">
					<time datetime="<?php echo get_the_date(__('Y-m-d\TH:i:sP', 'bunyad')); ?>" itemprop="datePublished"><?php echo get_the_date(); ?> </time>
					
					<?php echo apply_filters('bunyad_review_main_snippet', ''); ?>
					
					<span class="comments"><a href="<?php echo esc_attr(get_comments_link()); ?>"><i class="fa fa-comments-o"></i>
						<?php echo get_comments_number(); ?></a></span>
					
				</div>
				
				<h2 itemprop="name"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
				
				<div class="excerpt">
					<?php echo Bunyad::posts()->excerpt(null, Bunyad::options()->excerpt_length_news_focus, array('add_more' => false)); ?>
				</div>
				
			</article>
			
			<?php 	
				if ($count == $highlights) {
					break;
				}
			?>
			
		
		<?php endwhile; ?>
		
		</div>
		
		
		<ul class="column half block posts-list thumb">

		<?php while ($query->have_posts()): $query->the_post(); ?>
		
			<?php // other posts, in a list ?>
			
				<li>				
				
					<a href="<?php the_permalink() ?>"><?php the_post_thumbnail('post-thumbnail', array('title' => strip_tags(get_the_title()))); ?>

					<?php if (class_exists('Bunyad') && Bunyad::options()->review_show_widgets): ?>
						<?php echo apply_filters('bunyad_review_main_snippet', ''); ?>
					<?php endif; ?>
					
					</a>
					
					<div class="content">

						<?php 
							/* $category = current(get_the_category());
						
						<span class="cat-title cat-<?php echo $category->cat_ID; ?>"><a href="<?php echo esc_url(get_category_link($category)); ?>"><?php echo esc_html($category->name); ?></a></span>
						
						*/ ?>

						<time datetime="<?php echo get_the_date('Y-m-d\TH:i:sP'); ?>"><?php echo get_the_date(); ?> </time>

					
						<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>">
							<?php if (get_the_title()) the_title(); else the_ID(); ?></a>
							
						<?php echo apply_filters('bunyad_review_main_snippet', '', 'stars'); ?>
																	
					</div>			
					
				</li>
		
		<?php endwhile; ?>
		
		</ul>
			
		<?php wp_reset_query(); ?>
		
	</div>
	
	<?php endforeach; ?>
		
</section>