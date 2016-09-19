<aside id="sidebar" class="col-left">

    <div class="primary">
        
        <div class="widget">
            <h2>Модели</h2>
            <ul class="widget widget_categories ">
                <?php wp_list_categories('orderby=name&taxonomy=models&exclude='.SPECCAT.'&title_li='); ?> 
            </ul>
		<?php
        if (is_tax('models') ):
            $model = get_queried_object();
            $term_link = get_term_link($model);
            $url_model = esc_url($term_link);
?>          <h2>Группы</h2>
            <div id="accordion">
            <ul class="widget widget_categories accordion">
                <?php 
                    $args = array('taxonomy' => 'parts', 'title_li' => false, 'hide_empty' => true, 'walker' => new Parts_level($url_model));
                    wp_list_categories($args); 
                ?> 
            </ul>
            </div>
        </div>
<?php
        endif;
        dynamic_sidebar('sidebar-left'); ?>

    </div>        
</aside><!-- /#sidebar -->
