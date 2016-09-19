<?php
	get_header();
?>
   
    <div id="content" class="col-full">
    
		<section id="main" class="col-right">  
		
		<?php
			
//			$the_query = new WP_Query( array( 'posts_per_page' => 10 ) );
			
        	if ( have_posts() ) : $count = 0;
        ?>
        
			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); $count++; ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to overload this in a child theme then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', get_post_format() );
				?>

			<?php 
				endwhile; 
				// Reset Post Data
				//wp_reset_postdata();
			?>
			
			

		<?php else : ?>
        
      <article <?php post_class(); ?>>
                <p><?php _e( 'Sorry, no posts matched your criteria.'); ?></p>
            </article><!-- /.post -->
        
        <?php endif; ?>
        
		                
		</section><!-- /#main -->
		
        <?php get_sidebar(); ?>

    </div><!-- /#content -->
		
<?php get_footer(); ?>