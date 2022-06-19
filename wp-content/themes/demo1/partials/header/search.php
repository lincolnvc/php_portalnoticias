<?php
/**
 * Partial: Search used in header
 */

$classes = array('query');

// live search enabled?
if (Bunyad::options()->topbar_live_search) {
	$classes[] = 'live-search-query';
}

?>

			<div class="search">
				<form role="search" action="<?php echo esc_url(home_url('/')); ?>" method="get">
					<input type="text" name="s" class="<?php echo implode(' ', $classes); ?>" value="<?php the_search_query(); ?>" placeholder="<?php _e('Search...', 'bunyad'); ?>"/>
					<button class="search-button" type="submit"><i class="fa fa-search"></i></button>
				</form>
			</div> <!-- .search -->