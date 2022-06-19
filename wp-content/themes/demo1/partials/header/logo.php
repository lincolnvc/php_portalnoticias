<?php
/**
 * Partial: Logo 
 */
?>

		<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home">
		
			<?php if (Bunyad::options()->image_logo): // custom logo ?>
				
				<img src="<?php echo esc_attr(Bunyad::options()->image_logo); ?>" class="logo-image" alt="<?php 
					 echo esc_attr(get_bloginfo('name', 'display')); ?>" <?php 
					 echo (Bunyad::options()->image_logo_retina ? 'data-at2x="'. esc_attr(Bunyad::options()->image_logo_retina) .'"' : ''); 
				?> />
					 
			<?php else: ?>
				<?php echo do_shortcode(Bunyad::options()->text_logo); ?>
			<?php endif; ?>
			
		</a>