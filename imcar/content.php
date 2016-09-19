<?php
 	$settings = array(
					'thumb_w' => 787, 
					'thumb_h' => 300, 
					'thumb_align' => 'aligncenter'
					);
					
?>
	<article <?php post_class(); ?>>
		<aside class="meta">
			<span class="month"><?php the_time( 'M' ); ?></span>
			<span class="day"><?php the_time( 'd' ); ?></span>
			<span class="year"><?php the_time( 'o' ); ?></span>
		</aside>
		
		<section class="post-content">
		    
			<header>
				<h1><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
			</header>
	
			<section class="entry">
			<?php { the_excerpt(); } ?>
			</section>
		</section><!--/.post-content -->
	</article><!-- /.post -->
