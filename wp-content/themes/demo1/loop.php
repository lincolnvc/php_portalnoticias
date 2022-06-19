<?php 

/**
 * "loop" to display posts when using an existing query. Uses content.php template
 * to render in normal format.
 */

?>

	<?php
	
	global $bunyad_loop;
	
	if (!is_object($bunyad_loop)) {
		$bunyad_loop = $wp_query;
	}
	
	if ($bunyad_loop->have_posts()):
	
		$attribs = array('class' => array('row listing'));
		
		// infinite load?
		if (Bunyad::options()->pagination_type == 'infinite') {
			$attribs['data-infinite'] = Bunyad::markup()->unique_id('listing-'); 
		}
		
		// set larger image when full-width, for 2-col grid
		$image = Bunyad::core()->get_sidebar() == 'none' ?  'main-slider' : 'main-block';
		
		// grid type
		$loop_grid = Bunyad::registry()->loop_grid;
		
		if ($loop_grid) {
			$attribs['class'][] = 'grid-' . $loop_grid;
			
			// change image to smaller
			$image = Bunyad::core()->get_sidebar() == 'none' ?  'main-block' : 'gallery-block';
		}
		
	?>
	
	<div <?php Bunyad::markup()->attribs('loop', $attribs); ?>>
		
		<?php while ($bunyad_loop->have_posts()): $bunyad_loop->the_post(); ?>
			
		<div class="column <?php echo ($loop_grid == 3 ? 'one-third' : 'half'); ?>">
		
			<article <?php post_class('highlights'); ?> itemscope itemtype="http://schema.org/Article">
				
			<?php
				// object has category taxonomy? i.e., is it a post?
				if (in_array('category', get_object_taxonomies(get_post_type()))):
				
					// custom label selected?				
					if (($cat_label = Bunyad::posts()->meta('cat_label'))) {
						$category = get_category($cat_label);
					}
					else {
						$category = current(get_the_category());						
					}
			?>
			
				<span class="cat-title cat-<?php echo $category->cat_ID; ?>"><a href="<?php echo esc_url(get_category_link($category)); 
					?>"><?php echo esc_html($category->name); ?></a></span>
					
			<?php endif; ?>
					
				
				<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" class="image-link">
					<?php the_post_thumbnail($image, array('class' => 'image', 'title' => strip_tags(get_the_title()), 'itemprop' => 'image')); ?>
					
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
				
				<h2 itemprop="name"><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" itemprop="url"><?php the_title(); ?></a></h2>
				
				<div class="excerpt"><?php echo Bunyad::posts()->excerpt(null, Bunyad::options()->excerpt_length_modern, array('add_more' => false)); ?></div>
			
			</article>
		</div>
			
		<?php endwhile;  ?>
				
	</div>
	
	
	<?php if (!Bunyad::options()->blog_no_pagination): // pagination can be disabled ?>
		
	<div class="main-pagination">
		<?php echo Bunyad::posts()->paginate(array(), $bunyad_loop); ?>
	</div>
		
	<?php endif; ?>
		

	<?php elseif (is_archive() OR is_search()): // show error on archive only ?>

		<article id="post-0" class="page no-results not-found">
			<div class="post-content">
				<h1><?php _e( 'Nothing Found!', 'bunyad' ); ?></h1>
				<p><?php _e('Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', 'bunyad'); ?></p>
			</div><!-- .entry-content -->
		</article><!-- #post-0 -->
	
	<?php endif; ?>
