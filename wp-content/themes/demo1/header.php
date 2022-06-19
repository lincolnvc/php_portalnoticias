<!DOCTYPE html>

<!--[if IE 8]> <html class="ie ie8" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9]> <html class="ie ie9" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 9]><!--> <html <?php language_attributes(); ?>> <!--<![endif]-->

<head>

<?php 
/**
 * Match wp_head() indent level
 */
?>

<meta charset="<?php bloginfo('charset'); ?>" />
<title><?php wp_title(''); // stay compatible with SEO plugins ?></title>

<?php if (!Bunyad::options()->no_responsive): // don't add if responsiveness disabled ?> 
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?php endif; ?>
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	
<?php if (Bunyad::options()->favicon): ?>
<link rel="shortcut icon" href="<?php echo esc_attr(Bunyad::options()->favicon); ?>" />	
<?php endif; ?>

<?php if (Bunyad::options()->apple_icon): ?>
<link rel="apple-touch-icon-precomposed" href="<?php echo esc_attr(Bunyad::options()->apple_icon); ?>" />
<?php endif; ?>
	
<?php wp_head(); ?>
	
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->

</head>

<body <?php body_class(); ?>>

<div class="main-wrap">

	<?php 
	
	/**
	 * Get the partial template for top bar
	 */
	get_template_part('partials/header/top-bar'); 
	
	?>
	
	<div id="main-head" class="main-head">
		
		<div class="wrap">
		
		
			<?php 
					
				/**
				 * Get the header based on settings
				 */
				$header = Bunyad::options()->header_style ? Bunyad::options()->header_style : 'default';
				
				get_template_part('partials/header/' . $header); 

				
				/**
				 * Setup data variables to enable or disable sticky nav functionality
				 */
				$attribs = array('class' => array('navigation cf', Bunyad::options()->nav_align));
				
				if (Bunyad::options()->sticky_nav) {
					
					$attribs['data-sticky-nav'] = 1;
								
					// sticky navigation logo?
					if (Bunyad::options()->sticky_nav_logo) {
						$attribs['data-sticky-logo'] = 1;
					}
				}

			?>
			
			<nav <?php Bunyad::markup()->attribs('navigation', $attribs); ?>>
			
				<div class="mobile" data-type="<?php echo Bunyad::options()->mobile_menu_type; ?>" data-search="<?php echo Bunyad::options()->mobile_nav_search; ?>">
					<a href="#" class="selected">
						<span class="text"><?php _e('Navigate', 'bunyad'); ?></span><span class="current"></span> <i class="hamburger fa fa-bars"></i>
					</a>
				</div>
				
				<?php wp_nav_menu(array('theme_location' => 'main', 'fallback_cb' => '', 'walker' =>  'Bunyad_Menu_Walker')); ?>
			</nav>
			
		</div>
		
	</div>
	
<?php if (!Bunyad::options()->disable_breadcrumbs): ?>
	<div class="wrap">
		<?php Bunyad::core()->breadcrumbs(); ?>
	</div>
<?php endif; ?>

<?php do_action('bunyad_pre_main_content'); ?>