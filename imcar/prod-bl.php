    <div class="product">
    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <?php // the_time('F j, Y'); ?>
        <?php if ( has_post_thumbnail() ) : ?>
            <a href="<?php the_permalink(); ?>">
            <?php the_post_thumbnail(); ?>
            </a>
        <?php endif; ?>
        <?php //print(strip_tags(get_the_content('')));?>
    </div>
