<?php
get_header(); ?>

    <div id="content" class="col-full">
    
		<section id="main" class="col-right">  

<h1><?php wp_title(''); // Заголовок категории ?></h1>
<?php if (have_posts()) : while (have_posts()) : the_post(); // Цикл записей ?>
<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><!-- Заголовок поста + ссылка на него -->
<?php the_time('F j, Y'); ?>
<?php if ( has_post_thumbnail() ) { the_post_thumbnail(); } // Проверяем наличие миниатюры, если есть показываем ?>
<?php the_content('');?>
<?php endwhile; // Конец цикла.
else: echo '<h2>Извините, ничего не найдено...</h2>'; endif; // Если записей нет - извиняемся ?>	 
<?php // Пагинация
global $wp_query;
$big = 999999999;
echo paginate_links( array(
	'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
	'format' => '?paged=%#%',
	'current' => max( 1, get_query_var('paged') ),
	'type' => 'list',
	'prev_text'    => __('« Сюда'), 
    'next_text'    => __('Туда »'),
	'total' => $wp_query->max_num_pages
) );
?>
		</section><!-- /#main -->
		
        <?php get_sidebar(); ?>
    </div><!-- /#content -->


<?php get_footer(); ?>