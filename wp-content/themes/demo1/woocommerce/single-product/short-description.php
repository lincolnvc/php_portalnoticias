<?php
/**
 * Single product short description
 */

global $post;

if ( ! $post->post_excerpt ) return;
?>

<div itemprop="description" class="post-content">
	<?php echo apply_filters( 'woocommerce_short_description', $post->post_excerpt ) ?>
</div>