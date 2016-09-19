<?php
	get_header();
?>
    <div id="content" class="page col-full">
		<section id="main" class="col-right"> 			

        <?php
        	if ( have_posts() ) { $count = 0;
				woocommerce_content();
			} else {
		?>
			<article <?php post_class(); ?>>
            	<p><?php _e( 'Sorry, no posts matched your criteria.', 'woothemes' ); ?></p>
            </article><!-- /.post -->
        <?php } // End IF Statement ?>  
        
		</section><!-- /#main -->
		
         <?php get_sidebar(); ?>

    </div><!-- /#content -->
<?php get_footer(); ?>