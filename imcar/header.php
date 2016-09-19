<!DOCTYPE html>
<html <?php language_attributes(); ?> class="boxed">
<head>

<meta charset="<?php bloginfo( 'charset' ); ?>" />

<title><?php 
	global $page, $paged;
	wp_title( '|', true, 'right' );
	bloginfo( 'name' );
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );
?></title>
<link rel="stylesheet" type="text/css" href="<?php bloginfo( 'stylesheet_url' ); ?>" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/layout.css">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<?php
	wp_head();
?>
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/jquery.nestedAccordion.js"></script>
<script type="text/javascript" src="<?php echo get_template_directory_uri(); ?>/js/imcar.js"></script>
</head>

<body <?php body_class(); ?>>

<div id="wrapper">
	<div id="top">
		<nav class="col-full" role="navigation">
			<?php if ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'top-menu' ) ) { ?>
			<?php wp_nav_menu( array( 'depth' => 6, 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => 'top-nav', 'menu_class' => 'nav fl', 'theme_location' => 'top-menu' ) ); ?>
			<?php } ?>
		</nav>
	</div><!-- /#top -->

	<header id="header" class="col-full">
	    <hgroup>
	    	 <?php
			    $logo = esc_url( get_template_directory_uri() . '/images/logo2.png' );
			?>
			    <a id="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php esc_attr( get_bloginfo( 'description' ) ); ?>">
			    	<img src="<?php echo $logo; ?>" alt="<?php esc_attr( get_bloginfo( 'name' ) ); ?>" />
			    </a>
	        
			<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
			<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
		      	
		</hgroup>

        


<div id="left3">
<a href="http://usa.imcar.com.ua/" target="_blanck">
<img class="alignnone size-medium wp-image-170" src="<?php echo get_template_directory_uri(); ?>/images/buy-auto.png" alt="США" width="300" height="51" /></a>
</div>

<div id="con">
<p>Андрей: +38 (093) 50 50 777; +38 (098) 45 00 400;</p>
<p>+38 (099) 64 88 855</p>
<p>Артур: +38 (093) 50 50 444; +38 (097) 77 00 444</p>

</div>
		<nav id="navigation" class="col-full" role="navigation">
			
			<?php
			if ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'primary-menu' ) ) {
				wp_nav_menu( array( 'depth' => 6, 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => 'main-nav', 'menu_class' => 'nav fr', 'theme_location' => 'primary-menu' ) );
			} else {
			?>
			
	        <ul id="main-nav" class="nav fl">
				<?php if ( is_page() ) $highlight = 'page_item'; else $highlight = 'page_item current_page_item'; ?>
				<li class="<?php echo $highlight; ?>"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php _e( 'Home', 'woothemes' ); ?></a></li>
				<?php wp_list_pages( 'sort_column=menu_order&depth=6&title_li=&exclude=' ); ?>
			</ul><!-- /#nav -->
	        <?php } ?>
	
		</nav><!-- /#navigation -->

	
	</header><!-- /#header -->
		
