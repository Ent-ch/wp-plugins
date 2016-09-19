<?php
get_header(); ?>

    <div id="content" class="col-full">
        <section id="main" class="col-right">  
                <h1><?php wp_title(''); ?></h1>
<?php if (have_posts()) : while (have_posts()) : the_post(); // Цикл записей ?>
            <?php get_template_part('prod-bl');?>
<?php endwhile; // Конец цикла.
    else: 
        echo '<h2>Извините, ничего не найдено...</h2>'; 
    endif;  ?>	 
            <?php get_template_part('pagination');?>
		</section><!-- /#main -->
		
        <?php get_sidebar(); ?>
    </div><!-- /#content -->


<?php get_footer(); ?>